<template>
    <div>
        <!-- Сам календарь -->
        <div class="calendar-wrapper">
            <div class="calendar-inner">
                <DatePicker
                    :key="calendarKey"
                    v-model.range="internalRange"
                    :disabled-dates="disabledDates"
                    :min-date="minDate"
                    :is-inline="inline"
                    locale="ru"
                    :first-day-of-week="2"
                    color="indigo"
                    :popover="{ visibility: 'click' }"
                    class="w-full"
                />
            </div>
        </div>

        <!-- Информационный блок с выбранными датами -->
        <div v-if="showInfo && modelValue.start && modelValue.end" class="mt-4 bg-[#ebf7fb] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-gray-500">Выбрано:</span>
                    <div class="font-semibold text-[#283e46]">
                        {{ formatDateDisplay(modelValue.start) }} — {{ formatDateDisplay(modelValue.end) }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-sm text-gray-500">{{ nightsCount }} {{ nightsText }}</span>
                </div>
            </div>
            <button
                v-if="showReset"
                type="button"
                @click="clearDates"
                class="mt-3 w-full text-center text-sm text-gray-500 hover:text-[#77c4db] transition-colors"
            >
                ✕ Сбросить даты
            </button>
        </div>

        <!-- Подсказка о минимальном сроке -->
        <p v-if="inline" class="mt-3 text-xs text-gray-500 text-center">
            💡 Минимальный срок проживания — 1 ночь
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ start: null, end: null }),
    },
    disabledDates: { type: Array, default: () => [] },
    minDate: { type: Date, default: () => new Date() },
    showInfo: { type: Boolean, default: false },
    showReset: { type: Boolean, default: false },
    inline: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
const calendarKey = ref(0);

// Флаг для предотвращения рекурсии watch
let isAdjusting = false;

const internalRange = ref({
    start: props.modelValue.start ? new Date(props.modelValue.start + 'T00:00:00') : null,
    end: props.modelValue.end ? new Date(props.modelValue.end + 'T00:00:00') : null,
});

// Синхронизация: родитель → компонент
watch(() => props.modelValue, (newVal) => {
    if (isAdjusting) return;

    const newStart = newVal.start ? new Date(newVal.start + 'T00:00:00') : null;
    const newEnd = newVal.end ? new Date(newVal.end + 'T00:00:00') : null;

    if (!datesEqual(internalRange.value.start, newStart) || !datesEqual(internalRange.value.end, newEnd)) {
        internalRange.value = { start: newStart, end: newEnd };
    }
}, { deep: true });

// Синхронизация: компонент → родитель с проверкой мин. 1 ночи
watch(internalRange, (newVal) => {
    let start = newVal.start;
    let end = newVal.end;

    // Если обе даты выбраны и совпадают — корректируем выезд на следующий день
    if (start && end && start.getTime() === end.getTime()) {
        isAdjusting = true;

        const nextDay = new Date(start);
        nextDay.setDate(nextDay.getDate() + 1);
        end = nextDay;

        // Отправляем скорректированные значения родителю
        emit('update:modelValue', {
            start: toDateStr(start),
            end: toDateStr(end),
        });

        // Обновляем internalRange (с флагом, чтобы не было рекурсии)
        internalRange.value = { start, end };

        nextTick(() => {
            isAdjusting = false;
        });
        return;
    }

    emit('update:modelValue', {
        start: start ? toDateStr(start) : null,
        end: end ? toDateStr(end) : null,
    });
}, { deep: true });

function toDateStr(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function datesEqual(a, b) {
    if (!a && !b) return true;
    if (!a || !b) return false;
    return a.getTime() === b.getTime();
}

const nightsCount = computed(() => {
    if (!props.modelValue.start || !props.modelValue.end) return 0;
    const [y1, m1, d1] = props.modelValue.start.split('-').map(Number);
    const [y2, m2, d2] = props.modelValue.end.split('-').map(Number);
    return Math.ceil(Math.abs(new Date(y2, m2 - 1, d2) - new Date(y1, m1 - 1, d1)) / (1000 * 60 * 60 * 24));
});

const nightsText = computed(() => {
    const count = nightsCount.value;
    if (count === 1) return 'ночь';
    if (count >= 2 && count <= 4) return 'ночи';
    return 'ночей';
});

function formatDateDisplay(dateStr) {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('-');
    const months = ['янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
    return `${parseInt(day, 10)} ${months[parseInt(month, 10) - 1]} ${year}`;
}

function clearDates() {
    isAdjusting = true;
    internalRange.value = { start: undefined, end: undefined };
    emit('update:modelValue', { start: null, end: null });
    calendarKey.value++;

    nextTick(() => {
        isAdjusting = false;
    });
}
</script>

<style>
.vc-container {
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
    font-family: inherit;
    width: 100% !important;
    max-width: 100% !important;
    /* Гарантируем, что календарь не вылезет за границы родителя */
    overflow: hidden;
}

/* Внутренний контейнер календаря — принудительно вписываем в родителя */
.vc-container .vc-pane-container {
    width: 100% !important;
    max-width: 100% !important;
}

/* Сетка дней — подстраивается под ширину */
.vc-container .vc-weeks {
    width: 100% !important;
    max-width: 100% !important;
}

/* Дни недели — адаптивный размер */
.vc-container .vc-weekday {
    font-size: 0.7rem;
    padding: 0.25rem 0;
}

/* Ячейки дня — гибкий размер */
.vc-container .vc-day {
    min-width: 0;
}

.vc-container .vc-day-content {
    width: auto !important;
    height: auto !important;
    min-width: 28px;
    min-height: 28px;
    aspect-ratio: 1;
    font-size: 0.8rem;
    padding: 0;
}

/* Заголовок месяца — не обрезается */
.vc-container .vc-title {
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Кнопки навигации — меньше на узких экранах */
.vc-container .vc-arrow {
    width: 28px;
    height: 28px;
}

.vc-highlight { background-color: #77c4db !important; }
.vc-disabled { color: #fca5a5 !important; text-decoration: line-through; opacity: 0.6; }
.vc-day-content:hover:not(.vc-disabled) { background-color: #ebf7fb !important; }

/* На очень узких контейнерах (< 280px) уменьшаем ещё сильнее */
@media (max-width: 320px) {
    .vc-container .vc-day-content {
        min-width: 24px;
        min-height: 24px;
        font-size: 0.7rem;
    }
    .vc-container .vc-title {
        font-size: 0.8rem;
    }
}

/* Обёртка для масштабирования календаря */
.calendar-wrapper {
    width: 100%;
    overflow: hidden;
    position: relative;
}

.calendar-inner {
    width: 100%;
    /* На узких контейнерах календарь будет уменьшаться пропорционально */
    min-width: 280px;
    transform-origin: top left;
}

/* Автоматическое масштабирование через CSS контейнерные запросы (если поддерживается) */
@container (max-width: 320px) {
    .calendar-inner {
        transform: scale(0.9);
    }
}

@container (max-width: 280px) {
    .calendar-inner {
        transform: scale(0.8);
    }
}
</style>
