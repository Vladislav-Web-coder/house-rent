<template>
    <transition name="slide-up">
        <div
            v-if="showBanner"
            class="fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 shadow-2xl border-t border-gray-200 dark:border-gray-700 p-4 md:p-6"
        >
            <div class="container mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex-1 text-sm text-gray-700 dark:text-gray-300">
                    <p>
                        🍪 Мы используем файлы cookie для работы сайта и аналитики.
                        Продолжая пользоваться сайтом, вы соглашаетесь с
                        <router-link to="/policy" class="text-[#77c4db] hover:text-[#5fb5d1] underline">
                            политикой конфиденциальности
                        </router-link>.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="acceptAll"
                        class="px-6 py-2 bg-[#77c4db] hover:bg-[#5fb5d1] text-white rounded-lg font-medium transition-colors"
                    >
                        Принять все
                    </button>
                    <button
                        @click="acceptEssential"
                        class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Только необходимые
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const showBanner = ref(false);

onMounted(() => {
    const consent = localStorage.getItem('cookie_consent');
    if (!consent) {
        showBanner.value = true;
    }
});

function acceptAll() {
    localStorage.setItem('cookie_consent', 'all');
    showBanner.value = false;

    // Здесь можно инициализировать Яндекс.Метрику, GA и т.д.
    // initYandexMetrika();
    // initGoogleAnalytics();
}

function acceptEssential() {
    localStorage.setItem('cookie_consent', 'essential');
    showBanner.value = false;

    // Аналитика НЕ инициализируется
}
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}
</style>
