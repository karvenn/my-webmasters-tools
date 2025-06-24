<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'UAT Forms',
        href: '/forms',
    },
    {
        title: 'Create Form',
        href: '/forms/create',
    },
];

const form = useForm({
    website_name: '',
    website_url: '',
});

const submit = () => {
    form.post(route('forms.store'));
};
</script>

<template>
    <Head title="Create UAT Form" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div>
                <h1 class="text-2xl font-bold">Create UAT Form</h1>
                <p class="text-muted-foreground">Set up a new form to collect user feedback</p>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Website Information</CardTitle>
                    <CardDescription> Enter the details of the website where you'll embed the UAT form </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="website_name">Website Name</Label>
                            <Input id="website_name" v-model="form.website_name" type="text" placeholder="My Awesome Website" required />
                            <InputError :message="form.errors.website_name" class="mt-2" />
                        </div>

                        <div>
                            <Label for="website_url">Website URL</Label>
                            <Input id="website_url" v-model="form.website_url" type="url" placeholder="https://example.com" required />
                            <InputError :message="form.errors.website_url" class="mt-2" />
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" :disabled="form.processing"> Create Form </Button>
                            <Button type="button" variant="outline" @click="$inertia.visit(route('forms.index'))"> Cancel </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
