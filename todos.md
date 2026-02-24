# Panel Sorunları ve İyileştirmeler

## 1. Sidebar Menü Yapısı Yeniden Düzenleme
**Dosya:** `resources/js/components/panel/AppSidebar.vue`

Mevcut yapıda Abonelikler ve Ödemeler ayrı collapsible menü grubu. Yeni yapı şöyle olmalı:

**Hesap Yönetimi:**
- [x] Hesaplar → Müşteri listesi (`tenantsIndex`)
- [x] Abonelikler → Tüm abonelikler (`subscriptionsIndex`)
- [x] Kullanıcılar → Tüm kullanıcılar (`usersIndex`)

**Ödemeler** (ayrı menü grubu):
- [x] Ödemeler → Tüm ödemeler (`paymentsIndex`)
- [x] Yaklaşan Ödemeler → Ödemesine 30 gün ve altında kalan kayıtlar (`?upcoming=1` filtresi ile)

**Sistem Ayarları:**
- [x] Genel Ayarlar → Mevcut ayarlar sayfası (`settingsIndex`)
- [x] Firma Bilgileri → Yeni firma bilgileri sayfası (`companySettingsIndex`)

**Not:** "Tamamlanan Ödemeler" ve "Aktif/Süresi Dolmuş Abonelikler" alt menüleri kaldırılacak. Hepsi ana sayfadaki filtrelerle yönetilecek. ✅ Kaldırılmış.

## 2. İç Sayfa Navigasyonu Standardizasyonu
**Dosyalar:** `resources/js/pages/panel/Settings/layout/Layout.vue`, `resources/js/pages/panel/Plans/layout/Layout.vue`, `resources/js/pages/panel/Tenants/layout/Layout.vue`

Mevcut durumda tutarsızlık var:
- Ayarlar → Sol sidebar navigasyonu (Button listesi)
- Planlar → Üst tab navigasyonu (border-b ile)
- Tenants → Üst tab navigasyonu (border-b ile)

Tek bir standart belirlenecek. Planlar ve Tenants'taki **üst tab** yapısı daha yaygın kullanılıyor:
- [x] Ayarlar layout'unu üst tab yapısına çevir (Plans/layout/Layout.vue ile aynı pattern)
- [x] Ayarlar iç tabları: Genel, Firma Bilgileri
- [x] Tüm iç sayfa layout'larının aynı tab stilini kullandığından emin ol

## 3. Kullanıcılar Sayfası (Panel - Tüm Kullanıcılar)
**Dosya:** Yeni sayfa → `resources/js/pages/panel/Users/Index.vue`

Mevcut durumda "Kullanıcılar" linki `tenantsIndex()`'e gidiyor. Bunun yerine tüm tenant'lardaki kullanıcıları listeleyen bağımsız bir sayfa olmalı:
- [x] Backend: Yeni controller veya mevcut controller'a method ekle (tüm kullanıcıları listele)
- [x] Backend: Route tanımla (`panel/users`)
- [x] Frontend: `panel/Users/Index.vue` sayfası oluştur (Ad, E-posta, Tenant, Rol, Durum, Kayıt tarihi)
- [x] Sidebar'daki Kullanıcılar linkini yeni route'a bağla

## 4. Hesaplar (Tenants/Index) ve Abonelikler (Subscriptions/Index) — Boş Card Sorunu
**Dosyalar:** `resources/js/pages/panel/Tenants/Index.vue`, `resources/js/pages/panel/Subscriptions/Index.vue`

Bazı istatistik kartlarının içi boş geliyor. Backend'den gelen `statistics` verisinin hangi alanlarının eksik/null döndüğü kontrol edilmeli:
- [x] Tenants/Index: İstatistik kartları ekle veya backend kontrolü yap
- [x] Subscriptions/Index: İstatistik kartlarındaki veriyi backend ile karşılaştır, eksik alanları düzelt

## 5. Ödemeler (Payments/Index) — Müşteri Alanı "-" Gösteriyor
**Dosya:** `resources/js/pages/panel/Payments/Index.vue`

Tabloda müşteri sütunu hep "—" gösteriyor. Backend'den `tenant_name` alanı gelmemiş veya null:
- [x] Backend PaymentService'deki `getPaginated` metodunu kontrol et — `tenant` ilişkisinin yüklenmesini sağla
- [x] Frontend'de `payment.tenant_name` yerine doğru alanın kullanıldığından emin ol

## 6. Faturalandırma İyileştirmeleri
**Dosyalar:** `app/Models/Payment.php`, `app/Http/Controllers/Panel/Payment/PaymentController.php`, `resources/js/pages/panel/Payments/Show.vue`, `resources/js/pages/panel/Payments/Index.vue`

### 6a. Payment Model — Eksik Metodlar
Controller'da çağrılan `isInvoiced()` ve `isCompleted()` metodları Payment model'de yok:
- [x] `isInvoiced(): bool` metodu ekle (`$this->invoiced_at !== null`)
- [x] `isCompleted(): bool` metodu ekle (`$this->status === PaymentStatus::COMPLETED`)

### 6b. Faturalanmış Ödemelerin Durumu Değiştirilememeli
Faturalandırılmış bir ödemenin durumu artık değiştirilmemeli:
- [x] Backend: `updateStatus` metodunda `invoiced_at` kontrolü ekle
- [x] Frontend: `Payments/Show.vue` — Durum güncelleme kartını `payment.invoiced_at` varsa devre dışı bırak veya gizle

### 6c. "Faturala" → "Faturalandır" (Düzgün Türkçe)
- [x] `Payments/Show.vue` — "Faturala" → "Faturalandır"
- [x] `Payments/Index.vue` — "Faturala" → "Faturalandır"
- [x] Controller mesajlarında "faturalandırıldı" zaten doğru, kontrol et

### 6d. Faturalandırma Sırasında Fatura Numarası İstenmeli
Faturalandır butonuna basınca modal açılıp fatura numarası sorulmalı:
- [x] Frontend: Dialog/Modal ekle — fatura numarası (invoice_number) input alanı
- [x] Backend: `markAsInvoiced` metoduna `invoice_number` parametresi ekle
- [x] Migration: `payments` tablosuna `invoice_number` (nullable string) sütunu ekle
- [x] Frontend: Faturalanmış ödemelerde fatura numarasını göster

## 7. Yaklaşan Ödemeler Sayfası
Sidebar'da "Yaklaşan Ödemeler" linki normal `paymentsIndex()`'e gidiyor. Bunun ödemesine 30 gün ve altı kalan kayıtları göstermesi gerekiyor:
- [x] Backend: PaymentController'a `upcoming` metodu veya `index`'e filtre parametresi ekle (bitiş tarihi 30 gün içinde olan aboneliklerin ödemeleri)
- [x] Frontend: Ayrı sayfa veya `paymentsIndex` route'una `?upcoming=1` filtresi
- [x] Sidebar linkini güncelle

## 8. Planlar Sayfası İyileştirmeleri
**Dosya:** `resources/js/pages/panel/Plans/Index.vue`

### 8a. Fiyat Gösterilmiyor
Tabloda "Fiyatlar" sütununda sadece `prices_count` (adet) gösteriliyor:
- [x] Fiyat bilgisini göster (en azından başlangıç fiyatı veya fiyat aralığı). Backend'den `prices` ilişkisini veya özet bilgi gönder

### 8b. Arşivle Butonu Edit ile Aynı Yerde
Listede arşivle butonu düzenle butonunun yanında. Yanlışlıkla tıklanma riski var:
- [x] Arşivle butonunu listeden kaldır
- [x] Arşivleme işlemini Plans/Edit (detay) sayfasına taşı

## 9. Planlar Düzenle — Beyaz Ekran (SelectItem Boş Değer Hatası)
**Dosya:** `resources/js/pages/panel/Plans/Edit.vue`

Konsol hatası: "A SelectItem must have a value prop that is not an empty string"
- [x] `tenant_id` Select'ine `<SelectItem value="all">Tüm hesaplar</SelectItem>` ekle ve form değerini `null` yerine `"all"` ile yönet
- [x] `upgrade_proration_type` Select'ine `<SelectItem value="default">Varsayılan</SelectItem>` ekle
- [x] `downgrade_proration_type` Select'ine `<SelectItem value="default">Varsayılan</SelectItem>` ekle
- [x] Form gönderiminde bu sentinel değerleri (`"all"`, `"default"`) tekrar `null`'a çevir

## 10. Ayarlar — Firma Bilgileri Ayrı Sayfa
**Dosya:** `resources/js/pages/panel/Settings/General/Index.vue`

Genel ayarlar sayfasında firma bilgileri bölümü çok uzun. Ayrı sayfa olarak daha kullanışlı:
- [x] `panel/Settings/Company/Index.vue` sayfası oluştur (firma bilgileri formu)
- [x] Backend: Ayrı controller/method veya mevcut controller'da ayrı action
- [x] Route tanımla (`panel/settings/company`)
- [x] Ayarlar üst tab layout'una "Firma Bilgileri" tabı ekle
- [x] Sidebar "Sistem Ayarları" altına "Firma Bilgileri" linki ekle
- [x] Genel ayarlar sayfasından firma bölümünü kaldır

## 11. Butonlara İkon Ekleme (Genel İyileştirme)

Tüm panel sayfalarındaki butonlara, özellikle submit/kaydet butonlarına Lucide ikonları eklenmeli:
- [x] Kaydet/Güncelle butonları → `Save` veya `Check` ikonu
- [x] İptal/Vazgeç butonları → `X` ikonu
- [x] Sil/Kaldır butonları → `Trash2` ikonu
- [x] Ekle/Oluştur butonları → `Plus` ikonu
- [x] Geri butonları → `ArrowLeft` ikonu
- [x] Filtrele butonları → `Filter` ikonu
- [x] Faturalandır butonları → `FileText` ikonu (zaten var)
- [x] Diğer aksiyon butonları → Uygun Lucide ikonu
- [x] Mevcut sayfaları tarayıp ikonsuz butonları tespit et ve ekle
