<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as approvalsIndex, show as approvalsShow, approve as approvalsApprove, reject as approvalsReject } from '@/actions/App/Http/Controllers/ApprovalController';
import { type BreadcrumbItem, type PaginatedData, type User, type Transaction, type Category } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Eye, Check, X, Clock, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

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
    transaction: Transaction & { category?: Category };
    submitted_by_user: User;
    reviewed_by_user?: User;
}

interface Props {
    approvals: PaginatedData<ApprovalData>;
    stats: {
        pending: number;
        approved: number;
        rejected: number;
    };
    filters: {
        status: string;
    };
}

const props = defineProps<Props>();

const filterForm = useForm({
    status: props.filters.status || 'pending',
});

const approveForm = useForm({});
const rejectForm = useForm({
    rejection_reason: '',
});

const showApproveDialog = ref(false);
const showRejectDialog = ref(false);
const selectedApproval = ref<ApprovalData | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Approvals',
        href: approvalsIndex().url,
    },
];

function applyFilter() {
    filterForm.get(approvalsIndex().url, {
        preserveState: true,
        preserveScroll: true,
    });
}

function confirmApprove(approval: ApprovalData) {
    selectedApproval.value = approval;
    showApproveDialog.value = true;
}

function confirmReject(approval: ApprovalData) {
    selectedApproval.value = approval;
    rejectForm.rejection_reason = '';
    showRejectDialog.value = true;
}

function submitApprove() {
    if (!selectedApproval.value) return;

    approveForm.post(approvalsApprove(selectedApproval.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showApproveDialog.value = false;
            selectedApproval.value = null;
        },
    });
}

function submitReject() {
    if (!selectedApproval.value) return;

    rejectForm.post(approvalsReject(selectedApproval.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showRejectDialog.value = false;
            selectedApproval.value = null;
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
        month: 'short',
        day: 'numeric',
    });
}

function formatDateTime(date: string) {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Approvals" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Clock class="size-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ stats.pending }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved</CardTitle>
                        <CheckCircle class="size-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ stats.approved }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Rejected</CardTitle>
                        <XCircle class="size-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ stats.rejected }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Approvals Table Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Approval Requests</CardTitle>
                            <CardDescription>
                                Review and process transaction approval requests
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Filters -->
                    <div class="mb-4 flex gap-4">
                        <Select v-model="filterForm.status" @update:model-value="applyFilter">
                            <SelectTrigger class="w-48">
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="approved">Approved</SelectItem>
                                <SelectItem value="rejected">Rejected</SelectItem>
                                <SelectItem value="all">All</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Approvals Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Transaction #</TableHead>
                                    <TableHead>Submitted By</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="approvals.data.length === 0">
                                    <TableCell colspan="8" class="text-center text-muted-foreground">
                                        No approval requests found
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="approval in approvals.data"
                                    :key="approval.id"
                                >
                                    <TableCell class="font-medium">
                                        {{ approval.transaction.transaction_number }}
                                    </TableCell>
                                    <TableCell>
                                        {{ approval.submitted_by_user.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDateTime(approval.submitted_at) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :class="getTypeColor(approval.transaction.type)"
                                            variant="outline"
                                        >
                                            {{ approval.transaction.type === 'in' ? 'Cash In' : 'Cash Out' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell :class="getTypeColor(approval.transaction.type)" class="font-semibold">
                                        {{ formatCurrency(approval.transaction.amount) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ approval.transaction.category?.name || '-' }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(approval.status)">
                                            {{ approval.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="approvalsShow(approval.id).url">
                                                <Button variant="ghost" size="icon">
                                                    <Eye class="size-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                v-if="approval.status === 'pending'"
                                                variant="ghost"
                                                size="icon"
                                                class="text-green-600 hover:text-green-700 hover:bg-green-50"
                                                @click="confirmApprove(approval)"
                                            >
                                                <Check class="size-4" />
                                            </Button>
                                            <Button
                                                v-if="approval.status === 'pending'"
                                                variant="ghost"
                                                size="icon"
                                                class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                                @click="confirmReject(approval)"
                                            >
                                                <X class="size-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="approvals.links.length > 3"
                        class="mt-4 flex justify-center gap-1"
                    >
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, index) in approvals.links"
                            :key="index"
                            :href="link.url || ''"
                            :class="[
                                'px-3 py-1 border rounded',
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-accent',
                                !link.url && 'opacity-50 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Approve Confirmation Dialog -->
        <Dialog v-model:open="showApproveDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Approve Transaction</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to approve transaction
                        {{ selectedApproval?.transaction.transaction_number }}
                        for {{ selectedApproval ? formatCurrency(selectedApproval.transaction.amount) : '' }}?
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
                        {{ selectedApproval?.transaction.transaction_number }}.
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
    </AppLayout>
</template>

