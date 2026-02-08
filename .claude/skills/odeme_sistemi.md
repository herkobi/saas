# Ödeme ve Checkout Sistemi

## Ödeme Akışı (PayTR Entegrasyonu)

```
1. Tenant checkout başlatır
   → CheckoutController.initiate() → CheckoutService.initiate()

2. CheckoutService tutar hesaplar
   → Subtotal, vergi (%20 KDV), proration kredisi (upgrade ise), toplam

3. PayTR token oluşturulur
   → PayTRService.generatePaymentToken() → iframe URL döner

4. Kullanıcı PayTR iframe'de ödeme yapar

5. PayTR webhook gönderir (POST /payment/callback)
   → PaymentCallbackController.handle()
   → CheckoutService.processCallback() — imza doğrula, Payment oluştur

6. Event'ler tetiklenir
   → TenantPaymentSucceeded → TenantProcessSuccessfulPayment listener
   → Abonelik oluşturma/yenileme/upgrade işlemi yapılır
```

## Temel Servisler ve Dosyalar

| Servis | Dosya Yolu | Görev |
|--------|-----------|-------|
| CheckoutService | `app/Services/App/Account/CheckoutService.php` | Checkout başlatma, tutar hesaplama, callback işleme |
| PayTRService | `app/Services/App/Account/PayTRService.php` | PayTR token oluşturma, imza doğrulama |
| PaymentService (App) | `app/Services/App/Account/PaymentService.php` | Tenant ödeme geçmişi, istatistikler |
| PaymentService (Panel) | `app/Services/Panel/PaymentService.php` | Tüm ödemeler, fatura işaretleme, gelir raporu |
| ProrationService | `app/Services/App/Account/ProrationService.php` | Upgrade kredi hesaplama |

## Checkout Durumları (`app/Enums/CheckoutStatus.php`)

PENDING → PROCESSING → COMPLETED / FAILED / EXPIRED / CANCELLED

## Payment Durumları (`app/Enums/PaymentStatus.php`)

PENDING → PROCESSING → COMPLETED / FAILED / REFUNDED / CANCELLED

## Webhook Güvenliği

- `/payment/callback` route'u **authentication middleware yok** (PayTR'den gelir)
- İmza doğrulama `PayTRService` içinde yapılır
- Config: `config/paytr.php` — merchant_id, merchant_key, merchant_salt
- Callback URL: `config/paytr.php` → `callback_url`
- **Dikkat**: PayTR credential'larını asla kod içine yazma, `.env` üzerinden `config/paytr.php` ile çek

## Checkout Tipleri

Checkout kaydındaki `type` alanı ile akış belirlenir:
- **TYPE_NEW**: Yeni abonelik satın alma → `SubscriptionPurchaseService.createSubscription()`
- **TYPE_RENEW**: Mevcut abonelik yenileme → `SubscriptionPurchaseService.renewSubscription()`
- **TYPE_UPGRADE**: Plan yükseltme → `PlanChangeService.processUpgrade()`
- **TYPE_ADDON**: Addon satın alma → ilgili addon işleme

## Tutar Hesaplama

```
Subtotal  = Plan fiyatı (veya addon fiyatı)
Kredi     = Proration (upgrade ise, kalan gün kredisi)
Vergi     = (Subtotal - Kredi) × %20 (config: payment.tax_rate)
Toplam    = Subtotal - Kredi + Vergi
```

Para birimi: TRY (₺) — `config/herkobi.php` → `payment.currency`
