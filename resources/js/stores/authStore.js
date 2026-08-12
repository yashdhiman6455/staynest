import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { authService } from '@/services/authService';

const TOKEN_KEY = 'staynest_token';
const USER_KEY = 'staynest_user';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem(TOKEN_KEY) || '');
    const user = ref(JSON.parse(localStorage.getItem(USER_KEY) || 'null'));
    const loading = ref(false);

    const isAuthenticated = computed(() => Boolean(token.value));

    function persist() {
        localStorage.setItem(TOKEN_KEY, token.value);
        localStorage.setItem(USER_KEY, JSON.stringify(user.value));
    }

    async function register(payload) {
        loading.value = true;

        try {
            const response = await authService.register(payload);
            token.value = response.token;
            user.value = response.user;
            persist();

            return response;
        } finally {
            loading.value = false;
        }
    }

    async function login(payload) {
        loading.value = true;

        try {
            const response = await authService.login(payload);
            token.value = response.token;
            user.value = response.user;
            persist();

            return response;
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        try {
            await authService.logout();
        } finally {
            clear();
        }
    }

    function clear() {
        token.value = '';
        user.value = null;
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    }

    function hydrate(payload) {
        user.value = payload.user;
        persist();
    }

    return {
        token,
        user,
        loading,
        isAuthenticated,
        register,
        login,
        logout,
        clear,
        hydrate,
    };
});
