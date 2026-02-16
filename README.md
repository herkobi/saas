# Herkobi

Herkobi, Laravel 12 uzerinde insa edilmis, cok kiracili (multi-tenant) bir SaaS platformudur. Abonelik yonetimi, plan bazli ozellik erisimi, odeme entegrasyonu (PayTR), addon sistemi ve kullanici yonetimi gibi temel SaaS ihtiyaclarini kapsayan bir altyapi sunar.

## Teknik Altyapi

| Bilesen | Versiyon / Deger |
|---------|-----------------|
| PHP | 8.4+ |
| Laravel | 12.x |
| Veritabani | MySQL |
| Kimlik Dogrulama | Laravel Fortify (2FA dahil) |
| Test Framework | Pest 4.x |
| Kod Formatlama | Laravel Pint |
| Odeme Altyapisi | PayTR |
| Varsayilan Para Birimi | TRY |
| Varsayilan Vergi | %20 KDV |
| **Frontend** | |
| UI Framework | Vue 3.5 (Composition API) |
| SPA Router | Inertia.js 2.x |
| Component Library | shadcn-vue (new-york-v4 style) + Reka UI |
| CSS Framework | Tailwind CSS v4 |
| Icon Library | Lucide Vue Next |
| Type Safety | TypeScript + Laravel Wayfinder |
| Data Table | TanStack Vue Table |
| Toast | Vue Sonner |

## Mimari Yapi

Herkobi iki ana bolgeden olusur. Backend Laravel 12, frontend Vue 3 + Inertia.js ile SPA benzeri bir deneyim sunar.

### Panel (Admin)

Platform yoneticileri icin yonetim paneli. Rotalari `routes/panel.php` dosyasinda tanimlidir.

- Plan, fiyat ve ozellik yonetimi (CRUD)
- Addon yonetimi (CRUD)
- Tenant (musteri) yonetimi ve detay goruntuleme
- Tenant abonelik islemleri (olusturma, iptal, yenileme, plan degistirme, deneme suresi uzatma, grace period uzatma)
- Tenant addon yonetimi (uzatma, iptal)
- Tenant ozellik override yonetimi
- Odeme listesi, durum guncelleme, faturalandirma
- Abonelik listeleme
- Genel ayarlar yonetimi
- Profil, sifre, 2FA ve bildirim yonetimi

### App (Tenant / Musteri)

Kiracilarin (musterilerin) kullandigi uygulama tarafi. Rotalari `routes/app.php` dosyasinda tanimlidir.

- Dashboard
- Abonelik goruntuleme ve iptal
- Fatura bilgisi goruntuleme ve guncelleme
- Odeme gecmisi
- Checkout (odeme) akisi
- Addon satin alma ve iptal
- Plan degistirme (upgrade/downgrade)
- Kullanici yonetimi (team members — config ile acilir/kapanir)
- Davetiye sistemi (email davet, token, dogrudan ekleme, otomatik kabul)
- Ozellik kullanim takibi
- Profil, sifre, 2FA ve bildirim yonetimi

### Odeme Callback

`POST /payment/callback` rotasi kimlik dogrulama gerektirmez (webhook). PayTR'den gelen odeme sonuclarini isler.

## Cok Kiracili (Multi-Tenant) Mimari

Herkobi, tek veritabani uzerinde cok kiracili bir mimari kullanir:

- **Tenant izolasyonu**: `BaseTenant` soyut modeli, global scope ile her sorguyu otomatik olarak aktif tenant'a filtreler.
- **Tenant context**: `LoadTenantContext` middleware'i, kimlik dogrulanmis kullaniciyi baz alarak aktif tenant'i belirler.
- **Kullanici–Tenant iliskisi**: Coca-cok iliski (`tenant_user` pivot tablosu). Roller: `OWNER`, `STAFF`.
- **Config ile kontrol (hibrit)**: Team ve coklu tenant ozellikleri config ve plan bazli middleware'ler ile yonetilir.

## KVKK / GDPR Uyumlulugu

Herkobi, veri saklama ve anonimleistirme politikalarini `config/herkobi.php` uzerinden yonetir:

- Aktivite loglari: 2 yil sonra anonimleistirme
- Bildirimler: 3 ay sonra arsivleme, 2 yil sonra anonimleistirme
- Kullanici verileri: `UserAnonymizationService` ile kalici olarak temizlenebilir

## Kurulum

### Gereksinimler

- PHP 8.4+
- MySQL
- Node.js & NPM
- Composer

### Hizli Kurulum

```bash
git clone <repo-url> herkobi
cd herkobi
composer run setup
```

Bu komut sirasiyla bagimliliklari kurar, ortam dosyasini olusturur, veritabanini hazirlar ve frontend asset'lerini build eder.

### Gelistirme Ortami

```bash
composer run dev
```

Laravel sunucusu, queue worker ve Vite gelistirme sunucusu es zamanli baslatilir.

### Testler

```bash
composer test
```

Pest 4.x test framework'u ile yapilandirilmistir.
