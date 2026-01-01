import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    profilPicture?: string;
    role?: string;
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
    babysitter: Babysitter | User;
    parent: ParentProfile | User;
    services: Services[];
    details: Details | Details[];
    ref?: string;
    created_at?: string;
    notes?: string;
    total_amount: number;
    status?: string;
    date?: string;
    start_time?: string;
    end_time?: string;
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

export interface Rating {
    id: number;
    reservation_id: number;
    reviewer_id: number;
    reviewee_id: number;
    rating: number;
    comment?: string | null;
    created_at?: string;
    reviewer?: User;
}

export interface RatingsPayload {
    can_rate: boolean;
    mine?: Rating | null;
    other?: Rating | null;
    target_name?: string | null;
}

export interface Stats {
    current_month_revenue: number;
    previous_month_revenue: number;
    revenue_change_pct: number | null;
    current_month_count: number;
    previous_month_count: number;
    count_change_pct: number | null;
    avg_revenue_per_booking: number | null;
    total_revenue: number;
    total_count?: number;
    total_canceled_count: number;
    total_requests_count?: number;
    pending_count?: number;
    confirmed_count?: number;
    upcoming_count?: number;
    unique_babysitters_count?: number;
    unique_parents_count?: number;
    current_month_trend?: number[];
}

export interface AnnouncementParent {
    id: number;
    name: string;
    city?: string | null;
}

export interface AnnouncementChild {
    id?: number;
    name?: string | null;
    age?: string | number | null;
    allergies?: string | null;
    details?: string | null;
    photo?: string | null;
}

export interface Announcement {
    id: number;
    title: string;
    service: string;
    children?: AnnouncementChild[] | null;
    child_name?: string | null;
    child_age?: string | null;
    child_notes?: string | null;
    description?: string | null;
    status?: string;
    created_at?: string;
    parent?: AnnouncementParent | null;
}


export interface Preview {
    file: File;
    preview: string; // Ceci est un object URL
}

export interface SearchFilters {
    name?: string;
    city?: string;
    country?: string;
    min_price?: number | string | null;
    max_price?: number | string | null;
    min_rating?: number | string | null;
    payment_frequency?: string | null;
    sort?: string | null;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ratings?: RatingsPayload;
    filters?: SearchFilters;
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
    settings?: Record<string, unknown> | null;
}

export interface Address {
    id: number;
    street?: string | null;
    city?: string | null;
    province?: string | null;
    state?: string | null;
    country?: string | null;
    postal_code?: string | null;
    latitude?: number | null;
    longitude?: number | null;
}
// src/types/index.ts
export interface Babysitter {
    id: number;
    name: string;
    email: string;
    address: Address;
    babysitter_profile: BabysitterProfile;
    media: MediaItem[];
    rating_avg?: number | null;
    rating_count?: number | null;
    received_ratings?: Rating[];
}

export interface ParentProfile {
    id: number;
    first_name: string;
    last_name: string;
    phone: string;
    birthdate: string;
    user_id: User['id'];
    settings?: Record<string, unknown> | null;
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
