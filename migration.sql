-- =============================================================================
-- DCM DATABASE MIGRATION SCRIPT
-- Safe to run on cPanel — uses CREATE TABLE IF NOT EXISTS and a helper
-- procedure that skips any column or table that already exists.
-- Does NOT drop, truncate, or modify existing data.
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- =============================================================================
-- HELPER: safely add a column only when it does not already exist
-- Compatible with MySQL 8.0+ and MariaDB 10.x
-- =============================================================================
DROP PROCEDURE IF EXISTS _dcm_add_col;
DELIMITER $$
CREATE PROCEDURE _dcm_add_col(
    IN p_tbl  VARCHAR(64),
    IN p_col  VARCHAR(64),
    IN p_def  TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = p_tbl
          AND COLUMN_NAME  = p_col
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', p_tbl, '` ADD COLUMN `', p_col, '` ', p_def);
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END$$
DELIMITER ;

-- =============================================================================
-- SECTION 1: NEW COLUMNS ON EXISTING TABLES
-- =============================================================================

-- tbl_all_users — 2FA columns
CALL _dcm_add_col('tbl_all_users', 'totp_secret',  "VARCHAR(64) DEFAULT NULL");
CALL _dcm_add_col('tbl_all_users', 'totp_enabled', "TINYINT(1) NOT NULL DEFAULT 0");

-- tbl_course_chapters — drag-and-drop ordering
CALL _dcm_add_col('tbl_course_chapters', 'order', "INT(11) DEFAULT NULL");

-- tbl_course_chapter_lessons — thumbnail, content-type, sort order
CALL _dcm_add_col('tbl_course_chapter_lessons', 'lesson_thumbnail', "VARCHAR(500) DEFAULT NULL");
CALL _dcm_add_col('tbl_course_chapter_lessons', 'content_type',     "VARCHAR(200) DEFAULT 'Video'");
CALL _dcm_add_col('tbl_course_chapter_lessons', 'sort_order',       "INT(11) DEFAULT 0");

-- tbl_all_users — force password change flag (set by org admin on created/reset accounts)
CALL _dcm_add_col('tbl_all_users', 'force_pw_change', "TINYINT(1) NOT NULL DEFAULT 0");

-- tbl_notifications — add any columns that may be missing on older installs
CALL _dcm_add_col('tbl_notifications', 'type',    "VARCHAR(100) NOT NULL DEFAULT 'info'");
CALL _dcm_add_col('tbl_notifications', 'icon',    "VARCHAR(100) DEFAULT 'bi-bell'");
CALL _dcm_add_col('tbl_notifications', 'color',   "VARCHAR(30) DEFAULT '#6366f1'");
CALL _dcm_add_col('tbl_notifications', 'link',    "VARCHAR(500) DEFAULT NULL");
CALL _dcm_add_col('tbl_notifications', 'ws_sent', "TINYINT(1) DEFAULT 0");

-- =============================================================================
-- SECTION 2: NEW TABLES (all safe — skipped when they already exist)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Notifications (full schema, in case table itself doesn't exist yet)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_notifications` (
  `id`         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_code`  VARCHAR(200)        NOT NULL,
  `type`       VARCHAR(100)        NOT NULL DEFAULT 'info',
  `title`      VARCHAR(255)        NOT NULL,
  `body`       TEXT                DEFAULT NULL,
  `link`       VARCHAR(500)        DEFAULT NULL,
  `icon`       VARCHAR(100)        DEFAULT 'bi-bell',
  `color`      VARCHAR(30)         DEFAULT '#6366f1',
  `is_read`    TINYINT(1)          DEFAULT 0,
  `ws_sent`    TINYINT(1)          DEFAULT 0,
  `created_at` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_unread` (`user_code`, `is_read`),
  KEY `idx_ws_pending`  (`ws_sent`),
  KEY `idx_created`     (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 2FA role policy
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_2fa_role_policy` (
  `role_id`     INT(11)    NOT NULL,
  `require_2fa` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- Course review requests (instructor → admin approval workflow)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_course_review_requests` (
  `id`              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id`       BIGINT(20) UNSIGNED NOT NULL,
  `instructor_id`   VARCHAR(200)        NOT NULL,
  `status`          ENUM('pending','approved','rejected','revision_needed') NOT NULL DEFAULT 'pending',
  `instructor_note` TEXT                DEFAULT NULL,
  `admin_comment`   TEXT                DEFAULT NULL,
  `submitted_at`    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at`     TIMESTAMP           NULL     DEFAULT NULL,
  `reviewed_by`     VARCHAR(200)        DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course`      (`course_id`),
  KEY `idx_status`      (`status`),
  KEY `idx_instructor`  (`instructor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- Chapter deletion requests (instructor → admin approval workflow)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_chapter_deletion_requests` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `chapter_id`      INT(11)      NOT NULL,
  `course_id`       INT(11)      NOT NULL,
  `instructor_id`   VARCHAR(100) NOT NULL,
  `chapter_title`   VARCHAR(500) NOT NULL,
  `lesson_count`    INT(11)      DEFAULT 0,
  `status`          ENUM('pending','approved','rejected') DEFAULT 'pending',
  `instructor_note` TEXT         DEFAULT NULL,
  `admin_comment`   TEXT         DEFAULT NULL,
  `reviewed_by`     VARCHAR(100) DEFAULT NULL,
  `requested_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at`     TIMESTAMP    NULL     DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- Module permissions (feature-flag / role gating)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_module_permissions` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `module_key` VARCHAR(100) NOT NULL,
  `role_id`    INT(11)      NOT NULL,
  `is_enabled` TINYINT(1)   NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_mr` (`module_key`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- Main academic levels
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_main_academic_levels` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `level_title` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `level_title` (`level_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- SMS logs
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_sms_logs` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `phone_number` VARCHAR(30)  DEFAULT NULL,
  `message_body` TEXT         DEFAULT NULL,
  `message_id`   VARCHAR(100) DEFAULT NULL,
  `status`       VARCHAR(50)  DEFAULT NULL,
  `status_code`  VARCHAR(20)  DEFAULT NULL,
  `sms_cost`     VARCHAR(50)  DEFAULT NULL,
  `api_response` LONGTEXT     DEFAULT NULL,
  `created_at`   DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- Study notes & bookmarks
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `study_notes` (
  `id`           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id`    BIGINT(20) UNSIGNED NOT NULL,
  `chapter_id`   BIGINT(20) UNSIGNED NOT NULL,
  `lesson_id`    BIGINT(20) UNSIGNED NOT NULL,
  `question`     TEXT                NOT NULL,
  `answer`       LONGTEXT            NOT NULL,
  `language`     VARCHAR(20)         DEFAULT 'EN',
  `is_important` TINYINT(1)          DEFAULT 0,
  `sort_order`   INT(11)             DEFAULT 0,
  `created_by`   VARCHAR(200)        NOT NULL,
  `created_at`   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP           NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lesson` (`lesson_id`),
  KEY `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `study_note_bookmarks` (
  `id`            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       VARCHAR(200)        NOT NULL,
  `study_note_id` BIGINT(20) UNSIGNED NOT NULL,
  `created_at`    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bookmark` (`user_id`, `study_note_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- Organisation tables
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_organizations` (
  `id`                INT(11)      NOT NULL AUTO_INCREMENT,
  `org_code`          VARCHAR(50)  NOT NULL,
  `org_name`          VARCHAR(255) NOT NULL,
  `org_type`          ENUM('school','college','company','institution','training_center','ngo','government','other') DEFAULT 'school',
  `email`             VARCHAR(255) DEFAULT NULL,
  `phone`             VARCHAR(50)  DEFAULT NULL,
  `address`           TEXT         DEFAULT NULL,
  `country`           VARCHAR(100) DEFAULT NULL,
  `domain`            VARCHAR(255) DEFAULT NULL,
  `plan_id`           INT(11)      DEFAULT NULL,
  `admin_usr_code`    VARCHAR(50)  DEFAULT NULL,
  `created_by`        VARCHAR(50)  DEFAULT NULL,
  `notes`             TEXT         DEFAULT NULL,
  `status`            ENUM('active','suspended','expired','pending') DEFAULT 'active',
  `max_users`         INT(11)      DEFAULT -1,
  `storage_limit_gb`  INT(11)      DEFAULT 10,
  `logo`              VARCHAR(255) DEFAULT NULL,
  `license_expires_at` DATE        DEFAULT NULL,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_code` (`org_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_org_plans` (
  `id`            INT(11)         NOT NULL AUTO_INCREMENT,
  `plan_code`     VARCHAR(50)     NOT NULL,
  `plan_name`     VARCHAR(100)    NOT NULL,
  `max_users`     INT(11)         DEFAULT 50,
  `max_storage_gb` INT(11)        DEFAULT 10,
  `features`      LONGTEXT        DEFAULT NULL CHECK (JSON_VALID(`features`)),
  `price_monthly` DECIMAL(10,2)   DEFAULT 0.00,
  `price_yearly`  DECIMAL(10,2)   DEFAULT 0.00,
  `is_active`     TINYINT(1)      DEFAULT 1,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_code` (`plan_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_org_members` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `org_code`    VARCHAR(50)  NOT NULL,
  `usr_code`    VARCHAR(50)  NOT NULL,
  `org_role`    ENUM('admin','coordinator','instructor','student','staff') DEFAULT 'student',
  `dept_id`     INT(11)      DEFAULT NULL,
  `employee_id` VARCHAR(100) DEFAULT NULL,
  `status`      ENUM('active','inactive','suspended') DEFAULT 'active',
  `invited_by`  VARCHAR(50)  DEFAULT NULL,
  `joined_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_member` (`org_code`, `usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_org_departments` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `org_code`     VARCHAR(50)  NOT NULL,
  `dept_name`    VARCHAR(255) NOT NULL,
  `dept_code`    VARCHAR(50)  DEFAULT NULL,
  `description`  TEXT         DEFAULT NULL,
  `head_usr_code` VARCHAR(50) DEFAULT NULL,
  `status`       ENUM('active','inactive') DEFAULT 'active',
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_org_course_access` (
  `id`         INT(11)    NOT NULL AUTO_INCREMENT,
  `org_code`   VARCHAR(50) NOT NULL,
  `course_id`  INT(11)    NOT NULL,
  `is_active`  TINYINT(1) DEFAULT 1,
  `granted_by` VARCHAR(50) DEFAULT NULL,
  `expires_at` DATE        DEFAULT NULL,
  `granted_at` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_course` (`org_code`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_org_activity` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `org_code`      VARCHAR(50)  NOT NULL,
  `actor_usr_code` VARCHAR(50) DEFAULT NULL,
  `action`        VARCHAR(100) NOT NULL,
  `target_type`   VARCHAR(50)  DEFAULT NULL,
  `target_id`     VARCHAR(100) DEFAULT NULL,
  `details`       LONGTEXT     DEFAULT NULL CHECK (JSON_VALID(`details`)),
  `ip_address`    VARCHAR(45)  DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- Question Bank tables
-- (created in dependency order: levels/subjects first, then questions/options)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `qb_levels` (
  `level_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `level_name` VARCHAR(50)  NOT NULL,
  `sort_order` INT(11)      DEFAULT 0,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_sections` (
  `section_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `section_name` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`section_id`),
  UNIQUE KEY `section_name` (`section_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_subjects` (
  `subject_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `subject_code` VARCHAR(20)  DEFAULT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_difficulty_levels` (
  `difficulty_id`   INT(11)     NOT NULL AUTO_INCREMENT,
  `difficulty_name` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`difficulty_id`),
  UNIQUE KEY `difficulty_name` (`difficulty_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_bloom_levels` (
  `bloom_id`    INT(11)     NOT NULL AUTO_INCREMENT,
  `bloom_name`  VARCHAR(50) DEFAULT NULL,
  `description` TEXT        DEFAULT NULL,
  PRIMARY KEY (`bloom_id`),
  UNIQUE KEY `bloom_name` (`bloom_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_chapters` (
  `chapter_id`     INT(11)      NOT NULL AUTO_INCREMENT,
  `subject_id`     INT(11)      NOT NULL,
  `level_id`       INT(11)      NOT NULL,
  `chapter_number` VARCHAR(20)  DEFAULT NULL,
  `chapter_name`   VARCHAR(255) NOT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`chapter_id`),
  KEY `subject_id` (`subject_id`),
  KEY `level_id`   (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_subtopics` (
  `subtopic_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `chapter_id`    INT(11)      NOT NULL,
  `subtopic_name` VARCHAR(255) NOT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`subtopic_id`),
  KEY `chapter_id` (`chapter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_questions` (
  `question_id`              BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `q_uid`                    VARCHAR(100) NOT NULL,
  `year_year`                INT(11)      DEFAULT NULL,
  `section_id`               INT(11)      DEFAULT NULL,
  `question_number`          VARCHAR(20)  DEFAULT NULL,
  `subject_id`               INT(11)      NOT NULL,
  `level_id`                 INT(11)      NOT NULL,
  `chapter_id`               INT(11)      NOT NULL,
  `subtopic_id`              INT(11)      NOT NULL,
  `difficulty_id`            INT(11)      DEFAULT NULL,
  `bloom_id`                 INT(11)      DEFAULT NULL,
  `question_stem`            LONGTEXT     NOT NULL,
  `correct_answer`           TEXT         DEFAULT NULL,
  `solution_explanation`     LONGTEXT     DEFAULT NULL,
  `swahili_hint`             TEXT         DEFAULT NULL,
  `estimated_time_seconds`   INT(11)      DEFAULT 60,
  `marks`                    DECIMAL(5,2) DEFAULT 1.00,
  `cira_flag`                TINYINT(1)   DEFAULT 0,
  `question_type`            ENUM('mcq','true_false','essay','matching','fill_blank') DEFAULT 'mcq',
  `status`                   ENUM('draft','review','approved','published','archived') NOT NULL DEFAULT 'draft',
  `created_by`               BIGINT(20)   DEFAULT NULL,
  `approved_by`              BIGINT(20)   DEFAULT NULL,
  `created_at`               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`),
  UNIQUE KEY `q_uid` (`q_uid`),
  KEY `section_id`    (`section_id`),
  KEY `subject_id`    (`subject_id`),
  KEY `level_id`      (`level_id`),
  KEY `chapter_id`    (`chapter_id`),
  KEY `subtopic_id`   (`subtopic_id`),
  KEY `difficulty_id` (`difficulty_id`),
  KEY `bloom_id`      (`bloom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_question_options` (
  `option_id`    BIGINT(20)  NOT NULL AUTO_INCREMENT,
  `question_id`  BIGINT(20)  NOT NULL,
  `option_label` VARCHAR(10) DEFAULT NULL,
  `option_text`  TEXT        NOT NULL,
  `is_correct`   TINYINT(1)  DEFAULT 0,
  `sort_order`   INT(11)     DEFAULT 0,
  `created_at`   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_question_media` (
  `media_id`    BIGINT(20)  NOT NULL AUTO_INCREMENT,
  `question_id` BIGINT(20)  NOT NULL,
  `media_type`  ENUM('image','audio','video','document') DEFAULT NULL,
  `media_path`  TEXT        NOT NULL,
  `created_at`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`media_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_question_usage` (
  `usage_id`    BIGINT(20) NOT NULL AUTO_INCREMENT,
  `question_id` BIGINT(20) NOT NULL,
  `exam_id`     BIGINT(20) DEFAULT NULL,
  `times_used`  INT(11)    DEFAULT 0,
  PRIMARY KEY (`usage_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_curriculum_refs` (
  `ref_id`           BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `question_id`      BIGINT(20)   NOT NULL,
  `competency_code`  VARCHAR(100) DEFAULT NULL,
  `learning_outcome` TEXT         DEFAULT NULL,
  PRIMARY KEY (`ref_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_exams` (
  `exam_id`              BIGINT(20)    NOT NULL AUTO_INCREMENT,
  `exam_title`           VARCHAR(255)  NOT NULL,
  `exam_code`            VARCHAR(60)   DEFAULT NULL,
  `subject_id`           INT(11)       DEFAULT NULL,
  `level_id`             INT(11)       DEFAULT NULL,
  `description`          TEXT          DEFAULT NULL,
  `instructions`         TEXT          DEFAULT NULL,
  `duration_minutes`     INT(11)       DEFAULT 60,
  `total_marks`          DECIMAL(8,2)  DEFAULT 0.00,
  `passing_marks`        DECIMAL(8,2)  DEFAULT 0.00,
  `exam_type`            ENUM('manual','random') DEFAULT 'manual',
  `status`               ENUM('draft','published','archived') DEFAULT 'draft',
  `shuffle_questions`    TINYINT(1)    DEFAULT 0,
  `shuffle_options`      TINYINT(1)    DEFAULT 0,
  `show_answers_after`   TINYINT(1)    DEFAULT 0,
  `created_by`           BIGINT(20)    DEFAULT NULL,
  `created_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `exam_code` (`exam_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_exam_questions` (
  `eq_id`           BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `exam_id`         BIGINT(20)   NOT NULL,
  `question_id`     BIGINT(20)   NOT NULL,
  `sort_order`      INT(11)      DEFAULT 0,
  `marks_override`  DECIMAL(5,2) DEFAULT NULL,
  PRIMARY KEY (`eq_id`),
  UNIQUE KEY `uq_exam_question` (`exam_id`, `question_id`),
  KEY `idx_exam_id` (`exam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_exam_sessions` (
  `session_id`          BIGINT(20)  NOT NULL AUTO_INCREMENT,
  `exam_id`             BIGINT(20)  NOT NULL,
  `student_id`          VARCHAR(50) DEFAULT NULL,
  `started_at`          TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitted_at`        TIMESTAMP   NULL     DEFAULT NULL,
  `score`               DECIMAL(8,2) DEFAULT 0.00,
  `total_marks`         DECIMAL(8,2) DEFAULT 0.00,
  `status`              ENUM('in_progress','submitted','graded') DEFAULT 'in_progress',
  `time_taken_seconds`  INT(11)     DEFAULT 0,
  `question_order`      TEXT        DEFAULT NULL,
  `option_orders`       TEXT        DEFAULT NULL,
  `flagged`             TEXT        DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `idx_exam_id` (`exam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qb_exam_answers` (
  `answer_id`    BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `session_id`   BIGINT(20)   NOT NULL,
  `question_id`  BIGINT(20)   NOT NULL,
  `answer_given` TEXT         DEFAULT NULL,
  `is_correct`   TINYINT(1)   DEFAULT 0,
  `marks_awarded` DECIMAL(5,2) DEFAULT 0.00,
  PRIMARY KEY (`answer_id`),
  KEY `idx_session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================================
-- SECTION 3: INDEXES — add only when missing (best-effort, errors are ignored
-- by the procedure because ADD INDEX can fail if index already exists).
-- Run each as a separate statement so one failure doesn't abort the script.
-- =============================================================================

-- Indexes for tbl_notifications ws_sent column (needed for WebSocket worker)
DROP PROCEDURE IF EXISTS _dcm_add_index;
DELIMITER $$
CREATE PROCEDURE _dcm_add_index(
    IN p_tbl  VARCHAR(64),
    IN p_idx  VARCHAR(64),
    IN p_cols TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = p_tbl
          AND INDEX_NAME   = p_idx
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', p_tbl, '` ADD INDEX `', p_idx, '` (', p_cols, ')');
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END$$
DELIMITER ;

CALL _dcm_add_index('tbl_notifications', 'idx_user_unread', '`user_code`, `is_read`');
CALL _dcm_add_index('tbl_notifications', 'idx_ws_pending',  '`ws_sent`');
CALL _dcm_add_index('tbl_notifications', 'idx_created',     '`created_at`');

-- =============================================================================
-- SECTION 4: SEED DATA (insert-ignore so duplicates are skipped silently)
-- =============================================================================

-- QB difficulty levels
INSERT IGNORE INTO `qb_difficulty_levels` (`difficulty_name`) VALUES
  ('Easy'), ('Medium'), ('Hard');

-- QB Bloom's taxonomy levels
INSERT IGNORE INTO `qb_bloom_levels` (`bloom_name`, `description`) VALUES
  ('Remember',     'Recall facts and basic concepts'),
  ('Understand',   'Explain ideas or concepts'),
  ('Apply',        'Use information in new situations'),
  ('Analyze',      'Draw connections among ideas'),
  ('Evaluate',     'Justify a decision or course of action'),
  ('Create',       'Produce new or original work');

-- =============================================================================
-- SECTION 5: CHAT MODULE TABLES (2026-05-29)
-- =============================================================================

-- Conversations (direct 1-on-1 and group)
CREATE TABLE IF NOT EXISTS `tbl_chat_conversations` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`            ENUM('direct','group') NOT NULL DEFAULT 'direct',
  `name`            VARCHAR(255)  DEFAULT NULL,
  `avatar`          VARCHAR(500)  DEFAULT NULL,
  `created_by`      VARCHAR(50)   NOT NULL,
  `last_message`    TEXT          DEFAULT NULL,
  `last_msg_type`   VARCHAR(20)   DEFAULT 'text',
  `last_message_at` TIMESTAMP     NULL DEFAULT NULL,
  `last_message_by` VARCHAR(50)   DEFAULT NULL,
  `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_last_msg` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Conversation participants
CREATE TABLE IF NOT EXISTS `tbl_chat_participants` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `conv_id`          BIGINT UNSIGNED NOT NULL,
  `usr_code`         VARCHAR(50)  NOT NULL,
  `role`             ENUM('member','admin') DEFAULT 'member',
  `last_read_at`     TIMESTAMP    NULL DEFAULT NULL,
  `last_read_msg_id` BIGINT UNSIGNED DEFAULT 0,
  `is_muted`         TINYINT(1)   DEFAULT 0,
  `joined_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `left_at`          TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cp` (`conv_id`, `usr_code`),
  KEY `idx_usr` (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Messages
CREATE TABLE IF NOT EXISTS `tbl_chat_messages` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `conv_id`     BIGINT UNSIGNED NOT NULL,
  `sender_code` VARCHAR(50)  NOT NULL,
  `type`        ENUM('text','image','file','audio','video','system') DEFAULT 'text',
  `body`        TEXT         DEFAULT NULL,
  `file_path`   VARCHAR(500) DEFAULT NULL,
  `file_name`   VARCHAR(255) DEFAULT NULL,
  `file_size`   INT UNSIGNED DEFAULT NULL,
  `reply_to`    BIGINT UNSIGNED DEFAULT NULL,
  `deleted_at`  TIMESTAMP    NULL DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conv_time` (`conv_id`, `created_at`),
  KEY `idx_conv_id`   (`conv_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Online presence + typing indicator
CREATE TABLE IF NOT EXISTS `tbl_chat_presence` (
  `usr_code`   VARCHAR(50)  NOT NULL,
  `status`     ENUM('online','offline') DEFAULT 'offline',
  `typing_in`  BIGINT UNSIGNED DEFAULT NULL,
  `last_seen`  TIMESTAMP    NULL DEFAULT NULL,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================================
-- SECTION 6: CATEGORISATION, INTERESTS & COMBINATIONS (2026-05-30)
-- =============================================================================

-- Extend tbl_course_categories with new metadata columns
CALL _dcm_add_col('tbl_course_categories', 'category_code',        "VARCHAR(50) DEFAULT NULL");
CALL _dcm_add_col('tbl_course_categories', 'category_description', "TEXT DEFAULT NULL");
CALL _dcm_add_col('tbl_course_categories', 'created_by',           "VARCHAR(50) DEFAULT NULL");
CALL _dcm_add_col('tbl_course_categories', 'sort_order',           "INT(11) DEFAULT 0");

-- Course ↔ Category (many-to-many junction)
CREATE TABLE IF NOT EXISTS `tbl_course_category_map` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `course_id`   BIGINT UNSIGNED NOT NULL,
  `category_id` INT(11)       NOT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cc` (`course_id`, `category_id`),
  KEY `idx_course`   (`course_id`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrate existing single category_id values into the map (idempotent)
INSERT IGNORE INTO `tbl_course_category_map` (course_id, category_id)
SELECT id, category_id FROM `tbl_courses`
WHERE category_id IS NOT NULL AND deleted_at IS NULL;

-- Academic Level ↔ Category priority map
CREATE TABLE IF NOT EXISTS `tbl_level_category_map` (
  `id`              INT          NOT NULL AUTO_INCREMENT,
  `education_level` VARCHAR(50)  NOT NULL,
  `category_code`   VARCHAR(50)  NOT NULL,
  `priority`        ENUM('high','medium','low','excluded') NOT NULL DEFAULT 'medium',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_lc` (`education_level`, `category_code`),
  KEY `idx_level` (`education_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed: Primary School
INSERT IGNORE INTO `tbl_level_category_map` (education_level,category_code,priority) VALUES
('primary','MATH','high'),('primary','LANG','high'),('primary','LIT','high'),('primary','ART','high'),('primary','ISL','high'),
('primary','BIO','medium'),('primary','IT','low'),('primary','VOC','low'),
('primary','PHY','excluded'),('primary','CHEM','excluded'),('primary','ACC','excluded'),('primary','ECO','excluded'),
('primary','ENT','excluded'),('primary','PRG','excluded'),('primary','CS','excluded'),('primary','WEB','excluded'),
('primary','MOB','excluded'),('primary','AI','excluded'),('primary','DS','excluded'),('primary','SEC','excluded'),
('primary','NET','excluded'),('primary','PRO','excluded'),('primary','BUS','excluded');
-- Seed: O-Level
INSERT IGNORE INTO `tbl_level_category_map` (education_level,category_code,priority) VALUES
('o_level','MATH','high'),('o_level','PHY','high'),('o_level','CHEM','high'),('o_level','BIO','high'),
('o_level','LANG','high'),('o_level','LIT','high'),('o_level','IT','high'),('o_level','ISL','high'),('o_level','NET','high'),
('o_level','ACC','medium'),('o_level','ECO','medium'),('o_level','ENT','medium'),('o_level','BUS','medium'),
('o_level','ART','medium'),('o_level','VOC','medium'),('o_level','PRG','low'),('o_level','CS','low'),
('o_level','AI','excluded'),('o_level','DS','excluded'),('o_level','MOB','excluded'),('o_level','PRO','excluded'),
('o_level','SEC','excluded'),('o_level','WEB','excluded');
-- Seed: A-Level
INSERT IGNORE INTO `tbl_level_category_map` (education_level,category_code,priority) VALUES
('a_level','MATH','high'),('a_level','PHY','high'),('a_level','CHEM','high'),('a_level','BIO','high'),
('a_level','CS','high'),('a_level','IT','high'),('a_level','NET','high'),('a_level','ACC','high'),
('a_level','ECO','high'),('a_level','ENT','high'),('a_level','BUS','high'),('a_level','LIT','high'),
('a_level','LANG','high'),('a_level','ART','high'),('a_level','ISL','high'),
('a_level','PRG','medium'),('a_level','WEB','medium'),('a_level','VOC','medium'),
('a_level','DS','low'),('a_level','AI','low'),('a_level','MOB','low'),('a_level','SEC','low'),('a_level','PRO','low');
-- Seed: University
INSERT IGNORE INTO `tbl_level_category_map` (education_level,category_code,priority) VALUES
('university','PRG','high'),('university','CS','high'),('university','WEB','high'),('university','MOB','high'),
('university','AI','high'),('university','DS','high'),('university','SEC','high'),('university','NET','high'),
('university','ACC','high'),('university','ECO','high'),('university','ENT','high'),('university','PRO','high'),
('university','BUS','high'),('university','MATH','medium'),('university','PHY','medium'),('university','CHEM','medium'),
('university','BIO','medium'),('university','LANG','medium'),('university','LIT','medium'),('university','ISL','medium'),
('university','IT','medium'),('university','ART','medium'),('university','VOC','low');
-- Seed: Professional
INSERT IGNORE INTO `tbl_level_category_map` (education_level,category_code,priority) VALUES
('professional','PRO','high'),('professional','PRG','high'),('professional','DS','high'),('professional','AI','high'),
('professional','SEC','high'),('professional','ACC','high'),('professional','ENT','high'),('professional','CS','high'),
('professional','WEB','high'),('professional','MOB','high'),('professional','NET','high'),('professional','BUS','high'),
('professional','ECO','medium'),('professional','MATH','medium'),('professional','IT','medium'),
('professional','LANG','medium'),('professional','ISL','medium'),('professional','VOC','medium'),
('professional','PHY','low'),('professional','CHEM','low'),('professional','BIO','low'),
('professional','LIT','low'),('professional','ART','low');
-- Seed: Pre-School
INSERT IGNORE INTO `tbl_level_category_map` (education_level,category_code,priority) VALUES
('pre_school','ART','high'),('pre_school','LANG','high'),('pre_school','LIT','medium'),
('pre_school','ISL','medium'),('pre_school','MATH','medium'),
('pre_school','IT','excluded'),('pre_school','PHY','excluded'),('pre_school','CHEM','excluded'),
('pre_school','BIO','excluded'),('pre_school','ACC','excluded'),('pre_school','ECO','excluded'),
('pre_school','ENT','excluded'),('pre_school','PRG','excluded'),('pre_school','CS','excluded'),
('pre_school','WEB','excluded'),('pre_school','MOB','excluded'),('pre_school','AI','excluded'),
('pre_school','DS','excluded'),('pre_school','SEC','excluded'),('pre_school','NET','excluded'),
('pre_school','PRO','excluded'),('pre_school','BUS','excluded'),('pre_school','VOC','excluded');

-- Student Interests (many-to-many: student ↔ category)
CREATE TABLE IF NOT EXISTS `tbl_student_interests` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `student_id`  VARCHAR(50)  NOT NULL,
  `category_id` INT(11)      NOT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_si` (`student_id`, `category_id`),
  KEY `idx_si_student`  (`student_id`),
  KEY `idx_si_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Subject Combinations (O-Level / A-Level streams)
CREATE TABLE IF NOT EXISTS `tbl_combinations` (
  `combination_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `combination_code` VARCHAR(20)  NOT NULL,
  `combination_name` VARCHAR(200) NOT NULL,
  `stream_type`      ENUM('science','arts','business','general') DEFAULT 'science',
  `subjects`         VARCHAR(500) DEFAULT NULL,
  `description`      TEXT         DEFAULT NULL,
  `status`           ENUM('active','inactive') DEFAULT 'active',
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`combination_id`),
  UNIQUE KEY `uniq_combo_code` (`combination_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student Academic Profiles (education level, stream, combination)
CREATE TABLE IF NOT EXISTS `tbl_student_profiles` (
  `profile_id`      INT(11)      NOT NULL AUTO_INCREMENT,
  `student_id`      VARCHAR(50)  NOT NULL,
  `education_level` ENUM('primary','o_level','a_level','university','professional','other') DEFAULT NULL,
  `stream`          ENUM('science','arts','business','general') DEFAULT NULL,
  `combination_id`  INT(11)      DEFAULT NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `uniq_sp_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed: 23 default course categories (insert-ignore, safe to re-run)
INSERT IGNORE INTO `tbl_course_categories` (category_code,category_title,category_description,icon,status,sort_order) VALUES
('IT',  'Information Technology','Software, hardware, networks and IT infrastructure',     'bi-laptop',         1,1),
('CS',  'Computer Science',      'Algorithms, data structures and computational theory',   'bi-cpu',            1,2),
('PRG', 'Programming',           'Coding languages, frameworks and software development',  'bi-code-slash',     1,3),
('WEB', 'Web Development',       'Front-end, back-end and full-stack web technologies',    'bi-globe2',         1,4),
('MOB', 'Mobile Development',    'Android, iOS and cross-platform mobile apps',            'bi-phone',          1,5),
('AI',  'Artificial Intelligence','Machine learning, deep learning and AI applications',   'bi-robot',          1,6),
('DS',  'Data Science',          'Data analysis, visualisation and big data',              'bi-bar-chart-line', 1,7),
('SEC', 'Cyber Security',        'Network security, ethical hacking and digital safety',   'bi-shield-lock',    1,8),
('NET', 'Networking',            'Computer networks, protocols and cloud infrastructure',  'bi-diagram-3',      1,9),
('MATH','Mathematics',           'Pure and applied mathematics across all levels',         'bi-calculator',     1,10),
('PHY', 'Physics',               'Classical and modern physics principles',                'bi-lightning',      1,11),
('CHEM','Chemistry',             'Organic, inorganic and analytical chemistry',            'bi-droplet',        1,12),
('BIO', 'Biology',               'Life sciences, genetics and ecology',                   'bi-flower1',        1,13),
('BUS', 'Business Studies',      'Business strategy, management and operations',           'bi-briefcase',      1,14),
('ENT', 'Entrepreneurship',      'Startup skills, innovation and business creation',       'bi-rocket-takeoff', 1,15),
('ACC', 'Accounting',            'Financial accounting, auditing and tax',                 'bi-receipt',        1,16),
('ECO', 'Economics',             'Micro and macroeconomics principles',                    'bi-graph-up',       1,17),
('ART', 'Arts',                  'Visual arts, design and creative expression',            'bi-palette',        1,18),
('LIT', 'Literature',            'English and world literature, creative writing',         'bi-book',           1,19),
('LANG','Languages',             'Foreign languages and linguistics',                      'bi-translate',      1,20),
('ISL', 'Islamic Studies',       'Quran, hadith, fiqh and Islamic education',              'bi-moon-stars',     1,21),
('PRO', 'Professional Courses',  'Industry certifications and professional skills',        'bi-award',          1,22),
('VOC', 'Vocational Skills',     'Practical trade and technical skills training',          'bi-tools',          1,23);

-- Seed: 13 default subject combinations
INSERT IGNORE INTO `tbl_combinations` (combination_code,combination_name,stream_type,subjects,description) VALUES
('PCM','Physics, Chemistry & Mathematics',    'science', 'Physics,Chemistry,Mathematics','Core science for engineering'),
('PCB','Physics, Chemistry & Biology',        'science', 'Physics,Chemistry,Biology',    'Core science for medicine'),
('PGM','Physics, Geography & Mathematics',   'science', 'Physics,Geography,Mathematics','Applied physical sciences'),
('CBG','Chemistry, Biology & Geography',     'science', 'Chemistry,Biology,Geography',  'Environmental life sciences'),
('EGM','Economics, Geography & Mathematics', 'science', 'Economics,Geography,Mathematics','Quantitative sciences'),
('HGL','History, Geography & Literature',    'arts',    'History,Geography,Literature', 'Humanities combination'),
('HKL','History, Kiswahili & Literature',    'arts',    'History,Kiswahili,Literature', 'East African humanities'),
('HGK','History, Geography & Kiswahili',     'arts',    'History,Geography,Kiswahili',  'Social studies with languages'),
('ECA','Economics, Commerce & Accounting',   'arts',    'Economics,Commerce,Accounting','Business arts stream'),
('HGE','History, Geography & Economics',     'arts',    'History,Geography,Economics',  'Social sciences with economics'),
('CBA','Commerce, Book-keeping & Accounting','business','Commerce,Book-keeping,Accounting','Practical business'),
('ECG','Economics, Commerce & Geography',    'business','Economics,Commerce,Geography', 'Business geography'),
('BAM','Business, Accounting & Mathematics', 'business','Business,Accounting,Mathematics','Quantitative business');

-- =============================================================================
-- SECTION 7: INSTITUTIONAL COMMERCE & COMMUNITIES (2026-05-30)
-- =============================================================================

-- ── Column extensions on existing tables ──────────────────────────────────

CALL _dcm_add_col('tbl_courses', 'org_price',      "DECIMAL(10,2) DEFAULT NULL COMMENT 'institutional base price override'");
CALL _dcm_add_col('tbl_courses', 'org_discount',   "TINYINT UNSIGNED DEFAULT 0 COMMENT 'default % discount for orgs'");
CALL _dcm_add_col('tbl_courses', 'min_seats',      "SMALLINT UNSIGNED DEFAULT 1");
CALL _dcm_add_col('tbl_courses', 'max_seats',      "SMALLINT UNSIGNED DEFAULT NULL");
CALL _dcm_add_col('tbl_courses', 'pricing_notes',  "TEXT DEFAULT NULL");
CALL _dcm_add_col('tbl_courses', 'bundle_eligible',"TINYINT(1) DEFAULT 1");

CALL _dcm_add_col('tbl_org_course_access', 'seats_purchased',     "SMALLINT UNSIGNED DEFAULT NULL");
CALL _dcm_add_col('tbl_org_course_access', 'seats_used',          "SMALLINT UNSIGNED DEFAULT 0");
CALL _dcm_add_col('tbl_org_course_access', 'purchase_request_id', "INT UNSIGNED DEFAULT NULL");
CALL _dcm_add_col('tbl_org_course_access', 'access_type',         "ENUM('open','seat_limited','dept_restricted') DEFAULT 'open'");
CALL _dcm_add_col('tbl_org_course_access', 'bundle_id',           "INT UNSIGNED DEFAULT NULL");

CALL _dcm_add_col('tbl_chat_conversations', 'linked_type',  "ENUM('course','org_group','announcement') DEFAULT NULL");
CALL _dcm_add_col('tbl_chat_conversations', 'linked_id',    "INT DEFAULT NULL");
CALL _dcm_add_col('tbl_chat_conversations', 'auto_managed', "TINYINT(1) DEFAULT 0 COMMENT 'auto-add/remove on enroll/unenroll'");
CALL _dcm_add_col('tbl_chat_conversations', 'org_code',     "VARCHAR(50) DEFAULT NULL");
CALL _dcm_add_col('tbl_chat_conversations', 'dept_id',      "INT DEFAULT NULL");

CALL _dcm_add_col('tbl_course_discussions', 'status',      "ENUM('open','closed','pinned') DEFAULT 'open'");
CALL _dcm_add_col('tbl_course_discussions', 'views',       "INT DEFAULT 0");
CALL _dcm_add_col('tbl_course_discussions', 'is_resolved', "TINYINT(1) DEFAULT 0");
CALL _dcm_add_col('tbl_course_discussions', 'pinned_by',   "VARCHAR(200) DEFAULT NULL");

CALL _dcm_add_col('tbl_course_discussion_answers', 'is_accepted', "TINYINT(1) DEFAULT 0");
CALL _dcm_add_col('tbl_course_discussion_answers', 'accepted_by', "VARCHAR(200) DEFAULT NULL");

CALL _dcm_add_col('tbl_order_items', 'bundle_id', "INT DEFAULT NULL COMMENT 'set when item is part of a bundle purchase'");

-- ── New indexes ──────────────────────────────────────────────────────────
CALL _dcm_add_index('tbl_chat_conversations', 'idx_linked',  '`linked_type`, `linked_id`');
CALL _dcm_add_index('tbl_chat_conversations', 'idx_cc_org',  '`org_code`');

-- ── Course pricing tiers (volume breaks per course) ──────────────────────
CREATE TABLE IF NOT EXISTS `tbl_course_pricing_tiers` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `course_id`     BIGINT UNSIGNED  NOT NULL,
  `min_seats`     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `max_seats`     SMALLINT UNSIGNED DEFAULT NULL COMMENT 'NULL = unlimited',
  `price`         DECIMAL(10,2)    NOT NULL,
  `label`         VARCHAR(100)     DEFAULT NULL COMMENT 'e.g. 11-25 Staff',
  `is_active`     TINYINT(1)       DEFAULT 1,
  `sort_order`    TINYINT UNSIGNED DEFAULT 0,
  `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cpt_course` (`course_id`),
  KEY `idx_cpt_seats`  (`course_id`, `min_seats`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Discount rules ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_discounts` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `discount_code` VARCHAR(50)      DEFAULT NULL,
  `discount_type` ENUM('percentage','fixed','promo') NOT NULL DEFAULT 'percentage',
  `value`         DECIMAL(10,2)    NOT NULL,
  `scope`         ENUM('course','bundle','org','global') NOT NULL DEFAULT 'course',
  `scope_id`      INT UNSIGNED     DEFAULT NULL COMMENT 'course_id, bundle_id, or NULL for org/global',
  `org_code`      VARCHAR(50)      DEFAULT NULL COMMENT 'restrict to specific org if set',
  `valid_from`    DATE             DEFAULT NULL,
  `valid_until`   DATE             DEFAULT NULL,
  `max_uses`      INT UNSIGNED     DEFAULT NULL,
  `used_count`    INT UNSIGNED     DEFAULT 0,
  `is_active`     TINYINT(1)       DEFAULT 1,
  `created_by`    VARCHAR(50)      NOT NULL,
  `notes`         TEXT             DEFAULT NULL,
  `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_disc_code` (`discount_code`),
  KEY `idx_disc_org`  (`org_code`),
  KEY `idx_disc_scope` (`scope`, `scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Institutional purchase requests ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_purchase_requests` (
  `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `request_code`     VARCHAR(30)      NOT NULL COMMENT 'e.g. PR-2026-001',
  `org_code`         VARCHAR(50)      NOT NULL,
  `course_id`        BIGINT UNSIGNED  DEFAULT NULL,
  `bundle_id`        INT UNSIGNED     DEFAULT NULL,
  `request_type`     ENUM('course','bundle','custom_quote') NOT NULL DEFAULT 'course',
  `seats_requested`  SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `expected_start`   DATE             DEFAULT NULL,
  `status`           ENUM('pending','reviewed','awaiting_payment','paid','active','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `original_price`   DECIMAL(10,2)    DEFAULT NULL,
  `discount_id`      INT UNSIGNED     DEFAULT NULL,
  `discount_value`   DECIMAL(10,2)    DEFAULT 0,
  `final_price`      DECIMAL(10,2)    DEFAULT NULL,
  `admin_remarks`    TEXT             DEFAULT NULL,
  `org_notes`        TEXT             DEFAULT NULL,
  `custom_staff_count` INT            DEFAULT NULL COMMENT 'for custom quote requests',
  `custom_budget`    VARCHAR(200)     DEFAULT NULL,
  `custom_requirements` TEXT          DEFAULT NULL,
  `reviewed_by`      VARCHAR(50)      DEFAULT NULL,
  `reviewed_at`      TIMESTAMP        NULL DEFAULT NULL,
  `paid_at`          TIMESTAMP        NULL DEFAULT NULL,
  `activated_at`     TIMESTAMP        NULL DEFAULT NULL,
  `expires_at`       DATE             DEFAULT NULL,
  `submitted_by`     VARCHAR(50)      NOT NULL,
  `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_req_code` (`request_code`),
  KEY `idx_pr_org`    (`org_code`),
  KEY `idx_pr_status` (`status`),
  KEY `idx_pr_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Purchase request audit trail ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_request_history` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `request_id`  INT UNSIGNED  NOT NULL,
  `old_status`  VARCHAR(30)   DEFAULT NULL,
  `new_status`  VARCHAR(30)   NOT NULL,
  `changed_by`  VARCHAR(50)   NOT NULL,
  `note`        TEXT          DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rh_request` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Seat assignments ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_org_seat_assignments` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `access_id`   INT UNSIGNED  NOT NULL COMMENT 'tbl_org_course_access.id',
  `org_code`    VARCHAR(50)   NOT NULL,
  `course_id`   BIGINT UNSIGNED NOT NULL,
  `usr_code`    VARCHAR(50)   NOT NULL,
  `assigned_by` VARCHAR(50)   NOT NULL,
  `assigned_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at`  TIMESTAMP     NULL DEFAULT NULL,
  `is_active`   TINYINT(1)    DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sa_active` (`access_id`, `usr_code`, `is_active`),
  KEY `idx_sa_org`    (`org_code`),
  KEY `idx_sa_course` (`course_id`),
  KEY `idx_sa_user`   (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Course bundles ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_course_bundles` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `bundle_code`      VARCHAR(50)   NOT NULL,
  `bundle_name`      VARCHAR(255)  NOT NULL,
  `bundle_type`      ENUM('subject','institutional','promotional') DEFAULT 'subject',
  `description`      TEXT          DEFAULT NULL,
  `thumbnail`        VARCHAR(500)  DEFAULT NULL,
  `individual_price` DECIMAL(10,2) DEFAULT NULL COMMENT 'override; NULL = sum of courses',
  `org_price`        DECIMAL(10,2) DEFAULT NULL,
  `status`           ENUM('active','inactive','draft') DEFAULT 'draft',
  `target_level`     VARCHAR(50)   DEFAULT NULL COMMENT 'primary, o_level, a_level etc.',
  `created_by`       VARCHAR(50)   NOT NULL,
  `sort_order`       TINYINT       DEFAULT 0,
  `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_bundle_code` (`bundle_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Bundle courses (many-to-many) ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_bundle_courses` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `bundle_id`  INT UNSIGNED    NOT NULL,
  `course_id`  BIGINT UNSIGNED NOT NULL,
  `sort_order` TINYINT         DEFAULT 0,
  `added_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_bc` (`bundle_id`, `course_id`),
  KEY `idx_bc_bundle` (`bundle_id`),
  KEY `idx_bc_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Instructor announcements ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_announcements` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `course_id`       BIGINT UNSIGNED NOT NULL,
  `sender_code`     VARCHAR(50)   NOT NULL,
  `subject`         VARCHAR(255)  NOT NULL,
  `body`            LONGTEXT      NOT NULL,
  `ann_type`        ENUM('announcement','reminder','assignment_notice','assessment_notice','discussion') DEFAULT 'announcement',
  `audience`        ENUM('all','org_only','selected') DEFAULT 'all',
  `org_code`        VARCHAR(50)   DEFAULT NULL COMMENT 'restrict to specific org if audience=org_only',
  `attachment_path` VARCHAR(500)  DEFAULT NULL,
  `sent_at`         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `scheduled_at`    TIMESTAMP     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ann_course` (`course_id`),
  KEY `idx_ann_sender` (`sender_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Announcement recipients (delivery tracking) ───────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_announcement_recipients` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `announcement_id` INT UNSIGNED NOT NULL,
  `usr_code`        VARCHAR(50)  NOT NULL,
  `is_read`         TINYINT(1)   DEFAULT 0,
  `read_at`         TIMESTAMP    NULL DEFAULT NULL,
  `delivered_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ar` (`announcement_id`, `usr_code`),
  KEY `idx_ar_user` (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Notification templates ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tbl_notification_templates` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_key` VARCHAR(80) NOT NULL,
  `title_tpl`  VARCHAR(255) NOT NULL,
  `body_tpl`   TEXT         NOT NULL,
  `icon`       VARCHAR(80)  DEFAULT 'bi-bell',
  `color`      VARCHAR(20)  DEFAULT '#6366f1',
  `channels`   VARCHAR(100) DEFAULT 'in_app' COMMENT 'in_app,sms,email comma-separated',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tmpl_key` (`template_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- seed notification templates
INSERT IGNORE INTO `tbl_notification_templates` (`template_key`,`title_tpl`,`body_tpl`,`icon`,`color`,`channels`) VALUES
('purchase_request_new',       'New Purchase Request',         'Organisation {{org_name}} submitted a request for {{course_title}}.','bi-cart-plus','#f59e0b','in_app'),
('purchase_request_reviewed',  'Request Reviewed',             'Your purchase request for {{course_title}} has been {{status}}.','bi-clipboard-check','#6366f1','in_app'),
('purchase_request_payment_due','Payment Due',                 'Invoice ready for {{course_title}}. Amount: {{amount}} TZS.','bi-receipt','#d97706','in_app'),
('purchase_request_paid',      'Payment Confirmed',            'Payment confirmed for {{course_title}}.','bi-check-circle','#16a34a','in_app'),
('course_access_granted',      'Course Access Granted',        '{{course_title}} is now available for your organisation.','bi-unlock','#16a34a','in_app'),
('license_expiring_soon',      'License Expiring Soon',        'Your license for {{course_title}} expires on {{expiry_date}}.','bi-clock-history','#dc2626','in_app'),
('announcement_received',      '{{subject}}',                  '{{preview}}','bi-megaphone','#8b5cf6','in_app'),
('seat_assigned',              'Course Seat Assigned',         'You have been assigned a seat for {{course_title}}.','bi-person-check','#16a34a','in_app'),
('seat_revoked',               'Course Seat Revoked',          'Your seat for {{course_title}} has been removed.','bi-person-x','#dc2626','in_app'),
('bundle_purchased',           'Bundle Purchase Complete',     'You now have access to all courses in {{bundle_name}}.','bi-collection','#6366f1','in_app');

-- =============================================================================
-- CLEANUP  (moved to end so all sections can use the helper procedures)
-- =============================================================================
DROP PROCEDURE IF EXISTS _dcm_add_col;
DROP PROCEDURE IF EXISTS _dcm_add_index;

SET foreign_key_checks = 1;

-- END OF MIGRATION
