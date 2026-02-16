export function useSubscriptionStatus() {
    const statusLabel = (status: string | undefined): string => {
        const map: Record<string, string> = {
            active: 'Aktif',
            trialing: 'Deneme',
            canceled: 'İptal Edildi',
            past_due: 'Ödeme Gecikmiş',
            expired: 'Süresi Doldu',
        };
        return map[status ?? ''] ?? status ?? '-';
    };

    const statusColor = (status: string | undefined): { bg: string; text: string } => {
        const map: Record<string, { bg: string; text: string }> = {
            active: { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400' },
            trialing: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            canceled: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' },
            past_due: { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-600 dark:text-red-400' },
            expired: { bg: 'bg-muted', text: 'text-muted-foreground' },
        };
        return map[status ?? ''] ?? { bg: 'bg-muted', text: 'text-muted-foreground' };
    };

    const statusDotColor = (status: string | undefined): string => {
        const map: Record<string, string> = {
            active: 'bg-green-500 dark:bg-green-400',
            trialing: 'bg-blue-500 dark:bg-blue-400',
            canceled: 'bg-amber-500 dark:bg-amber-400',
            past_due: 'bg-red-500 dark:bg-red-400',
            expired: 'bg-muted-foreground',
        };
        return map[status ?? ''] ?? 'bg-muted-foreground';
    };

    return { statusLabel, statusColor, statusDotColor };
}
