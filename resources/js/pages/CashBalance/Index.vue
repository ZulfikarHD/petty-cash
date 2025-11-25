<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as cashBalancesIndex,
    show as cashBalancesShow,
    create as cashBalancesCreate,
    reconcile as cashBalancesReconcile,
    destroy as cashBalancesDestroy,
} from '@/actions/App/Http/Controllers/CashBalanceController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Plus,
    Eye,
    Trash2,
    CheckCircle,
    DollarSign,
    TrendingUp,
    TrendingDown,
    AlertTriangle,
    Calculator,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface User {
    id: number;
    name: string;
}

interface CashBalance {
    id: number;
    period_start: string;
    period_end: string;
    opening_balance: string;
    closing_balance: string | null;
    calculated_balance: number;
    cash_in: number;
    cash_out: number;
    status: 'active' | 'reconciled' | 'closed';
    reconciliation_date: string | null;
    reconciled_by: User | null;
    created_by: User | null;
    has_discrepancy: boolean;
    discrepancy_amount: string | null;
    notes: string | null;
    created_at: string;
}

interface TodaySummary {
    date: string;
    cash_in: number;
    cash_out: number;
    net_flow: number;
}

interface Props {
    cashBalances: {
        data: CashBalance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        status?: string;
    };
    currentBalance: number;
    lowBalanceAlert: boolean;
    lowBalanceThreshold: number;
    todaySummary: TodaySummary;
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
];

const status = ref(props.filters.status || 'all');

watch(status, () => {
    router.get(
        cashBalancesIndex().url,
        { status: status.value === 'all' ? undefined : status.value },
        { preserveState: true, replace: true }
    );
});

function deleteCashBalance(cashBalance: CashBalance) {
    if (
        confirm(
            `Are you sure you want to delete this cash balance period?`
        )
    ) {
        router.delete(cashBalancesDestroy(cashBalance.id).url);
    }
}

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
    <Head title="Cash Balance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <!-- Current Balance Card -->
                <Card :class="{ 'border-destructive': lowBalanceAlert }">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Current Balance
                        </CardTitle>
                        <DollarSign class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold" :class="{ 'text-destructive': lowBalanceAlert }">
                            {{ formatCurrency(currentBalance) }}
                        </div>
                        <div v-if="lowBalanceAlert" class="mt-1 flex items-center gap-1 text-xs text-destructive">
                            <AlertTriangle class="size-3" />
                            Below {{ formatCurrency(lowBalanceThreshold) }} threshold
                        </div>
                    </CardContent>
                </Card>

                <!-- Today's Cash In -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Today's Cash In
                        </CardTitle>
                        <TrendingUp class="size-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ formatCurrency(todaySummary.cash_in) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(todaySummary.date) }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Today's Cash Out -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Today's Cash Out
                        </CardTitle>
                        <TrendingDown class="size-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ formatCurrency(todaySummary.cash_out) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(todaySummary.date) }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Net Flow -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Today's Net Flow
                        </CardTitle>
                        <Calculator class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold"
                            :class="{
                                'text-green-600': todaySummary.net_flow >= 0,
                                'text-red-600': todaySummary.net_flow < 0,
                            }"
                        >
                            {{ formatCurrency(todaySummary.net_flow) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Cash In - Cash Out
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Balance Periods Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Balance Periods</CardTitle>
                            <CardDescription>
                                Manage cash balance periods and reconciliation
                            </CardDescription>
                        </div>
                        <Link
                            v-if="$page.props.auth.user.permissions.includes('manage-transactions')"
                            :href="cashBalancesCreate().url"
                        >
                            <Button>
                                <Plus class="mr-2 size-4" />
                                New Period
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Filters -->
                    <div class="mb-6 flex gap-4">
                        <Select v-model="status">
                            <SelectTrigger class="w-48">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="reconciled">Reconciled</SelectItem>
                                <SelectItem value="closed">Closed</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Opening Balance</TableHead>
                                    <TableHead>Cash In</TableHead>
                                    <TableHead>Cash Out</TableHead>
                                    <TableHead>Calculated Balance</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="cashBalances.data.length === 0">
                                    <TableCell
                                        colspan="7"
                                        class="text-center text-muted-foreground"
                                    >
                                        No balance periods found
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="balance in cashBalances.data"
                                    :key="balance.id"
                                >
                                    <TableCell>
                                        <div class="font-medium">
                                            {{ formatDate(balance.period_start) }} -
                                            {{ formatDate(balance.period_end) }}
                                        </div>
                                        <div v-if="balance.created_by" class="text-xs text-muted-foreground">
                                            Created by {{ balance.created_by.name }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ formatCurrency(balance.opening_balance) }}
                                    </TableCell>
                                    <TableCell class="text-green-600">
                                        +{{ formatCurrency(balance.cash_in) }}
                                    </TableCell>
                                    <TableCell class="text-red-600">
                                        -{{ formatCurrency(balance.cash_out) }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium">
                                            {{ formatCurrency(balance.calculated_balance) }}
                                        </div>
                                        <div v-if="balance.has_discrepancy" class="flex items-center gap-1 text-xs text-destructive">
                                            <AlertTriangle class="size-3" />
                                            Discrepancy: {{ formatCurrency(balance.discrepancy_amount || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(balance.status)">
                                            {{ balance.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="cashBalancesShow(balance.id).url">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="size-4" />
                                                </Button>
                                            </Link>
                                            <Link
                                                v-if="balance.status === 'active' && $page.props.auth.user.permissions.includes('manage-transactions')"
                                                :href="cashBalancesReconcile(balance.id).url"
                                            >
                                                <Button variant="ghost" size="sm" title="Reconcile">
                                                    <CheckCircle class="size-4 text-green-600" />
                                                </Button>
                                            </Link>
                                            <Button
                                                v-if="balance.status === 'active' && $page.props.auth.user.permissions.includes('manage-transactions')"
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteCashBalance(balance)"
                                            >
                                                <Trash2 class="size-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="cashBalances.last_page > 1"
                        class="mt-4 flex items-center justify-between"
                    >
                        <div class="text-sm text-muted-foreground">
                            Showing
                            {{ (cashBalances.current_page - 1) * cashBalances.per_page + 1 }} to
                            {{
                                Math.min(
                                    cashBalances.current_page * cashBalances.per_page,
                                    cashBalances.total
                                )
                            }}
                            of {{ cashBalances.total }} periods
                        </div>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in cashBalances.links"
                                :key="link.label"
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                :disabled="!link.url"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                            ></Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

