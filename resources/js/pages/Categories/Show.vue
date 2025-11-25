<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as categoriesIndex,
    edit as categoriesEdit,
} from '@/actions/App/Http/Controllers/CategoryController';
import { show as transactionsShow } from '@/actions/App/Http/Controllers/TransactionController';
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
import { Edit, ArrowUpCircle, ArrowDownCircle } from 'lucide-vue-next';

interface Transaction {
    id: number;
    transaction_number: string;
    type: 'in' | 'out';
    amount: string;
    description: string;
    transaction_date: string;
    status: 'pending' | 'approved' | 'rejected';
    user: {
        name: string;
    };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    color: string;
    is_active: boolean;
    transactions: Transaction[];
}

interface Stats {
    total_transactions: number;
    total_in: string;
    total_out: string;
}

interface Props {
    category: Category;
    stats: Stats;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Categories',
        href: categoriesIndex().url,
    },
    {
        title: props.category.name,
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
    <Head :title="`Category: ${category.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Category Details Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="size-12 rounded-lg"
                                :style="{ backgroundColor: category.color }"
                            ></div>
                            <div>
                                <CardTitle>{{ category.name }}</CardTitle>
                                <CardDescription v-if="category.description">
                                    {{ category.description }}
                                </CardDescription>
                                <Badge
                                    :variant="
                                        category.is_active ? 'default' : 'secondary'
                                    "
                                    class="mt-2"
                                >
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                        <Link
                            v-if="$page.props.auth.user.permissions.includes('edit-categories')"
                            :href="categoriesEdit(category.slug).url"
                        >
                            <Button>
                                <Edit class="mr-2 size-4" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Total Transactions
                            </div>
                            <div class="mt-1 text-2xl font-bold">
                                {{ stats.total_transactions }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <ArrowUpCircle class="size-4 text-green-500" />
                                Total Cash In
                            </div>
                            <div class="mt-1 text-2xl font-bold text-green-600">
                                {{ formatCurrency(stats.total_in) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <ArrowDownCircle class="size-4 text-red-500" />
                                Total Cash Out
                            </div>
                            <div class="mt-1 text-2xl font-bold text-red-600">
                                {{ formatCurrency(stats.total_out) }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Transactions Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Transactions</CardTitle>
                    <CardDescription>
                        Last 10 transactions in this category
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Transaction #</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-if="category.transactions.length === 0"
                                >
                                    <TableCell
                                        colspan="6"
                                        class="text-center text-muted-foreground"
                                    >
                                        No transactions found
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="transaction in category.transactions"
                                    :key="transaction.id"
                                    class="cursor-pointer hover:bg-muted/50"
                                    @click="$inertia.visit(transactionsShow(transaction.id).url)"
                                >
                                    <TableCell class="font-medium">
                                        {{ transaction.transaction_number }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="
                                                transaction.type === 'in'
                                                    ? 'default'
                                                    : 'destructive'
                                            "
                                        >
                                            {{
                                                transaction.type === 'in'
                                                    ? 'Cash In'
                                                    : 'Cash Out'
                                            }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        :class="
                                            transaction.type === 'in'
                                                ? 'text-green-600'
                                                : 'text-red-600'
                                        "
                                    >
                                        {{ formatCurrency(transaction.amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <span class="line-clamp-1">{{
                                            transaction.description
                                        }}</span>
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(transaction.transaction_date) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="
                                                transaction.status === 'approved'
                                                    ? 'default'
                                                    : transaction.status === 'pending'
                                                      ? 'secondary'
                                                      : 'destructive'
                                            "
                                        >
                                            {{ transaction.status }}
                                        </Badge>
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

