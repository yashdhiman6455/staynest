<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { usePropertyStore } from '@/stores/propertyStore';
import { useAuthStore } from '@/stores/authStore';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import { formatPricePerNight, initials } from '@/utils/format';

const route = useRoute();
const store = usePropertyStore();
const auth = useAuthStore();

const error = ref('');
const contactNotice = ref(false);
const imgFailed = ref(false);

const successBanner = computed(() => {
    if (route.query.created) return 'Your property has been published successfully!';
    if (route.query.updated) return 'Your property has been updated successfully!';

    return '';
});

const property = computed(() => store.current);

onMounted(async () => {
    try {
        await store.fetchProperty(route.params.slug);
    } catch (err) {
        const status = err?.response?.status;

        error.value =
            status === 404
                ? 'This property could not be found.'
                : 'Something went wrong while loading this property.';
    }
});

function showContactNotice() {
    contactNotice.value = true;

    if (!auth.isAuthenticated) {
        setTimeout(() => (contactNotice.value = false), 4000);
    }
}
</script>

<template>
    <section class="container-page py-8 sm:py-10">
        <RouterLink
            :to="{ name: 'properties' }"
            class="btn-ghost -ml-3 mb-6"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
            </svg>
            Back to listings
        </RouterLink>

        <LoadingSpinner v-if="store.loading" label="Loading property…" />

        <div v-else-if="error" class="mx-auto max-w-md">
            <EmptyState
                :title="error"
                message="The listing may have been removed or the link is incorrect."
                action-label="Browse all stays"
                @action="$router.push({ name: 'properties' })"
            />
        </div>

        <div v-if="successBanner" class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 shrink-0">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            {{ successBanner }}
        </div>

        <div v-else-if="property" class="animate-fade-in">
            <div class="overflow-hidden rounded-3xl shadow-lift">
                <img
                    v-if="property.image_url && !imgFailed"
                    :src="property.image_url"
                    :alt="property.title"
                    class="h-64 w-full object-cover sm:h-96 lg:h-[28rem]"
                    @error="imgFailed = true"
                />
                <div v-else class="flex h-64 w-full items-center justify-center bg-gradient-to-br from-night-100 to-night-200 sm:h-96">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-16 w-16 text-night-400">
                        <path d="M3 10.5 12 3l9 7.5V21a1 1 0 01-1 1H4a1 1 0 01-1-1v-10.5z" stroke-linejoin="round" />
                        <path d="M9 22v-8h6v8" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_380px]">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge bg-brand-500/10 text-brand-700">{{ property.property_type }}</span>
                        <span class="badge bg-night-100 text-night-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                <path d="M10 2a3 3 0 00-3 3v.75H5.25A1.75 1.75 0 003.5 7.5v8A1.75 1.75 0 005.25 17h9.5a1.75 1.75 0 001.75-1.75v-8a1.75 1.75 0 00-1.75-1.75H13V5a3 3 0 00-3-3zM8 5a2 2 0 114 0v.75H8V5zm-2.5 5.25a.75.75 0 011.5 0v.5a.75.75 0 01-1.5 0v-.5zm7.5 0a.75.75 0 011.5 0v.5a.75.75 0 01-1.5 0v-.5z" />
                            </svg>
                            {{ property.status === 'draft' ? 'Draft' : 'Available' }}
                        </span>
                    </div>

                    <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-night-900 sm:text-4xl">
                        {{ property.title }}
                    </h1>

                    <p class="mt-3 flex items-center gap-1.5 text-base text-night-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-night-400">
                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.757.433zM7.5 9a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0z" clip-rule="evenodd" />
                        </svg>
                        {{ property.location }}{{ property.country ? `, ${property.country}` : '' }}
                    </p>

                    <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="card p-4 text-center">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-night-400">Guests</dt>
                            <dd class="mt-1 text-xl font-extrabold text-night-900">{{ property.guests }}</dd>
                        </div>
                        <div class="card p-4 text-center">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-night-400">Bedrooms</dt>
                            <dd class="mt-1 text-xl font-extrabold text-night-900">{{ property.bedrooms }}</dd>
                        </div>
                        <div class="card p-4 text-center">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-night-400">Bathrooms</dt>
                            <dd class="mt-1 text-xl font-extrabold text-night-900">{{ property.bathrooms }}</dd>
                        </div>
                        <div class="card p-4 text-center">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-night-400">Type</dt>
                            <dd class="mt-1 text-xl font-extrabold text-night-900">{{ property.property_type }}</dd>
                        </div>
                    </dl>

                    <h2 class="mt-10 text-xl font-bold text-night-900">About this stay</h2>
                    <p class="mt-3 whitespace-pre-line leading-relaxed text-night-600">
                        {{ property.description }}
                    </p>
                </div>

                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="card overflow-hidden">
                        <div class="border-b border-night-100 bg-night-50/60 p-5">
                            <p class="text-3xl font-extrabold text-night-900">
                                {{ formatPricePerNight(property.price_per_night) }}
                            </p>
                            <p class="mt-1 text-xs text-night-500">
                                No hidden fees · instant availability
                            </p>
                        </div>

                        <div class="p-5">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-600 text-base font-bold text-white"
                                >
                                    {{ initials(property.user?.name) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-night-800">
                                        Hosted by {{ property.user?.name || 'StayNest Host' }}
                                    </p>
                                    <p class="text-xs text-night-400">
                                        {{ property.user?.city || 'Verified host' }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn-primary mt-5 w-full"
                                @click="showContactNotice"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                    <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
                                    <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
                                </svg>
                                Contact Host
                            </button>

                            <Transition
                                enter-active-class="transition duration-200"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-150"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0"
                            >
                                <div
                                    v-if="contactNotice"
                                    class="mt-4 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-800"
                                >
                                    <p class="font-semibold">
                                        {{ auth.isAuthenticated ? 'Contact request sent!' : 'Please log in to contact the property owner.' }}
                                    </p>
                                    <RouterLink
                                        v-if="!auth.isAuthenticated"
                                        :to="{ name: 'login', query: { redirect: route.fullPath } }"
                                        class="mt-2 inline-block font-bold text-brand-700 underline-offset-2 hover:underline"
                                    >
                                        Log in now
                                    </RouterLink>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>
