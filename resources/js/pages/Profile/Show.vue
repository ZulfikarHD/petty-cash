<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import InputError from '@/components/InputError.vue';

interface Props {
    user: User;
}

const props = defineProps<Props>();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Profile',
        href: '/profile',
    },
];

function updateProfile() {
    profileForm.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            // Profile updated
        },
    });
}

function updatePassword() {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
}
</script>

<template>
    <Head title="Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Profile Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Profile Information</CardTitle>
                    <CardDescription>
                        Update your account's profile information and email
                        address
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="updateProfile" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="profileForm.name"
                                type="text"
                                required
                                autofocus
                            />
                            <InputError :message="profileForm.errors.name" />
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="profileForm.email"
                                type="email"
                                required
                            />
                            <InputError :message="profileForm.errors.email" />
                        </div>

                        <!-- Roles -->
                        <div v-if="user.roles && user.roles.length > 0">
                            <Label>Your Roles</Label>
                            <div class="mt-2 flex gap-2">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    variant="secondary"
                                >
                                    {{ role.name }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <Button
                                type="submit"
                                :disabled="profileForm.processing"
                            >
                                Save Changes
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Update Password -->
            <Card>
                <CardHeader>
                    <CardTitle>Update Password</CardTitle>
                    <CardDescription>
                        Ensure your account is using a long, random password to
                        stay secure
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="updatePassword" class="space-y-6">
                        <!-- Current Password -->
                        <div class="space-y-2">
                            <Label for="current_password">
                                Current Password
                            </Label>
                            <Input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                            />
                            <InputError
                                :message="passwordForm.errors.current_password"
                            />
                        </div>

                        <!-- New Password -->
                        <div class="space-y-2">
                            <Label for="password">New Password</Label>
                            <Input
                                id="password"
                                v-model="passwordForm.password"
                                type="password"
                                required
                            />
                            <InputError
                                :message="passwordForm.errors.password"
                            />
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <Label for="password_confirmation">
                                Confirm Password
                            </Label>
                            <Input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                            />
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <Button
                                type="submit"
                                :disabled="passwordForm.processing"
                            >
                                Update Password
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

