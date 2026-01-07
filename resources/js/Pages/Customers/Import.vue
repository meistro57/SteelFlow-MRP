<!-- resources/js/Pages/Customers/Import.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    file: null,
});

const submit = () => {
    form.post('/customers/import', {
        forceFormData: true,
    });
};
</script>

<template>
  <AppLayout title="Import Customers">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Import Customers
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Upload a CSV with customer details to bulk add or update records.
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
      <div>
        <label class="input-label">CSV File</label>
        <input
          type="file"
          accept=".csv,text/csv"
          class="input"
          required
          @change="form.file = $event.target.files[0]"
        >
        <div
          v-if="form.errors.file"
          class="input-error"
        >
          {{ form.errors.file }}
        </div>
        <p class="mt-2 text-xs text-steel-400 font-mono">
          Expected headers: code, name, address_1, address_2, city, state, zip, country, phone, email, notes, is_active.
          Rows with missing names will be skipped politely.
        </p>
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
          Import Customers
        </button>
      </div>
    </form>
  </AppLayout>
</template>
