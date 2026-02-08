# Herkobi Roadmap

## v1.1 — Temel Altyapı

### Çoklu Dil Desteği (i18n)
- [ ] `lang/tr` ve `lang/en` dizin yapısı
- [ ] Tüm hardcoded Türkçe stringlerin çıkarılması
- [ ] Enum label'larının lokalize edilmesi
- [ ] Locale middleware ve dil seçici
- [ ] Validation mesajlarının lokalizasyonu
- [ ] Blade/frontend string lokalizasyonu

### Yetkilendirme Sistemi
- [ ] Granüler permission tanımları (Panel + App)
- [ ] Laravel Policy sistemi veya Spatie Permission entegrasyonu
- [ ] Rol bazlı erişim kontrolü (OWNER/STAFF ötesinde)
- [ ] Panel tarafı için admin rolleri ve yetkileri
- [ ] Kaynak seviyesinde (resource-level) yetkilendirme

## v1.2 — Tenant Geliştirmeleri

### Sub-domain / Domain Ayrımı
- [ ] Domain bazlı tenant çözümleme middleware'i
- [ ] Subdomain routing altyapısı (tenant.app.com)
- [ ] Özel domain doğrulama ve DNS yönlendirme
- [ ] Session-based fallback korunarak hibrit çözüm

## v1.3 — Ödeme & Para Birimi

### Çoklu Para Birimi
- [ ] CurrencyHelper çoklu para birimi desteği
- [ ] Döviz kuru servisi ve saklama
- [ ] Plan fiyatlarında çoklu para birimi
- [ ] Checkout ve ödeme akışlarının güncellenmesi
- [ ] Para birimi bazlı vergi kuralları

### Farklı Ödeme Sistemleri
- [ ] Gateway manager/factory pattern
- [ ] Config bazlı gateway seçimi
- [ ] Stripe entegrasyonu
- [ ] iyzico entegrasyonu
- [ ] Tenant/işlem bazlı gateway override
- [ ] Gateway webhook URL standardizasyonu

## v1.4 — Entegrasyon

### Public API Katmanı
- [ ] Laravel Sanctum kurulumu
- [ ] RESTful API endpoint'leri (abonelik, kullanım, fatura)
- [ ] API versiyonlama (v1)
- [ ] API resource transformer'ları
- [ ] Rate limiting (tenant bazlı)
- [ ] API dokümantasyonu (OpenAPI/Swagger)

### Outgoing Webhook Sistemi
- [ ] Webhook endpoint kayıt sistemi (tenant bazlı)
- [ ] Event → webhook eşlemesi
- [ ] HMAC imza doğrulama standardı
- [ ] Webhook delivery retry mekanizması
- [ ] Webhook log ve debug araçları

## v2.0 — Global Pazar

### Çoklu Ülke Desteği
- [ ] Ülke bazlı vergi kuralları (KDV, VAT, GST)
- [ ] Ülke bazlı ödeme gateway yönlendirmesi
- [ ] Bölgesel uyumluluk (KVKK, GDPR vb.)
- [ ] Ülke bazlı fiyatlandırma stratejileri
- [ ] Zaman dilimi ve tarih formatı desteği
