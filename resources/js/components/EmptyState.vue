<script setup>
defineProps({
    title: { type: String, default: 'Nothing here yet' },
    message: { type: String, default: 'Try adjusting your search or explore more properties.' },
    actionLabel: { type: String, default: '' },
});

defineEmits(['action']);
</script>

<template>
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-night-200 bg-night-50/60 px-6 py-16 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-card">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-brand-400">
                <path d="M3 9.5 12 3l9 6.5" />
                <path d="M5 8.5V21h14V8.5" />
                <path d="M9 21v-6h6v6" />
            </svg>
        </div>
        <h3 class="mt-5 text-lg font-bold text-night-800">{{ title }}</h3>
        <p class="mt-1 max-w-sm text-sm text-night-500">{{ message }}</p>
        <button v-if="actionLabel" type="button" class="btn-primary mt-6" @click="$emit('action')">
            {{ actionLabel }}
        </button>
    </div>
</template>
