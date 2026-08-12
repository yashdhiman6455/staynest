<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePropertyStore } from '@/stores/propertyStore';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import EmptyState from '@/components/EmptyState.vue';
import { formatCurrency } from '@/utils/format';
import { extractErrorMessage } from '@/utils/errors';

const router = useRouter();
const store = usePropertyStore();

const errorMessage = ref('');
const deleting = ref(null);
const confirmOpen = ref(false);
const deleteError = ref('');

onMounted(async () => {
    try {
        await store.fetchMyProperties();
    } catch {
        errorMessage.value = 'We could not load your properties. Please try again.';
    }
});

function askDelete(property) {
    deleting.value = property;
    deleteError.value = '';
    confirmOpen.value = true;
}

async function confirmDelete() {
    if (!deleting.value) return;

    deleteError.value = '';

    try {
        await store.deleteProperty(deleting.value.id);
        await store.fetchMyProperties();
        confirmOpen.value = false;
        deleting.value = null;
    } catch (error) {
        deleteError.value = extractErrorMessage(error, 'Unable to delete this property.');
    }
}
</script>

<template>
    <section class="container-page py-10 sm:py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-night-900 sm:text-4xl">My properties</h1>
                <p class="mt-2 text-sm text-night-500 sm:text-base">
                    Manage the spaces you've listed on StayNest.
                </p>
            </div>
            <RouterLink :to="{ name: 'create-property' }" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Add property
            </RouterLink>
        </div>

        <p v-if="errorMessage" class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
            {{ errorMessage }}
        </p>

        <LoadingSpinner v-if="store.loading" label="Loading your properties…" />

        <div v-else-if="store.myProperties.length" class="mt-8 space-y-4">
            <div
                v-for="property in store.myProperties"
                :key="property.id"
                class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center"
            >
                <RouterLink
                    :to="{ name: 'property-details', params: { slug: property.slug } }"
                    class="block h-40 w-full shrink-0 overflow-hidden rounded-xl bg-night-100 sm:h-24 sm:w-36"
                >
                    <img
                        v-if="property.image_url"
                        :src="property.image_url"
                        :alt="property.title"
                        class="h-full w-full object-cover transition duration-300 hover:scale-105"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-night-100 to-night-200">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8 text-night-400">
                            <path d="M3 10.5 12 3l9 7.5V21a1 1 0 01-1 1H4a1 1 0 01-1-1v-10.5z" stroke-linejoin="round" />
                            <path d="M9 22v-8h6v8" stroke-linejoin="round" />
                        </svg>
                    </div>
                </RouterLink>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="truncate text-lg font-bold text-night-800">{{ property.title }}</h3>
                        <span
                            class="badge"
                            :class="property.status === 'published'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-amber-100 text-amber-700'"
                        >
                            {{ property.status === 'published' ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-night-500">
                        {{ property.city || property.location }}, {{ property.country }}
                    </p>
                    <p class="mt-1 text-sm font-bold text-night-800">{{ formatCurrency(property.price_per_night) }} / night</p>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2 sm:flex-col lg:flex-row">
                    <RouterLink
                        :to="{ name: 'property-details', params: { slug: property.slug } }"
                        class="btn-ghost px-3 py-2 text-xs"
                    >
                        View
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'edit-property', params: { id: property.id } }"
                        class="btn-secondary px-3 py-2 text-xs"
                    >
                        Edit
                    </RouterLink>
                    <button
                        type="button"
                        class="btn px-3 py-2 text-xs text-red-600 hover:bg-red-50"
                        @click="askDelete(property)"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="mt-8">
            <EmptyState
                title="You haven't listed any properties yet"
                message="Share your space with travellers and start earning."
                action-label="Add your first property"
                @action="router.push({ name: 'create-property' })"
            />
        </div>

        <div v-if="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-night-900/50 backdrop-blur-sm" @click="confirmOpen = false" />

            <div class="relative w-full max-w-sm animate-fade-up rounded-2xl bg-white p-6 shadow-lift">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-6 w-6 text-red-600">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>

                <h3 class="mt-4 text-lg font-bold text-night-900">Delete this property?</h3>
                <p class="mt-1 text-sm text-night-500">
                    "<strong>{{ deleting?.title }}</strong>" will be permanently removed. This action cannot be undone.
                </p>

                <p v-if="deleteError" class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-600">
                    {{ deleteError }}
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn-secondary" @click="confirmOpen = false">
                        Cancel
                    </button>
                    <button type="button" class="btn-danger" :disabled="store.saving" @click="confirmDelete">
                        {{ store.saving ? 'Deleting…' : 'Delete property' }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
