<script setup>
import { computed } from 'vue';
import {
    XMarkIcon,
    ArrowsPointingOutIcon,
    Cog6ToothIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';
import MetricsCardWidget from '@/Components/UIEditor/widgets/MetricsCardWidget.vue';
import ChartWidget from '@/Components/UIEditor/widgets/ChartWidget.vue';
import TableWidget from '@/Components/UIEditor/widgets/TableWidget.vue';
import ActivityFeedWidget from '@/Components/UIEditor/widgets/ActivityFeedWidget.vue';
import QuickActionsWidget from '@/Components/UIEditor/widgets/QuickActionsWidget.vue';
import StatusOverviewWidget from '@/Components/UIEditor/widgets/StatusOverviewWidget.vue';

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
    isSelected: {
        type: Boolean,
        default: false,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select', 'remove', 'drag-start', 'drag-end', 'resize', 'refresh']);

// Map widget types to components
const widgetComponents = {
    'metrics-card': MetricsCardWidget,
    'chart-bar': ChartWidget,
    'chart-line': ChartWidget,
    'chart-pie': ChartWidget,
    'chart-doughnut': ChartWidget,
    'table': TableWidget,
    'activity-feed': ActivityFeedWidget,
    'quick-actions': QuickActionsWidget,
    'status-overview': StatusOverviewWidget,
};

const widgetComponent = computed(() => {
    return widgetComponents[props.widget.widget_type] || null;
});

const handleDragStart = (event) => {
    if (!props.isEditing) return;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', JSON.stringify(props.widget));
    emit('drag-start', event, props.widget);
};
</script>

<template>
  <div
    class="card relative group transition-all"
    :class="{
      'ring-2 ring-forge-500 shadow-glow-forge': isSelected,
      'hover:ring-1 hover:ring-steel-600': isEditing && !isSelected,
      'cursor-grab': isEditing,
    }"
    :draggable="isEditing"
    @click="emit('select')"
    @dragstart="handleDragStart"
    @dragend="emit('drag-end')"
  >
    <!-- Widget Header -->
    <div class="flex items-center justify-between mb-3 pb-3 border-b border-steel-700">
      <h4 class="font-semibold text-steel-300 text-sm truncate">
        {{ widget.title || widget.widget_type }}
      </h4>

      <!-- Edit Mode Actions -->
      <div
        v-if="isEditing"
        class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <button
          class="p-1 text-steel-500 hover:text-steel-300 rounded"
          title="Configure"
          @click.stop="emit('select')"
        >
          <Cog6ToothIcon class="w-4 h-4" />
        </button>
        <button
          class="p-1 text-steel-500 hover:text-red-400 rounded"
          title="Remove"
          @click.stop="emit('remove')"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>

      <!-- View Mode Actions -->
      <div
        v-else
        class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <button
          class="p-1 text-steel-500 hover:text-steel-300 rounded"
          title="Refresh"
          @click.stop="emit('refresh')"
        >
          <ArrowPathIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Widget Content -->
    <div class="flex-1 overflow-hidden">
      <component
        :is="widgetComponent"
        v-if="widgetComponent"
        :widget="widget"
        :data="widget.data"
        :config="widget.config"
      />
      <div
        v-else
        class="h-full flex items-center justify-center text-steel-500 text-sm"
      >
        Unknown widget type: {{ widget.widget_type }}
      </div>
    </div>

    <!-- Resize Handle (Edit Mode Only) -->
    <div
      v-if="isEditing"
      class="absolute bottom-0 right-0 w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100 transition-opacity"
      @mousedown.stop.prevent="/* resize logic would go here */"
    >
      <ArrowsPointingOutIcon class="w-4 h-4 text-steel-500 rotate-90" />
    </div>
  </div>
</template>
