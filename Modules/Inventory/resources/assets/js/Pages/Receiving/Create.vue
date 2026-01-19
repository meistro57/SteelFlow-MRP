<!-- Modules/Inventory/resources/assets/js/Pages/Receiving/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    purchaseOrder: Object,
});

const form = useForm({
    receive_date: new Date().toISOString().split('T')[0],
    lines: props.purchaseOrder.lines.map(line => ({
        id: line.id,
        type: line.type,
        size: line.size,
        quantity_ordered: line.quantity,
        already_received: line.quantity_received,
        quantity_received: 0,
        heat_number: '',
        stock_area: '',
    })),
});

const submit = () => {
    form.post(route('purchase-orders.receive.store', props.purchaseOrder.id));
};
</script>

<template>
  <AppLayout title="Receive Materials">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-semibold text-xl text-white leading-tight">
            Receive Materials: {{ purchaseOrder.po_number }}
          </h2>
          <p class="text-sm text-steel-400">
            Vendor: {{ purchaseOrder.vendor.name }}
          </p>
        </div>
        <Link
          :href="route('purchase-orders.show', purchaseOrder.id)"
          class="btn-secondary"
        >
          Cancel
        </Link>
      </div>
    </template>

    <div class="max-w-7xl mx-auto space-y-6">
      <form
        class="space-y-6"
        @submit.prevent="submit"
      >
        <div class="card-industrial">
          <div class="flex items-center gap-6">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase">Receive Date</label>
              <input
                v-model="form.receive_date"
                type="date"
                class="input-industrial w-full font-mono"
                required
              >
            </div>
          </div>
        </div>

        <div class="card-industrial">
          <h3 class="text-forge-500 font-bold uppercase tracking-widest text-xs mb-4">
            Line Items
          </h3>
          
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-steel-700 text-steel-500 uppercase text-[10px] font-bold">
                  <th class="pb-2 text-left">
                    Item Info
                  </th>
                  <th class="pb-2 text-right">
                    Ordered
                  </th>
                  <th class="pb-2 text-right">
                    Prev. Received
                  </th>
                  <th class="pb-2 text-right w-32">
                    Receiving Now
                  </th>
                  <th class="pb-2 text-left pl-4">
                    Heat Number
                  </th>
                  <th class="pb-2 text-left pl-4 w-40">
                    Stock Area
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-steel-800">
                <tr
                  v-for="(line, index) in form.lines"
                  :key="index"
                  class="py-4"
                >
                  <td class="py-4">
                    <div class="font-bold text-white">
                      {{ line.type }} {{ line.size }}
                    </div>
                    <div class="text-xs text-steel-500 font-mono">
                      {{ line.grade }}
                    </div>
                  </td>
                  <td class="py-4 text-right font-mono">
                    {{ line.quantity_ordered }}
                  </td>
                  <td class="py-4 text-right font-mono text-steel-400">
                    {{ line.already_received }}
                  </td>
                  <td class="py-4 text-right pl-4">
                    <input
                      v-model="line.quantity_received"
                      type="number"
                      step="0.001"
                      min="0"
                      class="input-industrial-sm w-full text-right font-bold text-forge-400"
                    >
                  </td>
                  <td class="py-4 text-left pl-4">
                    <input
                      v-model="line.heat_number"
                      type="text"
                      class="input-industrial-sm w-full font-mono"
                      placeholder="Heat #"
                    >
                  </td>
                  <td class="py-4 text-left pl-4">
                    <select
                      v-model="line.stock_area"
                      class="input-industrial-sm w-full"
                    >
                      <option value="">
                        Select Area
                      </option>
                      <option value="yard_a">
                        Yard A
                      </option>
                      <option value="yard_b">
                        Yard B
                      </option>
                      <option value="warehouse">
                        Warehouse
                      </option>
                    </select>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="flex justify-end pt-6">
          <button
            type="submit"
            class="btn-forge px-12 py-3 text-lg"
            :disabled="form.processing"
          >
            Process Receiving
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
