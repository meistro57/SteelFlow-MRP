<!-- resources/js/Pages/PurchaseOrders/Index.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    purchaseOrders: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};
</script>

<template>
  <AppLayout title="Purchase Orders">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-white leading-tight">
          Purchase Orders
        </h2>
        <Link
          :href="route('purchase-orders.create')"
          class="btn-forge"
        >
          Create PO
        </Link>
      </div>
    </template>

    <div class="card-industrial">
      <div class="overflow-x-auto">
        <table class="table-industrial">
          <thead>
            <tr>
              <th class="text-left">
                PO Number
              </th>
              <th class="text-left">
                Vendor
              </th>
              <th class="text-left">
                Project
              </th>
              <th class="text-left">
                Status
              </th>
              <th class="text-left">
                Order Date
              </th>
              <th class="text-right">
                Total
              </th>
              <th class="text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="po in purchaseOrders.data"
              :key="po.id"
            >
              <td class="table-data-mono font-bold">
                <Link
                  :href="route('purchase-orders.show', po.id)"
                  class="text-forge-400 hover:text-forge-300"
                >
                  {{ po.po_number }}
                </Link>
              </td>
              <td>{{ po.vendor.name }}</td>
              <td>{{ po.project?.job_number || '-' }}</td>
              <td>
                <span
                  class="badge uppercase text-[10px]"
                  :class="{
                    'bg-steel-700': po.status === 'draft',
                    'bg-blue-900': po.status === 'sent',
                    'bg-green-900': po.status === 'received',
                  }"
                >
                  {{ po.status }}
                </span>
              </td>
              <td class="font-mono text-sm">
                {{ formatDate(po.order_date) }}
              </td>
              <td class="text-right font-mono">
                {{ formatCurrency(po.total) }}
              </td>
              <td class="text-right">
                <Link
                  :href="route('purchase-orders.edit', po.id)"
                  class="text-forge-500 hover:text-forge-400"
                >
                  Edit
                </Link>
              </td>
            </tr>
            <tr v-if="purchaseOrders.data.length === 0">
              <td
                colspan="7"
                class="text-center py-12 text-steel-500 italic"
              >
                No purchase orders found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
