# Multi-Tenancy ve Kullanıcı Yönetimi

## Tenant Yapısı

Herkobi multi-tenant SaaS'tır. Her müşteri bir `Tenant`, her tenant'ın kullanıcıları vardır.

```
User ←→ Tenant (many-to-many, pivot: tenant_user)
         ↳ role: TenantUserRole::OWNER | STAFF
         ↳ joined_at: timestamp
```

- Bir kullanıcının birden fazla tenant'ı olabilir (`config/herkobi.php` → `tenant.allow_multiple_tenants`)
- Bir tenant'ın birden fazla üyesi olabilir (`config/herkobi.php` → `tenant.allow_team_members`)

## Middleware Zinciri

Tenant route'ları (`routes/app.php`) şu middleware sırasıyla korunur:

```
auth → auth.session → verified
  → tenant.context (LoadTenantContext)     // Session'dan tenant yükle
    → subscription.active (EnsureActiveSubscription)  // Abonelik geçerli mi?
      → write.access (EnsureWriteAccess)   // Yazma izni var mı? (DRAFT kullanıcılar engellenr)
```

Ek middleware'ler:
- `tenant.owner` (EnsureTenantOwner): Sadece OWNER rolü
- `tenant.allow_team_members`: Feature gate kontrolü (config kapalıysa engeller)
- `feature.access` (EnsureFeatureAccess): Plan bazlı özellik erişim kontrolü (FEATURE/LIMIT/METERED)

## TenantContextService (`app/Services/Shared/TenantContextService.php`)

Session'daki aktif tenant'ı yönetir:
- `getCurrentTenant()`: Aktif tenant'ı döner
- `setCurrentTenant(Tenant $tenant)`: Tenant'ı session'a yazar
- `isTeamMembersAllowed()`: Config gate kontrolü
- `isMultipleTenantsAllowed()`: Config gate kontrolü
- `canCreateNewTenant()`: Yeni tenant oluşturma izni
- `canInviteTeamMember()`: Ekip üyesi davet izni (**hibrit kontrol**: config + plan limiti birlikte kontrol edilir)

## Kullanıcı Tipleri (`app/Enums/UserType.php`)

| Tip | Alan | Erişim |
|-----|------|--------|
| `ADMIN` | Panel (`routes/panel.php`) | Tam admin yetkisi |
| `TENANT` | App (`routes/app.php`) | Kendi tenant verileri |

## Tenant Kullanıcı Rolleri (`app/Enums/TenantUserRole.php`)

| Rol | Yetki |
|-----|-------|
| `OWNER` | Tam yetki — abonelik, ödeme, ekip yönetimi |
| `STAFF` | Sınırlı yetki — owner tarafından belirlenir |

## UserService (Ekip Yönetimi)

- **Contract**: `app/Contracts/App/Account/UserServiceInterface.php`
- **Service**: `app/Services/App/Account/UserService.php`
- Kullanıcı listele, rol değiştir, kullanıcı çıkar
- İzin kontrolleri: `canManageUser()`, `canChangeRole()`, `canInviteTeamMember()`
- Event'ler: `TenantUserRemoved`, `TenantUserRoleChanged`

## InvitationService (Davetiye Sistemi)

- **Contract**: `app/Contracts/App/Account/InvitationServiceInterface.php`
- **Service**: `app/Services/App/Account/InvitationService.php`
- **Model**: `app/Models/TenantInvitation.php` (`BaseTenant`'tan türetilir)
- **Enum**: `app/Enums/InvitationStatus.php` (PENDING, ACCEPTED, EXPIRED, REVOKED)

### Davet Akışı

```
invite(tenant, email, role, invitedBy):
  1. canInviteTeamMember() kontrolü (config + plan limiti)
  2. Bekleyen davetiyeler dahil limit kontrolü
  3a. Kullanıcı mevcut → doğrudan tenant'a ekle → TenantUserDirectlyAdded event
  3b. Kullanıcı yok → token oluştur (SHA-256 hash) → TenantUserInvited event → email gönder
```

### Token Güvenliği
- `Str::random(64)` ile raw token üretilir
- DB'de `hash('sha256', $rawToken)` saklanır
- Raw token sadece email linkinde bulunur
- Cross-tenant sorgularda `withoutTenantScope()` kullanılır

### Otomatik Kabul
- `CreateNewUser` (Fortify) kayıt sonrası `acceptPendingInvitations()` çağırır
- Yeni kayıt olan kullanıcının bekleyen davetiyeleri otomatik kabul edilir

### Config
- `herkobi.invitation.expires_days`: Davetiye geçerlilik süresi (varsayılan: 7 gün)

### Event'ler
- `TenantUserInvited` → `SendInvitationEmail`, `LogTenantInvitationActivity`
- `TenantUserDirectlyAdded` → `LogTenantInvitationActivity`
- `TenantInvitationAccepted` → `LogTenantInvitationActivity`
- `TenantInvitationRevoked` → `LogTenantInvitationActivity`

### Scheduled Job
- `ExpireOldInvitationsJob` (günlük): Süresi dolmuş davetiyeleri expire eder

## Hibrit Kontrol Paterni (Config + Plan)

```
Config kapalı → Özellik tamamen engellenir (middleware seviyesi)
Config açık   → Plan bazlı limit/erişim kontrolü yapılır (feature.access middleware)
```

Users route grubu örneği:
```
middleware: ['tenant.allow_team_members', 'feature.access:users']
  → tenant.allow_team_members: config('herkobi.tenant.allow_team_members') kapalıysa engeller
  → feature.access:users: açıksa plan'daki 'users' feature limitini kontrol eder
```

Servis seviyesinde de aynı hibrit kontrol:
- `TenantContextService.canInviteTeamMember()`: config + plan limiti birlikte kontrol eder

## Route Yapısı

### App (Tenant) — `routes/app.php`
```
/dashboard                    → DashboardController
/profile/*                    → ProfileController, PasswordController, TwoFactorController
/account/subscription         → SubscriptionController
/account/billing              → BillingController
/account/payments             → PaymentController
/account/checkout             → CheckoutController
/account/plan-change          → PlanChangeController
/account/addons               → AddonController
/account/users                → UserController (team members)
/account/users/invitations    → InvitationController (davet CRUD)
/invitation/accept/{token}    → InvitationAcceptController (public kabul)
/account/features             → FeatureController (usage/limits)
```

### Panel (Admin) — `routes/panel.php`
```
/dashboard                    → DashboardController
/payments                     → PaymentController
/subscriptions                → SubscriptionController
/tenants/{tenant}/*           → TenantController, TenantSubscriptionController, vb.
/plans/*                      → PlanController, FeatureController, AddonController
/settings/*                   → SettingController
```

## Panel Tenant Yönetimi

Admin tarafından tenant'lar üzerinde yapılabilecek işlemler:
- **TenantService**: Liste, detay, istatistik, aktivite, kullanıcılar
- **TenantSubscriptionService**: Abonelik oluştur/iptal/yenile/plan değiştir/trial uzat/grace uzat
- **TenantFeatureService**: Tenant bazlı özellik override'ları (plan limitlerini aş)
- **TenantAddonService**: Tenant'a addon ata/kaldır
