export function useUserStatus() {
    const statusLabel = (status: number | undefined): string => {
        const s = status ?? 1;
        if (s === 0) return 'Pasif';
        if (s === 2) return 'Taslak';
        return 'Aktif';
    };

    const statusSeverity = (status: number | undefined): string => {
        const s = status ?? 1;
        if (s === 0) return 'secondary';
        if (s === 2) return 'warning';
        return 'success';
    };

    const statusIcon = (status: number | undefined): string => {
        const s = status ?? 1;
        if (s === 0) return 'Ban';
        if (s === 2) return 'Eye';
        return 'CheckCircle';
    };

    const statusColor = (status: number | undefined): { bg: string; text: string } => {
        const s = status ?? 1;
        if (s === 0) return { bg: 'bg-muted', text: 'text-muted-foreground' };
        if (s === 2) return { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' };
        return { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400' };
    };

    const statusDotColor = (status: number | undefined): string => {
        const s = status ?? 1;
        if (s === 0) return 'bg-muted-foreground';
        if (s === 2) return 'bg-amber-500 dark:bg-amber-400';
        return 'bg-green-500 dark:bg-green-400';
    };

    const statusDescription = (status: number | undefined): string => {
        const s = status ?? 1;
        if (s === 0) return 'Erişim yok';
        if (s === 2) return 'Salt okunur';
        return 'Tam erişim';
    };

    const roleLabel = (role: string | undefined): string => {
        return role === 'owner' ? 'Sahip' : 'Üye';
    };

    const roleSeverity = (role: string | undefined): string => {
        return role === 'owner' ? 'warning' : 'info';
    };

    return {
        statusLabel,
        statusSeverity,
        statusIcon,
        statusColor,
        statusDotColor,
        statusDescription,
        roleLabel,
        roleSeverity,
    };
}
