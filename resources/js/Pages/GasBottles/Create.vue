<!-- resources/js/Pages/GasBottles/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customers: Array,
    bottles: Array,
});

const form = useForm({
    stock_item_id: '',
    customer_id: '',
    rental_type: 'reserve',
    project_id: '',
    expires_at: '',
    due_back_at: '',
    deposit_cents: 0,
    rental_rate_cents: 0,
    rental_rate_period: 'day',
    notes: '',
});

const selectedBottle = ref(null);

watch(() => form.stock_item_id, (newVal) => {
    selectedBottle.value = props.bottles.find(b => b.id === parseInt(newVal));
});

const submit = () => {
    form.post(route('gas-bottles.store'));
};

const formatCurrency = (value) => {
    if (!value) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value / 100);
};
</script>

<template>
  <AppLayout title="New Gas Bottle Rental">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          New Gas Bottle Rental
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Reserve or checkout a gas bottle to a customer.
        </p>
      </div>
      <Link
        :href="route('gas-bottles.index')"
        class="btn-secondary"
      >
        Back to Rentals
      </Link>
    </div>

    <form
      class="card p-6 space-y-6"
      @submit.prevent="submit"
    >
      <!-- Rental Type -->
      <div>
        <label class="input-label">Rental Type</label>
        <div class="flex gap-4 mt-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.rental_type"
              type="radio"
              value="reserve"
              class="text-forge-500 focus:ring-forge-500"
            >
            <span class="text-text-secondary">Reserve Only</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.rental_type"
              type="radio"
              value="checkout"
              class="text-forge-500 focus:ring-forge-500"
            >
            <span class="text-text-secondary">Checkout Immediately</span>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Gas Bottle -->
        <div>
          <label class="input-label">Gas Bottle</label>
          <select
            v-model="form.stock_item_id"
            class="input"
            required
          >
            <option value="">
              Select a bottle...
            </option>
            <option
              v-for="bottle in bottles"
              :key="bottle.id"
              :value="bottle.id"
            >
              {{ bottle.internal_code }} - {{ bottle.material?.description ?? 'Unknown' }}
            </option>
          </select>
          <div
            v-if="form.errors.stock_item_id"
            class="input-error"
          >
            {{ form.errors.stock_item_id }}
          </div>
          <div
            v-if="selectedBottle"
            class="mt-2 text-xs text-text-tertiary"
          >
            Status: <span class="text-green-400">{{ selectedBottle.status }}</span>
          </div>
        </div>

        <!-- Customer -->
        <div>
          <label class="input-label">Customer</label>
          <select
            v-model="form.customer_id"
            class="input"
            required
          >
            <option value="">
              Select a customer...
            </option>
            <option
              v-for="customer in customers"
              :key="customer.id"
              :value="customer.id"
            >
              {{ customer.code }} - {{ customer.name }}
            </option>
          </select>
          <div
            v-if="form.errors.customer_id"
            class="input-error"
          >
            {{ form.errors.customer_id }}
          </div>
        </div>

        <!-- Reservation Expiry (only for reserve type) -->
        <div v-if="form.rental_type === 'reserve'">
          <label class="input-label">Reservation Expires</label>
          <input
            v-model="form.expires_at"
            type="datetime-local"
            class="input"
          >
          <div
            v-if="form.errors.expires_at"
            class="input-error"
          >
            {{ form.errors.expires_at }}
          </div>
        </div>

        <!-- Due Back Date (only for checkout type) -->
        <div v-if="form.rental_type === 'checkout'">
          <label class="input-label">Due Back Date</label>
          <input
            v-model="form.due_back_at"
            type="date"
            class="input"
            required
          >
          <div
            v-if="form.errors.due_back_at"
            class="input-error"
          >
            {{ form.errors.due_back_at }}
          </div>
        </div>
      </div>

      <!-- Checkout Details (only for checkout type) -->
      <div v-if="form.rental_type === 'checkout'">
        <h3 class="text-lg font-semibold text-white mb-4 border-b border-steel-700 pb-2">
          Checkout Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Deposit -->
          <div>
            <label class="input-label">Deposit Amount ($)</label>
            <input
              v-model.number="form.deposit_cents"
              type="number"
              step="0.01"
              class="input"
              placeholder="0.00"
              @input="form.deposit_cents = Math.round($event.target.value * 100)"
            >
            <div
              v-if="form.errors.deposit_cents"
              class="input-error"
            >
              {{ form.errors.deposit_cents }}
            </div>
            <div class="mt-1 text-xs text-text-tertiary">
              Deposit: {{ formatCurrency(form.deposit_cents) }}
            </div>
          </div>

          <!-- Rental Rate -->
          <div>
            <label class="input-label">Rental Rate ($)</label>
            <input
              v-model.number="form.rental_rate_cents"
              type="number"
              step="0.01"
              class="input"
              placeholder="0.00"
              @input="form.rental_rate_cents = Math.round($event.target.value * 100)"
            >
            <div
              v-if="form.errors.rental_rate_cents"
              class="input-error"
            >
              {{ form.errors.rental_rate_cents }}
            </div>
            <div class="mt-1 text-xs text-text-tertiary">
              Rate: {{ formatCurrency(form.rental_rate_cents) }}
            </div>
          </div>

          <!-- Rental Period -->
          <div>
            <label class="input-label">Rental Period</label>
            <select
              v-model="form.rental_rate_period"
              class="input"
            >
              <option value="day">
                Per Day
              </option>
              <option value="week">
                Per Week
              </option>
              <option value="month">
                Per Month
              </option>
            </select>
            <div
              v-if="form.errors.rental_rate_period"
              class="input-error"
            >
              {{ form.errors.rental_rate_period }}
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="input-label">Notes</label>
        <textarea
          v-model="form.notes"
          rows="3"
          class="input"
          placeholder="Optional notes about this rental..."
        />
        <div
          v-if="form.errors.notes"
          class="input-error"
        >
          {{ form.errors.notes }}
        </div>
      </div>

      <!-- Submit -->
      <div class="flex items-center justify-end gap-4">
        <Link
          :href="route('gas-bottles.index')"
          class="btn-secondary"
        >
          Cancel
        </Link>
        <button
          type="submit"
          class="btn-primary"
          :disabled="form.processing"
        >
          {{ form.rental_type === 'reserve' ? 'Reserve Bottle' : 'Checkout Bottle' }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>
