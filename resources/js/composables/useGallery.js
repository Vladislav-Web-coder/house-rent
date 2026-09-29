/**
 * Компоновщик логики галереи (слайдер медиа + лайтбокс),
 * используемый в PropertyView.
 */
import { ref, computed } from 'vue';

export function useGallery(property) {
    const currentSlide = ref(0);
    const lightboxOpen = ref(false);

    const galleryItems = computed(() => property.value?.gallery ?? []);

    const currentMedia = computed(() => {
        if (galleryItems.value.length > 0) {
            return galleryItems.value[currentSlide.value];
        }
        if (property.value?.main_image) {
            return {
                id: 'main',
                type: property.value.main_media_type || 'image',
                url: property.value.main_image,
                thumb: property.value.main_image,
            };
        }
        return null;
    });

    function pauseCurrentVideo() {
        const video = document.querySelector('.video-player');
        if (video && !video.paused) {
            video.pause();
        }
    }

    function goToSlide(index) {
        pauseCurrentVideo();
        currentSlide.value = index;
    }

    function nextSlide() {
        if (galleryItems.value.length) {
            goToSlide((currentSlide.value + 1) % galleryItems.value.length);
        }
    }

    function prevSlide() {
        if (galleryItems.value.length) {
            goToSlide((currentSlide.value - 1 + galleryItems.value.length) % galleryItems.value.length);
        }
    }

    function reset() {
        currentSlide.value = 0;
        lightboxOpen.value = false;
    }

    return {
        currentSlide,
        lightboxOpen,
        galleryItems,
        currentMedia,
        nextSlide,
        prevSlide,
        goToSlide,
        reset,
    };
}
