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
    is_profile_picture: boolean;
    file_path: string;
    mime_type: string;
    is_profile: boolean;
}
export interface Reservation {
    id: number;
    babysitter: Babysitter;
    parent: ParentProfile;
    services: Services[];
    details: Details[];
    ref:  string;
    notes: string;
    total_amount: number;
    notes: string;
    status: string;
    date: string;
    start_time: string;
    end_time: string;
}

export interface Services {
    id: number;
    description: string;
    pivot: [
        {
            price: number;
            quantity: number;
        },
    ]
}

export interface Details {
    id: number;
    status: string;
    date: string;
    start_time: string;
    end_time: string;
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

export interface Address {
    id: number;
    street: string;
    city: string;
    provider: string;
    state: string;
    country: string;
    postal_code: string;
    latitude: number;
    longitude: number;
}
// src/types/index.ts
export interface Babysitter {
    id: number;
    name: string;
    email: string;
    address: Address;
    babysitter_profile: BabysitterProfile;
    media: MediaItem[];
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
