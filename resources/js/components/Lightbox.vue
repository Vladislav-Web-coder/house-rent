<template>
    <Teleport to="body">
        <Transition name="lightbox-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-[100] bg-[#0c111c]/[.97] backdrop-blur-sm flex flex-col"
            >
                <!-- Верхняя панель -->
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="text-sm font-medium text-[#fdf8ef]">
                        {{ index + 1 }} / {{ items.length }}
                    </div>
                    <button
                        @click="close"
                        class="w-10 h-10 rounded-full bg-white/10 hover:bg-[#e9a13b] hover:text-[#1a1206] text-white flex items-center justify-center transition-colors"
                        aria-label="Закрыть"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Контент -->
                <div
                    class="flex-1 relative flex items-center justify-center px-2 md:px-16 pb-2 min-h-0"
                    @click.self="close"
                    @touchstart="onTouchStart"
                    @touchend="onTouchEnd"
                >
                    <!-- Стрелки -->
                    <button
                        v-if="items.length > 1"
                        @click.stop="prev"
                        class="absolute left-2 md:left-4 top-1/2 -translate-y-1/2 w-11 h-11 md:w-12 md:h-12 rounded-full bg-white/10 hover:bg-[#e9a13b] hover:text-[#1a1206] text-white flex items-center justify-center transition-colors z-10"
                        aria-label="Предыдущее"
                    >
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button
                        v-if="items.length > 1"
                        @click.stop="next"
                        class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 w-11 h-11 md:w-12 md:h-12 rounded-full bg-white/10 hover:bg-[#e9a13b] hover:text-[#1a1206] text-white flex items-center justify-center transition-colors z-10"
                        aria-label="Следующее"
                    >
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <!-- Видео в лайтбоксе -->
                    <video
                        v-if="current && current.type === 'video'"
                        :key="current.id"
                        :src="current.url"
                        :poster="current.thumb || undefined"
                        controls
                        autoplay
                        playsinline
                        class="max-w-full max-h-full w-auto h-auto rounded-lg shadow-2xl"
                    >
                        Ваш браузер не поддерживает воспроизведение видео.
                    </video>

                    <!-- Фото в лайтбоксе -->
                    <img
                        v-else-if="current"
                        :key="current.id"
                        :src="current.url"
                        :alt="`Фото ${index + 1}`"
                        class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xl select-none"
                        draggable="false"
                    />
                </div>

                <!-- Миниатюры снизу -->
                <div v-if="items.length > 1" class="flex justify-start md:justify-center gap-2 px-4 pb-4 pt-2 overflow-x-auto scrollbar-hide">
                    <button
                        v-for="(item, i) in items"
                        :key="item.id"
                        @click="index = i"
                        class="relative flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden border-2 transition-all"
                        :class="i === index ? 'border-[#e9a13b] opacity-100' : 'border-transparent opacity-50 hover:opacity-80'"
                    >
                        <img :src="item.thumb || item.url" :alt="`Миниатюра ${i + 1}`" class="w-full h-full object-cover" />
                        <div v-if="item.type === 'video'" class="absolute inset-0 flex items-center justify-center bg-black/40 pointer-events-none">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    items: { type: Array, default: () => [] },
    startIndex: { type: Number, default: 0 },
});

const emit = defineEmits(['close']);

const index = ref(props.startIndex);
let touchStartX = 0;

const current = computed(() => props.items[index.value] || null);

// Синхронизация начального индекса при открытии
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        index.value = props.startIndex;
        document.body.style.overflow = 'hidden'; // блокируем скролл
        window.addEventListener('keydown', onKeydown);
    } else {
        document.body.style.overflow = '';
        window.removeEventListener('keydown', onKeydown);
    }
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});

function close() {
    emit('close');
}

function next() {
    if (props.items.length) {
        index.value = (index.value + 1) % props.items.length;
    }
}

function prev() {
    if (props.items.length) {
        index.value = (index.value - 1 + props.items.length) % props.items.length;
    }
}

// Клавиатура: Esc, стрелки
function onKeydown(e) {
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'ArrowLeft') prev();
}

// Свайпы на мобильных
function onTouchStart(e) {
    touchStartX = e.changedTouches[0].screenX;
}

function onTouchEnd(e) {
    const delta = e.changedTouches[0].screenX - touchStartX;
    if (Math.abs(delta) > 50) {
        delta < 0 ? next() : prev();
    }
}
</script>

<style scoped>
.lightbox-fade-enter-active,
.lightbox-fade-leave-active {
    transition: opacity 0.25s ease;
}

.lightbox-fade-enter-from,
.lightbox-fade-leave-to {
    opacity: 0;
}

.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
