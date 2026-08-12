<script setup>
import { reactive, ref, computed } from 'vue';
import { PROPERTY_TYPES } from '@/utils/properties';

const props = defineProps({
    initial: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    submitting: { type: Boolean, default: false },
    submitLabel: { type: String, default: 'Publish property' },
});

const emit = defineEmits(['submit']);

const form = reactive({
    title: props.initial.title ?? '',
    description: props.initial.description ?? '',
    property_type: props.initial.property_type ?? '',
    location: props.initial.location ?? '',
    city: props.initial.city ?? '',
    country: props.initial.country ?? '',
    price_per_night: props.initial.price_per_night ?? '',
    guests: props.initial.guests ?? '',
    bedrooms: props.initial.bedrooms ?? '',
    bathrooms: props.initial.bathrooms ?? '',
    image: null,
});

const previewUrl = ref(props.initial.image_url ?? '');
const imageInput = ref(null);

const fieldErrors = computed(() => props.errors);

function onFileChange(event) {
    const file = event.target.files?.[0];

    if (!file) return;

    form.image = file;

    if (previewUrl.value && !props.initial.image_url) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = URL.createObjectURL(file);
}

function clearImage() {
    form.image = null;
    previewUrl.value = '';
    if (imageInput.value) imageInput.value.value = '';
}

function submit() {
    const payload = { ...form };

    if (payload.image === null) {
        delete payload.image;
    }

    emit('submit', payload);
}

function errorFor(field) {
    return fieldErrors.value?.[field] ?? '';
}

const price = computed({
    get: () => form.price_per_night,
    set: (value) => {
        form.price_per_night = value;
    },
});
</script>

<template>
    <form class="card p-6 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label class="label" for="p-title">Property title</label>
                <input
                    id="p-title"
                    v-model.trim="form.title"
                    type="text"
                    maxlength="255"
                    placeholder="e.g. Beautiful apartment in Chandigarh"
                    class="input"
                />
                <p v-if="errorFor('title')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('title') }}</p>
            </div>

            <div class="lg:col-span-2">
                <label class="label" for="p-description">Description</label>
                <textarea
                    id="p-description"
                    v-model.trim="form.description"
                    rows="5"
                    placeholder="Describe the space, amenities and what makes it special…"
                    class="input resize-y"
                />
                <p v-if="errorFor('description')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('description') }}</p>
            </div>

            <div>
                <label class="label" for="p-type">Property type</label>
                <select id="p-type" v-model="form.property_type" class="input cursor-pointer">
                    <option value="" disabled>Select a type</option>
                    <option v-for="type in PROPERTY_TYPES" :key="type" :value="type">{{ type }}</option>
                </select>
                <p v-if="errorFor('property_type')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('property_type') }}</p>
            </div>

            <div>
                <label class="label" for="p-location">Location / Area</label>
                <input
                    id="p-location"
                    v-model.trim="form.location"
                    type="text"
                    placeholder="e.g. Sector 17, Chandigarh"
                    class="input"
                />
                <p v-if="errorFor('location')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('location') }}</p>
            </div>

            <div>
                <label class="label" for="p-city">City</label>
                <input id="p-city" v-model.trim="form.city" type="text" placeholder="e.g. Chandigarh" class="input" />
            </div>

            <div>
                <label class="label" for="p-country">Country</label>
                <input id="p-country" v-model.trim="form.country" type="text" placeholder="e.g. India" class="input" />
            </div>

            <div>
                <label class="label" for="p-price">Price per night (₹)</label>
                <input
                    id="p-price"
                    v-model.number="price"
                    type="number"
                    min="1"
                    step="0.01"
                    placeholder="e.g. 2500"
                    class="input"
                />
                <p v-if="errorFor('price_per_night')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('price_per_night') }}</p>
            </div>

            <div>
                <label class="label" for="p-guests">Maximum guests</label>
                <input
                    id="p-guests"
                    v-model.number="form.guests"
                    type="number"
                    min="1"
                    placeholder="e.g. 4"
                    class="input"
                />
                <p v-if="errorFor('guests')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('guests') }}</p>
            </div>

            <div>
                <label class="label" for="p-bedrooms">Bedrooms</label>
                <input
                    id="p-bedrooms"
                    v-model.number="form.bedrooms"
                    type="number"
                    min="0"
                    placeholder="e.g. 2"
                    class="input"
                />
                <p v-if="errorFor('bedrooms')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('bedrooms') }}</p>
            </div>

            <div>
                <label class="label" for="p-bathrooms">Bathrooms</label>
                <input
                    id="p-bathrooms"
                    v-model.number="form.bathrooms"
                    type="number"
                    min="0"
                    placeholder="e.g. 2"
                    class="input"
                />
                <p v-if="errorFor('bathrooms')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('bathrooms') }}</p>
            </div>

            <div class="lg:col-span-2">
                <span class="label">Property image</span>

                <div class="flex flex-col gap-4 sm:flex-row">
                    <div
                        class="relative flex h-44 w-full overflow-hidden rounded-xl border border-dashed border-night-300 bg-night-50 sm:w-64"
                    >
                        <img
                            v-if="previewUrl"
                            :src="previewUrl"
                            alt="Property preview"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex w-full flex-col items-center justify-center gap-2 text-night-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8">
                                <path d="M3 10.5 12 3l9 7.5V21a1 1 0 01-1 1H4a1 1 0 01-1-1v-10.5z" stroke-linejoin="round" />
                                <path d="M9 22v-8h6v8" stroke-linejoin="round" />
                            </svg>
                            <span class="text-xs font-medium">No image yet</span>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col justify-center gap-3">
                        <input
                            ref="imageInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            @change="onFileChange"
                        />
                        <button
                            type="button"
                            class="btn-secondary"
                            @click="imageInput?.click()"
                        >
                            {{ previewUrl ? 'Choose another image' : 'Upload image' }}
                        </button>
                        <p v-if="previewUrl" class="text-xs text-night-400">
                            JPEG, PNG or WebP · max 2MB
                        </p>
                        <button
                            v-if="previewUrl && !initial.image_url"
                            type="button"
                            class="text-left text-xs font-semibold text-red-500 hover:text-red-600"
                            @click="clearImage"
                        >
                            Remove image
                        </button>
                    </div>
                </div>

                <p v-if="errorFor('image')" class="mt-1.5 text-xs font-medium text-red-600">{{ errorFor('image') }}</p>
            </div>
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-night-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
            <RouterLink :to="{ name: 'my-properties' }" class="btn-secondary">
                Cancel
            </RouterLink>
            <button type="submit" class="btn-primary" :disabled="submitting">
                <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25" />
                    <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                {{ submitting ? 'Saving…' : submitLabel }}
            </button>
        </div>
    </form>
</template>
