<template>
    <div class="min-h-screen bg-[#faf7f0]">
        <!-- Header -->
        <Header />

        <!-- Hero Section: дневной залив -->
        <section class="relative min-h-screen flex items-end overflow-hidden">
            <div class="absolute inset-0">
                <img
                    src="/images/hero-bg.jpeg"
                    alt="Спящий залив"
                    class="w-full h-full object-cover"
                />
                <!-- ✅ Лёгкое общее затемнение для светлого дневного фото -->
                <div class="absolute inset-0 bg-[#0e1a24]/20"></div>
                <!-- ✅ Градиент снизу цвета глубокой воды -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#0e1a24]/90 via-[#0e1a24]/30 to-transparent"></div>
            </div>

            <div class="relative z-10 w-full">
                <div class="container mx-auto px-4 md:px-6 pb-20 md:pb-28 pt-36 md:pt-52 text-center">
                    <!-- ✅ Надзаголовок-акцент -->
                    <span class="inline-block text-[#e9a13b] tracking-[0.3em] md:tracking-[0.35em] uppercase text-[10px] md:text-sm font-semibold mb-4 md:mb-5">
                        Карелия · Берег Ладоги
                    </span>

                    <h1 class="text-4xl sm:text-5xl md:text-7xl font-light mb-4 md:mb-6 tracking-wide text-[#fdf8ef]">
                        Спящий залив
                    </h1>
                    <p class="text-lg md:text-2xl font-light mb-8 md:mb-10 max-w-2xl mx-auto text-[#e8eef1] leading-relaxed">
                        Дома для всей семьи на берегу Ладоги — рядом с самыми интересными местами Карелии
                    </p>
                    <button
                        @click="scrollToSection('properties')"
                        class="inline-block bg-[#e9a13b] text-[#1a1206] px-8 py-3 md:px-12 md:py-4 text-sm font-semibold tracking-wider hover:bg-[#d18a2a] transition-colors rounded-full cursor-pointer shadow-lg shadow-[#0e1a24]/40"
                    >
                        ЗАБРОНИРОВАТЬ
                    </button>
                </div>
            </div>

            <!-- ✅ Стрелка скролла только на десктопе -->
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 animate-bounce hidden md:block">
                <svg class="w-6 h-6 text-[#e9a13b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </section>

        <!-- Features Section: тёмная секция цвета воды -->
        <section class="py-14 md:py-20 bg-[#0e1a24]">
            <div class="container mx-auto px-4 md:px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span class="text-sm md:text-base text-[#eef3f5] font-medium">Можно с питомцами</span>
                    </div>
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                        </svg>
                        <span class="text-sm md:text-base text-[#eef3f5] font-medium">Сауна и баня</span>
                    </div>
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                        <span class="text-sm md:text-base text-[#eef3f5] font-medium">SUP-доски</span>
                    </div>
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-sm md:text-base text-[#eef3f5] font-medium">Лодки и пирс</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Date Filter Calendar -->
        <section class="container mx-auto px-4 md:px-6 -mt-8 md:-mt-10 relative z-20">
            <div class="bg-[#fffdf8] rounded-2xl shadow-xl border-2 border-[#251d12]/10 p-4 sm:p-6 md:p-8 max-w-3xl mx-auto">
                <h3 class="text-lg md:text-xl font-semibold text-[#251d12] mb-3 md:mb-4 text-center">Выберите даты проживания</h3>
                <p class="text-xs md:text-sm text-[#6e6459] text-center mb-4 md:mb-6">
                    Мы покажем только доступные объекты на выбранные даты. Даты, когда заняты все дома, будут недоступны.
                </p>

                <DateRangePicker
                    v-model="selectedDates"
                    :disabled-dates="fullyBookedDates"
                    :inline="true"
                    :show-info="true"
                    :show-reset="true"
                />
            </div>
        </section>

        <!-- Properties Section -->
        <section id="properties" class="py-16 md:py-24 bg-[#f5f2ec]">
            <div class="container mx-auto px-4 md:px-6">
                <div class="text-center mb-10 md:mb-16">
                    <h2 class="text-3xl md:text-5xl font-light mb-3 md:mb-4 text-[#251d12]">Наши дома</h2>
                    <p class="text-[#6e6459] text-base md:text-lg px-2">Продуманные до мелочей дома, тишина и карельская природа — всё, чтобы вы по-настоящему отдохнули</p>
                </div>

                <!-- ✅ Фильтры: flex-wrap для мобильных -->
                <div class="flex flex-wrap justify-center gap-3 md:gap-4 mb-10 md:mb-12">
                    <button
                        @click="updateFilter('all')"
                        class="px-5 py-2 md:px-8 md:py-3 rounded-full border-2 transition-all font-medium text-sm md:text-base"
                        :class="filter === 'all'
                            ? 'bg-[#e9a13b] text-[#1a1206] border-[#e9a13b]'
                            : 'border-[#251d12]/20 text-[#251d12] hover:border-[#e9a13b] hover:text-[#d18a2a]'"
                    >
                        Все дома
                    </button>
                    <button
                        @click="updateFilter('house')"
                        class="px-5 py-2 md:px-8 md:py-3 rounded-full border-2 transition-all font-medium text-sm md:text-base"
                        :class="filter === 'house'
                            ? 'bg-[#e9a13b] text-[#1a1206] border-[#e9a13b]'
                            : 'border-[#251d12]/20 text-[#251d12] hover:border-[#e9a13b] hover:text-[#d18a2a]'"
                    >
                        Дома
                    </button>
                    <button
                        @click="updateFilter('apartment')"
                        class="px-5 py-2 md:px-8 md:py-3 rounded-full border-2 transition-all font-medium text-sm md:text-base"
                        :class="filter === 'apartment'
                            ? 'bg-[#e9a13b] text-[#1a1206] border-[#e9a13b]'
                            : 'border-[#251d12]/20 text-[#251d12] hover:border-[#e9a13b] hover:text-[#d18a2a]'"
                    >
                        Квартиры
                    </button>
                </div>

                <!-- Message about filtered results -->
                <div v-if="selectedDates.start && selectedDates.end" class="text-center mb-8 px-4">
                    <p class="text-[#6e6459] text-sm md:text-base">
                        Показаны объекты, доступные с {{ formatDateDisplay(selectedDates.start) }} по {{ formatDateDisplay(selectedDates.end) }}
                    </p>
                </div>

                <!-- Loading state -->
                <div v-if="store.loading" class="text-center py-20">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-[#e9a13b]/20 border-t-[#e9a13b] mx-auto mb-4"></div>
                    <p class="text-[#6e6459] text-lg">Загрузка объектов...</p>
                </div>

                <!-- Properties Grid -->
                <div v-else-if="filteredProperties.length > 0" class="flex flex-col gap-8 md:gap-12 max-w-6xl mx-auto">
                    <PropertyCard
                        v-for="prop in filteredProperties"
                        :key="prop.id"
                        :property="prop"
                    />
                </div>

                <div v-else class="text-center py-20 px-4">
                    <p class="text-[#6e6459] text-lg">
                        {{ selectedDates.start && selectedDates.end ? 'На выбранные даты нет доступных объектов. Попробуйте выбрать другие даты.' : 'Пока нет доступных объектов' }}
                    </p>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="scroll-mt-[100px]">
            <Faq />
        </section>

        <!-- Footer -->
        <footer class="bg-[#0c111c] text-white py-12 md:py-16">
            <div class="container mx-auto px-4 md:px-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 md:gap-12 mb-10 md:mb-12">
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 border-2 border-[#e9a13b] rounded-lg flex items-center justify-center text-[#e9a13b]">
                                <img
                                    src="/images/logo.png"
                                    alt="Сказочная Карелия"
                                    class="h-12 w-12 md:h-14 md:w-14 object-contain rounded-full shadow-lg"
                                />
                            </div>
                            <span class="flex flex-col leading-none">
                                <span class="text-lg md:text-xl font-bold text-[#fdf8ef] tracking-wider uppercase">Сказочная</span>
                                <span class="text-lg md:text-xl font-bold text-[#e9a13b] tracking-wider uppercase">Карелия</span>
                            </span>
                        </div>
                        <p class="text-[#a89f92] leading-relaxed max-w-md">
                            Премиальная недвижимость для вашего комфорта. Дома для всей семьи на берегу Ладожского озера.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-lg font-bold mb-6 tracking-wider text-[#fdf8ef]">НАВИГАЦИЯ</h4>
                        <ul class="space-y-3 text-[#a89f92]">
                            <li>
                                <button @click="scrollToSection('properties')" class="hover:text-[#e9a13b] transition-colors">
                                    Дома
                                </button>
                            </li>
                            <li>
                                <button @click="scrollToSection('pricing')" class="hover:text-[#e9a13b] transition-colors">
                                    Цены
                                </button>
                            </li>
                            <li>
                                <button @click="scrollToSection('faq')" class="hover:text-[#e9a13b] transition-colors">
                                    Вопросы
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-lg font-bold mb-6 tracking-wider text-[#fdf8ef]">КОНТАКТЫ</h4>
                        <div class="space-y-3 text-[#a89f92]">
                            <p>
                                <a href="tel:+79999999999" class="hover:text-[#e9a13b] transition-colors">
                                    +7 (999) 999-99-99
                                </a>
                            </p>
                            <p>
                                <a href="mailto:info@uyutnydom.ru" class="hover:text-[#e9a13b] transition-colors">
                                    info@uyutnydom.ru
                                </a>
                            </p>
                            <p class="text-sm">Ежедневно 9:00-21:00</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#fdf8ef]/10 pt-6 md:pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-[#a89f92] text-sm mb-0 md:mb-0">
                        &copy; {{ new Date().getFullYear() }} Сказочная Карелия. Все права защищены.
                    </p>
                    <div class="flex space-x-6 text-sm text-[#a89f92]">
                        <a href="/policy" class="hover:text-[#e9a13b] transition-colors">Политика конфиденциальности</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <FloatingSupport />
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePropertiesStore } from '@/stores/propertiesStore';
import Header from '@/components/Header.vue';
import PropertyCard from '@/components/PropertyCard.vue';
import Faq from '@/components/Faq.vue';
import FloatingSupport from '@/components/FloatingSupport.vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

const route = useRoute();
const router = useRouter();
const store = usePropertiesStore();

// Фильтр категории
const filter = ref(route.query.category || 'all');

// Выбранные даты в календаре
const selectedDates = ref({ start: null, end: null });

// Даты, когда ВСЕ объекты заняты — блокируем в календаре
const fullyBookedDates = computed(() => {
    return store.fullyBookedDates.map(dateStr => ({
        start: new Date(dateStr + 'T00:00:00'),
        end: new Date(dateStr + 'T00:00:00'),
    }));
});

// Следим за изменениями в URL (когда приходят извне, например из хедера)
watch(() => route.query.category, (newCategory) => {
    const categoryValue = newCategory || 'all';
    if (filter.value !== categoryValue) {
        filter.value = categoryValue;
    }
}, { immediate: true });

// Реальная фильтрация объектов через store
const filteredProperties = computed(() => {
    let properties = store.properties;

    // Фильтр по категории
    if (filter.value !== 'all') {
        properties = properties.filter(p => p.category === filter.value);
    }

    // Фильтр по выбранным датам (через метод store)
    if (selectedDates.value.start && selectedDates.value.end) {
        properties = properties.filter(property => {
            return store.isPropertyAvailable(
                property.id,
                selectedDates.value.start,
                selectedDates.value.end
            );
        });
    }

    return properties;
});

// Функция для обновления фильтра (вызывается из кнопок на странице)
function updateFilter(newFilter) {
    filter.value = newFilter;

    // Обновляем URL без скролла (только query, без изменения пути)
    router.replace({
        path: '/',
        query: newFilter !== 'all' ? { category: newFilter } : {},
    }).catch(() => {});

    // Скроллим к секции объектов вручную
    nextTick(() => {
        const section = document.getElementById('properties');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

function formatDateDisplay(dateStr) {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('-');
    const months = ['янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
    return `${parseInt(day, 10)} ${months[parseInt(month, 10) - 1]} ${year}`;
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        router.replace({ hash: `#${sectionId}` }).catch(() => {});
    }
}

// Параллельная загрузка объектов и занятых дат
onMounted(async () => {
    await Promise.all([
        store.fetchProperties(),
        store.fetchDisabledDates(),
    ]);
});
</script>

<style scoped>
html {
    scroll-behavior: smooth;
}

#properties {
    scroll-margin-top: 100px;
}

#pricing {
    scroll-margin-top: 100px;
}

#faq {
    scroll-margin-top: 100px;
}
</style>
