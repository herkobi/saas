export function useFeatureType() {
    const typeLabel = (type: string | undefined): string => {
        const map: Record<string, string> = {
            limit: 'Limit',
            feature: 'Özellik',
            metered: 'Ölçümlü',
        };
        return map[type ?? ''] ?? type ?? '-';
    };

    const typeColor = (type: string | undefined): { bg: string; text: string } => {
        const map: Record<string, { bg: string; text: string }> = {
            limit: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            feature: { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400' },
            metered: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' },
        };
        return map[type ?? ''] ?? { bg: 'bg-muted', text: 'text-muted-foreground' };
    };

    const sourceLabel = (source: string | undefined): string => {
        const map: Record<string, string> = {
            plan: 'Plan',
            override: 'Özel',
            addon: 'Eklenti',
            default: 'Varsayılan',
        };
        return map[source ?? ''] ?? source ?? '-';
    };

    const sourceColor = (source: string | undefined): { bg: string; text: string } => {
        const map: Record<string, { bg: string; text: string }> = {
            plan: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            override: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' },
            addon: { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400' },
            default: { bg: 'bg-muted', text: 'text-muted-foreground' },
        };
        return map[source ?? ''] ?? { bg: 'bg-muted', text: 'text-muted-foreground' };
    };

    return { typeLabel, typeColor, sourceLabel, sourceColor };
}
