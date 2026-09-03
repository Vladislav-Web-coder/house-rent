<template>
    <div class="min-h-screen bg-white">
        <!-- Header -->
        <Header />

        <!-- Hero Section -->
        <section class="relative h-screen flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0">
                <img
                    src="/images/hero-bg.jpeg"
                    alt="Спящий залив"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black/30"></div>
            </div>

            <div class="relative z-10 text-center text-white px-4">
                <h1 class="text-5xl md:text-7xl font-light mb-6 tracking-wide">
                    Спящий залив
                </h1>
                <p class="text-xl md:text-2xl font-light mb-8 max-w-2xl mx-auto">
                    Дома для всей семьи на берегу Ладоги — рядом с самыми интересными местами Карелии
                </p>
                <button
                    @click="scrollToSection('properties')"
                    class="inline-block bg-white text-gray-900 px-12 py-4 text-sm font-medium tracking-wider hover:bg-[#77c4db] hover:text-white transition-colors rounded-full cursor-pointer"
                >
                    ЗАБРОНИРОВАТЬ
                </button>
            </div>

            <div class="absolute bottom-12 left-1/2 -translate-x-1/2 animate-bounce">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-16 h-16 mb-4 text-[#283e46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span class="text-gray-800 font-medium">Можно с питомцами</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-16 h-16 mb-4 text-[#283e46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                        </svg>
                        <span class="text-gray-800 font-medium">Сауна</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-16 h-16 mb-4 text-[#283e46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                        <span class="text-gray-800 font-medium">SUP-доски</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-16 h-16 mb-4 text-[#283e46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-gray-800 font-medium">Лодки и пирс</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Date Filter Calendar -->
        <section class="container mx-auto px-6 -mt-10 relative z-20">
            <div class="bg-white rounded-2xl shadow-xl border-2 border-[#283e46]/10 p-6 md:p-8 max-w-3xl mx-auto">
                <h3 class="text-xl font-semibold text-[#283e46] mb-4 text-center">Выберите даты проживания</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Мы покажем только доступные объекты на выбранные даты. Даты, когда заняты все дома, будут недоступны.
                </p>

                <!-- Добавлен :disabled-dates — блокирует даты, когда ВСЕ объекты заняты -->
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
        <section id="properties" class="py-24 bg-stone-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-light mb-4">Наши дома</h2>
                    <p class="text-gray-600 text-lg">Продуманные до мелочей дома, тишина и карельская природа — всё, чтобы вы по-настоящему отдохнули</p>
                </div>

                <!-- Filters -->
                <div class="flex justify-center space-x-4 mb-12">
                    <button
                        @click="updateFilter('all')"
                        class="px-8 py-3 rounded-full border-2 transition-all font-medium"
                        :class="filter === 'all' ? 'bg-[#77c4db] text-white border-[#77c4db]' : 'border-gray-300 hover:border-[#77c4db]'"
                    >
                        Все дома
                    </button>
                    <button
                        @click="updateFilter('house')"
                        class="px-8 py-3 rounded-full border-2 transition-all font-medium"
                        :class="filter === 'house' ? 'bg-[#77c4db] text-white border-[#77c4db]' : 'border-gray-300 hover:border-[#77c4db]'"
                    >
                        Дома
                    </button>
                    <button
                        @click="updateFilter('apartment')"
                        class="px-8 py-3 rounded-full border-2 transition-all font-medium"
                        :class="filter === 'apartment' ? 'bg-[#77c4db] text-white border-[#77c4db]' : 'border-gray-300 hover:border-[#77c4db]'"
                    >
                        Квартиры
                    </button>
                </div>

                <!-- Message about filtered results -->
                <div v-if="selectedDates.start && selectedDates.end" class="text-center mb-8">
                    <p class="text-gray-600">
                        Показаны объекты, доступные с {{ formatDateDisplay(selectedDates.start) }} по {{ formatDateDisplay(selectedDates.end) }}
                    </p>
                </div>

                <!-- Loading state -->
                <div v-if="store.loading" class="text-center py-20">
                    <p class="text-gray-600 text-lg">Загрузка объектов...</p>
                </div>

                <!-- Properties Grid -->
                <div v-else-if="filteredProperties.length > 0" class="flex flex-col gap-12 max-w-6xl mx-auto">
                    <PropertyCard
                        v-for="prop in filteredProperties"
                        :key="prop.id"
                        :property="prop"
                    />
                </div>

                <div v-else class="text-center py-20">
                    <p class="text-gray-600 text-lg">
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
        <footer class="bg-[#283e46] text-white py-16">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 border-2 border-white rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-light tracking-wide">УЮТНЫЙ<span class="font-bold">ДОМ</span></span>
                        </div>
                        <p class="text-gray-300 leading-relaxed max-w-md">
                            Премиальная недвижимость для вашего комфорта. Дома для всей семьи на берегу Ладожского озера.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-lg font-bold mb-6 tracking-wider">НАВИГАЦИЯ</h4>
                        <ul class="space-y-3 text-gray-300">
                            <li>
                                <button @click="scrollToSection('properties')" class="hover:text-white transition-colors">
                                    Дома
                                </button>
                            </li>
                            <li>
                                <button @click="scrollToSection('pricing')" class="hover:text-white transition-colors">
                                    Цены
                                </button>
                            </li>
                            <li>
                                <button @click="scrollToSection('faq')" class="hover:text-white transition-colors">
                                    Вопросы
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-lg font-bold mb-6 tracking-wider">КОНТАКТЫ</h4>
                        <div class="space-y-3 text-gray-300">
                            <p>
                                <a href="tel:+79999999999" class="hover:text-white transition-colors">
                                    +7 (999) 999-99-99
                                </a>
                            </p>
                            <p>
                                <a href="mailto:info@uyutnydom.ru" class="hover:text-white transition-colors">
                                    info@uyutnydom.ru
                                </a>
                            </p>
                            <p class="text-sm">Ежедневно 9:00-21:00</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-600 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm mb-4 md:mb-0">
                        &copy; {{ new Date().getFullYear() }} УЮТНЫЙДОМ. Все права защищены.
                    </p>
                    <div class="flex space-x-6 text-sm text-gray-400">
                        <a href="/policy" class="hover:text-white transition-colors">Политика конфиденциальности</a>
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
