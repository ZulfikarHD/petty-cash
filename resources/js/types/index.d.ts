import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles?: Role[];
    permissions?: Permission[];
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    created_at?: string;
    updated_at?: string;
}

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at?: string;
    updated_at?: string;
}

export interface PaginatedData<T> {
    data: T[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

export type TransactionType = 'in' | 'out';
export type TransactionStatus = 'pending' | 'approved' | 'rejected';

export interface Transaction {
    id: number;
    transaction_number: string;
    type: TransactionType;
    amount: string | number;
    description: string;
    transaction_date: string;
    category_id: number | null;
    vendor_id: number | null;
    user_id: number;
    status: TransactionStatus;
    approved_by: number | null;
    approved_at: string | null;
    rejection_reason: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    user?: User;
    approver?: User;
}

export interface Receipt {
    id: number;
    name: string;
    url: string;
    size: number;
}

export interface TransactionSummary {
    total_in: number;
    total_out: number;
    net_balance: number;
}

export type ApprovalStatus = 'pending' | 'approved' | 'rejected';

export interface Approval {
    id: number;
    transaction_id: number;
    submitted_by: number;
    reviewed_by: number | null;
    status: ApprovalStatus;
    notes: string | null;
    rejection_reason: string | null;
    submitted_at: string;
    reviewed_at: string | null;
    created_at: string;
    updated_at: string;
    transaction?: Transaction;
    submitted_by_user?: User;
    reviewed_by_user?: User;
}

export interface AppNotification {
    id: number;
    user_id: number;
    type: string;
    title: string;
    message: string;
    action_url: string | null;
    read_at: string | null;
    data: Record<string, unknown> | null;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    name: string;
    description: string | null;
    color: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
