<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRightIcon, HomeIcon } from '@heroicons/vue/24/outline';

defineProps({
    items: {
        type: Array,
        required: true,
        // Expected format: [{ label: 'Dashboard', href: '/' }, { label: 'Projects' }]
    },
});
</script>

<template>
  <nav
    class="flex items-center space-x-2 text-sm mb-6"
    aria-label="Breadcrumb"
  >
    <Link
      href="/dashboard"
      class="flex items-center text-steel-400 hover:text-steel-300 transition-colors"
    >
      <HomeIcon class="w-4 h-4" />
      <span class="sr-only">Home</span>
    </Link>

    <template
      v-for="(item, index) in items"
      :key="index"
    >
      <ChevronRightIcon class="w-4 h-4 text-steel-600" />

      <Link
        v-if="item.href && index < items.length - 1"
        :href="item.href"
        class="text-steel-400 hover:text-steel-300 transition-colors uppercase tracking-wider font-mono"
      >
        {{ item.label }}
      </Link>

      <span
        v-else
        class="text-steel-300 font-semibold uppercase tracking-wider font-mono"
        aria-current="page"
      >
        {{ item.label }}
      </span>
    </template>
  </nav>
</template>
