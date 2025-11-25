<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as transactionsIndex } from '@/actions/App/Http/Controllers/TransactionController';
import { index as categoriesIndex } from '@/actions/App/Http/Controllers/CategoryController';
import { index as budgetsIndex } from '@/actions/App/Http/Controllers/BudgetController';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, Receipt, Tags, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // Add Transactions menu item if user has permission
    if (page.props.auth?.can?.viewTransactions) {
        items.push({
            title: 'Transactions',
            href: transactionsIndex(),
            icon: Receipt,
        });
    }

    // Add Categories menu item if user has permission
    if (page.props.auth?.can?.viewCategories) {
        items.push({
            title: 'Categories',
            href: categoriesIndex(),
            icon: Tags,
        });
    }

    // Add Budgets menu item if user has permission
    if (page.props.auth?.can?.viewBudgets) {
        items.push({
            title: 'Budgets',
            href: budgetsIndex(),
            icon: Wallet,
        });
    }

    // Add Users menu item if user has permission
    if (page.props.auth?.can?.viewUsers) {
        items.push({
            title: 'Users',
            href: '/users',
            icon: Users,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
