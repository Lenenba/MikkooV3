<template>
    <transition name="fade">
        <div v-if="visible" role="alert" tabindex="-1" :class="[
            baseClasses,
            typeClasses[type]
        ]">
            <div class="flex items-start">
                <!-- Icon -->
                <component :is="icons[type]" class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <!-- Message text -->
                <div class="ml-2 flex-1 text-sm font-medium">
                    {{ message }}
                </div>
                <!-- Dismiss button -->
                <button @click="visible = false"
                    class="ml-4 inline-flex p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-1"
                    :class="buttonClasses[type]" aria-label="Dismiss">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { CheckCircle, AlertTriangle, XCircle } from 'lucide-vue-next';

interface Props {
    message: string;
    type: 'success' | 'error' | 'warning';
}

// English comment: define props
const props = defineProps<Props>();

// English comment: control visibility for auto-dismiss and manual dismiss
const visible = ref(true);
let timer: ReturnType<typeof setTimeout>;

// English comment: start auto-dismiss timer
onMounted(() => {
    timer = setTimeout(() => {
        visible.value = false;
    }, 3000);
});

// English comment: cleanup if needed
onUnmounted(() => {
    clearTimeout(timer);
});

// English comment: base styling for the container
const baseClasses =
    'fixed top-4 right-4 z-50 w-full max-w-sm border rounded-lg p-4 shadow-lg transition-transform transform';

// English comment: variant styling based on type
const typeClasses: Record<Props['type'], string> = {
    success:
        'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/10 dark:border-green-800 dark:text-green-400',
    error:
        'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/10 dark:border-red-800 dark:text-red-400',
    warning:
        'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/10 dark:border-yellow-800 dark:text-yellow-400',
};

// English comment: button colors for hover/focus per type
const buttonClasses: Record<Props['type'], string> = {
    success:
        'text-green-500 hover:bg-green-100 focus:ring-green-500 dark:hover:bg-green-800/50 dark:text-green-600',
    error:
        'text-red-500 hover:bg-red-100 focus:ring-red-500 dark:hover:bg-red-800/50 dark:text-red-600',
    warning:
        'text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-500 dark:hover:bg-yellow-800/50 dark:text-yellow-600',
};

// English comment: choose icon component based on type
const icons: Record<Props['type'], any> = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertTriangle,
};
</script>

<style scoped>
/* Fade transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
