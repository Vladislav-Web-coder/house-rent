import { defineStore } from 'pinia';
import axios from 'axios';

export const usePropertiesStore = defineStore('properties', {
    state: () => ({
        properties: [],
        disabledDates: {}, // { propertyId: ['2026-09-05', ...] }
        loading: false,
        error: null,
    }),

    getters: {
        /**
         * Проверка доступности объекта на диапазон дат
         */
        isPropertyAvailable: (state) => (propertyId, checkIn, checkOut) => {
            const disabled = state.disabledDates[propertyId] || [];
            if (!disabled.length) return true;

            const start = new Date(checkIn);
            const end = new Date(checkOut);
            const current = new Date(start);

            while (current < end) {
                const dateStr = current.toISOString().split('T')[0];
                if (disabled.includes(dateStr)) {
                    return false;
                }
                current.setDate(current.getDate() + 1);
            }

            return true;
        },

        /**
         * Даты, когда ВСЕ объекты заняты (для блокировки в главном календаре)
         */
        fullyBookedDates: (state) => {
            const propertiesCount = state.properties.length;
            if (!propertiesCount) return [];

            // Подсчёт занятых объектов для каждой даты
            const busyCounts = {};
            Object.values(state.disabledDates).forEach(dates => {
                dates.forEach(date => {
                    busyCounts[date] = (busyCounts[date] || 0) + 1;
                });
            });

            // Возвращаем даты, где заняты ВСЕ объекты
            return Object.entries(busyCounts)
                .filter(([, count]) => count >= propertiesCount)
                .map(([date]) => date)
                .sort();
        },
    },

    actions: {
        async fetchProperties() {
            if (this.properties.length > 0) return;

            this.loading = true;
            try {
                const response = await axios.get('/api/properties');
                this.properties = response.data.data;
            } catch (err) {
                this.error = err.message;
                console.error('Ошибка загрузки объектов:', err);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Загрузка занятых дат для всех объектов
         */
        async fetchDisabledDates() {
            if (Object.keys(this.disabledDates).length > 0) return;

            try {
                const response = await axios.get('/api/properties/disabled-dates');
                this.disabledDates = response.data.data;
            } catch (err) {
                console.error('Ошибка загрузки занятых дат:', err);
            }
        },
    },
});
