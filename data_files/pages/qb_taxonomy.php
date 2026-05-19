<?php
$view = $_GET['view'] ?? 'qb_subjects';

/* ── Shared status pills config ─────────────────────────────── */
$Q_PILLS = [
    ['key'=>'published_count','label'=>'Published','bg'=>'#dcfce7','color'=>'#15803d','icon'=>'bi-check-circle-fill'],
    ['key'=>'draft_count',    'label'=>'Draft',    'bg'=>'#fef9c3','color'=>'#92400e','icon'=>'bi-pencil-square'],
    ['key'=>'review_count',   'label'=>'Review',   'bg'=>'#e0f2fe','color'=>'#0369a1','icon'=>'bi-eye-fill'],
];

/* ── Full entity config ─────────────────────────────────────── */
$cfg = [

    /* ── SUBJECTS ─────────────────────────────────────────── */
    'qb_subjects' => [
        'title'  => 'Subjects',
        'icon'   => 'bi-book-fill',
        'entity' => 'subjects',
        'form'   => [
            ['name'=>'subject_code','label'=>'Subject Code','type'=>'text',  'req'=>false,'help'=>'e.g. MATH, BIO, PHY'],
            ['name'=>'subject_name','label'=>'Subject Name','type'=>'text',  'req'=>true],
        ],
        'ui' => [
            'hero_bg'  => 'linear-gradient(135deg,#0b1120 0%,#0f1e3d 35%,#1a1040 65%,#0d1628 100%)',
            'orb1'     => 'rgba(26,79,196,.45)', 'orb2' => 'rgba(109,40,217,.35)',
            'hero_sub' => 'Organise your question bank by academic subjects. Each subject holds chapters, subtopics and questions.',
            'name_key' => 'subject_name',
            'tag_key'  => 'subject_code', 'tag_pre' => '',
            'meta'     => [],
            'stats'    => [
                ['key'=>'question_count', 'label'=>'Questions', 'icon'=>'bi-question-circle'],
                ['key'=>'chapter_count',  'label'=>'Chapters',  'icon'=>'bi-bookmark'],
                ['key'=>'subtopic_count', 'label'=>'Subtopics', 'icon'=>'bi-bookmarks'],
            ],
            'pills'    => $Q_PILLS,
            'progress' => 'question_count',
            'sort'     => [
                ['val'=>'questions','label'=>'By Questions','key'=>'question_count','dir'=>'d'],
                ['val'=>'name',     'label'=>'By Name',     'key'=>'subject_name',  'dir'=>'a'],
                ['val'=>'chapters', 'label'=>'By Chapters', 'key'=>'chapter_count', 'dir'=>'d'],
                ['val'=>'newest',   'label'=>'Newest First','key'=>'created_at',    'dir'=>'d'],
            ],
            'kpis' => [
                ['label'=>'Subjects',   'agg'=>'count', 'key'=>''],
                ['label'=>'Questions',  'agg'=>'sum',   'key'=>'question_count'],
                ['label'=>'Chapters',   'agg'=>'sum',   'key'=>'chapter_count'],
                ['label'=>'Published',  'agg'=>'sum',   'key'=>'published_count'],
            ],
        ],
    ],

    /* ── LEVELS ───────────────────────────────────────────── */
    'qb_levels' => [
        'title'  => 'Levels & Classes',
        'icon'   => 'bi-layers-fill',
        'entity' => 'levels',
        'form'   => [
            ['name'=>'level_name', 'label'=>'Level Name', 'type'=>'text',  'req'=>true, 'help'=>'e.g. Standard 7, Form 2, Grade 4'],
            ['name'=>'sort_order', 'label'=>'Sort Order', 'type'=>'number','req'=>false,'help'=>'Lower number = shown first'],
        ],
        'ui' => [
            'hero_bg'  => 'linear-gradient(135deg,#052e16 0%,#064e3b 40%,#065f46 100%)',
            'orb1'     => 'rgba(5,150,105,.5)', 'orb2' => 'rgba(13,148,136,.4)',
            'hero_sub' => 'Define academic levels and class groups. Levels link chapters and questions to specific academic stages.',
            'name_key' => 'level_name',
            'tag_key'  => 'sort_order', 'tag_pre' => '#',
            'meta'     => [],
            'stats'    => [
                ['key'=>'chapter_count',  'label'=>'Chapters',  'icon'=>'bi-bookmark'],
                ['key'=>'question_count', 'label'=>'Questions', 'icon'=>'bi-question-circle'],
                ['key'=>'published_count','label'=>'Published', 'icon'=>'bi-check-circle'],
            ],
            'pills'    => $Q_PILLS,
            'progress' => 'question_count',
            'sort'     => [
                ['val'=>'order',    'label'=>'By Order',    'key'=>'sort_order',    'dir'=>'a'],
                ['val'=>'questions','label'=>'By Questions','key'=>'question_count','dir'=>'d'],
                ['val'=>'chapters', 'label'=>'By Chapters', 'key'=>'chapter_count', 'dir'=>'d'],
                ['val'=>'name',     'label'=>'By Name',     'key'=>'level_name',    'dir'=>'a'],
            ],
            'kpis' => [
                ['label'=>'Levels',    'agg'=>'count','key'=>''],
                ['label'=>'Chapters',  'agg'=>'sum',  'key'=>'chapter_count'],
                ['label'=>'Questions', 'agg'=>'sum',  'key'=>'question_count'],
                ['label'=>'Published', 'agg'=>'sum',  'key'=>'published_count'],
            ],
        ],
    ],

    /* ── CHAPTERS ─────────────────────────────────────────── */
    'qb_chapters' => [
        'title'  => 'Chapters',
        'icon'   => 'bi-bookmark-fill',
        'entity' => 'chapters',
        'form'   => [
            ['name'=>'subject_id',    'label'=>'Subject',        'type'=>'select_subjects','req'=>true],
            ['name'=>'level_id',      'label'=>'Level',          'type'=>'select_levels',  'req'=>true],
            ['name'=>'chapter_number','label'=>'Chapter Number', 'type'=>'text',           'req'=>false,'help'=>'e.g. 1, 2, 09'],
            ['name'=>'chapter_name',  'label'=>'Chapter Name',   'type'=>'text',           'req'=>true],
        ],
        'ui' => [
            'hero_bg'  => 'linear-gradient(135deg,#1c0a00 0%,#431407 40%,#7c2d12 100%)',
            'orb1'     => 'rgba(217,119,6,.5)', 'orb2' => 'rgba(234,88,12,.4)',
            'hero_sub' => 'Chapters group related questions by topic within a subject and level. Each chapter can hold multiple subtopics.',
            'name_key' => 'chapter_name',
            'tag_key'  => 'chapter_number', 'tag_pre' => 'Ch.',
            'meta'     => [
                ['key'=>'subject_name','label'=>'Subject','type'=>'pill'],
                ['key'=>'level_name',  'label'=>'Level',  'type'=>'pill'],
            ],
            'stats'    => [
                ['key'=>'subtopic_count', 'label'=>'Subtopics', 'icon'=>'bi-bookmarks'],
                ['key'=>'question_count', 'label'=>'Questions', 'icon'=>'bi-question-circle'],
                ['key'=>'published_count','label'=>'Published', 'icon'=>'bi-check-circle'],
            ],
            'pills'    => $Q_PILLS,
            'progress' => 'question_count',
            'sort'     => [
                ['val'=>'questions','label'=>'By Questions','key'=>'question_count', 'dir'=>'d'],
                ['val'=>'subject',  'label'=>'By Subject',  'key'=>'subject_name',  'dir'=>'a'],
                ['val'=>'name',     'label'=>'By Name',     'key'=>'chapter_name',  'dir'=>'a'],
                ['val'=>'number',   'label'=>'By Number',   'key'=>'chapter_number','dir'=>'a'],
            ],
            'kpis' => [
                ['label'=>'Chapters',  'agg'=>'count','key'=>''],
                ['label'=>'Subtopics', 'agg'=>'sum',  'key'=>'subtopic_count'],
                ['label'=>'Questions', 'agg'=>'sum',  'key'=>'question_count'],
                ['label'=>'Published', 'agg'=>'sum',  'key'=>'published_count'],
            ],
        ],
    ],

    /* ── SUBTOPICS ────────────────────────────────────────── */
    'qb_subtopics' => [
        'title'  => 'Subtopics',
        'icon'   => 'bi-bookmarks-fill',
        'entity' => 'subtopics',
        'form'   => [
            ['name'=>'chapter_id',   'label'=>'Chapter',       'type'=>'select_chapters','req'=>true],
            ['name'=>'subtopic_name','label'=>'Subtopic Name', 'type'=>'text',           'req'=>true],
        ],
        'ui' => [
            'hero_bg'  => 'linear-gradient(135deg,#1e0a2e 0%,#2e1065 40%,#4c1d95 100%)',
            'orb1'     => 'rgba(124,58,237,.5)', 'orb2' => 'rgba(168,85,247,.4)',
            'hero_sub' => 'Subtopics are the most granular level of content organisation. Questions are tagged to subtopics for precise filtering.',
            'name_key' => 'subtopic_name',
            'tag_key'  => null, 'tag_pre' => '',
            'meta'     => [
                ['key'=>'chapter_name','label'=>'Chapter','type'=>'pill'],
            ],
            'stats'    => [
                ['key'=>'question_count', 'label'=>'Questions', 'icon'=>'bi-question-circle'],
                ['key'=>'published_count','label'=>'Published', 'icon'=>'bi-check-circle'],
                ['key'=>'draft_count',    'label'=>'Draft',     'icon'=>'bi-pencil-square'],
            ],
            'pills'    => $Q_PILLS,
            'progress' => 'question_count',
            'sort'     => [
                ['val'=>'questions','label'=>'By Questions','key'=>'question_count','dir'=>'d'],
                ['val'=>'name',     'label'=>'By Name',     'key'=>'subtopic_name','dir'=>'a'],
                ['val'=>'chapter',  'label'=>'By Chapter',  'key'=>'chapter_name', 'dir'=>'a'],
            ],
            'kpis' => [
                ['label'=>'Subtopics', 'agg'=>'count','key'=>''],
                ['label'=>'Questions', 'agg'=>'sum',  'key'=>'question_count'],
                ['label'=>'Published', 'agg'=>'sum',  'key'=>'published_count'],
                ['label'=>'Draft',     'agg'=>'sum',  'key'=>'draft_count'],
            ],
        ],
    ],

    /* ── BLOOM LEVELS ─────────────────────────────────────── */
    'qb_bloom_levels' => [
        'title'  => "Bloom's Levels",
        'icon'   => 'bi-bar-chart-steps',
        'entity' => 'bloom_levels',
        'form'   => [
            ['name'=>'bloom_name',  'label'=>'Bloom Level', 'type'=>'text',    'req'=>true, 'help'=>'e.g. Remember, Understand, Apply'],
            ['name'=>'description', 'label'=>'Description', 'type'=>'textarea','req'=>false],
        ],
        'ui' => [
            'hero_bg'  => 'linear-gradient(135deg,#0c1a2e 0%,#0e2a4a 40%,#0c4a6e 100%)',
            'orb1'     => 'rgba(8,145,178,.5)', 'orb2' => 'rgba(14,165,233,.4)',
            'hero_sub' => "Bloom's Taxonomy classifies cognitive complexity from recall to creation. Assign levels to ensure cognitive diversity across your question bank.",
            'name_key' => 'bloom_name',
            'tag_key'  => null, 'tag_pre' => '',
            'meta'     => [
                ['key'=>'description','label'=>'','type'=>'desc'],
            ],
            'stats'    => [
                ['key'=>'question_count', 'label'=>'Questions', 'icon'=>'bi-question-circle'],
                ['key'=>'published_count','label'=>'Published', 'icon'=>'bi-check-circle'],
                ['key'=>'draft_count',    'label'=>'Draft',     'icon'=>'bi-pencil-square'],
            ],
            'pills'    => $Q_PILLS,
            'progress' => 'question_count',
            'sort'     => [
                ['val'=>'questions','label'=>'By Questions','key'=>'question_count','dir'=>'d'],
                ['val'=>'name',     'label'=>'By Name',     'key'=>'bloom_name',   'dir'=>'a'],
            ],
            'kpis' => [
                ['label'=>'Bloom Levels','agg'=>'count','key'=>''],
                ['label'=>'Questions',   'agg'=>'sum',  'key'=>'question_count'],
                ['label'=>'Published',   'agg'=>'sum',  'key'=>'published_count'],
                ['label'=>'Draft',       'agg'=>'sum',  'key'=>'draft_count'],
            ],
        ],
    ],

    /* ── DIFFICULTY LEVELS ────────────────────────────────── */
    'qb_difficulty_levels' => [
        'title'  => 'Difficulty Levels',
        'icon'   => 'bi-speedometer2',
        'entity' => 'difficulty_levels',
        'form'   => [
            ['name'=>'difficulty_name','label'=>'Difficulty Name','type'=>'text','req'=>true,'help'=>'e.g. Easy, Medium, Hard'],
        ],
        'ui' => [
            'hero_bg'  => 'linear-gradient(135deg,#2d0a14 0%,#4c0519 40%,#881337 100%)',
            'orb1'     => 'rgba(190,24,93,.5)', 'orb2' => 'rgba(236,72,153,.4)',
            'hero_sub' => 'Difficulty levels calibrate question sets for various scenarios — from beginner recall to advanced synthesis.',
            'name_key' => 'difficulty_name',
            'tag_key'  => null, 'tag_pre' => '',
            'meta'     => [],
            'stats'    => [
                ['key'=>'question_count', 'label'=>'Questions', 'icon'=>'bi-question-circle'],
                ['key'=>'published_count','label'=>'Published', 'icon'=>'bi-check-circle'],
                ['key'=>'draft_count',    'label'=>'Draft',     'icon'=>'bi-pencil-square'],
            ],
            'pills'    => $Q_PILLS,
            'progress' => 'question_count',
            'sort'     => [
                ['val'=>'questions','label'=>'By Questions', 'key'=>'question_count',  'dir'=>'d'],
                ['val'=>'name',     'label'=>'By Name',      'key'=>'difficulty_name', 'dir'=>'a'],
            ],
            'kpis' => [
                ['label'=>'Difficulties','agg'=>'count','key'=>''],
                ['label'=>'Questions',   'agg'=>'sum',  'key'=>'question_count'],
                ['label'=>'Published',   'agg'=>'sum',  'key'=>'published_count'],
                ['label'=>'Draft',       'agg'=>'sum',  'key'=>'draft_count'],
            ],
        ],
    ],

    /* ── SECTIONS ─────────────────────────────────────────── */
    'qb_sections' => [
        'title'  => 'Sections',
        'icon'   => 'bi-grid-fill',
        'entity' => 'sections',
        'form'   => [
            ['name'=>'section_name','label'=>'Section Name','type'=>'text','req'=>true,'help'=>'e.g. Section A, Section B'],
        ],
        'ui' => [
            'hero_bg'  => 'linear-gradient(135deg,#052e16 0%,#14532d 40%,#166534 100%)',
            'orb1'     => 'rgba(5,150,105,.5)', 'orb2' => 'rgba(22,163,74,.4)',
            'hero_sub' => 'Sections group questions into exam paper divisions such as Section A (MCQ) and Section B (Essay).',
            'name_key' => 'section_name',
            'tag_key'  => null, 'tag_pre' => '',
            'meta'     => [],
            'stats'    => [
                ['key'=>'question_count', 'label'=>'Questions', 'icon'=>'bi-question-circle'],
                ['key'=>'published_count','label'=>'Published', 'icon'=>'bi-check-circle'],
                ['key'=>'draft_count',    'label'=>'Draft',     'icon'=>'bi-pencil-square'],
            ],
            'pills'    => $Q_PILLS,
            'progress' => 'question_count',
            'sort'     => [
                ['val'=>'questions','label'=>'By Questions','key'=>'question_count','dir'=>'d'],
                ['val'=>'name',     'label'=>'By Name',     'key'=>'section_name', 'dir'=>'a'],
            ],
            'kpis' => [
                ['label'=>'Sections',  'agg'=>'count','key'=>''],
                ['label'=>'Questions', 'agg'=>'sum',  'key'=>'question_count'],
                ['label'=>'Published', 'agg'=>'sum',  'key'=>'published_count'],
                ['label'=>'Draft',     'agg'=>'sum',  'key'=>'draft_count'],
            ],
        ],
    ],
];

$e      = $cfg[$view] ?? $cfg['qb_subjects'];
$entity = $e['entity'];
$title  = $e['title'];
$icon   = $e['icon'];
$fields = $e['form'];
$ui     = $e['ui'];

/* ── Quick nav: all entities except current ─────────────────── */
$all_nav = [
    'qb_subjects'          => ['icon'=>'bi-book-fill',        'label'=>'Subjects'],
    'qb_levels'            => ['icon'=>'bi-layers-fill',      'label'=>'Levels'],
    'qb_chapters'          => ['icon'=>'bi-bookmark-fill',    'label'=>'Chapters'],
    'qb_subtopics'         => ['icon'=>'bi-bookmarks-fill',   'label'=>'Subtopics'],
    'qb_bloom_levels'      => ['icon'=>'bi-bar-chart-steps',  'label'=>"Bloom's"],
    'qb_difficulty_levels' => ['icon'=>'bi-speedometer2',     'label'=>'Difficulty'],
    'qb_sections'          => ['icon'=>'bi-grid-fill',        'label'=>'Sections'],
];
?>

<style>
/* ═══════════════════════════════════════════════════════════
   QB TAXONOMY — UNIVERSAL RICH UI
═══════════════════════════════════════════════════════════ */
.qbt-wrap { font-family:'Open Sans',sans-serif; }

/* ── Hero ───────────────────────────────────────────────── */
.qbt-hero {
  position:relative; overflow:hidden; isolation:isolate;
  border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.4rem;
}
.qbt-hero-grid {
  position:absolute; inset:0; z-index:0;
  background-image:linear-gradient(rgba(255,255,255,.028) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(255,255,255,.028) 1px,transparent 1px);
  background-size:44px 44px;
}
.qbt-hero-inner { position:relative; z-index:1; }
.qbt-hero-badge {
  display:inline-flex; align-items:center; gap:.4rem;
  background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15);
  border-radius:100px; padding:.28rem .85rem; font-size:.7rem; font-weight:700;
  color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase;
  margin-bottom:.75rem; backdrop-filter:blur(6px);
}
.qbt-hero-title {
  font-size:1.7rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif;
  letter-spacing:-.04em; line-height:1.15; margin-bottom:.3rem;
}
.qbt-hero-title em { font-style:normal; background:linear-gradient(90deg,#60a5fa,#c084fc); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbt-hero-sub { font-size:.81rem; color:rgba(255,255,255,.45); margin-bottom:1.4rem; max-width:520px; line-height:1.6; }

/* ── Hero KPIs ── */
.qbt-kpis { display:flex; gap:.7rem; flex-wrap:wrap; }
.qbt-kpi {
  background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
  border-radius:14px; padding:.6rem 1rem; backdrop-filter:blur(8px); min-width:90px;
  transition:background .2s;
}
.qbt-kpi:hover { background:rgba(255,255,255,.13); }
.qbt-kpi-val { font-size:1.2rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; line-height:1; }
.qbt-kpi-lbl { font-size:.63rem; color:rgba(255,255,255,.45); margin-top:.15rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
.qbt-kpi-trend { font-size:.64rem; font-weight:700; color:#4ade80; margin-top:.15rem; }

/* ── Side panel (quick nav + add button) ── */
.qbt-side-panel {
  background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.11);
  border-radius:16px; padding:.9rem 1.1rem; backdrop-filter:blur(10px); min-width:180px;
}
.qbt-side-title { font-size:.63rem; color:rgba(255,255,255,.38); font-weight:700; text-transform:uppercase; letter-spacing:.07em; margin-bottom:.65rem; }
.qbt-nav-link {
  display:flex; align-items:center; gap:.55rem; padding:.32rem .55rem;
  border-radius:9px; text-decoration:none; font-size:.75rem; font-weight:600;
  color:rgba(255,255,255,.6); transition:all .15s; white-space:nowrap;
}
.qbt-nav-link:hover { background:rgba(255,255,255,.1); color:#fff; }
.qbt-nav-link.active { background:rgba(255,255,255,.15); color:#fff; }
.qbt-nav-link i { font-size:.78rem; width:16px; opacity:.75; flex-shrink:0; }

/* ── Add button ── */
.qbt-add-btn {
  display:inline-flex; align-items:center; gap:.45rem;
  background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff;
  border:none; border-radius:12px; padding:.6rem 1.3rem;
  font-size:.82rem; font-weight:700; cursor:pointer; font-family:inherit;
  box-shadow:0 4px 18px rgba(26,79,196,.4);
  transition:filter .18s, transform .12s; white-space:nowrap;
}
.qbt-add-btn:hover { filter:brightness(1.1); transform:translateY(-1px); color:#fff; }
.qbt-add-btn:active { transform:scale(.96); }

/* ── Toolbar ─────────────────────────────────────────────── */
.qbt-toolbar {
  display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
  background:#fff; border-radius:16px; padding:.85rem 1.1rem;
  box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.05);
  border:1px solid #f0f4f8; margin-bottom:1.2rem;
}
.qbt-search-wrap { position:relative; flex:1; min-width:200px; }
.qbt-search-wrap i { position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.84rem; pointer-events:none; }
.qbt-search {
  width:100%; padding:.5rem .85rem .5rem 2.2rem; border-radius:10px;
  border:1.5px solid #e2e8f0; font-size:.82rem; font-family:inherit;
  outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s, box-shadow .18s;
}
.qbt-search:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }
.qbt-sort {
  padding:.5rem .85rem; border-radius:10px; border:1.5px solid #e2e8f0;
  font-size:.79rem; font-family:inherit; outline:none; background:#f8fafc;
  color:#475569; cursor:pointer; transition:border-color .18s;
}
.qbt-sort:focus { border-color:#1a4fc4; }
.qbt-view-btns { display:flex; gap:.3rem; }
.qbt-view-btn {
  width:34px; height:34px; border-radius:9px; border:1.5px solid #e2e8f0;
  background:#f8fafc; color:#94a3b8; display:flex; align-items:center;
  justify-content:center; cursor:pointer; transition:all .15s; font-size:.88rem;
}
.qbt-view-btn.active { background:#eff6ff; border-color:#bfdbfe; color:#1a4fc4; }
.qbt-view-btn:hover:not(.active) { background:#f1f5f9; color:#475569; }
.qbt-badge {
  background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff;
  border-radius:100px; padding:.25rem .8rem; font-size:.72rem;
  font-weight:800; white-space:nowrap; font-family:'SUSE',sans-serif;
}

/* ── Card grid ───────────────────────────────────────────── */
.qbt-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; }
.qbt-grid.list-mode { grid-template-columns:1fr; gap:.55rem; }

/* ── Card ── */
.qbt-card {
  background:#fff; border-radius:18px; border:1px solid #f0f4f8;
  box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05);
  overflow:hidden; transition:transform .22s, box-shadow .22s;
  animation:qbt-up .4s cubic-bezier(.16,1,.3,1) both;
}
.qbt-card:hover { transform:translateY(-4px); box-shadow:0 14px 44px rgba(0,0,0,.11); }
@keyframes qbt-up { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

.qbt-card-accent { height:4px; }
.qbt-card-body { padding:1.2rem 1.2rem .85rem; }
.qbt-card-head { display:flex; align-items:flex-start; gap:.85rem; margin-bottom:.95rem; }
.qbt-card-icon {
  width:46px; height:46px; border-radius:13px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:1.25rem; color:#fff; box-shadow:0 6px 16px rgba(0,0,0,.18);
}
.qbt-card-tag {
  display:inline-block; border-radius:8px; padding:.16rem .58rem;
  font-size:.66rem; font-weight:800; letter-spacing:.07em; margin-bottom:.3rem;
}
.qbt-card-name {
  font-size:.97rem; font-weight:800; color:#0f172a; line-height:1.25;
  font-family:'SUSE',sans-serif;
}
.qbt-card-meta { display:flex; flex-wrap:wrap; gap:.3rem; margin-top:.4rem; }
.qbt-meta-pill {
  display:inline-flex; align-items:center; gap:.25rem; border-radius:100px;
  padding:.15rem .55rem; font-size:.65rem; font-weight:700;
  background:#f1f5f9; color:#475569;
}
.qbt-meta-desc { font-size:.73rem; color:#94a3b8; line-height:1.5; margin-top:.35rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

/* ── 3-stat strip ── */
.qbt-stats { display:flex; gap:.45rem; padding:0 1.2rem .8rem; }
.qbt-stat {
  flex:1; background:#f8fafc; border-radius:10px; padding:.5rem .6rem;
  border:1px solid #f0f4f8; text-align:center; transition:background .15s;
}
.qbt-stat:hover { background:#f1f5f9; }
.qbt-stat-val { font-size:.95rem; font-weight:800; color:#0f172a; font-family:'SUSE',sans-serif; line-height:1; }
.qbt-stat-lbl { font-size:.58rem; color:#94a3b8; font-weight:600; margin-top:.12rem; text-transform:uppercase; letter-spacing:.04em; }

/* ── Progress ── */
.qbt-prog-wrap { padding:0 1.2rem .8rem; }
.qbt-prog-labels { display:flex; justify-content:space-between; font-size:.67rem; font-weight:600; color:#94a3b8; margin-bottom:.3rem; }
.qbt-prog-track { height:5px; background:#f0f4f8; border-radius:100px; overflow:hidden; }
.qbt-prog-fill  { height:100%; border-radius:100px; width:0%; transition:width 1.3s cubic-bezier(.16,1,.3,1); }

/* ── Status pills ── */
.qbt-pills { display:flex; gap:.35rem; padding:0 1.2rem .8rem; flex-wrap:wrap; }
.qbt-pill {
  display:inline-flex; align-items:center; gap:.28rem;
  border-radius:100px; padding:.16rem .58rem; font-size:.65rem; font-weight:700;
}

/* ── Card footer ── */
.qbt-card-foot {
  display:flex; align-items:center; justify-content:space-between;
  padding:.65rem 1.2rem; background:#fafbfd; border-top:1px solid #f0f4f8;
}
.qbt-card-date { font-size:.66rem; color:#94a3b8; font-weight:600; }
.qbt-card-acts { display:flex; gap:.38rem; }
.qbt-icn { width:30px; height:30px; border-radius:9px; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.78rem; transition:all .15s; }
.qbt-icn:active { transform:scale(.88); }
.qbt-icn-edit { background:#eff6ff; color:#1a4fc4; }
.qbt-icn-edit:hover { background:#1a4fc4; color:#fff; }
.qbt-icn-del  { background:#fff1f2; color:#e11d48; }
.qbt-icn-del:hover  { background:#e11d48; color:#fff; }

/* ── List mode overrides ── */
.qbt-grid.list-mode .qbt-card { border-radius:14px; }
.qbt-grid.list-mode .qbt-card-accent { display:none; }
.qbt-grid.list-mode .qbt-card-body { padding:.8rem 1rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap; }
.qbt-grid.list-mode .qbt-card-head { margin-bottom:0; flex:1; min-width:200px; }
.qbt-grid.list-mode .qbt-stats { padding:0; flex-shrink:0; }
.qbt-grid.list-mode .qbt-prog-wrap,
.qbt-grid.list-mode .qbt-pills   { display:none; }
.qbt-grid.list-mode .qbt-card-foot { padding:.6rem 1rem; }

/* ── Empty state ── */
.qbt-empty {
  text-align:center; padding:4rem 2rem; background:#fff;
  border-radius:18px; border:1.5px dashed #e2e8f0; grid-column:1/-1;
}
.qbt-empty-icon { font-size:3.5rem; color:#e2e8f0; margin-bottom:1rem; display:block; }

/* ── Skeleton ── */
.qbt-skel { background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size:200% 100%; animation:qbt-shimmer 1.5s infinite; border-radius:8px; }
@keyframes qbt-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Modal ── */
.qbt-modal .modal-content { border-radius:20px; border:none; box-shadow:0 24px 80px rgba(0,0,0,.18); overflow:hidden; font-family:'Open Sans',sans-serif; }
.qbt-modal .modal-header { border-bottom:none; padding:1.3rem 1.5rem; }
.qbt-modal .modal-title { font-size:.95rem; font-weight:800; color:#fff; font-family:'SUSE',sans-serif; display:flex; align-items:center; gap:.6rem; }
.qbt-modal .modal-body { padding:1.4rem 1.5rem; }
.qbt-modal .form-label { font-size:.77rem; font-weight:700; color:#475569; margin-bottom:.32rem; }
.qbt-modal .form-control,
.qbt-modal .form-select { border-radius:11px; border:1.5px solid #e2e8f0; font-size:.81rem; font-family:inherit; padding:.52rem .85rem; transition:border-color .18s, box-shadow .18s; }
.qbt-modal .form-control:focus,
.qbt-modal .form-select:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); outline:none; }
.qbt-modal .form-control.is-invalid { border-color:#e11d48; box-shadow:0 0 0 3px rgba(225,29,72,.08); }
.qbt-modal .modal-footer { border-top:1px solid #f0f4f8; padding:.85rem 1.5rem; }
.qbt-modal-save {
  display:inline-flex; align-items:center; gap:.4rem;
  background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff;
  border:none; border-radius:11px; padding:.55rem 1.3rem;
  font-size:.81rem; font-weight:700; cursor:pointer; font-family:inherit;
  box-shadow:0 4px 14px rgba(26,79,196,.35); transition:filter .18s;
}
.qbt-modal-save:hover { filter:brightness(1.1); color:#fff; }
.qbt-modal-cancel {
  background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0;
  border-radius:11px; padding:.5rem 1.1rem; font-size:.79rem;
  font-weight:700; cursor:pointer; font-family:inherit; transition:background .15s;
}
.qbt-modal-cancel:hover { background:#e2e8f0; }
</style>

<div class="container-fluid px-3 py-3 qbt-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbt-hero" style="background:<?= $ui['hero_bg'] ?>">
  <div class="qbt-hero-grid"></div>
  <!-- Animated orbs -->
  <div style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:220px;height:220px;border-radius:50%;background:conic-gradient(from 0deg,<?= $ui['orb1'] ?>,<?= $ui['orb2'] ?>,<?= $ui['orb1'] ?>);filter:blur(42px);opacity:.55;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div style="position:absolute;left:30%;bottom:-40px;width:140px;height:140px;border-radius:50%;background:<?= $ui['orb2'] ?>;filter:blur(36px);opacity:.35;z-index:0"></div>

  <div class="qbt-hero-inner">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="qbt-hero-badge"><i class="bi <?= $icon ?>"></i>Question Bank Taxonomy</div>
        <div class="qbt-hero-title"><em><?= $title ?></em> Management</div>
        <div class="qbt-hero-sub"><?= $ui['hero_sub'] ?></div>
        <div class="qbt-kpis" id="heroKpis">
          <?php for ($ki=0;$ki<4;$ki++): ?>
          <div class="qbt-kpi"><div class="qbt-skel" style="width:44px;height:22px;margin-bottom:5px"></div><div class="qbt-kpi-lbl">Loading</div></div>
          <?php endfor; ?>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-end align-items-center gap-3 mt-3 mt-lg-0">
        <!-- Quick nav -->
        <div class="qbt-side-panel">
          <div class="qbt-side-title">Taxonomy Modules</div>
          <?php foreach ($all_nav as $vk => $vn): ?>
          <a href="?view=<?= $vk ?>" class="qbt-nav-link<?= $vk===$view?' active':'' ?>">
            <i class="bi <?= $vn['icon'] ?>"></i><?= $vn['label'] ?>
            <?php if ($vk===$view): ?><i class="bi bi-check2 ms-auto" style="opacity:.7;font-size:.7rem"></i><?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
        <button class="qbt-add-btn" onclick="openAddModal()">
          <i class="bi bi-plus-lg"></i>Add <?= rtrim($title,"'s") === $title ? rtrim($title,'s') : $title ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Toolbar ───────────────────────────────────────────── -->
<div class="qbt-toolbar">
  <div class="qbt-search-wrap">
    <i class="bi bi-search"></i>
    <input type="text" class="qbt-search" id="qbSearch" placeholder="Search <?= strtolower($title) ?>…" oninput="filterCards(this.value)">
  </div>
  <select class="qbt-sort" id="qbSort" onchange="renderCards()">
    <?php foreach ($ui['sort'] as $so): ?>
    <option value="<?= $so['val'] ?>"><?= $so['label'] ?></option>
    <?php endforeach; ?>
  </select>
  <div class="qbt-view-btns">
    <button class="qbt-view-btn active" id="btnGrid" onclick="setView('grid')" title="Grid"><i class="bi bi-grid-fill"></i></button>
    <button class="qbt-view-btn"        id="btnList" onclick="setView('list')" title="List"><i class="bi bi-list-ul"></i></button>
  </div>
  <div class="qbt-badge" id="qbCount">…</div>
  <button class="qbt-add-btn d-lg-none" onclick="openAddModal()"><i class="bi bi-plus-lg"></i>Add</button>
</div>

<!-- ── Card grid ─────────────────────────────────────────── -->
<div class="qbt-grid" id="qbGrid">
  <?php for ($si=0;$si<6;$si++): ?>
  <div class="qbt-card">
    <div class="qbt-card-accent qbt-skel" style="height:4px"></div>
    <div class="qbt-card-body">
      <div class="qbt-card-head">
        <div class="qbt-skel" style="width:46px;height:46px;border-radius:13px;flex-shrink:0"></div>
        <div style="flex:1"><div class="qbt-skel" style="width:52px;height:16px;border-radius:6px;margin-bottom:7px"></div><div class="qbt-skel" style="width:75%;height:20px;border-radius:6px"></div></div>
      </div>
    </div>
    <div class="qbt-stats"><?php for($j=0;$j<3;$j++): ?><div class="qbt-stat"><div class="qbt-skel" style="width:100%;height:36px;border-radius:8px"></div></div><?php endfor; ?></div>
    <div class="qbt-prog-wrap"><div class="qbt-skel" style="width:100%;height:24px;border-radius:6px"></div></div>
    <div class="qbt-card-foot" style="border-top:1px solid #f0f4f8">
      <div class="qbt-skel" style="width:80px;height:13px;border-radius:5px"></div>
      <div class="qbt-skel" style="width:64px;height:28px;border-radius:9px"></div>
    </div>
  </div>
  <?php endfor; ?>
</div>

</div><!-- /.container-fluid -->

<!-- ── Add / Edit Modal ───────────────────────────────────── -->
<div class="modal fade qbt-modal" id="qbModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
    <div class="modal-content">
      <div class="modal-header" style="background:<?= $ui['hero_bg'] ?>">
        <div class="modal-title">
          <div style="width:30px;height:30px;border-radius:9px;background:rgba(255,255,255,.13);display:flex;align-items:center;justify-content:center"><i class="bi <?= $icon ?>"></i></div>
          <span id="qbModalTitle">Add</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) brightness(2);opacity:.7"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="qbEditId">
        <?php foreach ($fields as $f): ?>
        <div class="mb-3">
          <label class="form-label"><?= $f['label'] ?><?= $f['req'] ? ' <span class="text-danger">*</span>' : '' ?></label>
          <?php if ($f['type']==='textarea'): ?>
            <textarea id="field_<?= $f['name'] ?>" class="form-control" rows="3" placeholder="<?= $f['label'] ?>"></textarea>
          <?php elseif ($f['type']==='select_subjects'): ?>
            <select id="field_<?= $f['name'] ?>" class="form-select"><option value="">— Select Subject —</option></select>
          <?php elseif ($f['type']==='select_levels'): ?>
            <select id="field_<?= $f['name'] ?>" class="form-select"><option value="">— Select Level —</option></select>
          <?php elseif ($f['type']==='select_chapters'): ?>
            <select id="field_<?= $f['name'] ?>" class="form-select"><option value="">— Select Chapter —</option></select>
          <?php else: ?>
            <input id="field_<?= $f['name'] ?>" type="<?= $f['type'] ?>" class="form-control" placeholder="<?= $f['label'] ?>">
          <?php endif; ?>
          <?php if (!empty($f['help'])): ?><small class="text-muted" style="font-size:.71rem"><?= $f['help'] ?></small><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="modal-footer gap-2">
        <button type="button" class="qbt-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="qbt-modal-save" onclick="saveRecord()">
          <i class="bi bi-check2-circle"></i>Save Record
        </button>
      </div>
    </div>
  </div>
</div>

<script>
/* ── Config from PHP ─────────────────────────────────────── */
const QB_ENTITY = '<?= $entity ?>';
const QB_TITLE  = '<?= addslashes($title) ?>';
const QB_ICON   = '<?= $icon ?>';
const QB_FIELDS = <?= json_encode($fields) ?>;
const QB_UI     = <?= json_encode($ui) ?>;

/* ── Colour palette (8 colours, cycled per card) ─────────── */
const PAL = [
  {grad:'linear-gradient(135deg,#1a4fc4,#6d28d9)', glow:'rgba(26,79,196,.25)',  light:'#eff6ff',  text:'#1a4fc4'},
  {grad:'linear-gradient(135deg,#059669,#0d9488)', glow:'rgba(5,150,105,.25)',  light:'#f0fdf4',  text:'#059669'},
  {grad:'linear-gradient(135deg,#d97706,#f59e0b)', glow:'rgba(217,119,6,.25)',  light:'#fffbeb',  text:'#d97706'},
  {grad:'linear-gradient(135deg,#dc2626,#e11d48)', glow:'rgba(220,38,38,.25)',  light:'#fff1f2',  text:'#dc2626'},
  {grad:'linear-gradient(135deg,#0891b2,#0ea5e9)', glow:'rgba(8,145,178,.25)',  light:'#f0f9ff',  text:'#0891b2'},
  {grad:'linear-gradient(135deg,#be185d,#ec4899)', glow:'rgba(190,24,93,.25)',  light:'#fdf2f8',  text:'#be185d'},
  {grad:'linear-gradient(135deg,#7c3aed,#a855f7)', glow:'rgba(124,58,237,.25)', light:'#f5f3ff',  text:'#7c3aed'},
  {grad:'linear-gradient(135deg,#ea580c,#f97316)', glow:'rgba(234,88,12,.25)',  light:'#fff7ed',  text:'#ea580c'},
];

/* ── Icon resolution per entity ──────────────────────────── */
const ICON_MAPS = {
  subjects: {math:'bi-calculator',science:'bi-flask',biology:'bi-heart-pulse',chemistry:'bi-flask',physics:'bi-cpu',english:'bi-translate',history:'bi-book-half',geography:'bi-globe',music:'bi-music-note-beamed',art:'bi-palette',computer:'bi-cpu',ict:'bi-cpu',kiswahili:'bi-translate',religion:'bi-shield-fill',commerce:'bi-shop',bookkeeping:'bi-journal-text',civics:'bi-bank'},
  bloom_levels: {remember:'bi-brain',recall:'bi-brain',understand:'bi-book-open',comprehend:'bi-book-open',apply:'bi-hammer',use:'bi-hammer',analyse:'bi-search',analyze:'bi-search',evaluate:'bi-patch-check-fill',judge:'bi-patch-check-fill',create:'bi-lightbulb-fill',synthesize:'bi-lightbulb-fill'},
  difficulty_levels: {easy:'bi-circle',simple:'bi-circle',basic:'bi-circle',medium:'bi-circle-half',moderate:'bi-circle-half',intermediate:'bi-circle-half',hard:'bi-circle-fill',difficult:'bi-circle-fill',advanced:'bi-circle-fill',expert:'bi-fire',extreme:'bi-fire'},
};

function resolveIcon(row, idx) {
  const map = ICON_MAPS[QB_ENTITY];
  if (map) {
    const nameVal = (row[QB_UI.name_key] || '').toLowerCase();
    for (const [k, v] of Object.entries(map)) {
      if (nameVal.includes(k)) return v;
    }
  }
  return QB_ICON;
}

/* ── DCM Alerts ──────────────────────────────────────────── */
const dcmAlert = {
  _css:`
    .ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}
    .ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem!important}
    .ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}
    .ds-can{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important;background:#f1f5f9!important;color:#475569!important;border:1.5px solid #e2e8f0!important}
    .ds-ico{border:none!important;margin-bottom:.4rem!important}
    .ds-tst{border-radius:14px!important;font-family:'Open Sans',sans-serif!important;box-shadow:0 8px 32px rgba(0,0,0,.14)!important;padding:.75rem 1.1rem!important;border-left:4px solid}
    .dst-ok{border-color:#059669!important}.dst-er{border-color:#dc2626!important}.dst-wn{border-color:#d97706!important}
  `,
  _css_done: false,
  _inject(){ if(!this._css_done){const s=document.createElement('style');s.textContent=this._css;document.head.appendChild(s);this._css_done=true;} },
  toast(icon,title,text=''){
    this._inject();
    const cls={success:'dst-ok',error:'dst-er',warning:'dst-wn'}[icon]||'';
    Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:3400,timerProgressBar:true,icon,title,text,customClass:{popup:`ds-tst ${cls}`}});
  },
  success(t,x=''){ this.toast('success',t,x); },
  error(t,x=''){
    this._inject();
    Swal.fire({icon:'error',title:t,text:x||'Something went wrong.',customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#dc2626',confirmButtonText:'Got it'});
  },
  validation(){
    this._inject();
    Swal.fire({icon:'warning',title:'Required fields missing',html:'<div style="font-size:.85rem;color:#64748b">Please fill in all highlighted fields.</div>',customClass:{popup:'ds-pop',title:'ds-ttl',icon:'ds-ico',confirmButton:'ds-btn'},confirmButtonColor:'#d97706',confirmButtonText:"Fix it",showClass:{popup:'animate__animated animate__shakeX animate__faster'}});
  },
  loading(t='Saving…'){ this._inject(); Swal.fire({title:t,allowOutsideClick:false,customClass:{popup:'ds-pop',title:'ds-ttl'},didOpen:()=>Swal.showLoading()}); },
  confirm({title,text,confirmText='Confirm',confirmColor='#dc2626',onConfirm}){
    this._inject();
    Swal.fire({title,text,icon:'warning',showCancelButton:true,confirmButtonText:confirmText,cancelButtonText:'Cancel',confirmButtonColor:confirmColor,reverseButtons:true,
      customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn',cancelButton:'ds-can',icon:'ds-ico'},
      showClass:{popup:'animate__animated animate__zoomIn animate__faster'},
      hideClass:{popup:'animate__animated animate__zoomOut animate__faster'}
    }).then(r=>{ if(r.isConfirmed&&onConfirm) onConfirm(); });
  }
};

let qbModal, allRows = [], searchQuery = '';

function _qbtInit() {
  qbModal = new bootstrap.Modal(document.getElementById('qbModal'));
  loadData();
  if (QB_FIELDS.some(f=>f.type==='select_subjects')) loadOpts('subjects','field_subject_id','subject_id','subject_name');
  if (QB_FIELDS.some(f=>f.type==='select_levels'))   loadOpts('levels',  'field_level_id',  'level_id',  'level_name');
  if (QB_FIELDS.some(f=>f.type==='select_chapters')) loadOpts('chapters','field_chapter_id','chapter_id','chapter_name');
}
// DOMContentLoaded already fired in SPA navigation — call immediately if DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _qbtInit);
} else {
  _qbtInit();
}

function loadOpts(entity, elId, vk, lk) {
  fetch(`ajax/ajax_qb_taxonomy.php?entity=${entity}&action=list`)
    .then(r=>r.json()).then(res=>{
      const sel = document.getElementById(elId);
      if (!sel || res.status!=='success') return;
      res.data.forEach(row => {
        const o = document.createElement('option');
        o.value = row[vk]; o.textContent = row[lk];
        sel.appendChild(o);
      });
    });
}

/* ── Load & render ───────────────────────────────────────── */
window.loadData = function() {
  fetch(`ajax/ajax_qb_taxonomy.php?entity=${QB_ENTITY}&action=list`)
    .then(r=>r.json())
    .then(res=>{
      allRows = res.status==='success' ? res.data : [];
      renderHeroKpis(allRows);
      renderCards(allRows);
    })
    .catch(()=>{
      document.getElementById('qbGrid').innerHTML = `
        <div class="qbt-empty">
          <i class="bi bi-exclamation-triangle qbt-empty-icon"></i>
          <div style="font-size:1rem;font-weight:800;color:#475569;margin-bottom:.35rem">Failed to load</div>
          <div style="font-size:.8rem;color:#94a3b8;margin-bottom:1.2rem">Could not reach the server.</div>
          <button class="qbt-add-btn" onclick="loadData()"><i class="bi bi-arrow-clockwise"></i>Retry</button>
        </div>`;
      document.getElementById('heroKpis').innerHTML = QB_UI.kpis.map(k=>`<div class="qbt-kpi"><div class="qbt-kpi-val">—</div><div class="qbt-kpi-lbl">${k.label}</div></div>`).join('');
    });
};
var loadData = window.loadData;

function renderHeroKpis(rows) {
  document.getElementById('heroKpis').innerHTML = QB_UI.kpis.map((k,i) => {
    let val = 0;
    if (k.agg === 'count') val = rows.length;
    else if (k.agg === 'sum' && k.key) val = rows.reduce((a,r)=>a+(+r[k.key]||0),0);
    const trend = (i===0) ? '' : (val>0 ? `<div class="qbt-kpi-trend"><i class="bi bi-arrow-up-right"></i>${val.toLocaleString()}</div>` : '');
    return `<div class="qbt-kpi"><div class="qbt-kpi-val">${val.toLocaleString()}</div><div class="qbt-kpi-lbl">${k.label}</div>${trend}</div>`;
  }).join('');
}

function getSortedRows(rows) {
  const sortVal = document.getElementById('qbSort')?.value || QB_UI.sort[0].val;
  const sortCfg = QB_UI.sort.find(s=>s.val===sortVal) || QB_UI.sort[0];
  return [...rows].sort((a,b) => {
    const va = a[sortCfg.key] ?? '';
    const vb = b[sortCfg.key] ?? '';
    const na = isNaN(va) ? va : +va;
    const nb = isNaN(vb) ? vb : +vb;
    const cmp = typeof na==='number' ? na-nb : String(na).localeCompare(String(nb));
    return sortCfg.dir==='d' ? -cmp : cmp;
  });
}

window.renderCards = function(rows) {
  if (rows === undefined) rows = allRows;
  const grid   = document.getElementById('qbGrid');
  const sorted = getSortedRows(rows);
  const maxQ   = Math.max(...sorted.map(r=>+(r[QB_UI.progress]||0)), 1);
  const total  = sorted.length;

  document.getElementById('qbCount').textContent = `${total} ${QB_TITLE}${total!==1?'s':''}`;

  if (!total) {
    const q = document.getElementById('qbSearch').value;
    grid.innerHTML = `
      <div class="qbt-empty">
        <i class="bi bi-inbox qbt-empty-icon"></i>
        <div style="font-size:1rem;font-weight:800;color:#475569;margin-bottom:.35rem">${q ? 'No matches found' : 'Nothing here yet'}</div>
        <div style="font-size:.8rem;color:#94a3b8;margin-bottom:1.2rem">${q ? 'Try a different search term.' : 'Click Add to create your first entry.'}</div>
        ${!q ? `<button class="qbt-add-btn" onclick="openAddModal()"><i class="bi bi-plus-lg"></i>Add ${QB_TITLE}</button>` : ''}
      </div>`;
    return;
  }

  grid.innerHTML = sorted.map((row, i) => {
    const pal  = PAL[i % PAL.length];
    const icon = resolveIcon(row, i);
    const pk   = row[Object.keys(row)[0]];

    /* tag badge */
    let tagHtml = '';
    if (QB_UI.tag_key && (row[QB_UI.tag_key] !== null && row[QB_UI.tag_key] !== '')) {
      tagHtml = `<span class="qbt-card-tag" style="background:${pal.light};color:${pal.text}">${QB_UI.tag_pre}${row[QB_UI.tag_key]}</span>`;
    }

    /* meta (pills or description) */
    const metaHtml = (QB_UI.meta||[]).map(m => {
      const v = row[m.key];
      if (!v) return '';
      if (m.type === 'desc') return `<div class="qbt-meta-desc">${v}</div>`;
      return `<span class="qbt-meta-pill"><i class="bi bi-tag" style="font-size:.6rem;opacity:.6"></i>${v}</span>`;
    }).join('');

    /* stats strip */
    const statsHtml = QB_UI.stats.map(s => {
      const v = +(row[s.key]||0);
      return `<div class="qbt-stat"><div class="qbt-stat-val" style="color:${v>0?pal.text:'#94a3b8'}">${v.toLocaleString()}</div><div class="qbt-stat-lbl">${s.label}</div></div>`;
    }).join('');

    /* progress bar */
    const progVal  = +(row[QB_UI.progress]||0);
    const progPct  = Math.round(progVal/maxQ*100);
    const pubPct   = progVal>0 ? Math.round((+(row.published_count||0))/progVal*100) : 0;
    const progHtml = `
      <div class="qbt-prog-wrap">
        <div class="qbt-prog-labels">
          <span style="color:${pal.text};font-weight:700">Coverage</span>
          <span>${pubPct}% published</span>
        </div>
        <div class="qbt-prog-track">
          <div class="qbt-prog-fill" data-pct="${progPct}" style="background:${pal.grad}"></div>
        </div>
      </div>`;

    /* status pills */
    const pillsArr = (QB_UI.pills||[]).filter(p=>+(row[p.key]||0)>0);
    const pillsHtml = pillsArr.length ? `
      <div class="qbt-pills">${pillsArr.map(p=>`
        <span class="qbt-pill" style="background:${p.bg};color:${p.color}">
          <i class="bi ${p.icon}" style="font-size:.62rem"></i>${row[p.key]} ${p.label}
        </span>`).join('')}
      </div>` : '';

    /* date */
    const dateStr = row.created_at ? new Date(row.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '';
    const footLeft = dateStr ? `<span class="qbt-card-date"><i class="bi bi-calendar3 me-1"></i>${dateStr}</span>` : '<span></span>';

    const rowJson = JSON.stringify(row).replace(/'/g,"&#39;");

    return `
    <div class="qbt-card" style="animation-delay:${Math.min(i*0.04,.3)}s">
      <div class="qbt-card-accent" style="background:${pal.grad}"></div>
      <div class="qbt-card-body">
        <div class="qbt-card-head">
          <div class="qbt-card-icon" style="background:${pal.grad};box-shadow:0 6px 18px ${pal.glow}">
            <i class="bi ${icon}"></i>
          </div>
          <div style="min-width:0;flex:1">
            ${tagHtml}
            <div class="qbt-card-name">${row[QB_UI.name_key]||'—'}</div>
            ${metaHtml ? `<div class="qbt-card-meta">${metaHtml}</div>` : ''}
          </div>
        </div>
      </div>
      <div class="qbt-stats">${statsHtml}</div>
      ${progHtml}
      ${pillsHtml}
      <div class="qbt-card-foot">
        ${footLeft}
        <div class="qbt-card-acts">
          <button class="qbt-icn qbt-icn-edit" onclick='editRecord(${rowJson})' title="Edit"><i class="bi bi-pencil"></i></button>
          <button class="qbt-icn qbt-icn-del"  onclick="deleteRecord(${pk})"     title="Delete"><i class="bi bi-trash"></i></button>
        </div>
      </div>
    </div>`;
  }).join('');

  /* animate progress bars after paint */
  requestAnimationFrame(() => {
    document.querySelectorAll('.qbt-prog-fill').forEach(el => { el.style.width = (el.dataset.pct||0)+'%'; });
  });
};
var renderCards = window.renderCards;

window.filterCards = function(q) {
  searchQuery = q;
  const filtered = q ? allRows.filter(r=>Object.values(r).some(v=>String(v).toLowerCase().includes(q.toLowerCase()))) : allRows;
  renderCards(filtered);
};

window.setView = function(v) {
  document.getElementById('btnGrid').classList.toggle('active', v==='grid');
  document.getElementById('btnList').classList.toggle('active', v==='list');
  document.getElementById('qbGrid').classList.toggle('list-mode', v==='list');
};

/* ── CRUD ────────────────────────────────────────────────── */
window.openAddModal = function() {
  document.getElementById('qbModalTitle').textContent = `Add ${QB_TITLE}`;
  document.getElementById('qbEditId').value = '';
  QB_FIELDS.forEach(f => {
    const el = document.getElementById(`field_${f.name}`);
    if (el) { el.value=''; el.classList.remove('is-invalid'); }
  });
  qbModal.show();
};

window.editRecord = function(row) {
  document.getElementById('qbModalTitle').textContent = `Edit ${QB_TITLE}`;
  document.getElementById('qbEditId').value = row[Object.keys(row)[0]];
  QB_FIELDS.forEach(f => {
    const el = document.getElementById(`field_${f.name}`);
    if (el) { el.value = row[f.name]??''; el.classList.remove('is-invalid'); }
  });
  qbModal.show();
};

window.saveRecord = function() {
  const id   = document.getElementById('qbEditId').value;
  const data = { entity: QB_ENTITY, action: id?'update':'create', id };
  let valid  = true;
  QB_FIELDS.forEach(f => {
    const el = document.getElementById(`field_${f.name}`);
    if (!el) return;
    data[f.name] = el.value.trim();
    if (f.req && !data[f.name]) { el.classList.add('is-invalid'); valid=false; }
    else el.classList.remove('is-invalid');
  });
  if (!valid) return dcmAlert.validation();
  dcmAlert.loading('Saving…');
  fetch('ajax/ajax_qb_taxonomy.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) })
    .then(r=>r.json())
    .then(res=>{
      Swal.close();
      if (res.status==='success') {
        qbModal.hide();
        dcmAlert.success(id ? `${QB_TITLE} updated!` : `${QB_TITLE} added!`, res.message||'Record saved.');
        loadData();
      } else dcmAlert.error('Could not save', res.message);
    })
    .catch(()=>dcmAlert.error('Request failed','Unable to reach the server.'));
};

window.deleteRecord = function(id) {
  dcmAlert.confirm({
    title: `Delete this ${QB_TITLE}?`,
    text:  'This action cannot be undone. Linked data may also be affected.',
    confirmText: '<i class="bi bi-trash me-1"></i>Yes, delete it',
    confirmColor: '#dc2626',
    onConfirm() {
      dcmAlert.loading('Deleting…');
      fetch('ajax/ajax_qb_taxonomy.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({entity:QB_ENTITY,action:'delete',id}) })
        .then(r=>r.json())
        .then(res=>{
          Swal.close();
          if (res.status==='success') { dcmAlert.success('Deleted!',`${QB_TITLE} removed.`); loadData(); }
          else dcmAlert.error('Delete failed', res.message);
        })
        .catch(()=>dcmAlert.error('Request failed','Unable to reach the server.'));
    }
  });
};
</script>
