<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as transactionsIndex, create as transactionsCreate, show as transactionsShow, edit as transactionsEdit, destroy as transactionsDestroy } from '@/actions/App/Http/Controllers/TransactionController';
import {
    type BreadcrumbItem,
    type PaginatedData,
    type Transaction,
    type TransactionSummary,
} from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
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
import { Pencil, Trash2, Plus, Search, Eye, TrendingUp, TrendingDown, DollarSign } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Props {
    transactions: PaginatedData<Transaction>;
    filters: {
        search?: string;
        type?: string;
        status?: string;
        start_date?: string;
        end_date?: string;
    };
    summary: TransactionSummary;
}

const props = defineProps<Props>();

const searchForm = useForm({
    search: props.filters.search || '',
    type: props.filters.type || 'all',
    status: props.filters.status || 'all',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const deleteForm = useForm({});
const showDeleteDialog = ref(false);
const transactionToDelete = ref<Transaction | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Transactions',
        href: transactionsIndex().url,
    },
];

function search() {
    searchForm.get(transactionsIndex().url, {
        preserveState: true,
        preserveScroll: true,
    });
}

function confirmDelete(transaction: Transaction) {
    transactionToDelete.value = transaction;
    showDeleteDialog.value = true;
}

function deleteTransaction() {
    if (!transactionToDelete.value) {
        return;
    }

    deleteForm.delete(transactionsDestroy(transactionToDelete.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            transactionToDelete.value = null;
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

const canEdit = computed(() => (transaction: Transaction) => {
    return transaction.status === 'pending';
});

const canDelete = computed(() => (transaction: Transaction) => {
    return transaction.status === 'pending';
});
</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Total Cash In
                        </CardTitle>
                        <TrendingUp class="size-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ formatCurrency(summary.total_in) }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Total Cash Out
                        </CardTitle>
                        <TrendingDown class="size-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ formatCurrency(summary.total_out) }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Net Balance
                        </CardTitle>
                        <DollarSign class="size-4" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold"
                            :class="summary.net_balance >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            {{ formatCurrency(summary.net_balance) }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Transactions Table Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Transactions</CardTitle>
                            <CardDescription>
                                Manage petty cash transactions
                            </CardDescription>
                        </div>
                        <Link :href="transactionsCreate().url">
                            <Button>
                                <Plus class="mr-2 size-4" />
                                New Transaction
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Search and Filters -->
                    <div class="mb-4 grid gap-4 md:grid-cols-6">
                        <div class="relative md:col-span-2">
                            <Search
                                class="absolute left-2 top-2.5 size-4 text-muted-foreground"
                            />
                            <Input
                                v-model="searchForm.search"
                                placeholder="Search transactions..."
                                class="pl-8"
                                @keyup.enter="search"
                            />
                        </div>

                        <Select v-model="searchForm.type">
                            <SelectTrigger>
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="in">Cash In</SelectItem>
                                <SelectItem value="out">Cash Out</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="searchForm.status">
                            <SelectTrigger>
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="approved">Approved</SelectItem>
                                <SelectItem value="rejected">Rejected</SelectItem>
                            </SelectContent>
                        </Select>

                        <Input
                            v-model="searchForm.start_date"
                            type="date"
                            placeholder="Start Date"
                        />

                        <Input
                            v-model="searchForm.end_date"
                            type="date"
                            placeholder="End Date"
                        />
                    </div>

                    <div class="mb-4 flex gap-2">
                        <Button @click="search"> Apply Filters </Button>
                        <Button
                            variant="outline"
                            @click="() => {
                                searchForm.reset();
                                search();
                            }"
                        >
                            Clear
                        </Button>
                    </div>

                    <!-- Transactions Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Transaction #</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Created By</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-if="transactions.data.length === 0"
                                >
                                    <TableCell colspan="8" class="text-center text-muted-foreground">
                                        No transactions found
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="transaction in transactions.data"
                                    :key="transaction.id"
                                >
                                    <TableCell class="font-medium">
                                        {{ transaction.transaction_number }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(transaction.transaction_date) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :class="getTypeColor(transaction.type)"
                                            variant="outline"
                                        >
                                            {{ transaction.type === 'in' ? 'Cash In' : 'Cash Out' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell :class="getTypeColor(transaction.type)" class="font-semibold">
                                        {{ formatCurrency(transaction.amount) }}
                                    </TableCell>
                                    <TableCell class="max-w-xs truncate">
                                        {{ transaction.description }}
                                    </TableCell>
                                    <TableCell>
                                        {{ transaction.user?.name }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(transaction.status)">
                                            {{ transaction.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="transactionsShow(transaction.id).url"
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                >
                                                    <Eye class="size-4" />
                                                </Button>
                                            </Link>
                                            <Link
                                                v-if="canEdit(transaction)"
                                                :href="transactionsEdit(transaction.id).url"
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                >
                                                    <Pencil class="size-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                v-if="canDelete(transaction)"
                                                variant="ghost"
                                                size="icon"
                                                @click="confirmDelete(transaction)"
                                            >
                                                <Trash2 class="size-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="transactions.links.length > 3"
                        class="mt-4 flex justify-center gap-1"
                    >
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, index) in transactions.links"
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

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Transaction</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete transaction
                        {{ transactionToDelete?.transaction_number }}? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showDeleteDialog = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="deleteForm.processing"
                        @click="deleteTransaction"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

