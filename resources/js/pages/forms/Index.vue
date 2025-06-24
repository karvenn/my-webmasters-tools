<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Plus, Trash2, Edit, Power, PowerOff } from 'lucide-vue-next';

interface Form {
    id: number;
    website_name: string;
    website_url: string;
    embed_token: string;
    submissions_count: number;
    new_submissions_count: number;
    created_at: string;
    is_active: boolean;
}

defineProps<{
    forms: Form[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'UAT Forms',
        href: '/forms',
    },
];

const deleteForm = (id: number) => {
    if (confirm('Are you sure you want to delete this form?')) {
        router.delete(route('forms.destroy', id));
    }
};

const toggleStatus = (id: number) => {
    router.post(route('forms.toggle-status', id));
};
</script>

<template>
    <Head title="UAT Forms" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">UAT Forms</h1>
                    <p class="text-muted-foreground">Manage your user acceptance testing forms</p>
                </div>
                <Button as-child>
                    <Link :href="route('forms.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Create Form
                    </Link>
                </Button>
            </div>

            <div v-if="forms.length === 0" class="flex flex-1 items-center justify-center">
                <Card class="w-full max-w-md">
                    <CardHeader class="text-center">
                        <CardTitle>No forms yet</CardTitle>
                        <CardDescription> Create your first UAT form to start collecting feedback </CardDescription>
                    </CardHeader>
                    <CardContent class="text-center">
                        <Button as-child>
                            <Link :href="route('forms.create')">
                                <Plus class="mr-2 h-4 w-4" />
                                Create Your First Form
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="form in forms" :key="form.id" :class="{ 'opacity-60': !form.is_active }">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <CardTitle class="flex items-center gap-2">
                                    {{ form.website_name }}
                                    <span v-if="!form.is_active" class="text-xs font-normal text-muted-foreground">(Inactive)</span>
                                </CardTitle>
                                <CardDescription>{{ form.website_url }}</CardDescription>
                            </div>
                            <Button
                                size="sm"
                                :variant="form.is_active ? 'default' : 'secondary'"
                                @click="toggleStatus(form.id)"
                                :title="form.is_active ? 'Deactivate form' : 'Activate form'"
                            >
                                <Power v-if="form.is_active" class="h-4 w-4" />
                                <PowerOff v-else class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-4 flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Total submissions</span>
                            <span class="font-medium">{{ form.submissions_count }}</span>
                        </div>
                        <div v-if="form.new_submissions_count > 0" class="mb-4 flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">New submissions</span>
                            <span class="font-medium text-orange-600">{{ form.new_submissions_count }}</span>
                        </div>
                        <div class="flex gap-2">
                            <Button as-child size="sm" class="flex-1">
                                <Link :href="route('forms.show', form.id)">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View
                                </Link>
                            </Button>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="route('forms.edit', form.id)">
                                    <Edit class="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button size="sm" variant="outline" @click="deleteForm(form.id)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
