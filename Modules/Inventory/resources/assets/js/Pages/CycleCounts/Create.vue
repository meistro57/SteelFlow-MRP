<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stockAreas: Object,
    types: Array,
    grades: Array,
    users: Array,
});

const form = useForm({
    stock_area: '',
    type_filter: '',
    grade_filter: '',
    scheduled_date: '',
    assigned_to: '',
    notes: '',
});

const submit = () => {
    form.post('/cycle-counts');
};
</script>

<template>
  <AppLayout title="Create Cycle Count">
    <template #header>
      <div class="flex items-center">
        <Link
          href="/cycle-counts"
          class="text-text-secondary hover:text-white transition-colors mr-4"
        >
          &larr; Back to Cycle Counts
        </Link>
        <div>
          <h1 class="text-3xl font-bold text-white">
            Create Cycle Count
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Set up a new physical inventory count
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-2xl">
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-weld-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
            />
          </svg>
          Cycle Count Settings
        </h3>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div class="bg-steel-900 border border-steel-700 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-white mb-2">
              Item Filters
            </h4>
            <p class="text-text-tertiary text-xs mb-4">
              Select filters to narrow down which items to include in this count.
              Leave empty to include all active stock items.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                  Stock Area
                </label>
                <select
                  v-model="form.stock_area"
                  class="input-industrial w-full"
                >
                  <option value="">
                    All Areas
                  </option>
                  <option
                    v-for="(label, value) in stockAreas"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                  Material Type
                </label>
                <select
                  v-model="form.type_filter"
                  class="input-industrial w-full"
                >
                  <option value="">
                    All Types
                  </option>
                  <option
                    v-for="type in types"
                    :key="type"
                    :value="type"
                  >
                    {{ type }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                  Grade
                </label>
                <select
                  v-model="form.grade_filter"
                  class="input-industrial w-full"
                >
                  <option value="">
                    All Grades
                  </option>
                  <option
                    v-for="grade in grades"
                    :key="grade"
                    :value="grade"
                  >
                    {{ grade }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                Scheduled Date
              </label>
              <input
                v-model="form.scheduled_date"
                type="date"
                class="input-industrial w-full"
              >
            </div>

            <div>
              <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                Assign To
              </label>
              <select
                v-model="form.assigned_to"
                class="input-industrial w-full"
              >
                <option value="">
                  Unassigned
                </option>
                <option
                  v-for="user in users"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Notes
            </label>
            <textarea
              v-model="form.notes"
              class="input-industrial w-full"
              rows="3"
              placeholder="Add any notes about this cycle count..."
            />
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-steel-700">
            <Link
              href="/cycle-counts"
              class="btn-ghost"
            >
              Cancel
            </Link>
            <button
              type="submit"
              class="btn-primary"
              :disabled="form.processing"
            >
              <svg
                v-if="form.processing"
                class="w-5 h-5 mr-2 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              Create Cycle Count
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
