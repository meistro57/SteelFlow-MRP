<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    stockItem: Object,
});

const form = useForm({
    remnant_length: '',
    notes: '',
});

const submit = () => {
    form.post(`/inventory/${props.stockItem.id}/remnant`);
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};

const remainingLength = computed(() => {
    if (!form.remnant_length || isNaN(parseFloat(form.remnant_length))) {
        return props.stockItem.length;
    }
    return Math.max(0, props.stockItem.length - parseFloat(form.remnant_length));
});

const isValidRemnant = computed(() => {
    const remnantLen = parseFloat(form.remnant_length);
    return remnantLen > 0 && remnantLen < props.stockItem.length;
});
</script>

<template>
  <AppLayout :title="`Create Remnant: ${stockItem.stock_id}`">
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
            Create Remnant
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Split {{ stockItem.stock_id }} to create a remnant piece
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-2xl">
      <!-- Stock Item Summary -->
      <div class="card-industrial mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">
          Parent Stock Item
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
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Grade</span>
            <div class="mt-1 font-mono text-white">
              {{ stockItem.grade }}
            </div>
          </div>
          <div>
            <span class="text-text-tertiary text-xs uppercase tracking-wider">Current Length</span>
            <div class="mt-1 font-mono text-weld-400 font-bold text-xl">
              {{ formatLength(stockItem.length) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Remnant Form -->
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-safety-400"
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
          Remnant Details
        </h3>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Remnant Length (feet) *
            </label>
            <input
              v-model="form.remnant_length"
              type="number"
              step="0.01"
              min="0.01"
              :max="stockItem.length - 0.01"
              class="input-industrial w-full text-xl font-mono font-bold"
              :class="{ 'border-red-500': form.errors.remnant_length }"
              placeholder="Enter remnant length..."
            >
            <p
              v-if="form.errors.remnant_length"
              class="text-red-400 text-sm mt-1"
            >
              {{ form.errors.remnant_length }}
            </p>
            <p class="text-text-tertiary text-xs mt-1">
              Enter the length of the piece being cut off (the remnant).
            </p>
          </div>

          <!-- Visual Preview -->
          <div class="bg-steel-900 border border-steel-700 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-white mb-4">
              Split Preview
            </h4>

            <div class="space-y-4">
              <!-- Visual bar representation -->
              <div class="relative h-8 bg-steel-700 rounded overflow-hidden">
                <div
                  class="absolute left-0 top-0 h-full bg-weld-600 transition-all duration-300"
                  :style="{ width: `${(remainingLength / stockItem.length) * 100}%` }"
                />
                <div
                  v-if="isValidRemnant"
                  class="absolute right-0 top-0 h-full bg-safety-600 transition-all duration-300"
                  :style="{ width: `${(parseFloat(form.remnant_length) / stockItem.length) * 100}%` }"
                />
              </div>

              <!-- Labels -->
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-weld-900/50 border border-weld-700 rounded p-3">
                  <div class="text-text-tertiary text-xs uppercase tracking-wider mb-1">
                    Parent Remaining
                  </div>
                  <div class="font-mono text-weld-400 font-bold text-lg">
                    {{ formatLength(remainingLength) }}
                  </div>
                </div>
                <div class="bg-safety-900/50 border border-safety-700 rounded p-3">
                  <div class="text-text-tertiary text-xs uppercase tracking-wider mb-1">
                    New Remnant
                  </div>
                  <div class="font-mono text-safety-400 font-bold text-lg">
                    {{ form.remnant_length ? formatLength(parseFloat(form.remnant_length)) : '-' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Notes (Optional)
            </label>
            <textarea
              v-model="form.notes"
              class="input-industrial w-full"
              rows="3"
              placeholder="Add any notes about this remnant..."
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
              :disabled="form.processing || !isValidRemnant"
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
              Create Remnant
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
