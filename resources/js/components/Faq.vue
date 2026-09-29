<template>
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-light mb-4">Ответим на все ваши вопросы</h2>
                <p class="text-gray-600 text-lg">Собрали ответы на самые частые вопросы наших гостей</p>
            </div>

            <div class="space-y-0">
                <div
                    v-for="(faq, index) in faqs"
                    :key="index"
                    class="border-t border-gray-200 last:border-b"
                >
                    <button
                        @click="toggleFaq(index)"
                        class="w-full text-left flex justify-between items-center py-6 hover:bg-gray-50 transition-colors px-4 -mx-4"
                    >
                        <span class="text-xl font-medium text-[#283e46] pr-8">{{ faq.question }}</span>
                        <svg
                            class="w-6 h-6 text-[#283e46] transform transition-transform flex-shrink-0"
                            :class="{ 'rotate-180': faq.open }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div
                        v-show="faq.open"
                        class="pb-6 px-4 -mx-4 text-gray-600 leading-relaxed"
                    >
                        {{ faq.answer }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue';

const faqs = ref([
    {
        question: 'Что включено в стоимость?',
        answer: 'В стоимость включено: постельное бельё, полотенца, бытовая техника, мангал, шампуры.',
        open: false
    },
    {
        question: 'Мы едем с маленьким ребёнком — есть ли удобства?',
        answer: 'Да, конечно. По вашему запросу предоставим детский манеж.',
        open: false
    },
    {
        question: 'Какое минимальное количество суток для бронирования?',
        answer: 'Минимальный срок аренды — 2 суток. Заезд с 15:00, выезд до 12:00.',
        open: false
    },
    {
        question: 'Можно ли приехать с домашними животными?',
        answer: 'Да, мы рады гостям с домашними питомцами. Просим заранее сообщить об этом при бронировании.',
        open: false
    }
]);

function toggleFaq(index) {
    faqs.value[index].open = !faqs.value[index].open;
}
</script>
