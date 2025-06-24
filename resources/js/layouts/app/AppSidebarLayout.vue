<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { onMounted, onUnmounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Add UAT widget to all authenticated pages
onMounted(() => {
    const script = document.createElement('script');
    script.src = 'http://webmasters_tools.test/embed/widget.js?token=pzMvdfNxnwLPzLRHAkuyiGd2ter9pzjQ&v=' + Date.now();
    script.async = true;
    script.id = 'uat-widget-script-app';
    document.body.appendChild(script);
});

onUnmounted(() => {
    // Clean up the script and widget when component unmounts
    const script = document.getElementById('uat-widget-script-app');
    if (script) {
        script.remove();
    }
    // Remove the widget container if it exists
    const widgetContainer = document.querySelector('[id^="uat-feedback-widget-"]');
    if (widgetContainer) {
        widgetContainer.remove();
    }
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
