<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Plus, Trash2, Edit, Power, PowerOff, MousePointer } from 'lucide-vue-next';

interface CtaButton {
    id: number;
    button_name: string;
    button_text: string;
    embed_token: string;
    rules_count: number;
    analytics_count: number;
    created_at: string;
    is_active: boolean;
}

defineProps<{
    buttons: CtaButton[];
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
];

const deleteButton = (id: number) => {
    if (confirm('Are you sure you want to delete this CTA button?')) {
        router.delete(route('cta-buttons.destroy', id));
    }
};

const toggleStatus = (id: number) => {
    router.post(route('cta-buttons.toggle-status', id), {}, {
        preserveScroll: true,
        preserveState: false,
    });
};
</script>

<template>
    <Head title="CTA Buttons" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">CTA Buttons</h1>
                    <p class="text-muted-foreground">Manage your call-to-action buttons with URL pattern matching</p>
                </div>
                <Button as-child>
                    <Link :href="route('cta-buttons.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Create CTA Button
                    </Link>
                </Button>
            </div>

            <div v-if="buttons.length === 0" class="flex flex-1 items-center justify-center">
                <Card class="w-full max-w-md">
                    <CardHeader class="text-center">
                        <CardTitle>No CTA buttons yet</CardTitle>
                        <CardDescription>Create your first CTA button to display contextual call-to-action buttons on your website</CardDescription>
                    </CardHeader>
                    <CardContent class="text-center">
                        <Button as-child>
                            <Link :href="route('cta-buttons.create')">
                                <Plus class="mr-2 h-4 w-4" />
                                Create Your First CTA Button
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="button in buttons" :key="button.id" :class="{ 'opacity-60': !button.is_active }">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <CardTitle class="flex items-center gap-2">
                                    <MousePointer class="h-4 w-4" />
                                    {{ button.button_name }}
                                    <span v-if="!button.is_active" class="text-xs font-normal text-muted-foreground">(Inactive)</span>
                                </CardTitle>
                                <CardDescription>{{ button.button_text }}</CardDescription>
                            </div>
                            <Button
                                size="sm"
                                :variant="button.is_active ? 'default' : 'secondary'"
                                @click="toggleStatus(button.id)"
                                :title="button.is_active ? 'Deactivate button' : 'Activate button'"
                            >
                                <Power v-if="button.is_active" class="h-4 w-4" />
                                <PowerOff v-else class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold">{{ button.rules_count || 0 }}</div>
                                <div class="text-xs text-muted-foreground">Rules</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ button.analytics_count || 0 }}</div>
                                <div class="text-xs text-muted-foreground">Clicks</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ new Date(button.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</div>
                                <div class="text-xs text-muted-foreground">Created</div>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Button size="sm" variant="outline" as-child class="flex-1">
                                <Link 
                                    :href="route('cta-buttons.edit', button.id)" 
                                    :data="{ timestamp: Date.now() }"
                                    preserve-scroll
                                    :preserve-state="false"
                                >
                                    <Edit class="mr-2 h-3 w-3" />
                                    Edit
                                </Link>
                            </Button>
                            <Button size="sm" variant="outline" as-child class="flex-1">
                                <Link :href="route('cta-buttons.show', button.id)">
                                    <Eye class="mr-2 h-3 w-3" />
                                    Analytics
                                </Link>
                            </Button>
                            <Button size="sm" variant="outline" @click="deleteButton(button.id)" class="px-2">
                                <Trash2 class="h-3 w-3" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>