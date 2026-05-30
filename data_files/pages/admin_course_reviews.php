<?php if (($user_role ?? 0) != 5) { include('403.php'); return; } ?>
<style>
/* ── Hero ── */
.acr-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);padding:2.2rem 0 4rem;position:relative;overflow:hidden}
.acr-hero::before{content:'';position:absolute;inset:0;pointer-events:none;
  background:radial-gradient(circle at 15% 50%,rgba(99,102,241,.22) 0%,transparent 55%),
             radial-gradient(circle at 85% 20%,rgba(139,92,246,.16) 0%,transparent 50%)}
.acr-hero::after{content:'';position:absolute;inset:0;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='20' cy='20' r='1.2' fill='%23fff' fill-opacity='.025'/%3E%3C/svg%3E")}

/* ── Canvas ── */
.acr-canvas{max-width:1400px;margin:-2.2rem auto 2.5rem;padding:0 1.25rem;position:relative;z-index:10}

/* ── Stat cards ── */
.stat-card{background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);padding:1.2rem 1.4rem;transition:transform .15s,box-shadow .15s}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.1)}
.stat-icon{width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
.stat-num{font-size:1.55rem;font-weight:800;line-height:1.1}
.stat-lbl{font-size:.73rem;color:#94a3b8;font-weight:500;margin-top:.15rem}

/* ── Split panel ── */
.acr-split{display:grid;grid-template-columns:400px 1fr;gap:1.25rem;align-items:start}
@media(max-width:1024px){.acr-split{grid-template-columns:1fr}}

/* ── Left panel ── */
.list-panel{background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.05);overflow:hidden}
.list-panel-header{padding:1rem 1.1rem .75rem;border-bottom:1px solid #f1f5f9}

/* ── Pill tabs ── */
.pill-tabs{display:flex;gap:.3rem;flex-wrap:wrap}
.pill-tab{border:none;border-radius:100px;padding:.3rem .8rem;font-size:.73rem;font-weight:700;cursor:pointer;transition:all .18s;color:#64748b;background:#f1f5f9;display:inline-flex;align-items:center;gap:.35rem;white-space:nowrap}
.pill-tab:hover{background:#e2e8f0;color:#334155}
.pill-tab.active{color:#fff}
.pill-tab.t-all.active{background:#6366f1}
.pill-tab.t-pending.active{background:#d97706}
.pill-tab.t-approved.active{background:#16a34a}
.pill-tab.t-rejected.active{background:#dc2626}
.pill-tab.t-revision.active{background:#9333ea}
.pill-tab.t-chapterdel.active{background:#92400e}
.tab-count{background:rgba(255,255,255,.3);border-radius:100px;padding:.05rem .4rem;font-size:.65rem;min-width:16px;text-align:center;line-height:1.5}
.pill-tab:not(.active) .tab-count{background:rgba(0,0,0,.08);color:#475569}

/* ── Search ── */
.acr-search{border-radius:10px;border:1.5px solid #e2e8f0;font-size:.82rem;padding:.45rem .75rem .45rem 2.2rem;width:100%;transition:border-color .18s}
.acr-search:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.search-wrap{position:relative}
.search-wrap i{position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.82rem;pointer-events:none}

/* ── Review cards ── */
.review-list{max-height:calc(100vh - 340px);overflow-y:auto;padding:.5rem}
.review-list::-webkit-scrollbar{width:3px}
.review-list::-webkit-scrollbar-thumb{background:rgba(0,0,0,.1);border-radius:2px}

.rcard{background:#fff;border:1.5px solid #f1f5f9;border-radius:13px;padding:.85rem 1rem;cursor:pointer;transition:all .18s;margin-bottom:.55rem;display:flex;align-items:flex-start;gap:.85rem}
.rcard:hover{border-color:#c7d2fe;background:#fafbff;box-shadow:0 4px 16px rgba(99,102,241,.1)}
.rcard.active{border-color:#6366f1;background:#f5f7ff;box-shadow:0 4px 20px rgba(99,102,241,.14)}
.rcard-thumb{width:60px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid #e2e8f0}
.rcard-title{font-size:.84rem;font-weight:700;color:#1e293b;line-height:1.3;margin-bottom:.2rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.rcard-meta{font-size:.72rem;color:#94a3b8}
.rcard-foot{display:flex;align-items:center;gap:.4rem;margin-top:.4rem;flex-wrap:wrap}

/* ── Status badges ── */
.sbadge{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .55rem;border-radius:100px;font-size:.67rem;font-weight:700;white-space:nowrap}
.sbadge.pending{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
.sbadge.approved{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.sbadge.rejected{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
.sbadge.revision_needed{background:#f3e8ff;color:#7c3aed;border:1px solid #ddd6fe}

/* ── Right / Detail panel ── */
.detail-panel{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.09);border:1px solid rgba(0,0,0,.06);position:sticky;top:80px;overflow:hidden;min-height:200px}
.dp-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 60%,#312e81 100%);padding:1.5rem 1.6rem;position:relative;overflow:hidden}
.dp-hero::after{content:'';position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='20' cy='20' r='1.2' fill='%23fff' fill-opacity='.025'/%3E%3C/svg%3E");pointer-events:none}
.dp-thumb{width:76px;height:56px;border-radius:10px;object-fit:cover;border:2px solid rgba(255,255,255,.2);flex-shrink:0}
.dp-title{font-size:1rem;font-weight:800;color:#fff;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.dp-instructor{font-size:.76rem;color:rgba(255,255,255,.55);margin-top:.3rem}

.dp-section{padding:1.1rem 1.5rem;border-bottom:1px solid #f1f5f9}
.dp-section:last-child{border-bottom:none}
.dp-label{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:.6rem}
.dp-stat-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.6rem .8rem;text-align:center;flex:1}
.dp-stat-box .num{font-size:1.2rem;font-weight:800}
.dp-stat-box .lbl{font-size:.67rem;color:#94a3b8;margin-top:.1rem}

.note-box{background:#fafbff;border:1.5px solid #e0e7ff;border-radius:10px;padding:.85rem 1rem;font-size:.82rem;color:#475569;white-space:pre-wrap;line-height:1.6}
.note-box.admin-note{background:#fff7ed;border-color:#fed7aa}
.comment-ta{border-radius:12px;border:1.5px solid #e2e8f0;font-size:.83rem;padding:.75rem 1rem;resize:none;width:100%;transition:border-color .18s,box-shadow .18s}
.comment-ta:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}

/* ── Action buttons ── */
.acr-actions{display:flex;flex-wrap:wrap;gap:.6rem}
.action-btn{border:none;border-radius:11px;padding:.55rem 1.15rem;font-size:.8rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;transition:filter .15s,box-shadow .15s,transform .1s;white-space:nowrap}
.action-btn:active{transform:scale(.96)}
.action-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.ab-approve{background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;box-shadow:0 4px 14px rgba(22,163,74,.3)}
.ab-approve:hover:not(:disabled){filter:brightness(1.07)}
.ab-revision{background:linear-gradient(135deg,#d97706,#b45309);color:#fff;box-shadow:0 4px 14px rgba(217,119,6,.28)}
.ab-revision:hover:not(:disabled){filter:brightness(1.07)}
.ab-reject{background:linear-gradient(135deg,#dc2626,#9f1239);color:#fff;box-shadow:0 4px 14px rgba(220,38,38,.24)}
.ab-reject:hover:not(:disabled){filter:brightness(1.07)}
.ab-comment{background:#eef2ff;color:#6366f1;border:1.5px solid #c7d2fe}
.ab-comment:hover:not(:disabled){background:#e0e7ff}

/* ── Empty / decided states ── */
.acr-empty{text-align:center;padding:4rem 2rem;color:#94a3b8}

/* ══ Custom SweetAlert2 theme ══ */
.dcm-swal-popup{border-radius:20px!important;padding:2rem 1.75rem 1.75rem!important;box-shadow:0 24px 60px rgba(0,0,0,.18)!important;border:1px solid rgba(0,0,0,.06)!important;max-width:420px!important;font-family:inherit!important}
.dcm-swal-icon-wrap{width:60px;height:60px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.55rem;margin:0 auto 1rem}
.dcm-swal-icon-wrap.success{background:#dcfce7;color:#16a34a}
.dcm-swal-icon-wrap.error{background:#fee2e2;color:#dc2626}
.dcm-swal-icon-wrap.warn{background:#fef9c3;color:#d97706}
.dcm-swal-ttl{font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:.4rem;line-height:1.3}
.dcm-swal-txt{font-size:.84rem;color:#64748b;line-height:1.6;margin-top:.35rem}
.dcm-swal-quote{background:#f8fafc;border:1.5px solid #e2e8f0;border-left:4px solid #6366f1;border-radius:0 10px 10px 0;padding:.65rem .9rem;font-size:.8rem;color:#475569;text-align:left;margin-top:.75rem;white-space:pre-wrap;line-height:1.6;font-style:italic}
.dcm-swal-actions{gap:.6rem!important;margin-top:1.4rem!important;justify-content:flex-end!important;flex-direction:row-reverse!important}
.dcm-swal-confirm{border:none!important;border-radius:11px!important;padding:.55rem 1.3rem!important;font-size:.83rem!important;font-weight:700!important;cursor:pointer!important;display:inline-flex!important;align-items:center!important;gap:.35rem!important;transition:filter .15s,transform .1s!important}
.dcm-swal-confirm:hover{filter:brightness(1.08)!important}
.dcm-swal-confirm:active{transform:scale(.96)!important}
.dcm-swal-cancel{border:1.5px solid #e2e8f0!important;background:#f8fafc!important;color:#64748b!important;border-radius:11px!important;padding:.55rem 1.2rem!important;font-size:.83rem!important;font-weight:600!important;cursor:pointer!important;transition:background .15s!important}
.dcm-swal-cancel:hover{background:#e2e8f0!important;color:#334155!important}
.swal2-timer-progress-bar{background:rgba(255,255,255,.35)!important;border-radius:0!important}
@keyframes dcm-swal-in{from{opacity:0;transform:scale(.88) translateY(16px)}to{opacity:1;transform:scale(1) translateY(0)}}
@keyframes dcm-swal-out{from{opacity:1;transform:scale(1)}to{opacity:0;transform:scale(.92)}}
.dcm-swal-in{animation:dcm-swal-in .22s cubic-bezier(.34,1.56,.64,1) both}
.dcm-swal-out{animation:dcm-swal-out .16s ease-in both}
.decided-banner{border-radius:12px;padding:.85rem 1.1rem;font-size:.83rem;font-weight:600;display:flex;align-items:center;gap:.5rem}
.decided-banner.approved{background:#f0fdf4;color:#166534;border:1.5px solid #bbf7d0}
.decided-banner.rejected{background:#fff1f2;color:#b91c1c;border:1.5px solid #fecaca}
.decided-banner.revision_needed{background:#faf5ff;color:#7c3aed;border:1.5px solid #ddd6fe}
</style>

<!-- HERO -->
<div class="acr-hero">
  <div class="container-xl position-relative" style="z-index:2">
    <nav class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:.75rem">
        <li class="breadcrumb-item"><a href="?view=admin_dashboard" class="text-white-50 text-decoration-none">Admin</a></li>
        <li class="breadcrumb-item active" style="color:rgba(255,255,255,.45)">Course Reviews</li>
      </ol>
    </nav>
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
           style="width:54px;height:54px;background:rgba(255,255,255,.1);font-size:1.5rem;color:#a5b4fc">
        <i class="bi bi-shield-check"></i>
      </div>
      <div>
        <h4 class="text-white fw-bold mb-0" style="letter-spacing:-.01em">Course Review Queue</h4>
        <p class="mb-0 small" style="color:rgba(255,255,255,.45)">Review instructor submissions and manage course approvals</p>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span id="heroPendingBadge" class="rounded-pill px-3 py-1"
              style="background:rgba(245,158,11,.2);color:#fbbf24;font-size:.8rem;font-weight:700;border:1px solid rgba(245,158,11,.3)">
          <i class="bi bi-hourglass-split me-1"></i><span id="heroPendingNum">—</span> Pending
        </span>
      </div>
    </div>
  </div>
</div>

<div class="acr-canvas">

  <!-- Stat cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#fef3c7"><i class="bi bi-hourglass-split" style="color:#d97706"></i></div>
        <div><div class="stat-num" style="color:#d97706" id="sPending">—</div><div class="stat-lbl">Pending Review</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#dcfce7"><i class="bi bi-check-circle-fill" style="color:#16a34a"></i></div>
        <div><div class="stat-num" style="color:#16a34a" id="sApprovedToday">—</div><div class="stat-lbl">Approved Today</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#fee2e2"><i class="bi bi-x-circle-fill" style="color:#dc2626"></i></div>
        <div><div class="stat-num" style="color:#dc2626" id="sRejected">—</div><div class="stat-lbl">Rejected</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#eef2ff"><i class="bi bi-collection-play-fill" style="color:#6366f1"></i></div>
        <div><div class="stat-num" style="color:#6366f1" id="sTotal">—</div><div class="stat-lbl">Total Requests</div></div>
      </div>
    </div>
  </div>

  <!-- Split panel -->
  <div class="acr-split">

    <!-- LEFT: list -->
    <div class="list-panel">
      <div class="list-panel-header">
        <div class="pill-tabs mb-2" id="pillTabs">
          <button class="pill-tab t-all active" data-s="" data-label="All">All <span class="tab-count" id="tc-all">—</span></button>
          <button class="pill-tab t-pending" data-s="pending">
            <i class="bi bi-hourglass-split"></i>Pending <span class="tab-count" id="tc-pending">—</span>
          </button>
          <button class="pill-tab t-approved" data-s="approved">
            <i class="bi bi-check-circle-fill"></i>Approved <span class="tab-count" id="tc-approved">—</span>
          </button>
          <button class="pill-tab t-rejected" data-s="rejected">
            <i class="bi bi-x-circle-fill"></i>Rejected <span class="tab-count" id="tc-rejected">—</span>
          </button>
          <button class="pill-tab t-revision" data-s="revision_needed">
            <i class="bi bi-arrow-repeat"></i>Revision <span class="tab-count" id="tc-revision">—</span>
          </button>
          <button class="pill-tab t-chapterdel" data-mode="chapter_del" data-s="" style="border-left:1.5px solid #e2e8f0;margin-left:.25rem">
            <i class="bi bi-folder-x"></i>Chapter Del. <span class="tab-count" id="tc-chdel">—</span>
          </button>
        </div>
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" id="acrSearch" class="acr-search" placeholder="Search course or instructor…">
        </div>
      </div>

      <div class="review-list" id="reviewList">
        <div class="acr-empty"><div class="spinner-border text-primary"></div></div>
      </div>
      <div id="reviewPager" class="d-flex justify-content-center gap-1 p-2 border-top" style="display:none!important"></div>
    </div>

    <!-- RIGHT: detail -->
    <div>
      <!-- Chapter deletion detail panel -->
      <div class="detail-panel" id="cdPanel" style="display:none">
        <div class="dp-hero position-relative" style="z-index:1;background:linear-gradient(135deg,#1c1917 0%,#292524 55%,#1c1917 100%)">
          <div class="d-flex align-items-start gap-3">
            <div style="width:54px;height:54px;border-radius:12px;background:rgba(245,158,11,.18);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fbbf24;flex-shrink:0">
              <i class="bi bi-folder-x"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="dp-title" id="cd_chapter_title"></div>
              <div class="dp-instructor" id="cd_course_name"></div>
              <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                <span id="cd_sbadge" class="sbadge pending"><i class="bi bi-hourglass-split" style="font-size:.45rem"></i> Pending</span>
                <span style="color:rgba(255,255,255,.38);font-size:.71rem" id="cd_time"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="dp-section">
          <div class="dp-label">Request Overview</div>
          <div class="d-flex gap-2">
            <div class="dp-stat-box">
              <div class="num" style="color:#f59e0b" id="cd_lessons">—</div>
              <div class="lbl">Lessons</div>
            </div>
            <div class="dp-stat-box">
              <div class="num" style="color:#6366f1" id="cd_instructor">—</div>
              <div class="lbl">Instructor</div>
            </div>
            <div class="dp-stat-box">
              <div class="num" style="color:#0891b2" id="cd_course_status">—</div>
              <div class="lbl">Course Status</div>
            </div>
          </div>
        </div>

        <!-- Instructor note -->
        <div class="dp-section" id="cd_note_sec" style="display:none">
          <div class="dp-label">Instructor's Reason</div>
          <div class="note-box" id="cd_note"></div>
        </div>

        <!-- Previous admin comment -->
        <div class="dp-section" id="cd_prev_sec" style="display:none">
          <div class="dp-label">Admin Response</div>
          <div class="note-box admin-note" id="cd_prev_comment"></div>
        </div>

        <!-- Decided banner -->
        <div class="dp-section" id="cd_decided_sec" style="display:none">
          <div class="decided-banner" id="cd_decided_banner"></div>
        </div>

        <!-- Admin comment -->
        <div class="dp-section" id="cd_action_sec">
          <div class="dp-label mb-2" id="cd_action_label">Admin Comment <span class="fw-normal text-muted" style="font-size:.68rem;text-transform:none;letter-spacing:0">(required when rejecting)</span></div>
          <textarea id="cd_comment" class="comment-ta" rows="3"
            placeholder="Add a note to the instructor (optional for approval, required for rejection)…"></textarea>
          <div class="acr-actions mt-3" id="cd_btn_row">
            <button class="action-btn ab-approve" id="cdBtnApprove" onclick="decideChDel('approve_del')">
              <i class="bi bi-trash3-fill"></i>Approve &amp; Delete
            </button>
            <button class="action-btn ab-reject" id="cdBtnReject" onclick="decideChDel('reject_del')">
              <i class="bi bi-x-circle-fill"></i>Reject
            </button>
          </div>
        </div>
      </div>

      <!-- Empty placeholder -->
      <div class="detail-panel acr-empty" id="detailEmpty">
        <div style="font-size:3.5rem;color:#e2e8f0"><i class="bi bi-shield-check"></i></div>
        <p class="text-muted mt-3 mb-0 small">Select a submission to review it</p>
      </div>

      <!-- Detail panel (hidden until selected) -->
      <div class="detail-panel" id="detailPanel" style="display:none">

        <!-- dp hero -->
        <div class="dp-hero position-relative" style="z-index:1">
          <div class="d-flex align-items-start gap-3">
            <img id="dp_thumb" src="" class="dp-thumb" onerror="this.src='../assets/img/logo.svg'">
            <div class="flex-grow-1 min-w-0">
              <div class="dp-title" id="dp_title"></div>
              <div class="dp-instructor" id="dp_instructor"></div>
              <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                <span id="dp_sbadge" class="sbadge"></span>
                <span style="color:rgba(255,255,255,.38);font-size:.71rem" id="dp_time"></span>
              </div>
            </div>
            <a id="dp_preview" href="#" target="_blank"
               class="btn btn-sm btn-outline-light flex-shrink-0" style="border-radius:9px;font-size:.74rem">
              <i class="bi bi-eye me-1"></i>Preview
            </a>
          </div>
        </div>

        <!-- Course stats -->
        <div class="dp-section">
          <div class="dp-label">Course Overview</div>
          <div class="d-flex gap-2">
            <div class="dp-stat-box">
              <div class="num" style="color:#6366f1" id="dp_chapters">—</div>
              <div class="lbl">Chapters</div>
            </div>
            <div class="dp-stat-box">
              <div class="num" style="color:#0891b2" id="dp_lessons">—</div>
              <div class="lbl">Lessons</div>
            </div>
            <div class="dp-stat-box">
              <div class="num" style="color:#16a34a" id="dp_free">—</div>
              <div class="lbl">Free Preview</div>
            </div>
            <div class="dp-stat-box">
              <div class="num" style="color:#d97706" id="dp_price">—</div>
              <div class="lbl">Price</div>
            </div>
          </div>
        </div>

        <!-- Instructor note -->
        <div class="dp-section" id="dp_note_sec" style="display:none">
          <div class="dp-label">Instructor's Message</div>
          <div class="note-box" id="dp_note"></div>
        </div>

        <!-- Previous admin comment -->
        <div class="dp-section" id="dp_prev_sec" style="display:none">
          <div class="dp-label">Previous Admin Comment</div>
          <div class="note-box admin-note" id="dp_prev_comment"></div>
        </div>

        <!-- Decided banner (shown when approved/rejected) -->
        <div class="dp-section" id="dp_decided_sec" style="display:none">
          <div class="decided-banner" id="dp_decided_banner"></div>
        </div>

        <!-- Comment textarea -->
        <div class="dp-section" id="dp_comment_sec">
          <div class="dp-label">Admin Comment / Feedback <span class="fw-normal text-muted" style="font-size:.68rem;text-transform:none;letter-spacing:0">(required for rejection & revision)</span></div>
          <textarea id="dp_comment" class="comment-ta" rows="4"
            placeholder="Write feedback for the instructor…"></textarea>
        </div>

        <!-- Actions -->
        <div class="dp-section" id="dp_action_sec">
          <div class="dp-label mb-2">Decision</div>
          <div class="acr-actions">
            <button class="action-btn ab-approve" id="btnApprove" onclick="decide('approve')">
              <i class="bi bi-check-circle-fill"></i>Approve &amp; Publish
            </button>
            <button class="action-btn ab-revision" id="btnRevision" onclick="decide('revision_needed')">
              <i class="bi bi-arrow-repeat"></i>Request Revision
            </button>
            <button class="action-btn ab-reject" id="btnReject" onclick="decide('reject')">
              <i class="bi bi-x-circle-fill"></i>Reject
            </button>
            <button class="action-btn ab-comment" id="btnSendComment" onclick="decide('comment')">
              <i class="bi bi-chat-dots-fill"></i>Send Comment
            </button>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
const AJAX = 'ajax/ajax_course_review.php';
let curPage = 1, curFilter = '', curSearch = '', activeId = null;
let tabCounts = { all:0, pending:0, approved:0, rejected:0, revision_needed:0 };
let curMode = 'reviews', curChDelFilter = 'pending', activeChDelId = null;

/* ══ Stats ══ */
function loadStats() {
  fetch(`${AJAX}?action=stats`).then(r=>r.json()).then(r=>{
    if (r.status !== 'success') return;
    document.getElementById('sPending').textContent       = r.pending;
    document.getElementById('sApprovedToday').textContent = r.approved_today;
    document.getElementById('sRejected').textContent      = r.rejected;
    document.getElementById('sTotal').textContent         = r.total;
    document.getElementById('heroPendingNum').textContent  = r.pending;
    // update tab counts from stats
    tabCounts.pending   = r.pending;
    tabCounts.rejected  = r.rejected;
    tabCounts.total     = r.total;
    updateTabCounts();
  });
}

function updateTabCounts() {
  document.getElementById('tc-pending').textContent  = tabCounts.pending  || 0;
  document.getElementById('tc-approved').textContent = tabCounts.approved  || '—';
  document.getElementById('tc-rejected').textContent = tabCounts.rejected  || 0;
  document.getElementById('tc-revision').textContent = tabCounts.revision  || '—';
  document.getElementById('tc-all').textContent      = tabCounts.total     || '—';
}

/* ══ List ══ */
function loadList(page) {
  page = page || 1; curPage = page;
  document.getElementById('reviewList').innerHTML = '<div class="acr-empty py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

  fetch(`${AJAX}?action=list&filter_status=${encodeURIComponent(curFilter)}&search=${encodeURIComponent(curSearch)}&page=${page}`)
  .then(r=>r.json()).then(r=>{
    if (r.status !== 'success') {
      document.getElementById('reviewList').innerHTML = `<div class="acr-empty"><i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>${r.message||'Error loading'}</div>`;
      return;
    }

    // update tab counts from totals
    tabCounts.total = r.total;
    updateTabCounts();

    if (!r.data.length) {
      document.getElementById('reviewList').innerHTML = `<div class="acr-empty"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No requests found</div>`;
      document.getElementById('reviewPager').innerHTML = '';
      document.getElementById('reviewPager').style.display = 'none';
      return;
    }

    document.getElementById('reviewList').innerHTML = r.data.map(req => {
      const thumb = req.thumbnail ? `../uploads/${req.thumbnail}` : '../assets/img/logo.svg';
      const statusIcon = { pending:'bi-hourglass-split', approved:'bi-check-circle-fill', rejected:'bi-x-circle-fill', revision_needed:'bi-arrow-repeat' };
      return `
      <div class="rcard${activeId==req.id?' active':''}" id="rcard_${req.id}" onclick="openDetail(${req.id})">
        <img src="${thumb}" class="rcard-thumb" onerror="this.src='../assets/img/logo.svg'">
        <div class="flex-grow-1 min-w-0">
          <div class="rcard-title">${esc(req.title)}</div>
          <div class="rcard-meta">${esc(req.first_name)} ${esc(req.last_name)} · ${esc(req.email_address)}</div>
          <div class="rcard-foot">
            <span class="sbadge ${req.status}"><i class="bi ${statusIcon[req.status]||'bi-circle'}"></i>${badgeLabel(req.status)}</span>
            <span class="rcard-meta"><i class="bi bi-collection me-1"></i>${req.chapters} ch · ${req.lessons} lessons</span>
            <span class="rcard-meta ms-auto"><i class="bi bi-clock me-1"></i>${timeAgo(req.submitted_at)}</span>
          </div>
        </div>
      </div>`;
    }).join('');

    // Pagination
    const pages = Math.ceil(r.total / r.per);
    if (pages > 1) {
      let pg = '';
      for (let p = 1; p <= pages; p++) {
        pg += `<button class="btn btn-sm ${p===page?'btn-primary':'btn-outline-secondary'}" onclick="loadList(${p})" style="border-radius:8px;width:32px;padding:0;height:32px">${p}</button>`;
      }
      document.getElementById('reviewPager').innerHTML = pg;
      document.getElementById('reviewPager').style.display = 'flex';
    } else {
      document.getElementById('reviewPager').style.display = 'none';
    }
  });
}

/* ══ Open detail ══ */
function openDetail(id) {
  activeId = id;
  document.querySelectorAll('.rcard').forEach(c => c.classList.remove('active'));
  const card = document.getElementById('rcard_' + id);
  if (card) card.classList.add('active');

  document.getElementById('detailEmpty').style.display = 'none';
  document.getElementById('detailPanel').style.display = 'block';
  document.getElementById('dp_title').textContent = 'Loading…';
  document.getElementById('dp_comment').value = '';

  fetch(`${AJAX}?action=get&id=${id}`).then(r=>r.json()).then(r=>{
    if (r.status !== 'success') return;
    const d = r.data;

    document.getElementById('dp_thumb').src = d.thumbnail ? `../uploads/${d.thumbnail}` : '../assets/img/logo.svg';
    document.getElementById('dp_title').textContent = d.title;
    document.getElementById('dp_instructor').textContent = `${d.first_name} ${d.last_name} · ${d.email_address}`;
    document.getElementById('dp_time').textContent = 'Submitted ' + fmtDate(d.submitted_at);
    document.getElementById('dp_chapters').textContent = d.chapters;
    document.getElementById('dp_lessons').textContent  = d.lessons;
    document.getElementById('dp_free').textContent     = d.free_lessons;
    document.getElementById('dp_price').textContent    = d.price > 0 ? 'TZS ' + Number(d.price).toLocaleString() : 'Free';
    document.getElementById('dp_preview').href = `?view=view_course_details&course_id=${encodeURIComponent(d.course_token)}`;

    const sb = document.getElementById('dp_sbadge');
    sb.className = 'sbadge ' + d.status;
    sb.innerHTML = `<i class="bi bi-circle-fill" style="font-size:.45rem"></i> ${badgeLabel(d.status)}`;

    // Instructor note
    if (d.instructor_note) {
      document.getElementById('dp_note').textContent = d.instructor_note;
      document.getElementById('dp_note_sec').style.display = '';
    } else {
      document.getElementById('dp_note_sec').style.display = 'none';
    }

    // Previous admin comment
    if (d.admin_comment) {
      document.getElementById('dp_prev_comment').textContent = d.admin_comment;
      document.getElementById('dp_prev_sec').style.display = '';
    } else {
      document.getElementById('dp_prev_sec').style.display = 'none';
    }

    // Decided / action state
    const isDone = d.status === 'approved' || d.status === 'rejected';
    const decBanner = document.getElementById('dp_decided_banner');
    const decSec    = document.getElementById('dp_decided_sec');
    const actionSec = document.getElementById('dp_action_sec');
    const commentSec = document.getElementById('dp_comment_sec');

    if (isDone) {
      decBanner.className = `decided-banner ${d.status}`;
      decBanner.innerHTML = d.status === 'approved'
        ? '<i class="bi bi-check-circle-fill"></i>This course is approved and live.'
        : '<i class="bi bi-x-circle-fill"></i>This course has been rejected.';
      decSec.style.display = '';
      // still show comment for re-commenting; hide big action buttons
      document.getElementById('btnApprove').style.display  = 'none';
      document.getElementById('btnRevision').style.display = 'none';
      document.getElementById('btnReject').style.display   = 'none';
      document.getElementById('btnSendComment').style.display = '';
      actionSec.querySelector('.dp-label').textContent = 'Update Comment';
    } else {
      decSec.style.display = 'none';
      ['btnApprove','btnRevision','btnReject','btnSendComment'].forEach(b => document.getElementById(b).style.display = '');
      actionSec.querySelector('.dp-label').textContent = 'Decision';
    }
  });
}

/* ══ Swal theme helpers ══ */
const swalBase = {
  customClass: {
    popup:          'dcm-swal-popup',
    title:          'dcm-swal-title',
    htmlContainer:  'dcm-swal-body',
    confirmButton:  'dcm-swal-confirm',
    cancelButton:   'dcm-swal-cancel',
    actions:        'dcm-swal-actions',
  },
  buttonsStyling: false,
  showClass: { popup:'dcm-swal-in' },
  hideClass: { popup:'dcm-swal-out' },
};

function swalSuccess(title, text) {
  return Swal.fire({ ...swalBase,
    html: `
      <div class="dcm-swal-icon-wrap success"><i class="bi bi-check-circle-fill"></i></div>
      <div class="dcm-swal-ttl">${title}</div>
      ${text ? `<div class="dcm-swal-txt">${text}</div>` : ''}`,
    timer: 2200, timerProgressBar: true, showConfirmButton: false,
  });
}

function swalError(title, text) {
  return Swal.fire({ ...swalBase,
    html: `
      <div class="dcm-swal-icon-wrap error"><i class="bi bi-x-circle-fill"></i></div>
      <div class="dcm-swal-ttl">${title}</div>
      ${text ? `<div class="dcm-swal-txt">${text}</div>` : ''}`,
    showConfirmButton: true,
    confirmButtonText: 'OK',
  });
}

function swalWarn(title, text) {
  return Swal.fire({ ...swalBase,
    html: `
      <div class="dcm-swal-icon-wrap warn"><i class="bi bi-exclamation-triangle-fill"></i></div>
      <div class="dcm-swal-ttl">${title}</div>
      ${text ? `<div class="dcm-swal-txt">${text}</div>` : ''}`,
    showConfirmButton: true,
    confirmButtonText: 'OK',
  });
}

function swalConfirm({ iconClass, iconColor, title, body, confirmText, confirmColor, cancelText }) {
  return Swal.fire({ ...swalBase,
    html: `
      <div class="dcm-swal-icon-wrap" style="background:${iconColor}20;color:${iconColor}"><i class="bi ${iconClass}"></i></div>
      <div class="dcm-swal-ttl">${title}</div>
      ${body ? `<div class="dcm-swal-txt">${body}</div>` : ''}`,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText || 'Cancel',
    reverseButtons: true,
    customClass: { ...swalBase.customClass,
      confirmButton: 'dcm-swal-confirm',
      cancelButton:  'dcm-swal-cancel',
    },
    didOpen: (popup) => {
      const btn = popup.querySelector('.swal2-confirm');
      if (btn) { btn.style.background = confirmColor; btn.style.color = '#fff'; }
    }
  });
}

/* ══ Decide ══ */
function decide(action) {
  const comment = document.getElementById('dp_comment').value.trim();

  if ((action === 'reject' || action === 'revision_needed') && !comment) {
    swalWarn('Comment Required', 'Please provide feedback to the instructor before ' + (action === 'reject' ? 'rejecting' : 'requesting revision') + '.');
    document.getElementById('dp_comment').focus();
    return;
  }

  const cfgMap = {
    approve:         { iconClass:'bi-check-circle-fill', iconColor:'#16a34a', title:'Approve &amp; Publish?',    confirmText:'<i class="bi bi-check-circle-fill me-1"></i>Approve &amp; Publish', confirmColor:'linear-gradient(135deg,#16a34a,#15803d)' },
    reject:          { iconClass:'bi-x-circle-fill',     iconColor:'#dc2626', title:'Reject this course?',       confirmText:'<i class="bi bi-x-circle-fill me-1"></i>Reject',                   confirmColor:'linear-gradient(135deg,#dc2626,#9f1239)' },
    revision_needed: { iconClass:'bi-arrow-repeat',      iconColor:'#d97706', title:'Request Revision?',         confirmText:'<i class="bi bi-arrow-repeat me-1"></i>Request Revision',           confirmColor:'linear-gradient(135deg,#d97706,#b45309)' },
    comment:         { iconClass:'bi-chat-dots-fill',    iconColor:'#6366f1', title:'Send comment to instructor?',confirmText:'<i class="bi bi-send me-1"></i>Send Comment',                      confirmColor:'linear-gradient(135deg,#6366f1,#4f46e5)' },
  };
  const cfg = cfgMap[action];

  const bodyHtml = comment
    ? `<div class="dcm-swal-quote">"${esc(comment.substring(0,140))}${comment.length>140?'…':''}"</div>`
    : '';

  swalConfirm({ ...cfg, body: bodyHtml, cancelText: 'Cancel' }).then(res => {
    if (!res.isConfirmed) return;

    const btnIds = ['btnApprove','btnRevision','btnReject','btnSendComment'];
    btnIds.forEach(b => { const el = document.getElementById(b); if (el) el.disabled = true; });

    fetch(AJAX, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, id: activeId, comment })
    })
    .then(r => r.json())
    .then(r => {
      btnIds.forEach(b => { const el = document.getElementById(b); if (el) el.disabled = false; });
      if (r.status === 'success') {
        swalSuccess('Done!', r.message);
        loadStats();
        loadList(curPage);
        if (action !== 'comment') setTimeout(() => openDetail(activeId), 500);
      } else {
        swalError('Action Failed', r.message);
      }
    })
    .catch(() => {
      btnIds.forEach(b => { const el = document.getElementById(b); if (el) el.disabled = false; });
      swalError('Network Error', 'Could not reach the server. Please try again.');
    });
  });
}

/* ══ Tabs ══ */
document.querySelectorAll('.pill-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.pill-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    if (btn.dataset.mode === 'chapter_del') {
      curMode = 'chapter_del';
      document.getElementById('acrSearch').value = '';
      document.getElementById('reviewPager').style.display = 'none';
      loadChapterDelList('pending');
    } else {
      curMode = 'reviews';
      curFilter = btn.dataset.s;
      curPage = 1;
      document.getElementById('cdPanel').style.display = 'none';
      document.getElementById('detailPanel').style.display = 'none';
      document.getElementById('detailEmpty').style.display = '';
      activeChDelId = null;
      loadList(1);
    }
  });
});

/* ══ Search ══ */
let st;
document.getElementById('acrSearch').addEventListener('input', function(){
  clearTimeout(st);
  if (curMode === 'chapter_del') return; /* search does not apply to chapter del mode */
  st = setTimeout(() => { curSearch = this.value; loadList(1); }, 350);
});

/* ══ Helpers ══ */
function badgeLabel(s){ return {pending:'Pending',approved:'Approved',rejected:'Rejected',revision_needed:'Revision Needed'}[s]||s; }
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(d){ if(!d) return '—'; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}); }
function timeAgo(d){
  if(!d) return '';
  const s = (Date.now()-new Date(d))/1000;
  if(s<60) return 'just now';
  if(s<3600) return Math.floor(s/60)+'m ago';
  if(s<86400) return Math.floor(s/3600)+'h ago';
  return Math.floor(s/86400)+'d ago';
}

/* ══ Chapter Deletion List ══ */
function loadChapterDelList(filter) {
  curChDelFilter = filter || 'pending';
  document.getElementById('reviewList').innerHTML = '<div class="acr-empty py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
  document.getElementById('cdPanel').style.display = 'none';
  document.getElementById('detailPanel').style.display = 'none';
  document.getElementById('detailEmpty').style.display = '';

  fetch(`ajax/ajax_delete_chapter.php?action=list_del&filter=${encodeURIComponent(curChDelFilter)}`)
  .then(r => r.json()).then(r => {
    if (r.status !== 'success') {
      document.getElementById('reviewList').innerHTML = `<div class="acr-empty"><i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>${r.message||'Error'}</div>`;
      return;
    }

    document.getElementById('tc-chdel').textContent = r.pending || 0;

    if (!r.data.length) {
      document.getElementById('reviewList').innerHTML = '<div class="acr-empty"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No deletion requests</div>';
      return;
    }

    document.getElementById('reviewList').innerHTML = r.data.map(req => {
      const thumb = req.course_thumb ? `../uploads/${req.course_thumb}` : '../assets/img/logo.svg';
      return `
      <div class="rcard${activeChDelId==req.id?' active':''}" id="cdcard_${req.id}" onclick="openChapterDelDetail(${req.id})">
        <img src="${thumb}" class="rcard-thumb" onerror="this.src='../assets/img/logo.svg'">
        <div class="flex-grow-1 min-w-0">
          <div class="rcard-title">${esc(req.chapter_title)}</div>
          <div class="rcard-meta">${esc(req.course_title)} · ${esc(req.first_name)} ${esc(req.last_name)}</div>
          <div class="rcard-foot">
            <span class="sbadge ${req.status}"><i class="bi bi-folder-x" style="font-size:.55rem"></i> ${badgeLabel(req.status)}</span>
            <span class="rcard-meta"><i class="bi bi-file-earmark-play me-1"></i>${req.lesson_count} lesson${req.lesson_count==1?'':'s'}</span>
            <span class="rcard-meta ms-auto"><i class="bi bi-clock me-1"></i>${timeAgo(req.requested_at)}</span>
          </div>
        </div>
      </div>`;
    }).join('');
  })
  .catch(() => {
    document.getElementById('reviewList').innerHTML = '<div class="acr-empty"><i class="bi bi-wifi-off fs-2 d-block mb-2"></i>Network error</div>';
  });
}

/* ══ Chapter Deletion Detail ══ */
function openChapterDelDetail(id) {
  activeChDelId = id;
  document.querySelectorAll('.rcard').forEach(c => c.classList.remove('active'));
  const card = document.getElementById('cdcard_' + id);
  if (card) card.classList.add('active');

  document.getElementById('detailEmpty').style.display = 'none';
  document.getElementById('detailPanel').style.display = 'none';
  document.getElementById('cdPanel').style.display = 'block';

  /* reset all sub-sections to a clean "loading" state before the fetch */
  document.getElementById('cd_chapter_title').textContent = 'Loading…';
  document.getElementById('cd_course_name').textContent   = '';
  document.getElementById('cd_time').textContent          = '';
  document.getElementById('cd_comment').value             = '';
  document.getElementById('cd_note_sec').style.display    = 'none';
  document.getElementById('cd_prev_sec').style.display    = 'none';
  document.getElementById('cd_decided_sec').style.display = 'none';
  document.getElementById('cd_action_sec').style.display  = '';   /* show approve/reject while loading */
  document.getElementById('cdBtnApprove').disabled        = false;
  document.getElementById('cdBtnReject').disabled         = false;

  fetch(`ajax/ajax_delete_chapter.php?action=get_del&id=${id}`)
  .then(r => r.json()).then(r => {
    if (r.status !== 'success' || !r.data) return;
    const d = r.data;

    document.getElementById('cd_chapter_title').textContent = d.chapter_title;
    document.getElementById('cd_course_name').textContent   = `${d.first_name} ${d.last_name} · ${d.course_title}`;
    document.getElementById('cd_time').textContent          = 'Requested ' + fmtDate(d.requested_at);
    document.getElementById('cd_lessons').textContent       = d.lesson_count;
    document.getElementById('cd_instructor').textContent    = d.first_name + ' ' + d.last_name;
    const cst = d.course_status || '';
    document.getElementById('cd_course_status').textContent = cst ? cst.charAt(0).toUpperCase() + cst.slice(1) : '—';

    const sb = document.getElementById('cd_sbadge');
    sb.className = 'sbadge ' + d.status;
    const sbIcons = { pending:'bi-hourglass-split', approved:'bi-check-circle-fill', rejected:'bi-x-circle-fill' };
    sb.innerHTML = `<i class="bi ${sbIcons[d.status]||'bi-circle'}" style="font-size:.45rem"></i> ${badgeLabel(d.status)}`;

    if (d.instructor_note) {
      document.getElementById('cd_note').textContent = d.instructor_note;
      document.getElementById('cd_note_sec').style.display = '';
    } else {
      document.getElementById('cd_note_sec').style.display = 'none';
    }

    if (d.admin_comment) {
      document.getElementById('cd_prev_comment').textContent = d.admin_comment;
      document.getElementById('cd_prev_sec').style.display = '';
    } else {
      document.getElementById('cd_prev_sec').style.display = 'none';
    }

    const isPending = d.status === 'pending';
    const decBanner = document.getElementById('cd_decided_banner');
    const decSec    = document.getElementById('cd_decided_sec');
    const actionSec = document.getElementById('cd_action_sec');

    if (!isPending) {
      decBanner.className = 'decided-banner ' + d.status;
      decBanner.innerHTML = d.status === 'approved'
        ? '<i class="bi bi-check-circle-fill me-1"></i>Chapter and all its lessons were deleted.'
        : '<i class="bi bi-x-circle-fill me-1"></i>This deletion request was rejected.';
      decSec.style.display = '';
      actionSec.style.display = 'none';
    } else {
      decSec.style.display = 'none';
      actionSec.style.display = '';
    }
  });
}

/* ══ Decide Chapter Deletion ══ */
function decideChDel(action) {
  const comment = document.getElementById('cd_comment').value.trim();

  if (action === 'reject_del' && !comment) {
    swalWarn('Comment Required', 'Please provide a reason for rejecting the deletion request.');
    document.getElementById('cd_comment').focus();
    return;
  }

  const cfgMap = {
    approve_del: {
      iconClass: 'bi-trash3-fill', iconColor: '#dc2626',
      title: 'Approve &amp; Delete Chapter?',
      body: 'This will permanently delete the chapter and all its lessons. This action cannot be undone.',
      confirmText: '<i class="bi bi-trash3-fill me-1"></i>Delete Permanently',
      confirmColor: 'linear-gradient(135deg,#dc2626,#9f1239)'
    },
    reject_del: {
      iconClass: 'bi-x-circle-fill', iconColor: '#d97706',
      title: 'Reject Deletion Request?',
      body: null,
      confirmText: '<i class="bi bi-x-circle-fill me-1"></i>Reject Request',
      confirmColor: 'linear-gradient(135deg,#d97706,#b45309)'
    }
  };
  const cfg = cfgMap[action];
  const bodyHtml = (cfg.body ? `<div class="dcm-swal-txt">${cfg.body}</div>` : '') +
    (comment ? `<div class="dcm-swal-quote">"${esc(comment.substring(0,140))}${comment.length>140?'…':''}"</div>` : '');

  swalConfirm({ ...cfg, body: bodyHtml, cancelText: 'Cancel' }).then(res => {
    if (!res.isConfirmed) return;

    document.getElementById('cdBtnApprove').disabled = true;
    document.getElementById('cdBtnReject').disabled  = true;

    fetch('ajax/ajax_delete_chapter.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, id: activeChDelId, comment })
    })
    .then(r => r.json())
    .then(r => {
      document.getElementById('cdBtnApprove').disabled = false;
      document.getElementById('cdBtnReject').disabled  = false;
      if (r.status === 'success') {
        swalSuccess('Done!', r.message);
        loadChapterDelList(curChDelFilter);
      } else {
        swalError('Action Failed', r.message);
      }
    })
    .catch(() => {
      document.getElementById('cdBtnApprove').disabled = false;
      document.getElementById('cdBtnReject').disabled  = false;
      swalError('Network Error', 'Could not reach the server. Please try again.');
    });
  });
}

/* ══ Init ══ */
loadStats();

/* auto-select tab from URL param (e.g. notifications link ?tab=chapter_del) */
(function(){
  const tab = new URLSearchParams(window.location.search).get('tab');
  if (tab === 'chapter_del') {
    const btn = document.querySelector('.pill-tab.t-chapterdel');
    if (btn) { btn.click(); return; }
  }
  loadList(1);
})();

/* pre-load chapter del pending count (only when not already loading the tab) */
fetch('ajax/ajax_delete_chapter.php?action=list_del&filter=pending')
  .then(r => r.json()).then(r => {
    if (r.status === 'success') document.getElementById('tc-chdel').textContent = r.pending || 0;
  });
</script>
