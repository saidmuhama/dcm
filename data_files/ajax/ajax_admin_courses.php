<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? '') != 5) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ── helpers ─────────────────────────────────────────── */
function qi($_db, $v){ return (int)$v; }
function qs($db, $v){ return $db->real_escape_string($v); }

switch ($action) {

/* ═══════════════════════════════════════════════════════
   LIST COURSES
═══════════════════════════════════════════════════════ */
case 'list':
    $search      = qs($db, $_POST['search'] ?? '');
    $instructor  = qs($db, $_POST['instructor'] ?? '');
    $status      = qs($db, $_POST['status'] ?? '');
    $approval    = qs($db, $_POST['approval'] ?? '');
    $page        = max(1, (int)($_POST['page'] ?? 1));
    $per         = 20;
    $offset      = ($page - 1) * $per;

    $where = "WHERE c.deleted_at IS NULL";
    if ($search)     $where .= " AND (c.title LIKE '%$search%' OR CONCAT(u.first_name,' ',u.last_name) LIKE '%$search%')";
    if ($instructor) $where .= " AND c.instructor_id = '$instructor'";
    if ($status)     $where .= " AND c.status = '$status'";
    if ($approval)   $where .= " AND c.is_approved = '$approval'";

    $total = $db->query("
        SELECT COUNT(*) AS c FROM tbl_courses c
        LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
        $where
    ")->fetch_assoc()['c'];

    $rows = $db->query("
        SELECT c.id, c.title, c.status, c.is_approved, c.thumbnail, c.price, c.created_at,
               u.first_name, u.last_name, u.email_address,
               (SELECT COUNT(*) FROM tbl_course_chapters ch WHERE ch.course_id = c.id) AS chapters,
               (SELECT COUNT(*) FROM tbl_course_chapter_lessons l WHERE l.course_id = c.id) AS lessons,
               (SELECT COUNT(*) FROM tbl_course_discussions d WHERE d.course_id = c.id) AS questions,
               (SELECT COUNT(*) FROM tbl_course_enrollments e WHERE e.course_id = c.id) AS enrollments
        FROM tbl_courses c
        LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
        $where
        ORDER BY c.created_at DESC
        LIMIT $per OFFSET $offset
    ")->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','data'=>$rows,'total'=>$total,'page'=>$page,'per'=>$per]);
    break;

/* ═══════════════════════════════════════════════════════
   GET INSTRUCTORS (for filter dropdown)
═══════════════════════════════════════════════════════ */
case 'instructors':
    $rows = $db->query("
        SELECT DISTINCT u.usr_code, u.first_name, u.last_name
        FROM tbl_all_users u
        WHERE u.user_role IN ('3','4')
        ORDER BY u.first_name
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
    break;

/* ═══════════════════════════════════════════════════════
   GET SINGLE COURSE
═══════════════════════════════════════════════════════ */
case 'get':
    $id = qi($db, $_POST['id'] ?? 0);
    $row = $db->query("
        SELECT c.*, u.first_name, u.last_name
        FROM tbl_courses c
        LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
        WHERE c.id = $id LIMIT 1
    ")->fetch_assoc();
    echo json_encode(['status'=>'success','data'=>$row]);
    break;

/* ═══════════════════════════════════════════════════════
   UPDATE COURSE (title, description, status, approval)
═══════════════════════════════════════════════════════ */
case 'update_course':
    $id       = qi($db, $_POST['id']);
    $title    = qs($db, $_POST['title'] ?? '');
    $desc     = qs($db, $_POST['description'] ?? '');
    $status   = in_array($_POST['status'] ?? '', ['active','is_draft','inactive']) ? $_POST['status'] : 'is_draft';
    $approval = in_array($_POST['is_approved'] ?? '', ['pending','approved','rejected']) ? $_POST['is_approved'] : 'pending';
    $price    = (float)($_POST['price'] ?? 0);

    $db->query("UPDATE tbl_courses SET title='$title', description='$desc', status='$status', is_approved='$approval', price=$price, updated_at=NOW() WHERE id=$id");
    echo json_encode(['status'=>'success','message'=>'Course updated']);
    break;

/* ═══════════════════════════════════════════════════════
   DELETE COURSE (soft)
═══════════════════════════════════════════════════════ */
case 'delete_course':
    $id = qi($db, $_POST['id']);
    $db->query("UPDATE tbl_courses SET deleted_at=NOW() WHERE id=$id");
    echo json_encode(['status'=>'success','message'=>'Course deleted']);
    break;

/* ═══════════════════════════════════════════════════════
   GET CHAPTERS + LESSONS for a course
═══════════════════════════════════════════════════════ */
case 'get_chapters':
    $course_id = qi($db, $_POST['course_id'] ?? 0);
    $chapters = $db->query("
        SELECT id, chapter_title, `order`
        FROM tbl_course_chapters WHERE course_id=$course_id
        ORDER BY `order`, id
    ")->fetch_all(MYSQLI_ASSOC);

    foreach ($chapters as &$ch) {
        $cid = (int)$ch['id'];
        $ch['lessons'] = $db->query("
            SELECT id, lesson_title, description, content_type, file_path, sort_order, status
            FROM tbl_course_chapter_lessons
            WHERE chapter_id=$cid
            ORDER BY sort_order, id
        ")->fetch_all(MYSQLI_ASSOC);
    }
    echo json_encode(['status'=>'success','data'=>$chapters]);
    break;

/* ═══════════════════════════════════════════════════════
   REORDER LESSONS (drag & drop)
   Expects: chapter_id, ordered array of lesson IDs
═══════════════════════════════════════════════════════ */
case 'reorder_lessons':
    $data = json_decode(file_get_contents('php://input'), true);
    $items = $data['items'] ?? [];  // [{id, chapter_id}, ...]
    foreach ($items as $i => $item) {
        $lid   = qi($db, $item['id']);
        $chid  = qi($db, $item['chapter_id']);
        $order = $i + 1;
        $db->query("UPDATE tbl_course_chapter_lessons SET sort_order=$order, chapter_id=$chid WHERE id=$lid");
    }
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   REORDER CHAPTERS
═══════════════════════════════════════════════════════ */
case 'reorder_chapters':
    $data = json_decode(file_get_contents('php://input'), true);
    $ids  = $data['ids'] ?? [];
    foreach ($ids as $i => $id) {
        $id = qi($db, $id);
        $db->query("UPDATE tbl_course_chapters SET `order`=" . ($i+1) . " WHERE id=$id");
    }
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   UPDATE CHAPTER TITLE
═══════════════════════════════════════════════════════ */
case 'update_chapter':
    $id    = qi($db, $_POST['id']);
    $title = qs($db, $_POST['title'] ?? '');
    $db->query("UPDATE tbl_course_chapters SET chapter_title='$title', updated_at=NOW() WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   DELETE CHAPTER (and its lessons)
═══════════════════════════════════════════════════════ */
case 'delete_chapter':
    $id = qi($db, $_POST['id']);
    $db->query("DELETE FROM tbl_course_chapter_lessons WHERE chapter_id=$id");
    $db->query("DELETE FROM tbl_course_chapters WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   ADD CHAPTER
═══════════════════════════════════════════════════════ */
case 'add_chapter':
    $course_id    = qi($db, $_POST['course_id']);
    $instructor   = qs($db, $_POST['instructor_id'] ?? '');
    $title        = qs($db, $_POST['title'] ?? 'New Chapter');
    $max = $db->query("SELECT MAX(`order`) AS m FROM tbl_course_chapters WHERE course_id=$course_id")->fetch_assoc()['m'] ?? 0;
    $db->query("INSERT INTO tbl_course_chapters (instructor_id, chapter_title, course_id, `order`, created_at) VALUES ('$instructor','$title',$course_id,".($max+1).",NOW())");
    echo json_encode(['status'=>'success','id'=>$db->insert_id]);
    break;

/* ═══════════════════════════════════════════════════════
   GET LESSON (for edit modal)
═══════════════════════════════════════════════════════ */
case 'get_lesson':
    $id = qi($db, $_POST['id']);
    $row = $db->query("SELECT * FROM tbl_course_chapter_lessons WHERE id=$id LIMIT 1")->fetch_assoc();
    echo json_encode(['status'=>'success','data'=>$row]);
    break;

/* ═══════════════════════════════════════════════════════
   UPDATE LESSON
═══════════════════════════════════════════════════════ */
case 'update_lesson':
    $id     = qi($db, $_POST['id']);
    $title  = qs($db, $_POST['lesson_title'] ?? '');
    $desc   = qs($db, $_POST['description'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $free   = qi($db, $_POST['isFreePreviewLesson'] ?? 0);
    $db->query("UPDATE tbl_course_chapter_lessons SET lesson_title='$title', description='$desc', status='$status', isFreePreviewLesson=$free, updated_at=NOW() WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   DELETE LESSON
═══════════════════════════════════════════════════════ */
case 'delete_lesson':
    $id = qi($db, $_POST['id']);
    $db->query("DELETE FROM tbl_course_chapter_lessons WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — LIST QUESTIONS for a course (filtered by type)
═══════════════════════════════════════════════════════ */
case 'list_questions':
    $course_id = qi($db, $_POST['course_id']);
    $type      = in_array($_POST['type'] ?? '', ['instructor','student']) ? $_POST['type'] : 'student';
    $rows = $db->query("
        SELECT d.id, d.title, d.description, d.created_at, d.type,
               u.first_name, u.last_name, u.email_address,
               (SELECT COUNT(*) FROM tbl_course_discussion_answers a WHERE a.discussion_id = d.id) AS answer_count
        FROM tbl_course_discussions d
        LEFT JOIN tbl_all_users u ON u.usr_code = d.user_id
        WHERE d.course_id = $course_id AND d.type = '$type'
        ORDER BY d.sort_order ASC, d.id ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — GET ANSWERS for a question
═══════════════════════════════════════════════════════ */
case 'list_answers':
    $qid = qi($db, $_POST['question_id']);
    $rows = $db->query("
        SELECT a.id, a.answer, a.is_correct, a.created_at,
               u.first_name, u.last_name
        FROM tbl_course_discussion_answers a
        LEFT JOIN tbl_all_users u ON u.usr_code = a.user_id
        WHERE a.discussion_id = $qid
        ORDER BY a.is_correct DESC, a.created_at ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — REORDER QUESTIONS
═══════════════════════════════════════════════════════ */
case 'reorder_questions':
    $data = json_decode(file_get_contents('php://input'), true);
    $ids  = $data['ids'] ?? [];
    foreach ($ids as $i => $id) {
        $id = qi($db, $id);
        $db->query("UPDATE tbl_course_discussions SET sort_order=" . ($i+1) . " WHERE id=$id");
    }
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — ADD QUESTION
═══════════════════════════════════════════════════════ */
case 'add_question':
    $course_id = qi($db, $_POST['course_id']);
    $title     = qs($db, $_POST['title'] ?? '');
    $desc      = qs($db, $_POST['description'] ?? '');
    $user_id   = qs($db, $_SESSION['usr_code'] ?? '');
    $type      = in_array($_POST['type'] ?? '', ['instructor','student']) ? $_POST['type'] : 'student';
    $max       = $db->query("SELECT MAX(sort_order) AS m FROM tbl_course_discussions WHERE course_id=$course_id AND type='$type'")->fetch_assoc()['m'] ?? 0;
    $db->query("INSERT INTO tbl_course_discussions (course_id, user_id, title, description, type, sort_order) VALUES ($course_id,'$user_id','$title','$desc','$type'," . ($max+1) . ")");
    echo json_encode(['status'=>'success','id'=>$db->insert_id]);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — UPDATE QUESTION
═══════════════════════════════════════════════════════ */
case 'update_question':
    $id   = qi($db, $_POST['id']);
    $title = qs($db, $_POST['title'] ?? '');
    $desc  = qs($db, $_POST['description'] ?? '');
    $db->query("UPDATE tbl_course_discussions SET title='$title', description='$desc' WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — DELETE QUESTION (+ its answers)
═══════════════════════════════════════════════════════ */
case 'delete_question':
    $id = qi($db, $_POST['id']);
    $db->query("DELETE FROM tbl_course_discussion_answers WHERE discussion_id=$id");
    $db->query("DELETE FROM tbl_course_discussions WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — ADD ANSWER
═══════════════════════════════════════════════════════ */
case 'add_answer':
    $qid     = qi($db, $_POST['question_id']);
    $answer  = qs($db, $_POST['answer'] ?? '');
    $user_id = qs($db, $_SESSION['usr_code'] ?? '');
    $db->query("INSERT INTO tbl_course_discussion_answers (discussion_id, user_id, answer) VALUES ($qid,'$user_id','$answer')");
    echo json_encode(['status'=>'success','id'=>$db->insert_id]);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — UPDATE ANSWER
═══════════════════════════════════════════════════════ */
case 'update_answer':
    $id     = qi($db, $_POST['id']);
    $answer = qs($db, $_POST['answer'] ?? '');
    $db->query("UPDATE tbl_course_discussion_answers SET answer='$answer' WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — DELETE ANSWER
═══════════════════════════════════════════════════════ */
case 'delete_answer':
    $id = qi($db, $_POST['id']);
    $db->query("DELETE FROM tbl_course_discussion_answers WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   Q&A — MARK ANSWER CORRECT / UNMARK
═══════════════════════════════════════════════════════ */
case 'mark_correct':
    $aid = qi($db, $_POST['answer_id']);
    $qid = qi($db, $_POST['question_id']);
    $is  = qi($db, $_POST['is_correct']);
    // unmark all answers for this question first
    $db->query("UPDATE tbl_course_discussion_answers SET is_correct=0 WHERE discussion_id=$qid");
    if ($is) $db->query("UPDATE tbl_course_discussion_answers SET is_correct=1 WHERE id=$aid");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   STUDY NOTES — LIST (chapters > lessons > notes)
═══════════════════════════════════════════════════════ */
case 'list_study_notes':
    $course_id = qi($db, $_POST['course_id']);
    $chapters  = $db->query("
        SELECT id, chapter_title, `order`
        FROM tbl_course_chapters WHERE course_id=$course_id
        ORDER BY `order`, id
    ")->fetch_all(MYSQLI_ASSOC);

    foreach ($chapters as &$ch) {
        $chid = (int)$ch['id'];
        $lessons = $db->query("
            SELECT id, lesson_title, sort_order
            FROM tbl_course_chapter_lessons
            WHERE chapter_id=$chid
            ORDER BY sort_order, id
        ")->fetch_all(MYSQLI_ASSOC);

        foreach ($lessons as &$lesson) {
            $lid = (int)$lesson['id'];
            $lesson['notes'] = $db->query("
                SELECT id, question, answer, language, is_important, sort_order, created_at
                FROM study_notes
                WHERE course_id=$course_id AND lesson_id=$lid
                ORDER BY sort_order, id
            ")->fetch_all(MYSQLI_ASSOC);
        }
        $ch['lessons'] = $lessons;
    }
    echo json_encode(['status'=>'success','data'=>$chapters]);
    break;

/* ═══════════════════════════════════════════════════════
   STUDY NOTES — ADD
═══════════════════════════════════════════════════════ */
case 'add_study_note':
    $course_id  = qi($db, $_POST['course_id']);
    $chapter_id = qi($db, $_POST['chapter_id']);
    $lesson_id  = qi($db, $_POST['lesson_id']);
    $question   = qs($db, $_POST['question'] ?? '');
    $answer     = qs($db, $_POST['answer']   ?? '');
    $language   = in_array($_POST['language']??'', ['EN','SW','FR','AR']) ? $_POST['language'] : 'EN';
    $important  = qi($db, $_POST['is_important'] ?? 0);
    $user_id    = qs($db, $_SESSION['usr_code'] ?? '');
    $max        = $db->query("SELECT MAX(sort_order) AS m FROM study_notes WHERE course_id=$course_id AND lesson_id=$lesson_id")->fetch_assoc()['m'] ?? 0;
    $db->query("INSERT INTO study_notes (course_id,chapter_id,lesson_id,question,answer,language,is_important,sort_order,created_by)
                VALUES ($course_id,$chapter_id,$lesson_id,'$question','$answer','$language',$important,".($max+1).",'$user_id')");
    echo json_encode(['status'=>'success','id'=>$db->insert_id]);
    break;

/* ═══════════════════════════════════════════════════════
   STUDY NOTES — UPDATE
═══════════════════════════════════════════════════════ */
case 'update_study_note':
    $id        = qi($db, $_POST['id']);
    $question  = qs($db, $_POST['question'] ?? '');
    $answer    = qs($db, $_POST['answer']   ?? '');
    $language  = in_array($_POST['language']??'', ['EN','SW','FR','AR']) ? $_POST['language'] : 'EN';
    $important = qi($db, $_POST['is_important'] ?? 0);
    $db->query("UPDATE study_notes SET question='$question',answer='$answer',language='$language',is_important=$important WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   STUDY NOTES — DELETE
═══════════════════════════════════════════════════════ */
case 'delete_study_note':
    $id = qi($db, $_POST['id']);
    $db->query("DELETE FROM study_notes WHERE id=$id");
    echo json_encode(['status'=>'success']);
    break;

/* ═══════════════════════════════════════════════════════
   STUDY NOTES — REORDER (within a lesson)
═══════════════════════════════════════════════════════ */
case 'reorder_study_notes':
    $data = json_decode(file_get_contents('php://input'), true);
    $ids  = $data['ids'] ?? [];
    foreach ($ids as $i => $id) {
        $id = qi($db, $id);
        $db->query("UPDATE study_notes SET sort_order=".($i+1)." WHERE id=$id");
    }
    echo json_encode(['status'=>'success']);
    break;

default:
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
