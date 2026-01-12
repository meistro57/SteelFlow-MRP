import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useUIEditorStore = defineStore('uiEditor', () => {
    // State
    const dashboards = ref([]);
    const currentDashboard = ref(null);
    const widgets = ref([]);
    const layout = ref([]);
    const selectedWidgetId = ref(null);
    const isDragging = ref(false);
    const hasUnsavedChanges = ref(false);
    const isLoading = ref(false);
    const error = ref(null);

    // Getters
    const selectedWidget = computed(() => {
        if (!selectedWidgetId.value) return null;
        return widgets.value.find(w => w.id === selectedWidgetId.value) || null;
    });

    const widgetCount = computed(() => widgets.value.length);

    const getWidgetLayout = computed(() => (widgetId) => {
        return layout.value.find(l => l.i === widgetId);
    });

    // Actions
    function setDashboards(data) {
        dashboards.value = data;
    }

    function setCurrentDashboard(dashboard) {
        currentDashboard.value = dashboard;
    }

    function setWidgets(data) {
        widgets.value = data;
    }

    function setLayout(data) {
        layout.value = data;
    }

    function selectWidget(widgetId) {
        selectedWidgetId.value = widgetId;
    }

    function deselectWidget() {
        selectedWidgetId.value = null;
    }

    function setDragging(value) {
        isDragging.value = value;
    }

    function markAsChanged() {
        hasUnsavedChanges.value = true;
    }

    function markAsSaved() {
        hasUnsavedChanges.value = false;
    }

    function addWidget(widget) {
        widgets.value.push(widget);
        layout.value.push({
            i: widget.id,
            x: widget.grid_x,
            y: widget.grid_y,
            w: widget.grid_w,
            h: widget.grid_h,
            minW: widget.min_w,
            minH: widget.min_h,
        });
        markAsChanged();
    }

    function updateWidget(widgetId, data) {
        const index = widgets.value.findIndex(w => w.id === widgetId);
        if (index !== -1) {
            widgets.value[index] = { ...widgets.value[index], ...data };
            markAsChanged();
        }
    }

    function removeWidget(widgetId) {
        widgets.value = widgets.value.filter(w => w.id !== widgetId);
        layout.value = layout.value.filter(l => l.i !== widgetId);
        if (selectedWidgetId.value === widgetId) {
            deselectWidget();
        }
        markAsChanged();
    }

    function updateWidgetPosition(widgetId, position) {
        const layoutIndex = layout.value.findIndex(l => l.i === widgetId);
        if (layoutIndex !== -1) {
            layout.value[layoutIndex] = {
                ...layout.value[layoutIndex],
                x: position.x,
                y: position.y,
                w: position.w,
                h: position.h,
            };
            markAsChanged();
        }
    }

    function updateLayout(newLayout) {
        layout.value = newLayout;
        markAsChanged();
    }

    function setLoading(value) {
        isLoading.value = value;
    }

    function setError(err) {
        error.value = err;
    }

    function clearError() {
        error.value = null;
    }

    function reset() {
        currentDashboard.value = null;
        widgets.value = [];
        layout.value = [];
        selectedWidgetId.value = null;
        isDragging.value = false;
        hasUnsavedChanges.value = false;
        error.value = null;
    }

    return {
        // State
        dashboards,
        currentDashboard,
        widgets,
        layout,
        selectedWidgetId,
        isDragging,
        hasUnsavedChanges,
        isLoading,
        error,

        // Getters
        selectedWidget,
        widgetCount,
        getWidgetLayout,

        // Actions
        setDashboards,
        setCurrentDashboard,
        setWidgets,
        setLayout,
        selectWidget,
        deselectWidget,
        setDragging,
        markAsChanged,
        markAsSaved,
        addWidget,
        updateWidget,
        removeWidget,
        updateWidgetPosition,
        updateLayout,
        setLoading,
        setError,
        clearError,
        reset,
    };
});
