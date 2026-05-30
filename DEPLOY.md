# DCM — cPanel Deployment Guide

> Generated: 2026-05-30  
> Commit: 422bb10 — "Add major LMS features: chat, categories, recommendations, org module, UI redesigns"  
> Run this every time you push changes from localhost to the live server.

---

## 1. Upload files to cPanel

Use **File Manager → Upload** or FTP/SFTP. Upload every file below to the matching path.

### Modified files (overwrite on server)

```
assets/css/dcm-system.css

data_files/ajax/ajax_change_password.php
data_files/ajax/ajax_fetch_lessons_paid.php
data_files/ajax/ajax_get_courses.php
data_files/ajax/ajax_login.php
data_files/ajax/ajax_org_admin.php
data_files/ajax/ajax_organizations.php
data_files/ajax/ajax_qb_taxonomy.php
data_files/ajax/ajax_save_course.php
data_files/ajax/ajax_student_exam.php
data_files/ajax/ajax_update_course_settings.php
data_files/ajax/ajax_update_lesson.php

data_files/config/dump.php

data_files/index.php

data_files/pages/admin_course_reviews.php
data_files/pages/admin_courses.php
data_files/pages/admin_org_detail.php
data_files/pages/admin_organizations.php
data_files/pages/admin_users.php
data_files/pages/change_password_request.php
data_files/pages/controller.php
data_files/pages/course_contents_management.php
data_files/pages/course_settings.php
data_files/pages/learning_chat_call.php
data_files/pages/learning_student_home.php
data_files/pages/lesson_input_form.php
data_files/pages/modal_lunch.php
data_files/pages/my_courses_online_contents_list_view.php
data_files/pages/org_members.php
data_files/pages/org_reports.php
data_files/pages/qb_taxonomy.php
data_files/pages/read_course_details_data.php
data_files/pages/side_menu.php
data_files/pages/student_profile_completion.php
data_files/pages/teacher_profile.php
data_files/pages/teacher_study_notes.php
data_files/pages/view_course_details.php
data_files/pages/view_lesson_contents.php
```

### New files (upload fresh — do NOT exist on server yet)

```
data_files/ajax/ajax_categories.php
data_files/ajax/ajax_chat.php
data_files/ajax/ajax_chat_upload.php
data_files/ajax/ajax_combinations.php
data_files/ajax/ajax_delete_chapter.php
data_files/ajax/ajax_recommendations.php
data_files/ajax/ajax_rename_course.php
data_files/ajax/ajax_reorder_chapters.php
data_files/ajax/ajax_save_course_categories.php

data_files/pages/admin_categories.php
data_files/pages/admin_combinations.php
data_files/pages/student_interests.php
```

### Create new directories on server (if they don't exist)

```
data_files/uploads/chat/         ← chmod 777
data_files/uploads/               ← chmod 777  (already exists, verify writable)
```

### Do NOT upload

| Path | Reason |
|---|---|
| `.env` | Contains secrets — create manually (see §3) |
| `data_files/uploads/` | Media files already live on server |
| `data_files/websocket/` | Requires persistent PHP process — not for shared hosting |
| `.claude/` | Local dev tooling |
| `data_files/ajax/tmp_session_init2.php` | Dev testing helper — delete if it exists on server |
| `migration.sql` | Run via phpMyAdmin, not uploaded as a file |
| `DEPLOY.md` | This file — dev reference only |

---

## 2. Run the SQL migration

1. Open **cPanel → phpMyAdmin** and select your live database.
2. Click the **SQL** tab.
3. Open `migration.sql` from your local machine and **paste the full contents**.
4. Click **Go**.

The script is fully **idempotent** — safe to run multiple times:
- `CREATE TABLE IF NOT EXISTS` for every new table
- `_dcm_add_col` procedure adds columns only when missing
- `INSERT IGNORE` for all seed data
- Does **NOT** drop, truncate, or modify existing data

### New tables this release

| Table | Purpose |
|---|---|
| `tbl_chat_conversations` | Chat threads (direct + group) |
| `tbl_chat_participants` | Who is in each conversation |
| `tbl_chat_messages` | Individual messages |
| `tbl_chat_presence` | Online/offline heartbeat |
| `tbl_course_category_map` | Many-to-many: courses ↔ categories |
| `tbl_level_category_map` | Academic-level → category priority rules |
| `tbl_student_interests` | Student → category interest selections |
| `tbl_student_profiles` | Student academic level, stream, combination |
| `tbl_combinations` | Subject combinations (PCM, PCB, HGL…) |

### New columns on existing tables

| Table | Column |
|---|---|
| `tbl_course_categories` | `category_code`, `category_description`, `created_by`, `sort_order` |
| `tbl_all_users` | `totp_secret`, `totp_enabled`, `force_pw_change` |
| `tbl_course_chapters` | `order` |
| `tbl_course_chapter_lessons` | `lesson_thumbnail`, `content_type`, `sort_order` |
| `tbl_notifications` | `type`, `icon`, `color`, `link`, `ws_sent` |

---

## 3. Create / update the `.env` file on the server

The `.env` must live at the **project root** (same folder as `data_files/`).

> ⚠️ Use the **exact same key** as your localhost `.env`.  
> Mismatched keys break all existing encrypted course/lesson URLs.

File path: `/home/<your-cpanel-user>/public_html/dcm/.env`

```
# DCM Application Environment
# SECURITY: Never commit this file to version control.

URL_CRYPT_KEY=<paste exact value from localhost .env>
```

---

## 4. Create the chat uploads directory

SSH into cPanel or use File Manager:

```bash
mkdir -p public_html/dcm/data_files/uploads/chat
chmod 777 public_html/dcm/data_files/uploads/chat
chmod 777 public_html/dcm/data_files/uploads
```

---

## 5. Post-deployment verification checklist

- [ ] Login works for all roles (student, instructor, org admin, super admin)
- [ ] Student dashboard shows personalised recommendations
- [ ] Student profile completion page loads and saves correctly
- [ ] Student interests page — categories are clickable and save
- [ ] Course detail page loads (encrypted token resolves)
- [ ] Course settings page saves title, categories, thumbnail
- [ ] Chat module loads at `?view=learning-chat-call`
- [ ] Chat send message works
- [ ] Admin categories page — cards load, edit/delete work
- [ ] Admin combinations page — cards load by stream
- [ ] Admin courses page — KPI cards animate, table loads
- [ ] Admin users page — users load, add/edit work
- [ ] QB taxonomy pages (subjects, levels, chapters…) — cards load
- [ ] Instructor: Create course modal shows category grid
- [ ] Instructor: My Courses page — category edit modal works
- [ ] Instructor: Course settings — save updates DB
- [ ] Student: Exam module hidden for Undergraduate/Courses level students
- [ ] Org admin: reports, members pages load

---

## 6. Rollback

If something breaks, restore from your cPanel file backup.  
The SQL migration is **additive-only** — no rollback SQL needed (new tables/columns are simply unused if you revert PHP files).
