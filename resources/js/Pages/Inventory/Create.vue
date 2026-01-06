<!-- resources/js/Pages/Inventory/Create.vue -->
<script setup>
import { computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManualInventoryRecorder from '@/Components/ManualInventoryRecorder.vue';

const props = defineProps({
    materials: Array,
    projects: Array,
    statuses: Object,
    stockAreas: Object,
    types: Array,
    sizesByType: Object,
    grades: Array,
});

const form = useForm({
    material_id: '',
    type: '',
    size: '',
    grade: '',
    length_ft: '',
    length_in: '',
    quantity: 1,
    status: 'free',
    reserved_project_id: '',
    stock_area: '',
    heat_number: '',
    po_number: '',
    country_of_origin: '',
    cost_per_unit: '',
    receive_date: new Date().toISOString().split('T')[0],
    notes: '',
});

// Computed property for available sizes based on selected type
const availableSizes = computed(() => {
    if (!form.type || !props.sizesByType[form.type]) {
        return [];
    }
    return props.sizesByType[form.type];
});

// Reset size when type changes
watch(() => form.type, () => {
    form.size = '';
});

const onMaterialChange = () => {
    const material = props.materials.find(m => m.id === parseInt(form.material_id));
    if (material) {
        form.type = material.type;
        form.size = material.size_imperial || material.size_metric;
        form.grade = material.grade?.code || '';
    }
};

const applyManualEntry = (entry) => {
    // Allow the speech-to-text manual entry assistant to prefill the form
    if (entry.shape) {
        form.type = entry.shape;
    }

    if (entry.size) {
        form.size = entry.size;
    }

    if (entry.grade) {
        form.grade = entry.grade;
    }

    if (entry.area) {
        form.stock_area = entry.area;
    }

    if (entry.length) {
        const numericLength = parseFloat(entry.length);

        if (!Number.isNaN(numericLength)) {
            form.length_ft = numericLength;
            form.length_in = '';
        } else {
            form.notes = [
                form.notes,
                `Voice length capture: ${entry.length}`,
            ].filter(Boolean).join('\n');
        }
    }
};

const submit = () => {
    // Convert feet and inches to total feet before submitting
    const totalFeet = (parseFloat(form.length_ft) || 0) + ((parseFloat(form.length_in) || 0) / 12);

    form.transform((data) => ({
        ...data,
        length: totalFeet,
    })).post('/inventory');
};
</script>

<template>
  <AppLayout title="Add Stock Item">
    <template #header>
      <div class="flex items-center">
        <Link
          href="/inventory"
          class="text-text-secondary hover:text-white transition-colors mr-4"
        >
          &larr; Back
        </Link>
        <div>
          <h1 class="text-3xl font-bold text-white">
            Add Stock Item
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Receive new material into inventory
          </p>
        </div>
      </div>
    </template>

    <div class="card-industrial">
      <ManualInventoryRecorder @apply="applyManualEntry" />
    </div>

    <div class="card-industrial mt-6">
      <form
        class="space-y-8"
        @submit.prevent="submit"
      >
        <!-- Material Selection -->
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
            <!-- Material Selector -->
            <div>
              <label
                for="material_id"
                class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold"
              >
                Material Catalog (Optional)
              </label>
              <select
                id="material_id"
                v-model="form.material_id"
                class="input-industrial w-full"
                @change="onMaterialChange"
              >
                <option value="">
                  Manual Entry
                </option>
                <option
                  v-for="material in materials"
                  :key="material.id"
                  :value="material.id"
                >
                  {{ material.type }} {{ material.size_imperial || material.size_metric }}
                </option>
              </select>
              <p class="mt-1 text-xs text-text-tertiary">
                Select from catalog or enter manually below
              </p>
            </div>

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
                min="1"
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
        <div class="flex justify-end gap-3 pt-6 border-t border-steel-700">
          <Link
            href="/inventory"
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
            {{ form.processing ? 'Adding Stock...' : 'Add Stock Item' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
