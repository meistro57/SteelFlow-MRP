<script setup>
defineProps({
    bar: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div class="p-4 border rounded-lg bg-gray-50 mb-4">
        <div class="flex justify-between mb-2 text-sm font-medium">
            <span>Bar ID: {{ bar.stock_id }}</span>
            <span>Total Length: {{ bar.length }}"</span>
        </div>
        
        <div class="relative w-full h-12 bg-gray-200 border rounded flex overflow-hidden">
            <div 
                v-for="part in bar.parts" 
                :key="part.id"
                class="h-full border-r border-white flex items-center justify-center text-[10px] font-bold text-white bg-primary"
                :style="{ width: (part.length / bar.length * 100) + '%' }"
                :title="part.mark + ' (' + part.length + ')'"
            >
                {{ part.mark }}
            </div>
            <div 
                class="h-full bg-yellow-400"
                :style="{ width: (bar.remnant / bar.length * 100) + '%' }"
                title="Remnant"
            ></div>
            <div 
                class="h-full bg-red-400 opacity-50"
                :style="{ width: (bar.kerf_loss / bar.length * 100) + '%' }"
                title="Kerf Loss"
            ></div>
        </div>
        
        <div class="mt-2 flex gap-4 text-xs text-gray-600">
            <div class="flex items-center"><span class="w-3 h-3 bg-primary mr-1"></span> Parts</div>
            <div class="flex items-center"><span class="w-3 h-3 bg-yellow-400 mr-1"></span> Remnant</div>
            <div class="flex items-center"><span class="w-3 h-3 bg-red-400 opacity-50 mr-1"></span> Kerf</div>
        </div>
    </div>
</template>
