<!-- resources/js/Pages/Shipping/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    projects: Array,
});

const form = useForm({
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
  <AppLayout title="New Load">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Create New Load
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Start a new shipping load for a project.
        </p>
      </div>
      <Link
        :href="route('shipping.index')"
        class="btn-secondary"
      >
        Back to Loads
      </Link>
    </div>

    <form
      class="card p-6 space-y-6"
      @submit.prevent="submit"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Project -->
        <div class="md:col-span-2">
          <label class="input-label">Project</label>
          <select
            v-model="form.project_id"
            class="input"
            required
          >
            <option value="">
              Select a project...
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
            class="input-error"
          >
            {{ form.errors.project_id }}
          </div>
        </div>

        <!-- Destination -->
        <div>
          <label class="input-label">Destination</label>
          <input
            v-model="form.destination"
            type="text"
            class="input"
            placeholder="City, State or Address"
          >
          <div
            v-if="form.errors.destination"
            class="input-error"
          >
            {{ form.errors.destination }}
          </div>
        </div>

        <!-- Ship Date -->
        <div>
          <label class="input-label">Ship Date</label>
          <input
            v-model="form.ship_date"
            type="date"
            class="input"
          >
          <div
            v-if="form.errors.ship_date"
            class="input-error"
          >
            {{ form.errors.ship_date }}
          </div>
        </div>

        <!-- Carrier -->
        <div>
          <label class="input-label">Carrier</label>
          <input
            v-model="form.carrier"
            type="text"
            class="input"
            placeholder="Carrier name"
          >
          <div
            v-if="form.errors.carrier"
            class="input-error"
          >
            {{ form.errors.carrier }}
          </div>
        </div>

        <!-- Truck Number -->
        <div>
          <label class="input-label">Truck Number</label>
          <input
            v-model="form.truck_number"
            type="text"
            class="input"
            placeholder="Truck #"
          >
          <div
            v-if="form.errors.truck_number"
            class="input-error"
          >
            {{ form.errors.truck_number }}
          </div>
        </div>

        <!-- Trailer Number -->
        <div>
          <label class="input-label">Trailer Number</label>
          <input
            v-model="form.trailer_number"
            type="text"
            class="input"
            placeholder="Trailer #"
          >
          <div
            v-if="form.errors.trailer_number"
            class="input-error"
          >
            {{ form.errors.trailer_number }}
          </div>
        </div>

        <!-- Driver Name -->
        <div>
          <label class="input-label">Driver Name</label>
          <input
            v-model="form.driver_name"
            type="text"
            class="input"
            placeholder="Driver name"
          >
          <div
            v-if="form.errors.driver_name"
            class="input-error"
          >
            {{ form.errors.driver_name }}
          </div>
        </div>

        <!-- Notes -->
        <div class="md:col-span-2">
          <label class="input-label">Notes</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="input"
            placeholder="Optional notes about this load..."
          />
          <div
            v-if="form.errors.notes"
            class="input-error"
          >
            {{ form.errors.notes }}
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex items-center justify-end gap-4 border-t border-steel-700 pt-4">
        <Link
          :href="route('shipping.index')"
          class="btn-secondary"
        >
          Cancel
        </Link>
        <button
          type="submit"
          class="btn-primary"
          :disabled="form.processing"
        >
          Create Load
        </button>
      </div>
    </form>
  </AppLayout>
</template>
