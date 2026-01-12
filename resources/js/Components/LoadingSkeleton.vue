<script setup>
defineProps({
    type: {
        type: String,
        default: 'card', // 'card', 'table', 'list', 'metric'
    },
    count: {
        type: Number,
        default: 1,
    },
});
</script>

<template>
  <!-- Card Skeleton -->
  <div
    v-if="type === 'card'"
    class="space-y-4"
  >
    <div
      v-for="n in count"
      :key="n"
      class="card animate-pulse"
    >
      <div class="h-4 skeleton w-1/3 mb-4" />
      <div class="h-8 skeleton w-2/3 mb-4" />
      <div class="space-y-2">
        <div class="h-3 skeleton w-full" />
        <div class="h-3 skeleton w-5/6" />
        <div class="h-3 skeleton w-4/6" />
      </div>
    </div>
  </div>

  <!-- Metric Card Skeleton -->
  <div
    v-else-if="type === 'metric'"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
  >
    <div
      v-for="n in count"
      :key="n"
      class="metric-card animate-pulse"
    >
      <div class="h-4 skeleton w-24 mb-3" />
      <div class="h-10 skeleton w-20 mb-2" />
      <div class="h-3 skeleton w-32" />
    </div>
  </div>

  <!-- Table Skeleton -->
  <div
    v-else-if="type === 'table'"
    class="card-elevated animate-pulse"
  >
    <div class="overflow-x-auto">
      <table class="table-industrial">
        <thead>
          <tr>
            <th><div class="h-3 skeleton w-20" /></th>
            <th><div class="h-3 skeleton w-32" /></th>
            <th><div class="h-3 skeleton w-24" /></th>
            <th><div class="h-3 skeleton w-16" /></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="n in count"
            :key="n"
          >
            <td><div class="h-3 skeleton w-16" /></td>
            <td><div class="h-3 skeleton w-40" /></td>
            <td><div class="h-3 skeleton w-20" /></td>
            <td><div class="h-3 skeleton w-12" /></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- List Skeleton -->
  <div
    v-else-if="type === 'list'"
    class="space-y-3"
  >
    <div
      v-for="n in count"
      :key="n"
      class="flex items-start gap-4 p-4 bg-steel-900 rounded-industrial border border-steel-800 animate-pulse"
    >
      <div class="w-10 h-10 skeleton rounded-full flex-shrink-0" />
      <div class="flex-1 space-y-2">
        <div class="h-3 skeleton w-3/4" />
        <div class="h-3 skeleton w-1/2" />
      </div>
    </div>
  </div>
</template>
