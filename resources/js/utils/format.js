/**
 * Общие форматтеры.
 */

/** «12 345» по русским правилам группировки разрядов. */
export function formatPrice(price) {
    if (!price) return '0';
    return new Intl.NumberFormat('ru-RU').format(price);
}
