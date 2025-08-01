export function useFormatter() {
    const formatRupiah = (amount: number): string => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    };

    const formatRupiahSimple = (amount: number): string => {
        return 'Rp ' + amount.toLocaleString('id-ID');
    };

    return {
        formatRupiah,
        formatRupiahSimple,
    };
}