# Proje Mimarisi ve Kodlama Kuralları

## Mimari Yapı

Herkobi, Laravel 12 tabanlı SaaS platformudur. İki ana alan vardır:
- **Panel** (`app/Http/Controllers/Panel/`): Admin tarafı — plan, özellik, addon, tenant, ödeme yönetimi
- **App** (`app/Http/Controllers/App/`): Tenant (müşteri) tarafı — abonelik, fatura, profil, ekip

## Service/Contract/Provider Deseni

Tüm iş mantığı servisler üzerinden yürür. Controller'lar ince tutulur.

```
app/Contracts/        → Interface tanımları (Panel/ ve App/ alt klasörleri)
app/Services/         → Interface implementasyonları (Panel/ ve App/ alt klasörleri)
app/Providers/Panel/  → ServiceProvider'lar (interface → implementation binding)
app/Providers/App/    → AccountServiceProvider (tenant servis binding'leri)
```

### Yeni Servis Ekleme Adımları

1. `app/Contracts/` altına interface oluştur (ör. `App/Account/YeniServiceInterface.php`)
2. `app/Services/` altına implementasyon yaz (ör. `App/Account/YeniService.php`)
3. İlgili ServiceProvider'da bind et:
   ```php
   $this->app->bind(YeniServiceInterface::class, YeniService::class);
   ```
4. Controller'da constructor injection ile kullan

### Controller Yapısı

- İnce controller'lar: Validasyonu Form Request'e, mantığı Service'e bırak
- İsimlendirme: PascalCase + `Controller` soneki (ör. `SubscriptionController`)
- Form Request: `app/Http/Requests/` altında, PascalCase + `Request` soneki (ör. `InitiateCheckoutRequest`)

## Model Kuralları

- **ULID**: Tüm modellerde `HasUlids` trait'i, migration'larda `$table->ulid('id')`
- **Enum Casting**: Status alanları enum sınıflarına cast edilir:
  ```php
  protected $casts = ['status' => SubscriptionStatus::class];
  ```
- **Soft Delete**: Çoğu modelde `SoftDeletes` trait'i kullanılır
- **Enum tanımları**: `app/Enums/` altında PHP 8.2 enum'ları (SubscriptionStatus, PaymentStatus, FeatureType, vb.)

## İsimlendirme Kuralları

| Öğe | Kural | Örnek |
|-----|-------|-------|
| Event | PascalCase, prefix ile alan belirt | `TenantSubscriptionPurchased`, `PanelPlanCreated` |
| Listener | Event adı + `Listener` veya açıklayıcı isim | `LogTenantSubscriptionActivity`, `SendTenantPaymentNotifications` |
| Controller | PascalCase + `Controller` | `CheckoutController` |
| Form Request | PascalCase + `Request` | `InitiateCheckoutRequest` |
| Service | PascalCase + `Service` | `CheckoutService` |
| Contract | PascalCase + `ServiceInterface` | `CheckoutServiceInterface` |

## Genel Kurallar

- **Dil**: String'ler Türkçe — localize istenmediği sürece Türkçe koru
- **Config gates**: `config/herkobi.php` ile UI gizleme + servis seviyesinde zorunlu kılma
- **Enum değişikliği**: Enum value'ları değiştirirken mutlaka DB migration + data migration yaz
- **Güvenlik**: `config/paytr.php` credential'larını kod içine yazma, config üzerinden çek
