<!-- resources/js/Pages/ShopTicket/Edit.vue -->
<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    ticket: Object,
    projects: Array,
    employees: Array,
    workAreas: Array,
});

const form = useForm({
    priority: props.ticket?.priority ?? 2,
    due_date: props.ticket?.due_date?.split('T')[0] ?? '',
    notes: props.ticket?.notes ?? '',
    assigned_to_employee_id: props.ticket?.assigned_to_employee_id ?? '',
});

const submit = () => {
    form.put(route('shop-tickets.update', props.ticket.id));
};

const getPriorityLabel = (priority) => {
    const labels = { 1: 'Low', 2: 'Normal', 3: 'Medium', 4: 'High', 5: 'Critical' };
    return labels[priority] || 'Unknown';
};
</script>

<template>
  <AppLayout :title="`Edit ${ticket.ticket_number}`">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Edit Shop Ticket
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              {{ ticket.ticket_number }} - {{ ticket.project?.job_number }}
            </p>
          </div>
          <div class="flex gap-3">
            <Link
              :href="route('shop-tickets.show', ticket.id)"
              class="btn-secondary"
            >
              Cancel
            </Link>
          </div>
        </div>
      </div>
    </template>

    <form
      class="space-y-6"
      @submit.prevent="submit"
    >
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="xl:col-span-2 space-y-6">
          <!-- Ticket Info (Read Only) -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Ticket Information
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <dt class="text-text-tertiary mb-1">
                  Ticket Number
                </dt>
                <dd class="text-white font-mono font-semibold">
                  {{ ticket.ticket_number }}
                </dd>
              </div>
              <div>
                <dt class="text-text-tertiary mb-1">
                  Status
                </dt>
                <dd class="text-white capitalize">
                  {{ ticket.status?.replace('_', ' ') }}
                </dd>
              </div>
              <div>
                <dt class="text-text-tertiary mb-1">
                  Project
                </dt>
                <dd class="text-white">
                  {{ ticket.project?.job_number }} - {{ ticket.project?.name }}
                </dd>
              </div>
              <div>
                <dt class="text-text-tertiary mb-1">
                  Created By
                </dt>
                <dd class="text-white">
                  {{ ticket.creator?.name ?? 'Unknown' }}
                </dd>
              </div>
            </dl>
            <p class="mt-4 text-xs text-text-tertiary">
              Note: Project and status cannot be changed after creation. To change project, delete this ticket and create a new one.
            </p>
          </div>

          <!-- Editable Fields -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Edit Details
            </h3>

            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                    Priority
                  </label>
                  <select
                    v-model="form.priority"
                    class="input-industrial w-full"
                    required
                  >
                    <option :value="1">
                      Low
                    </option>
                    <option :value="2">
                      Normal
                    </option>
                    <option :value="3">
                      Medium
                    </option>
                    <option :value="4">
                      High
                    </option>
                    <option :value="5">
                      Critical
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                    Due Date
                  </label>
                  <input
                    v-model="form.due_date"
                    type="date"
                    class="input-industrial w-full"
                  >
                </div>
              </div>

              <div>
                <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                  Assigned To
                </label>
                <select
                  v-model="form.assigned_to_employee_id"
                  class="input-industrial w-full"
                >
                  <option value="">
                    Unassigned
                  </option>
                  <option
                    v-for="employee in employees"
                    :key="employee.id"
                    :value="employee.id"
                  >
                    {{ employee.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                  Notes
                </label>
                <textarea
                  v-model="form.notes"
                  rows="4"
                  class="input-industrial w-full"
                  placeholder="Additional instructions or notes..."
                />
              </div>
            </div>
          </div>

          <!-- Current Steps (Read Only) -->
          <div
            v-if="ticket.steps?.length"
            class="card-industrial"
          >
            <h3 class="text-lg font-semibold text-white mb-4">
              Workflow Steps
            </h3>
            <p class="text-sm text-text-secondary mb-4">
              Steps cannot be modified once created. View the ticket to complete steps.
            </p>
            <div class="space-y-2">
              <div
                v-for="step in ticket.steps"
                :key="step.id"
                class="flex items-center gap-3 p-3 bg-steel-900/60 border border-steel-700 rounded-sm"
              >
                <div class="w-6 h-6 rounded-full bg-steel-700 flex items-center justify-center text-xs font-bold text-text-secondary">
                  {{ step.sequence }}
                </div>
                <div class="flex-1 text-white">
                  {{ step.work_area?.name ?? 'Unknown' }}
                </div>
                <div class="text-sm text-text-tertiary">
                  {{ step.estimated_hours ?? 0 }}h est.
                </div>
                <div
                  class="text-xs px-2 py-1 rounded capitalize"
                  :class="step.status === 'complete' ? 'bg-green-600/20 text-green-400' : step.status === 'in_progress' ? 'bg-amber-600/20 text-amber-400' : 'bg-steel-600 text-steel-300'"
                >
                  {{ step.status }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Actions -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Actions
            </h3>
            <div class="space-y-3">
              <button
                type="submit"
                class="btn-primary w-full"
                :disabled="form.processing"
              >
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Changes</span>
              </button>
              <Link
                :href="route('shop-tickets.show', ticket.id)"
                class="btn-secondary w-full text-center block"
              >
                View Ticket
              </Link>
            </div>
          </div>

          <!-- Current Values -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Current Values
            </h3>
            <dl class="space-y-3 text-sm">
              <div class="flex justify-between">
                <dt class="text-text-tertiary">
                  Priority
                </dt>
                <dd class="text-white">
                  {{ getPriorityLabel(ticket.priority) }}
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-text-tertiary">
                  Due Date
                </dt>
                <dd class="text-white">
                  {{ ticket.due_date ?? 'Not set' }}
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-text-tertiary">
                  Assigned To
                </dt>
                <dd class="text-white">
                  {{ ticket.assigned_employee?.name ?? 'Unassigned' }}
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </form>
  </AppLayout>
</template>
