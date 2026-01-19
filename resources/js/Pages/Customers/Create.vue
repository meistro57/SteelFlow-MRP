<!-- resources/js/Pages/Customers/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    code: '',
    name: '',
    address_1: '',
    address_2: '',
    city: '',
    state: '',
    zip: '',
    country: 'USA',
    phone: '',
    email: '',
    notes: '',
    is_active: true,
});

const submit = () => {
    form.post('/customers');
};
</script>

<template>
  <AppLayout title="New Customer">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          New Customer
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Capture customer details for quick reuse across projects.
        </p>
      </div>
      <Link
        href="/customers"
        class="btn-secondary"
      >
        Back to Customers
      </Link>
    </div>

    <form
      class="card p-6 space-y-6"
      @submit.prevent="submit"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="input-label">Customer Code</label>
          <input
            v-model="form.code"
            type="text"
            class="input"
            placeholder="Optional unique code"
          >
          <div
            v-if="form.errors.code"
            class="input-error"
          >
            {{ form.errors.code }}
          </div>
        </div>
        <div>
          <label class="input-label">Customer Name</label>
          <input
            v-model="form.name"
            type="text"
            class="input"
            required
            placeholder="ACME Fabrication"
          >
          <div
            v-if="form.errors.name"
            class="input-error"
          >
            {{ form.errors.name }}
          </div>
        </div>
        <div>
          <label class="input-label">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="input"
            placeholder="orders@example.com"
          >
          <div
            v-if="form.errors.email"
            class="input-error"
          >
            {{ form.errors.email }}
          </div>
        </div>
        <div>
          <label class="input-label">Phone</label>
          <input
            v-model="form.phone"
            type="text"
            class="input"
            placeholder="555-555-0100"
          >
          <div
            v-if="form.errors.phone"
            class="input-error"
          >
            {{ form.errors.phone }}
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="input-label">Address Line 1</label>
          <input
            v-model="form.address_1"
            type="text"
            class="input"
            placeholder="123 Foundry Way"
          >
          <div
            v-if="form.errors.address_1"
            class="input-error"
          >
            {{ form.errors.address_1 }}
          </div>
        </div>
        <div>
          <label class="input-label">Address Line 2</label>
          <input
            v-model="form.address_2"
            type="text"
            class="input"
            placeholder="Suite 200"
          >
          <div
            v-if="form.errors.address_2"
            class="input-error"
          >
            {{ form.errors.address_2 }}
          </div>
        </div>
        <div>
          <label class="input-label">City</label>
          <input
            v-model="form.city"
            type="text"
            class="input"
            placeholder="Pittsburgh"
          >
          <div
            v-if="form.errors.city"
            class="input-error"
          >
            {{ form.errors.city }}
          </div>
        </div>
        <div>
          <label class="input-label">State/Province</label>
          <input
            v-model="form.state"
            type="text"
            class="input"
            placeholder="PA"
          >
          <div
            v-if="form.errors.state"
            class="input-error"
          >
            {{ form.errors.state }}
          </div>
        </div>
        <div>
          <label class="input-label">Postal Code</label>
          <input
            v-model="form.zip"
            type="text"
            class="input"
            placeholder="15222"
          >
          <div
            v-if="form.errors.zip"
            class="input-error"
          >
            {{ form.errors.zip }}
          </div>
        </div>
        <div>
          <label class="input-label">Country</label>
          <input
            v-model="form.country"
            type="text"
            class="input"
            placeholder="USA"
          >
          <div
            v-if="form.errors.country"
            class="input-error"
          >
            {{ form.errors.country }}
          </div>
        </div>
      </div>

      <div>
        <label class="input-label">Notes</label>
        <textarea
          v-model="form.notes"
          rows="4"
          class="input"
          placeholder="Special instructions, billing contact, or account notes"
        />
        <div
          v-if="form.errors.notes"
          class="input-error"
        >
          {{ form.errors.notes }}
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <input
          id="is_active"
          v-model="form.is_active"
          type="checkbox"
          class="form-checkbox h-5 w-5 text-forge-400"
        >
        <label
          for="is_active"
          class="text-steel-200"
        >Customer is active</label>
        <div
          v-if="form.errors.is_active"
          class="input-error"
        >
          {{ form.errors.is_active }}
        </div>
      </div>

      <div class="flex justify-end space-x-3">
        <Link
          href="/customers"
          class="btn-secondary"
        >
          Cancel
        </Link>
        <button
          type="submit"
          class="btn-primary"
          :disabled="form.processing"
        >
          Save Customer
        </button>
      </div>
    </form>
  </AppLayout>
</template>
