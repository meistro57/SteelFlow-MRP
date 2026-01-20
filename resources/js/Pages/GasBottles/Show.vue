<!-- resources/js/Pages/GasBottles/Show.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    rental: Object,
    inspections: Array,
    availableBottles: Array,
});

const page = usePage();

const showReturnModal = ref(false);
const showSwapModal = ref(false);
const showFlagModal = ref(false);
const showInspectionModal = ref(false);

const returnForm = useForm({
    notes: '',
});

const swapForm = useForm({
    replacement_id: '',
    notes: '',
});

const flagForm = useForm({
    flag: 'damaged',
    notes: '',
});

const inspectionForm = useForm({
    scheduled_for: '',
    notes: '',
});

const returnBottle = () => {
    returnForm.post(route('gas-bottles.return', props.rental.id), {
        onSuccess: () => {
            showReturnModal.value = false;
            returnForm.reset();
        },
    });
};

const swapBottle = () => {
    swapForm.post(route('gas-bottles.swap', props.rental.id), {
        onSuccess: () => {
            showSwapModal.value = false;
            swapForm.reset();
        },
    });
};

const flagBottle = () => {
    flagForm.post(route('gas-bottles.flag', props.rental.id), {
        onSuccess: () => {
            showFlagModal.value = false;
            flagForm.reset();
        },
    });
};

const scheduleInspection = () => {
    inspectionForm.post(route('gas-bottles.schedule-inspection', props.rental.id), {
        onSuccess: () => {
            showInspectionModal.value = false;
            inspectionForm.reset();
        },
    });
};

const getStatusColor = (status) => {
    const colors = {
        reserved: 'bg-blue-600/20 text-blue-400',
        active: 'bg-green-600/20 text-green-400',
        closed: 'bg-steel-600 text-steel-300',
        lost: 'bg-red-600/20 text-red-400',
        damaged: 'bg-amber-600/20 text-amber-400',
    };
    return colors[status] || 'bg-steel-600 text-steel-200';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (cents) => {
    if (!cents) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(cents / 100);
};

const isOverdue = () => {
    if (!props.rental.due_back_at || props.rental.status !== 'active') return false;
    return new Date(props.rental.due_back_at) < new Date();
};
</script>

<template>
  <AppLayout :title="`Gas Bottle Rental #${rental.id}`">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Gas Bottle Rental #{{ rental.id }}
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          {{ rental.stock_item?.internal_code }}
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Link
          :href="route('gas-bottles.index')"
          class="btn-secondary"
        >
          Back to Rentals
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
          <h2 class="text-xl font-semibold text-white mb-4 border-b border-steel-700 pb-2">
            Rental Details
          </h2>
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Status
              </dt>
              <dd class="mt-1">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(rental.status)"
                >
                  {{ rental.status?.replace('_', ' ') }}
                </span>
                <span
                  v-if="isOverdue()"
                  class="ml-2 px-2 py-1 text-xs rounded-sm bg-red-600/20 text-red-400"
                >
                  OVERDUE
                </span>
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Customer
              </dt>
              <dd class="mt-1 text-text-secondary">
                <div class="text-white font-semibold">
                  {{ rental.customer?.name }}
                </div>
                <div class="text-sm text-text-tertiary">
                  {{ rental.customer?.code }}
                </div>
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Bottle
              </dt>
              <dd class="mt-1 text-text-secondary">
                <div class="text-white font-mono">
                  {{ rental.stock_item?.internal_code }}
                </div>
                <div class="text-sm text-text-tertiary">
                  {{ rental.stock_item?.material?.description }}
                </div>
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Checked Out
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ formatDate(rental.checked_out_at) }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Due Back
              </dt>
              <dd
                class="mt-1"
                :class="isOverdue() ? 'text-red-400' : 'text-text-secondary'"
              >
                {{ formatDate(rental.due_back_at) }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Returned
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ formatDate(rental.returned_at) }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Deposit
              </dt>
              <dd class="mt-1 text-text-secondary">
                {{ formatCurrency(rental.deposit_cents) }}
              </dd>
            </div>

            <div>
              <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
                Rental Rate
              </dt>
              <dd class="mt-1 text-text-secondary">
                <span v-if="rental.rental_rate_cents">
                  {{ formatCurrency(rental.rental_rate_cents) }}/{{ rental.rental_rate_period }}
                </span>
                <span v-else>-</span>
              </dd>
            </div>
          </dl>

          <div
            v-if="rental.notes"
            class="mt-4 pt-4 border-t border-steel-700"
          >
            <dt class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
              Notes
            </dt>
            <dd class="mt-1 text-text-secondary">
              {{ rental.notes }}
            </dd>
          </div>
        </div>

        <!-- Inspections -->
        <div class="card-industrial">
          <div class="flex items-center justify-between mb-4 border-b border-steel-700 pb-2">
            <h2 class="text-xl font-semibold text-white">
              Inspection History
            </h2>
            <button
              type="button"
              class="btn-secondary text-sm"
              @click="showInspectionModal = true"
            >
              Schedule Inspection
            </button>
          </div>

          <div
            v-if="inspections.length === 0"
            class="text-center py-8 text-text-tertiary"
          >
            No inspections scheduled.
          </div>

          <div
            v-else
            class="space-y-3"
          >
            <div
              v-for="inspection in inspections"
              :key="inspection.id"
              class="p-3 bg-steel-800/40 rounded border border-steel-700"
            >
              <div class="flex justify-between items-start">
                <div>
                  <div class="text-sm text-white font-semibold">
                    Scheduled: {{ formatDate(inspection.scheduled_for) }}
                  </div>
                  <div
                    v-if="inspection.completed_at"
                    class="text-xs text-text-tertiary"
                  >
                    Completed: {{ formatDate(inspection.completed_at) }}
                  </div>
                  <div
                    v-if="inspection.outcome"
                    class="text-xs text-text-secondary mt-1"
                  >
                    Outcome: {{ inspection.outcome }}
                  </div>
                </div>
                <span
                  class="px-2 py-1 text-xs rounded-sm"
                  :class="inspection.completed_at ? 'bg-green-600/20 text-green-400' : 'bg-blue-600/20 text-blue-400'"
                >
                  {{ inspection.completed_at ? 'Completed' : 'Pending' }}
                </span>
              </div>
              <div
                v-if="inspection.notes"
                class="mt-2 text-xs text-text-tertiary"
              >
                {{ inspection.notes }}
              </div>
            </div>
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
              v-if="rental.status === 'active'"
              type="button"
              class="w-full btn-primary"
              @click="showReturnModal = true"
            >
              Return Bottle
            </button>
            <button
              v-if="rental.status === 'active'"
              type="button"
              class="w-full btn-secondary"
              @click="showSwapModal = true"
            >
              Swap Bottle
            </button>
            <button
              v-if="rental.status === 'active'"
              type="button"
              class="w-full btn-secondary"
              @click="showFlagModal = true"
            >
              Flag as Lost/Damaged
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Return Modal -->
    <div
      v-if="showReturnModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="showReturnModal = false"
    >
      <div class="card-industrial max-w-md w-full">
        <h3 class="text-lg font-semibold text-white mb-4">
          Return Gas Bottle
        </h3>
        <form @submit.prevent="returnBottle">
          <div class="mb-4">
            <label class="input-label">Notes</label>
            <textarea
              v-model="returnForm.notes"
              rows="3"
              class="input"
              placeholder="Optional return notes..."
            />
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="btn-secondary"
              @click="showReturnModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="returnForm.processing"
            >
              Confirm Return
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Swap Modal -->
    <div
      v-if="showSwapModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="showSwapModal = false"
    >
      <div class="card-industrial max-w-md w-full">
        <h3 class="text-lg font-semibold text-white mb-4">
          Swap Gas Bottle
        </h3>
        <form @submit.prevent="swapBottle">
          <div class="mb-4">
            <label class="input-label">Replacement Bottle</label>
            <select
              v-model="swapForm.replacement_id"
              class="input"
              required
            >
              <option value="">
                Select a bottle...
              </option>
              <option
                v-for="bottle in availableBottles"
                :key="bottle.id"
                :value="bottle.id"
              >
                {{ bottle.internal_code }} - {{ bottle.material?.description }}
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="input-label">Notes</label>
            <textarea
              v-model="swapForm.notes"
              rows="3"
              class="input"
              placeholder="Reason for swap..."
            />
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="btn-secondary"
              @click="showSwapModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="swapForm.processing"
            >
              Confirm Swap
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Flag Modal -->
    <div
      v-if="showFlagModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="showFlagModal = false"
    >
      <div class="card-industrial max-w-md w-full">
        <h3 class="text-lg font-semibold text-white mb-4">
          Flag Gas Bottle
        </h3>
        <form @submit.prevent="flagBottle">
          <div class="mb-4">
            <label class="input-label">Flag Type</label>
            <select
              v-model="flagForm.flag"
              class="input"
              required
            >
              <option value="damaged">
                Damaged
              </option>
              <option value="lost">
                Lost
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="input-label">Notes</label>
            <textarea
              v-model="flagForm.notes"
              rows="3"
              class="input"
              placeholder="Describe the issue..."
              required
            />
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="btn-secondary"
              @click="showFlagModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="flagForm.processing"
            >
              Confirm Flag
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Inspection Modal -->
    <div
      v-if="showInspectionModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="showInspectionModal = false"
    >
      <div class="card-industrial max-w-md w-full">
        <h3 class="text-lg font-semibold text-white mb-4">
          Schedule Inspection
        </h3>
        <form @submit.prevent="scheduleInspection">
          <div class="mb-4">
            <label class="input-label">Scheduled Date</label>
            <input
              v-model="inspectionForm.scheduled_for"
              type="datetime-local"
              class="input"
              required
            >
          </div>
          <div class="mb-4">
            <label class="input-label">Notes</label>
            <textarea
              v-model="inspectionForm.notes"
              rows="3"
              class="input"
              placeholder="Optional inspection notes..."
            />
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="btn-secondary"
              @click="showInspectionModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="inspectionForm.processing"
            >
              Schedule
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
