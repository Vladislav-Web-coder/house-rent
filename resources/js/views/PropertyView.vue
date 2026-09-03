<template>
    <div class="min-h-screen bg-white">
        <Header />

        <div v-if="isLoading" class="py-32 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#77c4db]/20 border-t-[#77c4db] mx-auto"></div>
            <p class="mt-4 text-[#666666]">Загрузка информации об объекте...</p>
        </div>

        <div v-else-if="error" class="py-32 text-center">
            <p class="text-red-600 text-lg mb-4">{{ error }}</p>
            <router-link to="/" class="text-[#77c4db] hover:text-[#5fb5d1] font-medium transition-colors">
                ← Вернуться на главную
            </router-link>
        </div>

        <div v-else-if="property" class="pb-20">
            <!-- Хлебные крошки -->
            <div class="bg-white border-b border-gray-100">
                <div class="container mx-auto px-6 py-4">
                    <nav class="flex text-sm text-[#666666]">
                        <router-link to="/" class="hover:text-[#283e46] transition-colors">Главная</router-link>
                        <span class="mx-3">/</span>
                        <span class="text-[#283e46] font-medium truncate">{{ property.title }}</span>
                    </nav>
                </div>
            </div>

            <!-- Галерея фото и видео -->
            <div class="container mx-auto px-6 mt-8 mb-12">
                <div class="relative h-[400px] md:h-[500px] lg:h-[600px] overflow-hidden bg-gray-900 rounded-2xl group">

                    <!-- Видео: загружается только при клике на play -->
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

                    <!-- Изображение -->
                    <img
                        v-else
                        :key="currentMedia?.id || 'default'"
                        :src="currentMedia?.url || property.main_image"
                        :alt="property.title"
                        loading="lazy"
                        class="w-full h-full object-cover transition-opacity duration-300"
                        @error="handleImageError"
                    />

                    <!-- Кнопки навигации -->
                    <button v-if="property.gallery && property.gallery.length > 1" @click="prevSlide"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all opacity-0 group-hover:opacity-100 z-10">
                        <svg class="w-6 h-6 text-[#283e46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button v-if="property.gallery && property.gallery.length > 1" @click="nextSlide"
                            class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all opacity-0 group-hover:opacity-100 z-10">
                        <svg class="w-6 h-6 text-[#283e46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <!-- Индикаторы -->
                    <div v-if="property.gallery && property.gallery.length > 1" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
                        <button v-for="(media, index) in property.gallery" :key="media.id" @click="switchSlide(index)"
                                class="transition-all duration-300 rounded-full"
                                :class="index === currentSlide ? 'w-12 h-2 bg-white' : 'w-2 h-2 bg-white/60 hover:bg-white/80'">
                        </button>
                    </div>

                    <!-- Счётчик -->
                    <div v-if="property.gallery && property.gallery.length > 1" class="absolute top-6 right-6 bg-white/90 backdrop-blur-sm px-4 py-2 text-sm font-medium text-[#283e46] rounded-lg flex items-center gap-2 z-10">
                        <svg v-if="currentMedia?.type === 'video'" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ currentSlide + 1 }} / {{ property.gallery.length }}
                    </div>
                </div>

                <!-- Миниатюры -->
                <div v-if="property.gallery && property.gallery.length > 1" class="flex space-x-3 mt-4 overflow-x-auto pb-2 scrollbar-hide">
                    <button
                        v-for="(media, index) in property.gallery"
                        :key="media.id"
                        @click="switchSlide(index)"
                        class="relative flex-shrink-0 w-20 h-20 md:w-24 md:h-24 rounded-xl overflow-hidden border-2 transition-all"
                        :class="index === currentSlide ? 'border-[#77c4db] opacity-100' : 'border-transparent opacity-60 hover:opacity-100'"
                    >
                        <img
                            v-if="media.thumb"
                            :src="media.thumb"
                            :alt="`Медиа ${index + 1}`"
                            loading="lazy"
                            class="w-full h-full object-cover"
                        />

                        <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <svg v-if="media.type === 'video'" class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>

                        <!-- Иконка play для видео -->
                        <div
                            v-if="media.type === 'video'"
                            class="absolute inset-0 flex items-center justify-center bg-black/30 pointer-events-none"
                        >
                            <div class="w-10 h-10 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-[#77c4db] ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Остальная страница без изменений -->
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-12">
                    <div class="xl:col-span-8 space-y-12">
                        <div>
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                                <div>
                                    <h1 class="text-3xl md:text-4xl font-medium text-[#283e46] mb-3">{{ property.title }}</h1>
                                    <div class="flex items-center text-[#666666] text-lg">
                                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ property.address }}
                                    </div>
                                </div>
                                <span class="inline-flex items-center bg-[#ebf7fb] text-[#283e46] px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap w-fit">
                                    {{ property.category_label }}
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-4 md:gap-8 mt-8 pt-8 border-t border-gray-200">
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <div class="text-3xl font-medium text-[#283e46] mb-1">{{ (property.max_adults || 0) + (property.max_children || 0) }}</div>
                                    <div class="text-sm text-[#666666]">Гостей</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <div class="text-3xl font-medium text-[#283e46] mb-1">{{ property.area ? property.area + ' м²' : '—' }}</div>
                                    <div class="text-sm text-[#666666]">Площадь</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <div class="text-3xl font-medium text-[#283e46] mb-1">{{ property.rooms || '—' }}</div>
                                    <div class="text-sm text-[#666666]">Комнат</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <div class="text-3xl font-medium text-[#283e46] mb-1">{{ property.min_stay || 1 }}+</div>
                                    <div class="text-sm text-[#666666]">Ночей</div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-2xl md:text-3xl font-medium text-[#283e46] mb-6">О доме</h2>
                            <div class="prose prose-lg max-w-none text-[#666666] leading-relaxed" v-html="property.description"></div>
                        </div>

                        <div>
                            <h2 class="text-2xl md:text-3xl font-medium text-[#283e46] mb-6">Расположение</h2>
                            <YandexMap
                                :latitude="property.latitude"
                                :longitude="property.longitude"
                                :address="property.address"
                                :title="property.title"
                                :zoom="15"
                            />
                        </div>
                    </div>

                    <div class="xl:col-span-4">
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

            <div class="container mx-auto px-6 mt-20">
                <Faq />
            </div>
        </div>
        <FloatingSupport />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Header from '@/components/Header.vue';
import BookingForm from '@/components/BookingForm.vue';
import Faq from '@/components/Faq.vue';
import FloatingSupport from '@/components/FloatingSupport.vue';
import YandexMap from "@/components/YandexMap.vue";

const route = useRoute();
const property = ref(null);
const isLoading = ref(true);
const error = ref(null);
const currentSlide = ref(0);

// Текущее медиа (фото или видео)
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

// Останавливаем текущее видео
function pauseCurrentVideo() {
    const currentVideo = document.querySelector('.video-player');
    if (currentVideo && !currentVideo.paused) {
        currentVideo.pause();
    }
}

// Обработчики видео
function onVideoPlay() {
    // Видео начало воспроизводиться
    console.log('Видео запущено');
}

function onVideoPause() {
    // Видео остановлено
    console.log('Видео остановлено');
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

async function loadProperty() {
    isLoading.value = true;
    error.value = null;
    currentSlide.value = 0;

    try {
        const response = await axios.get(`/api/properties/${route.params.id}`);
        property.value = response.data.data;
        window.scrollTo({ top: 0, behavior: 'instant' });
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

onMounted(() => {
    loadProperty();
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.video-player {
    background: #000;
}

.video-player::-webkit-media-controls-panel {
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
}
</style>
