<?php
ini_set('display_errors', 0);
include('../config/db.php');
session_start();
header('Content-Type: application/json');

function _j($d) { echo json_encode($d); exit; }

$course_id = (int)($_GET['course_id'] ?? 0);
if (!$course_id) _j(['status' => 'error', 'message' => 'Invalid course']);

$usr = $_SESSION['usr_code'] ?? '';

/* ── Is the user enrolled? ──────────────────────────────── */
$enrolled = false;
if ($usr) {
    $s = $db->prepare("
        SELECT COUNT(*) AS cnt FROM tbl_orders o
        JOIN tbl_order_items oi ON oi.order_id = o.id
        WHERE o.user_id = ? AND o.payment_status = 'paid' AND oi.course_id = ?");
    $s->bind_param('si', $usr, $course_id);
    $s->execute();
    $enrolled = (bool)($s->get_result()->fetch_assoc()['cnt'] ?? 0);
}

/* ── Chapters ────────────────────────────────────────────── */
$chapters = $db->query("
    SELECT id, chapter_title, sort_order
    FROM tbl_course_chapters
    WHERE course_id = {$course_id}
    ORDER BY sort_order ASC, id ASC
")->fetch_all(MYSQLI_ASSOC);

$data = [];
foreach ($chapters as $ch) {
    $lessons = $db->query("
        SELECT id, lesson_title, description, video_duration,
               isFreePreviewLesson, content_type, storage,
               video_id, library_id, file_path, sort_order
        FROM tbl_course_chapter_lessons
        WHERE course_id = {$course_id} AND chapter_id = {$ch['id']}
          AND status = 'active'
        ORDER BY sort_order ASC, id ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $formatted = [];
    foreach ($lessons as $l) {
        $isFree = $l['isFreePreviewLesson'] == 1 || $l['isFreePreviewLesson'] === '1';
        $canPlay = $enrolled || $isFree;

        /* Build embeddable URL only for playable lessons */
        $embedUrl = null;
        if ($canPlay) {
            $embedUrl = buildEmbedUrl($l);
        }

        $formatted[] = [
            'id'          => (int)$l['id'],
            'title'       => $l['lesson_title'],
            'description' => $l['description'],
            'duration'    => $l['video_duration'] ?: '',
            'is_free'     => $isFree,
            'can_play'    => $canPlay,
            'content_type'=> $l['content_type'] ?? 'Video',
            'embed_url'   => $embedUrl,
        ];
    }

    $data[] = [
        'id'      => (int)$ch['id'],
        'title'   => $ch['chapter_title'],
        'lessons' => $formatted,
    ];
}

_j(['status' => 'success', 'enrolled' => $enrolled, 'data' => $data]);

/* ── Build embed URL from lesson row ──────────────────────── */
function buildEmbedUrl(array $l): ?string {
    $storage = $l['storage'] ?? '';
    $ct      = strtolower($l['content_type'] ?? 'video');
    $path    = $l['file_path'] ?? '';
    $vid     = $l['video_id']  ?? '';
    $lib     = $l['library_id']?? '';

    // BunnyCDN stream via video_id + library_id
    if ($vid && $lib) {
        return "https://iframe.mediadelivery.net/embed/{$lib}/{$vid}?autoplay=true&preload=true";
    }

    // BunnyCDN player URL → embed URL
    if ($path && preg_match('#player\.mediadelivery\.net/play/(\d+)/([a-f0-9\-]+)#i', $path, $m)) {
        return "https://iframe.mediadelivery.net/embed/{$m[1]}/{$m[2]}?autoplay=true";
    }

    // YouTube watch
    if ($path && preg_match('/youtube\.com\/watch\?v=([^&]+)/i', $path, $m)) {
        return "https://www.youtube.com/embed/{$m[1]}?autoplay=1";
    }
    if ($path && preg_match('/youtu\.be\/([^?]+)/i', $path, $m)) {
        return "https://www.youtube.com/embed/{$m[1]}?autoplay=1";
    }

    // Already an embed URL
    if ($path && str_contains($path, 'youtube.com/embed')) {
        return $path . (str_contains($path, '?') ? '&' : '?') . 'autoplay=1';
    }
    if ($path && str_contains($path, 'iframe.mediadelivery.net/embed')) {
        return $path . (str_contains($path, '?') ? '&' : '?') . 'autoplay=true';
    }

    return $path ?: null;
}
