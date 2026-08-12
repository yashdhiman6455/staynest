<script setup>
import BrandLogo from '@/components/BrandLogo.vue';
</script>

<template>
    <section class="container-page flex min-h-[50vh] items-center justify-center py-20">
        <div class="max-w-md text-center">
            <div class="flex justify-center">
                <BrandLogo :size="64" />
            </div>
            <h1 class="mt-6 text-6xl font-extrabold text-brand-500">404</h1>
            <p class="mt-2 text-lg font-bold text-night-800">Page not found</p>
            <p class="mt-2 text-sm text-night-500">
                The page you're looking for doesn't exist or has been moved.
            </p>
            <RouterLink :to="{ name: 'home' }" class="btn-primary mt-6">
                Back to home
            </RouterLink>
        </div>
    </section>
</template>
