import { ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme(value: Appearance) {
    // Force light mode - remove dark class regardless of value
    if (typeof window !== 'undefined') {
        document.documentElement.classList.remove('dark');
    }
}

export function initializeTheme() {
    // Force light mode on initialization
    updateTheme('light');
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    function updateAppearance(value: Appearance) {
        // Always use light mode regardless of the value passed
        appearance.value = 'light';
        updateTheme('light');
    }

    return {
        appearance,
        updateAppearance,
    };
}