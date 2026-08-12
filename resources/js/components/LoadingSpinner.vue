<script setup>
defineProps({
    label: { type: String, default: 'Loading…' },
    fullscreen: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="flex items-center justify-center py-16"
        :class="fullscreen ? 'min-h-[50vh]' : ''"
        role="status"
        aria-live="polite"
    >
        <div class="flex flex-col items-center gap-3">
            <svg class="h-8 w-8 animate-spin text-brand-500" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-20" />
                <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
            <span class="text-sm font-medium text-night-400">{{ label }}</span>
        </div>
    </div>
</template>
