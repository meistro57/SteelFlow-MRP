<template>
  <div class="relative w-full max-w-md mx-auto">
    <video ref="video" autoplay playsinline class="w-full h-auto rounded-lg shadow-md bg-black"></video>
    <div v-if="scanning" class="absolute inset-0 border-2 border-primary animate-pulse pointer-events-none"></div>
    <div v-if="error" class="mt-2 text-red-500 text-sm">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const emit = defineEmits(['result']);

const video = ref(null);
const scanning = ref(true);
const error = ref(null);
let stream = null;
let detectionInterval = null;

const startCamera = async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' }
        });
        if (video.value) {
            video.value.srcObject = stream;
        }
    } catch (err) {
        error.value = "Camera access denied: " + err.message;
        scanning.value = false;
    }
};

const stopCamera = () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    if (detectionInterval) {
        clearInterval(detectionInterval);
    }
};

onMounted(async () => {
    await startCamera();

    if ('BarcodeDetector' in window) {
        const barcodeDetector = new window.BarcodeDetector();
        detectionInterval = setInterval(async () => {
            if (video.value && video.value.readyState === video.value.HAVE_ENOUGH_DATA) {
                try {
                    const barcodes = await barcodeDetector.detect(video.value);
                    if (barcodes.length > 0) {
                        emit('result', barcodes[0].rawValue);
                    }
                } catch (err) {
                    console.error("Barcode detection failed:", err);
                }
            }
        }, 500);
    } else {
        error.value = "Barcode Detector API not supported in this browser.";
    }
});

onUnmounted(() => {
    stopCamera();
});
</script>
