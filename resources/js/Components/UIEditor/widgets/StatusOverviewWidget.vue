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

const statuses = computed(() => {
    return props.data?.statuses || [
        { label: 'In Progress', count: 12, color: 'blue' },
        { label: 'Complete', count: 45, color: 'green' },
        { label: 'Pending', count: 8, color: 'yellow' },
    ];
});

const getColorClass = (color) => {
    const colors = {
        blue: 'bg-weld-500 text-weld-100',
        green: 'bg-green-500 text-green-100',
        yellow: 'bg-safety-500 text-safety-100',
        red: 'bg-red-500 text-red-100',
        orange: 'bg-forge-500 text-forge-100',
    };
    return colors[color] || colors.blue;
};

const total = computed(() => {
    return statuses.value.reduce((sum, s) => sum + s.count, 0);
});
</script>

<template>
  <div class="h-full flex flex-col">
    <div class="space-y-3 flex-1">
      <div
        v-for="status in statuses"
        :key="status.label"
        class="flex items-center justify-between"
      >
        <div class="flex items-center gap-3">
          <span
            class="w-3 h-3 rounded-full"
            :class="getColorClass(status.color)"
          />
          <span class="text-sm text-steel-300">{{ status.label }}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-lg font-bold text-steel-200">{{ status.count }}</span>
          <span class="text-xs text-steel-500">
            ({{ Math.round((status.count / total) * 100) }}%)
          </span>
        </div>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="mt-4 pt-4 border-t border-steel-700">
      <div class="flex h-2 rounded-full overflow-hidden bg-steel-800">
        <div
          v-for="status in statuses"
          :key="status.label"
          :class="getColorClass(status.color)"
          :style="{ width: `${(status.count / total) * 100}%` }"
          class="transition-all"
        />
      </div>
      <div class="mt-2 text-xs text-steel-500 text-right">
        Total: {{ total }} items
      </div>
    </div>
  </div>
</template>
