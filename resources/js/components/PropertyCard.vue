<script setup>
import { ref } from 'vue';
import { formatPricePerNight } from '@/utils/format';

defineProps({
    property: {
        type: Object,
        required: true,
    },
});

const imgFailed = ref(false);
</script>

<template>
    <RouterLink
        :to="{ name: 'property-details', params: { slug: property.slug } }"
        class="group flex flex-col overflow-hidden rounded-2xl border border-night-100 bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-lift"
    >
        <div class="relative aspect-[4/3] overflow-hidden bg-night-100">
            <img
                v-if="property.image_url && !imgFailed"
                :src="property.image_url"
                :alt="property.title"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                @error="imgFailed = true"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-night-100 to-night-200">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-12 w-12 text-night-400">
                    <path d="M3 10.5 12 3l9 7.5V21a1 1 0 01-1 1H4a1 1 0 01-1-1v-10.5z" stroke-linejoin="round" />
                    <path d="M9 22v-8h6v8" stroke-linejoin="round" />
                </svg>
            </div>

            <span class="badge absolute left-3 top-3 bg-white/95 text-night-700 shadow-sm backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-brand-500">
                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                </svg>
                {{ property.property_type }}
            </span>

            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/30 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
        </div>

        <div class="flex flex-1 flex-col p-4">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-base font-bold leading-snug text-night-800 transition group-hover:text-brand-600">
                    {{ property.title }}
                </h3>
            </div>

            <p class="mt-1.5 flex items-center gap-1.5 text-sm text-night-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 text-night-400">
                    <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.757.433zM7.5 9a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0z" clip-rule="evenodd" />
                </svg>
                {{ property.city || property.location }}{{ property.country ? `, ${property.country}` : '' }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-night-500">
                <span class="flex items-center gap-1 rounded-lg bg-night-100 px-2 py-1 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-night-500">
                        <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a7.505 7.505 0 0113.07 0A.75.75 0 0115.87 15.5H4.13a.75.75 0 01-.665-1.007z" />
                    </svg>
                    {{ property.guests }} guests
                </span>
                <span v-if="property.bedrooms" class="flex items-center gap-1 rounded-lg bg-night-100 px-2 py-1 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-night-500">
                        <path d="M3.75 3a.75.75 0 00.75.75h11a.75.75 0 000-1.5h-11a.75.75 0 00-.75.75zM3 8.75A2.75 2.75 0 015.75 6h8.5A2.75 2.75 0 0117 8.75v1.25h-1.5V8.75a1.25 1.25 0 00-1.25-1.25h-8.5A1.25 1.25 0 004.5 8.75v1.25H3V8.75zM3 11.5v2a.75.75 0 01-1.5 0v-5A.75.75 0 012.25 7.75.75.75 0 013 8.5v3zM17.5 11.5v2a.75.75 0 101.5 0v-5a.75.75 0 00-1.5 0v3zM4.5 12.75h11v2.5a.75.75 0 101.5 0v-4h-14v4a.75.75 0 101.5 0v-2.5z" />
                    </svg>
                    {{ property.bedrooms }} bd
                </span>
                <span v-if="property.bathrooms" class="flex items-center gap-1 rounded-lg bg-night-100 px-2 py-1 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-night-500">
                        <path d="M10 2a2 2 0 00-2 2v1.5a.75.75 0 001.5 0V4a.5.5 0 011 0v6.25a2.25 2.25 0 004.5 0V6a.5.5 0 011 0v4.25a3.75 3.75 0 01-7.5 0V4a2 2 0 10-4 0v1.5a.75.75 0 001.5 0V4a3.5 3.5 0 115 0v1.5a.75.75 0 001.5 0V4a2 2 0 00-2-2z" />
                    </svg>
                    {{ property.bathrooms }} ba
                </span>
            </div>

            <div class="mt-4 flex items-end justify-between border-t border-night-100 pt-3">
                <p class="text-lg font-extrabold text-night-900">
                    {{ formatPricePerNight(property.price_per_night) }}
                </p>
            </div>

            <span class="btn-secondary mt-3 w-full group-hover:bg-brand-500 group-hover:text-white group-hover:border-brand-500">
                View Details
            </span>
        </div>
    </RouterLink>
</template>
