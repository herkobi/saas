# Claude Agent Instructions for Herkobi

## Proje Özeti

Herkobi, Laravel 12 üzerinde kurulu çok kiracılı (multi-tenant) bir SaaS platformudur. İki ana bölge vardır: **Panel** (admin yönetim paneli) ve **App** (tenant/müşteri uygulaması). Sistem; abonelik yönetimi, plan bazlı özellik erişimi, PayTR ödeme entegrasyonu, addon sistemi, proration (orantılı hesaplama), KVKK/GDPR uyumlu veri saklama ve event-driven mimari üzerine kuruludur.

## Teknik Yığın

### Backend
- **PHP 8.2+** (çalışma ortamı 8.3), **Laravel 12.x**, **MySQL**
- **Laravel Fortify** (kimlik doğrulama + 2FA)
- **Pest 4.x** (test), **Laravel Pint** (formatlama)
- **PayTR** (ödeme gateway), **TRY** (varsayılan para birimi), **%20 KDV**
- PHP 8.2 backed enum'lar tüm durum/tip alanları için kullanılır

### Frontend
- **Vue 3.5** (Composition API)
- **Inertia.js 2.x** (SPA benzeri deneyim, Laravel routing korunur)
- **PrimeVue 4.5** (UI component library, Aura theme, @primevue/forms)
- **Tailwind CSS v4** (@tailwindcss/vite)
- **TypeScript** (tam tip güvenliği)
- **Laravel Wayfinder** (tip güvenli route yardımcıları)
- **ESLint + Prettier** (kod kalitesi ve formatlama)

## İlk Bakış — Kritik Dosyalar

### Konfigürasyon
- `config/herkobi.php` — Platform davranışı: tenant ayarları, abonelik, proration, ödeme, addon, KVKK retention
- `config/paytr.php` — PayTR ödeme gateway ayarları

### Rotalar
- `routes/app.php` — Tenant rotaları. Middleware: `tenant.context`, `subscription.active`, `write.access`, `tenant.allow_team_members`, `feature.access`
- `GET/POST /invitation/accept/{token}` — Public davetiye kabul rotaları (auth sadece POST için)
- `routes/panel.php` — Admin rotaları. Middleware: `auth`, `write.access`
- `routes/console.php` — Zamanlanmış görevler (cron schedule)
- `POST /payment/callback` — PayTR webhook (kimlik doğrulama yok, `routes/app.php` içinde)

### İş Mantığı
- `app/Contracts/**` — Interface tanımları (her servisin bir interface'i vardır)
- `app/Services/**` — İş mantığı (controller'lar ince tutulur, servisler çağrılır)
- `app/Providers/Panel/*ServiceProvider.php` ve `app/Providers/App/*ServiceProvider.php` — Interface → implementation bağlamaları

### Event Sistemi
- `app/Events/**` — Domain event'leri (abonelik, ödeme, addon, kullanıcı işlemleri)
- `app/Listeners/**` — Event handler'ları (aktivite logu, bildirim, işlem tetikleme)
- `app/Listeners/TenantProcessSuccessfulPayment.php` — Ödeme sonrası yönlendirme (kritik listener)

### Ödeme Akışı
- `app/Http/Controllers/App/Account/PaymentCallbackController.php` — PayTR webhook handler
- `app/Services/App/Account/PayTRService.php` — PayTR API entegrasyonu
- `app/Services/App/Account/CheckoutService.php` — Checkout oluşturma ve tutar hesaplama
- `app/Services/App/Account/PaymentService.php` — Ödeme kaydı işlemleri

## Mimari Kurallar ve Konvansiyonlar

### ID Sistemi
- Tüm modeller **ULID** tabanlı birincil anahtarlar kullanır
- Migration'larda: `$table->ulid('id')->primary()`
- Model'lerde: `use HasUlids` trait'i

### Enum Kullanımı
- PHP 8.2 backed enum'lar tüm durum/tip alanları için zorunludur
- Enum değerleri **asla değiştirilmez** — değiştirmek için DB migration + data migration gerekir
- Model cast'larda enum sınıfı kullanılır: `'status' => SubscriptionStatus::class`
- Mevcut enum'lar: `UserType`, `UserStatus`, `TenantUserRole`, `SubscriptionStatus`, `CheckoutType`, `CheckoutStatus`, `PaymentStatus`, `PlanInterval`, `FeatureType`, `AddonType`, `ResetPeriod`, `GracePeriod`, `ProrationType`, `InvitationStatus`

### Servis Katmanı
- Tüm iş mantığı `app/Services/` içindedir. Controller metotları ince tutulur, servis çağırır.
- Her servisin bir interface'i (`app/Contracts/`) vardır
- Bağlamalar `app/Providers/` altındaki service provider'larda yapılır
- Yeni servis eklerken: Interface → Service → ServiceProvider binding sırası takip edilir

### Multi-Tenant İzolasyon
- `BaseTenant` soyut modeli global scope ile tenant izolasyonu sağlar
- `Subscription`, `Payment`, `TenantUsage`, `TenantFeature`, `TenantInvitation` modelleri `BaseTenant`'tan türetilir
- `TenantContextService` aktif tenant'ı yönetir
- `LoadTenantContext` middleware'i tenant context'i yükler
- **Tenant.owner()** bir query helper metodudur (relationship değil), `?User` döndürür

### Config ile Davranış Kontrolü (Hibrit Pattern)
- `herkobi.tenant.allow_team_members` — Team özelliği (UI gizleme + servis seviyesi kontrol)
- `herkobi.tenant.allow_multiple_tenants` — Çoklu tenant oluşturma
- `herkobi.proration.upgrade_behavior` / `downgrade_behavior` — Varsayılan proration davranışı
- `herkobi.addon.auto_renew` — Addon otomatik yenileme
- `herkobi.invitation.expires_days` — Davetiye geçerlilik süresi (varsayılan: 7 gün)
- **Hibrit kontrol**: Config kapalı → özellik tamamen engellenir. Config açık → plan bazlı `feature.access` middleware ile limit/erişim kontrolü yapılır.
- Config ayarları UI'da gizleme yapar ve **servis seviyesinde de enforce edilir** (`TenantContextService.canInviteTeamMember()` hem config hem plan kontrolü yapar)

### Proration Sistemi
- 4 senaryo: Upgrade/Downgrade × IMMEDIATE/END_OF_PERIOD
- Varsayılan davranış `config/herkobi.php`'den gelir
- Plan bazında override: `plans.upgrade_proration_type` ve `plans.downgrade_proration_type` (nullable)
- `Plan.resolveUpgradeProration()` / `resolveDowngradeProration()` DB değerini kontrol eder, yoksa config'e düşer
- `ProrationService.calculate()` gün bazlı kredi hesaplar (IMMEDIATE için) veya tam fiyat döndürür (END_OF_PERIOD için)
- `PlanChangeService` 4 senaryoyu yönetir: anında geçiş, planlanan geçiş, checkout yönlendirmesi

### Ödeme Akışı
- Checkout oluşturulur → PayTR token alınır → Iframe gösterilir → Webhook callback gelir
- `PaymentCallbackController.handle()` webhook'u işler
- `TenantProcessSuccessfulPayment` listener checkout type'a göre yönlendirir:
  - `NEW/RENEW` → `SubscriptionPurchaseService`
  - `UPGRADE` → `PlanChangeService.processUpgrade()`
  - `ADDON/ADDON_RENEW` → `AddonPurchaseService.processCheckout()`
- Webhook rotası kimlik doğrulama gerektirmez; imza doğrulama `PayTRService` içinde yapılır
- PayTR credential'ları `config/paytr.php`'den gelir, kodda asla açığa çıkmamalı

### Addon Sistemi
- Addon'lar `Feature`'a bağlıdır ve plan limitlerini genişletir
- 3 tip: `INCREMENT` (limite ekler), `UNLIMITED` (limiti kaldırır), `BOOLEAN` (özelliği açar)
- `TenantAddon` modeli `Pivot` sınıfından türetilir, global tenant scope vardır
- Recurring addon'ların `expires_at` alanı vardır
- `CheckExpiredAddonsJob` süresi dolanları pasif yapar, `SendAddonExpiryReminderJob` hatırlatma gönderir

## Frontend Mimari (Vue 3 + Inertia)

### Dosya Yapısı
```
resources/
├── views/
│   └── app.blade.php          # Tek Inertia root template
│   └── mail/                  # Email şablonları (Blade kalır)
├── js/
│   ├── app.ts                 # Vue + Inertia bootstrap
│   ├── Pages/                 # Inertia sayfaları (Vue SFC)
│   │   ├── Auth/              # Kimlik doğrulama (7 sayfa)
│   │   ├── App/               # Tenant uygulaması (22 sayfa)
│   │   └── Panel/             # Admin paneli (25+ sayfa)
│   ├── Components/            # Paylaşılan componentler
│   │   ├── Layout/            # AppLayout, PanelLayout, AuthLayout
│   │   └── UI/                # PrimeVue wrapper/custom
│   ├── Composables/           # Vue 3 composables (useAuth, useTenant)
│   ├── types/                 # TypeScript tip tanımları
│   └── wayfinder.ts           # Tip güvenli route() fonksiyonu
└── css/
    └── app.css                # Tailwind + PrimeVue tema
```

### Frontend Konvansiyonlar
- **Composition API**: `<script setup>` syntax kullan (Options API değil)
- **TypeScript**: Tüm Vue componentleri `.vue` dosyalarında TypeScript kullanır
- **Route helpers**: `route('route.name')` (Wayfinder) ile tip güvenli routing
- **Props**: `defineProps<{ ... }>()` ile TypeScript interface tanımları
- **Inertia form**: `useForm()` helper ile form yönetimi ve validation
- **Shared data**: `HandleInertiaRequests` middleware ile global data (`auth`, `tenant`, `flash`)
- **PrimeVue**: Component import'ları doğrudan `primevue/*` yolundan yapılır
- **Styling**: Tailwind utility class'ları + PrimeVue Aura theme

### Controller → Inertia Dönüşümü
```php
// ÖNCE (Blade)
return view('app.dashboard', ['data' => $data]);

// SONRA (Inertia)
return Inertia::render('app/dashboard', ['data' => $data]);
```

### Blade Kullanımı
- **Sadece** email template'leri için Blade kullanılır (`resources/views/mail/`)
- Web sayfaları artık Vue componentleridir (Blade değil)

## Dil ve String'ler

- Kullanıcı arayüzü string'leri genellikle **Türkçe**'dir
- Enum label'ları Türkçe'dir (örneğin: "Aktif", "Süresi Doldu", "Eklenti Satın Alma")
- Validation mesajları Türkçe'dir
- Türkçe dil tutarlılığı korunmalı — lokalizasyon istenmedikçe değiştirmeyin
- Türkçe label veya UX davranışı konusunda emin değilseniz, tahmin etmek yerine soru sorun

## Geliştirici Komutları

```bash
# Kurulum (env, migrate, npm install, build)
composer run setup

# Geliştirme (server + queue + vite eş zamanlı)
composer run dev

# Testler (Pest)
composer test

# Kod formatlama (Pint)
vendor/bin/pint
```

## Yeni Özellik Ekleme Kontrol Listesi

1. Gerekiyorsa `app/Contracts/**` altına yeni interface ekle veya mevcut interface'i güncelle
2. `app/Services/**` altında implement et — controller'lar ince tutulur
3. `app/Providers/` altındaki uygun service provider'da binding yap
4. DB değişikliği gerekiyorsa migration ekle (ULID konvansiyonu, enum varsayılanları)
   - **users** ve **notifications** tabloları için YENİ migration dosyası oluştur
   - Diğer tabloları mevcut migration dosyalarında güncelle
5. Yan etkiler için `app/Events/**` altına event, `app/Listeners/**` altına listener ekle
6. Rotaları güncelle (`routes/app.php` veya `routes/panel.php`) ve middleware uygula
7. Servis seviyesinde config kontrollerinin rota guard'ları ile tutarlı olduğunu doğrula
8. Test yaz (Pest tercih edilir) ve `composer test` çalıştır

## Güvenlik ve Dikkat Notları

- Enum değerlerini DB migration + data migration olmadan **değiştirme**
- Ödeme/güvenlik hassas kodlarında credential'ları açığa çıkarma, `config/paytr.php` kullan
- Webhook rotası (`/payment/callback`) kimlik doğrulama gerektirmez — imza doğrulama servistedir
- Kodlarda **log bırakma** (logging statement'ları production kodunda olmamalı)
- `Tenant.owner()` bir relationship değil, query helper — eager loading ile çalışmaz
- `TenantAddon` bir `Pivot` sınıfıdır — standart model gibi davranmaz
- `BaseTenant`'tan türetilen modellerde global tenant scope otomatik uygulanır — scope'suz sorgu için `withoutTenantScope()` kullan
- Yeni checkout type eklerken `TenantProcessSuccessfulPayment` listener'daki match ifadesini güncelle
- `ProcessScheduledDowngradesJob` hem upgrade hem downgrade işler — yön tespiti fiyat karşılaştırması ile yapılır
- Davetiye token'ları SHA-256 ile hash'lenir (raw token sadece email'de bulunur, DB'de hash saklanır)
- `TenantInvitation` cross-tenant sorgularda `withoutTenantScope()` kullanılmalı (token ile arama, expire job)

## Veritabanı Şema Özeti

### Temel Tablolar
- `users` — Kullanıcılar (ULID, soft delete, 2FA, anonimizasyon)
- `tenants` — Kiracılar (ULID, soft delete, JSON account/data)
- `tenant_user` — Kullanıcı–tenant ilişkisi (pivot, role: owner/staff)
- `plans` — Planlar (ULID, soft delete, proration tipi override'ları)
- `plan_prices` — Plan fiyatları (ULID, soft delete, interval, trial_days)
- `features` — Özellikler (ULID, soft delete, type: limit/feature/metered)
- `plan_features` — Plan–özellik ilişkisi (pivot, value)
- `subscriptions` — Abonelikler (ULID, soft delete, status enum, trial/grace period, next_plan_price_id)
- `checkouts` — Checkout işlemleri (ULID, soft delete, type/status enum, PayTR token)
- `payments` — Ödemeler (ULID, soft delete, tenant/plan_price/addon ilişkileri)
- `addons` — Ek paketler (ULID, soft delete, recurring, feature bağlantısı)
- `tenant_addons` — Tenant–addon ilişkisi (pivot, quantity, expires_at, is_active)
- `tenant_features` — Tenant özellik override'ları
- `tenant_usages` — Metered kullanım sayaçları
- `tenant_invitations` — Davetiyeler (ULID, soft delete, token hash, status enum, expires_at)
- `activities` — Aktivite günlüğü
- `notifications` — Bildirimler (özel model, arşivleme desteği)
- `archived_notifications` — Arşivlenmiş bildirimler
- `settings` — Platform ayarları

### İlişki Haritası
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

## Zamanlanmış Görevler

| Job | Sıklık | Açıklama |
|-----|--------|----------|
| `ExpireOldCheckoutsJob` | ~15 dk | Süresi geçmiş checkout'ları işaretler |
| `CheckExpiredSubscriptionsJob` | Saatlik | Abonelik süresi dolma tespiti |
| `CheckTrialExpiryJob` | Saatlik | Trial süresi dolma tespiti |
| `SendSubscriptionRenewalReminderJob` | Günlük | Yenileme hatırlatması |
| `SendTrialEndingReminderJob` | Günlük | Trial bitiş hatırlatması |
| `CheckExpiredAddonsJob` | Günlük | Addon süresi dolma tespiti |
| `SendAddonExpiryReminderJob` | Günlük | Addon hatırlatması |
| `ProcessScheduledDowngradesJob` | Günlük | Planlanan plan değişikliklerini uygular |
| `ResetMeteredUsageJob` | Günlük | Metered kullanım sıfırlama |
| `ArchiveOldNotificationsJob` | Günlük | Bildirim arşivleme (90 gün) |
| `AnonymizeOldNotificationsJob` | Günlük | Bildirim anonimizasyon (2 yıl) |
| `AnonymizeOldActivitiesJob` | Günlük | Aktivite anonimizasyon (2 yıl) |
| `ExpireOldInvitationsJob` | Günlük | Süresi dolmuş davetiyeleri expire eder |
| `SoftDeleteOldActivitiesJob` | Günlük | Aktivite soft delete |

## Event–Listener Haritalaması

Kritik event → listener ilişkileri:

- `TenantPaymentSucceeded` → `TenantProcessSuccessfulPayment` (checkout type'a göre abonelik/addon işleme)
- `TenantPaymentSucceeded` → `LogTenantPaymentActivity`, `SendTenantPaymentNotifications`
- `TenantSubscriptionPurchased` → `LogTenantSubscriptionActivity`, `SendTenantWelcomeEmail`
- `TenantSubscriptionUpgraded` → `LogTenantSubscriptionActivity`
- `TenantSubscriptionDowngraded` → `LogTenantSubscriptionActivity`
- `TenantSubscriptionExpired` → `LogTenantSubscriptionActivity`, `SendTenantSubscriptionExpiry`
- `TenantAddonPurchased` → `LogTenantAddonActivity`
- `TenantAddonExpired` → `LogTenantAddonActivity`
- `TenantAddonCancelled` → `LogTenantAddonActivity`
- `TenantTrialEnded` → `LogTenantTrialActivity`, `SendTenantTrialEnded`
- `TenantUsageLimitReached` → `LogTenantUsageLimitActivity`, `SendTenantUsageLimitReached`
- `TenantUserInvited` → `SendInvitationEmail`, `LogTenantInvitationActivity`
- `TenantUserDirectlyAdded` → `LogTenantInvitationActivity` (bildirim + aktivite)
- `TenantInvitationAccepted` → `LogTenantInvitationActivity` (bildirim + aktivite)
- `TenantInvitationRevoked` → `LogTenantInvitationActivity`
- Panel event'leri → `LogPanel*Activity` listener'ları + bildirim listener'ları

