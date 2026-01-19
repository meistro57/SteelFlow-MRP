<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    stockItem: Object,
    projects: Array,
    canAssign: Boolean,
});

const form = useForm({
    project_id: '',
    notes: '',
});

const submit = () => {
    form.post(`/inventory/${props.stockItem.id}/assign`);
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};

const getStatusColor = (status) => {
    const colors = {
        free: 'badge-free',
        assigned: 'badge-assigned',
        committed: 'badge-in-progress',
        used: 'badge-complete',
        scrapped: 'badge-cancelled',
    };
    return colors[status] || 'badge';
};
</script>

<template>
  <AppLayout :title="`Assign Stock: ${stockItem.stock_id}`">
    <template #header>
      <div class="flex items-center">
        <Link
          :href="`/inventory/${stockItem.id}`"
          class="text-text-secondary hover:text-white transition-colors mr-4"
        >
          &larr; Back to Stock Item
        </Link>
        <div>
          <h1 class="text-3xl font-bold text-white">
            Assign to Project
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Assign {{ stockItem.stock_id }} to a project
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-2xl">
      <!-- Stock Item Summary -->
      <div class="card-industrial mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">
          Stock Item
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
          <div>
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Stock ID</span>
            <div class="mt-1 font-mono text-weld-400 font-semibold">
              {{ stockItem.stock_id }}
            </div>
          </div>
          <div>
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Material</span>
            <div class="mt-1 font-mono text-white">
              {{ stockItem.type }} {{ stockItem.size }}
            </div>
          </div>
          <div>
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Length</span>
            <div class="mt-1 font-mono text-white">
              {{ formatLength(stockItem.length) }}
            </div>
          </div>
          <div>
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Status</span>
            <div class="mt-1">
              <span
                :class="getStatusColor(stockItem.status)"
                class="badge"
              >
                {{ stockItem.status }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Cannot Assign Warning -->
      <div
        v-if="!canAssign"
        class="card-industrial border-red-900 bg-red-900/20 mb-6"
      >
        <div class="flex items-start">
          <svg
            class="w-6 h-6 text-red-400 mr-3 flex-shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <div>
            <h4 class="text-red-400 font-semibold">
              Cannot Assign
            </h4>
            <p class="text-text-secondary text-sm mt-1">
              This stock item cannot be assigned from its current status ({{ stockItem.status }}).
              Only items with status 'free', 'assigned', or 'committed' can be assigned to projects.
            </p>
          </div>
        </div>
      </div>

      <!-- Assign Form -->
      <div
        v-if="canAssign"
        class="card-industrial"
      >
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-blue-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Assignment Details
        </h3>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Project *
            </label>
            <select
              v-model="form.project_id"
              class="input-industrial w-full"
              :class="{ 'border-red-500': form.errors.project_id }"
            >
              <option value="">
                Select Project
              </option>
              <option
                v-for="project in projects"
                :key="project.id"
                :value="project.id"
              >
                {{ project.job_number }} - {{ project.name }}
              </option>
            </select>
            <p
              v-if="form.errors.project_id"
              class="text-red-400 text-sm mt-1"
            >
              {{ form.errors.project_id }}
            </p>
          </div>

          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Notes (Optional)
            </label>
            <textarea
              v-model="form.notes"
              class="input-industrial w-full"
              rows="3"
              placeholder="Add any notes about this assignment..."
            />
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-steel-700">
            <Link
              :href="`/inventory/${stockItem.id}`"
              class="btn-ghost"
            >
              Cancel
            </Link>
            <button
              type="submit"
              class="btn-primary"
              :disabled="form.processing || !form.project_id"
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
              Assign to Project
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
