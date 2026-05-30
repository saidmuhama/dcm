# DCM — cPanel Deployment Guide

> Generated: 2026-05-25  
> Run this every time you push changes from localhost to the live server.

---

## 1. Upload files to cPanel

Use File Manager or FTP. Upload **every file in the list below** to the matching path on the server.

### Modified files (overwrite)

```
assets/css/dcm-system.css

data_files/ajax/ajax_change_password.php
data_files/ajax/ajax_fetch_lessons_paid.php
data_files/ajax/ajax_login.php
data_files/ajax/ajax_org_admin.php
data_files/ajax/ajax_organizations.php
data_files/ajax/ajax_update_course_settings.php
data_files/ajax/ajax_update_lesson.php

data_files/config/dump.php

data_files/index.php

data_files/pages/admin_course_reviews.php
data_files/pages/admin_org_detail.php
data_files/pages/admin_organizations.php
data_files/pages/change_password_request.php
data_files/pages/course_contents_management.php
data_files/pages/course_settings.php
data_files/pages/lesson_input_form.php
data_files/pages/org_members.php
data_files/pages/org_reports.php
data_files/pages/read_course_details_data.php
data_files/pages/side_menu.php
data_files/pages/teacher_profile.php
data_files/pages/view_course_details.php
data_files/pages/view_lesson_contents.php
```

### New files (upload fresh — these do not exist on the server yet)

```
data_files/ajax/ajax_delete_chapter.php
data_files/ajax/ajax_rename_course.php
data_files/ajax/ajax_reorder_chapters.php
```

### Do NOT upload

| Path | Reason |
|---|---|
| `.env` | Contains secrets — create manually on server (see §3) |
| `data_files/uploads/` | Media files — already live on server / CDN |
| `data_files/websocket/` | Requires persistent PHP process — not supported on shared hosting |
| `.claude/` | Local dev tooling only |
| `migration.sql` | Run via phpMyAdmin, not uploaded as a file |
| `DEPLOY.md` | This file — dev reference only |

---

## 2. Run the SQL migration

1. Open **cPanel → phpMyAdmin** and select your live database.
2. Click the **SQL** tab.
3. Open `migration.sql` from your local machine and **paste the full contents**.
4. Click **Go**.

The script is fully idempotent:
- Uses `CREATE TABLE IF NOT EXISTS` for every new table.
- Uses the `_dcm_add_col` procedure to add columns only when missing.
- Does **not** drop, truncate, or modify any existing data.

Safe to run multiple times.

---

## 3. Create the `.env` file on the server

The `.env` file must live at the **project root** (same folder as `data_files/`).

> ⚠️ This file must contain the **exact same key** as your localhost `.env`.  
> If the keys differ, all existing encrypted course/lesson URLs will break.

Create the file at: `/home/<your-cpanel-user>/public_html/dcm/.env`  
(adjust the path to match your actual document root)

Contents:

```
# DCM Application Environment
# SECURITY: Never commit this file to version control.

URL_CRYPT_KEY=<paste the value from your localhost .env here>
```

---

## 4. Verify after deployment

Open the live site and check each of these:

- [ ] Login works (role: student, instructor, org_admin, super_admin)
- [ ] Course detail page opens for a paid course (encryption tokens resolve)
- [ ] Course content page (`read_course_details_data`) loads without white screen
- [ ] Unenrolled user hitting a paid course sees the enrollment wall, not the content
- [ ] Org admin → Members page loads and Add Member modal opens
- [ ] Org admin → Reports page shows statistics (not blank)
- [ ] Instructor → Course Contents: chapter reorder drag works
- [ ] Instructor → Course Contents: rename course button works
- [ ] Instructor → Course Contents: delete chapter button sends request (live course) or deletes immediately (draft)
- [ ] Footer shows "Copyright © 2026, Digital Class | Help | Terms of Use | Privacy Policy" in one row
- [ ] No PHP errors in browser (enable `display_errors` temporarily in `data_files/config/db.php` if needed, then disable)

---

## 5. Rollback

If something breaks after deployment, restore from your cPanel **File Manager backup** or Git:

```bash
# restore a single file to its last committed state
git checkout HEAD -- data_files/pages/read_course_details_data.php
```

The SQL migration is additive-only — no rollback SQL is needed (new tables/columns are simply unused if you revert the PHP).
