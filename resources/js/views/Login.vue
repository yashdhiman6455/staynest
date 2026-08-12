<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import BrandLogo from '@/components/BrandLogo.vue';
import { extractErrorMessage, extractFieldErrors } from '@/utils/errors';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const form = reactive({
    email: '',
    password: '',
});

const fieldErrors = ref({});
const serverError = ref('');

async function submit() {
    fieldErrors.value = {};
    serverError.value = '';

    try {
        await auth.login(form);
        const redirect = route.query.redirect;

        router.push(typeof redirect === 'string' ? redirect : { name: 'home' });
    } catch (error) {
        fieldErrors.value = extractFieldErrors(error);
        serverError.value = extractErrorMessage(error, 'Unable to log you in. Please try again.');
    }
}
</script>

<template>
    <div class="relative min-h-[calc(100vh-4rem)] overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-br from-brand-50 via-white to-orange-50" />
        <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-96 w-96 rounded-full bg-brand-200/40 blur-3xl" />

        <div class="container-page flex min-h-[calc(100vh-4rem)] items-center justify-center py-12">
            <div class="w-full max-w-md animate-fade-up">
                <div class="card p-8 sm:p-10">
                    <div class="flex flex-col items-center text-center">
                        <BrandLogo :size="52" />
                        <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-night-900">Welcome back</h1>
                        <p class="mt-1 text-sm text-night-500">Log in to manage your stays and contact hosts.</p>
                    </div>

                    <form class="mt-8 space-y-5" @submit.prevent="submit">
                        <p v-if="serverError" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
                            {{ serverError }}
                        </p>

                        <div>
                            <label class="label" for="login-email">Email</label>
                            <input
                                id="login-email"
                                v-model.trim="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="input"
                                :class="fieldErrors.email ? 'border-red-400 focus:ring-red-500/30' : ''"
                            />
                            <p v-if="fieldErrors.email" class="mt-1.5 text-xs font-medium text-red-600">{{ fieldErrors.email }}</p>
                        </div>

                        <div>
                            <label class="label" for="login-password">Password</label>
                            <input
                                id="login-password"
                                v-model="form.password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="input"
                                :class="fieldErrors.password ? 'border-red-400 focus:ring-red-500/30' : ''"
                            />
                            <p v-if="fieldErrors.password" class="mt-1.5 text-xs font-medium text-red-600">{{ fieldErrors.password }}</p>
                        </div>

                        <button type="submit" class="btn-primary w-full" :disabled="auth.loading">
                            <svg v-if="auth.loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25" />
                                <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            {{ auth.loading ? 'Logging in…' : 'Log in' }}
                        </button>
                    </form>

                    <div class="mt-6 rounded-xl bg-night-50 p-4 text-sm text-night-500">
                        <p class="font-semibold text-night-600">Demo credentials</p>
                        <p class="mt-1">yash@staynest.test · password</p>
                    </div>

                    <p class="mt-6 text-center text-sm text-night-500">
                        New to StayNest?
                        <RouterLink :to="{ name: 'register' }" class="font-bold text-brand-600 hover:text-brand-700">
                            Create an account
                        </RouterLink>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
