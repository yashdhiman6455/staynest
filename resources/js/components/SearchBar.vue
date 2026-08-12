<script setup>
import { reactive } from 'vue';
import { PROPERTY_TYPES } from '@/utils/properties';

const props = defineProps({
    initial: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['search']);

const filters = reactive({
    location: props.initial.location ?? '',
    type: props.initial.type ?? '',
    min_price: props.initial.min_price ?? '',
    max_price: props.initial.max_price ?? '',
});

function submit() {
    emit('search', { ...filters });
}
</script>

<template>
    <form
        class="mx-auto max-w-3xl rounded-2xl border border-night-100 bg-white p-3 shadow-lift sm:rounded-3xl"
        @submit.prevent="submit"
    >
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <label class="flex flex-col gap-1.5 rounded-xl px-3 py-2 transition hover:bg-night-50">
                <span class="text-xs font-bold uppercase tracking-wide text-night-400">Location</span>
                <input
                    v-model.trim="filters.location"
                    type="text"
                    placeholder="Where to?"
                    class="w-full bg-transparent text-sm font-semibold text-night-800 placeholder-night-300 focus:outline-none"
                />
            </label>

            <label class="flex flex-col gap-1.5 rounded-xl px-3 py-2 transition hover:bg-night-50">
                <span class="text-xs font-bold uppercase tracking-wide text-night-400">Property type</span>
                <select
                    v-model="filters.type"
                    class="w-full cursor-pointer bg-transparent text-sm font-semibold text-night-800 focus:outline-none"
                >
                    <option value="">Any type</option>
                    <option v-for="type in PROPERTY_TYPES" :key="type" :value="type">{{ type }}</option>
                </select>
            </label>

            <label class="flex flex-col gap-1.5 rounded-xl px-3 py-2 transition hover:bg-night-50">
                <span class="text-xs font-bold uppercase tracking-wide text-night-400">Min price</span>
                <input
                    v-model.trim="filters.min_price"
                    type="number"
                    min="0"
                    placeholder="₹0"
                    class="w-full bg-transparent text-sm font-semibold text-night-800 placeholder-night-300 focus:outline-none"
                />
            </label>

            <label class="flex flex-col gap-1.5 rounded-xl px-3 py-2 transition hover:bg-night-50">
                <span class="text-xs font-bold uppercase tracking-wide text-night-400">Max price</span>
                <input
                    v-model.trim="filters.max_price"
                    type="number"
                    min="0"
                    placeholder="₹10,000+"
                    class="w-full bg-transparent text-sm font-semibold text-night-800 placeholder-night-300 focus:outline-none"
                />
            </label>
        </div>

        <button type="submit" class="btn-primary mt-3 w-full sm:mt-3 lg:w-full">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
            Search stays
        </button>
    </form>
</template>
