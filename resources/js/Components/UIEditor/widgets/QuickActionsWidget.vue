<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    PlusIcon,
    ArrowUpTrayIcon,
    DocumentTextIcon,
    CubeIcon,
    TruckIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
    data: {
        type: Object,
        default: () => ({}),
    },
    config: {
        type: Object,
        default: () => ({}),
    },
});

// Icon mapping
const iconMap = {
    plus: PlusIcon,
    upload: ArrowUpTrayIcon,
    'document-report': DocumentTextIcon,
    cube: CubeIcon,
    truck: TruckIcon,
    wrench: WrenchScrewdriverIcon,
};

const actions = computed(() => {
    return props.data?.actions || [
        { label: 'New Project', route: 'projects.create', icon: 'plus' },
        { label: 'Import BOM', route: null, icon: 'upload' },
        { label: 'View Reports', route: 'reports', icon: 'document-report' },
    ];
});

const getIcon = (iconName) => {
    return iconMap[iconName] || CubeIcon;
};

const getRoute = (routeName) => {
    try {
        return routeName ? route(routeName) : '#';
    } catch (e) {
        return '#';
    }
};
</script>

<template>
  <div class="h-full">
    <div class="grid grid-cols-2 gap-2 h-full">
      <component
        :is="action.route ? Link : 'button'"
        v-for="action in actions"
        :key="action.label"
        :href="action.route ? getRoute(action.route) : undefined"
        class="flex flex-col items-center justify-center gap-2 p-4 bg-steel-800 rounded-industrial border border-steel-700 hover:border-forge-500 hover:bg-steel-700 transition-all text-center"
      >
        <component
          :is="getIcon(action.icon)"
          class="w-6 h-6 text-forge-400"
        />
        <span class="text-sm text-steel-300">{{ action.label }}</span>
      </component>
    </div>
  </div>
</template>
