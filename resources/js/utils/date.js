/**
 * Единые утилиты для работы с датами (ISO-строки 'YYYY-MM-DD' <-> Date).
 */

const MONTHS_SHORT_RU = ['янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
const WEEKDAYS_SHORT_RU = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
const MS_PER_DAY = 1000 * 60 * 60 * 24;

/** Парсит ISO-строку 'YYYY-MM-DD' в локальную дату (полночь), без смещения часового пояса. */
export function parseIsoDate(isoStr) {
    if (!isoStr) return null;
    const [y, m, d] = String(isoStr).slice(0, 10).split('-').map(Number);
    return new Date(y, m - 1, d);
}

/** Форматирует Date в строку 'YYYY-MM-DD' (локальное время). */
export function toDateStr(date) {
    if (!date) return '';
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

/** Склонение слова «ночь»: 1 ночь, 2 ночи, 5 ночей. */
export function nightsText(count) {
    if (count === 1) return 'ночь';
    if (count >= 2 && count <= 4) return 'ночи';
    return 'ночей';
}

/** Количество ночей между ISO-датами заезда и выезда. */
export function getNightsCount(checkIn, checkOut) {
    if (!checkIn || !checkOut) return 0;
    const start = parseIsoDate(checkIn);
    const end = parseIsoDate(checkOut);
    return Math.round(Math.abs(end - start) / MS_PER_DAY);
}

/** «15 мая 2026» из ISO-строки или Date. */
export function formatDateDisplay(value) {
    if (!value) return '';
    const date = value instanceof Date ? value : parseIsoDate(value);
    if (!date) return '';
    return `${date.getDate()} ${MONTHS_SHORT_RU[date.getMonth()]} ${date.getFullYear()}`;
}

/** «15.05» из ISO-строки. */
export function formatDateShort(isoStr) {
    if (!isoStr) return '';
    const [, month, day] = isoStr.split('-');
    return `${parseInt(day, 10)}.${month}`;
}

/** «пт — пн» (дни недели заезда и выезда). */
export function getWeekDays(checkIn, checkOut) {
    if (!checkIn || !checkOut) return '';
    return `${WEEKDAYS_SHORT_RU[parseIsoDate(checkIn).getDay()]} — ${WEEKDAYS_SHORT_RU[parseIsoDate(checkOut).getDay()]}`;
}
