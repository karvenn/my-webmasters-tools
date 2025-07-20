<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

interface CtaButtonRule {
    id: number;
    url_pattern: string;
    destination_url: string;
    pattern_description?: string;
    priority: number;
    is_active: boolean;
    clicks_count?: number;
}

interface CtaButton {
    id: number;
    button_name: string;
    button_text: string;
    button_color: string;
    button_text_color: string;
    button_size: string;
    button_position: string;
    allowed_domains: string[];
    embed_token: string;
    is_active: boolean;
    rules: CtaButtonRule[];
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
        title: 'Edit',
        href: `/cta-buttons/${props.button.id}/edit`,
    },
];

// Button settings form
const buttonForm = useForm({
    button_name: props.button.button_name,
    button_text: props.button.button_text,
    button_color: props.button.button_color,
    button_text_color: props.button.button_text_color,
    button_size: props.button.button_size,
    button_position: props.button.button_position,
    allowed_domains: props.button.allowed_domains || [''],
    is_active: props.button.is_active,
});

// URL rule form
const ruleForm = useForm({
    url_pattern: '',
    destination_url: '',
    pattern_description: '',
    priority: 0,
    is_active: true,
});

// Test URL functionality
const testUrl = ref('');
const testResult = ref<any>(null);
const copied = ref(false);

const addDomain = () => {
    buttonForm.allowed_domains.push('');
};

const removeDomain = (index: number) => {
    buttonForm.allowed_domains.splice(index, 1);
};

const updateButton = () => {
    buttonForm.put(route('cta-buttons.update', props.button.id));
};

const addRule = () => {
    ruleForm.post(route('cta-buttons.rules.store', props.button.id), {
        onSuccess: () => {
            ruleForm.reset();
        },
    });
};

const deleteRule = (rule: CtaButtonRule) => {
    if (confirm('Are you sure you want to delete this rule?')) {
        router.delete(route('cta-buttons.rules.destroy', [props.button.id, rule.id]), {
            preserveScroll: true,
        });
    }
};

const testPattern = async () => {
    try {
        const response = await axios.post(route('cta-buttons.rules.test', props.button.id), {
            test_url: testUrl.value,
        });
        testResult.value = response.data;
    } catch (error) {
        console.error('Test failed:', error);
        testResult.value = { matched: false, message: 'Test failed' };
    }
};

const copyEmbedCode = async () => {
    if (!props.embedCode) return;
    
    try {
        // Try modern clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(props.embedCode);
        } else {
            // Fallback for older browsers or non-HTTPS
            const textArea = document.createElement('textarea');
            textArea.value = props.embedCode;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
        
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy text: ', err);
        // You could also show a toast notification here
    }
};
</script>

<template>
    <Head title="Edit CTA Button" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Edit CTA Button: {{ button.button_name }}</h1>
                    <p class="text-muted-foreground">Manage your CTA button settings and URL rules</p>
                </div>
            </div>

            <!-- Button Settings -->
            <Card>
                <CardHeader>
                    <CardTitle>Button Settings</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="updateButton" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Button Name -->
                            <div>
                                <Label for="button_name">Button Name</Label>
                                <Input
                                    id="button_name"
                                    v-model="buttonForm.button_name"
                                    type="text"
                                    required
                                />
                                <p v-if="buttonForm.errors.button_name" class="mt-1 text-sm text-red-600">
                                    {{ buttonForm.errors.button_name }}
                                </p>
                            </div>

                            <!-- Button Text -->
                            <div>
                                <Label for="button_text">Button Text</Label>
                                <Input
                                    id="button_text"
                                    v-model="buttonForm.button_text"
                                    type="text"
                                    required
                                />
                                <p v-if="buttonForm.errors.button_text" class="mt-1 text-sm text-red-600">
                                    {{ buttonForm.errors.button_text }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div class="flex items-center space-x-2">
                                <Checkbox
                                    id="is_active"
                                    v-model:checked="buttonForm.is_active"
                                />
                                <Label for="is_active">Active</Label>
                            </div>
                        </div>

                        <!-- Allowed Domains -->
                        <div>
                            <Label>Allowed Domains</Label>
                            <div class="space-y-2">
                                <div v-for="(domain, index) in buttonForm.allowed_domains" :key="index" class="flex items-center space-x-2">
                                    <Input
                                        v-model="buttonForm.allowed_domains[index]"
                                        type="text"
                                        required
                                        placeholder="example.com"
                                        class="flex-1"
                                    />
                                    <Button
                                        v-if="buttonForm.allowed_domains.length > 1"
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
                        </div>

                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                :disabled="buttonForm.processing"
                            >
                                Update Settings
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- URL Rules -->
            <Card>
                <CardHeader>
                    <CardTitle>URL Pattern Rules</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Add New Rule -->
                    <div class="p-4 bg-muted rounded-lg">
                        <h4 class="font-medium mb-3">Add New URL Rule</h4>
                        <form @submit.prevent="addRule" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="url_pattern">URL Pattern</Label>
                                    <Input
                                        id="url_pattern"
                                        v-model="ruleForm.url_pattern"
                                        type="text"
                                        required
                                        placeholder="/news/edit/(\d+)"
                                    />
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Use () for capture groups, e.g., /product/(\d+)
                                    </p>
                                </div>
                                <div>
                                    <Label for="destination_url">Destination URL</Label>
                                    <Input
                                        id="destination_url"
                                        v-model="ruleForm.destination_url"
                                        type="url"
                                        required
                                        placeholder="https://preview.com/news/$1"
                                    />
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Use $1, $2, etc. for captured groups
                                    </p>
                                </div>
                                <div>
                                    <Label for="pattern_description">Description (Optional)</Label>
                                    <Input
                                        id="pattern_description"
                                        v-model="ruleForm.pattern_description"
                                        type="text"
                                        placeholder="News article edit page"
                                    />
                                </div>
                                <div>
                                    <Label for="priority">Priority</Label>
                                    <Input
                                        id="priority"
                                        v-model.number="ruleForm.priority"
                                        type="number"
                                        min="0"
                                        required
                                    />
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Higher priority rules are matched first
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Button
                                    type="submit"
                                    :disabled="ruleForm.processing"
                                >
                                    Add Rule
                                </Button>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="rule_active"
                                        v-model:checked="ruleForm.is_active"
                                    />
                                    <Label for="rule_active">Active</Label>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Test URL Pattern -->
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-medium mb-2">Test URL Pattern</h4>
                        <div class="flex items-center space-x-2">
                            <Input
                                v-model="testUrl"
                                type="text"
                                placeholder="Enter a URL to test, e.g., /news/edit/123"
                                class="flex-1"
                            />
                            <Button
                                @click="testPattern"
                                type="button"
                                variant="outline"
                            >
                                Test
                            </Button>
                        </div>
                        <div v-if="testResult" class="mt-2 text-sm">
                            <p v-if="testResult.matched" class="text-green-600">
                                ✓ Matched! Destination: {{ testResult.destination_url }}
                            </p>
                            <p v-else class="text-red-600">
                                ✗ No matching rule found
                            </p>
                        </div>
                    </div>

                    <!-- Existing Rules -->
                    <div>
                        <h4 class="font-medium mb-3">Current Rules</h4>
                        <div v-if="button.rules.length === 0" class="text-center text-muted-foreground py-8">
                            No URL rules defined yet. Add one above to get started.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="rule in button.rules"
                                :key="rule.id"
                                class="p-4 border rounded-lg"
                                :class="rule.is_active ? 'bg-background border-border' : 'bg-muted border-muted'"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <code class="text-sm bg-muted px-2 py-1 rounded">{{ rule.url_pattern }}</code>
                                            <span class="text-muted-foreground">→</span>
                                            <a :href="rule.destination_url" target="_blank" class="text-sm text-blue-600 hover:underline">
                                                {{ rule.destination_url }}
                                            </a>
                                        </div>
                                        <div class="mt-1 text-sm text-muted-foreground">
                                            <span v-if="rule.pattern_description">{{ rule.pattern_description }} • </span>
                                            Priority: {{ rule.priority }} • 
                                            Status: <span :class="rule.is_active ? 'text-green-600' : 'text-red-600'">
                                                {{ rule.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <span v-if="rule.clicks_count"> • Clicks: {{ rule.clicks_count }}</span>
                                        </div>
                                    </div>
                                    <Button
                                        @click="deleteRule(rule)"
                                        variant="outline"
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Embed Code -->
            <Card>
                <CardHeader>
                    <CardTitle>Embed Code</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="embedCode" class="relative">
                        <pre class="p-4 bg-muted rounded text-sm overflow-x-auto">{{ embedCode }}</pre>
                        <Button
                            @click="copyEmbedCode"
                            variant="outline"
                            size="sm"
                            class="absolute top-2 right-2"
                        >
                            {{ copied ? 'Copied!' : 'Copy' }}
                        </Button>
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Add this code to your website's HTML, just before the closing &lt;/body&gt; tag.
                    </p>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <Button variant="outline" as-child>
                    <Link :href="route('cta-buttons.index')">
                        ← Back to CTA Buttons
                    </Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="route('cta-buttons.show', button.id)">
                        View Analytics
                    </Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>