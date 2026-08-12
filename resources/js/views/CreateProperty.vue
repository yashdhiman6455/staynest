<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePropertyStore } from '@/stores/propertyStore';
import PropertyForm from '@/components/PropertyForm.vue';
import { extractErrorMessage, extractFieldErrors } from '@/utils/errors';

const router = useRouter();
const store = usePropertyStore();

const fieldErrors = ref({});
const serverError = ref('');
const success = ref('');

async function handleSubmit(payload) {
    fieldErrors.value = {};
    serverError.value = '';
    success.value = '';

    try {
        const response = await store.createProperty(payload);
        success.value = response.message;
        router.push({
            name: 'property-details',
            params: { slug: response.data.slug },
            query: { created: 1 },
        });
    } catch (error) {
        fieldErrors.value = extractFieldErrors(error);
        serverError.value = extractErrorMessage(error, 'Unable to publish the property. Please try again.');
    }
}
</script>

<template>
    <section class="container-page py-10 sm:py-12">
        <div class="mx-auto max-w-3xl">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-night-900 sm:text-4xl">Add a property</h1>
                <p class="mt-2 text-sm text-night-500 sm:text-base">
                    List your space on StayNest and start welcoming guests.
                </p>
            </div>

            <p v-if="serverError" class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
                {{ serverError }}
            </p>
            <p v-if="success" class="mt-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ success }}
            </p>

            <div class="mt-6">
                <PropertyForm
                    :errors="fieldErrors"
                    :submitting="store.saving"
                    submit-label="Publish property"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </section>
</template>
