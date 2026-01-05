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
    if (confirm('Are you sure you want to delete this stock item?')) {
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
            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
          >
            &larr; Back
          </Link>
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit: {{ stockItem.stock_id }}
          </h2>
        </div>
        <button
          type="button"
          class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
          @click="deleteItem"
        >
          Delete
        </button>
      </div>
    </template>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
      <form
        class="p-6 space-y-6"
        @submit.prevent="submit"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Type -->
          <div>
            <label
              for="type"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Type *
            </label>
            <select
              id="type"
              v-model="form.type"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.type }}
            </p>
          </div>

          <!-- Size -->
          <div>
            <label
              for="size"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Size *
            </label>
            <select
              id="size"
              v-model="form.size"
              required
              :disabled="!form.type"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50"
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
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.size }}
            </p>
          </div>

          <!-- Grade -->
          <div>
            <label
              for="grade"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Grade *
            </label>
            <select
              id="grade"
              v-model="form.grade"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.grade }}
            </p>
          </div>

          <!-- Length (Feet and Inches) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Length *
            </label>
            <div class="mt-1 flex space-x-2">
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
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-8"
                  >
                  <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400 text-sm">
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
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-8"
                  >
                  <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400 text-sm">
                    in
                  </span>
                </div>
              </div>
            </div>
            <p
              v-if="form.errors.length"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.length }}
            </p>
          </div>

          <!-- Quantity -->
          <div>
            <label
              for="quantity"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Quantity *
            </label>
            <input
              id="quantity"
              v-model="form.quantity"
              type="number"
              min="0"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <p
              v-if="form.errors.quantity"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.quantity }}
            </p>
          </div>

          <!-- Status -->
          <div>
            <label
              for="status"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Status *
            </label>
            <select
              id="status"
              v-model="form.status"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Stock Area
            </label>
            <select
              id="stock_area"
              v-model="form.stock_area"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Reserved for Project
            </label>
            <select
              id="reserved_project_id"
              v-model="form.reserved_project_id"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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

          <!-- Heat Number -->
          <div>
            <label
              for="heat_number"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Heat Number
            </label>
            <input
              id="heat_number"
              v-model="form.heat_number"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
          </div>

          <!-- PO Number -->
          <div>
            <label
              for="po_number"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              PO Number
            </label>
            <input
              id="po_number"
              v-model="form.po_number"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
          </div>

          <!-- Receive Date -->
          <div>
            <label
              for="receive_date"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Receive Date
            </label>
            <input
              id="receive_date"
              v-model="form.receive_date"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
          </div>

          <!-- Country of Origin -->
          <div>
            <label
              for="country_of_origin"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Country of Origin
            </label>
            <input
              id="country_of_origin"
              v-model="form.country_of_origin"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label
            for="notes"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
          >
            Notes
          </label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
          <Link
            :href="`/inventory/${stockItem.id}`"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mr-3"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
