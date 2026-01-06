<!-- resources/js/Components/ManualInventoryRecorder.vue -->
<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';

const emit = defineEmits(['apply']);

const fields = ['shape', 'size', 'length', 'area', 'grade'];
const STORAGE_KEY = 'manual_inventory_last_values';
const KEEP_KEY = 'manual_inventory_keep_last';

const entryForm = reactive({
    shape: '',
    size: '',
    length: '',
    area: '',
    grade: '',
});

const entries = ref([]);
const keepLast = ref(false);
const lastValues = ref({ ...entryForm });
const statusMessage = ref('');
const listeningField = ref('');
const recognition = ref(null);
const recognitionSupported = ref(false);

const lastValueHelp = computed(() => {
    if (!keepLast.value) {
        return 'Enable "Reuse last values" to keep repeating the previous entry for faster capture.';
    }

    const activeValues = fields
        .filter((field) => lastValues.value[field])
        .map((field) => `${field}: ${lastValues.value[field]}`);

    if (!activeValues.length) {
        return 'Last value reuse is enabled. Capture your first entry to seed the defaults.';
    }

    return `Last used → ${activeValues.join(' • ')}`;
});

const persistLastValues = () => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(lastValues.value));
        localStorage.setItem(KEEP_KEY, keepLast.value ? 'true' : 'false');
    } catch (error) {
        // Gracefully degrade if persistence is blocked
        console.warn('Unable to persist manual inventory defaults', error);
    }
};

const hydratePreferences = () => {
    try {
        const storedKeep = localStorage.getItem(KEEP_KEY);
        keepLast.value = storedKeep === 'true';

        const storedLast = localStorage.getItem(STORAGE_KEY);
        if (storedLast) {
            lastValues.value = { ...lastValues.value, ...JSON.parse(storedLast) };
        }
    } catch (error) {
        keepLast.value = false;
        lastValues.value = { ...entryForm };
    }
};

const startListening = (field) => {
    if (!recognition.value) {
        statusMessage.value = 'Speech recognition is not available in this browser.';
        return;
    }

    if (listeningField.value && recognition.value.stop) {
        recognition.value.stop();
    }

    listeningField.value = field;
    statusMessage.value = `Listening for ${field}...`;
    recognition.value.start();
};

const stopListening = () => {
    if (recognition.value && recognition.value.stop) {
        recognition.value.stop();
    }
};

const addEntry = () => {
    const preparedEntry = fields.reduce((accumulator, field) => {
        const explicitValue = entryForm[field]?.trim();
        const lastValue = keepLast.value ? lastValues.value[field] : '';
        return { ...accumulator, [field]: explicitValue || lastValue || '' };
    }, {});

    const missing = fields.filter((field) => !preparedEntry[field]);
    if (missing.length) {
        statusMessage.value = `Please provide: ${missing.join(', ')}.`;
        return;
    }

    const newEntry = {
        ...preparedEntry,
        id: crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}`,
        capturedAt: new Date(),
    };

    entries.value = [newEntry, ...entries.value].slice(0, 10);
    lastValues.value = { ...preparedEntry };
    statusMessage.value = 'Entry captured. You can apply it to the form below.';

    fields.forEach((field) => {
        entryForm[field] = keepLast.value ? preparedEntry[field] : '';
    });

    persistLastValues();
};

const applyEntry = (entry) => {
    emit('apply', entry);
    statusMessage.value = 'Manual entry applied to the form.';
};

onMounted(() => {
    hydratePreferences();

    if (typeof window === 'undefined') {
        return;
    }

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
        recognitionSupported.value = true;
        const recognizer = new SpeechRecognition();
        recognizer.continuous = false;
        recognizer.interimResults = false;
        recognizer.lang = 'en-US';

        recognizer.onresult = (event) => {
            const transcript = event.results[0][0].transcript.trim();
            if (listeningField.value) {
                entryForm[listeningField.value] = transcript;
                statusMessage.value = `${listeningField.value} captured: ${transcript}`;
            }
        };

        recognizer.onerror = (event) => {
            statusMessage.value = `Microphone error: ${event.error}`;
        };

        recognizer.onend = () => {
            listeningField.value = '';
        };

        recognition.value = recognizer;
    } else {
        statusMessage.value = 'Speech recognition is not supported in this browser.';
    }
});

onUnmounted(() => {
    stopListening();
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-xl font-semibold text-white">
          Manual Intake (Voice Assisted)
        </h2>
        <p class="text-text-secondary text-sm">
          Capture stock specs quickly using speech recognition, then apply them to the form below.
        </p>
      </div>
      <label class="inline-flex items-center space-x-3 cursor-pointer select-none">
        <input
          v-model="keepLast"
          type="checkbox"
          class="form-checkbox h-5 w-5 text-weld-400 focus:ring-weld-300"
          @change="persistLastValues"
        >
        <span class="text-sm text-text-secondary">
          Reuse last values for missing fields
        </span>
      </label>
    </div>

    <p class="text-xs text-text-tertiary">
      {{ lastValueHelp }}
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="field in fields"
        :key="field"
      >
        <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
          {{ field }}
        </label>
        <div class="relative flex items-center">
          <input
            v-model="entryForm[field]"
            :placeholder="keepLast ? `Say or reuse last ${field}` : `Tap mic or type ${field}`"
            class="input-industrial w-full pr-12"
            :aria-label="`Manual entry ${field}`"
          >
          <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-weld-300 hover:text-weld-100"
            :disabled="!recognitionSupported || listeningField === field"
            :title="recognitionSupported ? `Capture ${field} with your microphone` : 'Speech recognition unavailable'"
            @click="startListening(field)"
          >
            <svg
              v-if="listeningField !== field"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 1a3 3 0 00-3 3v6a3 3 0 006 0V4a3 3 0 00-3-3zM19 10a7 7 0 11-14 0 7 7 0 0114 0zm-7 7v6m-4 0h8"
              />
            </svg>
            <svg
              v-else
              class="w-5 h-5 animate-pulse"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 19l.01 0M12 5v7m0 0a2 2 0 004 0V9m-4 3a2 2 0 11-4 0V9"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-3 md:space-y-0">
      <button
        type="button"
        class="btn-primary md:w-auto"
        @click="addEntry"
      >
        Save Manual Entry
      </button>
      <span class="text-sm text-text-secondary">
        {{ statusMessage }}
      </span>
    </div>

    <div
      v-if="entries.length"
      class="border border-steel-700 rounded-lg divide-y divide-steel-800"
    >
      <div class="px-4 py-3 flex items-center justify-between bg-steel-900 rounded-t-lg">
        <div>
          <p class="text-sm font-semibold text-white">
            Recent manual captures
          </p>
          <p class="text-xs text-text-tertiary">
            Tap Apply to copy values into the inventory form.
          </p>
        </div>
      </div>
      <div
        v-for="entry in entries"
        :key="entry.id"
        class="px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3"
      >
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-2 text-sm text-text-secondary">
          <span><span class="text-text-tertiary">Shape:</span> {{ entry.shape }}</span>
          <span><span class="text-text-tertiary">Size:</span> {{ entry.size }}</span>
          <span><span class="text-text-tertiary">Length:</span> {{ entry.length }}</span>
          <span><span class="text-text-tertiary">Area:</span> {{ entry.area }}</span>
          <span><span class="text-text-tertiary">Grade:</span> {{ entry.grade }}</span>
          <span class="text-text-tertiary text-xs">
            {{ new Date(entry.capturedAt).toLocaleTimeString() }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="btn-secondary"
            @click="applyEntry(entry)"
          >
            Apply to form
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
