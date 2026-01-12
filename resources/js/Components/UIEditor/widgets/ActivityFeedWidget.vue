<script setup>
import { computed } from 'vue';
import {
    BoltIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

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

const items = computed(() => {
    const maxItems = props.config.maxItems || 5;
    return (props.data?.items || [
        { id: 1, message: 'Batch B-2024-147 started cutting', time: '2 min ago', status: 'in_progress' },
        { id: 2, message: 'Load L-2024-089 departed for site', time: '15 min ago', status: 'complete' },
        { id: 3, message: 'Stock received: 15x W14x30 A992', time: '1 hour ago', status: 'complete' },
        { id: 4, message: 'Low stock alert: C10x15.3', time: '2 hours ago', status: 'warning' },
    ]).slice(0, maxItems);
});

const getStatusIcon = (status) => {
    switch (status) {
    case 'in_progress':
        return BoltIcon;
    case 'complete':
        return CheckCircleIcon;
    case 'warning':
        return ExclamationTriangleIcon;
    default:
        return ClockIcon;
    }
};

const getStatusColor = (status) => {
    switch (status) {
    case 'in_progress':
        return 'text-forge-400 bg-forge-500/10 border-forge-500';
    case 'complete':
        return 'text-green-400 bg-green-500/10 border-green-500';
    case 'warning':
        return 'text-safety-400 bg-safety-500/10 border-safety-500';
    default:
        return 'text-steel-400 bg-steel-500/10 border-steel-500';
    }
};
</script>

<template>
  <div class="h-full overflow-auto space-y-3">
    <div
      v-for="item in items"
      :key="item.id"
      class="flex items-start gap-3 p-3 bg-steel-800/50 rounded-industrial border border-steel-700 hover:border-steel-600 transition-colors"
    >
      <div class="flex-shrink-0">
        <span
          class="inline-flex items-center justify-center w-8 h-8 rounded-full border"
          :class="getStatusColor(item.status)"
        >
          <component
            :is="getStatusIcon(item.status)"
            class="w-4 h-4"
          />
        </span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-steel-300 text-sm">
          {{ item.message }}
        </p>
        <p class="text-steel-500 text-xs mt-1 flex items-center gap-1">
          <ClockIcon class="w-3 h-3" />
          {{ item.time }}
        </p>
      </div>
    </div>

    <div
      v-if="items.length === 0"
      class="h-full flex items-center justify-center text-steel-500 text-sm"
    >
      No recent activity
    </div>
  </div>
</template>
