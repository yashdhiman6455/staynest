import api from './api';

export const authService = {
    async register(payload) {
        const { data } = await api.post('/register', payload);
        return data;
    },

    async login(payload) {
        const { data } = await api.post('/login', payload);
        return data;
    },

    async logout() {
        const { data } = await api.post('/logout');
        return data;
    },

    async me() {
        const { data } = await api.get('/user');
        return data;
    },
};
