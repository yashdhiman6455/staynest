<script setup>
import { reactive } from 'vue';
import { PROPERTY_TYPES, PRICE_LIMITS } from '@/utils/properties';

const props = defineProps({
    initial: { type: Object, default: () => ({}) },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['apply', 'clear']);

const filters = reactive({
    location: props.initial.location ?? '',
    type: props.initial.type ?? '',
    min_price: props.initial.min_price ?? '',
    max_price: props.initial.max_price ?? '',
    guests: props.initial.guests ?? '',
});

const hasActiveFilters = () =>
    Boolean(filters.location || filters.type || filters.min_price || filters.max_price || filters.guests);

function apply() {
    emit('apply', { ...filters });
}

function clear() {
    filters.location = '';
    filters.type = '';
    filters.min_price = '';
    filters.max_price = '';
    filters.guests = '';
    emit('clear');
}
</script>

<template>
    <form class="card sticky top-20 p-5" @submit.prevent="apply">
        <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-night-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-brand-500">
                    <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.629V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd" />
                </svg>
                Filters
            </h2>
            <button
                type="button"
                class="text-xs font-semibold text-brand-600 hover:text-brand-700"
                @click="clear"
            >
                Clear all
            </button>
        </div>

        <div class="mt-4 space-y-4">
            <div>
                <label class="label" for="filter-location">Location</label>
                <input
                    id="filter-location"
                    v-model.trim="filters.location"
                    type="text"
                    placeholder="City or area"
                    class="input"
                />
            </div>

            <div>
                <label class="label" for="filter-type">Property type</label>
                <select id="filter-type" v-model="filters.type" class="input cursor-pointer">
                    <option value="">Any type</option>
                    <option v-for="type in PROPERTY_TYPES" :key="type" :value="type">{{ type }}</option>
                </select>
            </div>

            <div>
                <span class="label">Price per night (₹)</span>
                <div class="grid grid-cols-2 gap-2">
                    <input
                        v-model.trim="filters.min_price"
                        type="number"
                        :min="PRICE_LIMITS.min"
                        placeholder="Min"
                        class="input"
                        aria-label="Minimum price"
                    />
                    <input
                        v-model.trim="filters.max_price"
                        type="number"
                        :min="PRICE_LIMITS.min"
                        placeholder="Max"
                        class="input"
                        aria-label="Maximum price"
                    />
                </div>
            </div>

            <div>
                <label class="label" for="filter-guests">Guests</label>
                <select id="filter-guests" v-model="filters.guests" class="input cursor-pointer">
                    <option value="">Any guests</option>
                    <option v-for="n in [1,2,3,4,5,6,8,10]" :key="n" :value="n">
                        {{ n }}+ guest{{ n > 1 ? 's' : '' }}
                    </option>
                </select>
            </div>

            <button type="submit" class="btn-primary w-full" :disabled="loading">
                <svg v-if="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25" />
                    <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                {{ loading ? 'Searching…' : 'Apply filters' }}
            </button>
        </div>
    </form>
</template>
