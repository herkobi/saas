export function usePaymentStatus() {
    const statusLabel = (status: string | undefined): string => {
        const map: Record<string, string> = {
            pending: 'Bekliyor',
            processing: 'İşleniyor',
            completed: 'Tamamlandı',
            failed: 'Başarısız',
            refunded: 'İade Edildi',
            cancelled: 'İptal Edildi',
        };
        return map[status ?? ''] ?? status ?? '-';
    };

    const statusColor = (status: string | undefined): { bg: string; text: string } => {
        const map: Record<string, { bg: string; text: string }> = {
            pending: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' },
            processing: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            completed: { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400' },
            failed: { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-600 dark:text-red-400' },
            refunded: { bg: 'bg-muted', text: 'text-muted-foreground' },
            cancelled: { bg: 'bg-muted', text: 'text-muted-foreground' },
        };
        return map[status ?? ''] ?? { bg: 'bg-muted', text: 'text-muted-foreground' };
    };

    const statusDotColor = (status: string | undefined): string => {
        const map: Record<string, string> = {
            pending: 'bg-amber-500 dark:bg-amber-400',
            processing: 'bg-blue-500 dark:bg-blue-400',
            completed: 'bg-green-500 dark:bg-green-400',
            failed: 'bg-red-500 dark:bg-red-400',
            refunded: 'bg-muted-foreground',
            cancelled: 'bg-muted-foreground',
        };
        return map[status ?? ''] ?? 'bg-muted-foreground';
    };

    return { statusLabel, statusColor, statusDotColor };
}
