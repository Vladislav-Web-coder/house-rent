<template>
    <div class="bg-white border-2 border-[#283e46] p-4 sm:p-6 md:p-8 rounded-2xl overflow-hidden max-w-full">
        <h3 class="text-2xl font-medium text-[#283e46] mb-6">Забронировать</h3>

        <form @submit.prevent="onSubmit" class="space-y-6">
            <!-- Даты -->
            <div class="bg-[#ebf7fb] rounded-xl p-4 overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <span class="text-sm font-medium text-[#666666] shrink-0">Выбранные даты:</span>
                    <span v-if="checkInStr && checkOutStr" class="text-sm font-semibold text-[#283e46] whitespace-nowrap">
                        {{ formatDateDisplay(checkInStr) }} — {{ formatDateDisplay(checkOutStr) }}
                    </span>
                    <span v-else class="text-sm text-[#666666]">Не выбрано</span>
                </div>
                <div v-if="checkInStr && checkOutStr" class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="text-[#666666] shrink-0">{{ getNightsCount() }} {{ getNightsText() }}</span>
                    <span class="font-medium text-[#283e46] whitespace-nowrap">{{ getWeekDays() }}</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#283e46] mb-2">Выберите даты проживания</label>
                <DateRangePicker v-model="dateRangeModel" :disabled-dates="disabledDates" />
                <p v-if="errors.check_in" class="text-red-600 text-xs mt-1">{{ errors.check_in }}</p>
                <p v-if="errors.check_out" class="text-red-600 text-xs mt-1">{{ errors.check_out }}</p>
            </div>

            <!-- Гости -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#283e46] mb-2">Взрослые</label>
                    <input type="number" v-model.number="adults" :min="1" :max="maxAdults"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors"
                           :class="{ 'border-red-500': errors.adults }" />
                    <p v-if="errors.adults" class="text-red-600 text-xs mt-1">{{ errors.adults }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#283e46] mb-2">Дети</label>
                    <input type="number" v-model.number="children" :min="0" :max="maxChildren"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors"
                           :class="{ 'border-red-500': errors.children }" />
                    <p v-if="errors.children" class="text-red-600 text-xs mt-1">{{ errors.children }}</p>
                </div>
            </div>

            <!-- Имя -->
            <div>
                <label class="block text-sm font-medium text-[#283e46] mb-2">Ваше имя</label>
                <input type="text" v-model="guestName"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors"
                       :class="{ 'border-red-500': errors.guest_name }" />
                <p v-if="errors.guest_name" class="text-red-600 text-xs mt-1">{{ errors.guest_name }}</p>
            </div>

            <!-- Телефон: код страны слева, локальная часть справа -->
            <div>
                <label class="block text-sm font-medium text-[#283e46] mb-2">Телефон</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <select v-model="countryCode"
                            class="sm:w-28 w-full shrink-0 px-3 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors">
                        <option v-for="country in countries" :key="country.code" :value="country.code">
                            {{ country.flag }} {{ country.dialCode }}
                        </option>
                    </select>
                    <input type="tel" v-model="guestPhone" @input="formatPhoneInput" :placeholder="phonePlaceholder"
                           class="flex-1 min-w-0 px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors"
                           :class="{ 'border-red-500': errors.guest_phone }" />
                </div>
                <p v-if="errors.guest_phone" class="text-red-600 text-xs mt-1">{{ errors.guest_phone }}</p>
            </div>

            <!-- Почта -->
            <div>
                <label class="block text-sm font-medium text-[#283e46] mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    v-model="guestEmail"
                    placeholder="your@email.com"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors"
                    :class="{ 'border-red-500': errors.guest_email }"
                />
                <p v-if="errors.guest_email" class="text-red-600 text-xs mt-1">{{ errors.guest_email }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    📄 На этот email мы отправим подтверждение и договор аренды
                </p>
            </div>

            <!-- Способ связи (выпадающий список) -->
            <div>
                <label class="block text-sm font-medium text-[#283e46] mb-2">Предпочтительный способ связи</label>
                <select v-model="contactMethod"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors"
                        :class="{ 'border-red-500': errors.contact_method }">
                    <option v-for="method in contactMethods" :key="method.value" :value="method.value">
                        {{ method.icon }} {{ method.label }}
                    </option>
                </select>
                <p v-if="errors.contact_method" class="text-red-600 text-xs mt-1">{{ errors.contact_method }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#283e46] mb-2">Комментарий (необязательно)</label>
                <textarea v-model="comment" rows="3"
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#77c4db] focus:ring-4 focus:ring-[#77c4db]/20 transition-colors"></textarea>
            </div>

            <!-- Блок с ценами -->
            <div v-if="pricingData" class="border-2 border-gray-200 rounded-xl overflow-hidden">
                <div class="bg-[#283e46] text-white px-6 py-4">
                    <h4 class="font-semibold text-lg">Расчет стоимости</h4>
                    <p class="text-white/70 text-sm">{{ pricingData.nights }} {{ getNightsTextPricing(pricingData.nights) }}</p>
                </div>
                <div class="p-6 space-y-4">
                    <div v-for="(group, index) in pricingData.price_groups" :key="index"
                         class="flex items-center justify-between py-3"
                         :class="{ 'border-b border-gray-100': index < pricingData.price_groups.length - 1 }">
                        <div class="flex-1">
                            <div v-if="group.source" class="font-medium text-[#283e46]">{{ group.source }}</div>
                            <div class="text-sm text-[#666666]">
                                {{ group.nights }} {{ getNightsTextPricing(group.nights) }} × {{ formatPrice(group.price_per_night) }} ₽
                            </div>
                        </div>
                        <div class="font-semibold text-[#283e46] text-lg">{{ formatPrice(group.subtotal) }} ₽</div>
                    </div>
                </div>
                <div class="bg-[#ebf7fb] px-6 py-4 flex items-center justify-between">
                    <span class="font-bold text-xl text-[#283e46]">Итого:</span>
                    <span class="font-bold text-2xl text-[#283e46]">{{ formatPrice(pricingData.final_total) }} ₽</span>
                </div>
                <div class="border-t border-gray-200">
                    <button type="button" @click="showDetailedBreakdown = !showDetailedBreakdown"
                            class="w-full px-6 py-3 text-sm text-[#77c4db] hover:text-[#5fb5d1] font-medium flex items-center justify-center gap-2 transition-colors">
                        <span>{{ showDetailedBreakdown ? 'Скрыть' : 'Подробнее' }}</span>
                        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': showDetailedBreakdown }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div v-if="showDetailedBreakdown" class="px-6 pb-4">
                        <div class="space-y-2">
                            <div v-for="(night, index) in pricingData.breakdown" :key="index"
                                 class="flex items-center justify-between text-sm py-2 px-3 rounded-lg"
                                 :class="index % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                                <div class="flex items-center gap-3">
                                    <span class="text-[#666666] w-16">{{ night.day_name }}, {{ formatDateShort(night.date) }}</span>
                                    <span v-if="night.source" class="text-[#666666] text-xs bg-[#ebf7fb] px-2 py-0.5 rounded-full">{{ night.source }}</span>
                                </div>
                                <span class="font-medium text-[#283e46]">{{ formatPrice(night.price) }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="isSubmitting || isSubmittingServer"
                    class="w-full bg-[#77c4db] hover:bg-[#5fb5d1] text-white font-semibold py-4 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center">
                <svg v-if="isSubmitting || isSubmittingServer" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSubmittingServer ? 'Отправка...' : 'Отправить заявку' }}
            </button>

            <p v-if="serverError" class="text-red-600 text-sm text-center bg-red-50 p-3 rounded-lg">{{ serverError }}</p>
            <p v-if="serverSuccess" class="text-green-600 text-sm text-center bg-green-50 p-3 rounded-lg">{{ serverSuccess }}</p>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from 'vee-validate';
import { z } from 'zod';
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { AsYouType, getExampleNumber } from 'libphonenumber-js';
import examples from 'libphonenumber-js/mobile/examples';
import DateRangePicker from "./DateRangePicker.vue";

const props = defineProps({
    propertyId: { type: Number, required: true },
    unavailableDates: { type: Array, default: () => [] },
    maxAdults: { type: Number, default: 2 },
    maxChildren: { type: Number, default: 0 }
});

const countries = [
    { code: 'RU', flag: '🇷🇺', dialCode: '+7' },
    { code: 'BY', flag: '🇧🇾', dialCode: '+375' },
    { code: 'KZ', flag: '🇰🇿', dialCode: '+7' },
    { code: 'US', flag: '🇺🇸', dialCode: '+1' },
    { code: 'GB', flag: '🇬🇧', dialCode: '+44' },
    { code: 'DE', flag: '🇩🇪', dialCode: '+49' },
    { code: 'TR', flag: '🇹🇷', dialCode: '+90' },
    { code: 'AE', flag: '🇦🇪', dialCode: '+971' },
    { code: 'CN', flag: '🇨🇳', dialCode: '+86' },
];

// Способы связи
const contactMethods = [
    { value: 'telegram', label: 'Telegram'},
    { value: 'whatsapp', label: 'WhatsApp'},
    { value: 'max', label: 'MAX'},
    { value: 'phone', label: 'Звонок',},
];

const countryCode = ref('RU');
const contactMethod = ref('telegram');

const showDetailedBreakdown = ref(false);

const disabledDates = computed(() => {
    return props.unavailableDates.map(range => ({
        start: new Date(range.start),
        end: new Date(range.end)
    }));
});

const maxTotalGuests = props.maxAdults + props.maxChildren;

// ✅ Динамическая маска на основе libphonenumber-js
const phoneMask = computed(() => {
    const example = getExampleNumber(countryCode.value, examples);
    if (example) {
        return new AsYouType(countryCode.value).input(example.formatNational());
    }
    return '';
});

const validationSchema = toTypedSchema(
    z.object({
        check_in: z.string().min(1, 'Выберите дату заезда'),
        check_out: z.string().min(1, 'Выберите дату выезда'),
        adults: z.number().min(1, 'Минимум 1 взрослый').max(props.maxAdults, `Максимум ${props.maxAdults} взрослых`),
        children: z.number().min(0, 'Не может быть отрицательным').max(props.maxChildren, `Максимум ${props.maxChildren} детей`),
        guest_name: z.string().min(2, 'Имя слишком короткое').max(255),
        guest_email: z.string().min(1, 'Укажите email').email('Некорректный email'),
        guest_phone: z.string().min(5, 'Введите номер телефона'),
        country_code: z.string().min(1, 'Выберите страну'),
        contact_method: z.string().min(1, 'Выберите способ связи'),
        comment: z.string().max(1000, 'Комментарий слишком длинный').optional()
    })
        .refine((data) => {
            const totalGuests = (data.adults || 0) + (data.children || 0);
            return totalGuests <= maxTotalGuests;
        }, { message: `Максимальное количество гостей: ${maxTotalGuests}`, path: ['adults'] })
        .refine((data) => {
            if (!data.check_in || !data.check_out) return true;
            return new Date(data.check_out) > new Date(data.check_in);
        }, { message: 'Дата выезда должна быть позже даты заезда', path: ['check_out'] })
);

const { defineField, handleSubmit, errors, isSubmitting, setErrors } = useForm({
    validationSchema,
    initialValues: {
        check_in: '', check_out: '', adults: 1, children: 0,
        guest_name: '', guest_phone: '', country_code: 'RU', guest_email: '', contact_method: 'telegram', comment: ''
    }
});

const [checkInStr] = defineField('check_in');
const [checkOutStr] = defineField('check_out');
const [adults] = defineField('adults');
const [children] = defineField('children');
const [guestName] = defineField('guest_name');
const [guestEmail] = defineField('guest_email');
const [guestPhone] = defineField('guest_phone');
const [comment] = defineField('comment');

const isSubmittingServer = ref(false);
const serverError = ref('');
const serverSuccess = ref('');
const pricingData = ref(null);

// Получаем код страны (dialCode) по ISO-коду
const currentDialCode = computed(() => {
    const country = countries.find(c => c.code === countryCode.value);
    return country?.dialCode || '+7';
});

const dateRangeModel = computed({
    get: () => ({ start: checkInStr.value || null, end: checkOutStr.value || null }),
    set: (val) => {
        checkInStr.value = val.start || '';
        checkOutStr.value = val.end || '';
    },
});
function getNationalPrefix(countryCode) {
    const prefixes = {
        'RU': '8',
        'BY': '8',
        'KZ': '8',
    };
    return prefixes[countryCode] || '';
}

const phonePlaceholder = computed(() => {
    const example = getExampleNumber(countryCode.value, examples);
    if (example) {
        const formatter = new AsYouType(countryCode.value);
        let fullNumber = formatter.input(example.formatNational());

        const nationalPrefix = getNationalPrefix(countryCode.value);
        const dialCode = currentDialCode.value;

        // Заменяем национальный префикс (8 или 0) на международный код
        if (nationalPrefix && fullNumber.startsWith(nationalPrefix)) {
            fullNumber = dialCode + fullNumber.substring(nationalPrefix.length);
        }

        return fullNumber.replace(dialCode, '').trim();
    }
    return '(XXX) XXX-XX-XX';
});

const formatPhoneInput = (event) => {
    let input = event.target.value;

    if (countryCode.value === 'RU') {
        if (input.startsWith('8')) {
            input = '7' + input.substring(1);
        }
    }

    const digits = input.replace(/\D/g, '');

    if (!digits) {
        guestPhone.value = '';
        return;
    }

    // Форматируем только локальную часть
    const formatter = new AsYouType(countryCode.value);
    const fullInput = currentDialCode.value.replace('+', '') + digits;
    const formatted = formatter.input('+' + fullInput);

    // Убираем код страны из результата
    const dialCode = currentDialCode.value;
    let result = formatted.replace(dialCode, '').trim();

    if (countryCode.value === 'RU' && result.startsWith('8')) {
        result = '7' + result.substring(1);
    }

    guestPhone.value = result;
};

// При смене страны очищаем номер
watch(countryCode, () => {
    guestPhone.value = '';
});

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price);
}

function formatDateDisplay(dateStr) {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('-');
    const months = ['янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
    return `${parseInt(day, 10)} ${months[parseInt(month, 10) - 1]} ${year}`;
}

function formatDateShort(dateStr) {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('-');
    return `${parseInt(day, 10)}.${month}`;
}

function getNightsCount() {
    if (!checkInStr.value || !checkOutStr.value) return 0;
    const [y1, m1, d1] = checkInStr.value.split('-').map(Number);
    const [y2, m2, d2] = checkOutStr.value.split('-').map(Number);
    return Math.ceil(Math.abs(new Date(y2, m2 - 1, d2) - new Date(y1, m1 - 1, d1)) / (1000 * 60 * 60 * 24));
}

function getNightsText() {
    const count = getNightsCount();
    if (count === 1) return 'ночь';
    if (count >= 2 && count <= 4) return 'ночи';
    return 'ночей';
}

function getNightsTextPricing(count) {
    if (count === 1) return 'ночь';
    if (count >= 2 && count <= 4) return 'ночи';
    return 'ночей';
}

function getWeekDays() {
    if (!checkInStr.value || !checkOutStr.value) return '';
    const [y1, m1, d1] = checkInStr.value.split('-').map(Number);
    const [y2, m2, d2] = checkOutStr.value.split('-').map(Number);
    const weekDays = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
    return `${weekDays[new Date(y1, m1 - 1, d1).getDay()]} — ${weekDays[new Date(y2, m2 - 1, d2).getDay()]}`;
}

watch([checkInStr, checkOutStr], async ([newCheckIn, newCheckOut]) => {
    serverError.value = '';
    pricingData.value = null;
    showDetailedBreakdown.value = false;
    if (!newCheckIn || !newCheckOut) return;

    try {
        const response = await axios.post(`/api/properties/${props.propertyId}/calculate-price`, {
            check_in: newCheckIn, check_out: newCheckOut
        });
        pricingData.value = response.data.pricing;
    } catch (error) {
        if (error.response?.status === 422) {
            console.error('Ошибки валидации:', error.response.data.errors);
            setErrors(error.response.data.errors);
            serverError.value = 'Пожалуйста, проверьте правильность заполнения выделенных полей.';
        } else if (error.response?.status === 429) {
            console.warn('Превышен лимит запросов на расчёт цены');
        }
    }
});

const onSubmit = handleSubmit(async (values) => {
    isSubmittingServer.value = true;
    serverError.value = '';
    serverSuccess.value = '';

    try {
        const fullPhoneNumber = currentDialCode.value + ' ' + values.guest_phone;

        const response = await axios.post(`/api/properties/${props.propertyId}/booking-requests`, {
            ...values,
            guest_phone: fullPhoneNumber,
            country_code: countryCode.value,
            guest_email: guestEmail.value,
            contact_method: contactMethod.value,
            check_in: values.check_in,
            check_out: values.check_out
        });
        serverSuccess.value = response.data.message;
    } catch (error) {
        if (error.response?.status === 422) {
            console.error('Ошибки валидации:', error.response.data.errors);
            setErrors(error.response.data.errors);
            serverError.value = 'Пожалуйста, проверьте правильность заполнения выделенных полей.';
        } else if (error.response?.status === 409) {
            serverError.value = error.response.data.message || 'К сожалению, эти даты уже заняты.';
        } else if (error.response?.status === 429) {
            serverError.value = error.response.data.message
                || 'Слишком много заявок. Пожалуйста, попробуйте позже или свяжитесь с нами по телефону.';
        } else {
            serverError.value = 'Произошла ошибка сервера. Попробуйте позже.';
        }
    } finally {
        isSubmittingServer.value = false;
    }
});
</script>

<style>
</style>
