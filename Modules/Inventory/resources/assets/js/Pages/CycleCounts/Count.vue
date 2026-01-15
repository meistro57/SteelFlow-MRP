<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    cycleCount: Object,
});

const currentLineIndex = ref(0);
const showAllItems = ref(false);

const pendingLines = computed(() => {
    return props.cycleCount.lines.filter(line => !line.is_counted);
});

const countedLines = computed(() => {
    return props.cycleCount.lines.filter(line => line.is_counted);
});

const currentLine = computed(() => {
    if (showAllItems.value) {
        return props.cycleCount.lines[currentLineIndex.value];
    }
    return pendingLines.value[currentLineIndex.value];
});

const form = useForm({
    line_id: null,
    counted_quantity: null,
    notes: '',
});

const selectLine = (line, index) => {
    currentLineIndex.value = index;
    form.line_id = line.id;
    form.counted_quantity = line.counted_quantity ?? line.expected_quantity;
    form.notes = line.notes || '';
};

const submitCount = () => {
    if (!currentLine.value) return;

    form.line_id = currentLine.value.id;
    form.post(`/cycle-counts/${props.cycleCount.id}/count`, {
        preserveScroll: true,
        onSuccess: () => {
            // Move to next item
            if (!showAllItems.value && pendingLines.value.length > 1) {
                if (currentLineIndex.value >= pendingLines.value.length - 1) {
                    currentLineIndex.value = 0;
                }
            } else {
                currentLineIndex.value++;
                if (currentLineIndex.value >= (showAllItems.value ? props.cycleCount.lines.length : pendingLines.value.length)) {
                    currentLineIndex.value = 0;
                }
            }
            form.reset();
        },
    });
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};

const progress = computed(() => {
    const total = props.cycleCount.lines.length;
    const counted = countedLines.value.length;
    return total > 0 ? Math.round((counted / total) * 100) : 0;
});

// Initialize with first pending line
if (pendingLines.value.length > 0) {
    selectLine(pendingLines.value[0], 0);
}
</script>

<template>
  <AppLayout :title="`Counting: ${cycleCount.count_number}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            :href="`/cycle-counts/${cycleCount.id}`"
            class="text-text-secondary hover:text-white transition-colors mr-4"
          >
            &larr; Back to Count Details
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-white">
              Physical Count
            </h1>
            <p class="text-text-secondary mt-1 font-mono text-sm">
              {{ cycleCount.count_number }}
            </p>
          </div>
        </div>

        <!-- Progress -->
        <div class="text-right">
          <div class="text-text-tertiary text-sm">
            Progress: {{ countedLines.length }} / {{ cycleCount.lines.length }}
          </div>
          <div class="w-48 h-2 bg-steel-700 rounded-full mt-2 overflow-hidden">
            <div
              class="h-full bg-weld-500 transition-all duration-300"
              :style="{ width: `${progress}%` }"
            />
          </div>
        </div>
      </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Item List -->
      <div class="lg:col-span-1">
        <div class="card-industrial h-[calc(100vh-240px)] overflow-hidden flex flex-col">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">
              Items
            </h3>
            <label class="flex items-center text-sm text-text-secondary cursor-pointer">
              <input
                v-model="showAllItems"
                type="checkbox"
                class="form-checkbox rounded bg-steel-700 border-steel-600 text-weld-500 mr-2"
              >
              Show All
            </label>
          </div>

          <div class="flex-1 overflow-y-auto scrollbar-industrial space-y-2">
            <button
              v-for="(line, index) in (showAllItems ? cycleCount.lines : pendingLines)"
              :key="line.id"
              class="w-full text-left p-3 rounded-lg border transition-colors"
              :class="{
                'border-weld-500 bg-weld-900/30': currentLine?.id === line.id,
                'border-steel-700 bg-steel-900 hover:border-steel-600': currentLine?.id !== line.id,
              }"
              @click="selectLine(line, index)"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-mono text-weld-400 font-semibold text-sm">
                    {{ line.stock_item.stock_id }}
                  </div>
                  <div class="text-text-tertiary text-xs mt-1">
                    {{ line.stock_item.type }} {{ line.stock_item.size }}
                  </div>
                </div>
                <div class="text-right">
                  <div
                    v-if="line.is_counted"
                    class="text-green-400 text-xs font-semibold"
                  >
                    Counted
                  </div>
                  <div class="font-mono text-sm">
                    {{ line.expected_quantity }}
                  </div>
                </div>
              </div>
            </button>

            <div
              v-if="(showAllItems ? cycleCount.lines : pendingLines).length === 0"
              class="text-center py-8 text-text-tertiary"
            >
              <svg
                class="w-12 h-12 mx-auto mb-3 text-green-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              <p>All items counted!</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Count Form -->
      <div class="lg:col-span-2">
        <div
          v-if="currentLine"
          class="card-industrial"
        >
          <h3 class="text-lg font-semibold text-white mb-6">
            Count Item
          </h3>

          <!-- Item Details -->
          <div class="bg-steel-900 border border-steel-700 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <div class="text-text-tertiary text-xs uppercase tracking-wider">
                  Stock ID
                </div>
                <div class="font-mono text-weld-400 font-bold text-lg mt-1">
                  {{ currentLine.stock_item.stock_id }}
                </div>
              </div>
              <div>
                <div class="text-text-tertiary text-xs uppercase tracking-wider">
                  Material
                </div>
                <div class="font-mono text-white mt-1">
                  {{ currentLine.stock_item.type }} {{ currentLine.stock_item.size }}
                </div>
              </div>
              <div>
                <div class="text-text-tertiary text-xs uppercase tracking-wider">
                  Grade
                </div>
                <div class="font-mono text-white mt-1">
                  {{ currentLine.stock_item.grade }}
                </div>
              </div>
              <div>
                <div class="text-text-tertiary text-xs uppercase tracking-wider">
                  Length
                </div>
                <div class="font-mono text-white mt-1">
                  {{ formatLength(currentLine.stock_item.length) }}
                </div>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-steel-700">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-text-tertiary text-xs uppercase tracking-wider">
                    Location
                  </div>
                  <div class="font-mono text-white mt-1 capitalize">
                    {{ currentLine.stock_item.stock_area?.replace('_', ' ') || '-' }}
                  </div>
                </div>
                <div>
                  <div class="text-text-tertiary text-xs uppercase tracking-wider">
                    Heat #
                  </div>
                  <div class="font-mono text-white mt-1">
                    {{ currentLine.stock_item.heat_number || '-' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Count Input -->
          <form @submit.prevent="submitCount">
            <div class="grid grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                  Expected Quantity
                </label>
                <div class="input-industrial w-full text-2xl font-mono font-bold bg-steel-900 text-text-tertiary">
                  {{ currentLine.expected_quantity }}
                </div>
              </div>
              <div>
                <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                  Counted Quantity *
                </label>
                <input
                  v-model.number="form.counted_quantity"
                  type="number"
                  min="0"
                  class="input-industrial w-full text-2xl font-mono font-bold"
                  :class="{
                    'border-green-500': form.counted_quantity === currentLine.expected_quantity,
                    'border-red-500': form.counted_quantity !== null && form.counted_quantity !== currentLine.expected_quantity,
                  }"
                  autofocus
                >
              </div>
            </div>

            <div class="mb-6">
              <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                Notes
              </label>
              <textarea
                v-model="form.notes"
                class="input-industrial w-full"
                rows="2"
                placeholder="Add any notes..."
              />
            </div>

            <div class="flex justify-end gap-3">
              <button
                type="submit"
                class="btn-primary px-8"
                :disabled="form.processing || form.counted_quantity === null"
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
                Record Count
              </button>
            </div>
          </form>
        </div>

        <div
          v-else
          class="card-industrial text-center py-12"
        >
          <svg
            class="w-16 h-16 text-green-500 mx-auto mb-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h3 class="text-xl font-semibold text-white mb-2">
            All Items Counted
          </h3>
          <p class="text-text-secondary mb-6">
            Great job! All items in this cycle count have been counted.
          </p>
          <Link
            :href="`/cycle-counts/${cycleCount.id}`"
            class="btn-primary"
          >
            View Results
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
