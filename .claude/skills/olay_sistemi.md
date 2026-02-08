# Event-Driven Mimari

## Yapı

```
app/Events/       → Domain event sınıfları
app/Listeners/    → Event handler'ları
app/Providers/EventServiceProvider.php → Event-listener mapping
```

## Event İsimlendirme Kuralları

Prefix ile alan belirtilir:
- **`Tenant*`**: Tenant (müşteri) tarafındaki olaylar → `TenantSubscriptionPurchased`, `TenantPaymentSucceeded`
- **`Panel*`**: Admin tarafındaki olaylar → `PanelPlanCreated`, `PanelTenantUpdated`

## Listener Türleri

### 1. Activity Logging (Denetim İzi)
Her önemli işlem `ActivityService` ile loglanır:
```
LogTenantSubscriptionActivity    → Abonelik değişiklikleri
LogTenantPaymentActivity         → Ödeme olayları
LogTenantBillingActivity         → Fatura bilgisi değişiklikleri
LogPanelPlanActivity             → Plan CRUD işlemleri
LogPanelTenantActivity           → Tenant güncelleme
```

### 2. Notification (Bildirim)
E-posta ve uygulama içi bildirimler:
```
SendTenantWelcomeEmail           → Kayıt sonrası
SendTenantPaymentNotifications   → Ödeme başarılı/başarısız
SendTenantSubscriptionExpiry     → Abonelik süresi dolacak
SendTenantUsageLimitReached      → Kullanım limiti aşıldı
SendTenantTrialEnded             → Deneme süresi bitti
```

### 3. Business Logic (İş Mantığı)
Asıl işlemleri tetikleyen listener'lar:
```
TenantProcessSuccessfulPayment   → Başarılı ödemeden sonra abonelik oluştur/yenile/upgrade
  - TYPE_NEW    → SubscriptionPurchaseService.createSubscription()
  - TYPE_RENEW  → SubscriptionPurchaseService.renewSubscription()
  - TYPE_UPGRADE → PlanChangeService.processUpgrade()
```
Bu listener `payments` queue'sunda çalışır (queued).

## Kritik Event Zinciri: Ödeme → Abonelik

```
CheckoutService.processCallback()
  → TenantPaymentSucceeded event
    → TenantProcessSuccessfulPayment listener (queue: payments)
      → Abonelik oluştur/yenile/upgrade
        → TenantSubscriptionPurchased/Renewed/Upgraded event
          → LogTenantSubscriptionActivity
          → SendTenantPaymentNotifications
```

## Yeni Event/Listener Ekleme

1. Event sınıfı oluştur: `app/Events/TenantYeniOlay.php`
   ```php
   class TenantYeniOlay
   {
       use Dispatchable, InteractsWithSockets, SerializesModels;

       public function __construct(
           public readonly Tenant $tenant,
           // ek veriler...
       ) {}
   }
   ```

2. Listener sınıfı oluştur: `app/Listeners/LogTenantYeniOlayActivity.php`
   ```php
   class LogTenantYeniOlayActivity
   {
       public function __construct(
           private ActivityServiceInterface $activityService,
       ) {}

       public function handle(TenantYeniOlay $event): void
       {
           // ...
       }
   }
   ```

3. Event dispatch et (servisten):
   ```php
   event(new TenantYeniOlay($tenant));
   ```

## ActivityService Kullanımı

```php
$this->activityService->log(
    tenant: $tenant,
    user: $user,          // null olabilir (anonim)
    type: 'subscription',
    description: 'Abonelik oluşturuldu',
    data: ['plan' => $plan->name],
);
```

## NotificationService Kullanımı

- `getUserNotifications($user, $perPage)`: Sayfalı bildirimler
- `markAsRead($notification)`: Okundu işaretle
- `markAllAsRead($user)`: Tümünü okundu işaretle
- `getUnreadCount($user)`: Okunmamış sayısı
