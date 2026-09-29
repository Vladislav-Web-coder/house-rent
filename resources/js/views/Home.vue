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
                <div class="absolute inset-0 bg-[#0e1a24]/20"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#0e1a24]/90 via-[#0e1a24]/30 to-transparent"></div>
            </div>

            <div class="relative z-10 w-full">
                <div class="container mx-auto px-4 md:px-6 pb-20 md:pb-28 pt-36 md:pt-52 text-center">
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

                    <!-- ✅ Можно с питомцами: лапки -->
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <ellipse cx="4.5" cy="10" rx="2" ry="2.4"/>
                            <ellipse cx="9" cy="6.3" rx="2" ry="2.4"/>
                            <ellipse cx="15" cy="6.3" rx="2" ry="2.4"/>
                            <ellipse cx="19.5" cy="10" rx="2" ry="2.4"/>
                            <path stroke-linejoin="round" d="M12 11c-2.5 0-4.6 1.8-5.6 3.9-.9 2-.3 4.6 2.1 4.6 1.4 0 2.4-.9 3.5-.9s2.1.9 3.5.9c2.4 0 3-2.6 2.1-4.6-1-2.1-3.1-3.9-5.6-3.9z"/>
                        </svg>
                        <span class="text-sm md:text-base text-[#eef3f5] font-medium">Можно с питомцами</span>
                    </div>

                    <!-- ✅ Сауна и баня: пар над шайкой -->
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M8 3c-1.2 1.2 1.2 2.3 0 3.5"/>
                            <path stroke-linecap="round" d="M12 3c-1.2 1.2 1.2 2.3 0 3.5"/>
                            <path stroke-linecap="round" d="M16 3c-1.2 1.2 1.2 2.3 0 3.5"/>
                            <path stroke-linejoin="round" d="M5.5 10h13l-1.3 8.3a2 2 0 01-2 1.7H8.8a2 2 0 01-2-1.7L5.5 10z"/>
                            <path stroke-linecap="round" d="M6.3 14.5h11.4"/>
                        </svg>
                        <span class="text-sm md:text-base text-[#eef3f5] font-medium">Сауна и баня</span>
                    </div>

                    <!-- ✅ SUP-доски: доска + весло -->
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <!-- Доска (вид сверху): вытянутый овал с стрингером -->
                            <path d="M9.5 2c2.2 3 3.3 6.2 3.3 9.5s-1.1 6.5-3.3 9.5c-2.2-3-3.3-6.2-3.3-9.5S7.3 5 9.5 2z"/>
                            <path d="M9.5 5v13"/>
                            <!-- Весло: Т-образная ручка, shaft, лопасть -->
                            <path d="M16 4.5h3"/>
                            <path d="M17.5 4.5v11"/>
                            <path d="M17.5 15.5c1.3 0 2.1 1 2.1 2.3 0 1.6-.9 2.7-2.1 2.7s-2.1-1.1-2.1-2.7c0-1.3.8-2.3 2.1-2.3z"/>
                        </svg>
                        <span class="text-sm md:text-base text-[#eef3f5] font-medium">SUP-доски</span>
                    </div>

                    <!-- ✅ Лодки и пирс: лодка с вёслами на волнах -->
                    <div class="flex flex-col items-center text-center group">
                        <svg class="w-12 h-12 md:w-16 md:h-16 mb-3 md:mb-4 text-[#e9a13b] transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linejoin="round" d="M4 14h16c-.5 2-1.5 3.5-3 4.5H7C5.5 17.5 4.5 16 4 14z"/>
                            <path stroke-linecap="round" d="M9 14L6 10.5"/>
                            <path stroke-linecap="round" d="M15 14l3-3.5"/>
                            <path stroke-linecap="round" d="M3 21c1.5-1.2 3-1.2 4.5 0s3 1.2 4.5 0 3-1.2 4.5 0 3 1.2 4.5 0"/>
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

                <!-- ✅ Фильтры: "Дома" и "Квартиры" только если квартиры есть на бэке -->
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
                        v-if="hasApartments"
                        @click="updateFilter('house')"
                        class="px-5 py-2 md:px-8 md:py-3 rounded-full border-2 transition-all font-medium text-sm md:text-base"
                        :class="filter === 'house'
                            ? 'bg-[#e9a13b] text-[#1a1206] border-[#e9a13b]'
                            : 'border-[#251d12]/20 text-[#251d12] hover:border-[#e9a13b] hover:text-[#d18a2a]'"
                    >
                        Дома
                    </button>
                    <button
                        v-if="hasApartments"
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

        <!-- ✅ НОВАЯ СЕКЦИЯ: Интересное рядом (перед FAQ) -->
        <InterestingNearby />

        <!-- FAQ Section -->
        <section id="faq" class="scroll-mt-[100px]">
            <Faq />
        </section>

        <!-- Footer -->
        <footer class="bg-[#0c111c] text-white py-12 md:py-16">
            <div class="container mx-auto px-4 md:px-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 md:gap-12 mb-10 md:mb-12">
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-3 mb-6">
                            <img
                                src="/images/logo.png"
                                alt="Сказочная Карелия"
                                class="h-12 w-12 md:h-14 md:w-14 object-contain rounded-full shadow-lg"
                            />
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
                            <!-- ✅ Вместо "Цены" — новая секция -->
                            <li>
                                <button @click="scrollToSection('nearby')" class="hover:text-[#e9a13b] transition-colors">
                                    Интересное рядом
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
import { parseIsoDate, formatDateDisplay } from '@/utils/date';
import Header from '@/components/Header.vue';
import PropertyCard from '@/components/PropertyCard.vue';
import Faq from '@/components/Faq.vue';
import FloatingSupport from '@/components/FloatingSupport.vue';
import DateRangePicker from '@/components/DateRangePicker.vue';
import InterestingNearby from '@/components/InterestingNearby.vue';

const route = useRoute();
const router = useRouter();
const store = usePropertiesStore();

// Фильтр категории
const filter = ref(route.query.category || 'all');

// Выбранные даты в календаре
const selectedDates = ref({ start: null, end: null });

// ✅ Есть ли квартиры среди загруженных объектов
const hasApartments = computed(() =>
    store.properties.some(p => p.category === 'apartment')
);

// Даты, когда ВСЕ объекты заняты — блокируем в календаре
const fullyBookedDates = computed(() => {
    return store.fullyBookedDates.map(dateStr => ({
        start: parseIsoDate(dateStr),
        end: parseIsoDate(dateStr),
    }));
});

// Следим за изменениями в URL (когда приходят извне, например из хедера)
watch(() => route.query.category, (newCategory) => {
    const categoryValue = newCategory || 'all';
    if (filter.value !== categoryValue) {
        filter.value = categoryValue;
    }
}, { immediate: true });

// ✅ Защита: если квартир нет, а в URL пришёл фильтр apartment — сбрасываем
watch(hasApartments, (exists) => {
    if (!exists && filter.value === 'apartment') {
        filter.value = 'all';
        router.replace({ path: '/', query: {} }).catch(() => {});
    }
});

// Реальная фильтрация объектов через store
const filteredProperties = computed(() => {
    let properties = store.properties;

    if (filter.value !== 'all') {
        properties = properties.filter(p => p.category === filter.value);
    }

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

function updateFilter(newFilter) {
    filter.value = newFilter;

    router.replace({
        path: '/',
        query: newFilter !== 'all' ? { category: newFilter } : {},
    }).catch(() => {});

    nextTick(() => {
        const section = document.getElementById('properties');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        router.replace({ hash: `#${sectionId}` }).catch(() => {});
    }
}

onMounted(async () => {
    await Promise.all([
        store.fetchProperties(),
        store.fetchDisabledDates(),
    ]);

    if (route.hash) {
        nextTick(() => {
            setTimeout(() => {
                const el = document.querySelector(route.hash);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        });
    }
});
</script>

<style scoped>
html {
    scroll-behavior: smooth;
}

#properties {
    scroll-margin-top: 100px;
}

#nearby {
    scroll-margin-top: 100px;
}

#faq {
    scroll-margin-top: 100px;
}
</style>
