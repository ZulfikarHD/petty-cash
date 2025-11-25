<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as budgetsIndex,
    update as budgetsUpdate,
} from '@/actions/App/Http/Controllers/BudgetController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
    color: string;
}

interface Budget {
    id: number;
    category_id: number;
    amount: string;
    start_date: string;
    end_date: string;
    alert_threshold: string;
}

interface Props {
    budget: Budget;
    categories: Category[];
}

const props = defineProps<Props>();

const form = ref({
    category_id: String(props.budget.category_id),
    amount: props.budget.amount,
    start_date: props.budget.start_date,
    end_date: props.budget.end_date,
    alert_threshold: props.budget.alert_threshold,
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

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
        title: 'Edit',
        href: budgetsUpdate(props.budget.id).url,
    },
];

function submit() {
    processing.value = true;
    errors.value = {};

    router.put(budgetsUpdate(props.budget.id).url, form.value, {
        preserveScroll: true,
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Edit Budget" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Budget</CardTitle>
                    <CardDescription>
                        Update budget information
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Category -->
                        <div class="space-y-2">
                            <Label for="category_id">Category</Label>
                            <Select v-model="form.category_id" required>
                                <SelectTrigger id="category_id">
                                    <SelectValue placeholder="Select a category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="String(category.id)"
                                    >
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="size-3 rounded"
                                                :style="{ backgroundColor: category.color }"
                                            ></div>
                                            {{ category.name }}
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.category_id" />
                        </div>

                        <!-- Amount -->
                        <div class="space-y-2">
                            <Label for="amount">Budget Amount (IDR)</Label>
                            <Input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="0.00"
                            />
                            <InputError :message="errors.amount" />
                        </div>

                        <!-- Date Range -->
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="start_date">Start Date</Label>
                                <Input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    required
                                />
                                <InputError :message="errors.start_date" />
                            </div>
                            <div class="space-y-2">
                                <Label for="end_date">End Date</Label>
                                <Input
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="date"
                                    required
                                />
                                <InputError :message="errors.end_date" />
                            </div>
                        </div>

                        <!-- Alert Threshold -->
                        <div class="space-y-2">
                            <Label for="alert_threshold">
                                Alert Threshold (%)
                                <span class="text-sm text-muted-foreground">
                                    - Alert when spending reaches this percentage
                                </span>
                            </Label>
                            <Input
                                id="alert_threshold"
                                v-model="form.alert_threshold"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="80"
                            />
                            <InputError :message="errors.alert_threshold" />
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="processing">
                                {{ processing ? 'Updating...' : 'Update Budget' }}
                            </Button>
                            <Link :href="budgetsIndex().url">
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

