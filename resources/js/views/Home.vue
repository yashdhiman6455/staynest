<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePropertyStore } from '@/stores/propertyStore';
import SearchBar from '@/components/SearchBar.vue';
import PropertyCard from '@/components/PropertyCard.vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import { buildPropertyQuery } from '@/utils/properties';

const router = useRouter();
const store = usePropertyStore();

const loadError = ref('');

async function loadProperties() {
    loadError.value = '';

    try {
        await store.fetchProperties({ per_page: 6 });
    } catch {
        loadError.value = 'We could not load the latest stays. Please try again later.';
    }
}

onMounted(loadProperties);

function handleSearch(filters) {
    const query = buildPropertyQuery(filters);

    router.push({ name: 'properties', query });
}
</script>

<template>
    <div>
        <section class="relative overflow-hidden bg-gradient-to-br from-brand-50 via-white to-orange-50">
            <div
                class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-200/40 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-amber-100/60 blur-3xl"
            />

            <div class="container-page relative pb-16 pt-16 sm:pb-20 sm:pt-24">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="badge bg-brand-500/10 text-brand-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                            <path d="M10 1a6 6 0 00-3.815 10.631C7.237 12.5 8 13.443 8 14.456v.644a.75.75 0 00.572.729 6.016 6.016 0 002.856 0A.75.75 0 0012 15.1v-.644c0-1.013.762-1.957 1.815-2.825A6 6 0 0010 1zM8.863 17.414a.75.75 0 00-.226 1.483 9.066 9.066 0 003.687.017.75.75 0 00-.228-1.481 7.566 7.566 0 01-3.233-.019z" />
                        </svg>
                        Handpicked stays across India
                    </span>

                    <h1 class="text-balance mt-5 text-4xl font-extrabold leading-tight tracking-tight text-night-900 sm:text-5xl lg:text-6xl">
                        Find your <span class="text-brand-500">next stay</span>
                    </h1>
                    <p class="text-balance mx-auto mt-4 max-w-xl text-base leading-relaxed text-night-500 sm:text-lg">
                        Explore cosy cottages, modern apartments and beachfront villas.
                        Find a place you'll love to stay.
                    </p>
                </div>

                <div class="mt-10 animate-fade-up">
                    <SearchBar @search="handleSearch" />
                </div>
            </div>
        </section>

        <section class="container-page py-14 sm:py-16">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-night-900 sm:text-3xl">
                        Explore stays
                    </h2>
                    <p class="mt-1 text-sm text-night-500">
                        Fresh picks from hosts near you
                    </p>
                </div>
                <RouterLink :to="{ name: 'properties' }" class="btn-secondary shrink-0">
                    View all
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </RouterLink>
            </div>

            <p v-if="loadError" class="mt-6 flex flex-col items-start gap-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600 sm:flex-row sm:items-center sm:justify-between">
                <span>{{ loadError }}</span>
                <button
                    type="button"
                    class="btn-secondary px-4 py-1.5 text-xs"
                    :disabled="store.loading"
                    @click="loadProperties"
                >
                    Try again
                </button>
            </p>

            <LoadingSpinner v-if="store.loading" label="Loading stays…" />

            <div v-else-if="store.properties.length" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <PropertyCard
                    v-for="property in store.properties"
                    :key="property.id"
                    :property="property"
                />
            </div>

            <div v-else class="mt-8">
                <p class="text-sm text-night-500">No stays available yet. Be the first to add one!</p>
            </div>
        </section>
    </div>
</template>
