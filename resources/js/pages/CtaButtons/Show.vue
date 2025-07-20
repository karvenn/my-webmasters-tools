<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface CtaButton {
    id: number;
    button_name: string;
    button_text: string;
    embed_token: string;
    is_active: boolean;
}

const props = defineProps<{
    button: CtaButton;
    embedCode?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'CTA Buttons',
        href: '/cta-buttons',
    },
    {
        title: 'Analytics',
        href: `/cta-buttons/${props.button.id}`,
    },
];
</script>

<template>
    <Head title="CTA Button Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Analytics: {{ button.button_name }}</h1>
                    <p class="text-muted-foreground">View performance metrics for your CTA button</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Coming Soon</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-muted-foreground mb-4">
                        Analytics functionality is being implemented. This will show:
                    </p>
                    
                    <ul class="list-disc list-inside space-y-1 text-muted-foreground">
                        <li>Total clicks and impressions</li>
                        <li>Click-through rates by URL pattern</li>
                        <li>Geographic distribution</li>
                        <li>Device and browser analytics</li>
                        <li>Performance over time</li>
                    </ul>

                    <div class="mt-6">
                        <Button variant="outline" as-child>
                            <Link :href="route('cta-buttons.index')">
                                ← Back to CTA Buttons
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>