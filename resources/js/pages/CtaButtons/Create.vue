<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
        title: 'Create',
        href: '/cta-buttons/create',
    },
];

const form = useForm({
    button_name: '',
    allowed_domains: [''],
    button_text: 'Learn More',
    button_position: 'bottom-right',
    button_size: 'medium',
    button_color: '#3b82f6',
    button_text_color: '#ffffff',
});

const addDomain = () => {
    form.allowed_domains.push('');
};

const removeDomain = (index: number) => {
    form.allowed_domains.splice(index, 1);
};

const submit = () => {
    form.post(route('cta-buttons.store'));
};
</script>

<template>
    <Head title="Create CTA Button" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Create CTA Button</h1>
                    <p class="text-muted-foreground">Set up a new call-to-action button with URL pattern matching</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Button Configuration</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Button Name -->
                            <div>
                                <Label for="button_name">Button Name</Label>
                                <Input
                                    id="button_name"
                                    v-model="form.button_name"
                                    type="text"
                                    required
                                    placeholder="e.g., Product Preview Button"
                                />
                                <p v-if="form.errors.button_name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.button_name }}
                                </p>
                            </div>

                            <!-- Button Text -->
                            <div>
                                <Label for="button_text">Button Text</Label>
                                <Input
                                    id="button_text"
                                    v-model="form.button_text"
                                    type="text"
                                    required
                                    placeholder="Learn More"
                                />
                                <p v-if="form.errors.button_text" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.button_text }}
                                </p>
                            </div>

                            <!-- Button Position -->
                            <div>
                                <Label for="button_position">Button Position</Label>
                                <select
                                    id="button_position"
                                    v-model="form.button_position"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="bottom-right">Bottom Right</option>
                                    <option value="bottom-left">Bottom Left</option>
                                    <option value="top-right">Top Right</option>
                                    <option value="top-left">Top Left</option>
                                </select>
                                <p v-if="form.errors.button_position" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.button_position }}
                                </p>
                            </div>

                            <!-- Button Size -->
                            <div>
                                <Label for="button_size">Button Size</Label>
                                <select
                                    id="button_size"
                                    v-model="form.button_size"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="small">Small</option>
                                    <option value="medium">Medium</option>
                                    <option value="large">Large</option>
                                </select>
                                <p v-if="form.errors.button_size" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.button_size }}
                                </p>
                            </div>

                            <!-- Button Color -->
                            <div>
                                <Label for="button_color">Button Color</Label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        id="button_color"
                                        v-model="form.button_color"
                                        type="color"
                                        class="h-10 w-20 rounded border"
                                    />
                                    <Input
                                        v-model="form.button_color"
                                        type="text"
                                        placeholder="#3b82f6"
                                        class="flex-1"
                                    />
                                </div>
                                <p v-if="form.errors.button_color" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.button_color }}
                                </p>
                            </div>

                            <!-- Button Text Color -->
                            <div>
                                <Label for="button_text_color">Button Text Color</Label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        id="button_text_color"
                                        v-model="form.button_text_color"
                                        type="color"
                                        class="h-10 w-20 rounded border"
                                    />
                                    <Input
                                        v-model="form.button_text_color"
                                        type="text"
                                        placeholder="#ffffff"
                                        class="flex-1"
                                    />
                                </div>
                                <p v-if="form.errors.button_text_color" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.button_text_color }}
                                </p>
                            </div>
                        </div>

                        <!-- Allowed Domains -->
                        <div>
                            <Label>Allowed Domains</Label>
                            <div class="space-y-2">
                                <div v-for="(domain, index) in form.allowed_domains" :key="index" class="flex items-center space-x-2">
                                    <Input
                                        v-model="form.allowed_domains[index]"
                                        type="text"
                                        required
                                        placeholder="example.com"
                                        class="flex-1"
                                    />
                                    <Button
                                        v-if="form.allowed_domains.length > 1"
                                        @click="removeDomain(index)"
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                    >
                                        Remove
                                    </Button>
                                </div>
                                <Button
                                    @click="addDomain"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                >
                                    + Add Domain
                                </Button>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Use *.example.com for wildcard subdomains
                            </p>
                            <p v-if="form.errors.allowed_domains" class="mt-1 text-sm text-red-600">
                                {{ form.errors.allowed_domains }}
                            </p>
                        </div>

                        <!-- Preview -->
                        <div class="p-6 bg-muted rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Preview</h3>
                            <div class="relative h-32 bg-background rounded border">
                                <button
                                    :style="{
                                        position: 'absolute',
                                        [form.button_position.includes('bottom') ? 'bottom' : 'top']: '20px',
                                        [form.button_position.includes('right') ? 'right' : 'left']: '20px',
                                        backgroundColor: form.button_color,
                                        color: form.button_text_color,
                                        padding: form.button_size === 'small' ? '8px 16px' : form.button_size === 'large' ? '16px 32px' : '12px 24px',
                                        fontSize: form.button_size === 'small' ? '14px' : form.button_size === 'large' ? '18px' : '16px',
                                        borderRadius: '30px',
                                        fontWeight: '500',
                                        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
                                    }"
                                    class="transition-all hover:opacity-90 border-0"
                                    type="button"
                                >
                                    {{ form.button_text || 'Button Text' }}
                                </button>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <Button variant="outline" as-child>
                                <Link :href="route('cta-buttons.index')">
                                    Cancel
                                </Link>
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                            >
                                Create CTA Button
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>