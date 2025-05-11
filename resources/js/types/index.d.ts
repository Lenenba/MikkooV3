import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface MediaItem {
    id: number;
    url: string;
    collection_name: string;
    mime_type: string;
    is_profile: boolean;
}

export interface Preview {
    file: File;
    preview: string; // Ceci est un object URL
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}
export interface BabysitterProfile {
    id: number;
    first_name: string;
    last_name: string;
    phone: string;
    bio?: string;
    birthdate: string;
    experience: string;
    price_per_hour: number;
    user_id: User['id'];
    payment_frequency: string;
}

export interface ParentProfile {
    id: number;
    first_name: string;
    last_name: string;
    phone: string;
    birthdate: string;
    user_id: User['id'];
}
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
