<!-- resources/js/Pages/ShopTicket/Index.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tickets: Object,
    stats: Object,
    filters: Object,
    projects: Array,
});

const page = usePage();

const localFilters = ref({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
    priority: props.filters?.priority ?? '',
    project_id: props.filters?.project_id ?? '',
});

const applyFilters = () => {
    router.get(route('shop-tickets.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    localFilters.value = { search: '', status: '', priority: '', project_id: '' };
    router.get(route('shop-tickets.index'));
};

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-blue-600/20 text-blue-400',
        in_progress: 'bg-amber-600/20 text-amber-400',
        completed: 'bg-green-600/20 text-green-400',
        on_hold: 'bg-red-600/20 text-red-400',
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

const isOverdue = (ticket) => {
    if (!ticket.due_date || ticket.status === 'completed') return false;
    return new Date(ticket.due_date) < new Date();
};
</script>

<template>
  <AppLayout title="Shop Tickets">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Shop Tickets
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Digital work orders and stage-gate production tracking.
            </p>
          </div>
          <Link
            :href="route('shop-tickets.create')"
            class="btn-primary"
          >
            Create Ticket
          </Link>
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

    <!-- Stats cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 mb-8">
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Total Tickets
        </div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ stats?.total ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Open
        </div>
        <div class="mt-2 text-3xl font-bold text-blue-400">
          {{ stats?.open ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          In Progress
        </div>
        <div class="mt-2 text-3xl font-bold text-amber-400">
          {{ stats?.in_progress ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Completed
        </div>
        <div class="mt-2 text-3xl font-bold text-green-400">
          {{ stats?.completed ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Overdue
        </div>
        <div class="mt-2 text-3xl font-bold text-red-400">
          {{ stats?.overdue ?? 0 }}
        </div>
      </div>
    </section>

    <!-- Filters -->
    <section class="card-industrial mb-6">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label
            for="search"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Search
          </label>
          <input
            id="search"
            v-model="localFilters.search"
            type="text"
            placeholder="Ticket number..."
            class="input-industrial"
            @keyup.enter="applyFilters"
          >
        </div>
        <div>
          <label
            for="status"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Status
          </label>
          <select
            id="status"
            v-model="localFilters.status"
            class="input-industrial"
          >
            <option value="">
              All Statuses
            </option>
            <option value="open">
              Open
            </option>
            <option value="in_progress">
              In Progress
            </option>
            <option value="completed">
              Completed
            </option>
            <option value="on_hold">
              On Hold
            </option>
          </select>
        </div>
        <div>
          <label
            for="priority"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Priority
          </label>
          <select
            id="priority"
            v-model="localFilters.priority"
            class="input-industrial"
          >
            <option value="">
              All Priorities
            </option>
            <option value="5">
              Critical
            </option>
            <option value="4">
              High
            </option>
            <option value="3">
              Medium
            </option>
            <option value="2">
              Normal
            </option>
            <option value="1">
              Low
            </option>
          </select>
        </div>
        <div>
          <label
            for="project"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Project
          </label>
          <select
            id="project"
            v-model="localFilters.project_id"
            class="input-industrial"
          >
            <option value="">
              All Projects
            </option>
            <option
              v-for="project in projects"
              :key="project.id"
              :value="project.id"
            >
              {{ project.job_number }} - {{ project.name }}
            </option>
          </select>
        </div>
        <button
          type="button"
          class="btn-primary"
          @click="applyFilters"
        >
          Apply
        </button>
        <button
          type="button"
          class="btn-secondary"
          @click="clearFilters"
        >
          Clear
        </button>
      </div>
    </section>

    <!-- Tickets Table -->
    <section class="card-industrial">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">
                Ticket
              </th>
              <th class="py-3 text-left">
                Project
              </th>
              <th class="py-3 text-left">
                Status
              </th>
              <th class="py-3 text-left">
                Priority
              </th>
              <th class="py-3 text-left">
                Assigned To
              </th>
              <th class="py-3 text-left">
                Due Date
              </th>
              <th class="py-3 text-left">
                Progress
              </th>
              <th class="py-3 text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="ticket in tickets.data ?? []"
              :key="ticket.id"
              class="border-b border-steel-800/80 text-text-secondary"
              :class="{ 'bg-red-900/10': isOverdue(ticket) }"
            >
              <td class="py-3">
                <div class="text-white font-mono font-semibold">
                  {{ ticket.ticket_number }}
                </div>
              </td>
              <td class="py-3">
                {{ ticket.project?.job_number ?? 'N/A' }}
              </td>
              <td class="py-3">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(ticket.status)"
                >
                  {{ ticket.status?.replace('_', ' ') ?? 'unknown' }}
                </span>
              </td>
              <td class="py-3">
                <span :class="getPriorityColor(ticket.priority)">
                  {{ getPriorityLabel(ticket.priority) }}
                </span>
              </td>
              <td class="py-3">
                {{ ticket.assigned_employee?.name ?? 'Unassigned' }}
              </td>
              <td class="py-3">
                <span :class="{ 'text-red-400': isOverdue(ticket) }">
                  {{ formatDate(ticket.due_date) }}
                </span>
              </td>
              <td class="py-3">
                <div
                  v-if="ticket.steps?.length"
                  class="flex items-center gap-2"
                >
                  <div class="w-16 bg-steel-800 rounded-full h-2">
                    <div
                      class="bg-forge-500 h-2 rounded-full"
                      :style="{ width: `${(ticket.steps.filter(s => s.status === 'complete').length / ticket.steps.length) * 100}%` }"
                    />
                  </div>
                  <span class="text-xs text-text-tertiary">
                    {{ ticket.steps.filter(s => s.status === 'complete').length }}/{{ ticket.steps.length }}
                  </span>
                </div>
                <span
                  v-else
                  class="text-xs text-text-tertiary"
                >No steps</span>
              </td>
              <td class="py-3 text-right">
                <Link
                  :href="route('shop-tickets.show', ticket.id)"
                  class="btn-secondary text-sm"
                >
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="(tickets.data ?? []).length === 0">
              <td
                colspan="8"
                class="py-8 text-center text-sm text-text-secondary"
              >
                No shop tickets found. Create your first ticket to get started.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="tickets.links?.length > 3"
        class="mt-6 flex items-center justify-between border-t border-steel-700 pt-4"
      >
        <div class="text-sm text-text-secondary">
          Showing {{ tickets.from ?? 0 }} to {{ tickets.to ?? 0 }} of {{ tickets.total ?? 0 }} tickets
        </div>
        <div class="flex gap-2">
          <Link
            v-for="link in tickets.links"
            :key="link.label"
            :href="link.url ?? '#'"
            class="px-3 py-1 text-sm rounded"
            :class="link.active ? 'bg-forge-500 text-white' : 'bg-steel-800 text-text-secondary hover:bg-steel-700'"
            v-html="link.label"
          />
        </div>
      </div>
    </section>
  </AppLayout>
</template>
