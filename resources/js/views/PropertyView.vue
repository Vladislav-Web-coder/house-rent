<template>
    <div class="min-h-screen bg-white">
        <Header />

        <div v-if="isLoading" class="py-32 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#e9a13b]/20 border-t-[#e9a13b] mx-auto"></div>
            <p class="mt-4 text-[#6e6459]">Загрузка информации об объекте...</p>
        </div>

        <div v-else-if="error" class="py-32 text-center px-4">
            <p class="text-red-600 text-lg mb-4">{{ error }}</p>
            <router-link to="/" class="text-[#d18a2a] hover:text-[#b5741d] font-medium transition-colors">
                ← Вернуться на главную
            </router-link>
        </div>

        <div v-else-if="property" class="pb-16 md:pb-20">
            <!-- Хлебные крошки -->
            <div class="bg-white border-b border-[#251d12]/8">
                <div class="container mx-auto px-4 md:px-6 py-3 md:py-4">
                    <nav class="flex text-xs md:text-sm text-[#6e6459]">
                        <router-link to="/" class="hover:text-[#d18a2a] transition-colors">Главная</router-link>
                        <span class="mx-2 md:mx-3 text-[#251d12]/30">/</span>
                        <span class="text-[#251d12] font-medium truncate">{{ property.title }}</span>
                    </nav>
                </div>
            </div>

            <!-- Галерея фото и видео -->
            <div class="container mx-auto px-4 md:px-6 mt-4 md:mt-8 mb-6 md:mb-12">
                <div class="max-w-3xl md:max-w-5xl xl:max-w-6xl mx-auto">
                    <div class="relative aspect-[4/3] sm:aspect-[16/10] md:aspect-[16/9] overflow-hidden bg-[#0e1a24] rounded-xl md:rounded-2xl group">
                        <video
                            v-if="currentMedia?.type === 'video'"
                            :key="currentMedia.id"
                            :src="currentMedia.url"
                            :poster="currentMedia.thumb || undefined"
                            controls
                            playsinline
                            controlsList="nodownload"
                            preload="metadata"
                            class="w-full h-full object-cover video-player"
                        >
                            Ваш браузер не поддерживает воспроизведение видео.
                        </video>

                        <!-- Кнопка полноэкранного просмотра для видео -->
                        <button
                            v-if="currentMedia?.type === 'video'"
                            @click="openLightbox"
                            class="absolute top-3 left-3 md:top-6 md:left-6 bg-[#0e1a24]/70 hover:bg-[#e9a13b] hover:text-[#1a1206] backdrop-blur-sm px-3 py-2 rounded-lg text-xs md:text-sm font-medium text-[#fdf8ef] flex items-center gap-2 transition-colors z-10"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            На весь экран
                        </button>

                        <!-- Изображение: клик открывает лайтбокс -->
                        <img
                            v-else
                            :key="currentMedia?.id || 'default'"
                            :src="currentMedia?.url || property.main_image"
                            :alt="property.title"
                            loading="lazy"
                            class="w-full h-full object-cover cursor-zoom-in transition-transform duration-500 group-hover:scale-[1.02]"
                            @click="openLightbox"
                            @error="handleImageError"
                        />

                        <!-- Подсказка зума при наведении (только для фото) -->
                        <div
                            v-if="currentMedia?.type !== 'video'"
                            class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity bg-black/20"
                        >
                            <div class="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-[#251d12]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Стрелки: ТОЛЬКО для фото -->
                        <button v-if="property.gallery && property.gallery.length > 1 && currentMedia?.type !== 'video'" @click.stop="prevSlide"
                                class="absolute left-3 md:left-4 top-1/2 -translate-y-1/2 w-9 h-9 md:w-12 md:h-12 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all opacity-100 md:opacity-0 md:group-hover:opacity-100 z-10">
                            <svg class="w-4 h-4 md:w-6 md:h-6 text-[#251d12]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button v-if="property.gallery && property.gallery.length > 1 && currentMedia?.type !== 'video'" @click.stop="nextSlide"
                                class="absolute right-3 md:right-4 top-1/2 -translate-y-1/2 w-9 h-9 md:w-12 md:h-12 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all opacity-100 md:opacity-0 md:group-hover:opacity-100 z-10">
                            <svg class="w-4 h-4 md:w-6 md:h-6 text-[#251d12]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>

                        <!-- Точки-индикаторы: ТОЛЬКО для фото -->
                        <div v-if="property.gallery && property.gallery.length > 1 && currentMedia?.type !== 'video'" class="absolute bottom-3 md:bottom-6 left-1/2 -translate-x-1/2 flex space-x-1.5 md:space-x-2 z-10">
                            <button v-for="(media, index) in property.gallery" :key="media.id" @click.stop="switchSlide(index)"
                                    class="transition-all duration-300 rounded-full"
                                    :class="index === currentSlide ? 'w-6 md:w-12 h-1.5 md:h-2 bg-[#e9a13b]' : 'w-1.5 md:w-2 h-1.5 md:h-2 bg-white/60 hover:bg-white/80'">
                            </button>
                        </div>

                        <!-- Счётчик -->
                        <div v-if="property.gallery && property.gallery.length > 1" class="absolute top-3 right-3 md:top-6 md:right-6 bg-[#0e1a24]/70 backdrop-blur-sm px-2.5 py-1 md:px-4 md:py-2 text-[10px] md:text-sm font-medium text-[#fdf8ef] rounded-lg flex items-center gap-1.5 md:gap-2 z-10">
                            <svg v-if="currentMedia?.type === 'video'" class="w-3 h-3 md:w-4 md:h-4 text-[#e9a13b]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <svg v-else class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ currentSlide + 1 }} / {{ property.gallery.length }}
                        </div>
                    </div>

                    <!-- Миниатюры -->
                    <div
                        v-if="property.gallery && property.gallery.length > 1"
                        ref="thumbsContainer"
                        class="flex space-x-2 md:space-x-3 mt-2 md:mt-4 overflow-x-auto pb-2 scrollbar-hide"
                    >
                        <button
                            v-for="(media, index) in property.gallery"
                            :key="media.id"
                            @click="switchSlide(index)"
                            class="relative flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 md:w-24 md:h-24 rounded-lg md:rounded-xl overflow-hidden border-2 transition-all"
                            :class="index === currentSlide ? 'border-[#e9a13b] opacity-100' : 'border-transparent opacity-60 hover:opacity-100'"
                        >
                            <img
                                v-if="media.thumb"
                                :src="media.thumb"
                                :alt="`Медиа ${index + 1}`"
                                loading="lazy"
                                class="w-full h-full object-cover"
                            />

                            <div v-else class="w-full h-full bg-[#f5f2ec] flex items-center justify-center">
                                <svg v-if="media.type === 'video'" class="w-5 h-5 md:w-8 md:h-8 text-[#d18a2a]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5 md:w-8 md:h-8 text-[#6e6459]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>

                            <div
                                v-if="media.type === 'video'"
                                class="absolute inset-0 flex items-center justify-center bg-black/30 pointer-events-none"
                            >
                                <div class="w-7 h-7 md:w-10 md:h-10 rounded-full bg-[#e9a13b] flex items-center justify-center shadow-lg">
                                    <svg class="w-3.5 h-3.5 md:w-5 md:h-5 text-[#1a1206] ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Лайтбокс -->
            <Lightbox
                :open="lightboxOpen"
                :items="property?.gallery || []"
                :start-index="currentSlide"
                @close="lightboxOpen = false"
            />

            <!-- Основной контент -->
            <div class="container mx-auto px-4 md:px-6">
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 xl:gap-12">
                    <div class="xl:col-span-8 space-y-10 xl:space-y-12">

                        <!-- ✅ 1. Заголовок и характеристики (первыми) -->
                        <div>
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                                <div>
                                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-medium text-[#251d12] mb-3">{{ property.title }}</h1>
                                    <div class="flex items-center text-[#6e6459] text-base md:text-lg">
                                        <svg class="w-5 h-5 mr-2 flex-shrink-0 text-[#d18a2a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ property.address }}
                                    </div>
                                </div>
                                <span class="inline-flex items-center bg-[#e9a13b] text-[#1a1206] px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap w-fit shadow-sm">
                                    {{ property.category_label }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mt-6 md:mt-8 pt-6 md:pt-8 border-t border-[#251d12]/10">
                                <div class="text-center p-3 md:p-4 bg-[#f5f2ec] rounded-xl">
                                    <div class="text-2xl md:text-3xl font-medium text-[#251d12] mb-1">{{ (property.max_adults || 0) + (property.max_children || 0) }}</div>
                                    <div class="text-xs md:text-sm text-[#6e6459]">Гостей</div>
                                </div>
                                <div class="text-center p-3 md:p-4 bg-[#f5f2ec] rounded-xl">
                                    <div class="text-2xl md:text-3xl font-medium text-[#251d12] mb-1">{{ property.area ? property.area + ' м²' : '—' }}</div>
                                    <div class="text-xs md:text-sm text-[#6e6459]">Площадь</div>
                                </div>
                                <div class="text-center p-3 md:p-4 bg-[#f5f2ec] rounded-xl">
                                    <div class="text-2xl md:text-3xl font-medium text-[#251d12] mb-1">{{ property.rooms || '—' }}</div>
                                    <div class="text-xs md:text-sm text-[#6e6459]">Комнат</div>
                                </div>
                                <div class="text-center p-3 md:p-4 bg-[#f5f2ec] rounded-xl">
                                    <div class="text-2xl md:text-3xl font-medium text-[#251d12] mb-1">{{ property.min_stay || 1 }}+</div>
                                    <div class="text-xs md:text-sm text-[#6e6459]">Ночей</div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ 2. О доме: сворачиваемое описание -->
                        <div>
                            <h2 class="text-xl md:text-2xl lg:text-3xl font-medium text-[#251d12] mb-4 md:mb-6">О доме</h2>

                            <div class="relative">
                                <!-- Обёртка с ограничением высоты -->
                                <div
                                    ref="descriptionRef"
                                    class="overflow-hidden transition-[max-height] duration-500 ease-in-out"
                                    :style="{ maxHeight: descriptionExpanded ? fullDescriptionHeight + 'px' : COLLAPSED_HEIGHT + 'px' }"
                                >
                                    <!-- HTML-описание (из rich-редактора) -->
                                    <div
                                        v-if="descriptionIsHtml"
                                        class="prose prose-lg max-w-none text-[#6e6459] leading-relaxed property-description"
                                        v-html="property.description"
                                    ></div>
                                    <!-- Plain text: сохраняем пробелы и переносы -->
                                    <div
                                        v-else
                                        class="text-[#6e6459] leading-relaxed whitespace-pre-wrap text-base md:text-lg"
                                    >{{ property.description }}</div>
                                </div>

                                <!-- Градиентное затемнение внизу, когда свёрнуто -->
                                <div
                                    v-if="!descriptionExpanded && descriptionOverflows"
                                    class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-white to-transparent pointer-events-none"
                                ></div>
                            </div>

                            <!-- Кнопка развернуть / свернуть -->
                            <button
                                v-if="descriptionOverflows"
                                @click="descriptionExpanded = !descriptionExpanded"
                                class="mt-3 inline-flex items-center gap-2 text-[#d18a2a] hover:text-[#b5741d] font-medium transition-colors"
                            >
                                {{ descriptionExpanded ? 'Свернуть' : 'Читать далее' }}
                                <svg class="w-4 h-4 transition-transform duration-300" :class="descriptionExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- ✅ 3. Расположение -->
                        <div>
                            <h2 class="text-xl md:text-2xl lg:text-3xl font-medium text-[#251d12] mb-4 md:mb-6">Расположение</h2>
                            <YandexMap
                                :latitude="property.latitude"
                                :longitude="property.longitude"
                                :address="property.address"
                                :title="property.title"
                                :zoom="15"
                            />
                        </div>
                    </div>

                    <!-- Форма бронирования -->
                    <div class="xl:col-span-4" id="booking">
                        <div class="xl:sticky xl:top-24">
                            <BookingForm
                                :property-id="property.id"
                                :unavailable-dates="property.unavailable_dates || []"
                                :max-adults="property.max_adults || 2"
                                :max-children="property.max_children || 0"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="container mx-auto px-4 md:px-6 mt-16 md:mt-20">
                <Faq />
            </div>
        </div>

        <!-- ✅ ФУТЕР: как на главной странице -->
        <footer class="bg-[#0c111c] text-white pt-12 md:pt-16 pb-28 xl:pb-16">
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
                                <button @click="goToSection('properties')" class="hover:text-[#e9a13b] transition-colors">
                                    Дома
                                </button>
                            </li>
                            <li>
                                <button @click="goToSection('nearby')" class="hover:text-[#e9a13b] transition-colors">
                                    Интересное рядом
                                </button>
                            </li>
                            <li>
                                <button @click="goToSection('faq')" class="hover:text-[#e9a13b] transition-colors">
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
                    <p class="text-[#a89f92] text-sm">
                        &copy; {{ new Date().getFullYear() }} Сказочная Карелия. Все права защищены.
                    </p>
                    <div class="flex space-x-6 text-sm text-[#a89f92]">
                        <router-link to="/policy" class="hover:text-[#e9a13b] transition-colors">Политика конфиденциальности</router-link>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Мобильная нижняя панель с ценой и CTA -->
        <div v-if="property && !isLoading" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-[#251d12]/10 px-4 py-3 xl:hidden shadow-[0_-4px_20px_rgba(37,29,18,0.08)]">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs text-[#6e6459]">от</div>
                    <div class="text-lg font-bold text-[#251d12] leading-tight">
                        {{ formatPrice(property.base_price) }} ₽
                        <span class="text-sm font-normal text-[#6e6459]">/ ночь</span>
                    </div>
                </div>
                <button
                    @click="scrollToBooking"
                    class="flex-shrink-0 bg-[#e9a13b] text-[#1a1206] px-6 py-3 rounded-lg font-semibold hover:bg-[#d18a2a] transition-colors"
                >
                    Забронировать
                </button>
            </div>
        </div>

        <FloatingSupport raised />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchProperty } from '@/api';
import { formatPrice } from '@/utils/format';
import { useGallery } from '@/composables/useGallery';
import Header from '@/components/Header.vue';
import BookingForm from '@/components/BookingForm.vue';
import Faq from '@/components/Faq.vue';
import FloatingSupport from '@/components/FloatingSupport.vue';
import YandexMap from "@/components/YandexMap.vue";
import Lightbox from '@/components/Lightbox.vue';

const route = useRoute();
const router = useRouter();
const property = ref(null);
const isLoading = ref(true);
const error = ref(null);
const currentSlide = ref(0);
const lightboxOpen = ref(false);
const thumbsContainer = ref(null);

// ✅ Сворачивание описания
const COLLAPSED_HEIGHT = 320; // высота свёрнутого блока, px
const descriptionRef = ref(null);
const descriptionExpanded = ref(false);
const descriptionOverflows = ref(false);
const fullDescriptionHeight = ref(0);

const currentMedia = computed(() => {
    if (property.value?.gallery?.length > 0) {
        return property.value.gallery[currentSlide.value];
    }
    if (property.value?.main_image) {
        return {
            id: 'main',
            type: property.value.main_media_type || 'image',
            url: property.value.main_image,
            thumb: property.value.main_image,
        };
    }
    return null;
});

// ✅ Определяем, содержит ли описание HTML-теги
const descriptionIsHtml = computed(() => {
    return /<[a-z!\/][^>]*>/i.test(property.value?.description || '');
});

// ✅ Замер реальной высоты описания и нужно ли сворачивать
function measureDescription() {
    const el = descriptionRef.value;
    if (!el) return;
    fullDescriptionHeight.value = el.scrollHeight;
    descriptionOverflows.value = el.scrollHeight > COLLAPSED_HEIGHT + 40;
}

function pauseCurrentVideo() {
    const currentVideo = document.querySelector('.video-player');
    if (currentVideo && !currentVideo.paused) {
        currentVideo.pause();
    }
}

function nextSlide() {
    if (property.value?.gallery?.length) {
        pauseCurrentVideo();
        currentSlide.value = (currentSlide.value + 1) % property.value.gallery.length;
    }
}

function prevSlide() {
    if (property.value?.gallery?.length) {
        pauseCurrentVideo();
        currentSlide.value = (currentSlide.value - 1 + property.value.gallery.length) % property.value.gallery.length;
    }
}

function switchSlide(index) {
    pauseCurrentVideo();
    currentSlide.value = index;
}

function handleImageError(event) {
    event.target.src = '/images/fallback-property.jpg';
}

function formatPrice(price) {
    if (!price) return '0';
    return new Intl.NumberFormat('ru-RU').format(price);
}

function scrollToBooking() {
    const el = document.getElementById('booking');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ✅ Переход к секциям главной из футера
function goToSection(sectionId) {
    router.push({ path: '/', hash: `#${sectionId}` }).catch(() => {});
}

function openLightbox() {
    lightboxOpen.value = true;
}

async function loadProperty() {
    isLoading.value = true;
    error.value = null;
    currentSlide.value = 0;
    descriptionExpanded.value = false;

    try {
        const response = await axios.get(`/api/properties/${route.params.id}`);
        property.value = response.data.data;
        window.scrollTo({ top: 0, behavior: 'instant' });

        // Замеряем описание после рендера
        nextTick(() => {
            measureDescription();
        });
    } catch (err) {
        error.value = err.response?.status === 404
            ? 'Объект не найден.'
            : 'Не удалось загрузить информацию.';
    } finally {
        isLoading.value = false;
    }
}

watch(() => route.params.id, (newId) => {
    if (newId) {
        loadProperty();
    }
});

watch(currentSlide, (index) => {
    nextTick(() => {
        scrollThumbIntoView(index);
    });
});

function scrollThumbIntoView(index) {
    const container = thumbsContainer.value;
    if (!container) return;

    const thumb = container.children[index];
    if (!thumb) return;

    const target = thumb.offsetLeft - container.clientWidth / 2 + thumb.clientWidth / 2;
    container.scrollTo({ left: Math.max(0, target), behavior: 'smooth' });
}

// Перезамер при изменении размера окна (текст переносится иначе)
function onResize() {
    measureDescription();
}

onMounted(() => {
    loadProperty();
    window.addEventListener('resize', onResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', onResize);
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.video-player {
    background: #0e1a24;
}

.video-player::-webkit-media-controls-panel {
    background: linear-gradient(to top, rgba(14, 26, 36, 0.7), transparent);
}

.property-description :deep(h2) {
    color: #251d12;
    font-weight: 500;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.property-description :deep(h3) {
    color: #251d12;
    font-weight: 500;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}

.property-description :deep(p) {
    margin-bottom: 1rem;
    line-height: 1.8;
}

.property-description :deep(ul),
.property-description :deep(ol) {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.property-description :deep(li) {
    margin-bottom: 0.5rem;
}

.property-description :deep(a) {
    color: #d18a2a;
    text-decoration: underline;
}

.property-description :deep(a:hover) {
    color: #b5741d;
}

.property-description :deep(strong) {
    color: #251d12;
    font-weight: 600;
}
</style>
