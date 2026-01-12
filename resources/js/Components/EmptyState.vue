<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    icon: Object, // Heroicon component
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    actionText: {
        type: String,
        default: '',
    },
    actionHref: {
        type: String,
        default: '',
    },
    actionClick: {
        type: Function,
        default: null,
    },
});
</script>

<template>
  <div class="flex flex-col items-center justify-center py-12 px-4">
    <div
      v-if="icon"
      class="w-20 h-20 rounded-full bg-steel-800 border border-steel-700 flex items-center justify-center mb-6"
    >
      <component
        :is="icon"
        class="w-10 h-10 text-steel-500"
      />
    </div>

    <h3 class="text-xl font-bold text-steel-300 uppercase tracking-wider mb-2">
      {{ title }}
    </h3>

    <p
      v-if="description"
      class="text-sm text-steel-500 text-center max-w-md mb-6"
    >
      {{ description }}
    </p>

    <Link
      v-if="actionText && actionHref"
      :href="actionHref"
      class="btn-primary"
    >
      {{ actionText }}
    </Link>

    <button
      v-else-if="actionText && actionClick"
      class="btn-primary"
      @click="actionClick"
    >
      {{ actionText }}
    </button>
  </div>
</template>
