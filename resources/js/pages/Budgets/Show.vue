<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as budgetsIndex,
    edit as budgetsEdit,
} from '@/actions/App/Http/Controllers/BudgetController';
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
import { Edit, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    color: string;
}

interface Budget {
    id: number;
    category: Category;
    amount: string;
    start_date: string;
    end_date: string;
    alert_threshold: string;
    spent_amount: number;
    remaining_amount: number;
    percentage_spent: number;
    is_exceeded: boolean;
    is_alert_threshold_reached: boolean;
}

interface Transaction {
    id: number;
    transaction_number: string;
    amount: string;
    description: string;
    transaction_date: string;
    user: {
        name: string;
    };
}

interface Props {
    budget: Budget;
    transactions: Transaction[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Budgets',
        href: budgetsIndex().url,
    },
    {
        title: props.budget.category.name,
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
</script>

<template>
    <Head :title="`Budget: ${budget.category.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Budget Details Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="size-12 rounded-lg"
                                :style="{ backgroundColor: budget.category.color }"
                            ></div>
                            <div>
                                <CardTitle>{{ budget.category.name }}</CardTitle>
                                <CardDescription>
                                    {{ formatDate(budget.start_date) }} -
                                    {{ formatDate(budget.end_date) }}
                                </CardDescription>
                            </div>
                        </div>
                        <Link
                            v-if="$page.props.auth.user.permissions.includes('edit-budgets')"
                            :href="budgetsEdit(budget.id).url"
                        >
                            <Button>
                                <Edit class="mr-2 size-4" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Alert Banner -->
                    <div
                        v-if="budget.is_exceeded"
                        class="mb-6 flex items-center gap-2 rounded-md border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
                    >
                        <AlertTriangle class="size-5" />
                        <div>
                            <div class="font-semibold">Budget Exceeded</div>
                            <div class="text-sm">
                                Spending has exceeded the allocated budget by
                                {{ formatCurrency(Math.abs(budget.remaining_amount)) }}
                            </div>
                        </div>
                    </div>
                    <div
                        v-else-if="budget.is_alert_threshold_reached"
                        class="mb-6 flex items-center gap-2 rounded-md border border-yellow-200 bg-yellow-50 p-4 text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200"
                    >
                        <AlertTriangle class="size-5" />
                        <div>
                            <div class="font-semibold">Budget Alert</div>
                            <div class="text-sm">
                                {{ budget.percentage_spent.toFixed(1) }}% of budget has
                                been spent
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mb-6 flex items-center gap-2 rounded-md border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                    >
                        <CheckCircle2 class="size-5" />
                        <div>
                            <div class="font-semibold">On Track</div>
                            <div class="text-sm">
                                Budget is within normal limits
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Budget Amount
                            </div>
                            <div class="mt-1 text-2xl font-bold">
                                {{ formatCurrency(budget.amount) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Spent
                            </div>
                            <div class="mt-1 text-2xl font-bold text-red-600">
                                {{ formatCurrency(budget.spent_amount) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Remaining
                            </div>
                            <div
                                class="mt-1 text-2xl font-bold"
                                :class="
                                    budget.remaining_amount < 0
                                        ? 'text-red-600'
                                        : 'text-green-600'
                                "
                            >
                                {{ formatCurrency(budget.remaining_amount) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Percentage
                            </div>
                            <div class="mt-1 text-2xl font-bold">
                                {{ budget.percentage_spent.toFixed(1) }}%
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium">Progress</span>
                            <span class="text-sm text-muted-foreground">
                                Alert at {{ budget.alert_threshold }}%
                            </span>
                        </div>
                        <div
                            class="h-4 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full transition-all"
                                :class="
                                    budget.is_exceeded
                                        ? 'bg-red-500'
                                        : budget.is_alert_threshold_reached
                                          ? 'bg-yellow-500'
                                          : 'bg-green-500'
                                "
                                :style="{
                                    width: `${Math.min(budget.percentage_spent, 100)}%`,
                                }"
                            ></div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Transactions Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Transactions</CardTitle>
                    <CardDescription>
                        Approved cash-out transactions in this budget period
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Transaction #</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>User</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="transactions.length === 0">
                                    <TableCell
                                        colspan="5"
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
                                    <TableCell class="text-red-600">
                                        {{ formatCurrency(transaction.amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <span class="line-clamp-1">{{
                                            transaction.description
                                        }}</span>
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
        </div>
    </AppLayout>
</template>

