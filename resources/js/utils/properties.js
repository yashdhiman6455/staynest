export const PROPERTY_TYPES = [
    'Apartment',
    'House',
    'Villa',
    'Cottage',
    'Hotel',
    'Guest House',
];

export const PRICE_LIMITS = { min: 0, max: 100000 };

export function buildPropertyQuery(filters) {
    const params = {};

    if (filters.location?.trim()) params.location = filters.location.trim();
    if (filters.type) params.type = filters.type;
    if (filters.min_price) params.min_price = filters.min_price;
    if (filters.max_price) params.max_price = filters.max_price;
    if (filters.guests) params.guests = filters.guests;

    return params;
}
