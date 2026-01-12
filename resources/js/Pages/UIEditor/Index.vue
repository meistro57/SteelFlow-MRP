<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    DocumentDuplicateIcon,
    StarIcon,
    ShareIcon,
    Squares2X2Icon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    dashboards: {
        type: Array,
        default: () => [],
    },
});

const setDefault = (dashboard) => {
    router.post(route('ui-editor.set-default', dashboard.id), {}, {
        preserveScroll: true,
    });
};

const deleteDashboard = (dashboard) => {
    if (confirm(`Are you sure you want to delete "${dashboard.name}"?`)) {
        router.delete(route('ui-editor.destroy', dashboard.id));
    }
};

const duplicateDashboard = (dashboard) => {
    router.post(route('ui-editor.duplicate', dashboard.id), {
        name: `${dashboard.name} (Copy)`,
    });
};
</script>

<template>
  <AppLayout title="Dashboard Builder">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-300 text-glow-forge">
          DASHBOARD BUILDER
        </h1>
        <p class="text-steel-500 mt-1">
          Create and customize your dashboard layouts
        </p>
      </div>
      <div class="flex gap-3">
        <Link
          :href="route('ui-editor.create')"
          class="btn-primary"
        >
          <PlusIcon class="w-5 h-5" />
          <span>New Dashboard</span>
        </Link>
      </div>
    </div>

    <!-- Dashboards Grid -->
    <div
      v-if="dashboards.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <div
        v-for="dashboard in dashboards"
        :key="dashboard.id"
        class="card-elevated group hover:border-forge-500/50 transition-all"
      >
        <!-- Dashboard Preview -->
        <div class="relative h-40 bg-steel-800 rounded-t-industrial mb-4 overflow-hidden">
          <div class="absolute inset-0 grid grid-cols-12 gap-1 p-2 opacity-30">
            <div
              v-for="widget in dashboard.widgets?.slice(0, 6) || []"
              :key="widget.id"
              class="bg-steel-600 rounded"
              :style="{
                gridColumn: `span ${Math.min(widget.grid_w, 4)}`,
                gridRow: `span ${Math.min(widget.grid_h, 2)}`,
              }"
            />
          </div>
          <div class="absolute inset-0 flex items-center justify-center">
            <Squares2X2Icon class="w-16 h-16 text-steel-600" />
          </div>

          <!-- Default badge -->
          <div
            v-if="dashboard.is_default"
            class="absolute top-2 right-2 px-2 py-1 bg-forge-500 text-white text-xs font-bold rounded"
          >
            DEFAULT
          </div>

          <!-- Shared badge -->
          <div
            v-if="dashboard.is_shared"
            class="absolute top-2 left-2 px-2 py-1 bg-weld-500 text-white text-xs font-bold rounded flex items-center gap-1"
          >
            <ShareIcon class="w-3 h-3" />
            SHARED
          </div>
        </div>

        <!-- Dashboard Info -->
        <div class="px-4 pb-4">
          <h3 class="text-lg font-semibold text-steel-300 mb-1">
            {{ dashboard.name }}
          </h3>
          <p class="text-steel-500 text-sm mb-4 line-clamp-2">
            {{ dashboard.description || 'No description' }}
          </p>

          <div class="flex items-center justify-between text-xs text-steel-500 mb-4">
            <span>{{ dashboard.widgets?.length || 0 }} widgets</span>
            <span>{{ dashboard.columns }} columns</span>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <Link
              :href="route('ui-editor.edit', dashboard.id)"
              class="btn-primary flex-1 text-sm"
            >
              <PencilSquareIcon class="w-4 h-4" />
              <span>Edit</span>
            </Link>
            <Link
              :href="route('ui-editor.show', dashboard.id)"
              class="btn-secondary flex-1 text-sm"
            >
              <span>View</span>
            </Link>
          </div>

          <!-- Secondary Actions -->
          <div class="flex items-center gap-1 mt-3 pt-3 border-t border-steel-700">
            <button
              class="btn-ghost text-xs flex-1"
              :class="{ 'text-forge-400': dashboard.is_default }"
              :title="dashboard.is_default ? 'Default Dashboard' : 'Set as Default'"
              @click="setDefault(dashboard)"
            >
              <component
                :is="dashboard.is_default ? StarSolidIcon : StarIcon"
                class="w-4 h-4"
              />
            </button>
            <button
              class="btn-ghost text-xs flex-1"
              title="Duplicate"
              @click="duplicateDashboard(dashboard)"
            >
              <DocumentDuplicateIcon class="w-4 h-4" />
            </button>
            <button
              class="btn-ghost text-xs flex-1 text-red-400 hover:text-red-300"
              title="Delete"
              @click="deleteDashboard(dashboard)"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="card-elevated text-center py-16"
    >
      <Squares2X2Icon class="w-16 h-16 text-steel-600 mx-auto mb-4" />
      <h3 class="text-xl font-semibold text-steel-300 mb-2">
        No Dashboards Yet
      </h3>
      <p class="text-steel-500 mb-6">
        Create your first custom dashboard to get started.
      </p>
      <Link
        :href="route('ui-editor.create')"
        class="btn-primary"
      >
        <PlusIcon class="w-5 h-5" />
        <span>Create Dashboard</span>
      </Link>
    </div>
  </AppLayout>
</template>
