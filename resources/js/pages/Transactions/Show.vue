<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as transactionsIndex, show as transactionsShow, edit as transactionsEdit } from '@/actions/App/Http/Controllers/TransactionController';
import { type BreadcrumbItem, type Transaction, type Receipt, type Approval, type User } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Pencil, ArrowLeft, Download, Clock, CheckCircle, XCircle, User as UserIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';

interface ApprovalData {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    notes: string | null;
    rejection_reason: string | null;
    submitted_at: string;
    reviewed_at: string | null;
    submitted_by_user?: User;
    reviewed_by_user?: User;
}

interface Props {
    transaction: Transaction;
    receipts: Receipt[];
    approval?: ApprovalData | null;
}

const props = defineProps<Props>();

const showImageDialog = ref(false);
const selectedImage = ref<Receipt | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Transactions',
        href: transactionsIndex().url,
    },
    {
        title: props.transaction.transaction_number,
        href: transactionsShow(props.transaction.id).url,
    },
];

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

function viewImage(receipt: Receipt) {
    selectedImage.value = receipt;
    showImageDialog.value = true;
}

function formatFileSize(bytes: number) {
    if (bytes === 0) {
        return '0 Bytes';
    }
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}
</script>

<template>
    <Head :title="`Transaction ${transaction.transaction_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <Link :href="transactionsIndex().url">
                    <Button variant="outline">
                        <ArrowLeft class="mr-2 size-4" />
                        Back to Transactions
                    </Button>
                </Link>
                <Link
                    v-if="transaction.status === 'pending'"
                    :href="transactionsEdit(transaction.id).url"
                >
                    <Button>
                        <Pencil class="mr-2 size-4" />
                        Edit
                    </Button>
                </Link>
            </div>

            <!-- Transaction Details -->
            <Card>
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div>
                            <CardTitle>{{ transaction.transaction_number }}</CardTitle>
                            <CardDescription>
                                Transaction Details
                            </CardDescription>
                        </div>
                        <Badge :variant="getStatusVariant(transaction.status)">
                            {{ transaction.status }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Type
                                </p>
                                <Badge
                                    :class="getTypeColor(transaction.type)"
                                    variant="outline"
                                    class="mt-1"
                                >
                                    {{ transaction.type === 'in' ? 'Cash In' : 'Cash Out' }}
                                </Badge>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Amount
                                </p>
                                <p
                                    class="text-2xl font-bold"
                                    :class="getTypeColor(transaction.type)"
                                >
                                    {{ formatCurrency(transaction.amount) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Transaction Date
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ formatDate(transaction.transaction_date) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Created By
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ transaction.user?.name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Created At
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ formatDateTime(transaction.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Description
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ transaction.description }}
                                </p>
                            </div>

                            <div v-if="transaction.notes">
                                <p class="text-sm font-medium text-muted-foreground">
                                    Notes
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ transaction.notes }}
                                </p>
                            </div>

                            <div
                                v-if="transaction.approved_by && transaction.approver"
                            >
                                <p class="text-sm font-medium text-muted-foreground">
                                    Approved/Rejected By
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ transaction.approver.name }}
                                </p>
                            </div>

                            <div v-if="transaction.approved_at">
                                <p class="text-sm font-medium text-muted-foreground">
                                    Approved/Rejected At
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ formatDateTime(transaction.approved_at) }}
                                </p>
                            </div>

                            <div v-if="transaction.rejection_reason">
                                <p class="text-sm font-medium text-muted-foreground">
                                    Rejection Reason
                                </p>
                                <p class="mt-1 text-sm text-destructive">
                                    {{ transaction.rejection_reason }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Approval History -->
            <Card v-if="approval">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Clock class="size-5" />
                        Approval History
                    </CardTitle>
                    <CardDescription>
                        Approval workflow timeline for this transaction
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="relative space-y-4">
                        <!-- Timeline -->
                        <div class="absolute left-4 top-0 h-full w-0.5 bg-border" />

                        <!-- Submitted -->
                        <div class="relative flex items-start gap-4 pl-10">
                            <div class="absolute left-2 flex size-5 items-center justify-center rounded-full bg-blue-500 text-white">
                                <UserIcon class="size-3" />
                            </div>
                            <div>
                                <p class="font-medium">Submitted for Approval</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDateTime(approval.submitted_at) }}
                                </p>
                                <p v-if="approval.notes" class="mt-2 text-sm bg-muted p-2 rounded">
                                    "{{ approval.notes }}"
                                </p>
                            </div>
                        </div>

                        <!-- Reviewed (if applicable) -->
                        <div v-if="approval.reviewed_at" class="relative flex items-start gap-4 pl-10">
                            <div
                                :class="[
                                    'absolute left-2 flex size-5 items-center justify-center rounded-full text-white',
                                    approval.status === 'approved' ? 'bg-green-500' : 'bg-red-500'
                                ]"
                            >
                                <CheckCircle v-if="approval.status === 'approved'" class="size-3" />
                                <XCircle v-else class="size-3" />
                            </div>
                            <div>
                                <p class="font-medium">
                                    {{ approval.status === 'approved' ? 'Approved' : 'Rejected' }}
                                    <span v-if="approval.reviewed_by_user" class="font-normal text-muted-foreground">
                                        by {{ approval.reviewed_by_user.name }}
                                    </span>
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDateTime(approval.reviewed_at) }}
                                </p>
                                <p v-if="approval.rejection_reason" class="mt-2 text-sm bg-red-50 dark:bg-red-950 text-red-700 dark:text-red-300 p-2 rounded">
                                    Reason: {{ approval.rejection_reason }}
                                </p>
                            </div>
                        </div>

                        <!-- Pending (if still pending) -->
                        <div v-else class="relative flex items-start gap-4 pl-10">
                            <div class="absolute left-2 flex size-5 items-center justify-center rounded-full bg-yellow-500 text-white">
                                <Clock class="size-3" />
                            </div>
                            <div>
                                <p class="font-medium text-yellow-600">Awaiting Approval</p>
                                <p class="text-sm text-muted-foreground">
                                    Pending review by an approver
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Receipt Images -->
            <Card v-if="receipts.length > 0">
                <CardHeader>
                    <CardTitle>Receipt Images</CardTitle>
                    <CardDescription>
                        Attached receipt images for this transaction
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div
                            v-for="receipt in receipts"
                            :key="receipt.id"
                            class="group relative cursor-pointer"
                            @click="viewImage(receipt)"
                        >
                            <img
                                :src="receipt.url"
                                :alt="receipt.name"
                                class="h-48 w-full rounded-lg border object-cover transition-all group-hover:opacity-80"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <p class="text-sm text-white">Click to view</p>
                            </div>
                            <div class="mt-2">
                                <p class="truncate text-xs text-muted-foreground">
                                    {{ receipt.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatFileSize(receipt.size) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Image Preview Dialog -->
        <Dialog v-model:open="showImageDialog">
            <DialogContent class="max-w-4xl">
                <div v-if="selectedImage" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="font-medium">{{ selectedImage.name }}</p>
                        <a
                            :href="selectedImage.url"
                            :download="selectedImage.name"
                            target="_blank"
                        >
                            <Button variant="outline" size="sm">
                                <Download class="mr-2 size-4" />
                                Download
                            </Button>
                        </a>
                    </div>
                    <img
                        :src="selectedImage.url"
                        :alt="selectedImage.name"
                        class="w-full rounded-lg"
                    />
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

