<template>
    <header class="bg-[#fffdf8]/95 backdrop-blur-sm shadow-sm sticky top-0 z-50 border-b border-[#251d12]/5">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16 md:h-20">

                <!-- PNG Логотип -->
                <router-link to="/" class="flex items-center gap-2.5 md:gap-3 group shrink-0" @click="closeAllDropdowns">
                    <img
                        src="/images/logo.png"
                        alt="Сказочная Карелия"
                        class="h-10 w-10 md:h-12 md:w-12 object-contain rounded-full shadow-md ring-1 ring-[#251d12]/10 transition-transform duration-300 group-hover:scale-105"
                    />
                    <span class="flex flex-col leading-none">
                        <span class="text-base md:text-lg font-bold text-[#251d12] tracking-wider uppercase">Сказочная</span>
                        <span class="text-base md:text-lg font-bold text-[#d18a2a] tracking-wider uppercase">Карелия</span>
                    </span>
                </router-link>

                <!-- Навигация для десктопа -->
                <nav class="hidden md:flex items-center space-x-8">

                    <!-- Dropdown "Дома" -->
                    <div class="relative" ref="homesDropdownRef">
                        <button
                            @click.stop="toggleDropdown('homes')"
                            class="flex items-center space-x-1 text-[#251d12] hover:text-[#d18a2a] font-medium transition-colors py-2"
                        >
                            <span>Дома</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': activeDropdown === 'homes' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div
                            v-show="activeDropdown === 'homes'"
                            class="absolute top-full left-0 mt-2 w-56 bg-[#fffdf8] rounded-xl shadow-xl border border-[#251d12]/10 py-2 animate-fade-in"
                        >
                            <button @click="navigateTo('/', 'all')" class="w-full text-left block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] hover:text-[#d18a2a] transition-colors">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-[#e9a13b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span>Все объекты</span>
                                </div>
                            </button>
                            <button @click="navigateTo('/', 'house')" class="w-full text-left block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] hover:text-[#d18a2a] transition-colors">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-[#e9a13b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span>Дома</span>
                                </div>
                            </button>
                            <!-- Квартиры: показываем ТОЛЬКО если они есть на бэке -->
                            <button
                                v-if="hasApartments"
                                @click="navigateTo('/', 'apartment')"
                                class="w-full text-left block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] hover:text-[#d18a2a] transition-colors"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-[#e9a13b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>Квартиры</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Вопросы: ссылка на секцию FAQ -->
                    <button @click="navigateTo('/#faq')" class="text-[#251d12] hover:text-[#d18a2a] font-medium transition-colors">
                        Вопросы
                    </button>

                    <!-- Dropdown "Поддержка" -->
                    <div class="relative" ref="supportDropdownRef">
                        <button
                            @click.stop="toggleDropdown('support')"
                            class="flex items-center space-x-1 text-[#251d12] hover:text-[#d18a2a] font-medium transition-colors py-2"
                        >
                            <span>Поддержка</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': activeDropdown === 'support' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div
                            v-show="activeDropdown === 'support'"
                            class="absolute top-full right-0 mt-2 w-72 bg-[#fffdf8] rounded-xl shadow-xl border border-[#251d12]/10 py-2 animate-fade-in"
                        >
                            <!-- Телефон -->
                            <a href="tel:+79999999999" class="block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-[#f6efe3] rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-[#e9a13b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-[#251d12]">+7 (999) 999-99-99</div>
                                        <div class="text-xs text-[#6e6459]">Ежедневно 9:00-21:00</div>
                                    </div>
                                </div>
                            </a>
                            <!-- Email -->
                            <a href="mailto:info@uyutnydom.ru" class="block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-[#f6efe3] rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-[#e9a13b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="font-medium text-[#251d12]">info@uyutnydom.ru</span>
                                </div>
                            </a>

                            <div class="border-t border-[#251d12]/10 my-2"></div>
                            <div class="px-4 py-2">
                                <p class="text-xs font-semibold text-[#6e6459] uppercase tracking-wider">Мессенджеры</p>
                            </div>

                            <!-- Telegram -->
                            <a href="https://t.me/your_telegram_username" target="_blank" class="block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-[#229ED9] rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-[#251d12]">Telegram</div>
                                        <div class="text-xs text-[#6e6459]">Быстрый ответ</div>
                                    </div>
                                </div>
                            </a>
                            <!-- WhatsApp -->
                            <a href="https://wa.me/79999999999" target="_blank" class="block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-[#25D366] rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-[#251d12]">WhatsApp</div>
                                        <div class="text-xs text-[#6e6459]">Звонки и сообщения</div>
                                    </div>
                                </div>
                            </a>
                            <!-- MAX -->
                            <a href="https://max.ru/u/your_max_username" target="_blank" class="block px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-[#fffdf8] border border-[#251d12]/10 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                                        <MaxIcon :size="28" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-[#251d12]">MAX</div>
                                        <div class="text-xs text-[#6e6459]">Мессенджер MAX</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Кнопка для мобильных (бургер) -->
                <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 text-[#251d12] hover:text-[#d18a2a]">
                    <svg v-if="!showMobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Мобильное меню: absolute, поверх контента -->
        <div
            v-show="showMobileMenu"
            class="md:hidden absolute top-full left-0 right-0 bg-[#fffdf8]/98 backdrop-blur-md border-b border-[#251d12]/10 shadow-lg animate-fade-in z-50"
        >
            <div class="container mx-auto px-4 py-4 max-h-[calc(100vh-64px)] overflow-y-auto">
                <nav class="flex flex-col space-y-1">

                    <!-- Аккордеон "Дома" -->
                    <div>
                        <button
                            @click="toggleMobileSection('homes')"
                            class="w-full flex items-center justify-between px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] rounded-lg transition-colors"
                        >
                            <span class="font-medium">Дома</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': mobileOpenSection === 'homes' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div v-show="mobileOpenSection === 'homes'" class="pl-6 space-y-1 mt-1">
                            <button @click="navigateTo('/', 'all')" class="w-full text-left flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#251d12] hover:bg-[#f6efe3] rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-[#e9a13b] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span>Все объекты</span>
                            </button>
                            <button
                                v-if="hasApartments"
                                @click="navigateTo('/', 'house')" class="w-full text-left flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#251d12] hover:bg-[#f6efe3] rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-[#e9a13b] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span>Дома</span>
                            </button>
                            <!-- Квартиры: показываем только если они есть -->
                            <button
                                v-if="hasApartments"
                                @click="navigateTo('/', 'apartment')"
                                class="w-full text-left flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#251d12] hover:bg-[#f6efe3] rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4 text-[#e9a13b] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span>Квартиры</span>
                            </button>
                        </div>
                    </div>

                    <!-- Вопросы: ссылка на секцию FAQ -->
                    <button @click="navigateTo('/#faq')" class="w-full text-left flex items-center gap-3 px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] hover:text-[#d18a2a] rounded-lg transition-colors font-medium">
                        <span>Вопросы</span>
                    </button>

                    <!-- Аккордеон "Поддержка" -->
                    <div>
                        <button
                            @click="toggleMobileSection('support')"
                            class="w-full flex items-center justify-between px-4 py-3 text-[#251d12] hover:bg-[#f6efe3] rounded-lg transition-colors"
                        >
                            <span class="font-medium">Поддержка</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': mobileOpenSection === 'support' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div v-show="mobileOpenSection === 'support'" class="pl-6 space-y-1 mt-1">
                            <a href="tel:+79999999999" class="flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#251d12] hover:bg-[#f6efe3] rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-[#e9a13b] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span>+7 (999) 999-99-99</span>
                            </a>
                            <a href="mailto:info@uyutnydom.ru" class="flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#251d12] hover:bg-[#f6efe3] rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-[#e9a13b] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span>info@uyutnydom.ru</span>
                            </a>
                            <a href="https://t.me/your_telegram_username" target="_blank" class="flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#229ED9] hover:bg-[#f6efe3] rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-[#229ED9] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                                <span>Telegram</span>
                            </a>
                            <a href="https://wa.me/79999999999" target="_blank" class="flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#25D366] hover:bg-[#f6efe3] rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-[#25D366] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                            <a href="https://max.ru/u/your_max_username" target="_blank" class="flex items-center gap-3 px-4 py-2 text-[#6e6459] hover:text-[#d18a2a] hover:bg-[#f6efe3] rounded-lg transition-colors">
                                <MaxIcon :size="20" />
                                <span>MAX</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Backdrop для закрытия при клике вне меню -->
        <div
            v-show="showMobileMenu"
            class="md:hidden fixed inset-0 top-full bg-black/20 z-40"
            @click="showMobileMenu = false"
        ></div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { fetchHasApartments, fetchProperties } from '@/api';
import MaxIcon from "./MaxIcon.vue";

const router = useRouter();

const activeDropdown = ref(null);
const showMobileMenu = ref(false);
const mobileOpenSection = ref(null);

// Храним информацию о наличии квартир (кэш на сессию)
const apartmentsExist = ref(null); // null = не проверено

const homesDropdownRef = ref(null);
const supportDropdownRef = ref(null);

// Вычисляемое свойство: показывать ли пункт "Квартиры"
const hasApartments = computed(() => apartmentsExist.value === true);

function toggleDropdown(name) {
    activeDropdown.value = activeDropdown.value === name ? null : name;
}

function toggleMobileSection(name) {
    mobileOpenSection.value = mobileOpenSection.value === name ? null : name;
}

function closeAllDropdowns() {
    activeDropdown.value = null;
    showMobileMenu.value = false;
    mobileOpenSection.value = null;
}

function navigateTo(path, category = null) {
    showMobileMenu.value = false;
    mobileOpenSection.value = null;
    activeDropdown.value = null;

    setTimeout(() => {
        if (category) {
            router.push({
                path: '/',
                query: category !== 'all' ? { category } : {},
                hash: '#properties',
            }).catch(() => {});
        } else if (path.includes('#')) {
            const [pathname, hash] = path.split('#');
            router.push({ path: pathname || '/', hash: '#' + hash }).catch(() => {});
        } else {
            router.push({ path }).catch(() => {});
        }
    }, 150);
}

// Проверка наличия квартир на бэкенде (с кэшированием в sessionStorage)
async function checkApartmentsExist() {
    const cached = sessionStorage.getItem('apartmentsExist');
    if (cached !== null) {
        apartmentsExist.value = cached === 'true';
        return;
    }

    try {
        apartmentsExist.value = await fetchHasApartments();
        sessionStorage.setItem('apartmentsExist', String(apartmentsExist.value));
    } catch (err) {
        // Если эндпоинта нет — делаем фоллбек через полный список
        try {
            const properties = await fetchProperties();
            const exists = Array.isArray(properties) && properties.some(p => p.category === 'apartment');
            apartmentsExist.value = exists;
            sessionStorage.setItem('apartmentsExist', String(exists));
        } catch {
            apartmentsExist.value = false;
        }
    }
}

function handleClickOutside(event) {
    const isOutsideHomes = homesDropdownRef.value && !homesDropdownRef.value.contains(event.target);
    const isOutsideSupport = supportDropdownRef.value && !supportDropdownRef.value.contains(event.target);

    if (isOutsideHomes && isOutsideSupport) {
        activeDropdown.value = null;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    checkApartmentsExist();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
