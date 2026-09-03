<template>
    <div class="min-h-screen bg-white">
        <Header />

        <div class="container mx-auto px-6 py-20 flex items-center justify-center min-h-[60vh]">
            <div class="text-center max-w-2xl">
                <!-- Большая цифра 404 -->
                <div class="relative mb-8">
                    <h1 class="text-[180px] md:text-[240px] font-bold text-[#ebf7fb] leading-none select-none">
                        404
                    </h1>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-32 h-32 text-[#77c4db]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                </div>

                <!-- Заголовок -->
                <h2 class="text-3xl md:text-4xl font-light text-[#283e46] mb-4">
                    Упс! Страница не найдена
                </h2>

                <!-- Описание -->
                <p class="text-lg text-[#666666] mb-10 max-w-md mx-auto">
                    Похоже, вы забрели в леса Карелии не по той тропинке. Такой страницы не существует, но наши дома ждут вас!
                </p>

                <!-- Кнопки -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <router-link
                        to="/"
                        class="inline-flex items-center gap-2 bg-[#77c4db] hover:bg-[#5fb5d1] text-white px-8 py-4 rounded-full font-medium transition-all hover:scale-105"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        На главную
                    </router-link>

                    <router-link
                        to="/#properties"
                        class="inline-flex items-center gap-2 border-2 border-[#283e46] text-[#283e46] px-8 py-4 rounded-full font-medium hover:bg-[#283e46] hover:text-white transition-all"
                    >
                        Посмотреть дома
                    </router-link>
                </div>
            </div>
        </div>

        <FloatingSupport />
    </div>
</template>

<script setup>
import Header from '@/components/Header.vue';
import FloatingSupport from '@/components/FloatingSupport.vue';
</script>
