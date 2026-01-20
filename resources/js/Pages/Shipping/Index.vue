<!-- resources/js/Pages/Shipping/Index.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    loads: Object,
    stats: Object,
    filters: Object,
    projects: Array,
});

const page = usePage();

const localFilters = ref({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
    project_id: props.filters?.project_id ?? '',
});

const applyFilters = () => {
    router.get(route('shipping.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    localFilters.value = { search: '', status: '', project_id: '' };
    router.get(route('shipping.index'));
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

const formatWeight = (lbs) => {
    if (!lbs) return '0 lbs';
    return `${lbs.toLocaleString()} lbs`;
};
</script>

<template>
  <AppLayout title="Shipping & Loads">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Shipping & Loads
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Manage shipping loads, track deliveries, and generate BOLs.
            </p>
          </div>
          <Link
            :href="route('shipping.create')"
            class="btn-primary"
          >
            Create Load
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
          Total Loads
        </div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ stats?.total ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Draft
        </div>
        <div class="mt-2 text-3xl font-bold text-steel-300">
          {{ stats?.draft ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Planned
        </div>
        <div class="mt-2 text-3xl font-bold text-blue-400">
          {{ stats?.planned ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          In Transit
        </div>
        <div class="mt-2 text-3xl font-bold text-amber-400">
          {{ stats?.in_transit ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Delivered
        </div>
        <div class="mt-2 text-3xl font-bold text-green-400">
          {{ stats?.delivered ?? 0 }}
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
            placeholder="Load number, BOL..."
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
            <option value="draft">
              Draft
            </option>
            <option value="planned">
              Planned
            </option>
            <option value="in_transit">
              In Transit
            </option>
            <option value="delivered">
              Delivered
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

    <!-- Loads Table -->
    <section class="card-industrial">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">
                Load Number
              </th>
              <th class="py-3 text-left">
                Project
              </th>
              <th class="py-3 text-left">
                Status
              </th>
              <th class="py-3 text-left">
                Ship Date
              </th>
              <th class="py-3 text-left">
                Carrier
              </th>
              <th class="py-3 text-left">
                Total Weight
              </th>
              <th class="py-3 text-left">
                Total Pieces
              </th>
              <th class="py-3 text-left">
                BOL #
              </th>
              <th class="py-3 text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="load in loads.data ?? []"
              :key="load.id"
              class="border-b border-steel-800/80 text-text-secondary"
            >
              <td class="py-3">
                <div class="text-white font-mono font-semibold">
                  {{ load.load_number }}
                </div>
              </td>
              <td class="py-3">
                <div class="text-white">
                  {{ load.project?.job_number ?? 'N/A' }}
                </div>
                <div class="text-xs text-text-tertiary">
                  {{ load.project?.name ?? '' }}
                </div>
              </td>
              <td class="py-3">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(load.status)"
                >
                  {{ load.status?.replace('_', ' ') ?? 'unknown' }}
                </span>
              </td>
              <td class="py-3">
                {{ formatDate(load.ship_date) }}
              </td>
              <td class="py-3">
                {{ load.carrier ?? '-' }}
              </td>
              <td class="py-3">
                {{ formatWeight(load.total_weight_lbs) }}
              </td>
              <td class="py-3">
                {{ load.total_pieces }}
              </td>
              <td class="py-3">
                <span class="font-mono">{{ load.bol_number ?? '-' }}</span>
              </td>
              <td class="py-3 text-right">
                <Link
                  :href="route('shipping.show', load.id)"
                  class="btn-secondary text-sm"
                >
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="(loads.data ?? []).length === 0">
              <td
                colspan="9"
                class="py-8 text-center text-sm text-text-secondary"
              >
                No loads found. Create your first load to get started.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="loads.links?.length > 3"
        class="mt-6 flex items-center justify-between border-t border-steel-700 pt-4"
      >
        <div class="text-sm text-text-secondary">
          Showing {{ loads.from ?? 0 }} to {{ loads.to ?? 0 }} of {{ loads.total ?? 0 }} loads
        </div>
        <div class="flex gap-2">
          <Link
            v-for="link in loads.links"
            :key="link.label"
            :href="link.url ?? '#'"
            class="px-3 py-1 text-sm rounded"
            :class="link.active ? 'bg-forge-500 text-white' : 'bg-steel-800 text-text-secondary hover:bg-steel-700'"
          >
            <!-- eslint-disable-next-line vue/no-v-html -->
            <span v-html="link.label" />
          </Link>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
