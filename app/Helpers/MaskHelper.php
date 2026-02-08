<?php

/**
 * Mask Helper
 *
 * Provides data masking functions for KVKK/GDPR compliance.
 * Used to anonymize personal data while preserving data structure
 * and statistical value.
 *
 * @package    App\Helpers
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Helpers;

/**
 * Helper class for masking personal data.
 *
 * Provides methods to mask names, emails, and IP addresses
 * in a KVKK/GDPR compliant manner.
 */
class MaskHelper
{
    /**
     * Mask a person's name.
     *
     * Keeps the first character and masks the rest.
     *
     * @param string $name The name to mask
     * @return string The masked name
     *
     * @example
     * MaskHelper::name('Bülent') → 'B*****'
     * MaskHelper::name('Ali') → 'A**'
     */
    public static function name(string $name): string
    {
        if (empty($name) || mb_strlen($name) <= 1) {
            return $name;
        }

        $firstChar = mb_substr($name, 0, 1);
        $maskLength = mb_strlen($name) - 1;

        return $firstChar . str_repeat('*', $maskLength);
    }

    /**
     * Mask an email address.
     *
     * Keeps the first character of local and domain parts,
     * masks the rest while preserving the structure.
     *
     * @param string $email The email to mask
     * @return string The masked email
     *
     * @example
     * MaskHelper::email('bulent@example.com') → 'b*****@e******.com'
     * MaskHelper::email('user@gmail.com') → 'u***@g****.com'
     */
    public static function email(string $email): string
    {
        if (empty($email) || !str_contains($email, '@')) {
            return $email;
        }

        $parts = explode('@', $email);
        
        if (count($parts) !== 2) {
            return $email;
        }

        [$local, $domain] = $parts;

        // Mask local part (before @)
        $maskedLocal = self::maskPart($local);

        // Mask domain part (before last dot)
        $domainParts = explode('.', $domain);
        
        if (count($domainParts) < 2) {
            return "{$maskedLocal}@{$domain}";
        }

        $tld = array_pop($domainParts);
        $domainName = implode('.', $domainParts);
        $maskedDomain = self::maskPart($domainName);

        return "{$maskedLocal}@{$maskedDomain}.{$tld}";
    }

    /**
     * Mask an IP address.
     *
     * Keeps the first two octets and masks the last two.
     *
     * @param string $ip The IP address to mask
     * @return string The masked IP address
     *
     * @example
     * MaskHelper::ip('192.168.1.100') → '192.168.xxx.xxx'
     * MaskHelper::ip('10.0.0.1') → '10.0.xxx.xxx'
     */
    public static function ip(string $ip): string
    {
        if (empty($ip)) {
            return $ip;
        }

        // IPv4
        if (str_contains($ip, '.')) {
            $parts = explode('.', $ip);
            
            if (count($parts) !== 4) {
                return $ip;
            }

            return "{$parts[0]}.{$parts[1]}.xxx.xxx";
        }

        // IPv6 - mask last 4 groups
        if (str_contains($ip, ':')) {
            $parts = explode(':', $ip);
            $keepGroups = max(1, count($parts) - 4);
            $keptParts = array_slice($parts, 0, $keepGroups);
            
            return implode(':', $keptParts) . ':xxxx:xxxx:xxxx:xxxx';
        }

        return $ip;
    }

    /**
     * Mask a string part (helper method).
     *
     * Keeps the first character and masks the rest.
     *
     * @param string $part The string part to mask
     * @return string The masked string
     */
    protected static function maskPart(string $part): string
    {
        if (empty($part) || strlen($part) <= 1) {
            return $part;
        }

        $firstChar = substr($part, 0, 1);
        $maskLength = strlen($part) - 1;

        return $firstChar . str_repeat('*', $maskLength);
    }
}
