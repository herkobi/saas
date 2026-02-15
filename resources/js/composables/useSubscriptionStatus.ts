export function useSubscriptionStatus() {
    const statusLabel = (status: string | undefined): string => {
        const map: Record<string, string> = {
            active: 'Aktif',
            trial: 'Deneme',
            expired: 'Süresi Doldu',
            grace_period: 'Ek Süre',
            cancelled: 'İptal Edildi',
        };
        return map[status ?? ''] ?? status ?? '-';
    };

    const statusSeverity = (status: string | undefined): string => {
        const map: Record<string, string> = {
            active: 'success',
            trial: 'info',
            expired: 'danger',
            grace_period: 'warn',
            cancelled: 'secondary',
        };
        return map[status ?? ''] ?? 'secondary';
    };

    return { statusLabel, statusSeverity };
}
