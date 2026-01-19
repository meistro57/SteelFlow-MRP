<script setup>
import {
    ChartBarIcon,
    ChartPieIcon,
    TableCellsIcon,
    RectangleGroupIcon,
    BoltIcon,
    ClockIcon,
    DocumentTextIcon,
    CubeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    templates: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['drag-start']);

// Icon mapping for widget types
const iconMap = {
    'metrics-card': RectangleGroupIcon,
    'chart-bar': ChartBarIcon,
    'chart-line': ChartBarIcon,
    'chart-pie': ChartPieIcon,
    'chart-doughnut': ChartPieIcon,
    'table': TableCellsIcon,
    'activity-feed': ClockIcon,
    'quick-actions': BoltIcon,
    'status-overview': DocumentTextIcon,
    'default': CubeIcon,
};

// Default templates when none are provided from backend
const defaultTemplates = {
    'Metrics': [
        {
            name: 'Metrics Card',
            slug: 'metrics-card',
            description: 'Display a single KPI metric',
            icon: 'rectangle-group',
            default_w: 3,
            default_h: 2,
            default_config: { metric: 'active_projects' },
        },
    ],
    'Charts': [
        {
            name: 'Bar Chart',
            slug: 'chart-bar',
            description: 'Horizontal or vertical bar chart',
            icon: 'chart-bar',
            default_w: 6,
            default_h: 4,
            default_config: {},
        },
        {
            name: 'Line Chart',
            slug: 'chart-line',
            description: 'Line or area chart',
            icon: 'chart-bar',
            default_w: 6,
            default_h: 4,
            default_config: {},
        },
        {
            name: 'Pie Chart',
            slug: 'chart-pie',
            description: 'Pie or doughnut chart',
            icon: 'chart-pie',
            default_w: 4,
            default_h: 4,
            default_config: {},
        },
    ],
    'Data': [
        {
            name: 'Data Table',
            slug: 'table',
            description: 'Sortable data table',
            icon: 'table',
            default_w: 6,
            default_h: 4,
            default_config: {},
        },
        {
            name: 'Activity Feed',
            slug: 'activity-feed',
            description: 'Recent activity timeline',
            icon: 'clock',
            default_w: 4,
            default_h: 4,
            default_config: {},
        },
    ],
    'Actions': [
        {
            name: 'Quick Actions',
            slug: 'quick-actions',
            description: 'Action buttons grid',
            icon: 'bolt',
            default_w: 4,
            default_h: 3,
            default_config: {},
        },
        {
            name: 'Status Overview',
            slug: 'status-overview',
            description: 'Status summary cards',
            icon: 'document-text',
            default_w: 4,
            default_h: 3,
            default_config: {},
        },
    ],
};

const displayTemplates = Object.keys(props.templates).length > 0 ? props.templates : defaultTemplates;

const getIcon = (slug) => {
    return iconMap[slug] || iconMap.default;
};

const handleDragStart = (event, template) => {
    event.dataTransfer.effectAllowed = 'copy';
    event.dataTransfer.setData('text/plain', JSON.stringify({ ...template, isTemplate: true }));
    emit('drag-start', event, { ...template, isTemplate: true });
};
</script>

<template>
  <div class="card-elevated sticky top-24">
    <h3 class="text-lg font-semibold text-steel-300 mb-4">
      Widget Toolbox
    </h3>
    <p class="text-steel-500 text-sm mb-6">
      Drag widgets to add them to your dashboard
    </p>

    <div class="space-y-6">
      <div
        v-for="(categoryTemplates, category) in displayTemplates"
        :key="category"
      >
        <h4 class="text-xs font-bold text-steel-400 uppercase tracking-wider mb-3">
          {{ category }}
        </h4>

        <div class="space-y-2">
          <div
            v-for="template in categoryTemplates"
            :key="template.slug"
            draggable="true"
            class="flex items-center gap-3 p-3 bg-steel-800 rounded-industrial border border-steel-700 cursor-grab hover:border-forge-500 hover:bg-steel-700 transition-all group"
            @dragstart="handleDragStart($event, template)"
          >
            <div class="w-10 h-10 bg-steel-700 rounded flex items-center justify-center group-hover:bg-forge-500/20 transition-colors">
              <component
                :is="getIcon(template.slug)"
                class="w-5 h-5 text-steel-400 group-hover:text-forge-400"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-steel-300">
                {{ template.name }}
              </div>
              <div class="text-xs text-steel-500 truncate">
                {{ template.description }}
              </div>
            </div>
            <div class="text-xs text-steel-600 font-mono">
              {{ template.default_w }}x{{ template.default_h }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
