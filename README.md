# Herkobi

Herkobi, Laravel 12 uzerinde insa edilmis, cok kiracili (multi-tenant) bir SaaS platformudur. Abonelik yonetimi, plan bazli ozellik erisimi, odeme entegrasyonu (PayTR), addon sistemi ve kullanici yonetimi gibi temel SaaS ihtiyaclarini kapsayan bir altyapi sunar.

## Teknik Altyapi

| Bilesen | Versiyon / Deger |
|---------|-----------------|
| PHP | 8.2+ (Calisma ortami: 8.3) |
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
| Component Library | PrimeVue 4.5 (Aura theme) |
| CSS Framework | Tailwind CSS v4 |
| Type Safety | TypeScript + Laravel Wayfinder |

## Mimari Yapi

Herkobi iki ana bolgeden olusur. Backend Laravel 12, frontend Vue 3 + Inertia.js ile SPA benzeri deneyim sunar.

### Frontend Stack

- **Vue 3.5**: Composition API ve TypeScript ile tip guvenli component gelistirme
- **Inertia.js**: Server-side routing korunarak SPA deneyimi (tam sayfa yenilemesiz)
- **PrimeVue 4.5**: 90+ hazir component (Aura tema, forms kutuphanesi)
- **Tailwind CSS v4**: Utility-first CSS framework
- **Laravel Wayfinder**: Tip guvenli `route()` yardimcilari

### Panel (Admin)

Platform yoneticileri icin yonetim paneli. Rotalari `routes/panel.php` dosyasinda tanimlidir.

- Plan, fiyat ve ozellik yonetimi (CRUD)
- Addon yonetimi (CRUD)
- Tenant (musteri) yonetimi ve detay goruntuleme
- Tenant abonelik islemleri (olusturma, iptal, yenileme, plan degistirme, deneme suresi uzatma, grace period uzatma)
- Tenant addon yonetimi (uzatma, iptal)
- Tenant ozellik override yonetimi
- Odeme listesi, durum guncelleme, faturalandi olarak isaretleme
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
- Kullanici yonetimi (team members - config ile acilir/kapanir)
- Davetiye sistemi (email davet, token, dogrudan ekleme, otomatik kabul)
- Ozellik kullanim takibi
- Profil, sifre, 2FA ve bildirim yonetimi

### Odeme Callback

`POST /payment/callback` rotasi kimlik dogrulama gerektirmez (webhook). PayTR'den gelen odeme sonuclarini isler.

## Cok Kiracili (Multi-Tenant) Mimari

Herkobi, tek veritabani uzerinde cok kiracili bir mimari kullanir:

- **Tenant izolasyonu**: `BaseTenant` soyut modeli, global scope ile her sorguyu otomatik olarak aktif tenant'a filtreler. `Subscription`, `Payment`, `TenantUsage`, `TenantFeature`, `TenantInvitation` gibi modeller bu siniftan turetilir.
- **Tenant context**: `LoadTenantContext` middleware'i, kimlik dogrulanmis kullaniciyi baz alarak aktif tenant'i belirler ve `TenantContextService` uzerinden uygulamaya sunar.
- **Kullanici-Tenant iliskisi**: Coka-cok iliski (`tenant_user` pivot tablosu). Roller: `OWNER` ve `STAFF`.
- **Config ile kontrol (Hibrit)**: `herkobi.tenant.allow_team_members` ve `herkobi.tenant.allow_multiple_tenants` ayarlari ile team ve coklu tenant ozellikleri acilir/kapanir. Config acikken plan bazli `feature.access` middleware ile limit/erisim kontrolu yapilir.

## Veritabani Modelleri

Tum modeller ULID tabanli birincil anahtarlar kullanir (`HasUlids` trait).

### Temel Modeller

| Model | Tablo | Aciklama |
|-------|-------|----------|
| `User` | `users` | Platform kullanicilari. `UserType` enum ile ADMIN veya TENANT olarak ayrilir. Fortify 2FA destegi, soft delete ve anonimizasyon. |
| `Tenant` | `tenants` | Kiraclar (musteriler). Abonelik, odeme, kullanim ve ozellik iliskileri barindirir. `code`, `slug`, `domain`, JSON `account` ve `data` alanlari. |
| `Plan` | `plans` | Abonelik planlari. Ucretsiz/ucretli, aktif/pasif, genel/tenant'a ozel olabilir. Grace period ve proration tipi ayarlari. Arsivleme destegi. |
| `PlanPrice` | `plan_prices` | Plan fiyatlari. Bir planin birden fazla fiyati olabilir (aylik, yillik vb.). `interval` (month/year), `interval_count`, `trial_days`. |
| `Feature` | `features` | Platform ozellikleri. Tipler: `LIMIT` (sayisal), `FEATURE` (acik/kapali), `METERED` (sayacli). |
| `PlanFeature` | `plan_features` | Plan-ozellik iliskisi (pivot). Her planin hangi ozelliklere sahip oldugunu ve degerlerini tutar. |
| `Addon` | `addons` | Ek paketler. Feature'a baglidir. Tipler: `INCREMENT`, `UNLIMITED`, `BOOLEAN`. Tekrarli (recurring) veya tek seferlik. |
| `Subscription` | `subscriptions` | Tenant abonelikleri. Plan fiyatina baglidir. Durum yonetimi, trial, grace period, iptal ve planlanan degisiklik (`next_plan_price_id`). |
| `Checkout` | `checkouts` | Odeme islem kayitlari. Tipler: `NEW`, `RENEW`, `UPGRADE`, `DOWNGRADE`, `ADDON`, `ADDON_RENEW`. PayTR token ve merchant OID barindirir. |
| `Payment` | `payments` | Odeme kayitlari. Tenant, plan fiyati ve addon iliskileri. |
| `TenantAddon` | `tenant_addons` | Tenant-addon iliskisi (pivot, `Pivot` sinifinden turetilir). Miktar, ozel fiyat, baslangic/bitis tarihi, aktiflik. |
| `TenantFeature` | `tenant_features` | Tenant bazli ozellik override'lari. Admin panelden ayarlanir. |
| `TenantUsage` | `tenant_usages` | Metered ozellikler icin kullanim sayaclari. |
| `TenantInvitation` | `tenant_invitations` | Davetiyeler. SHA-256 token hash, status enum, expires_at. `BaseTenant`'tan turetilir. |
| `Activity` | `activities` | Aktivite gunlugu. |
| `DatabaseNotification` | `notifications` | Ozel bildirim modeli (Laravel'in varsayilan bildirimi uzerine). |
| `Setting` | `settings` | Platform ayarlari. |

## Enum Sistemi

PHP 8.2 backed enum'lar aktif olarak kullanilir:

| Enum | Degerler | Aciklama |
|------|----------|----------|
| `UserType` | `admin`, `tenant` | Kullanici tipi (panel yoneticisi veya musteri) |
| `UserStatus` | - | Kullanici durumu |
| `TenantUserRole` | `owner`, `staff` | Tenant icindeki kullanici rolu |
| `SubscriptionStatus` | `active`, `trialing`, `canceled`, `past_due`, `expired` | Abonelik durumu. `isValid()` ile erisim kontrolu. |
| `CheckoutType` | `new`, `renew`, `upgrade`, `downgrade`, `addon`, `addon_renew` | Odeme islemi tipi |
| `CheckoutStatus` | `pending`, `processing`, `completed`, `failed`, `expired`, `cancelled` | Checkout durumu |
| `PaymentStatus` | - | Odeme durumu |
| `PlanInterval` | `month`, `year` | Fatura donemi |
| `FeatureType` | `limit`, `feature`, `metered` | Ozellik tipi |
| `AddonType` | `increment`, `unlimited`, `boolean` | Addon tipi |
| `ResetPeriod` | - | Metered ozellik sifirlama donemi |
| `GracePeriod` | - | Grace period politikasi |
| `ProrationType` | `immediate`, `end_of_period` | Plan degisiklik davranisi |
| `InvitationStatus` | `pending`, `accepted`, `expired`, `revoked` | Davetiye durumu |

## Abonelik Yasam Dongusu

```
Yeni Kayit
  -> Checkout olustur (type=NEW)
  -> PayTR odeme sayfasi
  -> Odeme basarili -> Payment kaydi
  -> TenantPaymentSucceeded event
  -> TenantProcessSuccessfulPayment listener
  -> SubscriptionPurchaseService.processCheckout()
  -> Subscription olustur (status=ACTIVE veya TRIALING)

Yenileme:
  -> Checkout olustur (type=RENEW)
  -> Ayni odeme akisi
  -> Subscription ends_at guncellenir

Plan Yukseltme (Upgrade):
  -> PlanChangeService.resolveProrationType()
  -> IMMEDIATE: ProrationService ile kredi hesapla -> Checkout -> aninda gecis
  -> END_OF_PERIOD: next_plan_price_id ayarla -> donem sonunda gecis

Plan Dusurme (Downgrade):
  -> PlanChangeService.resolveProrationType()
  -> IMMEDIATE: processImmediateDowngrade() -> aninda plan degistir
  -> END_OF_PERIOD: schedulePlanChange() -> donem sonunda gecis

Iptal:
  -> subscription.canceled_at ayarlanir
  -> Donem sonuna kadar erisim devam eder
  -> Donem bitince status=EXPIRED

Suresi Dolma:
  -> CheckExpiredSubscriptionsJob (saatlik)
  -> Grace period varsa: status=PAST_DUE
  -> Grace period de dolunca: status=EXPIRED
```

## Addon Sistemi

Addonlar, planlarin uzerine ek kapasite veya ozellik saglar:

- **INCREMENT**: Mevcut limite deger ekler (ornegin +10 kullanici)
- **UNLIMITED**: Limiti tamamen kaldirir
- **BOOLEAN**: Bir ozelligi aktif/pasif yapar

```
Addon Satin Alma:
  -> AddonPurchaseService -> Checkout olustur (type=ADDON)
  -> Odeme basarili -> TenantProcessSuccessfulPayment
  -> AddonPurchaseService.processCheckout()
  -> TenantAddon pivot kaydini olustur/guncelle
  -> TenantAddonPurchased event

Recurring Addon Suresi Dolma:
  -> CheckExpiredAddonsJob (gunluk)
  -> expires_at gecmis ve is_active=true olanlari pasif yap
  -> TenantAddonExpired event + bildirim

Addon Hatirlatma:
  -> SendAddonExpiryReminderJob (gunluk)
  -> config('herkobi.addon.expiry_reminder_days') = [7, 3, 1]
```

## Proration (Orantili Hesaplama) Sistemi

Plan degisikliklerinde 4 senaryo desteklenir:

| Senaryo | Davranis |
|---------|----------|
| Upgrade + IMMEDIATE | Kalan gun kredisi hesaplanir, fark odenir, aninda gecis |
| Upgrade + END_OF_PERIOD | Donem sonunda gecis, odeme o zaman |
| Downgrade + IMMEDIATE | Aninda plan degistir |
| Downgrade + END_OF_PERIOD | next_plan_price_id ayarla, donem sonunda gecis |

Varsayilan davranislar `config/herkobi.php`'de tanimlidir. Plan bazinda override `plans` tablosundaki `upgrade_proration_type` ve `downgrade_proration_type` alanlari ile yapilir.

## Ozellik Erisimi ve Kullanim Takibi

```
Tenant.getFeatureLimit(feature):
  1. Tenant override kontrolu (TenantFeature)
  2. Plan ozelligi kontrolu (PlanFeature)
  3. Aktif addon kontrolu (INCREMENT/UNLIMITED)
  4. Sonuc: limit degeri veya null (sinirsiz)

Tenant.hasFeatureAccess(feature):
  1. Tenant override kontrolu
  2. Plan ozelligi kontrolu
  3. BOOLEAN addon kontrolu
  4. Sonuc: true/false

FeatureUsageService:
  - Metered ozellikler icin kullanim sayaci
  - ResetMeteredUsageJob ile periyodik sifirlama
  - Limit asimi kontrolu ve bildirim
```

## Zamanlanmis Gorevler (Scheduled Jobs)

| Job | Siklik | Aciklama |
|-----|--------|----------|
| `ExpireOldCheckoutsJob` | checkout_expiry / 2 dk | Suresi gecmis checkout'lari EXPIRED olarak isaretler |
| `CheckExpiredSubscriptionsJob` | Saatlik | Suresi dolmus abonelikleri tespit eder |
| `CheckTrialExpiryJob` | Saatlik | Trial suresi dolanlari tespit eder |
| `SendSubscriptionRenewalReminderJob` | Gunluk | Abonelik yenileme hatirlatmasi [7, 3, 1 gun] |
| `SendTrialEndingReminderJob` | Gunluk | Trial bitis hatirlatmasi [3, 1 gun] |
| `CheckExpiredAddonsJob` | Gunluk | Suresi dolmus addonlari pasif yapar |
| `SendAddonExpiryReminderJob` | Gunluk | Addon suresi dolma hatirlatmasi [7, 3, 1 gun] |
| `ProcessScheduledDowngradesJob` | Gunluk | Planlanan plan degisikliklerini uygular |
| `ResetMeteredUsageJob` | Gunluk | Metered ozellik sayaclarini sifirlar |
| `ArchiveOldNotificationsJob` | Gunluk | 90 gun onceki bildirimleri arsivler |
| `AnonymizeOldNotificationsJob` | Gunluk | 2 yil onceki bildirimleri anonimlestirir |
| `AnonymizeOldActivitiesJob` | Gunluk | 2 yil onceki aktiviteleri anonimlestirir |
| `ExpireOldInvitationsJob` | Gunluk | Suresi dolmus davetiyeleri expire eder |
| `SoftDeleteOldActivitiesJob` | Gunluk | Eski aktiviteleri soft delete yapar |

## Event-Driven Mimari

Sistem, yan etkileri (bildirim, aktivite logu, islem tetikleme) event-listener patterni ile yonetir.

### Tenant Tarafli Eventler

| Event | Tetiklenme Ani |
|-------|---------------|
| `TenantRegistered` | Yeni tenant kaydi |
| `TenantSubscriptionPurchased` | Abonelik satin alindi |
| `TenantSubscriptionRenewed` | Abonelik yenilendi |
| `TenantSubscriptionUpgraded` | Plan yukseltildi |
| `TenantSubscriptionDowngraded` | Plan dusuruldu |
| `TenantSubscriptionExpired` | Abonelik suresi doldu |
| `TenantPaymentSucceeded` | Odeme basarili |
| `TenantPaymentFailed` | Odeme basarisiz |
| `TenantCheckoutInitiated` | Checkout basladi |
| `TenantBillingUpdated` | Fatura bilgisi guncellendi |
| `TenantAddonPurchased` | Addon satin alindi |
| `TenantAddonExpired` | Addon suresi doldu |
| `TenantAddonCancelled` | Addon iptal edildi |
| `TenantTrialEnded` | Trial suresi doldu |
| `TenantMeteredUsageReset` | Metered kullanim sifirlandi |
| `TenantUsageLimitReached` | Kullanim limiti asildi |
| `TenantUserInvited` | Kullanici davet edildi (email gonderilir) |
| `TenantUserDirectlyAdded` | Mevcut kullanici dogrudan tenant'a eklendi |
| `TenantInvitationAccepted` | Davetiye kabul edildi |
| `TenantInvitationRevoked` | Davetiye iptal edildi |
| `TenantUserRemoved` | Kullanici tenant'tan cikarildi |
| `TenantUserRoleChanged` | Kullanici rolu degistirildi |
| `TenantProfileUpdated` | Profil guncellendi |
| `TenantPasswordChanged` | Sifre degistirildi |
| `TenantTwoFactorEnabled/Disabled` | 2FA acildi/kapandi |
| `ActivityLogged` | Aktivite kaydedildi |

### Panel Tarafli Eventler

| Event | Tetiklenme Ani |
|-------|---------------|
| `PanelPlanCreated/Updated/Archived` | Plan islemleri |
| `PanelPlanPriceCreated/Updated/Deleted` | Fiyat islemleri |
| `PanelFeatureCreated/Updated/Deleted` | Ozellik islemleri |
| `PanelAddonCreated/Updated/Deleted` | Addon islemleri |
| `PanelTenantUpdated` | Tenant guncellendi |
| `PanelTenantSubscriptionCreated/Cancelled/Renewed` | Abonelik islemleri |
| `PanelTenantSubscriptionPlanChanged` | Plan degisikligi |
| `PanelTenantTrialExtended` | Trial uzatildi |
| `PanelTenantGracePeriodExtended` | Grace period uzatildi |
| `PanelTenantFeatureOverrideUpdated` | Ozellik override |
| `PanelPaymentStatusUpdated` | Odeme durumu guncellendi |
| `PanelPaymentMarkedAsInvoiced` | Odeme faturalandi |
| `PanelSettingUpdated` | Ayar guncellendi |
| `PanelProfileUpdated` | Profil guncellendi |
| `PanelPasswordChanged` | Sifre degistirildi |
| `PanelTwoFactorEnabled/Disabled` | 2FA acildi/kapandi |

## Middleware Sistemi

| Middleware | Alias | Aciklama |
|------------|-------|----------|
| `LoadTenantContext` | `tenant.context` | Aktif tenant'i belirler ve context'e yukler |
| `EnsureActiveSubscription` | `subscription.active` | Aktif abonelik kontrolu |
| `EnsureWriteAccess` | `write.access` | Yazma yetkisi kontrolu (DRAFT kullanicilar engellenir) |
| `EnsureTenantOwner` | - | Tenant sahibi kontrolu |
| `EnsureTenantAllowsTeamMembers` | `tenant.allow_team_members` | Team members config kontrolu |
| `EnsureFeatureAccess` | `feature.access` | Plan bazli ozellik erisim kontrolu (FEATURE/LIMIT/METERED) |
| `EnsureTenant` | - | Tenant kullanici kontrolu |
| `EnsurePanel` | - | Panel (admin) kullanici kontrolu |
| `EnsureActiveUser` | - | Aktif kullanici kontrolu |

## Servis Katmani

Tum is mantigi `app/Services/` dizininde yasam, her servisin bir interface'i (`app/Contracts/`) vardir. Baglamalar `app/Providers/` altindaki service provider'larda yapilir.

### App (Tenant) Servisleri

| Servis | Aciklama |
|--------|----------|
| `SubscriptionService` | Abonelik goruntuleme ve iptal |
| `SubscriptionPurchaseService` | Yeni abonelik ve yenileme isleme |
| `SubscriptionLifecycleService` | Abonelik yasam dongusu (trial, expiry, grace period) |
| `CheckoutService` | Checkout olusturma, tutar hesaplama, PayTR entegrasyonu |
| `PaymentService` | Odeme kaydi islemleri |
| `PayTRService` | PayTR API entegrasyonu (token alma, imza dogrulama) |
| `ProrationService` | Orantili kredi hesaplama (gun bazli) |
| `PlanChangeService` | Plan degistirme (upgrade/downgrade, 4 senaryo) |
| `AddonPurchaseService` | Addon satin alma ve checkout isleme |
| `BillingService` | Fatura bilgisi yonetimi |
| `FeatureUsageService` | Ozellik kullanim takibi ve limit kontrolu |
| `UsageResetService` | Metered kullanim sifirlama |
| `UserService` | Tenant kullanici yonetimi |
| `InvitationService` | Davetiye yonetimi (davet, kabul, iptal, yeniden gonderme) |
| `ProfileService` | Profil guncelleme |
| `PasswordService` | Sifre degistirme |
| `TwoFactorService` | 2FA yonetimi |

### Panel (Admin) Servisleri

| Servis | Aciklama |
|--------|----------|
| `PlanService` | Plan CRUD islemleri |
| `PlanPriceService` | Plan fiyat CRUD |
| `FeatureService` | Ozellik CRUD |
| `AddonService` | Addon CRUD |
| `TenantService` | Tenant yonetimi |
| `TenantSubscriptionService` | Tenant abonelik islemleri (admin tarafindan) |
| `TenantFeatureService` | Tenant ozellik override yonetimi |
| `TenantAddonService` | Tenant addon uzatma ve iptal |
| `PaymentService` | Odeme yonetimi (durum guncelleme, faturalama) |
| `SubscriptionService` | Abonelik listeleme |
| `SettingService` | Genel ayar yonetimi |
| `ProfileService` | Admin profil yonetimi |
| `PasswordService` | Admin sifre degistirme |
| `TwoFactorService` | Admin 2FA yonetimi |

### Paylasilan (Shared) Servisler

| Servis | Aciklama |
|--------|----------|
| `TenantContextService` | Aktif tenant context yonetimi |
| `ActivityService` | Aktivite kaydi islemleri |
| `StorageService` | Dosya depolama islemleri |
| `NotificationService` | Bildirim islemleri |
| `UserAnonymizationService` | KVKK/GDPR uyumlu kullanici anonimizasyonu |

## Helper Siniflari

| Helper | Aciklama |
|--------|----------|
| `CurrencyHelper` | Para birimi formatlama ve varsayilan para birimi |
| `TaxHelper` | Vergi hesaplama (KDV) |
| `PaymentHelper` | Odeme islemleri yardimci fonksiyonlari |
| `MaskHelper` | Veri maskeleme (gizlilik) |
| `tenant_helpers.php` | Tenant ile ilgili global yardimci fonksiyonlar |
| `site_helpers.php` | Genel site yardimci fonksiyonlari |

## KVKK / GDPR Uyumlulugu

Herkobi, veri saklama politikalarini `config/herkobi.php` uzerinden yonetir:

- **Aktivite loglari**: 2 yil sonra anonimizasyon, opsiyonel soft delete
- **Bildirimler**: 3 ay sonra arsileme, 2 yil sonra anonimizasyon
- **Kullanici anonimizasyonu**: `UserAnonymizationService` ile kisisel verilerin kaldirilmasi

## Kurulum

### Gereksinimler

- PHP 8.2+
- MySQL
- Node.js ve NPM
- Composer

### Hizli Kurulum

```bash
git clone <repo-url> herkobi
cd herkobi
composer run setup
```

Bu komut sirasiyla:
1. Composer paketlerini yukler
2. `.env` dosyasini olusturur
3. Uygulama anahtari olusturur
4. Veritabani migration'larini calistirir
5. NPM paketlerini yukler
6. Frontend asset'lerini build eder

### Gelistirme Ortami

```bash
composer run dev
```

Bu komut uc servisi es zamanli baslatir:
- `php artisan serve` - Laravel sunucu
- `php artisan queue:listen --tries=1` - Kuyruk dinleyici
- `npm run dev` - Vite gelistirme sunucusu

### Testler

```bash
composer test
```

Pest 4.x test framework'u ile yapilandirilmistir.

### Kod Formatlama

```bash
vendor/bin/pint
```

Laravel Pint ile PSR-12 uyumlu formatlama.

## Konfigürasyon

### config/herkobi.php

Platform davranisini belirleyen ana konfigürasyon dosyasi:

- `tenant.allow_team_members`: Team ozelligi (varsayilan: false)
- `tenant.allow_multiple_tenants`: Coklu tenant olusturma (varsayilan: false)
- `subscription.renewal_reminder_days`: Yenileme hatirlatma gunleri
- `subscription.trial_reminder_days`: Trial hatirlatma gunleri
- `subscription.checkout_expiry_minutes`: Checkout suresi (varsayilan: 30 dk)
- `proration.upgrade_behavior`: Upgrade davranisi (varsayilan: immediate)
- `proration.downgrade_behavior`: Downgrade davranisi (varsayilan: end_of_period)
- `payment.*`: Ulke, para birimi, vergi ve odeme gateway ayarlari
- `addon.expiry_reminder_days`: Addon hatirlatma gunleri
- `addon.auto_renew`: Otomatik yenileme (varsayilan: false)
- `invitation.expires_days`: Davetiye gecerlilik suresi (varsayilan: 7 gun)
- `retention.*`: KVKK/GDPR veri saklama suresi ayarlari

### config/paytr.php

PayTR odeme gateway entegrasyon ayarlari.

## Dizin Yapisi

```
app/
  Contracts/          # Interface tanimlari
    App/              # Tenant tarafi
    Panel/            # Admin tarafi
    Shared/           # Paylasilan
  Enums/              # PHP 8.2 backed enum'lar
  Events/             # Domain event'leri
  Helpers/            # Yardimci sinif ve fonksiyonlar
  Http/
    Controllers/
      App/            # Tenant controller'lari
      Panel/          # Admin controller'lari
    Middleware/        # Custom middleware'ler
    Requests/         # Form request validation
  Jobs/               # Zamanlanmis gorevler
  Listeners/          # Event listener'lari
  Models/             # Eloquent modelleri
  Notifications/      # Bildirim siniflari
    App/              # Tenant bildirimleri
    Panel/            # Admin bildirimleri
  Providers/          # Service provider'lar (interface -> implementation baglama)
    App/
    Panel/
  Services/           # Is mantigi servisleri
    App/              # Tenant tarafi
    Panel/            # Admin tarafi
    Shared/           # Paylasilan
config/
  herkobi.php         # Platform konfigurasyonu
  paytr.php           # Odeme gateway ayarlari
database/
  factories/          # Test factory'leri
  migrations/         # Veritabani migration'lari
  seeders/            # Veri tohumlayicilar
routes/
  app.php             # Tenant rotalari
  panel.php           # Admin rotalari
  console.php         # Zamanlanmis gorevler
  web.php             # Genel web rotalari
resources/
  views/
    app.blade.php     # Inertia root template (tek Blade dosyasi)
    mail/             # Email sablonlari (Blade)
  js/
    app.ts            # Vue + Inertia bootstrap
    Pages/            # Inertia sayfalari (Vue SFC)
      Auth/           # Kimlik dogrulama (7 sayfa)
      App/            # Tenant uygulamasi (22 sayfa)
      Panel/          # Admin paneli (25+ sayfa)
    Components/       # Paylasilan componentler
      Layout/         # AppLayout, PanelLayout, AuthLayout
      UI/             # PrimeVue wrapper/custom
    Composables/      # Vue 3 composables
    types/            # TypeScript tip tanimlari
    wayfinder.ts      # Laravel Wayfinder route yardimcilari
  css/
    app.css           # Tailwind CSS + PrimeVue tema
tests/
  Feature/            # Feature testleri (Pest)
```
