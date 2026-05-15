<?php
$view = $_GET['view'] ?? '';

$modules = [
    'qb_question_media'       => ['icon' => 'bi-images',          'title' => 'Question Media',       'desc' => 'Upload and manage images, audio, and video attached to questions.'],
    'qb_bulk_upload'          => ['icon' => 'bi-cloud-upload',    'title' => 'Bulk Upload',           'desc' => 'Import questions in bulk from Excel or CSV templates.'],
    'qb_excel_import'         => ['icon' => 'bi-file-earmark-excel', 'title' => 'Excel Import',      'desc' => 'Map Excel columns to question fields and import in one step.'],
    'qb_csv_import'           => ['icon' => 'bi-filetype-csv',    'title' => 'CSV Import',           'desc' => 'Import questions from a comma-separated values file.'],
    'qb_export_questions'     => ['icon' => 'bi-download',        'title' => 'Export Questions',      'desc' => 'Export filtered question sets to Excel, PDF, or CSV.'],
    'qb_import_logs'          => ['icon' => 'bi-journal-text',    'title' => 'Import Logs',          'desc' => 'View history and errors from past bulk import operations.'],
    'qb_create_exam'          => ['icon' => 'bi-file-earmark-plus','title' => 'Create Exam',          'desc' => 'Manually pick questions and assemble a printable or online exam.'],
    'qb_exam_templates'       => ['icon' => 'bi-layout-text-window','title' => 'Exam Templates',     'desc' => 'Reusable exam structures with section rules and marks allocation.'],
    'qb_random_exam'          => ['icon' => 'bi-shuffle',          'title' => 'Random Exam',          'desc' => 'Auto-generate exams using topic and difficulty distribution rules.'],
    'qb_cbt_exams'            => ['icon' => 'bi-display',          'title' => 'CBT Exams',            'desc' => 'Computer-Based Testing — online exam delivery and submission.'],
    'qb_print_exams'          => ['icon' => 'bi-printer',          'title' => 'Print Exams',          'desc' => 'Generate print-ready PDF exam papers with answer keys.'],
    'qb_competencies'         => ['icon' => 'bi-award',            'title' => 'Competencies',         'desc' => 'Define and manage competency frameworks linked to questions.'],
    'qb_learning_outcomes'    => ['icon' => 'bi-bullseye',         'title' => 'Learning Outcomes',   'desc' => 'Map questions to specific learning objectives and outcomes.'],
    'qb_curriculum_references'=> ['icon' => 'bi-bookmarks',        'title' => 'Curriculum References','desc' => 'Link questions to national curriculum standards and codes.'],
    'qb_question_usage'       => ['icon' => 'bi-graph-up',         'title' => 'Question Usage',       'desc' => 'See how often each question appears in exams and its performance.'],
    'qb_difficulty_analytics' => ['icon' => 'bi-bar-chart',        'title' => 'Difficulty Analytics', 'desc' => 'Compare set difficulty vs actual student performance statistics.'],
    'qb_student_performance'  => ['icon' => 'bi-people',           'title' => 'Student Performance',  'desc' => 'Analyse per-student and cohort performance on specific questions.'],
    'qb_most_failed'          => ['icon' => 'bi-exclamation-triangle','title' => 'Most Failed',       'desc' => 'Identify questions with the highest failure rate for review.'],
    'qb_bloom_distribution'   => ['icon' => 'bi-pie-chart',        'title' => "Bloom's Distribution", 'desc' => "Visualise the spread of questions across Bloom's Taxonomy levels."],
    'qb_ai_generate'          => ['icon' => 'bi-robot',            'title' => 'AI Generate',          'desc' => 'Use AI to auto-generate draft questions from topic descriptions.'],
    'qb_ai_explanations'      => ['icon' => 'bi-chat-dots',        'title' => 'AI Explanations',      'desc' => 'Auto-generate solution explanations for existing questions.'],
    'qb_ai_difficulty'        => ['icon' => 'bi-speedometer',      'title' => 'AI Difficulty Rating', 'desc' => 'Let AI estimate and suggest difficulty levels for questions.'],
    'qb_duplicate_detector'   => ['icon' => 'bi-copy',             'title' => 'Duplicate Detector',   'desc' => 'Detect and merge near-duplicate or identical questions.'],
    'qb_id_rules'             => ['icon' => 'bi-tag',              'title' => 'Question ID Rules',    'desc' => 'Configure the format and segments used to auto-generate q_uid codes.'],
    'qb_approval_workflow'    => ['icon' => 'bi-check2-all',       'title' => 'Approval Workflow',    'desc' => 'Set up multi-step review and approval chains for new questions.'],
    'qb_media_storage'        => ['icon' => 'bi-hdd',              'title' => 'Media Storage',        'desc' => 'Manage storage quotas, CDN settings, and orphaned media files.'],
    'qb_general_settings'     => ['icon' => 'bi-gear',             'title' => 'General Settings',     'desc' => 'Configure question bank defaults, permissions, and preferences.'],
];

$m = $modules[$view] ?? ['icon' => 'bi-hourglass-split', 'title' => 'Module', 'desc' => 'This module is being built.'];
?>

<div class="container-fluid px-3 py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6 text-center">

      <div class="mb-4">
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
              style="width:80px;height:80px">
          <i class="bi <?= $m['icon'] ?> text-primary fs-2"></i>
        </span>
      </div>

      <h4 class="fw-semibold mb-2"><?= htmlspecialchars($m['title']) ?></h4>
      <p class="text-muted mb-4"><?= htmlspecialchars($m['desc']) ?></p>

      <div class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill bg-warning bg-opacity-10 text-warning fw-medium mb-4">
        <i class="bi bi-tools"></i> Coming Soon
      </div>

      <p class="text-muted small mb-4">
        This module is on the roadmap and will be available in an upcoming update.<br>
        The core Question Bank — taxonomy, authoring, and question management — is live now.
      </p>

      <div class="d-flex justify-content-center gap-2 flex-wrap">
        <a href="?view=qb_all_questions" class="btn btn-primary btn-sm">
          <i class="bi bi-patch-question me-1"></i> All Questions
        </a>
        <a href="?view=qb_add_question" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-plus-lg me-1"></i> Add Question
        </a>
        <a href="?view=qb_subjects" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-book me-1"></i> Taxonomy
        </a>
      </div>

    </div>
  </div>
</div>
