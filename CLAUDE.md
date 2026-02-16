# Herkobi SaaS

Herkobi, Laravel 12 uzerinde kurulu cok kiracili (multi-tenant) bir SaaS platformudur. Iki ana bolge vardir: **Panel** (admin yonetim paneli) ve **App** (tenant/musteri uygulamasi). Sistem; abonelik yonetimi, plan bazli ozellik erisimi, PayTR odeme entegrasyonu, addon sistemi, proration hesaplama, KVKK/GDPR uyumlu veri saklama ve event-driven mimari uzerine kuruludur.

---

## 1. Teknik Yigin

| Katman | Teknolojiler |
|--------|-------------|
| Backend | PHP 8.4+, Laravel 12, MySQL, Laravel Fortify (2FA), Pest 4, Pint |
| Frontend | Vue 3.5 (`<script setup>`), Inertia.js 2, shadcn-vue (new-york-v4 + Reka UI), Tailwind CSS v4, TypeScript, Lucide Vue Next |
| Araclar | Laravel Wayfinder (tip guvenli route), TanStack Vue Table, VueUse, Vue Sonner |
| Odeme | PayTR gateway, TRY para birimi, %20 KDV |

> Paket versiyonlarinin tam listesi icin asagidaki **Boost Kurallari > Temel Bagalam** bolumune bakin.

---

## 2. Proje Yapisi

### Backend

```
app/
├── Contracts/          # 37 interface (her servisin bir interface'i var)
├── Services/           # 37 servis (App/, Panel/, Shared/ altinda)
├── Http/
│   ├── Controllers/    # App/ ve Panel/ altinda izole controller'lar
│   ├── Middleware/      # 12 ozel middleware
│   └── Requests/       # Form Request siniflari
├── Models/             # 18 Eloquent model (ULID, soft delete)
├── Enums/              # 14 backed enum
├── Events/             # Domain event'leri
├── Listeners/          # Event handler'lari
├── Jobs/               # 15 zamanlanmis gorev
├── Providers/          # App/ ve Panel/ altinda service provider'lar
└── Notifications/      # Bildirim siniflari

config/herkobi.php      # Platform davranisi (tenant, abonelik, proration, odeme, addon, KVKK)
config/paytr.php        # PayTR gateway ayarlari
routes/app.php          # Tenant rotalari (middleware: tenant.context, subscription.active, write.access, feature.access)
routes/panel.php        # Admin rotalari (middleware: auth, write.access)
routes/console.php      # Zamanlanmis gorevler
```

### Frontend

```
resources/js/
├── pages/
│   ├── app/            # Tenant sayfalari (Dashboard, profile/*)
│   ├── panel/          # Admin sayfalari (Dashboard, profile/*)
│   └── auth/           # Paylasilan auth sayfalari (Login, Register, vb.)
├── components/
│   ├── app/            # 17 app-ozel component (AppShell, AppSidebar, NavMain, NavUser, TeamSwitcher, vb.)
│   ├── panel/          # 17 panel-ozel component (ayni yapida izole)
│   ├── ui/             # 57 shadcn-vue component (accordion, badge, button, card, dialog, table, vb.)
│   └── common/         # 7 paylasilan component (AlertError, AppLogo, Breadcrumbs, InputError, vb.)
├── layouts/
│   ├── AppLayout.vue / PanelLayout.vue / AuthLayout.vue
│   ├── app/            # AppSidebarLayout, AppHeaderLayout
│   ├── panel/          # AppSidebarLayout, AppHeaderLayout
│   └── auth/           # AuthSimpleLayout, AuthCardLayout, AuthSplitLayout
├── composables/        # 10 composable (useAppearance, useFormatting, useTwoFactorAuth, useUserStatus, vb.)
├── types/              # 11 TypeScript tip dosyasi (auth, tenant, billing, common, panel, site, navigation, ui, vb.)
├── lib/utils.ts        # cn() ve yardimci fonksiyonlar
├── actions/            # Wayfinder controller route fonksiyonlari (otomatik uretilir)
└── routes/             # Wayfinder named route fonksiyonlari (otomatik uretilir)
```

### Gelistirme Durumu

**Hazir sayfalar:**
- Auth: Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword, TwoFactorChallenge
- App: Dashboard, Profile (Profile, Password, TwoFactor, Appearance)
- Panel: Dashboard, Profile (Profile, Password, TwoFactor, Appearance)

**Frontend'i yapilmamis backend islemleri:**
- Plan, Fiyat, Ozellik, Addon yonetimi (Panel CRUD)
- Tenant yonetimi ve detay (Panel)
- Abonelik islemleri (Panel + App)
- Odeme listeleri ve checkout akisi (Panel + App)
- Kullanici/davetiye yonetimi (App)
- Bildirimler, aktiviteler, ayarlar

---

## 3. Mimari Kurallar

### Servis Katmani
- Tum is mantigi `app/Services/` icinde. Controller'lar ince tutulur, servis cagirir
- Her servisin bir interface'i (`app/Contracts/`) vardir
- Baglamalar `app/Providers/` altindaki service provider'larda yapilir
- Yeni servis: Interface → Service → ServiceProvider binding sirasi

### ID ve Enum
- Tum modeller **ULID** tabanli: migration'da `$table->ulid('id')->primary()`, model'de `use HasUlids`
- PHP 8.2 backed enum'lar zorunlu. Enum degerleri **asla degistirilmez** (DB migration + data migration gerekir)
- Mevcut enum'lar: `UserType`, `UserStatus`, `TenantUserRole`, `SubscriptionStatus`, `CheckoutType`, `CheckoutStatus`, `PaymentStatus`, `PlanInterval`, `FeatureType`, `AddonType`, `ResetPeriod`, `GracePeriod`, `ProrationType`, `InvitationStatus`

### Multi-Tenant Izolasyon
- `BaseTenant` soyut modeli global scope ile tenant izolasyonu saglar
- `Subscription`, `Payment`, `TenantUsage`, `TenantFeature`, `TenantInvitation` → `BaseTenant`'tan turetilir
- `TenantContextService` aktif tenant'i yonetir, `LoadTenantContext` middleware yukler
- **Tenant.owner()** bir query helper'dir (relationship degil), `?User` dondurur
- Scope'suz sorgu icin `withoutTenantScope()` kullan

### Middleware'ler

| Middleware | Gorevi |
|-----------|--------|
| `HandleInertiaRequests` | Shared data: auth, tenant, site |
| `LoadTenantContext` | Tenant context yukleme |
| `EnsureActiveSubscription` | Aktif abonelik kontrolu |
| `EnsureActiveUser` | Aktif kullanici kontrolu |
| `EnsureFeatureAccess` | Ozellik erisim kontrolu |
| `EnsureTenant` | Tenant zorunlulugu |
| `EnsureTenantOwner` | Tenant sahibi kontrolu |
| `EnsureTenantAllowsTeamMembers` | Takim uyesi izni |
| `EnsureTenantMemberActive` | Aktif uye kontrolu |
| `EnsureWriteAccess` | Yazma erisimi (status != taslak) |
| `EnsurePanel` | Panel erisim kontrolu |
| `HandleAppearance` | Tema tercihi |

### Config ile Davranis Kontrolu (Hibrit Pattern)
- `herkobi.tenant.allow_team_members` — Team ozelligi (UI + servis seviyesi)
- `herkobi.tenant.allow_multiple_tenants` — Coklu tenant
- `herkobi.proration.upgrade_behavior` / `downgrade_behavior` — Varsayilan proration
- `herkobi.addon.auto_renew` — Addon otomatik yenileme
- `herkobi.invitation.expires_days` — Davetiye suresi (varsayilan: 7 gun)
- **Hibrit kontrol**: Config kapali → tamamen engel. Config acik → plan bazli `feature.access` middleware ile kontrol

### Frontend Konvansiyonlar
- **Dual interface**: App sayfalari → `AppLayout`, Panel → `PanelLayout`, Auth → `AuthLayout`
- **Yeni sayfa**: `pages/app/` veya `pages/panel/` altina
- **Yeni component**: Bolume ozelse `components/app/` veya `components/panel/`, paylasilacaksa `components/common/`
- **Shared data**: `HandleInertiaRequests` ile global data (`auth`, `tenant`, `site`)
- **Icon**: Lucide Vue Next (`Ban`, `CheckCircle`, `CreditCard`, `Eye`, `Info`, `Users`, vb.)
- **Renk tokenleri**: shadcn CSS degiskenleri (`bg-muted`, `text-muted-foreground`, `bg-green-100 dark:bg-green-900/30`, vb.)
- **Blade**: Sadece email template'leri icin (`resources/views/mail/`)

---

## 4. Is Mantigi

### Odeme Akisi
1. Checkout olusturulur → PayTR token alinir → Iframe gosterilir → Webhook callback gelir
2. `TenantProcessSuccessfulPayment` listener checkout type'a gore yonlendirir:
   - `NEW/RENEW` → `SubscriptionPurchaseService`
   - `UPGRADE` → `PlanChangeService.processUpgrade()`
   - `ADDON/ADDON_RENEW` → `AddonPurchaseService.processCheckout()`
3. Webhook rotasi (`POST /payment/callback`) kimlik dogrulama gerektirmez; imza dogrulama `PayTRService` icinde

### Proration Sistemi
- 4 senaryo: Upgrade/Downgrade × IMMEDIATE/END_OF_PERIOD
- Varsayilan `config/herkobi.php`'den, plan bazinda override: `plans.upgrade_proration_type` / `downgrade_proration_type`
- `ProrationService.calculate()` gun bazli kredi (IMMEDIATE) veya tam fiyat (END_OF_PERIOD)
- `PlanChangeService` 4 senaryoyu yonetir

### Addon Sistemi
- Addon'lar `Feature`'a baglidir, plan limitlerini genisletir
- 3 tip: `INCREMENT` (limite ekler), `UNLIMITED` (limiti kaldirir), `BOOLEAN` (ozelligi acar)
- `TenantAddon` modeli `Pivot` sinifindan turetilir, global tenant scope vardir
- Recurring addon'larin `expires_at` alani vardir

---

## 5. Veritabani

### Temel Tablolar
- `users` — ULID, soft delete, 2FA, anonimizasyon
- `tenants` — ULID, soft delete, JSON account/data
- `tenant_user` — Pivot (role: owner/staff, status)
- `plans` / `plan_prices` / `features` / `plan_features` — Plan ve ozellik yapisi
- `subscriptions` — Status enum, trial/grace period, next_plan_price_id
- `checkouts` — Type/status enum, PayTR token
- `payments` — Tenant, plan_price, addon iliskileri
- `addons` / `tenant_addons` — Addon ve pivot (quantity, expires_at, is_active)
- `tenant_features` / `tenant_usages` — Override ve metered sayaclar
- `tenant_invitations` — Token hash, status enum, expires_at
- `activities` / `notifications` / `archived_notifications` / `settings`

### Iliski Haritasi
```
User ──M:M──▸ Tenant (tenant_user pivot)
Tenant ──1:M──▸ Subscription, Payment, TenantFeature, TenantUsage, TenantInvitation
Tenant ──M:M──▸ Addon (tenant_addons pivot)
Plan ──1:M──▸ PlanPrice
Plan ──M:M──▸ Feature (plan_features pivot)
PlanPrice ──1:M──▸ Subscription
Feature ──1:M──▸ Addon
Checkout ──▸ Tenant, PlanPrice, Addon, Payment
```

---

## 6. Arka Plan Islemleri

### Zamanlanmis Gorevler

| Siklik | Gorevler |
|--------|----------|
| ~15 dk | `ExpireOldCheckoutsJob` |
| Saatlik | `CheckExpiredSubscriptionsJob`, `CheckTrialExpiryJob` |
| Gunluk | `ProcessScheduledDowngradesJob`, `ResetMeteredUsageJob`, `CheckExpiredAddonsJob`, `ExpireOldInvitationsJob` |
| Gunluk (bildirim) | `SendSubscriptionRenewalReminderJob`, `SendTrialEndingReminderJob`, `SendAddonExpiryReminderJob` |
| Gunluk (temizlik) | `ArchiveOldNotificationsJob` (90 gun), `AnonymizeOldNotificationsJob` (2 yil), `AnonymizeOldActivitiesJob` (2 yil), `SoftDeleteOldActivitiesJob` |

### Event–Listener Haritalamasi
- `TenantPaymentSucceeded` → `TenantProcessSuccessfulPayment`, `LogTenantPaymentActivity`, `SendTenantPaymentNotifications`
- `TenantSubscriptionPurchased` → `LogTenantSubscriptionActivity`, `SendTenantWelcomeEmail`
- `TenantSubscriptionUpgraded/Downgraded/Expired` → `LogTenantSubscriptionActivity` (+ Expired: `SendTenantSubscriptionExpiry`)
- `TenantAddonPurchased/Expired/Cancelled` → `LogTenantAddonActivity`
- `TenantTrialEnded` → `LogTenantTrialActivity`, `SendTenantTrialEnded`
- `TenantUsageLimitReached` → `LogTenantUsageLimitActivity`, `SendTenantUsageLimitReached`
- `TenantUserInvited` → `SendInvitationEmail`, `LogTenantInvitationActivity`
- Panel event'leri → `LogPanel*Activity` + bildirim listener'lari

---

## 7. Guvenlik ve Dikkat Notlari

- Enum degerlerini DB migration + data migration olmadan **degistirme**
- Odeme credential'lari `config/paytr.php` uzerinden, kodda aciga cikarma
- Webhook rotasi auth gerektirmez — imza dogrulama servistedir
- Kodlarda **log birakma** (production'da olmamali)
- `TenantAddon` bir `Pivot` sinifi — standart model gibi davranmaz
- `BaseTenant` scope'suz sorgu icin `withoutTenantScope()` kullan
- Yeni checkout type → `TenantProcessSuccessfulPayment` listener'daki match'i guncelle
- `ProcessScheduledDowngradesJob` hem upgrade hem downgrade isler (fiyat karsilastirmasi ile yon tespiti)
- Davetiye token'lari SHA-256 ile hash'lenir (raw token sadece email'de)
- `TenantInvitation` cross-tenant sorgularda `withoutTenantScope()` gerekir

---

## 8. Yeni Ozellik Ekleme Kontrol Listesi

1. Interface (`app/Contracts/`) olustur veya guncelle
2. Servis (`app/Services/`) implement et
3. Service provider'da binding yap
4. Migration ekle (ULID, enum varsayilanlari). `users`/`notifications` icin yeni dosya, digerleri mevcut dosyada
5. Event + listener ekle (yan etkiler icin)
6. Rotalari guncelle ve middleware uygula
7. Config kontrollerinin rota guard'lari ile tutarli oldugunu dogrula
8. Frontend sayfa/component olustur (shadcn-vue, Lucide, Tailwind)
9. Test yaz (Pest) ve `composer test` calistir

---

## 9. Dil

- UI string'leri **Turkce** (enum label, validation, UX metinleri)
- Turkce tutarliligi korunmali — lokalizasyon istenmedikce degistirmeyin
- Emin degilseniz tahmin yerine soru sorun

---

## 10. Gelistirici Komutlari

```bash
composer run dev      # Gelistirme (server + queue + vite es zamanli)
composer test         # Testler (Pest)
vendor/bin/pint       # Kod formatlama
npm run build         # Frontend build
```

---

<!-- Asagidaki bolum Laravel Boost MCP tarafindan yonetilen kod yazma kurallaridir. -->
<!-- Proje-ozel bilgiler yukaridadir, genel gelistirme kurallari asagidadir. -->

<laravel-boost-guidelines>
=== temel kurallar ===

# Laravel Boost Kurallari

Laravel Boost kurallari bu uygulama icin ozel olarak Laravel gelistiricileri tarafindan hazirlanmistir. En iyi deneyimi saglamak icin bu kurallara uyulmalidir.

## Temel Bagalam

Bu uygulama bir Laravel uygulamasidir. Ana Laravel ekosistemi paket ve versiyonlari asagidadir. Tum bu paket ve versiyonlarda uzmansiniz. Bu spesifik paket ve versiyonlara uygun kod yazin.

- php - 8.4.16
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12

## Yetenek Aktivasyonu

Bu projede alan-ozel yetenekler mevcuttur. Ilgili alanda calisirken ilgili yetenegi MUTLAKA aktive edin — takilana kadar beklemeyin.

- `wayfinder-development` — Frontend component'lerinde backend rotalarina referans verildiginde aktive olur. `@/actions` veya `@/routes`'dan import ederken, TypeScript'ten Laravel rotalari cagirirken veya Wayfinder route fonksiyonlariyla calisirken kullanin.
- `pest-testing` — Pest 4 PHP framework'u ile test yazmak icin. Test yazarken, unit veya feature test olustururken, assertion eklerken, test hatalarini debuglarken, dataset veya mock ile calisirken; veya kullanici test, spec, TDD, expects, assertion, coverage gibi terimlerden bahsettiginde aktive olur.

## Konvansiyonlar

- Uygulamadaki mevcut tum kod konvansiyonlarini takip edin. Dosya olusturur veya duzenlerken, dogru yapi, yaklasim ve isimlendirme icin komsu dosyalari kontrol edin.
- Degiskenler ve metotlar icin aciklayici isimler kullanin. Ornegin: `isRegisteredForDiscounts`, `discount()` degil.
- Yeni component yazmadan once mevcut component'leri kontrol edin.

## Dogrulama Scriptleri

- Testler islevseligi kanitliyorsa dogrulama scripti veya tinker olusturmayin. Unit ve feature testleri daha onemlidir.

## Uygulama Yapisi ve Mimari

- Mevcut dizin yapisina bagli kalin; onay almadan yeni ana klasor olusturmayin.
- Onay almadan uygulamanin bagimlilik yapisini degistirmeyin.

## Frontend Paketleme

- Kullanici bir frontend degisikligini UI'da goremiyorsa, `npm run build`, `npm run dev` veya `composer run dev` calistirmalari gerekebilir. Sorun.

## Dokumantasyon Dosyalari

- Dokumantasyon dosyalari yalnizca kullanici tarafindan acikca istendiginde olusturulmalidir.

## Yanitlar

- Aciklamalarinizda kisa ve oz olun — bariz detaylari aciklamak yerine onemliye odaklanin.

=== boost kurallari ===

# Laravel Boost

- Laravel Boost bu uygulama icin ozel olarak tasarlanmis guclu araclarla birlikte gelen bir MCP sunucusudur. Bunlari kullanin.

## Artisan

- Artisan komutu calistirmaniz gerektiginde mevcut parametreleri dogrulamak icin `list-artisan-commands` aracini kullanin.

## URL'ler

- Kullaniciyla proje URL'si paylasirken dogru scheme, domain/IP ve port kullandiginizdan emin olmak icin `get-absolute-url` aracini kullanin.

## Tinker / Hata Ayiklama

- PHP kodu calistirarak hata ayiklamak veya Eloquent modellerini dogrudan sorgulamak icin `tinker` aracini kullanin.
- Yalnizca veritabanindan okuma yapmaniz gerektiginde `database-query` aracini kullanin.

## Tarayici Loglarini `browser-logs` Araci ile Okuma

- Boost'un `browser-logs` aracini kullanarak tarayici loglarini, hatalarini ve istisnalarini okuyabilirsiniz.
- Yalnizca guncel tarayici loglari faydali olacaktir — eski loglari goz ardi edin.

## Dokumantasyon Arama (Kritik Onem)

- Boost, Laravel veya Laravel ekosistemi paketleriyle calisirken diger yaklasimlardan once kullanmaniz gereken guclu bir `search-docs` aracina sahiptir. Bu arac, yuklenmis paketlerin listesini ve versiyonlarini otomatik olarak Boost API'sine gonderir, bu sayede yalnizca versiyona ozel dokumantasyon doner. Belirli paketler icin docs gerektiyorsa paket dizisi gondermelisiniz.
- Kod degisikligi yapmadan once dogru yaklasimi saglamak icin dokumantasyonu arayin.
- Birden fazla, genis, basit, konu bazli sorgulamalari ayni anda kullanin. Ornegin: `['rate limiting', 'routing rate limiting', 'routing']`. En ilgili sonuclar ilk sirada doner.
- Sorgulara paket isimleri eklemeyin; paket bilgisi zaten paylasiliyor. Ornegin: `test resource table` kullanin, `filament 4 test resource table` degil.

### Mevcut Arama Sozdizimi

1. Basit Kelime Aramalari (otomatik kok bulma) - query=authentication - 'authenticate' ve 'auth' bulur.
2. Birden Fazla Kelime (VE Mantigi) - query=rate limit - hem "rate" HEM "limit" iceren bilgiyi bulur.
3. Tirnak Icinde Ifadeler (Tam Konum) - query="infinite scroll" - kelimeler yan yana ve bu sirada olmalidir.
4. Karisik Sorgular - query=middleware "rate limit" - "middleware" VE tam ifade "rate limit".
5. Birden Fazla Sorgu - queries=["authentication", "middleware"] - bu terimlerden HERHANGI biri.

=== php kurallari ===

# PHP

- Tek satirlik govdeler icin bile kontrol yapilarinda her zaman suslu parantez kullanin.

## Constructor'lar

- `__construct()` icinde PHP 8 constructor property promotion kullanin.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Constructor private degilse sifir parametreli bos `__construct()` metotlarina izin vermeyin.

## Tip Bildirimleri

- Metotlar ve fonksiyonlar icin her zaman acik donus tipi bildirimi kullanin.
- Metot parametreleri icin uygun PHP tip ipuclari kullanin.

<code-snippet name="Acik Donus Tipleri ve Metot Parametreleri" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enum'lar

- Enum anahtarlari TitleCase olmalidir. Ornegin: `FavoritePerson`, `BestLake`, `Monthly`.

## Yorumlar

- Satir ici yorumlar yerine PHPDoc bloklari tercih edin. Mantik asiri karmasik degilse kodun icine yorum yazmayin.

## PHPDoc Bloklari

- Uygun oldugunda faydali dizi sekil tip tanimlari ekleyin.

=== herd kurallari ===

# Laravel Herd

- Uygulama Laravel Herd tarafindan sunulur ve su adreste erisilebilir: `https?://[kebab-case-proje-dizini].test`. Kullanici icin gecerli URL olusturmak icin `get-absolute-url` aracini kullanin.
- Siteyi HTTP(S) uzerinden erisilebilir kilmak icin herhangi bir komut calistirmayin. Laravel Herd uzerinden her zaman erisilebilirdir.

=== test kurallari ===

# Test Zorunlulugu

- Her degisiklik programatik olarak test edilmelidir. Yeni bir test yazin veya mevcut testi guncelleyin, ardinda etkilenen testleri calistirarak gectiginden emin olun.
- Kod kalitesi ve hiz icin gereken minimum sayida test calistirin. Belirli dosya adi veya filtre ile `php artisan test --compact` kullanin.

=== inertia-laravel/temel kurallar ===

# Inertia

- Inertia, modern SPA karmasikligi olmadan tamamen istemci tarafinda render edilen SPA'lar olusturur, mevcut sunucu tarafi kaliplari kullanir.
- Component'ler `resources/js/Pages` icinde bulunur (`vite.config.js`'de belirtilmedikce). Blade view'lari yerine `Inertia::render()` ile sunucu tarafi routing kullanin.
- Versiyona ozel Inertia dokumantasyonu ve guncel kod ornekleri icin her zaman `search-docs` aracini kullanin.

=== inertia-laravel/v2 kurallar ===

# Inertia v2

- v1 ve v2'nin tum Inertia ozelliklerini kullanin. Degisiklik yapmadan once dogru yaklasimdan emin olmak icin dokumantasyonu kontrol edin.
- Yeni ozellikler: deferred props, infinite scrolling (merging props + `WhenVisible`), lazy loading on scroll, polling, prefetching.
- Deferred props kullanirken, nabiz atan veya animasyonlu skeleton ile bos durum ekleyin.

=== laravel/temel kurallar ===

# Laravel Yontemiyle Yap

- Yeni dosyalar olusturmak icin `php artisan make:` komutlarini kullanin (migration, controller, model, vb.). Mevcut Artisan komutlarini `list-artisan-commands` araci ile listeleyebilirsiniz.
- Genel bir PHP sinifi olusturuyorsaniz `php artisan make:class` kullanin.
- Kullanici girisi olmadan calistiklarindan emin olmak icin tum Artisan komutlarina `--no-interaction` gecin. Dogru davranis icin dogru `--options`'lari da gecin.

## Veritabani

- Her zaman donus tipi ipuclari ile uygun Eloquent iliski metotlari kullanin. Ham sorgular veya manuel join'ler yerine iliski metotlarini tercih edin.
- Ham veritabani sorgulari onermeden once Eloquent modelleri ve iliskilerini kullanin.
- `DB::` kullanmaktan kacinin; `Model::query()` tercih edin. Laravel'in ORM yeteneklerini kullanan kod uretin.
- Eager loading kullanarak N+1 sorgu sorunlarini onleyen kod uretin.
- Cok karmasik veritabani islemleri icin Laravel'in query builder'ini kullanin.

### Model Olusturma

- Yeni model olustururken onlar icin faydali factory ve seeder'lar da olusturun. Kullaniciya baska seyler gerekip gerekmediklerini sorun, `list-artisan-commands` ile `php artisan make:model` mevcut seceneklerini kontrol edin.

### API'ler ve Eloquent Resources

- API'ler icin, mevcut API rotalari kullanmiyorsa varsayilan olarak Eloquent API Resources ve API versiyonlama kullanin, aksi takdirde mevcut uygulama konvansiyonunu takip edin.

## Controller'lar ve Validasyon

- Validasyon icin controller icinde inline validasyon yerine her zaman Form Request siniflari olusturun. Hem validasyon kurallarini hem de ozel hata mesajlarini ekleyin.
- Uygulamanin dizi mi yoksa string bazli validasyon kurallari mi kullandigini gormek icin komsu Form Request'leri kontrol edin.

## Kimlik Dogrulama ve Yetkilendirme

- Laravel'in yerlesik kimlik dogrulama ve yetkilendirme ozelliklerini kullanin (gate'ler, policy'ler, Sanctum, vb.).

## URL Olusturma

- Diger sayfalara link olustururken isimli rotalari ve `route()` fonksiyonunu tercih edin.

## Kuyruklar

- Zaman alan islemler icin `ShouldQueue` arayuzu ile kuyruga alinmis job'lar kullanin.

## Konfigurasyon

- Ortam degiskenlerini yalnizca konfigurasyon dosyalarinda kullanin — `env()` fonksiyonunu config dosyalari disinda asla dogrudan kullanmayin. Her zaman `config('app.name')` kullanin, `env('APP_NAME')` degil.

## Test

- Testlerde model olustururken model factory'lerini kullanin. Manuel kurulum yapmadan once factory'nin kullanilabilir ozel durumlarini (state) kontrol edin.
- Faker: `$this->faker->word()` veya `fake()->randomDigit()` gibi metotlar kullanin. `$this->faker` mi yoksa `fake()` mi kullanildigina dair mevcut konvansiyonlari takip edin.
- Test olustururken `php artisan make:test [options] {name}` kullanin (feature test icin), unit test icin `--unit` gecin. Cogu test feature test olmalidir.

## Vite Hatasi

- "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" hatasi aldiginizda `npm run build` calistirabilir veya kullanicidan `npm run dev` ya da `composer run dev` calistirmasini isteyebilirsiniz.

=== laravel/v12 kurallar ===

# Laravel 12

- KRITIK: Versiyona ozel Laravel dokumantasyonu ve guncel kod ornekleri icin her zaman `search-docs` aracini kullanin.
- Laravel 11'den beri Laravel yeni basitlestirilmis dosya yapisini kullanir ve bu proje de onu kullanir.

## Laravel 12 Yapisi

- Laravel 12'de middleware'ler artik `app/Http/Kernel.php`'de kayit edilmez.
- Middleware'ler `bootstrap/app.php` icinde `Application::configure()->withMiddleware()` ile deklaratif olarak yapilandirilir.
- `bootstrap/app.php` middleware, istisna ve routing dosyalarinin kayit edildigi dosyadir.
- `bootstrap/providers.php` uygulamaya ozel service provider'lari icerir.
- `app\Console\Kernel.php` dosyasi artik yoktur; konsol yapilandirmasi icin `bootstrap/app.php` veya `routes/console.php` kullanin.
- `app/Console/Commands/` icindeki konsol komutlari otomatik olarak kullanilabilir ve manuel kayit gerektirmez.

## Veritabani

- Bir sutunu degistirirken, migration onceden tanimlanmis tum nitelikleri icermelidir. Aksi takdirde silinir ve kaybolur.
- Laravel 12, eager loading'de kayitlari harici paket olmadan dogal olarak sinirlandirmayi destekler: `$query->latest()->limit(10);`.

### Modeller

- Cast'lar `$casts` ozeligi yerine modeldeki `casts()` metodu ile ayarlanmali. Diger modellerden mevcut konvansiyonlari takip edin.

=== wayfinder/temel kurallar ===

# Laravel Wayfinder

Wayfinder, Laravel rotalari icin TypeScript fonksiyonlari uretir. `@/actions/` (controller'lar) veya `@/routes/` (isimli rotalar) uzerinden import edin.

- ONEMLI: Frontend component'lerinde backend rotalarina referans verildiginde `wayfinder-development` yetenegini aktive edin.
- Invokable Controller'lar: `import StorePost from '@/actions/.../StorePostController'; StorePost()`.
- Parametre Baglama: Rota anahtarlarini (`{post:slug}`) algilar — `show({ slug: "my-post" })`.
- Sorgu Birlestirme: `show(1, { mergeQuery: { page: 2, sort: null } })` mevcut URL ile birlestirir, `null` parametreyi siler.
- Inertia: `<Form>` component'i ile `.form()` veya useForm ile `form.submit(store())` kullanin.

=== pint/temel kurallar ===

# Laravel Pint Kod Formatlayici

- Degisiklikleri sonlandirmadan once kodunuzun projenin beklenen stiline uygun oldugundan emin olmak icin `vendor/bin/pint --dirty` calistirmaniz gerekir.
- `vendor/bin/pint --test` calistirmayin, formatlama sorunlarini duzeltemek icin basitce `vendor/bin/pint` calistirin.

=== pest/temel kurallar ===

## Pest

- Bu proje test icin Pest kullanir. Test olusturma: `php artisan make:test --pest {name}`.
- Test calistirma: `php artisan test --compact` veya filtre: `php artisan test --compact --filter=testName`.
- Onay almadan testleri silmeyin.
- KRITIK: Versiyona ozel Pest dokumantasyonu ve guncel kod ornekleri icin her zaman `search-docs` aracini kullanin.
- ONEMLI: Pest veya test ile ilgili bir gorevde calistiginizda her zaman `pest-testing` yetenegini aktive edin.
</laravel-boost-guidelines>
