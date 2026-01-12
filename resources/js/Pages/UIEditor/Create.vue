<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    Squares2X2Icon,
} from '@heroicons/vue/24/outline';

defineProps({
    widgetTemplates: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    name: '',
    description: '',
    columns: 12,
    row_height: 80,
});

const submit = () => {
    form.post(route('ui-editor.store'));
};
</script>

<template>
  <AppLayout title="Create Dashboard">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Link
          :href="route('ui-editor.index')"
          class="btn-ghost"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-3xl font-bold text-steel-300 text-glow-forge">
            CREATE DASHBOARD
          </h1>
          <p class="text-steel-500 mt-1">
            Set up your new custom dashboard
          </p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="max-w-2xl">
      <form
        class="card-elevated"
        @submit.prevent="submit"
      >
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-steel-700">
          <div class="w-16 h-16 bg-forge-500/20 rounded-industrial flex items-center justify-center">
            <Squares2X2Icon class="w-8 h-8 text-forge-400" />
          </div>
          <div>
            <h2 class="text-xl font-semibold text-steel-300">
              Dashboard Details
            </h2>
            <p class="text-steel-500 text-sm">
              Configure your dashboard settings
            </p>
          </div>
        </div>

        <!-- Name -->
        <div class="mb-6">
          <label
            for="name"
            class="block text-sm font-medium text-steel-300 mb-2"
          >
            Dashboard Name *
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="input w-full"
            placeholder="My Dashboard"
            required
          >
          <p
            v-if="form.errors.name"
            class="mt-1 text-sm text-red-400"
          >
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Description -->
        <div class="mb-6">
          <label
            for="description"
            class="block text-sm font-medium text-steel-300 mb-2"
          >
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            class="input w-full"
            rows="3"
            placeholder="Optional description for this dashboard"
          />
          <p
            v-if="form.errors.description"
            class="mt-1 text-sm text-red-400"
          >
            {{ form.errors.description }}
          </p>
        </div>

        <!-- Grid Settings -->
        <div class="grid grid-cols-2 gap-6 mb-6">
          <div>
            <label
              for="columns"
              class="block text-sm font-medium text-steel-300 mb-2"
            >
              Grid Columns
            </label>
            <select
              id="columns"
              v-model="form.columns"
              class="input w-full"
            >
              <option :value="6">
                6 Columns
              </option>
              <option :value="12">
                12 Columns (Recommended)
              </option>
              <option :value="24">
                24 Columns
              </option>
            </select>
            <p class="mt-1 text-xs text-steel-500">
              More columns = finer positioning control
            </p>
          </div>

          <div>
            <label
              for="row_height"
              class="block text-sm font-medium text-steel-300 mb-2"
            >
              Row Height (px)
            </label>
            <input
              id="row_height"
              v-model.number="form.row_height"
              type="number"
              class="input w-full"
              min="40"
              max="200"
            >
            <p class="mt-1 text-xs text-steel-500">
              Height of each grid row
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-steel-700">
          <Link
            :href="route('ui-editor.index')"
            class="btn-secondary"
          >
            Cancel
          </Link>
          <button
            type="submit"
            class="btn-primary"
            :disabled="form.processing"
          >
            <span v-if="form.processing">Creating...</span>
            <span v-else>Create Dashboard</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
