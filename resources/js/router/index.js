import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/Home.vue'),
        meta: { title: 'StayNest — Find a place you will love to stay' },
    },
    {
        path: '/properties',
        name: 'properties',
        component: () => import('@/views/Properties.vue'),
        meta: { title: 'Explore Stays | StayNest' },
    },
    {
        path: '/properties/create',
        name: 'create-property',
        component: () => import('@/views/CreateProperty.vue'),
        meta: { title: 'Add Property | StayNest', requiresAuth: true },
    },
    {
        path: '/properties/:id/edit',
        name: 'edit-property',
        component: () => import('@/views/EditProperty.vue'),
        meta: { title: 'Edit Property | StayNest', requiresAuth: true },
    },
    {
        path: '/properties/:slug',
        name: 'property-details',
        component: () => import('@/views/PropertyDetails.vue'),
        meta: { title: 'Property | StayNest' },
    },
    {
        path: '/my-properties',
        name: 'my-properties',
        component: () => import('@/views/MyProperties.vue'),
        meta: { title: 'My Properties | StayNest', requiresAuth: true },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/Login.vue'),
        meta: { title: 'Log in | StayNest', guestOnly: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/Register.vue'),
        meta: { title: 'Create account | StayNest', guestOnly: true },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/views/NotFound.vue'),
        meta: { title: 'Page not found | StayNest' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return savedPosition || { top: 0 };
    },
});

router.beforeEach((to) => {
    const token = localStorage.getItem('staynest_token');

    if (to.meta.requiresAuth && !token) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && token) {
        return { name: 'home' };
    }

    return true;
});

router.afterEach((to) => {
    if (to.meta.title) {
        document.title = to.meta.title;
    }
});

export default router;
