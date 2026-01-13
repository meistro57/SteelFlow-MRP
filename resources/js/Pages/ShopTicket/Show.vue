<!-- resources/js/Pages/ShopTicket/Show.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    ticket: Object,
    employees: Array,
});

const page = usePage();

const showCompleteModal = ref(false);
const selectedStep = ref(null);

const completeForm = useForm({
    actual_hours: 0,
    notes: '',
    completed_by: null,
});

const openCompleteModal = (step) => {
    selectedStep.value = step;
    completeForm.actual_hours = step.estimated_hours || 0;
    completeForm.notes = '';
    completeForm.completed_by = null;
    showCompleteModal.value = true;
};

const submitComplete = () => {
    completeForm.post(route('shop-tickets.steps.complete', [props.ticket.id, selectedStep.value.id]), {
        onSuccess: () => {
            showCompleteModal.value = false;
            selectedStep.value = null;
        },
    });
};

const releaseTicket = () => {
    if (confirm('Release this ticket to production?')) {
        router.post(route('shop-tickets.release', props.ticket.id));
    }
};

const deleteTicket = () => {
    if (confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
        router.delete(route('shop-tickets.destroy', props.ticket.id));
    }
};

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-blue-600/20 text-blue-400',
        in_progress: 'bg-amber-600/20 text-amber-400',
        completed: 'bg-green-600/20 text-green-400',
        complete: 'bg-green-600/20 text-green-400',
        on_hold: 'bg-red-600/20 text-red-400',
        pending: 'bg-steel-600 text-steel-300',
    };
    return colors[status] || 'bg-steel-600 text-steel-200';
};

const getPriorityLabel = (priority) => {
    const labels = { 1: 'Low', 2: 'Normal', 3: 'Medium', 4: 'High', 5: 'Critical' };
    return labels[priority] || 'Unknown';
};

const getPriorityColor = (priority) => {
    if (priority >= 5) return 'text-red-400';
    if (priority >= 4) return 'text-amber-400';
    if (priority >= 3) return 'text-yellow-400';
    return 'text-text-secondary';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const completedStepsCount = () => {
    return (props.ticket.steps ?? []).filter(s => s.status === 'complete').length;
};

const progressPercentage = () => {
    const steps = props.ticket.steps ?? [];
    if (steps.length === 0) return 0;
    return Math.round((completedStepsCount() / steps.length) * 100);
};
</script>

<template>
  <AppLayout :title="`Shop Ticket ${ticket.ticket_number}`">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight font-mono">
              {{ ticket.ticket_number }}
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              {{ ticket.project?.job_number }} - {{ ticket.project?.name }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button
              v-if="ticket.status === 'open'"
              type="button"
              class="btn-primary"
              @click="releaseTicket"
            >
              Release to Production
            </button>
            <Link
              :href="route('shop-tickets.edit', ticket.id)"
              class="btn-secondary"
            >
              Edit
            </Link>
            <button
              type="button"
              class="btn-danger"
              @click="deleteTicket"
            >
              Delete
            </button>
            <Link
              :href="route('shop-tickets.index')"
              class="btn-secondary"
            >
              Back to List
            </Link>
          </div>
        </div>
      </div>
    </template>

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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Main Info -->
      <div class="xl:col-span-2 space-y-6">
        <!-- Status and Progress -->
        <div class="card-industrial">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">
              Status
            </h3>
            <span
              class="px-3 py-1 text-sm rounded-sm capitalize"
              :class="getStatusColor(ticket.status)"
            >
              {{ ticket.status?.replace('_', ' ') ?? 'unknown' }}
            </span>
          </div>

          <div
            v-if="ticket.steps?.length"
            class="space-y-3"
          >
            <div class="flex items-center justify-between text-sm">
              <span class="text-text-secondary">Overall Progress</span>
              <span class="text-white font-semibold">{{ progressPercentage() }}%</span>
            </div>
            <div class="w-full bg-steel-800 rounded-full h-3">
              <div
                class="bg-forge-500 h-3 rounded-full transition-all"
                :style="{ width: `${progressPercentage()}%` }"
              />
            </div>
            <div class="text-xs text-text-tertiary">
              {{ completedStepsCount() }} of {{ ticket.steps.length }} steps completed
            </div>
          </div>
        </div>

        <!-- Steps/Workflow -->
        <div class="card-industrial">
          <h3 class="text-lg font-semibold text-white mb-4">
            Workflow Steps
          </h3>

          <div
            v-if="ticket.steps?.length"
            class="space-y-4"
          >
            <div
              v-for="step in ticket.steps"
              :key="step.id"
              class="flex items-center gap-4 p-4 bg-steel-900/60 border border-steel-700 rounded-sm"
              :class="{ 'border-forge-500': step.status === 'in_progress' }"
            >
              <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                   :class="step.status === 'complete' ? 'bg-green-500 text-white' : step.status === 'in_progress' ? 'bg-amber-500 text-white' : 'bg-steel-700 text-text-secondary'">
                {{ step.sequence }}
              </div>

              <div class="flex-1">
                <div class="font-semibold text-white">
                  {{ step.work_area?.name ?? 'Unknown Work Area' }}
                </div>
                <div class="text-xs text-text-tertiary">
                  {{ step.work_area?.department?.name ?? 'No Department' }}
                </div>
              </div>

              <div class="text-sm text-text-secondary">
                <div v-if="step.estimated_hours">
                  Est: {{ step.estimated_hours }}h
                </div>
                <div v-if="step.actual_hours > 0">
                  Actual: {{ step.actual_hours }}h
                </div>
              </div>

              <div class="flex items-center gap-2">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(step.status)"
                >
                  {{ step.status }}
                </span>

                <button
                  v-if="step.status === 'in_progress'"
                  type="button"
                  class="btn-primary text-sm"
                  @click="openCompleteModal(step)"
                >
                  Complete
                </button>
              </div>
            </div>
          </div>

          <div
            v-else
            class="text-center text-sm text-text-secondary py-8"
          >
            No workflow steps defined for this ticket.
          </div>
        </div>

        <!-- Notes -->
        <div
          v-if="ticket.notes"
          class="card-industrial"
        >
          <h3 class="text-lg font-semibold text-white mb-4">
            Notes
          </h3>
          <p class="text-text-secondary whitespace-pre-wrap">
            {{ ticket.notes }}
          </p>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Details -->
        <div class="card-industrial">
          <h3 class="text-lg font-semibold text-white mb-4">
            Details
          </h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-text-tertiary">
                Priority
              </dt>
              <dd
                class="font-semibold"
                :class="getPriorityColor(ticket.priority)"
              >
                {{ getPriorityLabel(ticket.priority) }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-text-tertiary">
                Due Date
              </dt>
              <dd class="text-white">
                {{ formatDate(ticket.due_date) }}
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
            <div class="flex justify-between">
              <dt class="text-text-tertiary">
                Created By
              </dt>
              <dd class="text-white">
                {{ ticket.creator?.name ?? 'Unknown' }}
              </dd>
            </div>
          </dl>
        </div>

        <!-- Timestamps -->
        <div class="card-industrial">
          <h3 class="text-lg font-semibold text-white mb-4">
            Timeline
          </h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-text-tertiary">
                Created
              </dt>
              <dd class="text-white">
                {{ formatDateTime(ticket.created_at) }}
              </dd>
            </div>
            <div
              v-if="ticket.released_at"
              class="flex justify-between"
            >
              <dt class="text-text-tertiary">
                Released
              </dt>
              <dd class="text-white">
                {{ formatDateTime(ticket.released_at) }}
              </dd>
            </div>
            <div
              v-if="ticket.completed_at"
              class="flex justify-between"
            >
              <dt class="text-text-tertiary">
                Completed
              </dt>
              <dd class="text-white">
                {{ formatDateTime(ticket.completed_at) }}
              </dd>
            </div>
          </dl>
        </div>

        <!-- Associated Items -->
        <div
          v-if="ticket.assembly_instance || ticket.part_instance"
          class="card-industrial"
        >
          <h3 class="text-lg font-semibold text-white mb-4">
            Associated Items
          </h3>
          <div class="space-y-3 text-sm">
            <div v-if="ticket.assembly_instance">
              <div class="text-text-tertiary">
                Assembly
              </div>
              <div class="text-white font-semibold">
                {{ ticket.assembly_instance.assembly?.mark ?? 'N/A' }}
              </div>
            </div>
            <div v-if="ticket.part_instance">
              <div class="text-text-tertiary">
                Part
              </div>
              <div class="text-white font-semibold">
                {{ ticket.part_instance.part?.part_mark ?? 'N/A' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Complete Step Modal -->
    <div
      v-if="showCompleteModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
      @click.self="showCompleteModal = false"
    >
      <div class="bg-steel-900 border border-steel-700 rounded-sm p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-white mb-4">
          Complete Step: {{ selectedStep?.work_area?.name }}
        </h3>

        <form
          class="space-y-4"
          @submit.prevent="submitComplete"
        >
          <div>
            <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
              Actual Hours
            </label>
            <input
              v-model="completeForm.actual_hours"
              type="number"
              step="0.25"
              min="0"
              class="input-industrial w-full"
              required
            >
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
              Completed By
            </label>
            <select
              v-model="completeForm.completed_by"
              class="input-industrial w-full"
            >
              <option :value="null">
                Select Employee (Optional)
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
              v-model="completeForm.notes"
              rows="3"
              class="input-industrial w-full"
              placeholder="Optional notes about this step..."
            />
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              class="btn-secondary"
              @click="showCompleteModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="completeForm.processing"
            >
              Complete Step
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
