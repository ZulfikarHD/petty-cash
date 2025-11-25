<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Bell, Check, ExternalLink } from 'lucide-vue-next';
import { index as notificationsIndex, recent, markAsRead, markAllAsRead } from '@/actions/App/Http/Controllers/NotificationController';

interface AppNotification {
    id: number;
    type: string;
    title: string;
    message: string;
    action_url: string | null;
    read_at: string | null;
    created_at: string;
}

const notifications = ref<AppNotification[]>([]);
const unreadCount = ref(0);
const isLoading = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;

async function fetchNotifications() {
    try {
        const response = await fetch(recent().url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (response.ok) {
            const data = await response.json();
            notifications.value = data.notifications;
            unreadCount.value = data.unread_count;
        }
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
}

function handleMarkAsRead(notification: AppNotification) {
    router.post(markAsRead(notification.id).url, {}, {
        preserveScroll: true,
        onSuccess: () => {
            fetchNotifications();
        },
    });
}

function handleMarkAllAsRead() {
    router.post(markAllAsRead().url, {}, {
        preserveScroll: true,
        onSuccess: () => {
            fetchNotifications();
        },
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
    return new Date(date).toLocaleDateString();
}

onMounted(() => {
    fetchNotifications();
    // Poll for new notifications every 30 seconds
    pollInterval = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative">
                <Bell class="size-5" />
                <Badge
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 size-5 p-0 flex items-center justify-center text-xs"
                    variant="destructive"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </Badge>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80">
            <DropdownMenuLabel class="flex items-center justify-between">
                <span>Notifications</span>
                <Button
                    v-if="unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    class="h-auto p-1 text-xs"
                    @click.stop="handleMarkAllAsRead"
                >
                    Mark all read
                </Button>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />

            <div v-if="notifications.length === 0" class="py-6 text-center text-muted-foreground text-sm">
                No notifications
            </div>

            <div v-else class="max-h-80 overflow-y-auto">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    :class="[
                        'p-3 border-b last:border-b-0 hover:bg-muted/50 cursor-pointer',
                        !notification.read_at && 'bg-muted/30'
                    ]"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ notification.title }}</p>
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">
                                {{ notification.message }}
                            </p>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ getRelativeTime(notification.created_at) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <Link
                                v-if="notification.action_url"
                                :href="notification.action_url"
                            >
                                <Button variant="ghost" size="icon" class="size-7">
                                    <ExternalLink class="size-3" />
                                </Button>
                            </Link>
                            <Button
                                v-if="!notification.read_at"
                                variant="ghost"
                                size="icon"
                                class="size-7"
                                @click.stop="handleMarkAsRead(notification)"
                            >
                                <Check class="size-3" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <DropdownMenuSeparator />
            <Link :href="notificationsIndex().url" class="block">
                <DropdownMenuItem class="cursor-pointer justify-center">
                    View all notifications
                </DropdownMenuItem>
            </Link>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

