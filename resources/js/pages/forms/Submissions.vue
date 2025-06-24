<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link } from '@inertiajs/vue3';
import { 
    Calendar, 
    Check, 
    CheckCircle2,
    Clock,
    Copy, 
    Eye, 
    Link2, 
    Mail, 
    MoreVertical,
    Paperclip, 
    Trash2, 
    User,
    AlertCircle,
    ChevronLeft,
    ChevronRight,
    Pause,
    Users,
    Building2,
    FileImage,
    ExternalLink,
    Monitor,
    Maximize2,
    Wifi,
    Globe,
    AlertTriangle,
    Edit
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Form {
    id: number;
    website_name: string;
    website_url: string;
}

interface Attachment {
    id: number;
    filename: string;
    original_filename: string;
    mime_type: string;
    size: number;
    url: string;
}

interface Submission {
    id: number;
    issue_description: string;
    priority: 'low' | 'medium' | 'high';
    submitter_name: string;
    submitter_email: string;
    page_url: string;
    status: 'new' | 'wip' | 'agency_review' | 'client_review' | 'on_hold' | 'concluded';
    created_at: string;
    attachments?: Attachment[];
    browser_name?: string;
    browser_version?: string;
    operating_system?: string;
    device_type?: string;
    screen_resolution?: string;
    viewport_size?: string;
    technical_metadata?: {
        pixel_ratio?: number;
        color_depth?: number;
        orientation?: string;
        window_outer_size?: string;
        scroll_position?: string;
        page_load_time?: number;
        dom_ready_time?: number;
        cookies_enabled?: boolean;
        do_not_track?: string;
        language?: string;
        languages?: string[];
        platform?: string;
        online_status?: boolean;
        connection_type?: string;
        referrer?: string;
        page_title?: string;
        prefers_color_scheme?: string;
        prefers_reduced_motion?: boolean;
        timezone?: string;
        timezone_offset?: number;
        timestamp?: string;
        console_errors?: Array<{
            message: string;
            timestamp: string;
        }>;
    };
}

const props = defineProps<{
    form: Form;
    submissions: Submission[];
    embedCode: string;
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
        href: route('forms.show', props.form.id),
    },
];

const showEmbedDialog = ref(false);
const copiedCode = ref(false);
const selectedSubmission = ref<Submission | null>(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Pagination
const totalPages = computed(() => Math.ceil(props.submissions.length / itemsPerPage.value));
const paginatedSubmissions = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.submissions.slice(start, end);
});

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const copyEmbedCode = async () => {
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(props.embedCode);
        } else {
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
        copiedCode.value = true;
        setTimeout(() => {
            copiedCode.value = false;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy:', error);
    }
};

const updateStatus = (submissionId: number, status: string) => {
    router.patch(
        route('forms.submissions.update-status', [props.form.id, submissionId]), 
        { status: status },
        { 
            preserveScroll: true,
            onSuccess: () => {
                // Update the local submission if it's selected
                if (selectedSubmission.value && selectedSubmission.value.id === submissionId) {
                    selectedSubmission.value.status = status as any;
                }
            }
        }
    );
};

const deleteSubmission = (submissionId: number) => {
    if (confirm('Are you sure you want to delete this submission?')) {
        router.delete(
            route('forms.submissions.destroy', [props.form.id, submissionId]),
            { 
                preserveScroll: true,
                onSuccess: () => {
                    selectedSubmission.value = null;
                }
            }
        );
    }
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high':
            return 'bg-red-500';
        case 'medium':
            return 'bg-yellow-500';
        case 'low':
            return 'bg-green-500';
        default:
            return 'bg-gray-500';
    }
};

const getPriorityBadgeVariant = (priority: string) => {
    switch (priority) {
        case 'high':
            return 'destructive';
        case 'medium':
            return 'default';
        case 'low':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getStatusInfo = (status: string) => {
    switch (status) {
        case 'new':
            return { icon: AlertCircle, label: 'New', class: 'text-red-500' };
        case 'wip':
            return { icon: Clock, label: 'Work in Progress', class: 'text-blue-500' };
        case 'agency_review':
            return { icon: Building2, label: 'Agency Review', class: 'text-purple-500' };
        case 'client_review':
            return { icon: Users, label: 'Client Review', class: 'text-orange-500' };
        case 'on_hold':
            return { icon: Pause, label: 'On Hold', class: 'text-gray-500' };
        case 'concluded':
            return { icon: CheckCircle2, label: 'Concluded', class: 'text-green-500' };
        default:
            return { icon: AlertCircle, label: 'Unknown', class: 'text-gray-500' };
    }
};

const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
};

const truncateText = (text: string, length: number) => {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};

const isImageFile = (mimeType: string) => {
    return mimeType.startsWith('image/');
};

// Summary stats computed
const summaryStats = computed(() => ({
    total: props.submissions.length,
    new: props.submissions.filter(s => s.status === 'new').length,
    wip: props.submissions.filter(s => s.status === 'wip').length,
    agency_review: props.submissions.filter(s => s.status === 'agency_review').length,
    client_review: props.submissions.filter(s => s.status === 'client_review').length,
    on_hold: props.submissions.filter(s => s.status === 'on_hold').length,
    concluded: props.submissions.filter(s => s.status === 'concluded').length,
}));
</script>

<template>
    <Head :title="`${form.website_name} - Submissions`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ form.website_name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ form.website_url }}</p>
                </div>
                <div class="flex gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('forms.edit', form.id)">
                            <Edit class="mr-2 h-4 w-4" />
                            <span class="hidden sm:inline">Edit Form</span>
                            <span class="sm:hidden">Edit</span>
                        </Link>
                    </Button>
                    <Button @click="showEmbedDialog = true" variant="outline">
                        <Copy class="mr-2 h-4 w-4" />
                        <span class="hidden sm:inline">Embed Code</span>
                        <span class="sm:hidden">Embed</span>
                    </Button>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-7">
                <Card>
                    <CardHeader class="p-4">
                        <CardTitle class="text-sm font-medium">Total</CardTitle>
                        <p class="text-2xl font-bold">{{ summaryStats.total }}</p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="p-4">
                        <CardTitle class="text-sm font-medium">New</CardTitle>
                        <p class="text-2xl font-bold text-red-500">{{ summaryStats.new }}</p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="p-4">
                        <CardTitle class="text-sm font-medium">WIP</CardTitle>
                        <p class="text-2xl font-bold text-blue-500">{{ summaryStats.wip }}</p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="p-4">
                        <CardTitle class="text-sm font-medium">Agency</CardTitle>
                        <p class="text-2xl font-bold text-purple-500">{{ summaryStats.agency_review }}</p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="p-4">
                        <CardTitle class="text-sm font-medium">Client</CardTitle>
                        <p class="text-2xl font-bold text-orange-500">{{ summaryStats.client_review }}</p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="p-4">
                        <CardTitle class="text-sm font-medium">On Hold</CardTitle>
                        <p class="text-2xl font-bold text-gray-500">{{ summaryStats.on_hold }}</p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="p-4">
                        <CardTitle class="text-sm font-medium">Done</CardTitle>
                        <p class="text-2xl font-bold text-green-500">{{ summaryStats.concluded }}</p>
                    </CardHeader>
                </Card>
            </div>

            <!-- No submissions state -->
            <div v-if="submissions.length === 0" class="flex flex-1 items-center justify-center">
                <Card class="w-full max-w-md">
                    <CardHeader class="text-center">
                        <CardTitle>No submissions yet</CardTitle>
                        <CardDescription>Share the embed code to start collecting feedback</CardDescription>
                    </CardHeader>
                    <CardContent class="text-center">
                        <Button @click="showEmbedDialog = true">
                            <Copy class="mr-2 h-4 w-4" />
                            Get Embed Code
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Table View -->
            <Card v-else class="flex-1">
                <div class="w-full overflow-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="text-left text-sm font-medium text-muted-foreground">
                                <th class="p-4 w-10">Status</th>
                                <th class="p-4 w-20">Priority</th>
                                <th class="p-4 min-w-[300px]">Issue</th>
                                <th class="p-4 min-w-[150px]">Submitter</th>
                                <th class="p-4 w-24">Date</th>
                                <th class="p-4 w-10 text-center">Files</th>
                                <th class="p-4 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="submission in paginatedSubmissions" 
                                :key="submission.id"
                                class="border-b hover:bg-muted/50 cursor-pointer"
                                @click="selectedSubmission = submission"
                            >
                                <td class="p-4">
                                    <component 
                                        :is="getStatusInfo(submission.status).icon" 
                                        :class="['h-5 w-5', getStatusInfo(submission.status).class]"
                                    />
                                </td>
                                <td class="p-4">
                                    <Badge :variant="getPriorityBadgeVariant(submission.priority)">
                                        {{ submission.priority }}
                                    </Badge>
                                </td>
                                <td class="p-4">
                                    <p class="font-medium text-sm">
                                        {{ truncateText(submission.issue_description, 100) }}
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        {{ truncateText(submission.page_url, 50) }}
                                    </p>
                                </td>
                                <td class="p-4">
                                    <p class="text-sm font-medium">{{ submission.submitter_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ submission.submitter_email }}</p>
                                </td>
                                <td class="p-4 text-sm text-muted-foreground">
                                    {{ formatDate(submission.created_at) }}
                                </td>
                                <td class="p-4 text-center">
                                    <div v-if="submission.attachments && submission.attachments.length > 0" class="flex items-center justify-center">
                                        <Paperclip class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm ml-1">{{ submission.attachments.length }}</span>
                                    </div>
                                </td>
                                <td class="p-4" @click.stop>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="selectedSubmission = submission">
                                                <Eye class="h-4 w-4 mr-2" />
                                                View Details
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem @click="updateStatus(submission.id, 'new')">
                                                <AlertCircle class="h-4 w-4 mr-2" />
                                                Mark as New
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="updateStatus(submission.id, 'wip')">
                                                <Clock class="h-4 w-4 mr-2" />
                                                Mark as WIP
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="updateStatus(submission.id, 'agency_review')">
                                                <Building2 class="h-4 w-4 mr-2" />
                                                Agency Review
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="updateStatus(submission.id, 'client_review')">
                                                <Users class="h-4 w-4 mr-2" />
                                                Client Review
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="updateStatus(submission.id, 'on_hold')">
                                                <Pause class="h-4 w-4 mr-2" />
                                                On Hold
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="updateStatus(submission.id, 'concluded')">
                                                <CheckCircle2 class="h-4 w-4 mr-2" />
                                                Concluded
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem 
                                                @click="deleteSubmission(submission.id)"
                                                class="text-red-600"
                                            >
                                                <Trash2 class="h-4 w-4 mr-2" />
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-between p-4 border-t">
                    <p class="text-sm text-muted-foreground">
                        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to 
                        {{ Math.min(currentPage * itemsPerPage, submissions.length) }} of 
                        {{ submissions.length }} results
                    </p>
                    <div class="flex items-center gap-2">
                        <Button 
                            variant="outline" 
                            size="icon"
                            :disabled="currentPage === 1"
                            @click="goToPage(currentPage - 1)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <div class="flex items-center gap-1">
                            <Button
                                v-for="page in Math.min(5, totalPages)"
                                :key="page"
                                variant="outline"
                                size="sm"
                                :class="{ 'bg-primary text-primary-foreground': page === currentPage }"
                                @click="goToPage(page)"
                            >
                                {{ page }}
                            </Button>
                        </div>
                        <Button 
                            variant="outline" 
                            size="icon"
                            :disabled="currentPage === totalPages"
                            @click="goToPage(currentPage + 1)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Embed Code Dialog -->
        <Dialog v-model:open="showEmbedDialog">
            <DialogContent class="w-[90vw] max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Embed Code</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Copy and paste this code into your website's HTML, just before the closing &lt;/body&gt; tag.
                    </p>
                    <div class="relative">
                        <pre class="overflow-x-auto rounded-lg bg-muted p-4 pr-12 text-xs sm:text-sm whitespace-pre-wrap break-all"><code>{{ embedCode }}</code></pre>
                        <Button size="sm" variant="ghost" class="absolute top-2 right-2 h-8 w-8 p-0 z-10" @click="copyEmbedCode">
                            <Check v-if="copiedCode" class="h-4 w-4" />
                            <Copy v-else class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Submission Detail Sheet (Right Drawer) -->
        <Sheet :open="!!selectedSubmission" @update:open="(open) => !open && (selectedSubmission = null)">
            <SheetContent v-if="selectedSubmission" side="right" class="w-full sm:max-w-xl overflow-y-auto p-0">
                <!-- Compact Header -->
                <div class="sticky top-0 bg-background z-10 border-b px-6 py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <component 
                                    :is="getStatusInfo(selectedSubmission.status).icon" 
                                    :class="['h-4 w-4', getStatusInfo(selectedSubmission.status).class]"
                                />
                                <span class="text-sm font-medium">{{ getStatusInfo(selectedSubmission.status).label }}</span>
                                <Badge :variant="getPriorityBadgeVariant(selectedSubmission.priority)" class="scale-90">
                                    {{ selectedSubmission.priority }}
                                </Badge>
                            </div>
                            <p class="text-xs text-muted-foreground">ID: #{{ selectedSubmission.id }} • {{ formatDate(selectedSubmission.created_at) }}</p>
                        </div>
                        <Button 
                            @click="deleteSubmission(selectedSubmission.id)"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-muted-foreground hover:text-destructive"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <!-- Attachments Grid - Compact -->
                    <div v-if="selectedSubmission.attachments && selectedSubmission.attachments.length > 0" class="space-y-2">
                        <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Attachments ({{ selectedSubmission.attachments.length }})</h4>
                        <div class="grid grid-cols-3 gap-2">
                            <div 
                                v-for="attachment in selectedSubmission.attachments" 
                                :key="attachment.id"
                                class="relative group"
                            >
                                <!-- Image Preview -->
                                <a 
                                    v-if="isImageFile(attachment.mime_type)" 
                                    :href="attachment.url"
                                    target="_blank"
                                    class="block relative aspect-square rounded border overflow-hidden hover:ring-2 hover:ring-primary transition-all"
                                >
                                    <img 
                                        :src="attachment.url" 
                                        :alt="attachment.original_filename"
                                        class="w-full h-full object-contain bg-muted"
                                    />
                                    <div class="absolute inset-0 bg-black/0 hover:bg-black/20 transition-colors flex items-center justify-center">
                                        <ExternalLink class="h-4 w-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                    </div>
                                </a>
                                <!-- File Preview -->
                                <a 
                                    v-else
                                    :href="attachment.url" 
                                    target="_blank"
                                    class="flex flex-col items-center justify-center aspect-square border rounded hover:bg-muted transition-colors p-2 text-center"
                                >
                                    <FileImage class="h-6 w-6 text-muted-foreground mb-1" />
                                    <span class="text-[10px] leading-tight break-all line-clamp-2">{{ attachment.original_filename }}</span>
                                    <span class="text-[10px] text-muted-foreground mt-0.5">{{ formatFileSize(attachment.size) }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Issue Details - Compact Card -->
                    <div class="bg-muted/50 rounded-lg p-4 space-y-3">
                        <div>
                            <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-1">Page URL</h4>
                            <a 
                                :href="selectedSubmission.page_url" 
                                target="_blank" 
                                class="text-sm text-primary hover:underline break-all flex items-center gap-1"
                            >
                                <Link2 class="h-3 w-3 flex-shrink-0" />
                                {{ selectedSubmission.page_url }}
                            </a>
                        </div>
                        
                        <div>
                            <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-1">Issue Description</h4>
                            <p class="text-sm whitespace-pre-wrap">{{ selectedSubmission.issue_description }}</p>
                        </div>
                    </div>

                    <!-- Submitter Info - Inline -->
                    <div class="flex items-center justify-between py-2 border-y">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center">
                                <User class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ selectedSubmission.submitter_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ selectedSubmission.submitter_email }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-muted-foreground">Submitted</p>
                            <p class="text-xs font-medium">{{ new Date(selectedSubmission.created_at).toLocaleString() }}</p>
                        </div>
                    </div>

                    <!-- Technical Metadata Accordion -->
                    <div class="space-y-1 border-t pt-3">
                        <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">Technical Information</h4>
                        
                        <!-- Browser & Device Accordion -->
                        <details class="group border rounded-lg">
                            <summary class="flex items-center justify-between cursor-pointer p-3 hover:bg-muted/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded bg-blue-100 flex items-center justify-center">
                                        <Monitor class="h-3 w-3 text-blue-600" />
                                    </div>
                                    <span class="text-sm font-medium">Browser & Device</span>
                                </div>
                                <ChevronRight class="h-4 w-4 transition-transform group-open:rotate-90" />
                            </summary>
                            <div class="p-3 pt-0 text-xs space-y-1.5">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                    <div>
                                        <span class="text-muted-foreground">Browser:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.browser_name || 'Unknown' }} {{ selectedSubmission.browser_version || '' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Device:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.device_type || 'Unknown' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">OS:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.operating_system || 'Unknown' }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.platform">
                                        <span class="text-muted-foreground">Platform:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.platform }}</span>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <!-- Display & Screen Accordion -->
                        <details class="group border rounded-lg">
                            <summary class="flex items-center justify-between cursor-pointer p-3 hover:bg-muted/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded bg-purple-100 flex items-center justify-center">
                                        <Maximize2 class="h-3 w-3 text-purple-600" />
                                    </div>
                                    <span class="text-sm font-medium">Display & Screen</span>
                                </div>
                                <ChevronRight class="h-4 w-4 transition-transform group-open:rotate-90" />
                            </summary>
                            <div class="p-3 pt-0 text-xs space-y-1.5">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                    <div>
                                        <span class="text-muted-foreground">Screen:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.screen_resolution || 'Unknown' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Viewport:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.viewport_size || 'Unknown' }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.pixel_ratio">
                                        <span class="text-muted-foreground">Pixel Ratio:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.pixel_ratio }}x</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.color_depth">
                                        <span class="text-muted-foreground">Color Depth:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.color_depth }}-bit</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.orientation">
                                        <span class="text-muted-foreground">Orientation:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.orientation }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.scroll_position">
                                        <span class="text-muted-foreground">Scroll:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.scroll_position }}</span>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <!-- Network & Performance Accordion -->
                        <details class="group border rounded-lg">
                            <summary class="flex items-center justify-between cursor-pointer p-3 hover:bg-muted/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded bg-green-100 flex items-center justify-center">
                                        <Wifi class="h-3 w-3 text-green-600" />
                                    </div>
                                    <span class="text-sm font-medium">Network & Performance</span>
                                </div>
                                <ChevronRight class="h-4 w-4 transition-transform group-open:rotate-90" />
                            </summary>
                            <div class="p-3 pt-0 text-xs space-y-1.5">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                    <div v-if="selectedSubmission.technical_metadata?.connection_type">
                                        <span class="text-muted-foreground">Connection:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.connection_type }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.online_status !== undefined">
                                        <span class="text-muted-foreground">Status:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.online_status ? 'Online' : 'Offline' }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.page_load_time">
                                        <span class="text-muted-foreground">Page Load:</span>
                                        <span class="ml-1 font-medium">{{ (selectedSubmission.technical_metadata.page_load_time / 1000).toFixed(2) }}s</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.dom_ready_time">
                                        <span class="text-muted-foreground">DOM Ready:</span>
                                        <span class="ml-1 font-medium">{{ (selectedSubmission.technical_metadata.dom_ready_time / 1000).toFixed(2) }}s</span>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <!-- User Context Accordion -->
                        <details class="group border rounded-lg">
                            <summary class="flex items-center justify-between cursor-pointer p-3 hover:bg-muted/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded bg-orange-100 flex items-center justify-center">
                                        <Globe class="h-3 w-3 text-orange-600" />
                                    </div>
                                    <span class="text-sm font-medium">User Context</span>
                                </div>
                                <ChevronRight class="h-4 w-4 transition-transform group-open:rotate-90" />
                            </summary>
                            <div class="p-3 pt-0 text-xs space-y-1.5">
                                <div class="space-y-1">
                                    <div v-if="selectedSubmission.technical_metadata?.language">
                                        <span class="text-muted-foreground">Language:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.language }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.timezone">
                                        <span class="text-muted-foreground">Timezone:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.timezone }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.referrer && selectedSubmission.technical_metadata.referrer !== 'direct'">
                                        <span class="text-muted-foreground">Referrer:</span>
                                        <span class="ml-1 font-medium break-all">{{ selectedSubmission.technical_metadata.referrer }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.page_title">
                                        <span class="text-muted-foreground">Page Title:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.page_title }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.prefers_color_scheme">
                                        <span class="text-muted-foreground">Color Scheme:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.prefers_color_scheme }}</span>
                                    </div>
                                    <div v-if="selectedSubmission.technical_metadata?.cookies_enabled !== undefined">
                                        <span class="text-muted-foreground">Cookies:</span>
                                        <span class="ml-1 font-medium">{{ selectedSubmission.technical_metadata.cookies_enabled ? 'Enabled' : 'Disabled' }}</span>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <!-- Console Errors Accordion (if any) -->
                        <details v-if="selectedSubmission.technical_metadata?.console_errors && selectedSubmission.technical_metadata.console_errors.length > 0" class="group border rounded-lg border-red-200 bg-red-50/50">
                            <summary class="flex items-center justify-between cursor-pointer p-3 hover:bg-red-100/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded bg-red-100 flex items-center justify-center">
                                        <AlertTriangle class="h-3 w-3 text-red-600" />
                                    </div>
                                    <span class="text-sm font-medium text-red-900">Console Errors ({{ selectedSubmission.technical_metadata.console_errors.length }})</span>
                                </div>
                                <ChevronRight class="h-4 w-4 transition-transform group-open:rotate-90" />
                            </summary>
                            <div class="p-3 pt-0">
                                <div class="max-h-32 overflow-y-auto space-y-1">
                                    <div v-for="(error, index) in selectedSubmission.technical_metadata.console_errors" :key="index" class="text-xs text-red-700 font-mono bg-red-100 p-2 rounded">
                                        <span class="text-[10px] text-red-500">{{ new Date(error.timestamp).toLocaleTimeString() }}</span>
                                        <div class="mt-0.5">{{ error.message }}</div>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>

                    <!-- Status Actions - Compact Grid -->
                    <div class="space-y-1.5">
                        <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Update Status</h4>
                        <div class="grid grid-cols-3 gap-1">
                            <Button 
                                @click="updateStatus(selectedSubmission.id, 'new')"
                                variant="outline"
                                size="sm"
                                :class="[
                                    'h-8 text-[11px] px-2',
                                    selectedSubmission.status === 'new' ? 'bg-red-50 border-red-500 text-red-700' : ''
                                ]"
                            >
                                <AlertCircle class="h-3 w-3 mr-1" />
                                New
                            </Button>
                            <Button 
                                @click="updateStatus(selectedSubmission.id, 'wip')"
                                variant="outline"
                                size="sm"
                                :class="[
                                    'h-8 text-[11px] px-2',
                                    selectedSubmission.status === 'wip' ? 'bg-blue-50 border-blue-500 text-blue-700' : ''
                                ]"
                            >
                                <Clock class="h-3 w-3 mr-1" />
                                WIP
                            </Button>
                            <Button 
                                @click="updateStatus(selectedSubmission.id, 'agency_review')"
                                variant="outline"
                                size="sm"
                                :class="[
                                    'h-8 text-[11px] px-2',
                                    selectedSubmission.status === 'agency_review' ? 'bg-purple-50 border-purple-500 text-purple-700' : ''
                                ]"
                            >
                                <Building2 class="h-3 w-3 mr-1" />
                                Agency
                            </Button>
                            <Button 
                                @click="updateStatus(selectedSubmission.id, 'client_review')"
                                variant="outline"
                                size="sm"
                                :class="[
                                    'h-8 text-[11px] px-2',
                                    selectedSubmission.status === 'client_review' ? 'bg-orange-50 border-orange-500 text-orange-700' : ''
                                ]"
                            >
                                <Users class="h-3 w-3 mr-1" />
                                Client
                            </Button>
                            <Button 
                                @click="updateStatus(selectedSubmission.id, 'on_hold')"
                                variant="outline"
                                size="sm"
                                :class="[
                                    'h-8 text-[11px] px-2',
                                    selectedSubmission.status === 'on_hold' ? 'bg-gray-50 border-gray-500 text-gray-700' : ''
                                ]"
                            >
                                <Pause class="h-3 w-3 mr-1" />
                                Hold
                            </Button>
                            <Button 
                                @click="updateStatus(selectedSubmission.id, 'concluded')"
                                variant="outline"
                                size="sm"
                                :class="[
                                    'h-8 text-[11px] px-2',
                                    selectedSubmission.status === 'concluded' ? 'bg-green-50 border-green-500 text-green-700' : ''
                                ]"
                            >
                                <CheckCircle2 class="h-3 w-3 mr-1" />
                                Done
                            </Button>
                        </div>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>