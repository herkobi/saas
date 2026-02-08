# Abonelik Yaşam Döngüsü

## Model İlişkileri

```
Tenant → hasMany(Subscription) → belongsTo(PlanPrice) → belongsTo(Plan)
Tenant → belongsToMany(Addon)      // Satın alınan addon'lar (pivot: tenant_addons)
```

Plan, Feature ve Addon CRUD detayları için bkz: `plan_ozellik_addon.md`

## Abonelik Durumları (`app/Enums/SubscriptionStatus.php`)

| Durum | Açıklama | Erişim |
|-------|----------|--------|
| `ACTIVE` | Aktif abonelik | Tam erişim |
| `TRIALING` | Deneme süresi | Tam erişim |
| `CANCELED` | İptal edilmiş, grace period aktif | Sınırlı erişim |
| `PAST_DUE` | Ödeme gecikmiş, grace period aktif | Sınırlı erişim |
| `EXPIRED` | Süresi dolmuş | Erişim yok |

`isValid()` metodu: ACTIVE, TRIALING, CANCELED, PAST_DUE → true döner.

## Temel Servisler

### Tenant (App) Tarafı — `app/Contracts/App/Account/`
- **SubscriptionServiceInterface**: Mevcut abonelik bilgisi, plan özellikleri, kalan gün, trial/grace durumu (read-only)
- **SubscriptionPurchaseServiceInterface**: Başarılı ödemeden sonra abonelik oluşturma/yenileme
- **SubscriptionLifecycleServiceInterface**: Zamanlanmış işlemler — süresi dolan abonelikler, trial bitişi, zamanlanmış downgrade'ler
- **PlanChangeServiceInterface**: Upgrade/downgrade hesaplama ve uygulama

### Panel (Admin) Tarafı — `app/Contracts/Panel/`
- **TenantSubscriptionServiceInterface**: Admin abonelik yönetimi (oluştur, iptal, yenile, plan değiştir, trial/grace uzat)

## Plan Değişiklik Akışları

### Upgrade (Anında uygulanır)
1. `PlanChangeService.getAvailableUpgrades()` → uygun planları listele
2. `PlanChangeService.calculateUpgradeProration()` → kalan günün kredisini hesapla
3. `CheckoutService.initiate()` ile type=`upgrade` checkout başlat
4. Ödeme başarılı → `PlanChangeService.processUpgrade()` → subscription güncelle
5. `TenantSubscriptionUpgraded` event dispatch

### Downgrade (Dönem sonunda uygulanır)
1. `PlanChangeService.getAvailableDowngrades()` → uygun planları listele
2. `PlanChangeService.scheduleDowngrade()` → `next_plan_price_id` kaydet
3. Dönem sonunda `SubscriptionLifecycleService.processScheduledDowngrades()` çalışır
4. `TenantSubscriptionDowngraded` event dispatch
5. İptal: `PlanChangeService.cancelScheduledDowngrade()`

## Kullanım Takibi

- **FeatureUsageServiceInterface** (`app/Contracts/App/Account/`): Kullanım artır/azalt/sıfırla, limit kontrolü
- **UsageResetServiceInterface**: Metered özellikleri periyodik sıfırlama (ResetPeriod: DAILY, MONTHLY, YEARLY, NEVER)
- **TenantFeatureServiceInterface** (Panel): Admin tarafından tenant bazlı özellik override'ları
- Kullanım verisi: `tenant_usages` tablosu, `TenantUsage` modeli
