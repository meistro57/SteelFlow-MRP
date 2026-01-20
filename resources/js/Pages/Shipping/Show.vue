<!-- resources/js/Pages/Shipping/Show.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    load: Object,
    availableInstances: Array,
});

const page = usePage();

const showAddItemModal = ref(false);
const showEditModal = ref(false);

const addItemForm = useForm({
    assembly_instance_id: '',
});

const editForm = useForm({
    destination: props.load.destination || '',
    ship_date: props.load.ship_date || '',
    carrier: props.load.carrier || '',
    truck_number: props.load.truck_number || '',
    trailer_number: props.load.trailer_number || '',
    driver_name: props.load.driver_name || '',
    bol_number: props.load.bol_number || '',
    notes: props.load.notes || '',
});

const addItem = () => {
    addItemForm.post(route('shipping.add-item', props.load.id), {
        onSuccess: () => {
            showAddItemModal.value = false;
            addItemForm.reset();
        },
    });
};

const removeItem = (itemId) => {
    if (confirm('Remove this item from the load?')) {
        router.delete(route('shipping.remove-item', [props.load.id, itemId]));
    }
};

const updateLoad = () => {
    editForm.put(route('shipping.update', props.load.id), {
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const planLoad = () => {
    if (confirm('Mark this load as planned? This will make it ready to ship.')) {
        router.post(route('shipping.plan', props.load.id));
    }
};

const shipLoad = () => {
    if (confirm('Ship this load? This will mark it as in transit.')) {
        router.post(route('shipping.ship', props.load.id));
    }
};

const deliverLoad = () => {
    if (confirm('Mark this load as delivered?')) {
        router.post(route('shipping.deliver', props.load.id));
    }
};

const deleteLoad = () => {
    if (confirm('Delete this load? This cannot be undone.')) {
        router.delete(route('shipping.destroy', props.load.id));
    }
};

const getStatusColor = (status) => {
    const colors = {
        draft: 'bg-steel-600 text-steel-300',
        planned: 'bg-blue-600/20 text-blue-400',
        in_transit: 'bg-amber-600/20 text-amber-400',
        delivered: 'bg-green-600/20 text-green-400',
    };
    return colors[status] || 'bg-steel-600 text-steel-200';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatWeight = (lbs, kg) => {
    if (!lbs) return '0 lbs';
    return `${lbs.toLocaleString()} lbs (${kg?.toFixed(2) ?? 0} kg)`;
};
</script>

<template>
  <AppLayout :title="`Load ${load.load_number}`">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          {{ load.load_number }}
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          {{ load.project?.job_number }} - {{ load.project?.name }}
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Link
          :href="route('shipping.index')"
          class="btn-secondary"
        >
          Back to Loads
        </Link>
      </div>
    </div>

    <!-- Flash Messages -->
    <div
      v-if="page.props.flash?.success"
      class="mb-6 p-4 bg-green-600/20 border border-green-500 rounded text-green-400"
    >
      {{ page.props.flash.success }}
    </div>
    <div
      v-if="page.props.flash?.error"
      class="mb-6 p-4 bg-red-600/20 border border-red-500 rounded text-red-400"
    >
      {{ page.props.flash.error }}
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Details -->
      <div class="lg:col-span-2 space-y-6">
        <div class="card-industrial">
          <div class="flex items-center justify-between mb-4 border-b border-steel-700 pb-2">
            <h2 class="text-xl font-semibold text-white">
              Load Details
            </h2>
            <button
              v-if="load.status === 'draft'"
              type="button"
              class="btn-secondary text-sm"
              @click="showEditModal = true"
            >
              Edit Details
            </button>
          </div>
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Status
              </dt>
              <dd class="mt-1">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(load.status)"
                >
                  {{ load.status?.replace('_', ' ') }}
                </span>
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Destination
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ load.destination || '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Ship Date
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ formatDate(load.ship_date) }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Carrier
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ load.carrier || '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Truck Number
              </dt>
              <dd class="mt-1 text-text-secondary font-mono">
                {{ load.truck_number || '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Trailer Number
              </dt>
              <dd class="mt-1 text-text-secondary font-mono">
                {{ load.trailer_number || '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Driver Name
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ load.driver_name || '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                BOL Number
              </dt>
              <dd class="mt-1 text-text-secondary font-mono">
                {{ load.bol_number || '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Total Weight
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ formatWeight(load.total_weight_lbs, load.total_weight_kg) }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Total Pieces
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ load.total_pieces }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Shipped At
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ formatDate(load.shipped_at) }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Delivered At
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ formatDate(load.delivered_at) }}
              </dd>
            </div>
          </dl>

          <div
            v-if="load.notes"
            class="mt-4 pt-4 border-t border-steel-700"
          >
            <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
              Notes
            </dt>
            <dd class="mt-1 text-text-secondary">
              {{ load.notes }}
            </dd>
          </div>
        </div>

        <!-- Load Items -->
        <div class="card-industrial">
          <div class="flex items-center justify-between mb-4 border-b border-steel-700 pb-2">
            <h2 class="text-xl font-semibold text-white">
              Load Items ({{ load.items?.length ?? 0 }})
            </h2>
            <button
              v-if="load.status === 'draft'"
              type="button"
              class="btn-secondary text-sm"
              @click="showAddItemModal = true"
            >
              Add Item
            </button>
          </div>

          <div
            v-if="!load.items || load.items.length === 0"
            class="text-center py-8 text-text-tertiary"
          >
            No items added to this load yet.
          </div>

          <div
            v-else
            class="overflow-x-auto"
          >
            <table class="min-w-full text-sm">
              <thead class="text-xs uppercase tracking-wider text-text-tertiary">
                <tr class="border-b border-steel-700">
                  <th class="py-2 text-left">
                    Mark
                  </th>
                  <th class="py-2 text-left">
                    Assembly
                  </th>
                  <th class="py-2 text-left">
                    Weight
                  </th>
                  <th class="py-2 text-left">
                    Loaded At
                  </th>
                  <th
                    v-if="load.status === 'draft'"
                    class="py-2 text-right"
                  >
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in load.items"
                  :key="item.id"
                  class="border-b border-steel-800/80 text-text-secondary"
                >
                  <td class="py-3">
                    <span class="font-mono text-white font-semibold">
                      {{ item.assembly_instance?.mark ?? 'N/A' }}
                    </span>
                  </td>
                  <td class="py-3">
                    {{ item.assembly_instance?.assembly?.description ?? 'N/A' }}
                  </td>
                  <td class="py-3">
                    {{ formatWeight(item.weight_lbs, item.weight_kg) }}
                  </td>
                  <td class="py-3">
                    {{ formatDate(item.loaded_at) }}
                  </td>
                  <td
                    v-if="load.status === 'draft'"
                    class="py-3 text-right"
                  >
                    <button
                      type="button"
                      class="btn-secondary text-sm"
                      @click="removeItem(item.id)"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Actions Sidebar -->
      <div class="space-y-4">
        <div class="card-industrial">
          <h3 class="text-lg font-semibold text-white mb-4">
            Actions
          </h3>
          <div class="space-y-2">
            <button
              v-if="load.status === 'draft'"
              type="button"
              class="w-full btn-primary"
              @click="planLoad"
            >
              Mark as Planned
            </button>
            <button
              v-if="load.status === 'planned'"
              type="button"
              class="w-full btn-primary"
              @click="shipLoad"
            >
              Ship Load
            </button>
            <button
              v-if="load.status === 'in_transit'"
              type="button"
              class="w-full btn-primary"
              @click="deliverLoad"
            >
              Mark as Delivered
            </button>
            <button
              v-if="load.status === 'draft'"
              type="button"
              class="w-full btn-secondary text-red-400 hover:bg-red-900/20"
              @click="deleteLoad"
            >
              Delete Load
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Item Modal -->
    <div
      v-if="showAddItemModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="showAddItemModal = false"
    >
      <div class="card-industrial max-w-md w-full">
        <h3 class="text-lg font-semibold text-white mb-4">
          Add Item to Load
        </h3>
        <form @submit.prevent="addItem">
          <div class="mb-4">
            <label class="input-label">Assembly Instance</label>
            <select
              v-model="addItemForm.assembly_instance_id"
              class="input"
              required
            >
              <option value="">
                Select an assembly...
              </option>
              <option
                v-for="instance in availableInstances"
                :key="instance.id"
                :value="instance.id"
              >
                {{ instance.mark }} - {{ instance.assembly?.description }}
              </option>
            </select>
            <div
              v-if="availableInstances.length === 0"
              class="mt-2 text-xs text-text-tertiary"
            >
              No available assemblies for this project.
            </div>
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="btn-secondary"
              @click="showAddItemModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="addItemForm.processing || availableInstances.length === 0"
            >
              Add Item
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Load Modal -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto"
      @click.self="showEditModal = false"
    >
      <div class="card-industrial max-w-2xl w-full my-8">
        <h3 class="text-lg font-semibold text-white mb-4">
          Edit Load Details
        </h3>
        <form @submit.prevent="updateLoad">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="input-label">Destination</label>
              <input
                v-model="editForm.destination"
                type="text"
                class="input"
              >
            </div>
            <div>
              <label class="input-label">Ship Date</label>
              <input
                v-model="editForm.ship_date"
                type="date"
                class="input"
              >
            </div>
            <div>
              <label class="input-label">Carrier</label>
              <input
                v-model="editForm.carrier"
                type="text"
                class="input"
              >
            </div>
            <div>
              <label class="input-label">BOL Number</label>
              <input
                v-model="editForm.bol_number"
                type="text"
                class="input"
              >
            </div>
            <div>
              <label class="input-label">Truck Number</label>
              <input
                v-model="editForm.truck_number"
                type="text"
                class="input"
              >
            </div>
            <div>
              <label class="input-label">Trailer Number</label>
              <input
                v-model="editForm.trailer_number"
                type="text"
                class="input"
              >
            </div>
            <div>
              <label class="input-label">Driver Name</label>
              <input
                v-model="editForm.driver_name"
                type="text"
                class="input"
              >
            </div>
            <div class="md:col-span-2">
              <label class="input-label">Notes</label>
              <textarea
                v-model="editForm.notes"
                rows="3"
                class="input"
              />
            </div>
          </div>
          <div class="flex justify-end gap-3 mt-4">
            <button
              type="button"
              class="btn-secondary"
              @click="showEditModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="editForm.processing"
            >
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
