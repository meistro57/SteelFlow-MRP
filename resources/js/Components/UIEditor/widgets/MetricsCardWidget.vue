<script setup>
import { computed } from 'vue';
import {
    FolderOpenIcon,
    ScaleIcon,
    ChartBarIcon,
    TruckIcon,
    BoltIcon,
    CubeIcon,
    ArrowUpIcon,
    ArrowDownIcon,
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

// Icon mapping
const iconMap = {
    folder: FolderOpenIcon,
    scale: ScaleIcon,
    'chart-bar': ChartBarIcon,
    truck: TruckIcon,
    bolt: BoltIcon,
    cube: CubeIcon,
};

const icon = computed(() => {
    return iconMap[props.config.icon] || CubeIcon;
});

const value = computed(() => {
    return props.data?.value ?? '—';
});

const formattedValue = computed(() => {
    const val = value.value;
    if (typeof val === 'number') {
        if (props.config.metric === 'total_weight') {
            return val.toLocaleString();
        }
        if (props.config.metric === 'production_progress') {
            return `${val}%`;
        }
        return val.toLocaleString();
    }
    return val;
});

const trend = computed(() => props.data?.trend || null);
const change = computed(() => props.data?.change || 0);

const unit = computed(() => {
    if (props.config.metric === 'total_weight') return 'lbs';
    if (props.config.metric === 'ready_to_ship') return 'loads';
    return '';
});
</script>

<template>
  <div class="h-full flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <div class="text-3xl font-bold text-steel-200">
          {{ formattedValue }}
          <span
            v-if="unit"
            class="text-sm text-steel-500 font-normal"
          >{{ unit }}</span>
        </div>

        <!-- Trend -->
        <div
          v-if="trend && change"
          class="flex items-center gap-1 mt-2"
          :class="{
            'text-green-400': trend === 'up',
            'text-red-400': trend === 'down',
            'text-steel-500': trend === 'flat',
          }"
        >
          <ArrowUpIcon
            v-if="trend === 'up'"
            class="w-4 h-4"
          />
          <ArrowDownIcon
            v-if="trend === 'down'"
            class="w-4 h-4"
          />
          <span class="text-sm">{{ change }}%</span>
        </div>
      </div>

      <div class="opacity-30">
        <component
          :is="icon"
          class="w-12 h-12 text-forge-400"
        />
      </div>
    </div>
  </div>
</template>
