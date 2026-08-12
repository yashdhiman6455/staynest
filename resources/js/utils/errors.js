export function extractErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
    const response = error?.response?.data;

    if (!response) {
        return error?.message === 'Network Error'
            ? 'Unable to reach the server. Please check your connection.'
            : fallback;
    }

    if (response.message) {
        return response.message;
    }

    return fallback;
}

export function extractFieldErrors(error) {
    const errors = error?.response?.data?.errors;

    if (!errors || typeof errors !== 'object') {
        return {};
    }

    return Object.fromEntries(
        Object.entries(errors).map(([field, messages]) => [field, messages[0]])
    );
}
