// Resource types that exactly match the backend Resource classes
// These should be kept in sync with:
// - app/Http/Resources/InductionResource.php
// - app/Http/Resources/CourseResource.php  
// - app/Http/Resources/EquipmentResource.php

export type InductionResource = {
    id: number;
    key: string;
    trained: string | null;
    is_trainer: boolean;
    created_at: string;
    sign_off_requested_at: string | null;
    sign_off_expires_at: string | null;
    user?: {
        id: number;
        name: string;
        pronouns?: string | null;
        profile_photo_url?: string | null;
    };
    trainer?: {
        id: number;
        name: string;
    };
    urls?: {
        train: string;
        untrain: string;
        promote: string;
        demote: string;
    };
};

export type EquipmentResource = {
    id: number;
    name: string;
    slug: string;
    working: boolean;
    permaloan: boolean;
    dangerous: boolean;
    room: string | null;
    room_display: string | null;
    ppe: string[];
    photo_url: string | null;
    induction_category: string | null;
    access_code?: string;
    urls: {
        show: string;
    };
};

export type CourseResource = {
    id: number;
    name: string;
    slug: string;
    description: string;
    format: {
        label: string;
        value: string;
    };
    format_description: string;
    frequency: {
        label: string;
        value: string;
    };
    frequency_description: string;
    wait_time: string;
    training_organisation_description: string | null;
    schedule_url: string | null;
    quiz_url: string | null;
    request_induction_url: string | null;
    paused_at: string | null;
    is_paused: boolean;
    live: boolean;
    equipment: EquipmentResource[];
    user_course_induction?: InductionResource | null;
    trainers?: InductionResource[];
    urls: {
        show: string;
    };
};

// Additional types for page props
export type Member = {
    id: number;
    name: string;
};