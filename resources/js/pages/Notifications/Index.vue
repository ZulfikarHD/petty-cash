<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as notificationsIndex, markAsRead, markAllAsRead } from '@/actions/App/Http/Controllers/NotificationController';
import { type BreadcrumbItem, type PaginatedData, type AppNotification } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Bell, Check, CheckCheck, ExternalLink } from 'lucide-vue-next';

interface Props {
    notifications: PaginatedData<AppNotification>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Notifications',
        href: notificationsIndex().url,
    },
];

function handleMarkAsRead(notification: AppNotification) {
    router.post(markAsRead(notification.id).url, {}, {
        preserveScroll: true,
    });
}

function handleMarkAllAsRead() {
    router.post(markAllAsRead().url, {}, {
        preserveScroll: true,
    });
}

function formatDateTime(date: string) {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getRelativeTime(date: string) {
    const now = new Date();
    const then = new Date(date);
    const diffMs = now.getTime() - then.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return formatDateTime(date);
}

function getNotificationIcon(type: string) {
    switch (type) {
        case 'approval_request':
            return 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400';
        case 'approval_decision':
            return 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400';
        default:
            return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
    }
}

const hasUnread = props.notifications.data.some(n => !n.read_at);
</script>

<template>
    <Head title="Notifications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <Bell class="size-5" />
                                Notifications
                            </CardTitle>
                            <CardDescription>
                                Your notification history
                            </CardDescription>
                        </div>
                        <Button
                            v-if="hasUnread"
                            variant="outline"
                            size="sm"
                            @click="handleMarkAllAsRead"
                        >
                            <CheckCheck class="mr-2 size-4" />
                            Mark All as Read
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="notifications.data.length === 0" class="text-center py-8 text-muted-foreground">
                        <Bell class="size-12 mx-auto mb-4 opacity-50" />
                        <p>No notifications yet</p>
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="notification in notifications.data"
                            :key="notification.id"
                            :class="[
                                'flex items-start gap-4 p-4 rounded-lg border transition-colors',
                                notification.read_at
                                    ? 'bg-background'
                                    : 'bg-muted/50 border-primary/20'
                            ]"
                        >
                            <div
                                :class="[
                                    'size-10 rounded-full flex items-center justify-center shrink-0',
                                    getNotificationIcon(notification.type)
                                ]"
                            >
                                <Bell class="size-5" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium">{{ notification.title }}</p>
                                        <p class="text-sm text-muted-foreground mt-1">
                                            {{ notification.message }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <Badge v-if="!notification.read_at" variant="secondary" class="text-xs">
                                            New
                                        </Badge>
                                        <span class="text-xs text-muted-foreground">
                                            {{ getRelativeTime(notification.created_at) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 mt-3">
                                    <Link
                                        v-if="notification.action_url"
                                        :href="notification.action_url"
                                    >
                                        <Button variant="outline" size="sm">
                                            <ExternalLink class="mr-2 size-3" />
                                            View
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="!notification.read_at"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleMarkAsRead(notification)"
                                    >
                                        <Check class="mr-2 size-3" />
                                        Mark as Read
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="notifications.links.length > 3"
                        class="mt-4 flex justify-center gap-1"
                    >
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, index) in notifications.links"
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
    </AppLayout>
</template>

