<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as categoriesIndex,
    store as categoriesStore,
} from '@/actions/App/Http/Controllers/CategoryController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

const form = ref({
    name: '',
    slug: '',
    description: '',
    color: '#6366f1',
    is_active: true,
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

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
        title: 'Create',
        href: categoriesStore().url,
    },
];

// Preset colors
const presetColors = [
    '#6366f1', // Indigo
    '#8b5cf6', // Violet
    '#d946ef', // Fuchsia
    '#ec4899', // Pink
    '#f43f5e', // Rose
    '#ef4444', // Red
    '#f97316', // Orange
    '#f59e0b', // Amber
    '#eab308', // Yellow
    '#84cc16', // Lime
    '#22c55e', // Green
    '#10b981', // Emerald
    '#14b8a6', // Teal
    '#06b6d4', // Cyan
    '#0ea5e9', // Sky
    '#3b82f6', // Blue
];

function submit() {
    processing.value = true;
    errors.value = {};

    router.post(categoriesStore().url, form.value, {
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
    <Head title="Create Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Category</CardTitle>
                    <CardDescription>
                        Add a new expense category for organizing transactions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Category Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="e.g., Office Supplies"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <Label for="slug">
                                Slug (Optional)
                                <span class="text-sm text-muted-foreground">
                                    - Auto-generated if left empty
                                </span>
                            </Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                placeholder="e.g., office-supplies"
                            />
                            <InputError :message="errors.slug" />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description (Optional)</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Describe this category..."
                            />
                            <InputError :message="errors.description" />
                        </div>

                        <!-- Color -->
                        <div class="space-y-2">
                            <Label for="color">Color</Label>
                            <div class="flex gap-4">
                                <Input
                                    id="color"
                                    v-model="form.color"
                                    type="text"
                                    placeholder="#6366f1"
                                    class="w-32"
                                />
                                <div
                                    class="flex size-10 items-center justify-center rounded-md border"
                                    :style="{ backgroundColor: form.color }"
                                ></div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button
                                    v-for="color in presetColors"
                                    :key="color"
                                    type="button"
                                    class="size-8 rounded border-2 transition-transform hover:scale-110"
                                    :class="
                                        form.color === color
                                            ? 'border-foreground'
                                            : 'border-transparent'
                                    "
                                    :style="{ backgroundColor: color }"
                                    @click="form.color = color"
                                ></button>
                            </div>
                            <InputError :message="errors.color" />
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_active"
                                v-model:checked="form.is_active"
                            />
                            <Label
                                for="is_active"
                                class="cursor-pointer font-normal"
                            >
                                Active (category can be used for transactions)
                            </Label>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="processing">
                                {{ processing ? 'Creating...' : 'Create Category' }}
                            </Button>
                            <Link :href="categoriesIndex().url">
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

