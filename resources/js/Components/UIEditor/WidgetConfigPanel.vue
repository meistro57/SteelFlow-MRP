<script setup>
import { ref, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update', 'close']);

// Local form state
const form = ref({
    title: props.widget.title || '',
    config: { ...props.widget.config } || {},
});

// Watch for widget changes
watch(() => props.widget, (newWidget) => {
    form.value.title = newWidget.title || '';
    form.value.config = { ...newWidget.config } || {};
}, { deep: true });

// Metric options for metrics card
const metricOptions = [
    { value: 'active_projects', label: 'Active Projects' },
    { value: 'total_weight', label: 'Total Weight' },
    { value: 'production_progress', label: 'Production Progress' },
    { value: 'ready_to_ship', label: 'Ready to Ship' },
];

// Icon options
const iconOptions = [
    { value: 'folder', label: 'Folder' },
    { value: 'scale', label: 'Scale' },
    { value: 'chart-bar', label: 'Chart Bar' },
    { value: 'truck', label: 'Truck' },
    { value: 'bolt', label: 'Bolt' },
    { value: 'cube', label: 'Cube' },
];

const handleSubmit = () => {
    emit('update', {
        title: form.value.title,
        config: form.value.config,
    });
};
</script>

<template>
  <div class="card-elevated sticky top-24">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-steel-300">
        Widget Settings
      </h3>
      <button
        class="btn-ghost p-1"
        @click="emit('close')"
      >
        <XMarkIcon class="w-5 h-5" />
      </button>
    </div>

    <form @submit.prevent="handleSubmit">
      <!-- Title -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-steel-300 mb-2">
          Title
        </label>
        <input
          v-model="form.title"
          type="text"
          class="input w-full"
          placeholder="Widget title"
        >
      </div>

      <!-- Widget Type Info -->
      <div class="mb-4 p-3 bg-steel-800 rounded-industrial">
        <div class="text-xs text-steel-500 uppercase mb-1">
          Widget Type
        </div>
        <div class="text-steel-300 font-mono">
          {{ widget.widget_type }}
        </div>
      </div>

      <!-- Metrics Card Config -->
      <template v-if="widget.widget_type === 'metrics-card'">
        <div class="mb-4">
          <label class="block text-sm font-medium text-steel-300 mb-2">
            Metric
          </label>
          <select
            v-model="form.config.metric"
            class="input w-full"
          >
            <option
              v-for="option in metricOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-steel-300 mb-2">
            Icon
          </label>
          <select
            v-model="form.config.icon"
            class="input w-full"
          >
            <option
              v-for="option in iconOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>
      </template>

      <!-- Chart Config -->
      <template v-if="widget.widget_type.startsWith('chart-')">
        <div class="mb-4">
          <label class="block text-sm font-medium text-steel-300 mb-2">
            Chart Title
          </label>
          <input
            v-model="form.config.chartTitle"
            type="text"
            class="input w-full"
            placeholder="Chart title"
          >
        </div>

        <div class="mb-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.config.showLegend"
              type="checkbox"
              class="w-4 h-4 rounded border-steel-600 bg-steel-800 text-forge-500 focus:ring-forge-500"
            >
            <span class="text-sm text-steel-300">Show Legend</span>
          </label>
        </div>
      </template>

      <!-- Activity Feed Config -->
      <template v-if="widget.widget_type === 'activity-feed'">
        <div class="mb-4">
          <label class="block text-sm font-medium text-steel-300 mb-2">
            Max Items
          </label>
          <input
            v-model.number="form.config.maxItems"
            type="number"
            class="input w-full"
            min="3"
            max="20"
            placeholder="10"
          >
        </div>
      </template>

      <!-- Position Info -->
      <div class="mb-4 p-3 bg-steel-800 rounded-industrial">
        <div class="text-xs text-steel-500 uppercase mb-2">
          Position & Size
        </div>
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div class="text-steel-400">
            X: <span class="text-steel-300 font-mono">{{ widget.grid_x }}</span>
          </div>
          <div class="text-steel-400">
            Y: <span class="text-steel-300 font-mono">{{ widget.grid_y }}</span>
          </div>
          <div class="text-steel-400">
            W: <span class="text-steel-300 font-mono">{{ widget.grid_w }}</span>
          </div>
          <div class="text-steel-400">
            H: <span class="text-steel-300 font-mono">{{ widget.grid_h }}</span>
          </div>
        </div>
      </div>

      <!-- Visibility -->
      <div class="mb-6">
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            v-model="form.config.visible"
            type="checkbox"
            class="w-4 h-4 rounded border-steel-600 bg-steel-800 text-forge-500 focus:ring-forge-500"
            checked
          >
          <span class="text-sm text-steel-300">Visible</span>
        </label>
      </div>

      <!-- Actions -->
      <div class="flex gap-3">
        <button
          type="submit"
          class="btn-primary flex-1"
        >
          Save Changes
        </button>
      </div>
    </form>
  </div>
</template>
