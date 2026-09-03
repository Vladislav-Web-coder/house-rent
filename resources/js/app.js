import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';

import App from './App.vue';
import Home from './views/Home.vue';
import PropertyView from './views/PropertyView.vue';
import FilamentDateRangePicker from "./components/FilamentDateRangePicker.vue";
import PrivacyPolicy from "./views/PrivacyPolicy.vue";
import NotFound from "./views/NotFound.vue";

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/property/:id',
        name: 'property',
        component: PropertyView,
        props: true
    },
    {
        path: '/policy',
        name: 'policy',
        component: PrivacyPolicy,
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (to.hash) {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({
                        el: to.hash,
                        behavior: 'smooth',
                    });
                }, 100); // Небольшая задержка для рендера DOM
            });
        }
        if (savedPosition) {
            return savedPosition;
        }
        if (to.path === from.path && !to.hash) {
            return false;
        }
        return { top: 0 };
    },
});

const app = createApp(App);

app.component('FilamentDateRangePicker', FilamentDateRangePicker);
app.use(createPinia());
app.use(router);
app.mount('#app');
