<template>
    <div class="yandex-map-wrapper">
        <div v-if="!latitude || !longitude" class="flex items-center justify-center h-full bg-gray-100 dark:bg-gray-800 rounded-xl">
            <div class="text-center p-8">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Координаты объекта не указаны</p>
                <p v-if="address" class="text-sm text-gray-400 mt-2">{{ address }}</p>
            </div>
        </div>

        <div v-else ref="mapContainer" class="yandex-map rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-700"></div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    latitude: { type: [Number, String], default: null },
    longitude: { type: [Number, String], default: null },
    address: { type: String, default: '' },
    title: { type: String, default: '' },
    zoom: { type: Number, default: 15 },
});

const mapContainer = ref(null);
let mapInstance = null;
let ymaps = null;

// Ссылка для открытия в Яндекс.Картах
const yandexMapsLink = ref('');

onMounted(async () => {
    updateExternalLink();
    if (props.latitude && props.longitude) {
        await loadYandexMapsAPI();
        await initMap();
    }
});

// Следим за изменением координат
watch(() => [props.latitude, props.longitude], async ([newLat, newLng]) => {
    updateExternalLink();
    if (newLat && newLng && ymaps) {
        if (mapInstance) {
            mapInstance.setCenter([parseFloat(newLat), parseFloat(newLng)], props.zoom);
        } else {
            await initMap();
        }
    }
});

function updateExternalLink() {
    if (props.latitude && props.longitude) {
        const lat = parseFloat(props.latitude);
        const lng = parseFloat(props.longitude);
        yandexMapsLink.value = `https://yandex.ru/maps/?ll=${lng}%2C${lat}&z=${props.zoom}&pt=${lng}%2C${lat}%2Cpm2rdm`;
    } else {
        yandexMapsLink.value = '';
    }
}

// Динамическая загрузка Yandex Maps API через CDN
function loadYandexMapsAPI() {
    return new Promise((resolve, reject) => {
        // Проверяем, не загружен ли уже API
        if (window.ymaps) {
            ymaps = window.ymaps;
            resolve(ymaps);
            return;
        }

        // Создаём скрипт для загрузки API
        const script = document.createElement('script');
        script.src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU';
        script.async = true;

        script.onload = () => {
            ymaps = window.ymaps;
            ymaps.ready(() => {
                resolve(ymaps);
            });
        };

        script.onerror = () => {
            reject(new Error('Не удалось загрузить Yandex Maps API'));
        };

        document.head.appendChild(script);
    });
}

async function initMap() {
    if (!mapContainer.value || !ymaps) return;

    try {
        const lat = parseFloat(props.latitude);
        const lng = parseFloat(props.longitude);

        // Создаём карту
        mapInstance = new ymaps.Map(mapContainer.value, {
            center: [lat, lng],
            zoom: props.zoom,
            controls: ['zoomControl'],
        });

        // Создаём кастомную метку
        const markerLayout = ymaps.templateLayoutFactory.createClass(
            `<div style="
                background: #77c4db;
                width: 40px;
                height: 40px;
                border-radius: 50% 50% 50% 0;
                transform: rotate(-45deg);
                border: 3px solid white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.25);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: transform 0.2s;
            ">
                <svg style="transform: rotate(45deg);" width="20" height="20" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>`
        );

        const placemark = new ymaps.Placemark(
            [lat, lng],
            {
                hintContent: props.title || 'Объект',
                balloonContent: props.address || '',
            },
            {
                iconLayout: markerLayout,
                iconShape: {
                    type: 'Circle',
                    coordinates: [20, 20],
                    radius: 20,
                },
            }
        );

        mapInstance.geoObjects.add(placemark);

        // Открываем балун при клике
        placemark.events.add('click', () => {
            window.open(yandexMapsLink.value, '_blank');
        });
    } catch (error) {
        console.error('Ошибка инициализации Яндекс.Карты:', error);
        if (mapContainer.value) {
            mapContainer.value.innerHTML = `
                <div class="flex items-center justify-center h-full bg-gray-100 dark:bg-gray-800 p-8 text-center">
                    <div>
                        <p class="text-gray-600 dark:text-gray-300 mb-3">Не удалось загрузить карту</p>
                        <a href="${yandexMapsLink.value}" target="_blank" class="text-[#77c4db] hover:underline">
                            Открыть в Яндекс.Картах →
                        </a>
                    </div>
                </div>
            `;
        }
    }
}

onBeforeUnmount(() => {
    if (mapInstance && typeof mapInstance.destroy === 'function') {
        mapInstance.destroy();
        mapInstance = null;
    }
});
</script>

<style scoped>
.yandex-map-wrapper {
    width: 100%;
}

.yandex-map {
    width: 100%;
    height: 450px;
    min-height: 350px;
}

@media (max-width: 640px) {
    .yandex-map {
        height: 350px;
        min-height: 280px;
    }
}
</style>
