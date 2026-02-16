# Herkobi SaaS — Frontend Yapılacaklar

---

## 1. Mevcut Component Yapısı — Düzenleme Önerileri

### Common'a Taşınması Gereken Componentler

Aşağıdaki componentler app/ ve panel/ altında **birebir aynı**. Ortak kullanılmak üzere common/ altına taşınmalı:

| Component | Açıklama |
|-----------|----------|
| `AppearanceTabs.vue` | Tema seçici (light/dark/system) |
| `Heading.vue` | Sayfa başlık + açıklama |
| `NavFooter.vue` | Sidebar alt menü (dış linkler) |
| `UserInfo.vue` | Avatar + isim + email |
| `TwoFactorRecoveryCodes.vue` | 2FA kurtarma kodları kartı |
| `TwoFactorSetupModal.vue` | 2FA kurulum modalı |

> **Not:** `AppContent.vue`, `AppShell.vue`, `AppSidebarHeader.vue` layout bileşenleri — her iki tarafta da aynı ama layout'a bağlı oldukları için ayrı kalabilir.

### Uyarlanması Gereken Starter Kit Componentleri

Bu componentler Laravel starter kit'ten gelen demo verili componentler. Gerçek veriye uyarlanmalı:

| Component | Durum | Yapılacak |
|-----------|-------|-----------|
| `TeamSwitcher.vue` (app) | Hardcoded demo takımlar | Gerçek tenant listesi (`auth.tenants`), tenant switch işlemi |
| `NavMain.vue` (app + panel) | Hardcoded demo menü | Gerçek navigasyon yapısı (her taraf için farklı) |
| `NavUser.vue` (app + panel) | Hardcoded statik kullanıcı | `auth.user` ile gerçek kullanıcı verisi |
| `NavProjects.vue` (app + panel) | Hardcoded demo projeler | Kaldırılmalı veya farklı amaçla kullanılmalı |
| `AppSidebar.vue` (app + panel) | Hardcoded tüm demo data | Gerçek nav yapısıyla yeniden oluşturulmalı |
| `AppHeader.vue` (app + panel) | Kısmen gerçek, nav demo | Navigasyon menüsü gerçek veriye bağlanmalı |
| `AppLogo.vue` (common) | Hardcoded "Laravel Starter Kit" | `site.name` ve `site.logo` ile dinamik |
| `DeleteUser.vue` (app + panel) | İşlevsel ama panel'de gerekli mi? | Panel'de kullanıcı silme yoksa kaldır |

---

## 2. Yeni Componentler — Panel Tarafı

### Dashboard (`panel/Dashboard`)

| Component | Açıklama |
|-----------|----------|
| `StatsCards.vue` | Özet kartlar (toplam tenant, aktif abonelik, gelir, vb.) |
| `RevenueChart.vue` | Gelir grafiği (payments/revenue-chart endpoint) |
| `PlanDistributionChart.vue` | Plan dağılım grafiği |
| `RecentPayments.vue` | Son ödemeler listesi |
| `ExpiringSubscriptions.vue` | Süresi yaklaşan abonelikler |

### Plan Yönetimi (`panel/Plans/*`)

| Component | Açıklama |
|-----------|----------|
| `PlanForm.vue` | Plan oluşturma/düzenleme formu |
| `PlanPriceForm.vue` | Plan fiyat ekleme/düzenleme (modal veya inline) |
| `PlanPriceCard.vue` | Fiyat kartı (interval, price, trial_days gösterimi) |
| `PlanFeatureSync.vue` | Plan-özellik eşleştirme arayüzü (checkbox + value) |
| `PlanStatusBadge.vue` | Plan durumu (aktif/pasif/arşiv) badge |

### Özellik Yönetimi (`panel/Plans/Features/*`)

| Component | Açıklama |
|-----------|----------|
| `FeatureForm.vue` | Özellik oluşturma/düzenleme formu |
| `FeatureTypeBadge.vue` | Özellik tipi badge (limit/feature/metered) — `useFeatureType` kullanır |

### Addon Yönetimi (`panel/Addons/*`)

| Component | Açıklama |
|-----------|----------|
| `AddonForm.vue` | Addon oluşturma/düzenleme formu |
| `AddonTypeBadge.vue` | Addon tipi badge (increment/unlimited/boolean) |

### Tenant Yönetimi (`panel/Tenants/*`)

| Component | Açıklama |
|-----------|----------|
| `TenantDetailCard.vue` | Tenant bilgi kartı (account detayları) |
| `TenantSubscriptionCard.vue` | Mevcut abonelik bilgisi + işlemler (iptal, yenile, plan değiştir, trial/grace uzat) |
| `TenantSubscriptionForm.vue` | Manuel abonelik oluşturma formu |
| `TenantUserList.vue` | Tenant kullanıcıları tablosu + status güncelleme |
| `TenantPaymentList.vue` | Tenant ödemeleri tablosu |
| `TenantAddonList.vue` | Tenant addon'ları (uzat/iptal işlemleri) |
| `TenantFeatureOverrides.vue` | Özellik override'ları (sync/sil/temizle) |
| `TenantActivityList.vue` | Aktivite geçmişi listesi |
| `TenantTabs.vue` | Tenant detay sekmeleri — `useTenantTabs` kullanır |

### Ödeme Yönetimi (`panel/Payments/*`)

| Component | Açıklama |
|-----------|----------|
| `PaymentDetailCard.vue` | Ödeme detayı kartı |
| `PaymentStatusBadge.vue` | Ödeme durumu badge — `usePaymentStatus` kullanır |
| `PaymentStatusUpdateForm.vue` | Durum güncelleme formu |
| `PaymentInvoiceActions.vue` | Faturalama işlemleri (tek/toplu) |
| `PaymentFilters.vue` | Ödeme filtreleme (tarih, durum, tenant) |

### Abonelik Yönetimi (`panel/Subscriptions/*`)

| Component | Açıklama |
|-----------|----------|
| `SubscriptionDetailCard.vue` | Abonelik detay kartı |
| `SubscriptionStatusBadge.vue` | Abonelik durumu badge — `useSubscriptionStatus` kullanır |

### Ayarlar (`panel/Settings/*`)

| Component | Açıklama |
|-----------|----------|
| `SettingsForm.vue` | Genel ayarlar formu (site adı, logo, favicon vb.) |
| `FileUploadField.vue` | Dosya yükleme + silme alanı |

---

## 3. Yeni Componentler — App Tarafı

### Dashboard (`app/Dashboard`)

| Component | Açıklama |
|-----------|----------|
| `SubscriptionSummaryCard.vue` | Mevcut abonelik özeti |
| `UsageSummaryCard.vue` | Özellik kullanım özeti |
| `QuickActions.vue` | Hızlı işlem butonları |

### Tenant Oluşturma (`app/Tenant/*`)

| Component | Açıklama |
|-----------|----------|
| `TenantCreateForm.vue` | Yeni tenant oluşturma formu |

### Abonelik (`app/Account/Subscription/*`)

| Component | Açıklama |
|-----------|----------|
| `CurrentSubscriptionCard.vue` | Mevcut plan, bitiş tarihi, durum |
| `SubscriptionActions.vue` | İptal, plan değiştir butonları |

### Faturalama (`app/Account/Billing/*`)

| Component | Açıklama |
|-----------|----------|
| `BillingInfoForm.vue` | Fatura bilgileri formu (şirket, vergi no, adres) |

### Ödeme (`app/Account/Payments/*`)

| Component | Açıklama |
|-----------|----------|
| `PaymentList.vue` | Ödeme geçmişi tablosu |
| `PaymentDetail.vue` | Ödeme detay kartı |

### Checkout (`app/Account/Checkout/*`)

| Component | Açıklama |
|-----------|----------|
| `CheckoutSummary.vue` | Ödeme özeti (plan, fiyat, proration, toplam) |
| `PayTRIframe.vue` | PayTR ödeme iframe'i |
| `CheckoutProcessing.vue` | İşlem devam ediyor göstergesi |
| `CheckoutResult.vue` | Başarılı/başarısız sonuç kartı |

### Plan Değişikliği (`app/Account/Plan/*`)

| Component | Açıklama |
|-----------|----------|
| `PlanComparisonCard.vue` | Plan karşılaştırma kartı |
| `PlanChangeConfirmation.vue` | Onay ekranı (proration detayı ile) |
| `ScheduledDowngradeNotice.vue` | Zamanlanmış downgrade bildirimi |

### Addon'lar (`app/Account/Addons/*`)

| Component | Açıklama |
|-----------|----------|
| `AddonCard.vue` | Addon kartı (satın al/iptal et) |
| `ActiveAddonList.vue` | Aktif addon'lar listesi |

### Kullanıcı Yönetimi (`app/Account/Users/*`)

| Component | Açıklama |
|-----------|----------|
| `UserList.vue` | Kullanıcı tablosu |
| `UserDetailCard.vue` | Kullanıcı detay kartı (rol/durum güncelleme) |
| `UserRoleSelect.vue` | Rol seçimi |
| `UserStatusActions.vue` | Durum güncelleme + kullanıcı çıkarma |
| `UserActivityTimeline.vue` | Kullanıcı aktivite geçmişi |

### Davetiye (`app/Account/Users/Invitations/*`)

| Component | Açıklama |
|-----------|----------|
| `InvitationList.vue` | Davetiye tablosu (iptal/yeniden gönder) |
| `InviteUserForm.vue` | Yeni davetiye formu |

### Davetiye Kabul (`app/Invitation/*`)

| Component | Açıklama |
|-----------|----------|
| `InvitationAcceptCard.vue` | Davetiye kabul kartı |

### Bildirimler (`app/Profile/Notifications/*` + `panel/Profile/Notifications/*`)

| Component | Konum | Açıklama |
|-----------|-------|----------|
| `NotificationList.vue` | **common** | Bildirim listesi (okundu işaretle, tümünü okundu yap) |
| `NotificationItem.vue` | **common** | Tek bildirim kartı |
| `NotificationBell.vue` | **common** | Header'da bildirim ikonu + okunmamış sayısı |

---

## 4. Common'a Eklenecek Yeni Componentler

Bu componentler hem app hem panel tarafında kullanılacak:

| Component | Açıklama |
|-----------|----------|
| `StatusBadge.vue` | Genel amaçlı durum badge'i (renk objesi alır, `{ bg, text }`) |
| `DataTable.vue` | Genel tablo wrapper'ı (TanStack Vue Table üzerinde) |
| `DataTablePagination.vue` | Sayfalama kontrolü (PaginatedData tipi ile) |
| `DataTableFilters.vue` | Genel filtre alanları (arama, select, tarih) |
| `ConfirmDialog.vue` | Onay dialog'u (silme, iptal gibi tehlikeli işlemler için) |
| `EmptyState.vue` | Boş durum gösterimi (icon + mesaj + aksiyon butonu) |
| `LoadingState.vue` | Yükleme göstergesi (skeleton veya spinner) |
| `NotificationList.vue` | Bildirim listesi (her iki tarafta aynı yapıda) |
| `NotificationItem.vue` | Tek bildirim kartı |
| `NotificationBell.vue` | Header bildirim ikonu |
| `ActivityTimeline.vue` | Aktivite zaman çizelgesi (tenant + user activity) |
| `CurrencyDisplay.vue` | Para birimi gösterimi (`useFormatting` kullanır) |
| `DateDisplay.vue` | Tarih gösterimi (`useFormatting` kullanır) |

---

## 5. Özet: Sayfa → Component Eşlemesi

### Panel Sayfaları (oluşturulacak)

| Sayfa | Kullanacağı Componentler |
|-------|--------------------------|
| `panel/Dashboard` | StatsCards, RevenueChart, PlanDistributionChart, RecentPayments, ExpiringSubscriptions |
| `panel/Plans/Index` | DataTable, PlanStatusBadge |
| `panel/Plans/Create` | PlanForm |
| `panel/Plans/Edit` | PlanForm, PlanPriceForm, PlanPriceCard, PlanFeatureSync |
| `panel/Plans/Features/Index` | DataTable, FeatureTypeBadge |
| `panel/Plans/Features/Create` | FeatureForm |
| `panel/Plans/Features/Edit` | FeatureForm |
| `panel/Addons/Index` | DataTable, AddonTypeBadge |
| `panel/Addons/Create` | AddonForm |
| `panel/Addons/Edit` | AddonForm |
| `panel/Tenants/Index` | DataTable, SubscriptionStatusBadge |
| `panel/Tenants/Show` | TenantTabs, TenantDetailCard |
| `panel/Tenants/Subscription` | TenantSubscriptionCard, TenantSubscriptionForm |
| `panel/Tenants/Users` | TenantUserList, StatusBadge |
| `panel/Tenants/Payments` | TenantPaymentList, PaymentStatusBadge |
| `panel/Tenants/Addons` | TenantAddonList |
| `panel/Tenants/Features` | TenantFeatureOverrides, FeatureTypeBadge |
| `panel/Tenants/Activities` | TenantActivityList, ActivityTimeline |
| `panel/Payments/Index` | DataTable, PaymentFilters, PaymentStatusBadge, PaymentInvoiceActions |
| `panel/Payments/Show` | PaymentDetailCard, PaymentStatusUpdateForm |
| `panel/Subscriptions/Index` | DataTable, SubscriptionStatusBadge |
| `panel/Subscriptions/Show` | SubscriptionDetailCard |
| `panel/Settings/General/Index` | SettingsForm, FileUploadField |
| `panel/Profile/Notifications/Index` | NotificationList |
| `panel/Profile/Notifications/Archived` | NotificationList |

### App Sayfaları (oluşturulacak)

| Sayfa | Kullanacağı Componentler |
|-------|--------------------------|
| `app/Dashboard` | SubscriptionSummaryCard, UsageSummaryCard, QuickActions |
| `app/Tenant/Create` | TenantCreateForm |
| `app/Invitation/Accept` | InvitationAcceptCard |
| `app/Account/Subscription/Index` | CurrentSubscriptionCard, SubscriptionActions |
| `app/Account/Billing/Index` | BillingInfoForm |
| `app/Account/Payments/Index` | PaymentList, PaymentStatusBadge |
| `app/Account/Payments/Show` | PaymentDetail |
| `app/Account/Checkout/Index` | CheckoutSummary, PayTRIframe |
| `app/Account/Checkout/Processing` | CheckoutProcessing |
| `app/Account/Checkout/Success` | CheckoutResult |
| `app/Account/Checkout/Failed` | CheckoutResult |
| `app/Account/Addons/Index` | AddonCard, ActiveAddonList |
| `app/Account/Plan/Change` | PlanComparisonCard, ScheduledDowngradeNotice |
| `app/Account/Plan/Confirm` | PlanChangeConfirmation |
| `app/Account/Users/Index` | UserList |
| `app/Account/Users/Show` | UserDetailCard, UserRoleSelect, UserStatusActions |
| `app/Account/Users/Activities` | UserActivityTimeline, ActivityTimeline |
| `app/Account/Users/Invitations` | InvitationList, InviteUserForm |
| `app/Profile/Notifications/Index` | NotificationList |
| `app/Profile/Notifications/Archived` | NotificationList |

---

## 6. Çalışma Sırası Önerisi

1. **Önce:** Mevcut componentlerin common'a taşınması + starter kit componentlerinin temizlenmesi
2. **Sonra:** Common componentler (StatusBadge, DataTable, ConfirmDialog, EmptyState, vb.)
3. **Panel:** Dashboard → Plans/Features/Addons → Tenants → Payments/Subscriptions → Settings
4. **App:** Dashboard → Subscription/Billing → Checkout → Plan Change → Users/Invitations → Addons
5. **Son:** Bildirimler (her iki taraf)
