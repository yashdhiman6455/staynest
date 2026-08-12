<script setup>
import BrandLogo from './BrandLogo.vue';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-night-100 bg-night-50">
        <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-2">
                <div class="flex items-center gap-2.5">
                    <BrandLogo :size="32" />
                    <span class="text-lg font-extrabold tracking-tight text-night-900">
                        Stay<span class="text-brand-500">Nest</span>
                    </span>
                </div>
                <p class="mt-3 max-w-sm text-sm leading-relaxed text-night-500">
                    Find a place you'll love to stay. Discover handpicked homes, villas and
                    apartments across India — from cosy cottages to beachfront escapes.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-night-800">Explore</h3>
                <ul class="mt-3 space-y-2 text-sm text-night-500">
                    <li><RouterLink to="/" class="hover:text-brand-600">Home</RouterLink></li>
                    <li><RouterLink to="/properties" class="hover:text-brand-600">Browse stays</RouterLink></li>
                    <li><RouterLink to="/properties/create" class="hover:text-brand-600">Add property</RouterLink></li>
                    <li><RouterLink to="/my-properties" class="hover:text-brand-600">My properties</RouterLink></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-night-800">Account</h3>
                <ul class="mt-3 space-y-2 text-sm text-night-500">
                    <li><RouterLink to="/login" class="hover:text-brand-600">Log in</RouterLink></li>
                    <li><RouterLink to="/register" class="hover:text-brand-600">Create account</RouterLink></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-night-100 py-5">
            <p class="container-page text-center text-xs text-night-400">
                © {{ year }} StayNest. Find a place you'll love to stay.
            </p>
        </div>
    </footer>
</template>
