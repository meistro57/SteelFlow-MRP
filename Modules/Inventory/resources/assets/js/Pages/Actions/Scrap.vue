<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stockItem: Object,
    canScrap: Boolean,
});

const form = useForm({
    reason: '',
    notes: '',
});

const submit = () => {
    form.post(`/inventory/${props.stockItem.id}/scrap`);
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

const scrapReasons = [
    'Damaged beyond repair',
    'Corrosion/rust',
    'Wrong specification',
    'Customer reject',
    'Production defect',
    'End of life',
    'Other',
];
</script>

<template>
  <AppLayout :title="`Scrap Stock: ${stockItem.stock_id}`">
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
            Scrap Stock Item
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Write off {{ stockItem.stock_id }}
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-2xl">
      <!-- Warning Banner -->
      <div class="card-industrial border-red-900 bg-red-900/20 mb-6">
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
              Warning: Permanent Action
            </h4>
            <p class="text-text-secondary text-sm mt-1">
              Scrapping an item is a permanent status change. The item will be marked as
              'scrapped' and cannot be used in any future operations.
            </p>
          </div>
        </div>
      </div>

      <!-- Stock Item Summary -->
      <div class="card-industrial mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">
          Stock Item to Scrap
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

      <!-- Cannot Scrap Warning -->
      <div
        v-if="!canScrap"
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
              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
            />
          </svg>
          <div>
            <h4 class="text-red-400 font-semibold">
              Cannot Scrap
            </h4>
            <p class="text-text-secondary text-sm mt-1">
              This stock item is already scrapped or cannot be scrapped from its current status.
            </p>
          </div>
        </div>
      </div>

      <!-- Scrap Form -->
      <div
        v-if="canScrap"
        class="card-industrial"
      >
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-red-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            />
          </svg>
          Scrap Details
        </h3>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Reason for Scrapping *
            </label>
            <select
              v-model="form.reason"
              class="input-industrial w-full"
              :class="{ 'border-red-500': form.errors.reason }"
            >
              <option value="">
                Select Reason
              </option>
              <option
                v-for="reason in scrapReasons"
                :key="reason"
                :value="reason"
              >
                {{ reason }}
              </option>
            </select>
            <p
              v-if="form.errors.reason"
              class="text-red-400 text-sm mt-1"
            >
              {{ form.errors.reason }}
            </p>
          </div>

          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Additional Notes (Optional)
            </label>
            <textarea
              v-model="form.notes"
              class="input-industrial w-full"
              rows="3"
              placeholder="Add any additional notes about this scrap action..."
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
              class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="form.processing || !form.reason"
            >
              <svg
                v-if="form.processing"
                class="w-5 h-5 mr-2 inline-block animate-spin"
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
              Confirm Scrap
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
