<!-- resources/js/Pages/Customers/Index.vue -->
<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, (value) => {
    router.get('/customers', { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const confirmDelete = (customer) => {
    if (confirm(`Delete ${customer.name}? This cannot be undone.`)) {
        router.delete(`/customers/${customer.id}`);
    }
};
</script>

<template>
  <AppLayout title="Customers">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Customers
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Maintain your customer roster and import updates.
        </p>
      </div>
      <div class="flex items-center space-x-3 mt-4 md:mt-0">
        <Link
          href="/customers/import"
          class="btn-secondary"
        >
          Import CSV
        </Link>
        <Link
          href="/customers/create"
          class="btn-primary"
        >
          New Customer
        </Link>
      </div>
    </div>

    <div class="card mb-6">
      <div class="p-4">
        <label class="block text-xs uppercase tracking-wider text-steel-400 mb-2 font-mono">
          Search Customers
        </label>
        <input
          v-model="search"
          type="text"
          placeholder="Code, name, email, phone"
          class="input w-full md:w-1/2"
        >
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-steel-800">
          <thead class="bg-steel-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-steel-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-steel-900 divide-y divide-steel-800">
            <tr
              v-for="customer in customers.data"
              :key="customer.id"
              class="hover:bg-steel-800/60 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-200 font-mono">
                {{ customer.code || '—' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-semibold">
                {{ customer.name }}
                <div class="text-xs text-steel-400" v-if="customer.city || customer.state">
                  {{ [customer.city, customer.state].filter(Boolean).join(', ') }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-200">
                <div v-if="customer.email">{{ customer.email }}</div>
                <div v-if="customer.phone" class="text-steel-400">{{ customer.phone }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="customer.is_active"
                  class="badge-complete"
                >
                  Active
                </span>
                <span
                  v-else
                  class="badge-free"
                >
                  Inactive
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <Link
                  :href="`/customers/${customer.id}/edit`"
                  class="text-weld-300 hover:text-weld-200"
                >
                  Edit
                </Link>
                <button
                  type="button"
                  class="text-forge-300 hover:text-forge-200"
                  @click="confirmDelete(customer)"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="customers.data.length === 0">
              <td class="px-6 py-4 text-center text-sm text-steel-400" colspan="5">
                No customers found. Try adjusting your search or import a list.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
