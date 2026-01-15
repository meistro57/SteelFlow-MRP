<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stockItem: Object,
    stockAreas: Object,
});

const form = useForm({
    stock_area: '',
    notes: '',
});

const submit = () => {
    form.post(`/inventory/${props.stockItem.id}/transfer`);
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};
</script>

<template>
  <AppLayout :title="`Transfer Stock: ${stockItem.stock_id}`">
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
            Transfer Stock
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Move {{ stockItem.stock_id }} to a different location
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
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Current Location</span>
            <div class="mt-1 font-mono text-weld-400 font-bold uppercase">
              {{ stockItem.stock_area?.replace('_', ' ') || 'Not Set' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Transfer Form -->
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-purple-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
            />
          </svg>
          Transfer Details
        </h3>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Destination Location *
            </label>
            <select
              v-model="form.stock_area"
              class="input-industrial w-full"
              :class="{ 'border-red-500': form.errors.stock_area }"
            >
              <option value="">
                Select Destination
              </option>
              <option
                v-for="(label, value) in stockAreas"
                :key="value"
                :value="value"
                :disabled="value === stockItem.stock_area"
              >
                {{ label }}
                <template v-if="value === stockItem.stock_area">
                  (Current)
                </template>
              </option>
            </select>
            <p
              v-if="form.errors.stock_area"
              class="text-red-400 text-sm mt-1"
            >
              {{ form.errors.stock_area }}
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
              placeholder="Add any notes about this transfer..."
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
              :disabled="form.processing || !form.stock_area"
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
              Transfer Stock
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
