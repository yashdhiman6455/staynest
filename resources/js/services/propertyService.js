import api from './api';

export const propertyService = {
    async getAll(params = {}) {
        const { data } = await api.get('/properties', { params });
        return data;
    },

    async getBySlug(slug) {
        const { data } = await api.get(`/properties/${slug}`);
        return data;
    },

    async getMine() {
        const { data } = await api.get('/my-properties');
        return data;
    },

    async create(payload) {
        const { data } = await api.post('/properties', toFormData(payload));
        return data;
    },

    async update(id, payload) {
        const { data } = await api.put(`/properties/${id}`, toFormData(payload));
        return data;
    },

    async destroy(id) {
        const { data } = await api.delete(`/properties/${id}`);
        return data;
    },
};

function toFormData(payload) {
    const form = new FormData();

    Object.entries(payload).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            form.append(key, value);
        }
    });

    return form;
}
