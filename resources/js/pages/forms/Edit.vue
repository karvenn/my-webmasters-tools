<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Plus, X } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    form: {
        id: number;
        website_name: string;
        website_url: string;
        is_active: boolean;
        button_color: string;
        button_text_color: string;
        button_size: 'small' | 'medium' | 'large';
        button_position: 'bottom-right' | 'bottom-left' | 'top-right' | 'top-left';
        button_text: string;
        allowed_domains: string[];
    };
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
    {
        title: props.form.website_name,
        href: `/forms/${props.form.id}`,
    },
    {
        title: 'Edit',
        href: `/forms/${props.form.id}/edit`,
    },
];

const form = useForm({
    website_name: props.form.website_name,
    website_url: props.form.website_url,
    button_color: props.form.button_color || '#3b82f6',
    button_text_color: props.form.button_text_color || '#ffffff',
    button_size: props.form.button_size || 'medium',
    button_position: props.form.button_position || 'bottom-right',
    button_text: props.form.button_text || 'Report Issue',
    allowed_domains: props.form.allowed_domains || [],
});

const newDomain = ref('');

const addDomain = () => {
    if (newDomain.value && !form.allowed_domains.includes(newDomain.value)) {
        form.allowed_domains.push(newDomain.value);
        newDomain.value = '';
    }
};

const removeDomain = (index: number) => {
    form.allowed_domains.splice(index, 1);
};

const submit = () => {
    form.put(route('forms.update', props.form.id));
};

// Button preview styles
const buttonPreviewStyle = computed(() => {
    const sizes = {
        small: { padding: '8px 16px', fontSize: '14px' },
        medium: { padding: '12px 24px', fontSize: '16px' },
        large: { padding: '16px 32px', fontSize: '18px' },
    };

    return {
        backgroundColor: form.button_color,
        color: form.button_text_color,
        ...sizes[form.button_size],
        borderRadius: '30px',
        border: 'none',
        fontWeight: '500',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
        cursor: 'pointer',
    };
});

// Extract domain from URL when it changes
watch(() => form.website_url, (newUrl) => {
    try {
        const url = new URL(newUrl);
        const domain = url.hostname;
        if (domain && form.allowed_domains.length === 0) {
            form.allowed_domains.push(domain);
        }
    } catch (e) {
        // Invalid URL, ignore
    }
});
</script>

<template>
    <Head title="Edit UAT Form" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Edit UAT Form</h1>
                    <p class="text-muted-foreground">Update form settings and customization options</p>
                </div>
                <Button variant="outline" @click="$inertia.visit(route('forms.show', form.id))">
                    <ChevronLeft class="mr-2 h-4 w-4" />
                    Back to Form
                </Button>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 max-w-4xl">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription>Update the website details for this form</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
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
                    </CardContent>
                </Card>

                <!-- Widget Customization -->
                <Card>
                    <CardHeader>
                        <CardTitle>Widget Customization</CardTitle>
                        <CardDescription>Customize the appearance of your feedback button</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="button_text">Button Text</Label>
                                <Input id="button_text" v-model="form.button_text" type="text" placeholder="Report Issue" />
                                <InputError :message="form.errors.button_text" class="mt-2" />
                            </div>

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
                                <InputError :message="form.errors.button_size" class="mt-2" />
                            </div>

                            <div>
                                <Label for="button_color">Button Color</Label>
                                <div class="flex gap-2">
                                    <Input 
                                        id="button_color" 
                                        v-model="form.button_color" 
                                        type="color" 
                                        class="h-10 w-20" 
                                    />
                                    <Input 
                                        v-model="form.button_color" 
                                        type="text" 
                                        placeholder="#3b82f6" 
                                        pattern="^#[a-fA-F0-9]{6}$"
                                        class="flex-1"
                                    />
                                </div>
                                <InputError :message="form.errors.button_color" class="mt-2" />
                            </div>

                            <div>
                                <Label for="button_text_color">Text Color</Label>
                                <div class="flex gap-2">
                                    <Input 
                                        id="button_text_color" 
                                        v-model="form.button_text_color" 
                                        type="color" 
                                        class="h-10 w-20" 
                                    />
                                    <Input 
                                        v-model="form.button_text_color" 
                                        type="text" 
                                        placeholder="#ffffff" 
                                        pattern="^#[a-fA-F0-9]{6}$"
                                        class="flex-1"
                                    />
                                </div>
                                <InputError :message="form.errors.button_text_color" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
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
                                <InputError :message="form.errors.button_position" class="mt-2" />
                            </div>
                        </div>

                        <Separator />

                        <div>
                            <Label class="mb-3 block">Button Preview</Label>
                            <div class="flex items-center justify-center p-8 bg-muted rounded-lg">
                                <button :style="buttonPreviewStyle" type="button">
                                    {{ form.button_text }}
                                </button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Domain Management -->
                <Card>
                    <CardHeader>
                        <CardTitle>Allowed Domains</CardTitle>
                        <CardDescription>
                            Specify which domains can display the feedback widget. 
                            Use wildcards like *.example.com to allow all subdomains.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex gap-2">
                            <Input 
                                v-model="newDomain" 
                                type="text" 
                                placeholder="example.com or *.example.com"
                                @keydown.enter.prevent="addDomain"
                            />
                            <Button type="button" @click="addDomain" :disabled="!newDomain">
                                <Plus class="h-4 w-4" />
                            </Button>
                        </div>

                        <div v-if="form.allowed_domains.length > 0" class="space-y-2">
                            <div 
                                v-for="(domain, index) in form.allowed_domains" 
                                :key="index"
                                class="flex items-center justify-between p-2 bg-muted rounded-md"
                            >
                                <span class="text-sm">{{ domain }}</span>
                                <Button 
                                    type="button" 
                                    variant="ghost" 
                                    size="sm"
                                    @click="removeDomain(index)"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No domains specified. The widget will be accessible from any domain.
                        </p>
                        <InputError :message="form.errors.allowed_domains" class="mt-2" />
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">
                        Save Changes
                    </Button>
                    <Button type="button" variant="outline" @click="$inertia.visit(route('forms.show', form.id))">
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>