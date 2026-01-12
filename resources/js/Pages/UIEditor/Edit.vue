<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import WidgetToolbox from '@/Components/UIEditor/WidgetToolbox.vue';
import WidgetCard from '@/Components/UIEditor/WidgetCard.vue';
import WidgetConfigPanel from '@/Components/UIEditor/WidgetConfigPanel.vue';
import {
    ArrowLeftIcon,
    Cog6ToothIcon,
    EyeIcon,
    CheckIcon,
    XMarkIcon,
    Squares2X2Icon,
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
    widgetTemplates: {
        type: Object,
        default: () => ({}),
    },
});

// State
const localWidgets = ref([...props.widgets]);
const localLayout = ref([...props.layout]);
const selectedWidget = ref(null);
const isDragging = ref(false);
const draggedWidget = ref(null);
const hasUnsavedChanges = ref(false);
const isSaving = ref(false);
const showSettings = ref(false);
const gridRef = ref(null);

// Computed
const gridStyle = computed(() => ({
    display: 'grid',
    gridTemplateColumns: `repeat(${props.dashboard.columns}, 1fr)`,
    gap: '8px',
    minHeight: '600px',
}));

// Get widget position from layout
const getWidgetStyle = (widget) => {
    const layoutItem = localLayout.value.find(l => l.i === widget.id);
    if (!layoutItem) return {};

    return {
        gridColumn: `${layoutItem.x + 1} / span ${layoutItem.w}`,
        gridRow: `${layoutItem.y + 1} / span ${layoutItem.h}`,
        minHeight: `${layoutItem.h * props.dashboard.row_height}px`,
    };
};

// Drag and drop handlers
const handleDragStart = (event, widget) => {
    isDragging.value = true;
    draggedWidget.value = widget;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', JSON.stringify(widget));
};

const handleDragEnd = () => {
    isDragging.value = false;
    draggedWidget.value = null;
};

const handleDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
};

const handleDrop = (event) => {
    event.preventDefault();

    const data = event.dataTransfer.getData('text/plain');
    if (!data) return;

    const widget = JSON.parse(data);

    // Calculate drop position
    const rect = gridRef.value.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    const colWidth = rect.width / props.dashboard.columns;
    const newX = Math.floor(x / colWidth);
    const newY = Math.floor(y / props.dashboard.row_height);

    // If it's a new widget from toolbox
    if (widget.isTemplate) {
        addWidgetFromTemplate(widget, newX, newY);
    } else {
        // Move existing widget
        updateWidgetPosition(widget.id, newX, newY);
    }

    isDragging.value = false;
    draggedWidget.value = null;
};

// Add new widget from template
const addWidgetFromTemplate = async (template, x, y) => {
    try {
        const response = await fetch(route('ui-editor.widgets.store', props.dashboard.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                widget_type: template.slug,
                title: template.name,
                grid_x: Math.max(0, Math.min(x, props.dashboard.columns - template.default_w)),
                grid_y: y,
                grid_w: template.default_w,
                grid_h: template.default_h,
                config: template.default_config || {},
            }),
        });

        const data = await response.json();
        if (data.success) {
            localWidgets.value.push(data.widget);
            localLayout.value.push({
                i: data.widget.id,
                x: data.widget.grid_x,
                y: data.widget.grid_y,
                w: data.widget.grid_w,
                h: data.widget.grid_h,
                minW: data.widget.min_w,
                minH: data.widget.min_h,
            });
        }
    } catch (error) {
        console.error('Failed to add widget:', error);
    }
};

// Update widget position
const updateWidgetPosition = (widgetId, newX, newY) => {
    const layoutIndex = localLayout.value.findIndex(l => l.i === widgetId);
    if (layoutIndex === -1) return;

    const item = localLayout.value[layoutIndex];
    const maxX = props.dashboard.columns - item.w;

    localLayout.value[layoutIndex] = {
        ...item,
        x: Math.max(0, Math.min(newX, maxX)),
        y: Math.max(0, newY),
    };

    hasUnsavedChanges.value = true;
};

// Resize widget
const resizeWidget = (widgetId, newW, newH) => {
    const layoutIndex = localLayout.value.findIndex(l => l.i === widgetId);
    if (layoutIndex === -1) return;

    const item = localLayout.value[layoutIndex];

    localLayout.value[layoutIndex] = {
        ...item,
        w: Math.max(item.minW || 2, Math.min(newW, props.dashboard.columns - item.x)),
        h: Math.max(item.minH || 2, newH),
    };

    hasUnsavedChanges.value = true;
};

// Remove widget
const removeWidget = async (widget) => {
    if (!confirm('Remove this widget from the dashboard?')) return;

    try {
        const response = await fetch(route('ui-editor.widgets.destroy', [props.dashboard.id, widget.id]), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();
        if (data.success) {
            localWidgets.value = localWidgets.value.filter(w => w.id !== widget.id);
            localLayout.value = localLayout.value.filter(l => l.i !== widget.id);
            if (selectedWidget.value?.id === widget.id) {
                selectedWidget.value = null;
            }
        }
    } catch (error) {
        console.error('Failed to remove widget:', error);
    }
};

// Save layout
const saveLayout = async () => {
    isSaving.value = true;

    try {
        const response = await fetch(route('ui-editor.layout.update', props.dashboard.id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ layout: localLayout.value }),
        });

        const data = await response.json();
        if (data.success) {
            hasUnsavedChanges.value = false;
        }
    } catch (error) {
        console.error('Failed to save layout:', error);
    } finally {
        isSaving.value = false;
    }
};

// Select widget for configuration
const selectWidget = (widget) => {
    selectedWidget.value = widget;
};

// Update widget config
const updateWidgetConfig = async (widget, config) => {
    try {
        const response = await fetch(route('ui-editor.widgets.update', [props.dashboard.id, widget.id]), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(config),
        });

        const data = await response.json();
        if (data.success) {
            const index = localWidgets.value.findIndex(w => w.id === widget.id);
            if (index !== -1) {
                localWidgets.value[index] = data.widget;
            }
            selectedWidget.value = data.widget;
        }
    } catch (error) {
        console.error('Failed to update widget:', error);
    }
};

// Warn before leaving with unsaved changes
const handleBeforeUnload = (event) => {
    if (hasUnsavedChanges.value) {
        event.preventDefault();
        event.returnValue = '';
    }
};

onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>

<template>
  <AppLayout title="Edit Dashboard">
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
          <p class="text-steel-500 text-sm">
            Drag widgets from the toolbox to build your dashboard
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <!-- Unsaved indicator -->
        <span
          v-if="hasUnsavedChanges"
          class="text-safety-400 text-sm flex items-center gap-1"
        >
          <span class="w-2 h-2 bg-safety-400 rounded-full animate-pulse" />
          Unsaved changes
        </span>

        <button
          class="btn-ghost"
          @click="showSettings = !showSettings"
        >
          <Cog6ToothIcon class="w-5 h-5" />
        </button>

        <Link
          :href="route('ui-editor.show', dashboard.id)"
          class="btn-secondary"
        >
          <EyeIcon class="w-5 h-5" />
          <span>Preview</span>
        </Link>

        <button
          class="btn-primary"
          :disabled="!hasUnsavedChanges || isSaving"
          @click="saveLayout"
        >
          <CheckIcon class="w-5 h-5" />
          <span>{{ isSaving ? 'Saving...' : 'Save Layout' }}</span>
        </button>
      </div>
    </div>

    <!-- Main Editor Area -->
    <div class="flex gap-6">
      <!-- Widget Toolbox (Left Sidebar) -->
      <div class="w-64 flex-shrink-0">
        <WidgetToolbox
          :templates="widgetTemplates"
          @drag-start="handleDragStart"
        />
      </div>

      <!-- Grid Editor -->
      <div class="flex-1">
        <div
          ref="gridRef"
          class="bg-steel-800/50 rounded-industrial border-2 border-dashed border-steel-700 p-4 min-h-[600px] relative"
          :class="{ 'border-forge-500 bg-forge-500/5': isDragging }"
          :style="gridStyle"
          @dragover="handleDragOver"
          @drop="handleDrop"
        >
          <!-- Grid Overlay (for visual guidance) -->
          <div
            v-if="isDragging"
            class="absolute inset-0 pointer-events-none"
          >
            <div
              class="w-full h-full grid opacity-20"
              :style="{ gridTemplateColumns: `repeat(${dashboard.columns}, 1fr)` }"
            >
              <div
                v-for="i in dashboard.columns"
                :key="i"
                class="border-r border-steel-600 h-full"
              />
            </div>
          </div>

          <!-- Widgets -->
          <WidgetCard
            v-for="widget in localWidgets"
            :key="widget.id"
            :widget="widget"
            :style="getWidgetStyle(widget)"
            :is-selected="selectedWidget?.id === widget.id"
            :is-editing="true"
            @select="selectWidget(widget)"
            @remove="removeWidget(widget)"
            @drag-start="handleDragStart($event, widget)"
            @drag-end="handleDragEnd"
            @resize="(w, h) => resizeWidget(widget.id, w, h)"
          />

          <!-- Empty State -->
          <div
            v-if="localWidgets.length === 0"
            class="absolute inset-0 flex items-center justify-center col-span-full"
          >
            <div class="text-center">
              <Squares2X2Icon class="w-16 h-16 text-steel-600 mx-auto mb-4" />
              <p class="text-steel-500">
                Drag widgets here to build your dashboard
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Widget Config Panel (Right Sidebar) -->
      <div
        v-if="selectedWidget"
        class="w-80 flex-shrink-0"
      >
        <WidgetConfigPanel
          :widget="selectedWidget"
          @update="(config) => updateWidgetConfig(selectedWidget, config)"
          @close="selectedWidget = null"
        />
      </div>
    </div>

    <!-- Settings Modal -->
    <div
      v-if="showSettings"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="card-elevated w-full max-w-md">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-steel-300">
            Dashboard Settings
          </h2>
          <button
            class="btn-ghost"
            @click="showSettings = false"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="router.put(route('ui-editor.update', dashboard.id), { name: dashboard.name, description: dashboard.description })">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-steel-300 mb-2">
                Name
              </label>
              <input
                v-model="dashboard.name"
                type="text"
                class="input w-full"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-steel-300 mb-2">
                Description
              </label>
              <textarea
                v-model="dashboard.description"
                class="input w-full"
                rows="3"
              />
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-steel-700">
            <button
              type="button"
              class="btn-secondary"
              @click="showSettings = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
            >
              Save Settings
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
