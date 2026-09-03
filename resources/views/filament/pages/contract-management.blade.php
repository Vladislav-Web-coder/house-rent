<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Информация о текущем файле -->
        <x-filament::section>
            <x-slot name="heading">
                📄 Текущий файл договора
            </x-slot>

            @if(app(\App\Services\ContractFileProvider::class)->exists())
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <x-filament::icon
                            icon="heroicon-o-check-circle"
                            class="w-6 h-6 text-green-500"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            <strong>Файл загружен и готов к использованию</strong>
                        </span>
                    </div>

                    <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400 pl-9">
                        <div>
                            <span class="text-gray-500">Размер:</span>
                            <strong>{{ number_format(app(\App\Services\ContractFileProvider::class)->getSize() / 1024, 1) }} КБ</strong>
                        </div>
                        <div>
                            <span class="text-gray-500">Путь:</span>
                            <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">
                                {{ app(\App\Services\ContractFileProvider::class)->getExpectedPath() }}
                            </code>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-3">
                    <x-filament::icon
                        icon="heroicon-o-exclamation-triangle"
                        class="w-6 h-6 text-red-500 flex-shrink-0"
                    />
                    <div>
                        <p class="text-sm font-medium text-red-700 dark:text-red-400">
                            Файл договора не найден
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Нажмите кнопку <strong>«Загрузить договор»</strong> в верхней части страницы,
                            чтобы добавить PDF-файл. Без него клиенты не будут получать договор в письмах.
                        </p>
                    </div>
                </div>
            @endif
        </x-filament::section>

        <!-- Инструкция -->
        <x-filament::section>
            <x-slot name="heading">
                Как это работает
            </x-slot>

            <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2">
                    <x-filament::icon icon="heroicon-o-check" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <span>При создании заявки клиенту автоматически отправляется письмо с деталями бронирования</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-filament::icon icon="heroicon-o-check" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <span>К письму прикрепляется этот файл как <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">Договор-аренды.pdf</code></span>
                </li>
                <li class="flex items-start gap-2">
                    <x-filament::icon icon="heroicon-o-check" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <span>Вы можете обновить файл в любой момент — изменения применятся к следующим заявкам</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-filament::icon icon="heroicon-o-check" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <span>Рекомендуемый формат: PDF, размер до 10 МБ</span>
                </li>
            </ul>
        </x-filament::section>

        <!-- Быстрая загрузка -->
        <x-filament::section>
            <x-slot name="heading">
                Быстрая загрузка нового файла
            </x-slot>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Или используйте кнопку <strong>«Загрузить договор»</strong> в верхней части страницы.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
