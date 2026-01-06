<!-- resources/js/Pages/Customers/Edit.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customer: Object,
});

const form = useForm({
    code: props.customer.code || '',
    name: props.customer.name || '',
    address_1: props.customer.address_1 || '',
    address_2: props.customer.address_2 || '',
    city: props.customer.city || '',
    state: props.customer.state || '',
    zip: props.customer.zip || '',
    country: props.customer.country || 'USA',
    phone: props.customer.phone || '',
    email: props.customer.email || '',
    notes: props.customer.notes || '',
    is_active: Boolean(props.customer.is_active),
});

const submit = () => {
    form.put(`/customers/${props.customer.id}`);
};
</script>

<template>
  <AppLayout :title="`Edit ${props.customer.name}`">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Edit Customer
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Keep customer details tidy for scheduling and reporting.
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
          <div class="input-error" v-if="form.errors.code">{{ form.errors.code }}</div>
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
          <div class="input-error" v-if="form.errors.name">{{ form.errors.name }}</div>
        </div>
        <div>
          <label class="input-label">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="input"
            placeholder="orders@example.com"
          >
          <div class="input-error" v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>
        <div>
          <label class="input-label">Phone</label>
          <input
            v-model="form.phone"
            type="text"
            class="input"
            placeholder="555-555-0100"
          >
          <div class="input-error" v-if="form.errors.phone">{{ form.errors.phone }}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="input-label">Address Line 1</label>
          <input v-model="form.address_1" type="text" class="input" placeholder="123 Foundry Way">
          <div class="input-error" v-if="form.errors.address_1">{{ form.errors.address_1 }}</div>
        </div>
        <div>
          <label class="input-label">Address Line 2</label>
          <input v-model="form.address_2" type="text" class="input" placeholder="Suite 200">
          <div class="input-error" v-if="form.errors.address_2">{{ form.errors.address_2 }}</div>
        </div>
        <div>
          <label class="input-label">City</label>
          <input v-model="form.city" type="text" class="input" placeholder="Pittsburgh">
          <div class="input-error" v-if="form.errors.city">{{ form.errors.city }}</div>
        </div>
        <div>
          <label class="input-label">State/Province</label>
          <input v-model="form.state" type="text" class="input" placeholder="PA">
          <div class="input-error" v-if="form.errors.state">{{ form.errors.state }}</div>
        </div>
        <div>
          <label class="input-label">Postal Code</label>
          <input v-model="form.zip" type="text" class="input" placeholder="15222">
          <div class="input-error" v-if="form.errors.zip">{{ form.errors.zip }}</div>
        </div>
        <div>
          <label class="input-label">Country</label>
          <input v-model="form.country" type="text" class="input" placeholder="USA">
          <div class="input-error" v-if="form.errors.country">{{ form.errors.country }}</div>
        </div>
      </div>

      <div>
        <label class="input-label">Notes</label>
        <textarea
          v-model="form.notes"
          rows="4"
          class="input"
          placeholder="Special instructions, billing contact, or account notes"
        ></textarea>
        <div class="input-error" v-if="form.errors.notes">{{ form.errors.notes }}</div>
      </div>

      <div class="flex items-center space-x-3">
        <input
          id="is_active"
          v-model="form.is_active"
          type="checkbox"
          class="form-checkbox h-5 w-5 text-forge-400"
        >
        <label for="is_active" class="text-steel-200">Customer is active</label>
        <div class="input-error" v-if="form.errors.is_active">{{ form.errors.is_active }}</div>
      </div>

      <div class="flex justify-end space-x-3">
        <Link href="/customers" class="btn-secondary">Cancel</Link>
        <button
          type="submit"
          class="btn-primary"
          :disabled="form.processing"
        >
          Update Customer
        </button>
      </div>
    </form>
  </AppLayout>
</template>
