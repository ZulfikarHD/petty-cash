<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as transactionsIndex, show as transactionsShow } from '@/actions/App/Http/Controllers/TransactionController';
import { index as approvalsIndex } from '@/actions/App/Http/Controllers/ApprovalController';
import { overview as budgetsOverview } from '@/actions/App/Http/Controllers/BudgetController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Users, UserCheck, UserPlus, Receipt, Clock, TrendingUp, TrendingDown, AlertTriangle, DollarSign, Wallet, ClipboardCheck, FileCheck } from 'lucide-vue-next';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

interface TransactionSummary {
    id: number;
    transaction_number: string;
    type: string;
    amount: string | number;
    description: string;
    transaction_date: string;
    status: string;
    user: {
        name: string;
    };
}

interface BudgetAlert {
    id: number;
    category: {
        id: number;
        name: string;
        color: string;
    };
    amount: string;
    spent_amount: number;
    remaining_amount: number;
    percentage_spent: number;
    is_exceeded: boolean;
    severity: 'danger' | 'warning';
    message: string;
}

interface ApprovalStats {
    pendingApprovals?: number;
    myPendingSubmissions?: number;
}

interface Props {
    stats?: {
        totalUsers?: number;
        verifiedUsers?: number;
        recentUsers?: number;
        totalTransactions?: number;
        pendingTransactions?: number;
        todayCashIn?: number;
        todayCashOut?: number;
        recentTransactions?: TransactionSummary[];
        currentBalance?: number;
        lowBalanceAlert?: boolean;
        lowBalanceThreshold?: number;
    };
    budgetAlerts?: BudgetAlert[];
    approvalStats?: ApprovalStats;
}

defineProps<Props>();

const page = usePage();

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

function getStatusVariant(status: string) {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        pending: 'secondary',
        approved: 'default',
        rejected: 'destructive',
    };
    return variants[status] || 'outline';
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- User Statistics (only if user can view users) -->
            <div v-if="stats && page.props.auth?.can?.viewUsers" class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Total Users
                        </CardTitle>
                        <Users class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalUsers || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            All registered users
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Verified Users
                        </CardTitle>
                        <UserCheck class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.verifiedUsers || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            Email verified accounts
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            New Users (30 days)
                        </CardTitle>
                        <UserPlus class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.recentUsers || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            Registered this month
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Current Balance Card (prominent display) -->
            <div v-if="stats && page.props.auth?.can?.viewTransactions" class="grid auto-rows-min gap-4 md:grid-cols-1">
                <Card :class="{ 'border-destructive': stats.lowBalanceAlert }">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Current Cash Balance
                        </CardTitle>
                        <div class="flex items-center gap-2">
                            <Link href="/cash-balances" class="text-xs text-primary hover:underline">
                                Manage Balance
                            </Link>
                            <Wallet class="size-4 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold" :class="{ 'text-destructive': stats.lowBalanceAlert }">
                            {{ formatCurrency(stats.currentBalance || 0) }}
                        </div>
                        <div v-if="stats.lowBalanceAlert" class="mt-2 flex items-center gap-1 text-sm text-destructive">
                            <AlertTriangle class="size-4" />
                            Low balance alert: Below {{ formatCurrency(stats.lowBalanceThreshold || 0) }} threshold
                        </div>
                        <p v-else class="text-xs text-muted-foreground">
                            Total available petty cash
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Transaction Statistics (only if user can view transactions) -->
            <div v-if="stats && page.props.auth?.can?.viewTransactions" class="grid auto-rows-min gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Total Transactions
                        </CardTitle>
                        <Receipt class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalTransactions || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            All time transactions
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Pending
                        </CardTitle>
                        <Clock class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pendingTransactions || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            Awaiting approval
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Today's Cash In
                        </CardTitle>
                        <TrendingUp class="size-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ formatCurrency(stats.todayCashIn || 0) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Cash received today
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Today's Cash Out
                        </CardTitle>
                        <TrendingDown class="size-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ formatCurrency(stats.todayCashOut || 0) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Cash spent today
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Approval Stats -->
            <div v-if="approvalStats && (approvalStats.pendingApprovals !== undefined || approvalStats.myPendingSubmissions !== undefined)" class="grid auto-rows-min gap-4 md:grid-cols-2">
                <!-- Pending Approvals (for approvers) -->
                <Card v-if="approvalStats.pendingApprovals !== undefined" :class="{ 'border-yellow-500': approvalStats.pendingApprovals > 0 }">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Pending Approvals
                        </CardTitle>
                        <ClipboardCheck class="size-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold" :class="{ 'text-yellow-600': approvalStats.pendingApprovals > 0 }">
                            {{ approvalStats.pendingApprovals }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Transactions awaiting your review
                        </p>
                        <Link v-if="approvalStats.pendingApprovals > 0" :href="approvalsIndex().url" class="mt-2 inline-block text-sm text-primary hover:underline">
                            Review now →
                        </Link>
                    </CardContent>
                </Card>

                <!-- My Pending Submissions (for requesters) -->
                <Card v-if="approvalStats.myPendingSubmissions !== undefined">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            My Pending Requests
                        </CardTitle>
                        <FileCheck class="size-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ approvalStats.myPendingSubmissions }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Your transactions awaiting approval
                        </p>
                        <Link :href="transactionsIndex().url + '?status=pending'" class="mt-2 inline-block text-sm text-primary hover:underline">
                            View requests →
                        </Link>
                    </CardContent>
                </Card>
            </div>

            <!-- Budget Alerts -->
            <Card v-if="budgetAlerts && budgetAlerts.length > 0">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <AlertTriangle class="size-5 text-yellow-500" />
                            <div>
                                <CardTitle>Budget Alerts</CardTitle>
                                <CardDescription>
                                    Categories approaching or exceeding budget limits
                                </CardDescription>
                            </div>
                        </div>
                        <Link :href="budgetsOverview().url">
                            <span class="text-sm text-primary hover:underline">View all budgets</span>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="alert in budgetAlerts"
                            :key="alert.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                            :class="
                                alert.severity === 'danger'
                                    ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950'
                                    : 'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-950'
                            "
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="size-10 rounded-lg"
                                    :style="{ backgroundColor: alert.category.color }"
                                ></div>
                                <div>
                                    <div class="font-semibold">
                                        {{ alert.category.name }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ alert.message }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold">
                                    {{ formatCurrency(alert.spent_amount) }} /
                                    {{ formatCurrency(alert.amount) }}
                                </div>
                                <Badge
                                    :variant="
                                        alert.severity === 'danger'
                                            ? 'destructive'
                                            : 'secondary'
                                    "
                                >
                                    {{ alert.percentage_spent.toFixed(1) }}%
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Transactions -->
            <Card v-if="stats?.recentTransactions && stats.recentTransactions.length > 0">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Recent Transactions</CardTitle>
                            <CardDescription>
                                Latest 5 transactions
                            </CardDescription>
                        </div>
                        <Link :href="transactionsIndex().url">
                            <span class="text-sm text-primary hover:underline">View all</span>
                        </Link>
                    </div>
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
                                    <TableHead>Description</TableHead>
                                    <TableHead>Created By</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="transaction in stats.recentTransactions"
                                    :key="transaction.id"
                                >
                                    <TableCell class="font-medium">
                                        <Link :href="transactionsShow(transaction.id).url" class="hover:underline">
                                            {{ transaction.transaction_number }}
                                        </Link>
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(transaction.transaction_date) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :class="transaction.type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                            variant="outline"
                                        >
                                            {{ transaction.type === 'in' ? 'Cash In' : 'Cash Out' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        :class="transaction.type === 'in' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
                                    >
                                        {{ formatCurrency(transaction.amount) }}
                                    </TableCell>
                                    <TableCell class="max-w-xs truncate">
                                        {{ transaction.description }}
                                    </TableCell>
                                    <TableCell>
                                        {{ transaction.user.name }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(transaction.status)">
                                            {{ transaction.status }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Default placeholder if no stats -->
            <div v-if="!stats || (!page.props.auth?.can?.viewUsers && !page.props.auth?.can?.viewTransactions)" class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
