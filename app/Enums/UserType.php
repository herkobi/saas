<?php

declare(strict_types=1);

namespace App\Enums;

enum UserType: string
{
    case ADMIN = 'admin';
    case TENANT = 'tenant';

    /**
     * Kullanıcı Platform Yöneticisi mi?
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Kullanıcı bir Tenant kullanıcısı mı?
     */
    public function isTenant(): bool
    {
        return $this === self::TENANT;
    }

    /**
     * Kullanıcı bir Tenant kullanıcısı mı? (Geriye dönük uyumluluk ve netlik için)
     */
    public function isTenantUser(): bool
    {
        return $this === self::TENANT;
    }

    /**
     * Kullanıcı tipi için okunabilir etiket.
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Yönetici',
            self::TENANT => 'Müşteri',
        };
    }
}
