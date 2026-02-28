export type UserRole = 'superadmin' | 'admin' | 'user';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    logto_id?: string;
    phone?: string;
    address?: string;
    role: UserRole;
    last_login_at?: string;
    custom_data?: Record<string, unknown>;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type SignInLog = {
    id: number;
    user_id: number;
    ip_address?: string;
    user_agent?: string;
    device_info?: {
        user_agent?: string;
        browser?: string;
        os?: string;
    };
    signed_in_at: string;
    logto_event_id?: string;
};

export type Auth = {
    user?: User | null;
};

export type UserStatistics = {
    total_users: number;
    superadmins: number;
    admins: number;
    regular_users: number;
    total_sign_ins: number;
    average_sign_ins_per_user: number;
};
