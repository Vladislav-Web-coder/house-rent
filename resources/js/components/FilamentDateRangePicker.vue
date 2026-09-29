<template>
    <div class="filament-date-range-picker">
        <!-- Календарь -->
        <DatePicker
            v-model.range="internalRange"
            :disabled-dates="disabledDates"
            :min-date="minDate"
            :is-inline="true"
            locale="ru"
            :first-day-of-week="2"
            color="indigo"
            class="w-full"
        />

        <!-- Информационный блок -->
        <div v-if="internalRange.start && internalRange.end" class="mt-4 bg-[#ebf7fb] rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-gray-500">Выбрано:</span>
                    <div class="font-semibold text-[#283e46]">
                        {{ formatDateDisplay(internalRange.start) }} — {{ formatDateDisplay(internalRange.end) }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-sm text-gray-500">{{ nightsCount }} {{ nightsText }}</span>
                </div>
            </div>
            <button
                type="button"
                @click="clearDates"
                class="mt-3 w-full text-center text-sm text-gray-500 hover:text-[#77c4db] transition-colors"
            >
                ✕ Сбросить даты
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import { parseIsoDate, toDateStr, nightsText as declineNights, formatDateDisplay } from '@/utils/date';

const props = defineProps({
    propertyId: { type: Number, required: true },
    checkIn: { type: String, default: null },
    checkOut: { type: String, default: null },
});

const emit = defineEmits(['update:checkIn', 'update:checkOut']);

// Внутреннее состояние
const internalRange = ref({
    start: parseIsoDate(props.checkIn),
    end: parseIsoDate(props.checkOut),
});

// Занятые даты (будут загружены с сервера)
const disabledDates = ref([]);
const minDate = new Date();

// Загрузка занятых дат при монтировании и изменении объекта
onMounted(() => loadDisabledDates());

watch(() => props.propertyId, () => {
    loadDisabledDates();
    clearDates(); // Сбрасываем даты при смене объекта
});

// Синхронизация: родитель → компонент
watch([() => props.checkIn, () => props.checkOut], ([newIn, newOut]) => {
    const newStart = parseIsoDate(newIn);
    const newEnd = parseIsoDate(newOut);
    if (!datesEqual(internalRange.value.start, newStart) || !datesEqual(internalRange.value.end, newEnd)) {
        internalRange.value = { start: newStart, end: newEnd };
    }
});

// Синхронизация: компонент → родитель (и Livewire)
watch(internalRange, (newVal) => {
    const checkInStr = newVal.start ? toDateStr(newVal.start) : null;
    const checkOutStr = newVal.end ? toDateStr(newVal.end) : null;

    // Обновляем Livewire форму
    if (window.Livewire) {
        window.Livewire.find(props.livewireId).set('data.check_in', checkInStr);
        window.Livewire.find(props.livewireId).set('data.check_out', checkOutStr);
    }
}, { deep: true });

// Загрузка занятых дат через API
async function loadDisabledDates() {
    if (!props.propertyId) {
        disabledDates.value = [];
        return;
    }

    try {
        const response = await fetch(`/api/admin/properties/${props.propertyId}/disabled-dates`);
        if (response.ok) {
            const dates = await response.json();
            disabledDates.value = dates.map(dateStr => ({
                start: parseIsoDate(dateStr),
                end: parseIsoDate(dateStr),
            }));
        }
    } catch (error) {
        console.error('Ошибка загрузки занятых дат:', error);
    }
}

function datesEqual(a, b) {
    if (!a && !b) return true;
    if (!a || !b) return false;
    return a.getTime() === b.getTime();
}

const nightsCount = computed(() => {
    if (!internalRange.value.start || !internalRange.value.end) return 0;
    const MS_PER_DAY = 1000 * 60 * 60 * 24;
    return Math.round(Math.abs(internalRange.value.end - internalRange.value.start) / MS_PER_DAY);
});

const nightsText = computed(() => declineNights(nightsCount.value));

function clearDates() {
    internalRange.value = { start: null, end: null };
}
</script>

<style scoped>
.filament-date-range-picker {
    width: 100%;
}
</style>

<style>
/* Глобальные стили календаря */
.vc-container { border-radius: 0.5rem; border: 1px solid #d1d5db; font-family: inherit; width: 100%; }
.vc-highlight { background-color: #77c4db !important; }
.vc-disabled { color: #fca5a5 !important; text-decoration: line-through; opacity: 0.6; }
.vc-day-content:hover:not(.vc-disabled) { background-color: #ebf7fb !important; }
</style>
