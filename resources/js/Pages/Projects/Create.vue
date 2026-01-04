<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customers: Array,
    statuses: Object,
    jobTypes: Object,
});

const form = useForm({
    job_number: '',
    name: '',
    customer_id: '',
    status: 'pending',
    job_type: '',
    po_number: '',
    contract_weight_lbs: '',
    ship_date: '',
    notes: '',
});

const submit = () => {
    form.post('/projects');
};
</script>

<template>
  <AppLayout title="Create Project">
    <template #header>
      <div class="flex items-center">
        <Link
          href="/projects"
          class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
        >
          &larr; Back
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Create Project
        </h2>
      </div>
    </template>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
      <form
        class="p-6 space-y-6"
        @submit.prevent="submit"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Job Number -->
          <div>
            <label
              for="job_number"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Job Number *
            </label>
            <input
              id="job_number"
              v-model="form.job_number"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <p
              v-if="form.errors.job_number"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.job_number }}
            </p>
          </div>

          <!-- Name -->
          <div>
            <label
              for="name"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Project Name *
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <p
              v-if="form.errors.name"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Customer -->
          <div>
            <label
              for="customer_id"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Customer
            </label>
            <select
              id="customer_id"
              v-model="form.customer_id"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">
                Select Customer
              </option>
              <option
                v-for="customer in customers"
                :key="customer.id"
                :value="customer.id"
              >
                {{ customer.name }}
              </option>
            </select>
            <p
              v-if="form.errors.customer_id"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.customer_id }}
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
            <p
              v-if="form.errors.status"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.status }}
            </p>
          </div>

          <!-- Job Type -->
          <div>
            <label
              for="job_type"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Job Type
            </label>
            <select
              id="job_type"
              v-model="form.job_type"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">
                Select Type
              </option>
              <option
                v-for="(label, value) in jobTypes"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>
            </select>
            <p
              v-if="form.errors.job_type"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.job_type }}
            </p>
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
            <p
              v-if="form.errors.po_number"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.po_number }}
            </p>
          </div>

          <!-- Contract Weight -->
          <div>
            <label
              for="contract_weight_lbs"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Contract Weight (lbs)
            </label>
            <input
              id="contract_weight_lbs"
              v-model="form.contract_weight_lbs"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <p
              v-if="form.errors.contract_weight_lbs"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.contract_weight_lbs }}
            </p>
          </div>

          <!-- Ship Date -->
          <div>
            <label
              for="ship_date"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Ship Date
            </label>
            <input
              id="ship_date"
              v-model="form.ship_date"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <p
              v-if="form.errors.ship_date"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.ship_date }}
            </p>
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
          <p
            v-if="form.errors.notes"
            class="mt-1 text-sm text-red-600"
          >
            {{ form.errors.notes }}
          </p>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
          <Link
            href="/projects"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mr-3"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150"
          >
            Create Project
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
