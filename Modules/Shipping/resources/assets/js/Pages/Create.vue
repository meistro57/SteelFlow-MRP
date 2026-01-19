<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    projects: Array,
    statuses: Array,
});

const form = useForm({
    load_number: '',
    project_id: '',
    destination: '',
    ship_date: '',
    carrier: '',
    truck_number: '',
    trailer_number: '',
    driver_name: '',
    notes: '',
});

const submit = () => {
    form.post(route('shipping.store'));
};
</script>

<template>
  <AppLayout title="Create Load">
    <Head title="Create Load - SteelFlow MRP" />

    <template #header>
      <div class="flex items-center">
        <Link
          href="/shipping"
          class="text-text-secondary hover:text-white transition-colors mr-4"
        >
          &larr; Back
        </Link>
        <h1 class="text-3xl font-bold text-white">
          Create New Load
        </h1>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <form
        class="card-industrial space-y-6"
        @submit.prevent="submit"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Load Number -->
          <div>
            <label
              for="load_number"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Load Number</label>
            <input
              id="load_number"
              v-model="form.load_number"
              type="text"
              class="input w-full"
              placeholder="e.g. L-2026-001"
              required
            >
            <div
              v-if="form.errors.load_number"
              class="text-red-500 text-xs mt-1"
            >
              {{ form.errors.load_number }}
            </div>
          </div>

          <!-- Project Selection -->
          <div>
            <label
              for="project_id"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Project</label>
            <select
              id="project_id"
              v-model="form.project_id"
              class="select w-full"
              required
            >
              <option
                value=""
                disabled
              >
                Select Project
              </option>
              <option
                v-for="project in projects"
                :key="project.id"
                :value="project.id"
              >
                {{ project.job_number }} - {{ project.name }}
              </option>
            </select>
            <div
              v-if="form.errors.project_id"
              class="text-red-500 text-xs mt-1"
            >
              {{ form.errors.project_id }}
            </div>
          </div>

          <!-- Destination -->
          <div>
            <label
              for="destination"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Destination</label>
            <input
              id="destination"
              v-model="form.destination"
              type="text"
              class="input w-full"
              placeholder="Job site address or name"
            >
            <div
              v-if="form.errors.destination"
              class="text-red-500 text-xs mt-1"
            >
              {{ form.errors.destination }}
            </div>
          </div>

          <!-- Ship Date -->
          <div>
            <label
              for="ship_date"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Scheduled Ship Date</label>
            <input
              id="ship_date"
              v-model="form.ship_date"
              type="date"
              class="input w-full"
            >
            <div
              v-if="form.errors.ship_date"
              class="text-red-500 text-xs mt-1"
            >
              {{ form.errors.ship_date }}
            </div>
          </div>

          <!-- Carrier -->
          <div>
            <label
              for="carrier"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Carrier</label>
            <input
              id="carrier"
              v-model="form.carrier"
              type="text"
              class="input w-full"
              placeholder="Shipping company name"
            >
            <div
              v-if="form.errors.carrier"
              class="text-red-500 text-xs mt-1"
            >
              {{ form.errors.carrier }}
            </div>
          </div>

          <!-- Driver Name -->
          <div>
            <label
              for="driver_name"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Driver Name</label>
            <input
              id="driver_name"
              v-model="form.driver_name"
              type="text"
              class="input w-full"
            >
          </div>

          <!-- Truck & Trailer -->
          <div>
            <label
              for="truck_number"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Truck Number</label>
            <input
              id="truck_number"
              v-model="form.truck_number"
              type="text"
              class="input w-full"
            >
          </div>
          <div>
            <label
              for="trailer_number"
              class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
            >Trailer Number</label>
            <input
              id="trailer_number"
              v-model="form.trailer_number"
              type="text"
              class="input w-full"
            >
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label
            for="notes"
            class="block text-sm font-medium text-text-secondary mb-2 uppercase tracking-wider"
          >Shipping Notes</label>
          <textarea
            id="notes"
            v-model="form.notes"
            class="textarea w-full"
            rows="4"
          />
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-steel-700">
          <Link
            href="/shipping"
            class="btn-secondary"
          >
            Cancel
          </Link>
          <button
            type="submit"
            class="btn-primary"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Creating...' : 'Create Load' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
