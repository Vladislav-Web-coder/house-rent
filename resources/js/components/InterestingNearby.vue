<template>
    <section id="nearby" class="py-16 md:py-24 bg-[#0e1a24] overflow-hidden">
        <div class="container mx-auto px-4 md:px-6">
            <!-- Заголовок -->
            <div class="text-center mb-10 md:mb-14">
                <span class="inline-block text-[#e9a13b] tracking-[0.3em] uppercase text-[10px] md:text-sm font-semibold mb-3">
                    Что посмотреть
                </span>
                <h2 class="text-3xl md:text-5xl font-light text-[#fdf8ef]">Интересное рядом</h2>
                <p class="text-[#a8b6bd] text-base md:text-lg mt-3 max-w-2xl mx-auto">
                    Места, ради которых стоит остаться подольше — всё в шаговой доступности от вашего дома
                </p>
            </div>

            <!-- Слайдер -->
            <div
                class="relative max-w-5xl mx-auto"
                @mouseenter="pauseAutoplay"
                @mouseleave="startAutoplay"
                @touchstart="onTouchStart"
                @touchend="onTouchEnd"
            >
                <div class="overflow-hidden rounded-2xl shadow-2xl">
                    <div
                        class="flex transition-transform duration-500 ease-out"
                        :style="{ transform: `translateX(-${current * 100}%)` }"
                    >
                        <!-- Слайд: flex, чтобы карточка растягивалась на всю высоту ленты -->
                        <div v-for="item in items" :key="item.id" class="w-full flex-shrink-0 flex">
                            <!-- h-full: белая карточка заполняет всю высоту слайда -->
                            <div class="grid grid-cols-1 md:grid-cols-2 bg-[#fffdf8] w-full h-full">
                                <!-- Фото: относительный контейнер, внутри — стрелки -->
                                <div class="relative h-64 md:h-full md:min-h-[420px]">
                                    <img
                                        :src="item.image"
                                        :alt="item.title"
                                        loading="lazy"
                                        class="absolute inset-0 w-full h-full object-cover"
                                        @error="onImageError"
                                    />

                                    <!-- Бейдж расстояния -->
                                    <span class="absolute top-4 left-4 bg-[#e9a13b] text-[#1a1206] px-3 py-1.5 rounded-full text-xs font-bold shadow-lg z-10">
        {{ item.distance }}
    </span>

                                    <!-- ✅ Стрелки внутри фото-колонки -->
                                    <button
                                        @click.stop="prev"
                                        class="absolute left-3 md:left-4 top-1/2 -translate-y-1/2 w-10 h-10 md:w-11 md:h-11 bg-white/90 hover:bg-[#e9a13b] hover:text-[#1a1206] text-[#251d12] rounded-full flex items-center justify-center shadow-lg transition-colors z-10"
                                        aria-label="Предыдущее место"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="next"
                                        class="absolute right-3 md:right-4 top-1/2 -translate-y-1/2 w-10 h-10 md:w-11 md:h-11 bg-white/90 hover:bg-[#e9a13b] hover:text-[#1a1206] text-[#251d12] rounded-full flex items-center justify-center shadow-lg transition-colors z-10"
                                        aria-label="Следующее место"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Описание: центрируется по вертикали -->
                                <div class="p-6 md:p-10 flex flex-col justify-center">
                                    <h3 class="text-xl md:text-2xl font-medium text-[#251d12] mb-4">{{ item.title }}</h3>
                                    <p class="text-[#6e6459] leading-relaxed mb-6">{{ item.description }}</p>
                                    <div class="flex items-center gap-2 text-[#d18a2a] text-sm font-medium">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ item.location }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Точки -->
                <div class="flex justify-center gap-2 mt-6">
                    <button
                        v-for="(item, i) in items"
                        :key="item.id"
                        @click="goTo(i)"
                        class="h-2 rounded-full transition-all duration-300"
                        :class="i === current ? 'w-8 bg-[#e9a13b]' : 'w-2 bg-[#fdf8ef]/30 hover:bg-[#fdf8ef]/60'"
                        :aria-label="`Слайд ${i + 1}`"
                    ></button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

// Данные слайдера — редактируйте здесь
const items = ref([
    {
        id: 1,
        title: 'Горный парк «Рускеала»',
        description: 'Мраморный каньон с изумрудной водой и отвесными скалами. Бывший карьер, где добывали камень для Петербурга. Прогулки по тропам, лодки и подземные штольни.',
        distance: '~40 км',
        location: 'п. Рускеала, Сортавальский район',
        image: '/images/nearby/ruskeala.jpg',
    },
    {
        id: 2,
        title: 'Исторический парк «Бастион»',
        description: 'Интерактивный музей под открытым небом на берегу Ладоги. Крепость викингов, мастер-классы и живая история — можно всё трогать и примерять.',
        distance: '~5 км',
        location: 'г. Сортавала, набережная Ладоги',
        image: '/images/nearby/bastion.png',
    },
    {
        id: 3,
        title: 'Парк Ваккосалми',
        description: 'Живописный городской парк с горой Кухавуори. Панорамные виды на Сортавалу и озеро, певческое поле с уникальной акустикой и скульптурой Ангела.',
        distance: '~7 км',
        location: 'г. Сортавала',
        image: '/images/nearby/vakkosalmi.png',
    },
    {
        id: 4,
        title: 'Карельский зоопарк',
        description: 'Один из самых больших зоопарков России. Здесь живут медведи, волки, рыси, лоси и множество других животных. Отличный вариант для семейного дня и знакомства с северной природой.',
        distance: '~100 км',
        location: 'п. Сяпся, Пряжинский район',
        image: '/images/nearby/zoo.jpg',
    },
    {
        id: 5,
        title: 'Остров Валаам',
        description: 'Легендарный архипелаг посреди Ладоги. Древний монастырь, скалистые берега, хвойные леса и особая северная атмосфера «Северного Афона».',
        distance: '~45 км + теплоход',
        location: 'о. Валаам, Ладожское озеро',
        image: '/images/nearby/valaam.png',
    },
]);

const current = ref(0);
let autoplayTimer = null;
let touchStartX = 0;

function next() {
    current.value = (current.value + 1) % items.value.length;
}

function prev() {
    current.value = (current.value - 1 + items.value.length) % items.value.length;
}

function goTo(i) {
    current.value = i;
}

function startAutoplay() {
    pauseAutoplay();
    autoplayTimer = setInterval(next, 6000);
}

function pauseAutoplay() {
    if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
    }
}

function onTouchStart(e) {
    touchStartX = e.changedTouches[0].screenX;
    pauseAutoplay();
}

function onTouchEnd(e) {
    const delta = e.changedTouches[0].screenX - touchStartX;
    if (Math.abs(delta) > 50) {
        delta < 0 ? next() : prev();
    }
    startAutoplay();
}

function onImageError(event) {
    event.target.src = '/images/hero-bg.jpeg';
}

onMounted(() => {
    startAutoplay();
});

onBeforeUnmount(() => {
    pauseAutoplay();
});
</script>
