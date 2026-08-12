<script setup>
import { computed } from 'vue';

const props = defineProps({
    meta: {
        type: Object,
        required: true,
    },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['change']);

const pages = computed(() => {
    const total = props.meta.last_page ?? 1;
    const current = props.meta.current_page ?? 1;

    if (total <= 1) return [];

    const items = new Set([1, total, current - 1, current, current + 1]);

    return [...items]
        .filter((p) => p >= 1 && p <= total)
        .sort((a, b) => a - b);
});

function goto(page) {
    if (page < 1 || page > props.meta.last_page || page === props.meta.current_page || props.loading) {
        return;
    }

    emit('change', page);
}
</script>

<template>
    <nav v-if="meta.last_page > 1" class="mt-10 flex items-center justify-center gap-2" aria-label="Pagination">
        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-night-200 text-night-600 transition hover:bg-night-50 disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="meta.current_page <= 1 || loading"
            aria-label="Previous page"
            @click="goto(meta.current_page - 1)"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
        </button>

        <template v-for="(page, index) in pages" :key="page">
            <span
                v-if="index > 0 && pages[index - 1] !== page - 1"
                class="px-1 text-night-400"
                aria-hidden="true"
            >
                …
            </span>
            <button
                type="button"
                class="h-10 min-w-10 rounded-xl px-3 text-sm font-semibold transition"
                :class="page === meta.current_page
                    ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/25'
                    : 'border border-night-200 text-night-600 hover:bg-night-50'"
                :disabled="loading"
                :aria-current="page === meta.current_page ? 'page' : undefined"
                @click="goto(page)"
            >
                {{ page }}
            </button>
        </template>

        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-night-200 text-night-600 transition hover:bg-night-50 disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="meta.current_page >= meta.last_page || loading"
            aria-label="Next page"
            @click="goto(meta.current_page + 1)"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
        </button>
    </nav>
</template>
