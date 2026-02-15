export function useFeatureType() {
    const typeLabel = (type: string | undefined): string => {
        const map: Record<string, string> = {
            limit: 'Limit',
            feature: 'Özellik',
            metered: 'Ölçümlü',
        };
        return map[type ?? ''] ?? type ?? '-';
    };

    const typeSeverity = (type: string | undefined): string => {
        const map: Record<string, string> = {
            limit: 'info',
            feature: 'success',
            metered: 'warn',
        };
        return map[type ?? ''] ?? 'secondary';
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

    const sourceSeverity = (source: string | undefined): string => {
        const map: Record<string, string> = {
            plan: 'info',
            override: 'warn',
            addon: 'success',
            default: 'secondary',
        };
        return map[source ?? ''] ?? 'secondary';
    };

    return { typeLabel, typeSeverity, sourceLabel, sourceSeverity };
}
