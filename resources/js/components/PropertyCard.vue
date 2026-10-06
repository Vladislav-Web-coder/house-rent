<template>
    <div class="group bg-white overflow-hidden hover:shadow-xl transition-all duration-300 rounded-2xl border border-[#251d12]/8 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2">

            <!-- Левая часть: фото ночного дома -->
            <div class="relative h-80 lg:h-[500px] overflow-hidden">
                <img
                    :src="currentImage"
                    :alt="property.title"
                    loading="lazy"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />

                <!--  Иконка плей ТОЛЬКО если текущий слайд — видео -->
                <div
                    v-if="currentMedia?.type === 'video'"
                    class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/30 transition-colors pointer-events-none"
                >
                    <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-[#e9a13b]/90 group-hover:bg-[#e9a13b] flex items-center justify-center shadow-2xl transition-all group-hover:scale-110">
                        <svg class="w-8 h-8 md:w-10 md:h-10 text-[#1a1206] ml-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>

                <!-- Кнопки навигации -->
                <button
                    v-if="galleryItems.length > 1"
                    @click.stop="prevSlide"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 hover:bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                >
                    <svg class="w-5 h-5 text-[#251d12]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <button
                    v-if="galleryItems.length > 1"
                    @click.stop="nextSlide"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 hover:bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                >
                    <svg class="w-5 h-5 text-[#251d12]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Индикаторы слайдов (показываем только если <= 15 медиа) -->
                <div
                    v-if="galleryItems.length > 1 && galleryItems.length <= 15"
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 bg-black/30 backdrop-blur-sm px-3 py-1.5 rounded-full z-10"
                >
                    <button
                        v-for="(item, index) in galleryItems"
                        :key="item.id || index"
                        @click.stop="currentSlide = index"
                        class="h-2 rounded-full transition-all relative"
                        :class="index === currentSlide ? 'bg-[#e9a13b] w-8' : 'bg-white/60 w-2 hover:bg-white/80'"
                    >
                        <svg
                            v-if="item.type === 'video' && index !== currentSlide"
                            class="absolute -top-1 left-1/2 -translate-x-1/2 w-3 h-3 text-white drop-shadow"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                </div>

                <!-- Если файлов больше 15 — показываем просто счётчик -->
                <div
                    v-if="galleryItems.length > 15"
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/40 backdrop-blur-sm px-4 py-2 rounded-full text-white text-sm font-medium z-10"
                >
                    {{ currentSlide + 1 }} / {{ galleryItems.length }}
                </div>

                <!--  Бейдж категории: янтарный, контрастный на тёмном фото -->
                <div class="absolute top-4 left-4 bg-[#e9a13b] px-3 py-1.5 rounded-full text-xs font-bold text-[#1a1206] z-10 shadow-lg">
                    {{ categoryLabel }}
                </div>
            </div>

            <!-- Правая часть: контент -->
            <div class="p-8 lg:p-12 flex flex-col justify-center">
                <h3 class="text-2xl lg:text-3xl font-medium text-[#251d12] mb-4">
                    {{ property.title }}
                </h3>

                <div class="text-[#251d12]/70 mb-8 leading-relaxed line-clamp-3">
                    {{ description }}
                </div>

                <!-- Характеристики -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <div class="flex flex-col items-center gap-2 p-3 bg-[#f5f2ec] rounded-xl">
                        <svg class="w-6 h-6 text-[#d18a2a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <div class="text-center">
                            <div class="text-sm font-bold text-[#251d12]">до {{ maxGuests }}</div>
                            <div class="text-xs text-[#6e6459]">гостей</div>
                        </div>
                    </div>

                    <div v-if="property.rooms" class="flex flex-col items-center gap-2 p-3 bg-[#f5f2ec] rounded-xl">
                        <svg class="w-6 h-6 text-[#d18a2a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <div class="text-center">
                            <div class="text-sm font-bold text-[#251d12]">{{ property.rooms }}</div>
                            <div class="text-xs text-[#6e6459]">{{ roomsLabel }}</div>
                        </div>
                    </div>

                    <div v-if="property.area" class="flex flex-col items-center gap-2 p-3 bg-[#f5f2ec] rounded-xl">
                        <svg class="w-6 h-6 text-[#d18a2a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                        <div class="text-center">
                            <div class="text-sm font-bold text-[#251d12]">{{ property.area }} м²</div>
                            <div class="text-xs text-[#6e6459]">площадь</div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-2 p-3 bg-[#f5f2ec] rounded-xl">
                        <svg class="w-6 h-6 text-[#d18a2a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div class="text-center">
                            <div class="text-sm font-bold text-[#251d12]">{{ property.min_stay || 1 }}+</div>
                            <div class="text-xs text-[#6e6459]">ночей</div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#251d12]/10 mb-8"></div>

                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-sm text-[#6e6459]">от</span>
                            <span class="text-3xl lg:text-4xl font-bold text-[#251d12]">{{ formatPrice(property.base_price) }}</span>
                            <span class="text-[#251d12]/60 text-base">₽ / ночь</span>
                        </div>
                    </div>
                    <router-link
                        :to="{ name: 'property', params: { id: property.id } }"
                        class="bg-[#e9a13b] text-[#1a1206] px-8 py-3 font-semibold hover:bg-[#d18a2a] transition-all duration-300 rounded-lg shadow-sm hover:shadow-md"
                        @click.stop
                    >
                        Забронировать
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { formatPrice } from '@/utils/format';

const props = defineProps({
    property: {
        type: Object,
        required: true
    }
});

const currentSlide = ref(0);

const galleryItems = computed(() => {
    const gallery = props.property.gallery || props.property.images || [];

    if (gallery.length > 0 && typeof gallery[0] === 'string') {
        return gallery.map(url => ({ type: 'image', url, thumb: url }));
    }

    return gallery;
});

const currentMedia = computed(() => {
    if (galleryItems.value.length > 0) {
        return galleryItems.value[currentSlide.value] || galleryItems.value[0];
    }

    if (props.property.main_image) {
        return {
            type: props.property.main_media_type || 'image',
            url: props.property.main_image,
            thumb: props.property.main_image,
        };
    }

    return { type: 'image', url: '/images/no-image.jpg', thumb: '/images/no-image.jpg' };
});

const currentImage = computed(() => {
    const media = currentMedia.value;

    if (!media) {
        return '/images/no-image.jpg';
    }

    if (media.type === 'video') {
        return media.thumb || '/images/no-image.jpg';
    }

    return media.url || media.thumb || '/images/no-image.jpg';
});

const maxGuests = computed(() => {
    return (props.property.max_adults || 0) + (props.property.max_children || 0);
});

const roomsLabel = computed(() => {
    const n = props.property.rooms;
    if (!n) return 'комнат';
    if (n % 10 === 1 && n % 100 !== 11) return 'комната';
    if ([2, 3, 4].includes(n % 10) && ![12, 13, 14].includes(n % 100)) return 'комнаты';
    return 'комнат';
});

const categoryLabel = computed(() => {
    if (props.property.category_label) {
        return props.property.category_label;
    }
    const labels = {
        'house': 'Дом',
        'apartment': 'Квартира',
    };
    return labels[props.property.category] || props.property.category;
});

const description = computed(() => {
    const raw = props.property.description || '';
    const text = raw.replace(/<[^>]*>/g, '').trim();
    return text || 'Уютное место для отдыха в окружении природы. Всё необходимое для комфортного проживания.';
});

function nextSlide() {
    if (galleryItems.value.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % galleryItems.value.length;
    }
}

function prevSlide() {
    if (galleryItems.value.length > 0) {
        currentSlide.value = (currentSlide.value - 1 + galleryItems.value.length) % galleryItems.value.length;
    }
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
