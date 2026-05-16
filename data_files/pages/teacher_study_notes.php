<?php
$me = $_SESSION['usr_code'];

// Fetch all instructor courses with chapters, lessons, and notes counts in one query
$sql = "
    SELECT
        c.id          AS cid,
        c.title       AS ctitle,
        c.thumbnail,
        c.status      AS cstatus,
        ch.id         AS chid,
        ch.chapter_title,
        ch.`order`    AS chorder,
        l.id          AS lid,
        l.lesson_title,
        l.content_type,
        l.file_type,
        (SELECT COUNT(*) FROM study_notes sn WHERE sn.lesson_id = l.id) AS notes_count
    FROM tbl_courses c
    LEFT JOIN tbl_course_chapters ch ON ch.course_id = c.id
    LEFT JOIN tbl_course_chapter_lessons l ON l.chapter_id = ch.id AND l.status = 'active'
    WHERE c.instructor_id = '$me' AND c.deleted_at IS NULL
    ORDER BY c.id DESC, ch.`order` ASC, l.id ASC
";
$rows = $db->query($sql)->fetch_all(MYSQLI_ASSOC);

// Group into nested structure
$courses = [];
foreach ($rows as $row) {
    $cid  = $row['cid'];
    $chid = $row['chid'];
    $lid  = $row['lid'];

    if (!isset($courses[$cid])) {
        $courses[$cid] = [
            'id'        => $cid,
            'title'     => $row['ctitle'],
            'thumbnail' => $row['thumbnail'],
            'status'    => $row['cstatus'],
            'chapters'  => [],
        ];
    }
    if ($chid && !isset($courses[$cid]['chapters'][$chid])) {
        $courses[$cid]['chapters'][$chid] = [
            'id'      => $chid,
            'title'   => $row['chapter_title'],
            'lessons' => [],
        ];
    }
    if ($chid && $lid) {
        $courses[$cid]['chapters'][$chid]['lessons'][] = [
            'id'           => $lid,
            'title'        => $row['lesson_title'],
            'content_type' => $row['content_type'] ?? 'Video',
            'file_type'    => $row['file_type']    ?? 'video',
            'notes_count'  => (int) $row['notes_count'],
        ];
    }
}

// Content-type icon map
$typeIcon = [
    'video'        => 'bi-play-circle',
    'audio'        => 'bi-music-note-beamed',
    'pdf'          => 'bi-file-earmark-pdf',
    'presentation' => 'bi-easel',
    'image'        => 'bi-image',
    'live'         => 'bi-broadcast',
];
?>

<style>
.tsn-lesson-row { transition: background .12s ease; }
.tsn-lesson-row:hover { background: rgba(var(--bs-primary-rgb),.04); }
.tsn-notes-badge { min-width: 2rem; text-align: center; }
.tsn-course-status { font-size: .7rem; }
.tsn-empty-art { opacity: .35; }
</style>

<div class="container-fluid px-3 pt-3 pb-5">

    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h5 class="fw-semibold mb-1">
                <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Manage Study Notes
            </h5>
            <p class="text-muted small mb-0">Add and manage Q&amp;A study notes for each lesson across all your courses.</p>
        </div>
        <a href="../data_files/?view=my_courses_online_contents_list_view" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-collection me-1"></i>My Courses
        </a>
    </div>

    <?php if (empty($courses)): ?>
    <!-- Empty state -->
    <div class="text-center py-5 mt-4">
        <i class="bi bi-collection-play display-4 text-muted tsn-empty-art d-block mb-3"></i>
        <h6 class="text-muted fw-normal">You have no courses yet.</h6>
        <p class="text-muted small">Create a course first, then add study notes to each lesson.</p>
    </div>

    <?php else: ?>

    <!-- Stats row -->
    <?php
        $totalCourses  = count($courses);
        $totalLessons  = 0;
        $lessonsWithNotes = 0;
        $totalNotes    = 0;
        foreach ($courses as $c) {
            foreach ($c['chapters'] as $ch) {
                foreach ($ch['lessons'] as $l) {
                    $totalLessons++;
                    $totalNotes += $l['notes_count'];
                    if ($l['notes_count'] > 0) $lessonsWithNotes++;
                }
            }
        }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-primary mb-0"><?= $totalCourses ?></div>
                <div class="small text-muted">Courses</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-info mb-0"><?= $totalLessons ?></div>
                <div class="small text-muted">Total Lessons</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-success mb-0"><?= $lessonsWithNotes ?></div>
                <div class="small text-muted">Lessons with Notes</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-warning mb-0"><?= $totalNotes ?></div>
                <div class="small text-muted">Total Q&amp;A Notes</div>
            </div>
        </div>
    </div>

    <!-- Courses accordion -->
    <div class="accordion" id="courseAccordion">
    <?php foreach ($courses as $ci => $course):
        $courseNotes   = array_sum(array_map(fn($ch) => array_sum(array_column($ch['lessons'], 'notes_count')), $course['chapters']));
        $courseLessons = array_sum(array_map(fn($ch) => count($ch['lessons']), $course['chapters']));
        $collapseId = 'course_' . $course['id'];
        $isActive   = ($ci === array_key_first($courses)); // expand first course by default
    ?>
    <div class="card border-0 shadow-sm mb-3 overflow-hidden">

        <!-- Course header -->
        <div class="card-header bg-white p-0 border-bottom-0">
            <button class="btn w-100 text-start d-flex align-items-center gap-3 p-3 <?= $isActive ? '' : 'collapsed' ?>"
                    data-bs-toggle="collapse"
                    data-bs-target="#<?= $collapseId ?>"
                    aria-expanded="<?= $isActive ? 'true' : 'false' ?>">

                <!-- Thumbnail -->
                <?php $thumb = $course['thumbnail'] ? 'uploads/' . basename($course['thumbnail']) : 'uploads/course_default.png'; ?>
                <div class="flex-shrink-0 rounded" style="width:48px;height:48px;background:url('<?= htmlspecialchars($thumb) ?>') center/cover no-repeat;background-color:#e9ecef;"></div>

                <!-- Title & meta -->
                <div class="flex-grow-1 text-start min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="fw-semibold text-truncate"><?= htmlspecialchars($course['title']) ?></span>
                        <?php
                        $stBadge = match($course['status']) {
                            'active'   => ['bg-success','Published'],
                            'is_draft' => ['bg-secondary','Draft'],
                            default    => ['bg-secondary','Inactive'],
                        };
                        ?>
                        <span class="badge <?= $stBadge[0] ?> tsn-course-status"><?= $stBadge[1] ?></span>
                    </div>
                    <div class="d-flex gap-3 small text-muted">
                        <span><i class="bi bi-play-circle me-1"></i><?= $courseLessons ?> lesson<?= $courseLessons != 1 ? 's' : '' ?></span>
                        <span>
                            <i class="bi bi-journal-bookmark me-1 <?= $courseNotes > 0 ? 'text-primary' : '' ?>"></i>
                            <?= $courseNotes ?> note<?= $courseNotes != 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>

                <!-- Manage course link -->
                <a href="../data_files/?view=course_contents_management&course_id=<?= $course['id'] ?>"
                   class="btn btn-sm btn-outline-secondary flex-shrink-0"
                   onclick="event.stopPropagation()"
                   title="Manage course content">
                    <i class="bi bi-pencil-square me-1 d-none d-sm-inline"></i>Content
                </a>

                <i class="bi bi-chevron-down text-muted flex-shrink-0" style="transition:transform .2s;font-size:.8rem"></i>
            </button>
        </div>

        <!-- Chapters & lessons -->
        <div id="<?= $collapseId ?>" class="collapse <?= $isActive ? 'show' : '' ?>" data-bs-parent="#courseAccordion">
            <?php if (empty($course['chapters'])): ?>
            <div class="card-body text-center py-4 text-muted small">
                <i class="bi bi-collection d-block fs-3 mb-2 opacity-50"></i>No chapters yet.
            </div>
            <?php else: ?>

            <?php foreach ($course['chapters'] as $chapter): ?>
            <div class="border-top">
                <!-- Chapter title -->
                <div class="px-4 py-2 bg-light bg-opacity-75 d-flex align-items-center gap-2">
                    <i class="bi bi-collection text-muted small"></i>
                    <span class="fw-medium small text-muted"><?= htmlspecialchars($chapter['title']) ?></span>
                </div>

                <?php if (empty($chapter['lessons'])): ?>
                <div class="px-4 py-3 small text-muted">No lessons in this chapter.</div>
                <?php else: ?>

                <?php foreach ($chapter['lessons'] as $lesson):
                    $icon = $typeIcon[$lesson['file_type']] ?? 'bi-file-play';
                    $hasNotes = $lesson['notes_count'] > 0;
                ?>
                <div class="d-flex align-items-center gap-3 px-4 py-3 tsn-lesson-row border-top border-light">

                    <!-- Lesson type icon -->
                    <div class="flex-shrink-0 text-center" style="width:32px">
                        <i class="bi <?= $icon ?> text-muted fs-5"></i>
                    </div>

                    <!-- Lesson title -->
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 fw-medium small text-truncate"><?= htmlspecialchars($lesson['title']) ?></p>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.68rem">
                            <?= htmlspecialchars(ucfirst($lesson['content_type'] ?? 'Video')) ?>
                        </span>
                    </div>

                    <!-- Notes badge -->
                    <div class="flex-shrink-0">
                        <?php if ($hasNotes): ?>
                        <span class="badge bg-primary tsn-notes-badge" title="<?= $lesson['notes_count'] ?> notes">
                            <i class="bi bi-journal-bookmark-fill me-1"></i><?= $lesson['notes_count'] ?>
                        </span>
                        <?php else: ?>
                        <span class="badge bg-light text-muted tsn-notes-badge border">
                            0
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Action button -->
                    <div class="flex-shrink-0">
                        <a href="../data_files/?view=study_notes_manager&lesson_id=<?= $lesson['id'] ?>&course_id=<?= $course['id'] ?>&chapter_id=<?= $chapter['id'] ?>"
                           class="btn btn-sm <?= $hasNotes ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <?php if ($hasNotes): ?>
                                <i class="bi bi-pencil me-1"></i>Edit Notes
                            <?php else: ?>
                                <i class="bi bi-plus-lg me-1"></i>Add Notes
                            <?php endif; ?>
                        </a>
                    </div>

                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>
