/**
 * Тонкая обёртка над axios: единая точка доступа к API фронтенда.
 */
import axios from 'axios';

const http = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

/** GET /api/properties — список объектов. */
export async function fetchProperties() {
    const { data } = await http.get('/api/properties');
    return data.data ?? data;
}

/** GET /api/properties/{id} — карточка объекта. */
export async function fetchProperty(id) {
    const { data } = await http.get(`/api/properties/${id}`);
    return data.data;
}

/** GET /api/properties/disabled-dates — занятые даты всех объектов. */
export async function fetchDisabledDates() {
    const { data } = await http.get('/api/properties/disabled-dates');
    return data.data;
}

/** GET /api/properties/has-apartments — есть ли квартиры в каталоге. */
export async function fetchHasApartments() {
    const { data } = await http.get('/api/properties/has-apartments');
    return data.has_apartments === true;
}

/** POST /api/properties/{id}/calculate-price — расчёт стоимости за даты. */
export async function calculatePrice(propertyId, checkIn, checkOut) {
    const { data } = await http.post(`/api/properties/${propertyId}/calculate-price`, {
        check_in: checkIn,
        check_out: checkOut,
    });
    return data.pricing;
}

/** POST /api/properties/{id}/booking-requests — отправка заявки на бронирование. */
export async function submitBookingRequest(propertyId, payload) {
    const { data } = await http.post(`/api/properties/${propertyId}/booking-requests`, payload);
    return data;
}

export default http;
