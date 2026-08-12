<script setup>
import { onBeforeUnmount, onMounted } from 'vue';
import { RouterView } from 'vue-router';
import DefaultLayout from '@/layouts/DefaultLayout.vue';

const onLogout = () => {
    window.location.reload();
};

onMounted(() => window.addEventListener('staynest:logout', onLogout));
onBeforeUnmount(() => window.removeEventListener('staynest:logout', onLogout));
</script>

<template>
    <DefaultLayout>
        <RouterView />
    </DefaultLayout>
</template>
