import { defineStore } from 'pinia';
import { ref } from 'vue';
import { propertyService } from '@/services/propertyService';

export const usePropertyStore = defineStore('property', () => {
    const properties = ref([]);
    const myProperties = ref([]);
    const current = ref(null);
    const loading = ref(false);
    const saving = ref(false);
    const meta = ref({
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0,
    });

    async function fetchProperties(params = {}) {
        loading.value = true;

        try {
            const response = await propertyService.getAll(params);
            properties.value = response.data;
            meta.value = response.meta;

            return response;
        } finally {
            loading.value = false;
        }
    }

    async function fetchProperty(slug) {
        loading.value = true;

        try {
            const response = await propertyService.getBySlug(slug);
            current.value = response.data;

            return response;
        } finally {
            loading.value = false;
        }
    }

    async function fetchMyProperties() {
        loading.value = true;

        try {
            const response = await propertyService.getMine();
            myProperties.value = response.data;

            return response;
        } finally {
            loading.value = false;
        }
    }

    async function createProperty(payload) {
        saving.value = true;

        try {
            return await propertyService.create(payload);
        } finally {
            saving.value = false;
        }
    }

    async function updateProperty(id, payload) {
        saving.value = true;

        try {
            return await propertyService.update(id, payload);
        } finally {
            saving.value = false;
        }
    }

    async function deleteProperty(id) {
        saving.value = true;

        try {
            return await propertyService.destroy(id);
        } finally {
            saving.value = false;
        }
    }

    function resetCurrent() {
        current.value = null;
    }

    return {
        properties,
        myProperties,
        current,
        loading,
        saving,
        meta,
        fetchProperties,
        fetchProperty,
        fetchMyProperties,
        createProperty,
        updateProperty,
        deleteProperty,
        resetCurrent,
    };
});
