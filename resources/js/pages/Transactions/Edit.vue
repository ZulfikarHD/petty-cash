<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as transactionsIndex, show as transactionsShow, update as transactionsUpdate } from '@/actions/App/Http/Controllers/TransactionController';
import { type BreadcrumbItem, type Transaction, type Receipt } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
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
import { Upload, X } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
}

interface Props {
    transaction: Transaction;
    receipts: Receipt[];
    categories: Category[];
}

const props = defineProps<Props>();

const form = ref({
    type: props.transaction.type,
    amount: props.transaction.amount.toString(),
    description: props.transaction.description,
    transaction_date: props.transaction.transaction_date,
    category_id: props.transaction.category_id ? String(props.transaction.category_id) : null as string | null,
    notes: props.transaction.notes || '',
    receipts: [] as File[],
    delete_receipts: [] as number[],
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);
const receiptPreviews = ref<{ file: File; url: string }[]>([]);
const existingReceipts = ref<Receipt[]>([...props.receipts]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Transactions',
        href: transactionsIndex().url,
    },
    {
        title: props.transaction.transaction_number,
        href: transactionsShow(props.transaction.id).url,
    },
    {
        title: 'Edit',
        href: transactionsUpdate(props.transaction.id).url,
    },
];

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const files = Array.from(target.files);
        files.forEach((file) => {
            if (!form.value.receipts.find((r) => r.name === file.name)) {
                form.value.receipts.push(file);
                receiptPreviews.value.push({
                    file,
                    url: URL.createObjectURL(file),
                });
            }
        });
    }
}

function removeNewReceipt(index: number) {
    URL.revokeObjectURL(receiptPreviews.value[index].url);
    receiptPreviews.value.splice(index, 1);
    form.value.receipts.splice(index, 1);
}

function removeExistingReceipt(receipt: Receipt) {
    form.value.delete_receipts.push(receipt.id);
    existingReceipts.value = existingReceipts.value.filter((r) => r.id !== receipt.id);
}

function submit() {
    processing.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('type', form.value.type);
    formData.append('amount', form.value.amount);
    formData.append('description', form.value.description);
    formData.append('transaction_date', form.value.transaction_date);
    if (form.value.category_id) {
        formData.append('category_id', form.value.category_id);
    }
    formData.append('notes', form.value.notes);

    form.value.receipts.forEach((file, index) => {
        formData.append(`receipts[${index}]`, file);
    });

    form.value.delete_receipts.forEach((id, index) => {
        formData.append(`delete_receipts[${index}]`, id.toString());
    });

    router.post(transactionsUpdate(props.transaction.id).url, formData, {
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
    <Head :title="`Edit Transaction ${transaction.transaction_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Transaction</CardTitle>
                    <CardDescription>
                        Update transaction details for {{ transaction.transaction_number }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Transaction Type -->
                        <div class="space-y-2">
                            <Label>Transaction Type</Label>
                            <RadioGroup v-model="form.type">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem value="in" id="type-in" />
                                    <Label for="type-in" class="cursor-pointer font-normal">
                                        Cash In
                                    </Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem value="out" id="type-out" />
                                    <Label for="type-out" class="cursor-pointer font-normal">
                                        Cash Out
                                    </Label>
                                </div>
                            </RadioGroup>
                            <InputError :message="errors.type" />
                        </div>

                        <!-- Amount -->
                        <div class="space-y-2">
                            <Label for="amount">Amount (IDR)</Label>
                            <Input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                            />
                            <InputError :message="errors.amount" />
                        </div>

                        <!-- Transaction Date -->
                        <div class="space-y-2">
                            <Label for="transaction_date">Transaction Date</Label>
                            <Input
                                id="transaction_date"
                                v-model="form.transaction_date"
                                type="date"
                                :max="new Date().toISOString().split('T')[0]"
                                required
                            />
                            <InputError :message="errors.transaction_date" />
                        </div>

                        <!-- Category -->
                        <div class="space-y-2">
                            <Label for="category_id">Category (Optional)</Label>
                            <Select v-model="form.category_id">
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

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                required
                                placeholder="Enter transaction details..."
                            />
                            <InputError :message="errors.description" />
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Notes (Optional)</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                rows="2"
                                placeholder="Additional notes..."
                            />
                            <InputError :message="errors.notes" />
                        </div>

                        <!-- Existing Receipt Images -->
                        <div v-if="existingReceipts.length > 0" class="space-y-2">
                            <Label>Existing Receipt Images</Label>
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                                <div
                                    v-for="receipt in existingReceipts"
                                    :key="receipt.id"
                                    class="relative group"
                                >
                                    <img
                                        :src="receipt.url"
                                        :alt="receipt.name"
                                        class="h-32 w-full rounded-lg border object-cover"
                                    />
                                    <button
                                        type="button"
                                        @click="removeExistingReceipt(receipt)"
                                        class="absolute -right-2 -top-2 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <X class="size-4" />
                                    </button>
                                    <p class="mt-1 truncate text-xs text-muted-foreground">
                                        {{ receipt.name }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- New Receipt Upload -->
                        <div class="space-y-2">
                            <Label for="receipts">Add New Receipt Images (Optional)</Label>
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <Input
                                        id="receipts"
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleFileSelect"
                                        class="cursor-pointer"
                                    />
                                    <Upload class="size-5 text-muted-foreground" />
                                </div>
                                <InputError :message="errors.receipts" />

                                <!-- New Receipt Previews -->
                                <div
                                    v-if="receiptPreviews.length > 0"
                                    class="grid grid-cols-2 gap-4 md:grid-cols-4"
                                >
                                    <div
                                        v-for="(preview, index) in receiptPreviews"
                                        :key="index"
                                        class="relative group"
                                    >
                                        <img
                                            :src="preview.url"
                                            :alt="preview.file.name"
                                            class="h-32 w-full rounded-lg border object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="removeNewReceipt(index)"
                                            class="absolute -right-2 -top-2 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            <X class="size-4" />
                                        </button>
                                        <p class="mt-1 truncate text-xs text-muted-foreground">
                                            {{ preview.file.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4">
                            <Button
                                type="submit"
                                :disabled="processing"
                            >
                                {{ processing ? 'Updating...' : 'Update Transaction' }}
                            </Button>
                            <Link :href="transactionsShow(transaction.id).url">
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

