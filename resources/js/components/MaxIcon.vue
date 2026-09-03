<template>
    <img
        :src="'/images/max-icon.png'"
        :width="size"
        :height="size"
        alt="MAX"
        class="max-icon"
    />
</template>

<script setup>
defineProps({
    size: {
        type: [String, Number],
        default: 24
    }
});
</script>

<style scoped>
.max-icon {
    display: block;
    image-rendering: -webkit-optimize-contrast; /* Чёткость на Retina */
}
</style>
