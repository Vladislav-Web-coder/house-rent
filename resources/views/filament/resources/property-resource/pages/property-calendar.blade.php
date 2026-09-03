<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $this->record->title }}</h2>
            <p class="text-gray-600 dark:text-gray-400">Календарь доступности и ценообразования</p>
        </div>

        <!-- Календарь -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 dark:border-gray-700" x-data="{ selectedDay: null }">
            <!-- Навигация по месяцам -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <button wire:click="prevMonth" class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-full transition shadow-sm hover:shadow-md" title="Предыдущий месяц">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <!-- Выбор месяца -->
                    <select wire:model.live="currentMonth" class="px-3 py-2 md:px-4 md:py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-semibold text-sm md:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @foreach($this->months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    <!-- Выбор года -->
                    <select wire:model.live="currentYear" class="px-3 py-2 md:px-4 md:py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-semibold text-sm md:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @foreach($this->years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>

                    <button wire:click="nextMonth" class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-full transition shadow-sm hover:shadow-md" title="Следующий месяц">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>

                <!-- Кнопка "Сегодня" -->
                <button wire:click="goToCurrentMonth" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition shadow-sm hover:shadow-md">
                    Текущий месяц
                </button>
            </div>

            <!-- Сетка календаря -->
            <div class="grid grid-cols-7 gap-1 md:gap-3">
                @foreach(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $dayName)
                    <div class="text-center font-bold text-gray-700 dark:text-gray-300 py-2 md:py-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg text-xs md:text-base">{{ $dayName }}</div>
                @endforeach

                @php
                    $firstDay = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1);
                    $startDayOfWeek = $firstDay->dayOfWeek === 0 ? 7 : $firstDay->dayOfWeek;
                    $lastDay = $firstDay->copy()->endOfMonth();
                @endphp

                {{-- Пустые ячейки до начала месяца --}}
                @for ($i = 1; $i < $startDayOfWeek; $i++)
                    <div class="rounded-xl p-1 md:p-3 min-h-[60px] md:min-h-[120px] invisible"></div>
                @endfor

                {{-- Дни месяца --}}
                @for ($day = 1; $day <= $lastDay->day; $day++)
                    @php
                        $dateStr = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, $day)->toDateString();
                        $dayData = $this->calendarData[$dateStr] ?? null;

                        $bgClass = 'bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/30 border-green-300 dark:border-green-600 hover:shadow-lg hover:scale-105';

                        if ($dayData && !$dayData['is_available']) {
                            if ($dayData['booking_info']['type'] === 'booking') {
                                $bgClass = 'bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/40 border-red-400 dark:border-red-600';
                            } elseif ($dayData['booking_info']['type'] === 'request') {
                                $bgClass = 'bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/40 border-yellow-400 dark:border-yellow-600';
                            } elseif ($dayData['booking_info']['type'] === 'synced') {
                                $bgClass = 'bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/40 border-purple-400 dark:border-purple-600';
                            }
                        }
                    @endphp

                    <div
                        class="border-2 rounded-xl p-1 md:p-3 min-h-[60px] md:min-h-[120px] cursor-pointer transition-all duration-200 {{ $bgClass }}"
                        @click="selectedDay = @js($dayData)"
                    >
                        <div class="flex justify-between items-start mb-1 md:mb-2">
                            <span class="text-xs md:text-base font-bold text-gray-900 dark:text-white">{{ $day }}</span>
                            @if($dayData && !$dayData['is_available'])
                                <div class="w-4 h-4 md:w-6 md:h-6 rounded-full flex items-center justify-center">
                                    @if($dayData['booking_info']['type'] === 'booking')
                                        <svg class="w-3 h-3 md:w-5 md:h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    @elseif($dayData['booking_info']['type'] === 'request')
                                        <svg class="w-3 h-3 md:w-5 md:h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                    @elseif($dayData['booking_info']['type'] === 'synced')
                                        <svg class="w-3 h-3 md:w-5 md:h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($dayData)
                            <div class="text-sm md:text-xl font-black text-gray-900 dark:text-white mb-0.5 md:mb-1">
                                {{ number_format($dayData['price'], 0, '.', ' ') }} ₽
                            </div>

                            @if($dayData['booking_info'])
                                <div class="text-[10px] md:text-xs font-semibold mt-0.5 md:mt-1 px-1 md:px-2 py-0.5 md:py-1 rounded hidden md:block">
                                    @if($dayData['booking_info']['type'] === 'booking')
                                        <span class="text-red-700 dark:text-red-300 bg-red-200/50 dark:bg-red-900/50 px-1 md:px-2 py-0.5 rounded">Занято</span>
                                    @elseif($dayData['booking_info']['type'] === 'request')
                                        <span class="text-yellow-700 dark:text-yellow-300 bg-yellow-200/50 dark:bg-yellow-900/50 px-1 md:px-2 py-0.5 rounded">Ожидает</span>
                                    @elseif($dayData['booking_info']['type'] === 'synced')
                                        <span class="text-purple-700 dark:text-purple-300 bg-purple-200/50 dark:bg-purple-900/50 px-1 md:px-2 py-0.5 rounded">
                                            {{ $dayData['booking_info']['source'] }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <div class="text-[10px] md:text-xs font-medium text-green-700 dark:text-green-300 mt-0.5 md:mt-1 hidden md:block">
                                    ✓ Свободно
                                </div>
                            @endif
                        @endif
                    </div>
                @endfor
            </div>

            <!-- Модальное окно с деталями дня -->
            <div x-show="selectedDay" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4" @click.self="selectedDay = null" x-cloak>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-300 dark:border-gray-600">
                    <!-- Заголовок -->
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedDay?.date"></h3>
                            <button @click="selectedDay = null" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Содержимое -->
                    <div class="px-6 py-5 space-y-4">
                        <!-- Блок цены (как статус) -->
                        <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-blue-500 dark:bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm text-blue-700 dark:text-blue-400 font-medium">Цена за сутки</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        <span x-text="new Intl.NumberFormat('ru-RU').format(selectedDay?.price)"></span> ₽
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Источник цены -->
                        <template x-if="selectedDay?.source">
                            <div class="flex items-center justify-between p-4 bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-200 dark:border-indigo-800 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-indigo-500 dark:bg-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm text-indigo-700 dark:text-indigo-400 font-medium">Источник цены</div>
                                        <div class="text-base text-gray-900 dark:text-white font-semibold" x-text="selectedDay?.source"></div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Статус -->
                        <div class="flex items-center justify-between p-4 rounded-xl" :class="selectedDay?.is_available ? 'bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" :class="selectedDay?.is_available ? 'bg-green-500 dark:bg-green-600' : 'bg-red-500 dark:bg-red-600'">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path x-show="selectedDay?.is_available" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        <path x-show="!selectedDay?.is_available" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium" :class="selectedDay?.is_available ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'">Статус</div>
                                    <div class="text-base font-semibold" :class="selectedDay?.is_available ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300'"
                                         x-text="selectedDay?.is_available ? 'Свободно' : 'Занято'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация о бронировании (как статус) -->
                        <template x-if="selectedDay?.booking_info">
                            <div class="flex items-center justify-between p-4 rounded-xl border-2"
                                 :class="{
                                    'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800': selectedDay?.booking_info.type === 'booking',
                                    'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800': selectedDay?.booking_info.type === 'request',
                                    'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800': selectedDay?.booking_info.type === 'synced'
                                 }">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                                         :class="{
                                            'bg-red-500 dark:bg-red-600': selectedDay?.booking_info.type === 'booking',
                                            'bg-yellow-500 dark:bg-yellow-600': selectedDay?.booking_info.type === 'request',
                                            'bg-purple-500 dark:bg-purple-600': selectedDay?.booking_info.type === 'synced'
                                         }">
                                        <template x-if="selectedDay?.booking_info.type === 'booking'">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="selectedDay?.booking_info.type === 'request'">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="selectedDay?.booking_info.type === 'synced'">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-purple-500 dark:bg-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm text-purple-700 dark:text-purple-400 font-medium mb-1">Внешняя синхронизация</div>
                                                    <div class="text-base text-gray-900 dark:text-white font-semibold" x-text="'Платформа: ' + selectedDay?.booking_info.source"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium"
                                             :class="{
                                                'text-red-700 dark:text-red-400': selectedDay?.booking_info.type === 'booking',
                                                'text-yellow-700 dark:text-yellow-400': selectedDay?.booking_info.type === 'request',
                                                'text-purple-700 dark:text-purple-400': selectedDay?.booking_info.type === 'synced'
                                             }">
                                            <span x-show="selectedDay?.booking_info.type === 'booking'">Подтвержденная бронь</span>
                                            <span x-show="selectedDay?.booking_info.type === 'request'">Заявка на рассмотрении</span>
                                            <span x-show="selectedDay?.booking_info.type === 'synced'">Внешняя синхронизация</span>
                                        </div>
                                        <div class="text-base text-gray-900 dark:text-white font-semibold">
                                            <span x-show="selectedDay?.booking_info.type === 'booking'" x-text="'Гость: ' + selectedDay?.booking_info.guest_name"></span>
                                            <span x-show="selectedDay?.booking_info.type === 'request'" x-text="'Гость: ' + selectedDay?.booking_info.guest_name"></span>
                                            <span x-show="selectedDay?.booking_info.type === 'synced'" x-text="'Источник: ' + selectedDay?.booking_info.source"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Кнопка закрытия -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <button @click="selectedDay = null" class="w-full px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-filament-panels::page>
