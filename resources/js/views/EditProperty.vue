<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePropertyStore } from '@/stores/propertyStore';
import PropertyForm from '@/components/PropertyForm.vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import { extractErrorMessage, extractFieldErrors } from '@/utils/errors';

const route = useRoute();
const router = useRouter();
const store = usePropertyStore();

const fieldErrors = ref({});
const serverError = ref('');
const notFound = ref(false);

const property = computed(() => store.myProperties.find((p) => p.id === Number(route.params.id)));

onMounted(async () => {
    try {
        if (!store.myProperties.length) {
            await store.fetchMyProperties();
        }

        if (!store.myProperties.some((p) => p.id === Number(route.params.id))) {
            notFound.value = true;
        }
    } catch {
        notFound.value = true;
    }
});

async function handleSubmit(payload) {
    fieldErrors.value = {};
    serverError.value = '';

    try {
        const response = await store.updateProperty(route.params.id, payload);
        router.push({
            name: 'property-details',
            params: { slug: response.data.slug },
            query: { updated: 1 },
        });
    } catch (error) {
        fieldErrors.value = extractFieldErrors(error);
        serverError.value = extractErrorMessage(error, 'Unable to update the property. Please try again.');
    }
}
</script>

<template>
    <section class="container-page py-10 sm:py-12">
        <div class="mx-auto max-w-3xl">
            <RouterLink :to="{ name: 'my-properties' }" class="btn-ghost -ml-3 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                </svg>
                Back to my properties
            </RouterLink>

            <h1 class="text-3xl font-extrabold tracking-tight text-night-900 sm:text-4xl">Edit property</h1>

            <p v-if="serverError" class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
                {{ serverError }}
            </p>

            <div v-if="store.loading" class="mt-6">
                <LoadingSpinner label="Loading property…" />
            </div>

            <div v-else-if="notFound || !property" class="mt-6">
                <EmptyState
                    title="Property not found"
                    message="This property does not exist or you do not have permission to edit it."
                    action-label="Back to my properties"
                    @action="$router.push({ name: 'my-properties' })"
                />
            </div>

            <div v-else class="mt-6">
                <PropertyForm
                    :initial="property"
                    :errors="fieldErrors"
                    :submitting="store.saving"
                    submit-label="Save changes"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </section>
</template>
