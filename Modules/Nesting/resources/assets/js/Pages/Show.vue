<!-- resources/js/Pages/Nesting/Show.vue -->
<script setup>
import { Link, router } from '@inertiajs/vue3';
import { CheckCircleIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    nesting: Object,
});

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};

const getPartColor = (index) => {
    const colors = [
        'bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500',
        'bg-pink-500', 'bg-indigo-500', 'bg-cyan-500', 'bg-teal-500'
    ];
    return colors[index % colors.length];
};

const approveNesting = () => {
    if (confirm('Approve this nesting? This will assign stock items to the nesting.')) {
        router.post(route('nesting.approve', props.nesting.id));
    }
};

const confirmNesting = () => {
    if (confirm('Confirm this nesting? This will mark stock as used and create remnants.')) {
        router.post(route('nesting.confirm', props.nesting.id));
    }
};

const getStatusBadge = (status) => {
    const badges = {
        'draft': 'bg-gray-500',
        'approved': 'bg-blue-500',
        'verified': 'bg-yellow-500',
        'confirmed': 'bg-green-500',
    };
    return badges[status] || 'bg-gray-500';
};
</script>

<template>
  <AppLayout :title="`Nesting: ${nesting.nesting_number}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            :href="route('nesting.index')"
            class="text-steel-400 hover:text-white mr-4"
          >
            &larr; Back
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-white leading-tight">
              Nesting: {{ nesting.nesting_number }}
            </h2>
            <p class="text-sm text-steel-400">
              Project: {{ nesting.project.name }} • Type: {{ nesting.material_type }} {{ nesting.grade }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <!-- Status Badge -->
          <div
            class="px-3 py-1 rounded-full text-xs uppercase font-bold text-white"
            :class="getStatusBadge(nesting.status)"
          >
            {{ nesting.status }}
          </div>

          <!-- Workflow Actions -->
          <button
            v-if="nesting.status === 'draft'"
            class="btn-primary flex items-center gap-2"
            @click="approveNesting"
          >
            <ShieldCheckIcon class="h-5 w-5" />
            Approve Nesting
          </button>

          <button
            v-if="nesting.status === 'approved'"
            class="btn-success flex items-center gap-2"
            @click="confirmNesting"
          >
            <CheckCircleIcon class="h-5 w-5" />
            Confirm Nesting
          </button>

          <div class="text-xs uppercase font-bold text-steel-500">
            Efficiency: <span class="text-green-400 text-lg ml-1">{{ nesting.efficiency_percent }}%</span>
          </div>
        </div>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Bars Visualization -->
      <div
        v-for="bar in nesting.bars"
        :key="bar.id"
        class="card-industrial"
      >
        <div class="flex justify-between items-end mb-4">
          <div class="text-sm">
            <span class="font-bold text-white uppercase tracking-widest">Bar #{{ bar.bar_number }}</span>
            <span class="ml-4 text-steel-500 font-mono">{{ formatLength(bar.length) }}</span>
            <span
              v-if="bar.stockItem"
              class="ml-4 text-xs text-weld-400 font-mono border border-weld-900 px-1 rounded"
            >
              Stock: {{ bar.stockItem.stock_id }}
            </span>
          </div>
          <div class="text-right text-[10px] uppercase text-steel-500 font-bold">
            Waste: {{ formatLength(bar.remnant_length) }} ({{ Math.round((bar.remnant_length / bar.length) * 100) }}%)
          </div>
        </div>

        <!-- Linear Bar Visualization -->
        <div class="h-12 w-full bg-steel-900 rounded-sm overflow-hidden flex border border-steel-700 relative group">
          <div 
            v-for="(nPart, pIndex) in bar.nesting_parts" 
            :key="nPart.id"
            class="h-full border-r border-steel-900/50 flex items-center justify-center text-[10px] font-bold text-white relative group/part transition-all hover:brightness-110"
            :class="getPartColor(pIndex)"
            :style="{ width: `${(nPart.cut_length / bar.length) * 100}%` }"
          >
            <span class="truncate px-1">{{ nPart.part_instance?.part?.part_mark }}</span>
            
            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover/part:block z-10 w-48 pointer-events-none">
              <div class="bg-steel-800 border border-steel-700 p-2 rounded shadow-2xl text-xs">
                <div class="font-bold text-forge-400">
                  {{ nPart.part_instance?.part?.part_mark }}
                </div>
                <div class="text-white mt-1">
                  Length: {{ formatLength(nPart.cut_length) }}
                </div>
                <div class="text-steel-400 mt-1">
                  Assembly: {{ nPart.part_instance?.part?.assembly?.mark }}
                </div>
              </div>
            </div>
          </div>
          <!-- Remnant Area -->
          <div 
            v-if="bar.remnant_length > 0"
            class="h-full bg-steel-800 flex items-center justify-center text-[10px] font-mono text-steel-500 italic"
            :style="{ width: `${(bar.remnant_length / bar.length) * 100}%` }"
          >
            {{ formatLength(bar.remnant_length) }}
          </div>
        </div>

        <!-- Parts Detail List -->
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
          <div
            v-for="nPart in bar.nesting_parts"
            :key="nPart.id"
            class="text-[10px] border border-steel-800 p-1 rounded font-mono flex flex-col justify-center"
          >
            <div class="text-white font-bold">
              {{ nPart.part_instance?.part?.part_mark }}
            </div>
            <div class="text-steel-500">
              {{ formatLength(nPart.cut_length) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
