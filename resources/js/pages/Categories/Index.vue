<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    index as categoriesIndex,
    create as categoriesCreate,
    show as categoriesShow,
    edit as categoriesEdit,
    destroy as categoriesDestroy,
} from '@/actions/App/Http/Controllers/CategoryController';
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
import { Plus, Search, Edit, Trash2, Eye } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    color: string;
    is_active: boolean;
    transactions_count: number;
    created_at: string;
}

interface Props {
    categories: {
        data: Category[];
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
        title: 'Categories',
        href: categoriesIndex().url,
    },
];

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');

const debouncedSearch = useDebounceFn(() => {
    router.get(
        categoriesIndex().url,
        { search: search.value, status: status.value },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, debouncedSearch);
watch(status, () => {
    router.get(
        categoriesIndex().url,
        { search: search.value, status: status.value },
        { preserveState: true, replace: true }
    );
});

function deleteCategory(category: Category) {
    if (category.transactions_count > 0) {
        alert('Cannot delete category with existing transactions.');
        return;
    }

    if (
        confirm(
            `Are you sure you want to delete the category "${category.name}"?`
        )
    ) {
        router.delete(categoriesDestroy(category.slug).url);
    }
}
</script>

<template>
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Categories</CardTitle>
                            <CardDescription>
                                Manage expense categories for transactions
                            </CardDescription>
                        </div>
                        <Link
                            v-if="$page.props.auth.user.permissions.includes('create-categories')"
                            :href="categoriesCreate().url"
                        >
                            <Button>
                                <Plus class="mr-2 size-4" />
                                Create Category
                            </Button>
                        </Link>
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
                                placeholder="Search categories..."
                                class="pl-10"
                            />
                        </div>
                        <Select v-model="status">
                            <SelectTrigger class="w-48">
                                <SelectValue placeholder="All Statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Statuses</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Transactions</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-if="categories.data.length === 0"
                                >
                                    <TableCell
                                        colspan="5"
                                        class="text-center text-muted-foreground"
                                    >
                                        No categories found
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="category in categories.data"
                                    :key="category.id"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="size-4 rounded"
                                                :style="{ backgroundColor: category.color }"
                                            ></div>
                                            <span class="font-medium">{{
                                                category.name
                                            }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="line-clamp-1">{{
                                            category.description || '-'
                                        }}</span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="
                                                category.is_active
                                                    ? 'default'
                                                    : 'secondary'
                                            "
                                        >
                                            {{
                                                category.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                            }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {{ category.transactions_count }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="categoriesShow(category.slug).url"
                                            >
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="size-4" />
                                                </Button>
                                            </Link>
                                            <Link
                                                v-if="$page.props.auth.user.permissions.includes('edit-categories')"
                                                :href="categoriesEdit(category.slug).url"
                                            >
                                                <Button variant="ghost" size="sm">
                                                    <Edit class="size-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                v-if="$page.props.auth.user.permissions.includes('delete-categories')"
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteCategory(category)"
                                            >
                                                <Trash2 class="size-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="categories.last_page > 1"
                        class="mt-4 flex items-center justify-between"
                    >
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (categories.current_page - 1) * categories.per_page + 1 }}
                            to
                            {{
                                Math.min(
                                    categories.current_page * categories.per_page,
                                    categories.total
                                )
                            }}
                            of {{ categories.total }} categories
                        </div>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in categories.links"
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

