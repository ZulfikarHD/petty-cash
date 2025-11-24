<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type PaginatedData, type User, type Role } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Pencil, Trash2, Plus, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    users: PaginatedData<User>;
    filters: {
        search?: string;
        role?: string;
    };
    roles: Role[];
}

const props = defineProps<Props>();

const searchForm = useForm({
    search: props.filters.search || '',
    role: props.filters.role || '',
});

const deleteForm = useForm({});
const showDeleteDialog = ref(false);
const userToDelete = ref<User | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Users',
        href: '/users',
    },
];

function search() {
    searchForm.get('/users', {
        preserveState: true,
        preserveScroll: true,
    });
}

function confirmDelete(user: User) {
    userToDelete.value = user;
    showDeleteDialog.value = true;
}

function deleteUser() {
    if (!userToDelete.value) {
        return;
    }

    deleteForm.delete(`/users/${userToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            userToDelete.value = null;
        },
    });
}
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>User Management</CardTitle>
                            <CardDescription>
                                Manage user accounts and their roles
                            </CardDescription>
                        </div>
                        <Link href="/users/create">
                            <Button>
                                <Plus class="mr-2 size-4" />
                                Add User
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Search and Filter -->
                    <div class="mb-4 flex gap-4">
                        <div class="relative flex-1">
                            <Search
                                class="absolute left-2 top-2.5 size-4 text-muted-foreground"
                            />
                            <Input
                                v-model="searchForm.search"
                                placeholder="Search users..."
                                class="pl-8"
                                @keyup.enter="search"
                            />
                        </div>
                        <Button @click="search"> Search </Button>
                    </div>

                    <!-- Users Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Roles</TableHead>
                                    <TableHead>Joined</TableHead>
                                    <TableHead class="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="user in users.data"
                                    :key="user.id"
                                >
                                    <TableCell class="font-medium">
                                        {{ user.name }}
                                    </TableCell>
                                    <TableCell>{{ user.email }}</TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Badge
                                                v-for="role in user.roles"
                                                :key="role.id"
                                                variant="secondary"
                                            >
                                                {{ role.name }}
                                            </Badge>
                                            <Badge
                                                v-if="
                                                    !user.roles ||
                                                    user.roles.length === 0
                                                "
                                                variant="outline"
                                            >
                                                No roles
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            new Date(
                                                user.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/users/${user.id}/edit`"
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                >
                                                    <Pencil class="size-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                @click="confirmDelete(user)"
                                            >
                                                <Trash2 class="size-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="users.links.length > 3"
                        class="mt-4 flex justify-center gap-1"
                    >
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, index) in users.links"
                            :key="index"
                            :href="link.url || ''"
                            :class="[
                                'px-3 py-1 border rounded',
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-accent',
                                !link.url && 'opacity-50 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete User</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        {{ userToDelete?.name }}? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showDeleteDialog = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="deleteForm.processing"
                        @click="deleteUser"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

