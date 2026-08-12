<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePropertyStore } from '@/stores/propertyStore';
import PropertyCard from '@/components/PropertyCard.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import { buildPropertyQuery } from '@/utils/properties';

const route = useRoute();
const router = useRouter();
const store = usePropertyStore();

const errorMessage = ref('');

const queryFromRoute = computed(() => ({
    location: String(route.query.location ?? ''),
    type: String(route.query.type ?? ''),
    min_price: String(route.query.min_price ?? ''),
    max_price: String(route.query.max_price ?? ''),
    guests: String(route.query.guests ?? ''),
}));

const hasActiveFilters = computed(() => Object.keys(buildPropertyQuery(queryFromRoute.value)).length > 0);

async function load() {
    errorMessage.value = '';

    try {
        await store.fetchProperties({
            ...buildPropertyQuery(queryFromRoute.value),
            per_page: 12,
            page: Number(route.query.page) || 1,
        });
    } catch {
        errorMessage.value = 'We could not load properties. Please try again later.';
    }
}
function applyFilters(filters) {
    router.push({
        name: 'properties',
        query: {
            ...buildPropertyQuery(filters),
            page: undefined,
        },
    });
}

function clearFilters() {
    router.replace({ name: 'properties', query: {} });
}

function goToPage(page) {
    router.push({
        name: 'properties',
        query: { ...route.query, page },
    });

    document.getElementById('stays-heading')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

onMounted(load);
watch(() => route.query, load);
</script>

<template>
    <section class="container-page py-10 sm:py-12">
        <div class="max-w-2xl scroll-mt-24" id="stays-heading">
            <h1 class="text-3xl font-extrabold tracking-tight text-night-900 sm:text-4xl">Explore stays</h1>
            <p class="mt-2 text-sm text-night-500 sm:text-base">
                Search, filter and find the perfect place to stay.
            </p>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[290px_1fr]">
            <FilterPanel
                class="lg:sticky lg:top-24 lg:self-start"
                :initial="queryFromRoute"
                :loading="store.loading"
                @apply="applyFilters"
                @clear="clearFilters"
            />

            <div>
                <div v-if="errorMessage" class="mb-4 flex flex-col items-start gap-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600 sm:flex-row sm:items-center sm:justify-between">
                    <span>{{ errorMessage }}</span>
                    <button
                        type="button"
                        class="btn-secondary px-4 py-1.5 text-xs"
                        :disabled="store.loading"
                        @click="load"
                    >
                        Try again
                    </button>
                </div>

                <div v-if="!errorMessage && !store.loading" class="mb-5 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-night-500">
                        {{ store.meta.total }} {{ store.meta.total === 1 ? 'stay' : 'stays' }} found
                    </span>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="badge bg-night-100 text-night-600 transition hover:bg-night-200"
                        @click="clearFilters"
                    >
                        Clear filters
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>

                <LoadingSpinner v-if="store.loading" label="Finding stays…" />

                <template v-else-if="store.properties.length">
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <PropertyCard
                            v-for="property in store.properties"
                            :key="property.id"
                            :property="property"
                        />
                    </div>

                    <Pagination
                        :meta="store.meta"
                        :loading="store.loading"
                        @change="goToPage"
                    />
                </template>

                <EmptyState
                    v-else
                    :title="hasActiveFilters ? 'No stays match your search' : 'No stays available yet'"
                    :message="hasActiveFilters
                        ? 'Try adjusting your filters, or clear them to see every stay.'
                        : 'Be the first to list a property and start welcoming guests.'"
                    :action-label="hasActiveFilters ? 'Clear filters' : ''"
                    :action-visible="hasActiveFilters"
                    @action="clearFilters"
                />
            </div>
        </div>
    </section>
</template>
