<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as approvalsIndex, approve as approvalsApprove, reject as approvalsReject } from '@/actions/App/Http/Controllers/ApprovalController';
import { show as transactionsShow } from '@/actions/App/Http/Controllers/TransactionController';
import { type BreadcrumbItem, type User, type Transaction, type Category } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ArrowLeft, Check, X, Clock, User as UserIcon, FileText, Calendar, DollarSign, Tag, Image } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface MediaItem {
    id: number;
    name: string;
    original_url: string;
    size: number;
    mime_type: string;
}

interface ApprovalData {
    id: number;
    transaction_id: number;
    submitted_by: number;
    reviewed_by: number | null;
    status: 'pending' | 'approved' | 'rejected';
    notes: string | null;
    rejection_reason: string | null;
    submitted_at: string;
    reviewed_at: string | null;
    transaction: Transaction & {
        category?: Category;
        media?: MediaItem[];
        user?: User;
    };
    submitted_by_user: User;
    reviewed_by_user?: User;
}

interface Props {
    approval: ApprovalData;
}

const props = defineProps<Props>();

const approveForm = useForm({});
const rejectForm = useForm({
    rejection_reason: '',
});

const showApproveDialog = ref(false);
const showRejectDialog = ref(false);
const showImageModal = ref(false);
const selectedImage = ref<MediaItem | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Approvals',
        href: approvalsIndex().url,
    },
    {
        title: `Approval #${props.approval.id}`,
        href: '#',
    },
];

const isPending = computed(() => props.approval.status === 'pending');

function submitApprove() {
    approveForm.post(approvalsApprove(props.approval.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showApproveDialog.value = false;
        },
    });
}

function submitReject() {
    rejectForm.post(approvalsReject(props.approval.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showRejectDialog.value = false;
            rejectForm.reset();
        },
    });
}

function getStatusVariant(status: string) {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        pending: 'secondary',
        approved: 'default',
        rejected: 'destructive',
    };
    return variants[status] || 'outline';
}

function getTypeColor(type: string) {
    return type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
}

function formatCurrency(amount: string | number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(Number(amount));
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatDateTime(date: string) {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function openImageModal(image: MediaItem) {
    selectedImage.value = image;
    showImageModal.value = true;
}
</script>

<template>
    <Head :title="`Approval #${approval.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="approvalsIndex().url">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft class="size-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold">Approval Request</h1>
                        <p class="text-muted-foreground">
                            Review transaction {{ approval.transaction.transaction_number }}
                        </p>
                    </div>
                </div>
                <div v-if="isPending" class="flex gap-2">
                    <Button
                        class="bg-green-600 hover:bg-green-700"
                        @click="showApproveDialog = true"
                    >
                        <Check class="mr-2 size-4" />
                        Approve
                    </Button>
                    <Button
                        variant="destructive"
                        @click="showRejectDialog = true"
                    >
                        <X class="mr-2 size-4" />
                        Reject
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <!-- Transaction Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Transaction Details</CardTitle>
                        <CardDescription>
                            Information about the transaction
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center gap-3">
                            <FileText class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Transaction Number</p>
                                <p class="font-medium">{{ approval.transaction.transaction_number }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <DollarSign class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Amount</p>
                                <p class="font-medium" :class="getTypeColor(approval.transaction.type)">
                                    {{ formatCurrency(approval.transaction.amount) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Tag class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Type</p>
                                <Badge :class="getTypeColor(approval.transaction.type)" variant="outline">
                                    {{ approval.transaction.type === 'in' ? 'Cash In' : 'Cash Out' }}
                                </Badge>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Calendar class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Transaction Date</p>
                                <p class="font-medium">{{ formatDate(approval.transaction.transaction_date) }}</p>
                            </div>
                        </div>

                        <div v-if="approval.transaction.category" class="flex items-center gap-3">
                            <Tag class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Category</p>
                                <p class="font-medium">{{ approval.transaction.category.name }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground mb-1">Description</p>
                            <p class="text-sm">{{ approval.transaction.description }}</p>
                        </div>

                        <div v-if="approval.transaction.notes">
                            <p class="text-sm text-muted-foreground mb-1">Notes</p>
                            <p class="text-sm">{{ approval.transaction.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Approval Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Approval Information</CardTitle>
                        <CardDescription>
                            Details about this approval request
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center gap-3">
                            <Clock class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Status</p>
                                <Badge :variant="getStatusVariant(approval.status)">
                                    {{ approval.status }}
                                </Badge>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <UserIcon class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Submitted By</p>
                                <p class="font-medium">{{ approval.submitted_by_user.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ approval.submitted_by_user.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Calendar class="size-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Submitted At</p>
                                <p class="font-medium">{{ formatDateTime(approval.submitted_at) }}</p>
                            </div>
                        </div>

                        <div v-if="approval.notes">
                            <p class="text-sm text-muted-foreground mb-1">Submission Notes</p>
                            <p class="text-sm bg-muted p-3 rounded-md">{{ approval.notes }}</p>
                        </div>

                        <div v-if="approval.reviewed_by_user" class="pt-4 border-t">
                            <div class="flex items-center gap-3 mb-3">
                                <UserIcon class="size-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm text-muted-foreground">Reviewed By</p>
                                    <p class="font-medium">{{ approval.reviewed_by_user.name }}</p>
                                </div>
                            </div>

                            <div v-if="approval.reviewed_at" class="flex items-center gap-3">
                                <Calendar class="size-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm text-muted-foreground">Reviewed At</p>
                                    <p class="font-medium">{{ formatDateTime(approval.reviewed_at) }}</p>
                                </div>
                            </div>

                            <div v-if="approval.rejection_reason" class="mt-3">
                                <p class="text-sm text-muted-foreground mb-1">Rejection Reason</p>
                                <p class="text-sm bg-red-50 dark:bg-red-950 text-red-700 dark:text-red-300 p-3 rounded-md">
                                    {{ approval.rejection_reason }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Receipts -->
            <Card v-if="approval.transaction.media && approval.transaction.media.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Image class="size-5" />
                        Receipts
                    </CardTitle>
                    <CardDescription>
                        Attached receipt images
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        <div
                            v-for="media in approval.transaction.media"
                            :key="media.id"
                            class="relative group cursor-pointer"
                            @click="openImageModal(media)"
                        >
                            <img
                                :src="media.original_url"
                                :alt="media.name"
                                class="w-full h-40 object-cover rounded-lg border"
                            />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                <span class="text-white text-sm">Click to view</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- View Transaction Link -->
            <div class="flex justify-end">
                <Link :href="transactionsShow(approval.transaction.id).url">
                    <Button variant="outline">
                        View Full Transaction Details
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Approve Confirmation Dialog -->
        <Dialog v-model:open="showApproveDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Approve Transaction</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to approve transaction
                        {{ approval.transaction.transaction_number }}
                        for {{ formatCurrency(approval.transaction.amount) }}?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showApproveDialog = false">
                        Cancel
                    </Button>
                    <Button
                        class="bg-green-600 hover:bg-green-700"
                        :disabled="approveForm.processing"
                        @click="submitApprove"
                    >
                        Approve
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Reject Dialog -->
        <Dialog v-model:open="showRejectDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Reject Transaction</DialogTitle>
                    <DialogDescription>
                        Please provide a reason for rejecting transaction
                        {{ approval.transaction.transaction_number }}.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <Label for="rejection_reason">Rejection Reason</Label>
                    <Textarea
                        id="rejection_reason"
                        v-model="rejectForm.rejection_reason"
                        placeholder="Enter the reason for rejection (minimum 10 characters)"
                        class="mt-2"
                        rows="4"
                    />
                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-sm text-red-500">
                        {{ rejectForm.errors.rejection_reason }}
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showRejectDialog = false">
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="rejectForm.processing || rejectForm.rejection_reason.length < 10"
                        @click="submitReject"
                    >
                        Reject
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Image Modal -->
        <Dialog v-model:open="showImageModal">
            <DialogContent class="max-w-4xl">
                <DialogHeader>
                    <DialogTitle>{{ selectedImage?.name }}</DialogTitle>
                </DialogHeader>
                <div class="flex items-center justify-center">
                    <img
                        v-if="selectedImage"
                        :src="selectedImage.original_url"
                        :alt="selectedImage.name"
                        class="max-h-[70vh] object-contain"
                    />
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

