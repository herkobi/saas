# Geliştirme, Frontend ve Test

## Geliştirme Komutları

```bash
composer run setup    # Ortam kurulumu: env, migrate, npm install, build
composer run dev      # Eşzamanlı: server + queue listener + Vite
composer test         # Pest testleri çalıştır (config cache temizler)
vendor/bin/pint       # Kod formatlama (Laravel Pint)
```

## Frontend Stack

- **Vue 3.5** (Composition API + TypeScript)
- **Inertia.js 2.x**: SPA benzeri deneyim, Laravel routing korunur
- **PrimeVue 4.5**: UI component library (Aura theme, @primevue/forms)
- **Tailwind CSS v4**: Utility-first CSS framework (@tailwindcss/vite)
- **TypeScript**: Tam tip güvenliği
- **Laravel Wayfinder**: Tip güvenli `route()` yardımcıları
- **ESLint + Prettier**: Kod kalitesi ve formatlama
- **Vite**: Build tool ve HMR

### Inertia Sayfa Yapısı

```
resources/js/
├── app.ts                 # Vue + Inertia bootstrap
├── Pages/                 # Inertia sayfaları (Vue SFC)
│   ├── Auth/              # Kimlik doğrulama (7 sayfa)
│   ├── App/               # Tenant uygulaması (22 sayfa)
│   └── Panel/             # Admin paneli (25+ sayfa)
├── Components/            # Paylaşılan componentler
│   ├── Layout/            # AppLayout, PanelLayout, AuthLayout
│   └── UI/                # PrimeVue wrapper/custom
├── Composables/           # Vue 3 composables (useAuth, useTenant)
├── types/                 # TypeScript tip tanımları
└── wayfinder.ts           # Laravel Wayfinder route yardımcıları

resources/css/
└── app.css                # Tailwind CSS + PrimeVue tema

resources/views/
├── app.blade.php          # Tek Inertia root template
└── mail/                  # Email şablonları (Blade kalır)
```

### Frontend Konvansiyonlar

- **Composition API**: `<script setup>` syntax kullan (Options API değil)
- **TypeScript**: Tüm Vue componentleri TypeScript kullanır
- **Route helpers**: `route('route.name')` (Wayfinder) ile tip güvenli routing
- **Props**: `defineProps<{ ... }>()` ile TypeScript interface tanımları
- **Inertia form**: `useForm()` helper ile form yönetimi ve validation
- **Shared data**: `HandleInertiaRequests` middleware ile global data (`auth`, `tenant`, `flash`)
- **PrimeVue**: Component import'ları doğrudan `primevue/*` yolundan yapılır
- **Styling**: Tailwind utility class'ları + PrimeVue Aura theme
- **Blade**: Sadece email template'leri için kullanılır (`resources/views/mail/`)

## Test Yazma

- **Framework**: Pest (v4.2)
- **Dizin**: `tests/Feature/` ve `tests/Unit/`
- **Factory'ler**: `database/factories/` — User, Tenant, Plan, PlanPrice

### Pest Syntax

```php
it('creates a subscription', function () {
    $tenant = Tenant::factory()->create();
    $planPrice = PlanPrice::factory()->create();

    // test logic...

    expect($tenant->subscriptions)->toHaveCount(1);
});
```

### Test Çalıştırma

```bash
composer test                    # Tüm testler
php artisan test --filter=...    # Belirli test
```

## Yeni Özellik Ekleme Checklist

1. Contract ekle: `app/Contracts/` altına interface
2. Service ekle: `app/Services/` altına implementasyon
3. Provider'da bind et: `app/Providers/` altındaki ilgili ServiceProvider
4. DB gerekiyorsa: Migration yaz (ULID, enum default'ları)
5. Event/Listener gerekiyorsa: `app/Events/` + `app/Listeners/`
6. Route ekle: `routes/app.php` veya `routes/panel.php`, middleware uygula
7. Controller + Form Request yaz
8. Vue component oluştur: `resources/js/Pages/` altında Inertia sayfası
9. Test yaz (Pest) ve çalıştır: `composer test`
10. Formatla: `vendor/bin/pint`
