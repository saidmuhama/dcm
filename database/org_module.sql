-- ═══════════════════════════════════════════════════════════════════
--  DCM — Organization / School Management Module Schema
--  Run once against the `e_learning` database
-- ═══════════════════════════════════════════════════════════════════
SET FOREIGN_KEY_CHECKS = 0;

-- ── Subscription plans ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_plans` (
  `id`             int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_name`      varchar(100)     NOT NULL,
  `plan_code`      varchar(50)      NOT NULL,
  `max_users`      int(11)          NOT NULL DEFAULT 50    COMMENT '-1 = unlimited',
  `max_storage_gb` int(11)          NOT NULL DEFAULT 10    COMMENT '-1 = unlimited',
  `price_monthly`  decimal(10,2)    DEFAULT  0.00,
  `price_yearly`   decimal(10,2)    DEFAULT  0.00,
  `features`       json             DEFAULT  NULL,
  `is_active`      tinyint(1)       NOT NULL DEFAULT 1,
  `created_at`     timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_code` (`plan_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `tbl_org_plans`
  (`plan_name`,`plan_code`,`max_users`,`max_storage_gb`,`price_monthly`,`price_yearly`,`features`)
VALUES
  ('Starter',      'starter',      50,   10,  0.00,    0.00,    '["Up to 50 users","10 GB storage","Basic reports","Email support"]'),
  ('Professional', 'professional', 200,  50,  49.00,   490.00,  '["Up to 200 users","50 GB storage","Advanced reports","Priority support","CSV/Excel import"]'),
  ('Enterprise',   'enterprise',   1000, 200, 199.00,  1990.00, '["Up to 1000 users","200 GB storage","Full analytics","Dedicated support","Custom branding","API access"]'),
  ('Unlimited',    'unlimited',    -1,   -1,  499.00,  4990.00, '["Unlimited users","Unlimited storage","All features","24/7 support","White-label","SLA guarantee"]');

-- ── Organizations ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_organizations` (
  `id`               int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_code`         varchar(20)      NOT NULL,
  `org_name`         varchar(200)     NOT NULL,
  `org_type`         enum('school','college','company','institution','training_center') NOT NULL DEFAULT 'school',
  `logo`             varchar(500)     DEFAULT NULL,
  `email`            varchar(200)     DEFAULT NULL,
  `phone`            varchar(50)      DEFAULT NULL,
  `address`          text             DEFAULT NULL,
  `country`          varchar(100)     DEFAULT NULL,
  `domain`           varchar(200)     DEFAULT NULL,
  `plan_id`          int(10) UNSIGNED DEFAULT NULL,
  `status`           enum('active','suspended','expired','pending') NOT NULL DEFAULT 'pending',
  `license_expires_at` date           DEFAULT NULL,
  `max_users`        int(11)          NOT NULL DEFAULT 50,
  `storage_limit_gb` int(11)          NOT NULL DEFAULT 10,
  `storage_used_mb`  bigint(20)       NOT NULL DEFAULT 0,
  `admin_usr_code`   varchar(200)     DEFAULT NULL,
  `created_by`       varchar(200)     DEFAULT NULL,
  `notes`            text             DEFAULT NULL,
  `created_at`       timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`       timestamp        NULL     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_code` (`org_code`),
  KEY `idx_status`   (`status`),
  KEY `idx_type`     (`org_type`),
  KEY `idx_admin`    (`admin_usr_code`),
  KEY `idx_plan`     (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Departments within an organization ───────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_departments` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_code`      varchar(20)      NOT NULL,
  `dept_name`     varchar(200)     NOT NULL,
  `dept_code`     varchar(50)      DEFAULT NULL,
  `description`   text             DEFAULT NULL,
  `head_usr_code` varchar(200)     DEFAULT NULL,
  `status`        enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`    timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_org`    (`org_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Organization members ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_members` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_code`    varchar(20)      NOT NULL,
  `usr_code`    varchar(200)     NOT NULL,
  `org_role`    enum('admin','coordinator','instructor','student','staff') NOT NULL DEFAULT 'student',
  `dept_id`     int(10) UNSIGNED DEFAULT NULL,
  `employee_id` varchar(100)     DEFAULT NULL,
  `status`      enum('active','suspended') NOT NULL DEFAULT 'active',
  `invited_by`  varchar(200)     DEFAULT NULL,
  `joined_at`   timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_member` (`org_code`, `usr_code`),
  KEY `idx_org`    (`org_code`),
  KEY `idx_usr`    (`usr_code`),
  KEY `idx_role`   (`org_role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Course access grants ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_course_access` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_code`   varchar(20)      NOT NULL,
  `course_id`  int(11)          NOT NULL,
  `granted_by` varchar(200)     DEFAULT NULL,
  `granted_at` timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` date             DEFAULT NULL,
  `is_active`  tinyint(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_access` (`org_code`, `course_id`),
  KEY `idx_org`    (`org_code`),
  KEY `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Pending email invitations ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_invitations` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_code`     varchar(20)      NOT NULL,
  `email`        varchar(200)     NOT NULL,
  `org_role`     enum('admin','coordinator','instructor','student','staff') NOT NULL DEFAULT 'student',
  `dept_id`      int(10) UNSIGNED DEFAULT NULL,
  `invite_token` varchar(100)     NOT NULL,
  `invited_by`   varchar(200)     DEFAULT NULL,
  `expires_at`   timestamp        NOT NULL,
  `accepted_at`  timestamp        NULL DEFAULT NULL,
  `status`       enum('pending','accepted','expired','cancelled') NOT NULL DEFAULT 'pending',
  `created_at`   timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_token` (`invite_token`),
  KEY `idx_org`    (`org_code`),
  KEY `idx_email`  (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Audit / activity log ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_activity` (
  `id`             int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_code`       varchar(20)      NOT NULL,
  `actor_usr_code` varchar(200)     NOT NULL,
  `action`         varchar(100)     NOT NULL,
  `target_type`    varchar(50)      DEFAULT NULL,
  `target_id`      varchar(200)     DEFAULT NULL,
  `details`        json             DEFAULT NULL,
  `ip_address`     varchar(45)      DEFAULT NULL,
  `created_at`     timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_org`     (`org_code`),
  KEY `idx_actor`   (`actor_usr_code`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
