const currencyFormatter = new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 0,
});

export function formatCurrency(value) {
    const number = Number(value ?? 0);

    if (number >= 1000) {
        return currencyFormatter.format(number);
    }

    return `₹${number}`;
}

export function formatPricePerNight(value) {
    return `${formatCurrency(value)} / night`;
}

export function formatDate(value) {
    if (!value) return '';

    return new Date(value).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export function initials(name = '') {
    return name
        .split(' ')
        .map((part) => part[0])
        .filter(Boolean)
        .slice(0, 2)
        .join('')
        .toUpperCase();
}
