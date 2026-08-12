<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import BrandLogo from './BrandLogo.vue';
import { initials } from '@/utils/format';

const router = useRouter();
const auth = useAuthStore();

const mobileOpen = ref(false);

const navLinks = [
    { label: 'Home', to: { name: 'home' } },
    { label: 'Explore', to: { name: 'properties' } },
    { label: 'My Properties', to: { name: 'my-properties' }, authOnly: true },
];

async function handleLogout() {
    mobileOpen.value = false;
    await auth.logout();
    router.push({ name: 'home' });
}

function closeMenu() {
    mobileOpen.value = false;
}
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-night-100 bg-white/90 backdrop-blur-md">
        <div class="container-page flex h-16 items-center justify-between gap-4">
            <RouterLink :to="{ name: 'home' }" class="flex items-center gap-2.5" @click="closeMenu">
                <BrandLogo :size="34" />
                <span class="text-xl font-extrabold tracking-tight text-night-900">
                    Stay<span class="text-brand-500">Nest</span>
                </span>
            </RouterLink>

            <nav class="hidden items-center gap-1 md:flex">
                <RouterLink
                    v-for="link in navLinks.filter((l) => !l.authOnly || auth.isAuthenticated)"
                    :key="link.label"
                    :to="link.to"
                    class="rounded-lg px-3.5 py-2 text-sm font-semibold text-night-600 transition hover:bg-night-100 hover:text-night-900"
                    active-class="text-brand-600 !bg-brand-50"
                >
                    {{ link.label }}
                </RouterLink>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <template v-if="auth.isAuthenticated">
                    <RouterLink
                        :to="{ name: 'create-property' }"
                        class="btn-primary"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Add Property
                    </RouterLink>

                    <div class="relative ml-1 flex items-center gap-2 border-l border-night-100 pl-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white shadow-sm"
                        >
                            {{ initials(auth.user?.name) }}
                        </div>
                        <div class="leading-tight">
                            <p class="max-w-[9rem] truncate text-sm font-bold text-night-800">{{ auth.user?.name }}</p>
                            <button
                                type="button"
                                class="text-xs font-medium text-night-400 hover:text-red-500"
                                @click="handleLogout"
                            >
                                Log out
                            </button>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <RouterLink :to="{ name: 'login' }" class="btn-ghost">Log in</RouterLink>
                    <RouterLink :to="{ name: 'register' }" class="btn-primary">Sign up</RouterLink>
                </template>
            </div>

            <button
                type="button"
                class="rounded-lg p-2 text-night-600 hover:bg-night-100 md:hidden"
                aria-label="Toggle menu"
                @click="mobileOpen = !mobileOpen"
            >
                <svg v-if="!mobileOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-6 w-6">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-6 w-6">
                    <path d="M6 6l12 12M6 18L18 6" />
                </svg>
            </button>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-2 opacity-0"
        >
            <div v-if="mobileOpen" class="border-t border-night-100 bg-white px-4 pb-5 pt-3 md:hidden">
                <nav class="flex flex-col gap-1">
                    <RouterLink
                        v-for="link in navLinks.filter((l) => !l.authOnly || auth.isAuthenticated)"
                        :key="link.label"
                        :to="link.to"
                        class="rounded-lg px-3 py-2.5 text-sm font-semibold text-night-700 hover:bg-night-100"
                        @click="closeMenu"
                    >
                        {{ link.label }}
                    </RouterLink>
                </nav>

                <div class="mt-3 border-t border-night-100 pt-3">
                    <template v-if="auth.isAuthenticated">
                        <RouterLink
                            :to="{ name: 'create-property' }"
                            class="btn-primary w-full"
                            @click="closeMenu"
                        >
                            Add Property
                        </RouterLink>
                        <button
                            type="button"
                            class="btn-secondary mt-2 w-full"
                            @click="handleLogout"
                        >
                            Log out ({{ auth.user?.name }})
                        </button>
                    </template>
                    <template v-else>
                        <RouterLink
                            :to="{ name: 'login' }"
                            class="btn-secondary w-full"
                            @click="closeMenu"
                        >
                            Log in
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'register' }"
                            class="btn-primary mt-2 w-full"
                            @click="closeMenu"
                        >
                            Sign up
                        </RouterLink>
                    </template>
                </div>
            </div>
        </Transition>
    </header>
</template>
