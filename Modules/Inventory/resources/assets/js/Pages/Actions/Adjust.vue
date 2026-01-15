<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    stockItem: Object,
});

const form = useForm({
    quantity: props.stockItem.quantity,
    reason: '',
    notes: '',
});

const submit = () => {
    form.post(`/inventory/${props.stockItem.id}/adjust`);
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};

const difference = computed(() => {
    return form.quantity - props.stockItem.quantity;
});

const differenceText = computed(() => {
    if (difference.value > 0) return `+${difference.value}`;
    if (difference.value < 0) return `${difference.value}`;
    return '0 (no change)';
});

const adjustmentReasons = [
    'Physical count correction',
    'Damage write-off',
    'Found stock',
    'Data entry error',
    'Split/combined items',
    'Other',
];
</script>

<template>
  <AppLayout :title="`Adjust Stock: ${stockItem.stock_id}`">
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
            Adjust Quantity
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Correct the quantity for {{ stockItem.stock_id }}
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
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Current Quantity</span>
            <div class="mt-1 font-mono text-weld-400 font-bold text-xl">
              {{ stockItem.quantity }}
            </div>
          </div>
        </div>
      </div>

      <!-- Adjust Form -->
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-yellow-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
            />
          </svg>
          Adjustment Details
        </h3>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                New Quantity *
              </label>
              <input
                v-model.number="form.quantity"
                type="number"
                min="0"
                class="input-industrial w-full text-xl font-mono font-bold"
                :class="{ 'border-red-500': form.errors.quantity }"
              >
              <p
                v-if="form.errors.quantity"
                class="text-red-400 text-sm mt-1"
              >
                {{ form.errors.quantity }}
              </p>
            </div>
            <div>
              <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                Difference
              </label>
              <div
                class="input-industrial w-full text-xl font-mono font-bold bg-steel-900 flex items-center"
                :class="{
                  'text-green-400': difference > 0,
                  'text-red-400': difference < 0,
                  'text-text-tertiary': difference === 0
                }"
              >
                {{ differenceText }}
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Reason *
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
                v-for="reason in adjustmentReasons"
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
              placeholder="Add any additional notes about this adjustment..."
            />
          </div>

          <div class="bg-steel-900 border border-steel-700 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-white mb-2">
              Adjustment Preview
            </h4>
            <div class="flex items-center gap-4 text-sm font-mono">
              <span class="text-text-tertiary">
                Current: {{ stockItem.quantity }}
              </span>
              <svg
                class="w-4 h-4 text-weld-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M14 5l7 7m0 0l-7 7m7-7H3"
                />
              </svg>
              <span
                class="font-bold"
                :class="{
                  'text-white': form.quantity !== stockItem.quantity,
                  'text-text-tertiary': form.quantity === stockItem.quantity
                }"
              >
                New: {{ form.quantity }}
              </span>
            </div>
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
              :disabled="form.processing || !form.reason || form.quantity === stockItem.quantity"
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
              Apply Adjustment
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
