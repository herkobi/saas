export function useInitials() {
    /**
     * Verilen tam ismin baş harflerini döndürür.
     * Örnek: "Fatih Sultan Mehmet" -> "FM"
     * Örnek: "Ahmet Yılmaz" -> "AY"
     */
    const getInitials = (fullName?: string): string => {
        if (!fullName) return '';

        const names = fullName.trim().split(' ');

        if (names.length === 0) return '';

        // Sadece bir isim varsa ilk harfini büyük döner
        if (names.length === 1) return names[0].charAt(0).toUpperCase();

        // İlk ismin ilk harfi ve son ismin ilk harfini birleştirir
        return `${names[0].charAt(0)}${names[names.length - 1].charAt(0)}`.toUpperCase();
    };

    return { getInitials };
}
