<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stockItem: Object,
    materials: Array,
    projects: Array,
    statuses: Object,
    stockAreas: Object,
    types: Array,
    sizesByType: Object,
    grades: Array,
});

// Convert total feet to feet and inches
const totalLength = parseFloat(props.stockItem.length) || 0;
const initialFeet = Math.floor(totalLength);
const initialInches = Math.round((totalLength - initialFeet) * 12);

const form = useForm({
    material_id: props.stockItem.material_id || '',
    type: props.stockItem.type,
    size: props.stockItem.size,
    grade: props.stockItem.grade,
    length_ft: initialFeet,
    length_in: initialInches,
    quantity: props.stockItem.quantity,
    status: props.stockItem.status,
    reserved_project_id: props.stockItem.reserved_project_id || '',
    stock_area: props.stockItem.stock_area || '',
    heat_number: props.stockItem.heat_number || '',
    po_number: props.stockItem.po_number || '',
    country_of_origin: props.stockItem.country_of_origin || '',
    cost_per_unit: props.stockItem.cost_per_unit || '',
    receive_date: props.stockItem.receive_date || '',
    notes: props.stockItem.notes || '',
});

// Computed property for available sizes based on selected type
const availableSizes = computed(() => {
    if (!form.type || !props.sizesByType[form.type]) {
        return [];
    }
    return props.sizesByType[form.type];
});

// Track if we should reset size on type change (only for user-initiated changes)
const isInitialLoad = ref(true);

watch(() => form.type, (newType, oldType) => {
    // Only reset size if it's not the initial load and the type actually changed
    if (!isInitialLoad.value && oldType !== newType) {
        form.size = '';
    }
    isInitialLoad.value = false;
});

const submit = () => {
    // Convert feet and inches to total feet before submitting
    const totalFeet = (parseFloat(form.length_ft) || 0) + ((parseFloat(form.length_in) || 0) / 12);

    form.transform((data) => ({
        ...data,
        length: totalFeet,
    })).put(`/inventory/${props.stockItem.id}`);
};

const deleteItem = () => {
    if (confirm('Are you sure you want to delete this stock item? This action cannot be undone.')) {
        router.delete(`/inventory/${props.stockItem.id}`);
    }
};
</script>

<template>
  <AppLayout title="Edit Stock Item">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            :href="`/inventory/${stockItem.id}`"
            class="text-text-secondary hover:text-white transition-colors mr-4"
          >
            &larr; Back
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-white">
              Edit Stock Item
            </h1>
            <p class="text-text-secondary mt-1 font-mono text-sm">
              {{ stockItem.stock_id }} - {{ stockItem.type }} {{ stockItem.size }}
            </p>
          </div>
        </div>
        <button
          type="button"
          class="btn-ghost text-safety-400 border-safety-700 hover:bg-safety-900 hover:text-safety-300"
          @click="deleteItem"
        >
          <svg
            class="w-5 h-5 inline-block mr-2"
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
          Delete
        </button>
      </div>
    </template>

    <div class="card-industrial">
      <form
        class="space-y-8"
        @submit.prevent="submit"
      >
        <!-- Material Specifications -->
        <div>
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
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
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
            Material Specifications
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Type -->
            <div>
              <label
                for="type"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Type <span class="text-safety-400">*</span>
              </label>
              <select
                id="type"
                v-model="form.type"
                required
                class="input-industrial w-full"
              >
                <option value="">
                  Select Type
                </option>
                <option
                  v-for="type in types"
                  :key="type"
                  :value="type"
                >
                  {{ type }}
                </option>
              </select>
              <p
                v-if="form.errors.type"
                class="mt-1 text-sm text-safety-400"
              >
                {{ form.errors.type }}
              </p>
            </div>

            <!-- Size -->
            <div>
              <label
                for="size"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Size <span class="text-safety-400">*</span>
              </label>
              <select
                id="size"
                v-model="form.size"
                required
                :disabled="!form.type"
                class="input-industrial w-full disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <option value="">
                  {{ form.type ? 'Select Size' : 'Select Type First' }}
                </option>
                <option
                  v-for="size in availableSizes"
                  :key="size"
                  :value="size"
                >
                  {{ size }}
                </option>
              </select>
              <p
                v-if="form.errors.size"
                class="mt-1 text-sm text-safety-400"
              >
                {{ form.errors.size }}
              </p>
            </div>

            <!-- Grade -->
            <div>
              <label
                for="grade"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Grade <span class="text-safety-400">*</span>
              </label>
              <select
                id="grade"
                v-model="form.grade"
                required
                class="input-industrial w-full"
              >
                <option value="">
                  Select Grade
                </option>
                <option
                  v-for="grade in grades"
                  :key="grade"
                  :value="grade"
                >
                  {{ grade }}
                </option>
              </select>
              <p
                v-if="form.errors.grade"
                class="mt-1 text-sm text-safety-400"
              >
                {{ form.errors.grade }}
              </p>
            </div>

            <!-- Length (Feet and Inches) -->
            <div>
              <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                Length <span class="text-safety-400">*</span>
              </label>
              <div class="flex space-x-2">
                <div class="flex-1">
                  <div class="relative">
                    <input
                      id="length_ft"
                      v-model="form.length_ft"
                      type="number"
                      min="0"
                      step="1"
                      required
                      placeholder="0"
                      class="input-industrial w-full pr-12 font-mono"
                    >
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-tertiary text-sm font-mono font-bold">
                      ft
                    </span>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="relative">
                    <input
                      id="length_in"
                      v-model="form.length_in"
                      type="number"
                      min="0"
                      max="11"
                      step="1"
                      placeholder="0"
                      class="input-industrial w-full pr-12 font-mono"
                    >
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-tertiary text-sm font-mono font-bold">
                      in
                    </span>
                  </div>
                </div>
              </div>
              <p
                v-if="form.errors.length"
                class="mt-1 text-sm text-safety-400"
              >
                {{ form.errors.length }}
              </p>
            </div>

            <!-- Quantity -->
            <div>
              <label
                for="quantity"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Quantity <span class="text-safety-400">*</span>
              </label>
              <input
                id="quantity"
                v-model="form.quantity"
                type="number"
                min="0"
                required
                class="input-industrial w-full font-mono"
              >
              <p
                v-if="form.errors.quantity"
                class="mt-1 text-sm text-safety-400"
              >
                {{ form.errors.quantity }}
              </p>
            </div>

            <!-- Status -->
            <div>
              <label
                for="status"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Status <span class="text-safety-400">*</span>
              </label>
              <select
                id="status"
                v-model="form.status"
                required
                class="input-industrial w-full"
              >
                <option
                  v-for="(label, value) in statuses"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Stock Area -->
            <div>
              <label
                for="stock_area"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Stock Area
              </label>
              <select
                id="stock_area"
                v-model="form.stock_area"
                class="input-industrial w-full"
              >
                <option value="">
                  Select Area
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

            <!-- Reserved Project -->
            <div>
              <label
                for="reserved_project_id"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Reserved for Project
              </label>
              <select
                id="reserved_project_id"
                v-model="form.reserved_project_id"
                class="input-industrial w-full"
              >
                <option value="">
                  Not Reserved
                </option>
                <option
                  v-for="project in projects"
                  :key="project.id"
                  :value="project.id"
                >
                  {{ project.job_number }} - {{ project.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Receiving Details -->
        <div>
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <svg
              class="w-5 h-5 mr-2 text-forge-400"
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
            Receiving Details
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Heat Number -->
            <div>
              <label
                for="heat_number"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Heat Number
              </label>
              <input
                id="heat_number"
                v-model="form.heat_number"
                type="text"
                class="input-industrial w-full font-mono"
                placeholder="e.g., H12345-A"
              >
            </div>

            <!-- PO Number -->
            <div>
              <label
                for="po_number"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                PO Number
              </label>
              <input
                id="po_number"
                v-model="form.po_number"
                type="text"
                class="input-industrial w-full font-mono"
                placeholder="e.g., PO-2026-001"
              >
            </div>

            <!-- Receive Date -->
            <div>
              <label
                for="receive_date"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Receive Date
              </label>
              <input
                id="receive_date"
                v-model="form.receive_date"
                type="date"
                class="input-industrial w-full font-mono"
              >
            </div>

            <!-- Country of Origin -->
            <div>
              <label
                for="country_of_origin"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Country of Origin
              </label>
              <input
                id="country_of_origin"
                v-model="form.country_of_origin"
                type="text"
                class="input-industrial w-full"
                placeholder="e.g., USA, Canada"
              >
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <h3 class="text-lg font-semibold text-white mb-4">
            Additional Notes
          </h3>
          <label
            for="notes"
            class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
          >
            Notes
          </label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="4"
            class="input-industrial w-full font-mono"
            placeholder="Any additional information about this stock item..."
          />
        </div>

        <!-- Submit -->
        <div class="flex justify-between items-center gap-3 pt-6 border-t border-steel-700">
          <Link
            :href="`/inventory/${stockItem.id}`"
            class="btn-ghost"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-primary"
          >
            <svg
              v-if="!form.processing"
              class="w-5 h-5 inline-block mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
            <svg
              v-else
              class="w-5 h-5 inline-block mr-2 animate-spin"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
            {{ form.processing ? 'Saving Changes...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
