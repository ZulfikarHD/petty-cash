<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as budgetsIndex,
    create as budgetsCreate,
    show as budgetsShow,
    edit as budgetsEdit,
    destroy as budgetsDestroy,
    overview as budgetsOverview,
} from '@/actions/App/Http/Controllers/BudgetController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import { Plus, Search, Edit, Trash2, Eye, BarChart3 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

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

interface Props {
    budgets: {
        data: Budget[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
    };
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
];

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');

const debouncedSearch = useDebounceFn(() => {
    router.get(
        budgetsIndex().url,
        { search: search.value, status: status.value },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, debouncedSearch);
watch(status, () => {
    router.get(
        budgetsIndex().url,
        { search: search.value, status: status.value },
        { preserveState: true, replace: true }
    );
});

function deleteBudget(budget: Budget) {
    if (
        confirm(
            `Are you sure you want to delete the budget for "${budget.category.name}"?`
        )
    ) {
        router.delete(budgetsDestroy(budget.id).url);
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
    <Head title="Budgets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Budgets</CardTitle>
                            <CardDescription>
                                Manage budget allocations for categories
                            </CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-if="$page.props.auth.user.permissions.includes('view-budgets')"
                                :href="budgetsOverview().url"
                            >
                                <Button variant="outline">
                                    <BarChart3 class="mr-2 size-4" />
                                    Overview
                                </Button>
                            </Link>
                            <Link
                                v-if="$page.props.auth.user.permissions.includes('create-budgets')"
                                :href="budgetsCreate().url"
                            >
                                <Button>
                                    <Plus class="mr-2 size-4" />
                                    Create Budget
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Filters -->
                    <div class="mb-6 flex gap-4">
                        <div class="relative flex-1">
                            <Search
                                class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                type="search"
                                placeholder="Search by category..."
                                class="pl-10"
                            />
                        </div>
                        <Select v-model="status">
                            <SelectTrigger class="w-48">
                                <SelectValue placeholder="All Periods" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Periods</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="upcoming">Upcoming</SelectItem>
                                <SelectItem value="expired">Expired</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Budget</TableHead>
                                    <TableHead>Spent</TableHead>
                                    <TableHead>Progress</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="budgets.data.length === 0">
                                    <TableCell
                                        colspan="6"
                                        class="text-center text-muted-foreground"
                                    >
                                        No budgets found
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="budget in budgets.data"
                                    :key="budget.id"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="size-4 rounded"
                                                :style="{
                                                    backgroundColor: budget.category.color,
                                                }"
                                            ></div>
                                            <span class="font-medium">{{
                                                budget.category.name
                                            }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">
                                            {{ formatDate(budget.start_date) }} -
                                            {{ formatDate(budget.end_date) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ formatCurrency(budget.amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">
                                            {{ formatCurrency(budget.spent_amount) }}
                                            <Badge
                                                v-if="budget.is_exceeded"
                                                variant="destructive"
                                                class="ml-2"
                                            >
                                                Exceeded
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <div
                                                class="h-2 w-32 overflow-hidden rounded-full bg-muted"
                                            >
                                                <div
                                                    class="h-full transition-all"
                                                    :class="getProgressBarColor(budget)"
                                                    :style="{
                                                        width: `${Math.min(budget.percentage_spent, 100)}%`,
                                                    }"
                                                ></div>
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ budget.percentage_spent.toFixed(1) }}%
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="budgetsShow(budget.id).url">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="size-4" />
                                                </Button>
                                            </Link>
                                            <Link
                                                v-if="$page.props.auth.user.permissions.includes('edit-budgets')"
                                                :href="budgetsEdit(budget.id).url"
                                            >
                                                <Button variant="ghost" size="sm">
                                                    <Edit class="size-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                v-if="$page.props.auth.user.permissions.includes('delete-budgets')"
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteBudget(budget)"
                                            >
                                                <Trash2
                                                    class="size-4 text-destructive"
                                                />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="budgets.last_page > 1"
                        class="mt-4 flex items-center justify-between"
                    >
                        <div class="text-sm text-muted-foreground">
                            Showing
                            {{ (budgets.current_page - 1) * budgets.per_page + 1 }} to
                            {{
                                Math.min(
                                    budgets.current_page * budgets.per_page,
                                    budgets.total
                                )
                            }}
                            of {{ budgets.total }} budgets
                        </div>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in budgets.links"
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

