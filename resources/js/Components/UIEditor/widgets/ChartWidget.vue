<script setup>
import { computed } from 'vue';

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
    data: {
        type: Object,
        default: () => ({}),
    },
    config: {
        type: Object,
        default: () => ({}),
    },
});

const chartType = computed(() => {
    const type = props.widget.widget_type;
    if (type.includes('pie') || type.includes('doughnut')) return 'pie';
    if (type.includes('line')) return 'line';
    return 'bar';
});

const labels = computed(() => props.data?.labels || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']);
const datasets = computed(() => props.data?.datasets || [{ data: [12, 19, 3, 5, 2] }]);

// Simple bar visualization (would use Chart.js in production)
const maxValue = computed(() => {
    const allValues = datasets.value.flatMap(d => d.data || []);
    return Math.max(...allValues, 1);
});
</script>

<template>
  <div class="h-full flex flex-col">
    <div
      v-if="config.chartTitle"
      class="text-sm text-steel-400 mb-3"
    >
      {{ config.chartTitle }}
    </div>

    <!-- Simple Bar Chart Visualization -->
    <div
      v-if="chartType === 'bar'"
      class="flex-1 flex items-end gap-2"
    >
      <div
        v-for="(value, index) in datasets[0]?.data || []"
        :key="index"
        class="flex-1 flex flex-col items-center gap-1"
      >
        <div
          class="w-full bg-weld-500/80 rounded-t transition-all hover:bg-weld-400"
          :style="{ height: `${(value / maxValue) * 100}%`, minHeight: '4px' }"
        />
        <span class="text-xs text-steel-500">{{ labels[index] }}</span>
      </div>
    </div>

    <!-- Simple Line Chart Visualization -->
    <div
      v-else-if="chartType === 'line'"
      class="flex-1 flex items-end gap-2"
    >
      <svg
        class="w-full h-full"
        viewBox="0 0 100 50"
        preserveAspectRatio="none"
      >
        <polyline
          fill="none"
          stroke="rgba(59, 130, 246, 0.8)"
          stroke-width="2"
          :points="datasets[0]?.data?.map((v, i) => `${(i / (datasets[0].data.length - 1)) * 100},${50 - (v / maxValue) * 45}`).join(' ') || '0,50'"
        />
        <circle
          v-for="(value, index) in datasets[0]?.data || []"
          :key="index"
          :cx="(index / (datasets[0].data.length - 1)) * 100"
          :cy="50 - (value / maxValue) * 45"
          r="3"
          fill="rgba(59, 130, 246, 1)"
        />
      </svg>
    </div>

    <!-- Simple Pie Chart Visualization -->
    <div
      v-else-if="chartType === 'pie'"
      class="flex-1 flex items-center justify-center"
    >
      <div class="relative w-32 h-32">
        <svg
          viewBox="0 0 32 32"
          class="w-full h-full -rotate-90"
        >
          <circle
            v-for="(value, index) in datasets[0]?.data?.slice(0, 4) || [25, 25, 25, 25]"
            :key="index"
            r="16"
            cx="16"
            cy="16"
            fill="transparent"
            :stroke="['#f97316', '#3b82f6', '#22c55e', '#eab308'][index % 4]"
            stroke-width="32"
            :stroke-dasharray="`${value} ${100 - value}`"
            :stroke-dashoffset="datasets[0]?.data?.slice(0, index).reduce((a, b) => a + b, 0) || 0"
            class="transition-all"
          />
        </svg>
      </div>
    </div>

    <!-- Legend -->
    <div
      v-if="config.showLegend !== false"
      class="flex flex-wrap gap-3 mt-3 text-xs"
    >
      <div
        v-for="(label, index) in labels.slice(0, 4)"
        :key="label"
        class="flex items-center gap-1"
      >
        <span
          class="w-2 h-2 rounded-full"
          :style="{ backgroundColor: ['#f97316', '#3b82f6', '#22c55e', '#eab308'][index % 4] }"
        />
        <span class="text-steel-400">{{ label }}</span>
      </div>
    </div>
  </div>
</template>
