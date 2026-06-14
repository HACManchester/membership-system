# Domain Subsystems

What each part of the system does, the key code, and an honest assessment of strengths and
weaknesses. The billing subsystem has its own document ([billing.md](billing.md)), as does the
operational picture ([operations.md](operations.md)).

---

## 1. Member accounts & profiles

**Purpose:** registration, profile data, photo approval, email confirmation, leaving/rejoining.

**Key code:** `app/Entities/User.php`, `app/Entities/ProfileData.php`, `app/Entities/Address.php`,
`app/Http/Controllers/AccountController.php`, `ProfileController.php`, `app/Observer/UserObserver.php`.

**Data held:** `User` carries identity (given/family/display/announce name, pronouns, contact
details), lifecycle (`status`, `active`, `banned`, `induction_completed`, `trusted`, `key_holder`),
payment fields, and privacy flags (`profile_private`, `suppress_real_name`, `online_only`).
`ProfileData` holds social links, bio, skills, and photo flags. `Address` holds the postal address
with an `approved` flag.

**Flows:**

- **Registration** (`AccountController@store`): full members provide address + photo; users start
  in `status='setting-up'` and must confirm email. **Online-only** registration
  (`@createOnlineOnly`) skips physical details.
- **Email confirmation:** changing email clears verification and re-triggers confirmation
  (`UserObserver` `updating` hook).
- **Photo approval:** uploads land as `new_profile_photo` pending admin approval; declines fire
  `MemberPhotoWasDeclined` and an email.
- **Lifecycle:** `setting-up → active → payment-warning → suspended → leaving/left`.
  `UserObserver@saved` watches transitions and fires events, emails, and Telegram notifications.
- **Deletion** (`AccountController@destroy`): hard-deletes only `setting-up` users; everyone else
  is set to `leaving`.

**Good:** clear status lifecycle; observer-driven side effects keep controllers thin; explicit
audit fields for sensitive flags (`User::$auditFields`).
**Could improve:** `AccountController` is a ~557-line god controller mixing registration, profile,
admin updates, and subscription changes; boolean casting on `User` is incomplete (TODO at
`User.php:141`); deletion doesn't cascade to related records.

---

## 2. General induction

**Purpose:** a one-time health-and-safety induction gate that unlocks physical access features.

**Key code:** `app/Http/Controllers/GeneralInductionController.php`,
`User.induction_completed` / `inducted_by`, `GeneralInductionCodeRule` (code stored in Settings).

**Flow:** member attends an induction, receives a code, submits it at `/general_induction`
(optionally registering a key fob in the same step). `UserRepository::recordInductionCompleted()`
records completion and who inducted them.

**What it gates:** key fob / access code registration (`KeyFobController@store` checks
`induction_completed`), and is a prerequisite for equipment training flows.

**Good:** small, single-purpose; combines fob registration to reduce onboarding friction.

---

## 3. Equipment & training

**Purpose:** equipment catalogue, induction/training requirements, trainers, and the newer
course-based training system.

**Key code:** `app/Entities/Equipment.php`, `Induction.php`, `Course.php`, `EquipmentArea.php`,
`MaintainerGroup.php`; controllers `EquipmentController`, `InductionController`,
`CourseController`, `CourseInductionController`, `CourseTrainingController`.

**Two systems run in parallel:**

- **Legacy:** `Equipment.induction_category` ↔ `Induction.key` string matching. Still used by
  `NotificationEmailController` to find trainers/trained users.
- **Modern:** `Course` ↔ `Equipment` many-to-many (`course_equipment`), `Induction.course_id`,
  sign-off requests with 7-day expiry (`Induction.php` `sign_off_requested_at`), bulk training
  (`CourseTrainingController@bulkTrain`). A migration command
  (`MigrateCourseEquipmentData`, tested) moves data across.

**Concepts:** *trainer* = `Induction.is_trainer` for a piece of equipment; *maintainer* = member of
a `MaintainerGroup` linked to equipment; *area coordinator* = member of an `EquipmentArea`. Policies
(`InductionPolicy`, `EquipmentPolicy`, `CoursePolicy`) grant these groups management rights, with
authorization carried by FormRequests (`TrainInductionRequest::authorize()` etc.).

**Flows:** member requests induction → trainer marks trained (fires
`InductionCompletedEvent`) → optionally promoted to trainer. Course flow adds a member-initiated
sign-off request step.

**Good:** the course system is the best-engineered area of the codebase — policy-driven
authorization, events, Inertia UI, strong test coverage. It's the reference implementation for
new work (see architecture.md).
**Could improve:** finish retiring the legacy `induction_category` path (the dual system confuses
both code and humans); equipment soft-delete doesn't cascade to inductions.

---

## 4. Physical access (key fobs & access codes)

**Purpose:** issue/revoke access credentials and feed them to the door system.

**Key code:** `app/Entities/KeyFob.php`, `KeyFobController`, `KeyFobCsvController`,
`routes/api.php`.

**Flows:**

- Members register a fob ID or request a generated access code (`KeyFobController@store`) — gated
  on `active` account + completed general induction.
- Lost fobs are soft-revoked (`markLost()` → `lost=true, active=false`).
- The door system periodically pulls a token-authenticated export of active credentials
  (`/api/keyfobs/csv`) — filtered to active fobs of active users, so suspension or banning revokes
  door access on the next poll. One-way pull; door events are not fed back. The export format is a
  de-facto contract with the door controller — coordinate any change to `KeyFobCsvController`
  with the acs team, and keep `KeyFobCsvTest` green.

**Good:** simple model; access revocation falls naturally out of membership status; uses
`announce_name` rather than real name.

---

## 5. Disciplinary / bans

**Purpose:** ban and unban members.

**Key code:** `app/Http/Controllers/DisciplinaryController.php`; `User.banned`, `banned_reason`,
`banned_date`.

**Flow:** admin bans with a required reason → `active=false`, `status='left'`, `banned=true`,
timestamped. Banning removes door access and directory visibility, and is checked at middleware
level. Unban clears the flags but does not restore membership status.

**Good:** reason + timestamp captured; covered by feature tests.
**Could improve:** reusing `status='left'` overloads the leaving state (distinguishable only via
the `banned` flag).

---

## 6. Roles & ad-hoc notifications

**Purpose:** team/role management and bulk email to member groups.

**Key code:** `app/Entities/Role.php`, `app/Traits/UserRoleTrait.php`, `RolesController`,
`RoleUsersController`, `NotificationEmailController`, `NewsletterController`.

**Roles** are rows in `roles` with a `role_user` pivot, seeded by migrations (admin, board,
finance, membership, comms, safety, storage, equipment, laser, acs, metalworking, woodworking,
3dprinting, welding…). Assignment is admin-only.

**Notification emails** (`NotificationEmailController`): admins can email all members; equipment
trainers can email people trained / awaiting training on their tool. Recipient resolution still
uses the legacy `Induction.key` system.

**Newsletter** (`NewsletterController`): admin-only Inertia page for exporting recipient lists
into an external mail tool.

**Good:** role concept is simple and visible; trainer-scoped email is a nice delegation pattern.
**Could improve:** authorization logic in the notification controller is duplicated between
`create()` and `store()` and hand-rolled rather than policy-based — converging on policies (see
architecture.md) makes it easier to maintain.

---

## 7. Discourse integration

**Purpose:** keep the Discourse forum in sync with membership status.

**Key code:** `app/Jobs/DiscourseSync.php` (queued), `app/Listeners/DiscourseSyncSubscriber.php`,
`config/discourse.php`.

**Mechanics:** triggered on login, on `MemberBecameActive`/`MemberBecameInactive`, and on
`MemberDiscourseParamsChanged` (fired by `UserObserver` when relevant fields change). Pushes a
signed SSO payload to Discourse's `sync_sso` endpoint, moving users between `active_members` and
`previous_members` groups. Respects `suppress_real_name`.

**Good:** event-driven and queued; privacy flag honoured; failures logged to Sentry.
**Could improve:** no retry/backoff on failure (a failed sync silently leaves Discourse stale);
the tracked-fields list in `UserObserver` must be manually kept in sync with
`DiscourseSync::sso_params` (TODO in code).

---

## 8. Stats, leaderboard, directory

**Key code:** `StatsController`, `LeaderboardController`, `MembersController`, `LinksController`.

- **Stats** (`/stats`, members-only): income projections vs costs (currently hardcoded in
  `StatsController` — should move to settings), payment-method and amount distributions, 7-day
  member-count history, fixed-vs-variable DD migration tracker. Aggregates only.
- **Leaderboard** (members-only): top trainers by induction count over selectable timeframes.
- **Member directory** (`/members`, members-only): active members with display name + photo.
- **Links** (`/links/forum`): redirect that records `visited_forum` for engagement tracking.

---

## 9. Cross-cutting

- **`UserObserver`** (`app/Observer/UserObserver.php`) — the heart of lifecycle side effects:
  status-transition events, welcome/warning/suspension/leaving emails, Telegram notices, Discourse
  param tracking. Powerful but increasingly crowded; candidates for extraction into listeners.
- **Auditing** — `owen-it/laravel-auditing` is installed but no model uses its `Auditable` trait
  (so the package is currently dormant). The live audit mechanism
  is the custom `UserAuditObserver` writing to an `AuditLog` table for three User flags
  (`induction_completed`, `trusted`, `key_holder`). Worth unifying.
- **TelegramHelper** — leveled notifications (job/log/render/error/warning) to an operational
  chat; gracefully no-ops when unconfigured. Exception notifications are rate-limited
  (`app/Exceptions/Handler.php`).
- **FlashNotification** — session-flash facade used consistently across controllers.
- **Presenters** (`laracasts/presenter`) — legacy view-formatting layer; migrate to accessors as
  models are touched.

## Adjacent dead/vestigial areas

Migrations exist for features with no live code: proposals/votes, devices/detected_devices,
equipment_log, access_log. The `Gift` entity has a registration flow but no management UI. Treat
these as candidates for removal or revival, not as active subsystems.
