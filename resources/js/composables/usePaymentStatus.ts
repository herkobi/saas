export function usePaymentStatus() {
    const statusLabel = (status: string | undefined): string => {
        const map: Record<string, string> = {
            pending: 'Bekliyor',
            paid: 'Ödendi',
            failed: 'Başarısız',
            refunded: 'İade Edildi',
            cancelled: 'İptal Edildi',
        };
        return map[status ?? ''] ?? status ?? '-';
    };

    const statusSeverity = (status: string | undefined): string => {
        const map: Record<string, string> = {
            pending: 'warn',
            paid: 'success',
            failed: 'danger',
            refunded: 'info',
            cancelled: 'secondary',
        };
        return map[status ?? ''] ?? 'secondary';
    };

    return { statusLabel, statusSeverity };
}
