<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as cashBalancesIndex,
    reconcile as cashBalancesReconcile,
} from '@/actions/App/Http/Controllers/CashBalanceController';
import { type BreadcrumbItem } from '@/types';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    CheckCircle,
    AlertTriangle,
    TrendingUp,
    TrendingDown,
    ArrowLeft,
    Calendar,
} from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
    color: string;
}

interface CashBalance {
    id: number;
    period_start: string;
    period_end: string;
    opening_balance: string;
    closing_balance: string | null;
    status: 'active' | 'reconciled' | 'closed';
    reconciliation_date: string | null;
    reconciled_by: User | null;
    created_by: User | null;
    has_discrepancy: boolean;
    discrepancy_amount: string | null;
    discrepancy_notes: string | null;
    notes: string | null;
    created_at: string;
}

interface PeriodBalance {
    opening_balance: number;
    cash_in: number;
    cash_out: number;
    net_flow: number;
    closing_balance: number;
    period_start: string;
    period_end: string;
}

interface Transaction {
    id: number;
    transaction_number: string;
    type: 'in' | 'out';
    amount: string;
    description: string;
    transaction_date: string;
    category: Category | null;
    user: {
        name: string;
    };
}

interface BalanceHistoryItem {
    date: string;
    cash_in: number;
    cash_out: number;
    net_flow: number;
    balance: number;
    transaction_count: number;
}

interface Props {
    cashBalance: CashBalance;
    periodBalance: PeriodBalance;
    transactions: Transaction[];
    balanceHistory: BalanceHistoryItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Cash Balance',
        href: cashBalancesIndex().url,
    },
    {
        title: `${formatDate(props.cashBalance.period_start)} - ${formatDate(props.cashBalance.period_end)}`,
        href: '',
    },
];

function formatCurrency(amount: string | number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function formatDateTime(date: string): string {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusVariant(status: string): 'default' | 'secondary' | 'outline' {
    const variants: Record<string, 'default' | 'secondary' | 'outline'> = {
        active: 'default',
        reconciled: 'secondary',
        closed: 'outline',
    };
    return variants[status] || 'outline';
}
</script>

<template>
    <Head title="Cash Balance Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <Calendar class="size-8 text-muted-foreground" />
                            <div>
                                <CardTitle>
                                    {{ formatDate(cashBalance.period_start) }} -
                                    {{ formatDate(cashBalance.period_end) }}
                                </CardTitle>
                                <CardDescription>
                                    <Badge :variant="getStatusVariant(cashBalance.status)" class="mt-1">
                                        {{ cashBalance.status }}
                                    </Badge>
                                    <span v-if="cashBalance.created_by" class="ml-2">
                                        Created by {{ cashBalance.created_by.name }}
                                    </span>
                                </CardDescription>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-if="cashBalance.status === 'active' && $page.props.auth.user.permissions.includes('manage-transactions')"
                                :href="cashBalancesReconcile(cashBalance.id).url"
                            >
                                <Button>
                                    <CheckCircle class="mr-2 size-4" />
                                    Reconcile
                                </Button>
                            </Link>
                            <Link :href="cashBalancesIndex().url">
                                <Button variant="outline">
                                    <ArrowLeft class="mr-2 size-4" />
                                    Back
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Discrepancy Alert -->
                    <div
                        v-if="cashBalance.has_discrepancy"
                        class="mb-6 flex items-center gap-2 rounded-md border border-yellow-200 bg-yellow-50 p-4 text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200"
                    >
                        <AlertTriangle class="size-5" />
                        <div>
                            <div class="font-semibold">Reconciliation Discrepancy</div>
                            <div class="text-sm">
                                Discrepancy of {{ formatCurrency(cashBalance.discrepancy_amount || 0) }} was recorded.
                                <span v-if="cashBalance.discrepancy_notes">
                                    Notes: {{ cashBalance.discrepancy_notes }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Reconciliation Info -->
                    <div
                        v-if="cashBalance.status === 'reconciled' && cashBalance.reconciled_by"
                        class="mb-6 flex items-center gap-2 rounded-md border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                    >
                        <CheckCircle class="size-5" />
                        <div>
                            <div class="font-semibold">Reconciled</div>
                            <div class="text-sm">
                                Reconciled by {{ cashBalance.reconciled_by.name }}
                                <span v-if="cashBalance.reconciliation_date">
                                    on {{ formatDateTime(cashBalance.reconciliation_date) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid gap-4 md:grid-cols-5">
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Opening Balance
                            </div>
                            <div class="mt-1 text-2xl font-bold">
                                {{ formatCurrency(periodBalance.opening_balance) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                <TrendingUp class="size-3 text-green-500" />
                                Cash In
                            </div>
                            <div class="mt-1 text-2xl font-bold text-green-600">
                                +{{ formatCurrency(periodBalance.cash_in) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                <TrendingDown class="size-3 text-red-500" />
                                Cash Out
                            </div>
                            <div class="mt-1 text-2xl font-bold text-red-600">
                                -{{ formatCurrency(periodBalance.cash_out) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Net Flow
                            </div>
                            <div
                                class="mt-1 text-2xl font-bold"
                                :class="{
                                    'text-green-600': periodBalance.net_flow >= 0,
                                    'text-red-600': periodBalance.net_flow < 0,
                                }"
                            >
                                {{ formatCurrency(periodBalance.net_flow) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Closing Balance
                            </div>
                            <div class="mt-1 text-2xl font-bold">
                                {{ formatCurrency(periodBalance.closing_balance) }}
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="cashBalance.notes" class="mt-4 rounded-lg border p-4">
                        <div class="text-sm font-medium text-muted-foreground">Notes</div>
                        <div class="mt-1">{{ cashBalance.notes }}</div>
                    </div>
                </CardContent>
            </Card>

            <!-- Transactions Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Transactions</CardTitle>
                    <CardDescription>
                        Approved transactions in this balance period
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Transaction #</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>User</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="transactions.length === 0">
                                    <TableCell
                                        colspan="7"
                                        class="text-center text-muted-foreground"
                                    >
                                        No transactions found
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="transaction in transactions"
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
                                            :variant="transaction.type === 'in' ? 'default' : 'secondary'"
                                        >
                                            {{ transaction.type === 'in' ? 'Cash In' : 'Cash Out' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        :class="{
                                            'text-green-600': transaction.type === 'in',
                                            'text-red-600': transaction.type === 'out',
                                        }"
                                    >
                                        {{ transaction.type === 'in' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="transaction.category" class="flex items-center gap-2">
                                            <div
                                                class="size-3 rounded"
                                                :style="{ backgroundColor: transaction.category.color }"
                                            ></div>
                                            {{ transaction.category.name }}
                                        </div>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <span class="line-clamp-1">{{ transaction.description }}</span>
                                    </TableCell>
                                    <TableCell>
                                        {{ transaction.user.name }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Daily Balance History Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Daily Balance History</CardTitle>
                    <CardDescription>
                        Running balance for each day in the period
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Cash In</TableHead>
                                    <TableHead>Cash Out</TableHead>
                                    <TableHead>Net Flow</TableHead>
                                    <TableHead>Running Balance</TableHead>
                                    <TableHead>Transactions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="balanceHistory.length === 0">
                                    <TableCell
                                        colspan="6"
                                        class="text-center text-muted-foreground"
                                    >
                                        No history available
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="day in balanceHistory"
                                    :key="day.date"
                                    :class="{
                                        'bg-muted/50': day.transaction_count > 0,
                                    }"
                                >
                                    <TableCell class="font-medium">
                                        {{ formatDate(day.date) }}
                                    </TableCell>
                                    <TableCell class="text-green-600">
                                        {{ day.cash_in > 0 ? '+' : '' }}{{ formatCurrency(day.cash_in) }}
                                    </TableCell>
                                    <TableCell class="text-red-600">
                                        {{ day.cash_out > 0 ? '-' : '' }}{{ formatCurrency(day.cash_out) }}
                                    </TableCell>
                                    <TableCell
                                        :class="{
                                            'text-green-600': day.net_flow > 0,
                                            'text-red-600': day.net_flow < 0,
                                        }"
                                    >
                                        {{ formatCurrency(day.net_flow) }}
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ formatCurrency(day.balance) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ day.transaction_count }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

