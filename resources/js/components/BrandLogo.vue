<script setup>
defineProps({
    size: { type: [Number, String], default: 36 },
});
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 40 40"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        aria-label="StayNest"
    >
        <defs>
            <linearGradient id="brand-grad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#FB923C" />
                <stop offset="1" stop-color="#EA580C" />
            </linearGradient>
        </defs>
        <rect width="40" height="40" rx="12" fill="url(#brand-grad)" />
        <path
            d="M12 20.5L20 13l8 7.5"
            stroke="white"
            stroke-width="2.6"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M14.5 19v8.5a1 1 0 001 1h3.2v-4.6a1.3 1.3 0 012.6 0v4.6h3.2a1 1 0 001-1V19"
            stroke="white"
            stroke-width="2.4"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <circle cx="20" cy="13.4" r="2.2" fill="white" />
    </svg>
</template>
