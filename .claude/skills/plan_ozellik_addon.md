# Plan, Özellik ve Addon Yönetimi

## Genel Yapı

Admin panelinden yönetilen ürün kataloğu. Hiyerarşi:
```
Plan → hasMany(PlanPrice)              // Fiyat varyantları
Plan → belongsToMany(Feature)          // Plan özellikleri (pivot: plan_features, value alanı)
Feature → hasMany(Addon)               // Özelliğe bağlı ek ürünler
```

## Plan Yönetimi

### Model (`app/Models/Plan.php`)
- Alanlar: `slug`, `name`, `description`, `tenant_id`, `is_free`, `is_active`, `is_public`, `archived_at`, `grace_period_days`, `grace_period_policy`
- Cast: `grace_period_policy` → `GracePeriod` enum
- Scope'lar: `global()`, `forTenant()`, `free()`, `paid()`

### Plan Türleri
| Özellik | Açıklama |
|---------|----------|
| `is_public=true` | Herkes görebilir, `tenant_id` otomatik null olur |
| `tenant_id` set | Sadece o tenant'a özel plan |
| `is_free=true` | Ücretsiz plan |
| `is_active=true` | Yayında (fiyat ve özellik gerektirir) |
| `archived_at` set | Arşivlenmiş (aktif abonelik yoksa arşivlenebilir) |

### Servis ve Controller
- **Contract**: `app/Contracts/Panel/Plan/PlanServiceInterface.php`
- **Service**: `app/Services/Panel/Plan/PlanService.php`
- **Controller**: `app/Http/Controllers/Panel/Plan/PlanController.php`

### CRUD İşlemleri
| İşlem | Metod | Not |
|-------|-------|-----|
| Oluştur | `create(data, user, ip, userAgent)` | `is_active=false` default |
| Güncelle | `update(plan, data, ...)` | `is_public=true` ise `tenant_id` temizlenir |
| Yayınla | `publish(plan, ...)` | Fiyat ve özellik gerektirir |
| Yayından kaldır | `unpublish(plan, ...)` | `is_active=false` |
| Arşivle | `archive(plan, ...)` | Aktif abonelik varsa engellenır |
| Geri al | `restore(plan, ...)` | `archived_at` temizlenir |

### Form Request Validasyonu (`app/Http/Requests/Panel/Plan/`)
- `name`: required, max:255
- `slug`: nullable, unique:plans
- `description`: nullable, max:1000
- `tenant_id`: nullable, ulid, exists:tenants
- `is_free`, `is_active`, `is_public`: boolean
- `grace_period_days`: required, integer, 0-30

### Route'lar
```
GET    /plans                       → index (filter: archived)
POST   /plans                       → store
GET    /plans/{plan}/edit           → edit
PUT    /plans/{plan}                → update
POST   /plans/{plan}/publish        → publish
POST   /plans/{plan}/unpublish      → unpublish
POST   /plans/{plan}/archive        → archive
POST   /plans/{plan}/restore        → restore
```

---

## PlanPrice Yönetimi

### Model (`app/Models/PlanPrice.php`)
- Alanlar: `plan_id`, `price`, `currency`, `interval`, `interval_count`, `trial_days`
- Cast: `price` → decimal:2, `interval` → `PlanInterval` enum

### PlanInterval Enum (`app/Enums/PlanInterval.php`)
- `DAY` (Günlük), `MONTH` (Aylık), `YEAR` (Yıllık)

### Servis
- **Contract**: `app/Contracts/Panel/Plan/PlanPriceServiceInterface.php`
- **Service**: `app/Services/Panel/Plan/PlanPriceService.php`
- **Controller**: `app/Http/Controllers/Panel/Plan/PlanPriceController.php`

### Validasyon (`app/Http/Requests/Panel/Plan/`)
- `price`: required, numeric, min:0
- `currency`: required, size:3, sistem para birimiyle eşleşmeli
- `interval`: required, PlanInterval enum
- `interval_count`: required, integer, 1-12
- `trial_days`: nullable, integer, 0-365

### Route'lar
```
POST   /plans/{plan}/prices              → store
PUT    /plans/{plan}/prices/{price}      → update
DELETE /plans/{plan}/prices/{price}      → destroy
```

---

## Feature (Özellik) Yönetimi

### Model (`app/Models/Feature.php`)
- Alanlar: `code`, `slug`, `name`, `description`, `type`, `unit`, `reset_period`, `is_active`
- Cast: `type` → `FeatureType` enum, `reset_period` → `ResetPeriod` enum

### FeatureType Enum (`app/Enums/FeatureType.php`)
| Tip | Açıklama | Örnek | İzin verilen AddonType |
|-----|----------|-------|----------------------|
| `LIMIT` | Sayısal üst sınır | 10 kullanıcı | INCREMENT, UNLIMITED |
| `FEATURE` | Boolean açık/kapalı | API erişimi | BOOLEAN |
| `METERED` | Periyodik sıfırlanan sayaç | 1000 email/ay | INCREMENT, UNLIMITED |

- METERED tip için `unit` (ör. "email") ve `reset_period` (DAILY, MONTHLY, YEARLY, NEVER) zorunlu
- LIMIT tip için `unit` zorunlu

### Servis ve Controller
- **Contract**: `app/Contracts/Panel/Plan/FeatureServiceInterface.php`
- **Service**: `app/Services/Panel/Plan/FeatureService.php`
- **Controller**: `app/Http/Controllers/Panel/Plan/FeatureController.php`

### Validasyon (`app/Http/Requests/Panel/Plan/`)
- `name`: required, max:150
- `code`: required, max:150, unique:features
- `type`: required, FeatureType enum
- `unit`: required if LIMIT|METERED, max:50
- `reset_period`: required if METERED, ResetPeriod enum
- `is_active`: boolean

### Route'lar
```
GET    /plans/features                      → index (filter: search, type, is_active)
POST   /plans/features                      → store
GET    /plans/features/{feature}/edit       → edit
PUT    /plans/features/{feature}            → update
DELETE /plans/features/{feature}            → destroy
```

---

## Feature-Plan Sync (Özellik Atama)

Plan'a özellik atarken pivot table `plan_features` kullanılır ve her özelliğin bir `value` değeri olur.

### Value Mantığı
| Feature Type | Value | Anlamı |
|-------------|-------|--------|
| FEATURE | null veya '1' | Özellik aktif |
| LIMIT | sayısal değer | Üst sınır (ör. "10") |
| LIMIT | null veya '-1' | Sınırsız |
| METERED | sayısal değer | Periyodik limit (ör. "1000") |
| METERED | null veya '-1' | Sınırsız |

### Sync İşlemi
- **Controller**: `app/Http/Controllers/Panel/Plan/PlanFeatureController.php` → `sync()`
- **Request**: `SyncPlanFeaturesRequest` — `features` array, her biri `feature_id` + `value`
- **Service**: `PlanService.syncFeatures()` → `plan.features().sync(syncData)`
- `-1` veya boş string → null olarak kaydedilir (sınırsız)

```
PUT /plans/{plan}/features → PlanFeatureController@sync
```

---

## Addon Yönetimi

### Model (`app/Models/Addon.php`)
- Alanlar: `name`, `slug`, `description`, `feature_id`, `addon_type`, `value`, `price`, `currency`, `is_recurring`, `interval`, `interval_count`, `is_active`, `is_public`
- Cast: `addon_type` → `AddonType` enum, `interval` → `PlanInterval` enum

### AddonType Enum (`app/Enums/AddonType.php`)
| Tip | Açıklama | Örnek |
|-----|----------|-------|
| `INCREMENT` | Mevcut limite ekleme | +10 kullanıcı |
| `UNLIMITED` | Limiti tamamen kaldırma | Sınırsız kullanıcı |
| `BOOLEAN` | Özelliği açma | API erişimi aç |

**Kısıtlama**: Addon tipi, bağlı feature'ın tipine uygun olmalı:
- LIMIT/METERED feature → INCREMENT veya UNLIMITED addon
- FEATURE feature → BOOLEAN addon

### Tekrar Eden vs Tek Seferlik
- `is_recurring=true`: Periyodik ödeme (interval + interval_count gerekli)
- `is_recurring=false`: Tek seferlik satın alma

### Servis ve Controller
- **Contract**: `app/Contracts/Panel/Addon/AddonServiceInterface.php`
- **Service**: `app/Services/Panel/Addon/AddonService.php`
- **Controller**: `app/Http/Controllers/Panel/Addon/AddonController.php`

### Validasyon (`app/Http/Requests/Panel/Addon/`)
- `name`: required, max:255
- `slug`: required, unique:addons
- `feature_id`: required, ulid, exists:features
- `addon_type`: required, AddonType enum, feature tipine uygun olmalı
- `value`: required if INCREMENT (pozitif integer)
- `price`: required, numeric, min:0
- `currency`: required, size:3
- `is_recurring`: boolean
- `interval` + `interval_count`: required if recurring

### Route'lar
```
GET    /plans/addons                    → index (filter: search, feature_id, addon_type, is_recurring, is_active)
POST   /plans/addons                    → store
GET    /plans/addons/{addon}/edit      → edit
PUT    /plans/addons/{addon}           → update
DELETE /plans/addons/{addon}           → destroy
```

---

## Event'ler

Tüm CRUD işlemlerinde audit trail için event dispatch edilir:
- Plan: `PanelPlanCreated`, `PanelPlanUpdated`, `PanelPlanArchived`
- PlanPrice: `PanelPlanPriceCreated`, `PanelPlanPriceUpdated`, `PanelPlanPriceDeleted`
- Feature: `PanelFeatureCreated`, `PanelFeatureUpdated`, `PanelFeatureDeleted`
- Addon: `PanelAddonCreated`, `PanelAddonUpdated`, `PanelAddonDeleted`

Her event `($model, $user, $ip, $userAgent)` parametreleri alır.
