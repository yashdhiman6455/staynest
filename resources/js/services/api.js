import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api/v1',
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('staynest_token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;

        if (status === 401 && !error.config?.url?.includes('/login') && !error.config?.url?.includes('/register')) {
            localStorage.removeItem('staynest_token');
            localStorage.removeItem('staynest_user');
            window.dispatchEvent(new CustomEvent('staynest:logout'));
        }

        return Promise.reject(error);
    }
);

export default api;
