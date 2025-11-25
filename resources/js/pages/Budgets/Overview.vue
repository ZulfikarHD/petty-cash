<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as budgetsIndex,
    show as budgetsShow,
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
import { AlertTriangle, CheckCircle2, List } from 'lucide-vue-next';

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

interface Alert {
    id: number;
    category: Category;
    amount: string;
    spent_amount: number;
    remaining_amount: number;
    percentage_spent: number;
    is_exceeded: boolean;
    severity: 'danger' | 'warning';
    message: string;
}

interface Props {
    activeBudgets: Budget[];
    alerts: Alert[];
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
        title: 'Overview',
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

function getProgressBarColor(budget: Budget): string {
    if (budget.is_exceeded) {
        return 'bg-red-500';
    }
    if (budget.is_alert_threshold_reached) {
        return 'bg-yellow-500';
    }
    return 'bg-green-500';
}
</script>

<template>
    <Head title="Budget Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Budget Overview</h1>
                    <p class="text-muted-foreground">
                        Monitor all active budgets and spending
                    </p>
                </div>
                <Link :href="budgetsIndex().url">
                    <Button variant="outline">
                        <List class="mr-2 size-4" />
                        View All Budgets
                    </Button>
                </Link>
            </div>

            <!-- Alerts Card -->
            <Card v-if="alerts.length > 0">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <AlertTriangle class="size-5 text-yellow-500" />
                        <CardTitle>Budget Alerts</CardTitle>
                    </div>
                    <CardDescription>
                        Categories that have exceeded or are approaching their budget limits
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="alert in alerts"
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

            <!-- No Alerts Message -->
            <Card v-else>
                <CardContent class="py-12">
                    <div class="flex flex-col items-center justify-center text-center">
                        <CheckCircle2 class="mb-4 size-12 text-green-500" />
                        <h3 class="text-lg font-semibold">All Budgets On Track</h3>
                        <p class="text-muted-foreground">
                            No budget alerts at this time
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Active Budgets Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Active Budgets</CardTitle>
                    <CardDescription>
                        Currently active budget allocations
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="activeBudgets.length === 0" class="py-8 text-center">
                        <p class="text-muted-foreground">No active budgets</p>
                    </div>
                    <div v-else class="space-y-6">
                        <Link
                            v-for="budget in activeBudgets"
                            :key="budget.id"
                            :href="budgetsShow(budget.id).url"
                            class="block rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-10 rounded-lg"
                                        :style="{ backgroundColor: budget.category.color }"
                                    ></div>
                                    <div>
                                        <div class="font-semibold">
                                            {{ budget.category.name }}
                                        </div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ formatDate(budget.start_date) }} -
                                            {{ formatDate(budget.end_date) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-muted-foreground">
                                        Budget
                                    </div>
                                    <div class="font-semibold">
                                        {{ formatCurrency(budget.amount) }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">
                                        {{ formatCurrency(budget.spent_amount) }} spent
                                    </span>
                                    <span
                                        class="font-medium"
                                        :class="
                                            budget.remaining_amount < 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{ formatCurrency(budget.remaining_amount) }}
                                        remaining
                                    </span>
                                </div>
                                <div
                                    class="h-3 w-full overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full transition-all"
                                        :class="getProgressBarColor(budget)"
                                        :style="{
                                            width: `${Math.min(budget.percentage_spent, 100)}%`,
                                        }"
                                    ></div>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-muted-foreground">
                                        {{ budget.percentage_spent.toFixed(1) }}% used
                                    </span>
                                    <Badge
                                        v-if="budget.is_exceeded"
                                        variant="destructive"
                                        class="text-xs"
                                    >
                                        Exceeded
                                    </Badge>
                                    <Badge
                                        v-else-if="budget.is_alert_threshold_reached"
                                        variant="secondary"
                                        class="text-xs"
                                    >
                                        Alert
                                    </Badge>
                                </div>
                            </div>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

