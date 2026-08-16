// Resource types that exactly match the backend Resource classes
// These should be kept in sync with:
// - app/Http/Resources/TrainingRecordResource.php
// - app/Http/Resources/CourseResource.php
// - app/Http/Resources/EquipmentResource.php

export type TrainingRecordResource = {
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
    removeFromWaitlist: string;
  };
};

export type EquipmentResource = {
  id: number;
  name: string;
  slug: string;
  working: boolean;
  permaloan: boolean;
  dangerous: boolean;
  lone_working: boolean;
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
  user_course_training_record?: TrainingRecordResource | null;
  trainers?: TrainingRecordResource[];
  urls: {
    show: string;
    training: string;
  };
};

export type EquipmentListResource = {
  id: number;
  name: string;
  slug: string;
  requires_induction: boolean;
  accepting_inductions: boolean;
  working: boolean;
  permaloan: boolean;
  dangerous: boolean;
  lone_working: boolean;
  photo_url: string | null;
  room_display: string | null;
  trained: boolean;
  access_code?: string;
  can: {
    update: boolean;
  };
  urls: {
    show: string;
    edit: string;
  };
};

export type EquipmentFormResource = {
  id: number;
  name: string;
  slug: string;
  manufacturer: string | null;
  model_number: string | null;
  room_id: number | null;
  detail: string | null;
  description: string | null;
  help_text: string | null;
  docs: string | null;
  maintainer_group_id: number | null;
  working: boolean;
  permaloan: boolean;
  permaloan_user_id: number | null;
  permaloan_user: Member | null;
  dangerous: boolean;
  lone_working: boolean;
  ppe: string[];
  access_fee: number;
  usage_cost: number;
  usage_cost_per: string | null;
  access_code: string | null;
  admin_notes: string | null;
  course_id: number | null;
  requires_induction: boolean;
  induction_category: string | null;
  accepting_inductions: boolean;
  induction_instructions: string | null;
  trainer_instructions: string | null;
  trained_instructions: string | null;
  photos: { index: number; url: string; destroy_url: string }[];
  urls: {
    update: string;
    show: string;
    photo_store: string;
  };
};

export type RoomResource = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  equipment_count: number;
  urls: {
    show: string;
  };
};

export type MemberAvatar = {
  id: number;
  name: string;
  profile_photo_url: string | null;
  url: string;
};

export type EquipmentAreaResource = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  area_coordinators: MemberAvatar[];
  urls: {
    show: string;
  };
};

export type MaintainerGroupResource = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  equipment_area: { id: number; name: string; url: string } | null;
  maintainers: MemberAvatar[];
  equipment?: { name: string; url: string }[];
  equipment_count: number;
  urls: {
    show: string;
  };
};

export type RoleResource = {
  id: number;
  name: string;
  title: string | null;
  description: string | null;
  email_public: string | null;
  email_private: string | null;
  slack_channel: string | null;
  member_count: number;
  members: { id: number; name: string }[];
  urls: {
    edit: string;
  };
};

export type AccessLockdownResource = {
  id: number;
  reason: string | null;
  roles: string[];
  started_by: string | null;
  started_at: string | null;
  lifted_by: string | null;
  lifted_at: string | null;
};

// Additional types for page props
export type Member = {
  id: number;
  name: string;
};
