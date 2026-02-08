# Claude Agent Instructions for Herkobi

## Proje Ozeti

Herkobi, Laravel 12 uzerinde kurulu cok kiracili (multi-tenant) bir SaaS platformudur. Iki ana bolge vardir: **Panel** (admin yonetim paneli) ve **App** (tenant/musteri uygulamasi). Sistem; abonelik yonetimi, plan bazli ozellik erisimi, PayTR odeme entegrasyonu, addon sistemi, proration (orantili hesaplama), KVKK/GDPR uyumlu veri saklama ve event-driven mimari uzerine kuruludur.

## Teknik Yigin

### Backend
- **PHP 8.2+** (calisma ortami 8.3), **Laravel 12.x**, **MySQL**
- **Laravel Fortify** (kimlik dogrulama + 2FA)
- **Pest 4.x** (test), **Laravel Pint** (formatlama)
- **PayTR** (odeme gateway), **TRY** (varsayilan para birimi), **%20 KDV**
- PHP 8.2 backed enum'lar tum durum/tip alanlari icin kullanilir

### Frontend
- **Vue 3.5** (Composition API)
- **Inertia.js 2.x** (SPA benzeri deneyim, Laravel routing korunur)
- **PrimeVue 4.5** (UI component library, Aura theme, @primevue/forms)
- **Tailwind CSS v4** (@tailwindcss/vite)
- **TypeScript** (tam tip guvenligi)
- **Laravel Wayfinder** (tip guvenli route yardimcilari)
- **ESLint + Prettier** (kod kalitesi ve formatlama)

## Ilk Bakis — Kritik Dosyalar

### Konfigurasyon
- `config/herkobi.php` — Platform davranisi: tenant ayarlari, abonelik, proration, odeme, addon, KVKK retention
- `config/paytr.php` — PayTR odeme gateway ayarlari

### Rotalar
- `routes/app.php` — Tenant rotalari. Middleware: `tenant.context`, `subscription.active`, `write.access`, `tenant.allow_team_members`, `feature.access`
- `GET/POST /invitation/accept/{token}` — Public davetiye kabul rotalari (auth sadece POST icin)
- `routes/panel.php` — Admin rotalari. Middleware: `auth`, `write.access`
- `routes/console.php` — Zamanlanmis gorevler (cron schedule)
- `POST /payment/callback` — PayTR webhook (kimlik dogrulama yok, `routes/app.php` icinde)

### Is Mantigi
- `app/Contracts/**` — Interface tanimlari (her servisin bir interface'i vardir)
- `app/Services/**` — Is mantigi (controller'lar ince tutulur, servisler cagirilir)
- `app/Providers/Panel/*ServiceProvider.php` ve `app/Providers/App/*ServiceProvider.php` — Interface -> implementation baglamalari

### Event Sistemi
- `app/Events/**` — Domain event'leri (abonelik, odeme, addon, kullanici islemleri)
- `app/Listeners/**` — Event handler'lari (aktivite logu, bildirim, islem tetikleme)
- `app/Listeners/TenantProcessSuccessfulPayment.php` — Odeme sonrasi yonlendirme (kritik listener)

### Odeme Akisi
- `app/Http/Controllers/App/Account/PaymentCallbackController.php` — PayTR webhook handler
- `app/Services/App/Account/PayTRService.php` — PayTR API entegrasyonu
- `app/Services/App/Account/CheckoutService.php` — Checkout olusturma ve tutar hesaplama
- `app/Services/App/Account/PaymentService.php` — Odeme kaydi islemleri

## Mimari Kurallar ve Konvansiyonlar

### ID Sistemi
- Tum modeller **ULID** tabanli birincil anahtarlar kullanir
- Migration'larda: `$table->ulid('id')->primary()`
- Model'lerde: `use HasUlids` trait'i

### Enum Kullanimi
- PHP 8.2 backed enum'lar tum durum/tip alanlari icin zorunludur
- Enum degerleri **asla degistirilmez** — degistirmek icin DB migration + data migration gerekir
- Model cast'larda enum sinifi kullanilir: `'status' => SubscriptionStatus::class`
- Mevcut enum'lar: `UserType`, `UserStatus`, `TenantUserRole`, `SubscriptionStatus`, `CheckoutType`, `CheckoutStatus`, `PaymentStatus`, `PlanInterval`, `FeatureType`, `AddonType`, `ResetPeriod`, `GracePeriod`, `ProrationType`, `InvitationStatus`

### Servis Katmani
- Tum is mantigi `app/Services/` icindedir. Controller metotlari ince tutulur, servis cagirir.
- Her servisin bir interface'i (`app/Contracts/`) vardir
- Baglamalar `app/Providers/` altindaki service provider'larda yapilir
- Yeni servis eklerken: Interface -> Service -> ServiceProvider binding sirasi takip edilir

### Multi-Tenant Izolasyon
- `BaseTenant` soyut modeli global scope ile tenant izolasyonu saglar
- `Subscription`, `Payment`, `TenantUsage`, `TenantFeature`, `TenantInvitation` modelleri `BaseTenant`'tan turetilir
- `TenantContextService` aktif tenant'i yonetir
- `LoadTenantContext` middleware'i tenant context'i yukler
- **Tenant.owner()** bir query helper metotudur (relationship degil), `?User` dondurur

### Config ile Davranis Kontrolu (Hibrit Pattern)
- `herkobi.tenant.allow_team_members` — Team ozelligi (UI gizleme + servis seviyesi kontrol)
- `herkobi.tenant.allow_multiple_tenants` — Coklu tenant olusturma
- `herkobi.proration.upgrade_behavior` / `downgrade_behavior` — Varsayilan proration davranisi
- `herkobi.addon.auto_renew` — Addon otomatik yenileme
- `herkobi.invitation.expires_days` — Davetiye gecerlilik suresi (varsayilan: 7 gun)
- **Hibrit kontrol**: Config kapali → ozellik tamamen engellenir. Config acik → plan bazli `feature.access` middleware ile limit/erisim kontrolu yapilir.
- Config ayarlari UI'da gizleme yapar ve **servis seviyesinde de enforce edilir** (`TenantContextService.canInviteTeamMember()` hem config hem plan kontrolu yapar)

### Proration Sistemi
- 4 senaryo: Upgrade/Downgrade x IMMEDIATE/END_OF_PERIOD
- Varsayilan davranis `config/herkobi.php`'den gelir
- Plan bazinda override: `plans.upgrade_proration_type` ve `plans.downgrade_proration_type` (nullable)
- `Plan.resolveUpgradeProration()` / `resolveDowngradeProration()` DB degerini kontrol eder, yoksa config'e duser
- `ProrationService.calculate()` gun bazli kredi hesaplar (IMMEDIATE icin) veya tam fiyat dondurur (END_OF_PERIOD icin)
- `PlanChangeService` 4 senaryoyu yonetir: aninda gecis, planlanan gecis, checkout yonlendirmesi

### Odeme Akisi
- Checkout olusturulur -> PayTR token alinir -> Iframe gosterilir -> Webhook callback gelir
- `PaymentCallbackController.handle()` webhook'u isler
- `TenantProcessSuccessfulPayment` listener checkout type'a gore yonlendirir:
  - `NEW/RENEW` -> `SubscriptionPurchaseService`
  - `UPGRADE` -> `PlanChangeService.processUpgrade()`
  - `ADDON/ADDON_RENEW` -> `AddonPurchaseService.processCheckout()`
- Webhook rotasi kimlik dogrulama gerektirmez; imza dogrulama `PayTRService` icinde yapilir
- PayTR credential'lari `config/paytr.php`'den gelir, kodda asla aciga cikmamali

### Addon Sistemi
- Addon'lar `Feature`'a baglidir ve plan limitlerini genisletir
- 3 tip: `INCREMENT` (limite ekler), `UNLIMITED` (limiti kaldirir), `BOOLEAN` (ozelligi acar)
- `TenantAddon` modeli `Pivot` sinifinden turetilir, global tenant scope vardir
- Recurring addon'larin `expires_at` alani vardir
- `CheckExpiredAddonsJob` suresi dolanlari pasif yapar, `SendAddonExpiryReminderJob` hatirlatma gonderir

## Frontend Mimari (Vue 3 + Inertia)

### Dosya Yapisi
```
resources/
├── views/
│   └── app.blade.php          # Tek Inertia root template
│   └── mail/                  # Email sablonlari (Blade kalir)
├── js/
│   ├── app.ts                 # Vue + Inertia bootstrap
│   ├── Pages/                 # Inertia sayfalari (Vue SFC)
│   │   ├── Auth/              # Kimlik dogrulama (7 sayfa)
│   │   ├── App/               # Tenant uygulamasi (22 sayfa)
│   │   └── Panel/             # Admin paneli (25+ sayfa)
│   ├── Components/            # Paylasilan componentler
│   │   ├── Layout/            # AppLayout, PanelLayout, AuthLayout
│   │   └── UI/                # PrimeVue wrapper/custom
│   ├── Composables/           # Vue 3 composables (useAuth, useTenant)
│   ├── types/                 # TypeScript tip tanimlari
│   └── wayfinder.ts           # Tip guvenli route() fonksiyonu
└── css/
    └── app.css                # Tailwind + PrimeVue tema
```

### Frontend Konvansiyonlar
- **Composition API**: `<script setup>` syntax kullan (Options API degil)
- **TypeScript**: Tum Vue componentleri `.vue` dosyalarinda TypeScript kullanir
- **Route helpers**: `route('route.name')` (Wayfinder) ile tip guvenli routing
- **Props**: `defineProps<{ ... }>()` ile TypeScript interface tanimlari
- **Inertia form**: `useForm()` helper ile form yonetimi ve validation
- **Shared data**: `HandleInertiaRequests` middleware ile global data (`auth`, `tenant`, `flash`)
- **PrimeVue**: Component import'lari dogrudan `primevue/*` yolundan yapilir
- **Styling**: Tailwind utility class'lari + PrimeVue Aura theme

### Controller -> Inertia Donusumu
```php
// ONCE (Blade)
return view('app.dashboard', ['data' => $data]);

// SONRA (Inertia)
return Inertia::render('app/dashboard', ['data' => $data]);
```

### Blade Kullanimi
- **Sadece** email template'leri icin Blade kullanilir (`resources/views/mail/`)
- Web sayfalari artik Vue componentleridir (Blade degil)

## Dil ve String'ler

- Kullanici arayuzu string'leri genellikle **Turkce**'dir
- Enum label'lari Turkce'dir (ornegin: 'Aktif', 'Suresi Doldu', 'Eklenti Satin Alma')
- Validation mesajlari Turkce'dir
- Turkce dil tutarliligi korunmali — lokalizasyon istenmedikce degistirmeyin
- Turkce label veya UX davranisi konusunda emin degilseniz, tahmin etmek yerine soru sorun

## Gelistirici Komutlari

```bash
# Kurulum (env, migrate, npm install, build)
composer run setup

# Gelistirme (server + queue + vite es zamanli)
composer run dev

# Testler (Pest)
composer test

# Kod formatlama (Pint)
vendor/bin/pint
```

## Yeni Ozellik Ekleme Kontrol Listesi

1. Gerekiyorsa `app/Contracts/**` altina yeni interface ekle veya mevcut interface'i guncelle
2. `app/Services/**` altinda implement et — controller'lar ince tutulur
3. `app/Providers/` altindaki uygun service provider'da binding yap
4. DB degisikligi gerekiyorsa migration ekle (ULID konvansiyonu, enum varsayilanlari)
   - **users** ve **notifications** tablolari icin YENI migration dosyasi olustur
   - Diger tablolari mevcut migration dosyalarinda guncelle
5. Yan etkiler icin `app/Events/**` altina event, `app/Listeners/**` altina listener ekle
6. Rotalari guncelle (`routes/app.php` veya `routes/panel.php`) ve middleware uygula
7. Servis seviyesinde config kontrollerinin rota guard'lari ile tutarli oldugunu dogrula
8. Test yaz (Pest tercih edilir) ve `composer test` calistir

## Guvenlik ve Dikkat Notlari

- Enum degerlerini DB migration + data migration olmadan **degistirme**
- Odeme/guvenlik hassas kodlarinda credential'lari aciga cikarma, `config/paytr.php` kullan
- Webhook rotasi (`/payment/callback`) kimlik dogrulama gerektirmez — imza dogrulama servistedir
- Kodlarda **Log birakma** (logging statement'lari production kodunda olmamali)
- `Tenant.owner()` bir relationship degil, query helper — eager loading ile calismaz
- `TenantAddon` bir `Pivot` sinifidir — standart model gibi davranmaz
- `BaseTenant`'tan turetilen modellerde global tenant scope otomatik uygulanir — scope'suz sorgu icin `withoutTenantScope()` kullan
- Yeni checkout type eklerken `TenantProcessSuccessfulPayment` listener'daki match ifadesini guncelle
- `ProcessScheduledDowngradesJob` hem upgrade hem downgrade isler — yon tespiti fiyat karsilastirmasi ile yapilir
- Davetiye token'lari SHA-256 ile hash'lenir (raw token sadece email'de bulunur, DB'de hash saklanir)
- `TenantInvitation` cross-tenant sorgularda `withoutTenantScope()` kullanilmali (token ile arama, expire job)

## Veritabani Sema Ozeti

### Temel Tablolar
- `users` — Kullanicilar (ULID, soft delete, 2FA, anonimizasyon)
- `tenants` — Kiraclar (ULID, soft delete, JSON account/data)
- `tenant_user` — Kullanici-tenant iliskisi (pivot, role: owner/staff)
- `plans` — Planlar (ULID, soft delete, proration tipi override'lari)
- `plan_prices` — Plan fiyatlari (ULID, soft delete, interval, trial_days)
- `features` — Ozellikler (ULID, soft delete, type: limit/feature/metered)
- `plan_features` — Plan-ozellik iliskisi (pivot, value)
- `subscriptions` — Abonelikler (ULID, soft delete, status enum, trial/grace period, next_plan_price_id)
- `checkouts` — Checkout islemleri (ULID, soft delete, type/status enum, PayTR token)
- `payments` — Odemeler (ULID, soft delete, tenant/plan_price/addon iliskileri)
- `addons` — Ek paketler (ULID, soft delete, recurring, feature baglantisi)
- `tenant_addons` — Tenant-addon iliskisi (pivot, quantity, expires_at, is_active)
- `tenant_features` — Tenant ozellik override'lari
- `tenant_usages` — Metered kullanim sayaclari
- `tenant_invitations` — Davetiyeler (ULID, soft delete, token hash, status enum, expires_at)
- `activities` — Aktivite gunlugu
- `notifications` — Bildirimler (ozel model, arsivleme destegi)
- `archived_notifications` — Arsivlenmis bildirimler
- `settings` — Platform ayarlari

### Iliski Haritasi
```
User --M:M--> Tenant (tenant_user pivot, role)
Tenant --1:M--> Subscription
Tenant --1:M--> Payment
Tenant --M:M--> Addon (tenant_addons pivot)
Tenant --1:M--> TenantFeature (override)
Tenant --1:M--> TenantUsage
Tenant --1:M--> TenantInvitation
Plan --1:M--> PlanPrice
Plan --M:M--> Feature (plan_features pivot, value)
PlanPrice --1:M--> Subscription
Subscription --> PlanPrice (plan_price_id, next_plan_price_id)
Feature --1:M--> Addon
Addon --1:M--> Payment
Checkout --> Tenant, PlanPrice, Addon, Payment
```

## Zamanlanmis Gorevler

| Job | Siklik | Aciklama |
|-----|--------|----------|
| `ExpireOldCheckoutsJob` | ~15 dk | Suresi gecmis checkout'lari isaretler |
| `CheckExpiredSubscriptionsJob` | Saatlik | Abonelik suresi dolma tespiti |
| `CheckTrialExpiryJob` | Saatlik | Trial suresi dolma tespiti |
| `SendSubscriptionRenewalReminderJob` | Gunluk | Yenileme hatirlatmasi |
| `SendTrialEndingReminderJob` | Gunluk | Trial bitis hatirlatmasi |
| `CheckExpiredAddonsJob` | Gunluk | Addon suresi dolma tespiti |
| `SendAddonExpiryReminderJob` | Gunluk | Addon hatirlatmasi |
| `ProcessScheduledDowngradesJob` | Gunluk | Planlanan plan degisikliklerini uygular |
| `ResetMeteredUsageJob` | Gunluk | Metered kullanim sifirlama |
| `ArchiveOldNotificationsJob` | Gunluk | Bildirim arsivleme (90 gun) |
| `AnonymizeOldNotificationsJob` | Gunluk | Bildirim anonimizasyon (2 yil) |
| `AnonymizeOldActivitiesJob` | Gunluk | Aktivite anonimizasyon (2 yil) |
| `ExpireOldInvitationsJob` | Gunluk | Suresi dolmus davetiyeleri expire eder |
| `SoftDeleteOldActivitiesJob` | Gunluk | Aktivite soft delete |

## Event-Listener Haritalamasi

Kritik event -> listener iliskileri:

- `TenantPaymentSucceeded` -> `TenantProcessSuccessfulPayment` (checkout type'a gore abonelik/addon isleme)
- `TenantPaymentSucceeded` -> `LogTenantPaymentActivity`, `SendTenantPaymentNotifications`
- `TenantSubscriptionPurchased` -> `LogTenantSubscriptionActivity`, `SendTenantWelcomeEmail`
- `TenantSubscriptionUpgraded` -> `LogTenantSubscriptionActivity`
- `TenantSubscriptionDowngraded` -> `LogTenantSubscriptionActivity`
- `TenantSubscriptionExpired` -> `LogTenantSubscriptionActivity`, `SendTenantSubscriptionExpiry`
- `TenantAddonPurchased` -> `LogTenantAddonActivity`
- `TenantAddonExpired` -> `LogTenantAddonActivity`
- `TenantAddonCancelled` -> `LogTenantAddonActivity`
- `TenantTrialEnded` -> `LogTenantTrialActivity`, `SendTenantTrialEnded`
- `TenantUsageLimitReached` -> `LogTenantUsageLimitActivity`, `SendTenantUsageLimitReached`
- `TenantUserInvited` -> `SendInvitationEmail`, `LogTenantInvitationActivity`
- `TenantUserDirectlyAdded` -> `LogTenantInvitationActivity` (bildirim + aktivite)
- `TenantInvitationAccepted` -> `LogTenantInvitationActivity` (bildirim + aktivite)
- `TenantInvitationRevoked` -> `LogTenantInvitationActivity`
- Panel event'leri -> `LogPanel*Activity` listener'lari + bildirim listener'lari
