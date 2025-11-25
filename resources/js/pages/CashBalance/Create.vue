<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as cashBalancesIndex,
    store as cashBalancesStore,
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
import { ref } from 'vue';

interface Props {
    suggestedStart: string;
    suggestedEnd: string;
    suggestedOpeningBalance: number;
}

const props = defineProps<Props>();

const form = ref({
    period_start: props.suggestedStart,
    period_end: props.suggestedEnd,
    opening_balance: props.suggestedOpeningBalance.toString(),
    notes: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

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
        title: 'Create',
        href: cashBalancesStore().url,
    },
];

function submit() {
    processing.value = true;
    errors.value = {};

    router.post(cashBalancesStore().url, form.value, {
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
</script>

<template>
    <Head title="Create Cash Balance Period" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Balance Period</CardTitle>
                    <CardDescription>
                        Set up a new cash balance period with opening balance
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Date Range -->
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="period_start">Period Start Date</Label>
                                <Input
                                    id="period_start"
                                    v-model="form.period_start"
                                    type="date"
                                    required
                                />
                                <InputError :message="errors.period_start" />
                            </div>
                            <div class="space-y-2">
                                <Label for="period_end">Period End Date</Label>
                                <Input
                                    id="period_end"
                                    v-model="form.period_end"
                                    type="date"
                                    required
                                />
                                <InputError :message="errors.period_end" />
                            </div>
                        </div>

                        <!-- Opening Balance -->
                        <div class="space-y-2">
                            <Label for="opening_balance">Opening Balance (IDR)</Label>
                            <Input
                                id="opening_balance"
                                v-model="form.opening_balance"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                            />
                            <p class="text-sm text-muted-foreground">
                                The cash on hand at the start of this period.
                                <span v-if="suggestedOpeningBalance > 0">
                                    Suggested from previous period: {{ formatCurrency(suggestedOpeningBalance) }}
                                </span>
                            </p>
                            <InputError :message="errors.opening_balance" />
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Notes (Optional)</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Any additional notes about this balance period..."
                                rows="3"
                            />
                            <InputError :message="errors.notes" />
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="processing">
                                {{ processing ? 'Creating...' : 'Create Period' }}
                            </Button>
                            <Link :href="cashBalancesIndex().url">
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

