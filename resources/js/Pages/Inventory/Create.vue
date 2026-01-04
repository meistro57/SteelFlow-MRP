<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    materials: Array,
    projects: Array,
    statuses: Object,
    stockAreas: Object,
});

const form = useForm({
    material_id: '',
    type: '',
    size: '',
    grade: '',
    length: '',
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

const onMaterialChange = () => {
    const material = props.materials.find(m => m.id === parseInt(form.material_id));
    if (material) {
        form.type = material.type;
        form.size = material.size;
        form.grade = material.grade;
    }
};

const submit = () => {
    form.post('/inventory');
};
</script>

<template>
  <AppLayout title="Add Stock Item">
    <template #header>
      <div class="flex items-center">
        <Link
          href="/inventory"
          class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
        >
          &larr; Back
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Add Stock Item
        </h2>
      </div>
    </template>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
      <form
        class="p-6 space-y-6"
        @submit.prevent="submit"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Material Selector -->
          <div>
            <label
              for="material_id"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Material (optional)
            </label>
            <select
              id="material_id"
              v-model="form.material_id"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                {{ material.description }}
              </option>
            </select>
          </div>

          <!-- Type -->
          <div>
            <label
              for="type"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Type *
            </label>
            <input
              id="type"
              v-model="form.type"
              type="text"
              required
              placeholder="e.g., W, C, L, HSS"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
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
            <input
              id="size"
              v-model="form.size"
              type="text"
              required
              placeholder="e.g., W14x30, C10x15.3"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
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
            <input
              id="grade"
              v-model="form.grade"
              type="text"
              required
              placeholder="e.g., A992, A36"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <p
              v-if="form.errors.grade"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.grade }}
            </p>
          </div>

          <!-- Length -->
          <div>
            <label
              for="length"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Length (ft) *
            </label>
            <input
              id="length"
              v-model="form.length"
              type="number"
              step="0.01"
              min="0"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
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
              min="1"
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
            href="/inventory"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mr-3"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150"
          >
            Add Stock Item
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
