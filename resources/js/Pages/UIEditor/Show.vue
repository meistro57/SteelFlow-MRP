<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import WidgetCard from '@/Components/UIEditor/WidgetCard.vue';
import {
    ArrowLeftIcon,
    PencilSquareIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    dashboard: {
        type: Object,
        required: true,
    },
    widgets: {
        type: Array,
        default: () => [],
    },
    layout: {
        type: Array,
        default: () => [],
    },
});

// Computed grid style
const gridStyle = computed(() => ({
    display: 'grid',
    gridTemplateColumns: `repeat(${props.dashboard.columns}, 1fr)`,
    gap: '16px',
}));

// Get widget position from layout
const getWidgetStyle = (widget) => {
    const layoutItem = props.layout.find(l => l.i === widget.id);
    if (!layoutItem) return {};

    return {
        gridColumn: `${layoutItem.x + 1} / span ${layoutItem.w}`,
        gridRow: `${layoutItem.y + 1} / span ${layoutItem.h}`,
        minHeight: `${layoutItem.h * props.dashboard.row_height}px`,
    };
};

// Refresh widget data
const refreshWidget = async (widget) => {
    try {
        const response = await fetch(route('ui-editor.widgets.data', [props.dashboard.id, widget.id]));
        const data = await response.json();
        widget.data = data.data;
    } catch (error) {
        console.error('Failed to refresh widget:', error);
    }
};
</script>

<template>
  <AppLayout :title="dashboard.name">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Link
          :href="route('ui-editor.index')"
          class="btn-ghost"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-steel-300">
            {{ dashboard.name }}
          </h1>
          <p
            v-if="dashboard.description"
            class="text-steel-500 text-sm"
          >
            {{ dashboard.description }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          class="btn-ghost"
          title="Refresh All"
          @click="widgets.forEach(refreshWidget)"
        >
          <ArrowPathIcon class="w-5 h-5" />
        </button>
        <Link
          :href="route('ui-editor.edit', dashboard.id)"
          class="btn-primary"
        >
          <PencilSquareIcon class="w-5 h-5" />
          <span>Edit</span>
        </Link>
      </div>
    </div>

    <!-- Dashboard Grid -->
    <div
      :style="gridStyle"
      class="min-h-[400px]"
    >
      <WidgetCard
        v-for="widget in widgets"
        :key="widget.id"
        :widget="widget"
        :style="getWidgetStyle(widget)"
        :is-editing="false"
        @refresh="refreshWidget(widget)"
      />
    </div>

    <!-- Empty State -->
    <div
      v-if="widgets.length === 0"
      class="card-elevated text-center py-16"
    >
      <p class="text-steel-500 mb-4">
        This dashboard has no widgets yet.
      </p>
      <Link
        :href="route('ui-editor.edit', dashboard.id)"
        class="btn-primary"
      >
        <PencilSquareIcon class="w-5 h-5" />
        <span>Add Widgets</span>
      </Link>
    </div>
  </AppLayout>
</template>
