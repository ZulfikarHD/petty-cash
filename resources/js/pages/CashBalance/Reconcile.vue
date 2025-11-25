<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as cashBalancesIndex,
    show as cashBalancesShow,
    storeReconciliation,
} from '@/actions/App/Http/Controllers/CashBalanceController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import {
    Calculator,
    CheckCircle,
    AlertTriangle,
    TrendingUp,
    TrendingDown,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

interface CashBalance {
    id: number;
    period_start: string;
    period_end: string;
    opening_balance: string;
    status: string;
    notes: string | null;
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

interface Props {
    cashBalance: CashBalance;
    periodBalance: PeriodBalance;
}

const props = defineProps<Props>();

const form = ref({
    actual_balance: props.periodBalance.closing_balance.toString(),
    discrepancy_notes: '',
    has_discrepancy: false,
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const discrepancy = computed(() => {
    const actual = parseFloat(form.value.actual_balance) || 0;
    return actual - props.periodBalance.closing_balance;
});

const hasDiscrepancy = computed(() => {
    return Math.abs(discrepancy.value) > 0.01;
});

watch(hasDiscrepancy, (value) => {
    form.value.has_discrepancy = value;
});

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
        href: cashBalancesShow(props.cashBalance.id).url,
    },
    {
        title: 'Reconcile',
        href: '',
    },
];

function submit() {
    processing.value = true;
    errors.value = {};

    router.post(storeReconciliation(props.cashBalance.id).url, {
        actual_balance: form.value.actual_balance,
        discrepancy_notes: form.value.discrepancy_notes,
        has_discrepancy: hasDiscrepancy.value,
    }, {
        preserveScroll: true,
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
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
</script>

<template>
    <Head title="Reconcile Cash Balance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Period Summary Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Balance Summary</CardTitle>
                    <CardDescription>
                        Period: {{ formatDate(cashBalance.period_start) }} -
                        {{ formatDate(cashBalance.period_end) }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-5">
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Opening Balance
                            </div>
                            <div class="mt-1 text-xl font-bold">
                                {{ formatCurrency(periodBalance.opening_balance) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                <TrendingUp class="size-3 text-green-500" />
                                Cash In
                            </div>
                            <div class="mt-1 text-xl font-bold text-green-600">
                                +{{ formatCurrency(periodBalance.cash_in) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                <TrendingDown class="size-3 text-red-500" />
                                Cash Out
                            </div>
                            <div class="mt-1 text-xl font-bold text-red-600">
                                -{{ formatCurrency(periodBalance.cash_out) }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-muted-foreground">
                                Net Flow
                            </div>
                            <div
                                class="mt-1 text-xl font-bold"
                                :class="{
                                    'text-green-600': periodBalance.net_flow >= 0,
                                    'text-red-600': periodBalance.net_flow < 0,
                                }"
                            >
                                {{ formatCurrency(periodBalance.net_flow) }}
                            </div>
                        </div>
                        <div class="rounded-lg border border-primary bg-primary/5 p-4">
                            <div class="flex items-center gap-1 text-sm font-medium text-primary">
                                <Calculator class="size-3" />
                                System Balance
                            </div>
                            <div class="mt-1 text-xl font-bold text-primary">
                                {{ formatCurrency(periodBalance.closing_balance) }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Reconciliation Form Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Cash Reconciliation</CardTitle>
                    <CardDescription>
                        Enter the actual cash on hand to reconcile with the system balance
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Actual Balance -->
                        <div class="space-y-2">
                            <Label for="actual_balance">Actual Cash on Hand (IDR)</Label>
                            <Input
                                id="actual_balance"
                                v-model="form.actual_balance"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                                class="text-lg"
                            />
                            <p class="text-sm text-muted-foreground">
                                Count the physical cash and enter the total amount.
                            </p>
                            <InputError :message="errors.actual_balance" />
                        </div>

                        <!-- Discrepancy Display -->
                        <div
                            v-if="hasDiscrepancy"
                            class="rounded-lg border p-4"
                            :class="{
                                'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-950': true,
                            }"
                        >
                            <div class="flex items-center gap-2">
                                <AlertTriangle class="size-5 text-yellow-600" />
                                <div>
                                    <div class="font-semibold text-yellow-800 dark:text-yellow-200">
                                        Discrepancy Detected
                                    </div>
                                    <div class="text-sm text-yellow-700 dark:text-yellow-300">
                                        Difference between actual and system balance:
                                        <span class="font-bold">
                                            {{ formatCurrency(discrepancy) }}
                                        </span>
                                        ({{ discrepancy > 0 ? 'Over' : 'Short' }})
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950"
                        >
                            <div class="flex items-center gap-2">
                                <CheckCircle class="size-5 text-green-600" />
                                <div>
                                    <div class="font-semibold text-green-800 dark:text-green-200">
                                        Balance Matches
                                    </div>
                                    <div class="text-sm text-green-700 dark:text-green-300">
                                        Actual cash matches the system balance.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Discrepancy Notes -->
                        <div v-if="hasDiscrepancy" class="space-y-2">
                            <Label for="discrepancy_notes">
                                Discrepancy Notes
                                <span class="text-destructive">*</span>
                            </Label>
                            <Textarea
                                id="discrepancy_notes"
                                v-model="form.discrepancy_notes"
                                placeholder="Please explain the reason for the discrepancy..."
                                rows="3"
                                required
                            />
                            <p class="text-sm text-muted-foreground">
                                Explain why there is a difference between actual and system balance.
                            </p>
                            <InputError :message="errors.discrepancy_notes" />
                        </div>

                        <!-- Summary -->
                        <div class="rounded-lg border p-4">
                            <h4 class="mb-3 font-semibold">Reconciliation Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">System Balance:</span>
                                    <span class="font-medium">{{ formatCurrency(periodBalance.closing_balance) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Actual Balance:</span>
                                    <span class="font-medium">{{ formatCurrency(form.actual_balance) }}</span>
                                </div>
                                <div class="border-t pt-2">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Discrepancy:</span>
                                        <span
                                            class="font-bold"
                                            :class="{
                                                'text-green-600': !hasDiscrepancy,
                                                'text-yellow-600': hasDiscrepancy,
                                            }"
                                        >
                                            {{ hasDiscrepancy ? formatCurrency(discrepancy) : 'None' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4">
                            <Button
                                type="submit"
                                :disabled="processing || (hasDiscrepancy && !form.discrepancy_notes)"
                            >
                                <CheckCircle class="mr-2 size-4" />
                                {{ processing ? 'Reconciling...' : 'Complete Reconciliation' }}
                            </Button>
                            <Link :href="cashBalancesShow(cashBalance.id).url">
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

