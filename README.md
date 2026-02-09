# Herkobi

Herkobi, Laravel 12 üzerinde inşa edilmiş, çok kiracılı (multi-tenant) bir SaaS platformudur. Abonelik yönetimi, plan bazlı özellik erişimi, ödeme entegrasyonu (PayTR), addon sistemi ve kullanıcı yönetimi gibi temel SaaS ihtiyaçlarını kapsayan bir altyapı sunar.

## Teknik Altyapı

| Bileşen | Versiyon / Değer |
|---------|-----------------|
| PHP | 8.2+ (Çalışma ortamı: 8.3) |
| Laravel | 12.x |
| Veritabanı | MySQL |
| Kimlik Doğrulama | Laravel Fortify (2FA dahil) |
| Test Framework | Pest 4.x |
| Kod Formatlama | Laravel Pint |
| Ödeme Altyapısı | PayTR |
| Varsayılan Para Birimi | TRY |
| Varsayılan Vergi | %20 KDV |
| **Frontend** | |
| UI Framework | Vue 3.5 (Composition API) |
| SPA Router | Inertia.js 2.x |
| Component Library | PrimeVue 4.5 (Aura theme) |
| CSS Framework | Tailwind CSS v4 |
| Type Safety | TypeScript + Laravel Wayfinder |

## Mimari Yapı

Herkobi iki ana bölgeden oluşur. Backend Laravel 12, frontend Vue 3 + Inertia.js ile SPA benzeri bir deneyim sunar.

### Frontend Stack

- **Vue 3.5**: Composition API ve TypeScript ile tip güvenli component geliştirme
- **Inertia.js**: Server-side routing korunarak SPA deneyimi (tam sayfa yenilemesiz)
- **PrimeVue 4.5**: 90+ hazır component (Aura tema, forms kütüphanesi)
- **Tailwind CSS v4**: Utility-first CSS framework
- **Laravel Wayfinder**: Tip güvenli `route()` yardımcıları

### Panel (Admin)

Platform yöneticileri için yönetim paneli. Rotaları `routes/panel.php` dosyasında tanımlıdır.

- Plan, fiyat ve özellik yönetimi (CRUD)
- Addon yönetimi (CRUD)
- Tenant (müşteri) yönetimi ve detay görüntüleme
- Tenant abonelik işlemleri (oluşturma, iptal, yenileme, plan değiştirme, deneme süresi uzatma, grace period uzatma)
- Tenant addon yönetimi (uzatma, iptal)
- Tenant özellik override yönetimi
- Ödeme listesi, durum güncelleme, faturalandı olarak işaretleme
- Abonelik listeleme
- Genel ayarlar yönetimi
- Profil, şifre, 2FA ve bildirim yönetimi

### App (Tenant / Müşteri)

Kiracıların (müşterilerin) kullandığı uygulama tarafı. Rotaları `routes/app.php` dosyasında tanımlıdır.

- Dashboard
- Abonelik görüntüleme ve iptal
- Fatura bilgisi görüntüleme ve güncelleme
- Ödeme geçmişi
- Checkout (ödeme) akışı
- Addon satın alma ve iptal
- Plan değiştirme (upgrade/downgrade)
- Kullanıcı yönetimi (team members – config ile açılır/kapanır)
- Davetiye sistemi (email davet, token, doğrudan ekleme, otomatik kabul)
- Özellik kullanım takibi
- Profil, şifre, 2FA ve bildirim yönetimi

### Ödeme Callback

`POST /payment/callback` rotası kimlik doğrulama gerektirmez (webhook). PayTR’den gelen ödeme sonuçlarını işler.

## Çok Kiracılı (Multi-Tenant) Mimari

Herkobi, tek veritabanı üzerinde çok kiracılı bir mimari kullanır:

- **Tenant izolasyonu**: `BaseTenant` soyut modeli, global scope ile her sorguyu otomatik olarak aktif tenant’a filtreler.
- **Tenant context**: `LoadTenantContext` middleware’i, kimlik doğrulanmış kullanıcıyı baz alarak aktif tenant’ı belirler.
- **Kullanıcı–Tenant ilişkisi**: Çoka-çok ilişki (`tenant_user` pivot tablosu). Roller: `OWNER`, `STAFF`.
- **Config ile kontrol (hibrit)**: Team ve çoklu tenant özellikleri config ve plan bazlı middleware’ler ile yönetilir.

## KVKK / GDPR Uyumluluğu

Herkobi, veri saklama ve anonimleştirme politikalarını `config/herkobi.php` üzerinden yönetir:

- Aktivite logları: 2 yıl sonra anonimleştirme
- Bildirimler: 3 ay sonra arşivleme, 2 yıl sonra anonimleştirme
- Kullanıcı verileri: `UserAnonymizationService` ile kalıcı olarak temizlenebilir

## Kurulum

### Gereksinimler

- PHP 8.2+
- MySQL
- Node.js & NPM
- Composer

### Hızlı Kurulum

```bash
git clone <repo-url> herkobi
cd herkobi
composer run setup
```

Bu komut sırasıyla bağımlılıkları kurar, ortam dosyasını oluşturur, veritabanını hazırlar ve frontend asset’lerini build eder.

### Geliştirme Ortamı

```bash
composer run dev
```

Laravel sunucusu, queue worker ve Vite geliştirme sunucusu eş zamanlı başlatılır.

### Testler

```bash
composer test
```

Pest 4.x test framework’ü ile yapılandırılmıştır.

