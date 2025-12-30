<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BarcodeScanner from '@/Components/BarcodeScanner.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const lastScanned = ref(null);

const handleBarcode = (barcode) => {
    lastScanned.value = barcode;
    router.post(route('production.process-scan'), { barcode }, {
        preserveScroll: true,
        onSuccess: () => {
            // Optional notifications can be added here
        }
    });
};
</script>

<template>
    <AppLayout title="Mobile Scan">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mobile Barcode Scanner
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="text-center mb-6">
                        <p class="text-gray-600 mb-4">Point the camera at a part or batch barcode</p>
                        <BarcodeScanner @result="handleBarcode" />
                    </div>

                    <div v-if="lastScanned" class="mt-8 border-t pt-6 text-center">
                        <h3 class="text-lg font-medium text-gray-900">Last Scanned:</h3>
                        <p class="text-2xl font-bold text-primary mt-2">{{ lastScanned }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
