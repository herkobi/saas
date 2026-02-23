# Panel Sorunları ve İyileştirmeler

## 1. Sidebar Menü Yapısı Yeniden Düzenleme
**Dosya:** `resources/js/components/panel/AppSidebar.vue`

Mevcut yapıda Abonelikler ve Ödemeler ayrı collapsible menü grubu. Bunlar "Hesap Yönetimi" altında düz yapıda olmalı:
- [ ] Hesaplar → Müşteri listesi (`tenantsIndex`)
- [ ] Abonelikler → Tüm abonelikler (`subscriptionsIndex`)
- [ ] Kullanıcılar → Tüm kullanıcılar (yeni route/sayfa gerekli)
- [ ] Ödemeler → Tüm ödemeler (`paymentsIndex`)
- [ ] Yaklaşan Ödemeler → Ödemesine 30 gün ve altında kalan kayıtlar (yeni route/filtre gerekli)

**Not:** "Tamamlanan Ödemeler" ve "Aktif/Süresi Dolmuş Abonelikler" alt menüleri kaldırılacak. Hepsi ana sayfadaki filtrelerle yönetilecek.

## 2. Kullanıcılar Sayfası (Panel - Tüm Kullanıcılar)
**Dosya:** Yeni sayfa → `resources/js/pages/panel/Users/Index.vue`

Mevcut durumda "Kullanıcılar" linki `tenantsIndex()`'e gidiyor. Bunun yerine tüm tenant'lardaki kullanıcıları listeleyen bağımsız bir sayfa olmalı:
- [ ] Backend: Yeni controller veya mevcut controller'a method ekle (tüm kullanıcıları listele)
- [ ] Backend: Route tanımla (`panel/users`)
- [ ] Frontend: `panel/Users/Index.vue` sayfası oluştur (Ad, E-posta, Tenant, Rol, Durum, Kayıt tarihi)
- [ ] Sidebar'daki Kullanıcılar linkini yeni route'a bağla

## 3. Hesaplar (Tenants/Index) ve Abonelikler (Subscriptions/Index) — Boş Card Sorunu
**Dosyalar:** `resources/js/pages/panel/Tenants/Index.vue`, `resources/js/pages/panel/Subscriptions/Index.vue`

Bazı istatistik kartlarının içi boş geliyor. Backend'den gelen `statistics` verisinin hangi alanlarının eksik/null döndüğü kontrol edilmeli:
- [ ] Tenants/Index: İstatistik kartları ekle veya backend kontrolü yap
- [ ] Subscriptions/Index: İstatistik kartlarındaki veriyi backend ile karşılaştır, eksik alanları düzelt

## 4. Ödemeler (Payments/Index) — Müşteri Alanı "-" Gösteriyor
**Dosya:** `resources/js/pages/panel/Payments/Index.vue`

Tabloda müşteri sütunu hep "—" gösteriyor. Backend'den `tenant_name` alanı gelmemiş veya null:
- [ ] Backend PaymentService'deki `getPaginated` metodunu kontrol et — `tenant` ilişkisinin yüklenmesini sağla
- [ ] Frontend'de `payment.tenant_name` yerine doğru alanın kullanıldığından emin ol

## 5. Faturalandırma İyileştirmeleri
**Dosyalar:** `app/Models/Payment.php`, `app/Http/Controllers/Panel/Payment/PaymentController.php`, `resources/js/pages/panel/Payments/Show.vue`, `resources/js/pages/panel/Payments/Index.vue`

### 5a. Payment Model — Eksik Metodlar
Controller'da çağrılan `isInvoiced()` ve `isCompleted()` metodları Payment model'de yok:
- [ ] `isInvoiced(): bool` metodu ekle (`$this->invoiced_at !== null`)
- [ ] `isCompleted(): bool` metodu ekle (`$this->status === PaymentStatus::COMPLETED`)

### 5b. Faturalanmış Ödemelerin Durumu Değiştirilememeli
Faturalandırılmış bir ödemenin durumu artık değiştirilmemeli:
- [ ] Backend: `updateStatus` metodunda `invoiced_at` kontrolü ekle
- [ ] Frontend: `Payments/Show.vue` — Durum güncelleme kartını `payment.invoiced_at` varsa devre dışı bırak veya gizle

### 5c. "Faturala" → "Faturalandır" (Düzgün Türkçe)
- [ ] `Payments/Show.vue` — "Faturala" → "Faturalandır" (satır 278)
- [ ] `Payments/Index.vue` — "Faturala" → "Faturalandır" (varsa)
- [ ] Controller mesajlarında "faturalandırıldı" zaten doğru, kontrol et

### 5d. Faturalandırma Sırasında Fatura Numarası İstenmeli
Faturalandır butonuna basınca modal açılıp fatura numarası sorulmalı:
- [ ] Frontend: Dialog/Modal ekle — fatura numarası (invoice_number) input alanı
- [ ] Backend: `markAsInvoiced` metoduna `invoice_number` parametresi ekle
- [ ] Migration: `payments` tablosuna `invoice_number` (nullable string) sütunu ekle
- [ ] Frontend: Faturalanmış ödemelerde fatura numarasını göster

## 6. Yaklaşan Ödemeler Sayfası
Sidebar'da "Yaklaşan Ödemeler" linki normal `paymentsIndex()`'e gidiyor. Bunun ödemesine 30 gün ve altı kalan kayıtları göstermesi gerekiyor:
- [ ] Backend: PaymentController'a `upcoming` metodu veya `index`'e filtre parametresi ekle (bitiş tarihi 30 gün içinde olan aboneliklerin ödemeleri)
- [ ] Frontend: Ayrı sayfa veya `paymentsIndex` route'una `?upcoming=1` filtresi
- [ ] Sidebar linkini güncelle

## 7. Planlar Sayfası İyileştirmeleri
**Dosya:** `resources/js/pages/panel/Plans/Index.vue`

### 7a. Fiyat Gösterilmiyor
Tabloda "Fiyatlar" sütununda sadece `prices_count` (adet) gösteriliyor:
- [ ] Fiyat bilgisini göster (en azından başlangıç fiyatı veya fiyat aralığı). Backend'den `prices` ilişkisini veya özet bilgi gönder

### 7b. Arşivle Butonu Edit ile Aynı Yerde
Listede arşivle butonu düzenle butonunun yanında. Yanlışlıkla tıklanma riski var:
- [ ] Arşivle butonunu listeden kaldır
- [ ] Arşivleme işlemini Plans/Edit (detay) sayfasına taşı

## 8. Planlar Düzenle — Beyaz Ekran (SelectItem Boş Değer Hatası)
**Dosya:** `resources/js/pages/panel/Plans/Edit.vue`

Konsol hatası: "A SelectItem must have a value prop that is not an empty string"
- [ ] `tenant_id` Select'ine `<SelectItem value="all">Tüm hesaplar</SelectItem>` ekle ve form değerini `null` yerine `"all"` ile yönet
- [ ] `upgrade_proration_type` Select'ine `<SelectItem value="default">Varsayılan</SelectItem>` ekle
- [ ] `downgrade_proration_type` Select'ine `<SelectItem value="default">Varsayılan</SelectItem>` ekle
- [ ] Form gönderiminde bu sentinel değerleri (`"all"`, `"default"`) tekrar `null`'a çevir

## 9. Ayarlar — Firma Bilgileri Ayrı Sayfa
**Dosya:** `resources/js/pages/panel/Settings/General/Index.vue`

Genel ayarlar sayfasında firma bilgileri bölümü çok uzun. Ayrı sayfa olarak daha kullanışlı:
- [ ] `panel/Settings/Company/Index.vue` sayfası oluştur (firma bilgileri formu)
- [ ] Backend: Ayrı controller/method veya mevcut controller'da ayrı action
- [ ] Route tanımla (`panel/settings/company`)
- [ ] Settings iç layout'a "Firma Bilgileri" tabı ekle
- [ ] Genel ayarlar sayfasından firma bölümünü kaldır
