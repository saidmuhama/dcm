<?php if ($user_role != 5) { include('403.php'); return; } ?>

<style>
.acr-hero {
    background: linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);
    padding: 2rem 0 3.5rem; position: relative; overflow: hidden;
}
.acr-hero::before {
    content:''; position:absolute; inset:0; pointer-events:none;
    background: radial-gradient(circle at 10% 60%,rgba(99,102,241,.18) 0%,transparent 55%),
                radial-gradient(circle at 85% 20%,rgba(168,85,247,.13) 0%,transparent 50%);
}
.acr-hero::after {
    content:''; position:absolute; inset:0; pointer-events:none;
    background-image:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='20' cy='20' r='1.5' fill='%23fff' fill-opacity='.03'/%3E%3C/svg%3E");
}
.acr-canvas { max-width:1400px; margin:-2rem auto 2rem; padding:0 1rem; position:relative; z-index:10; }
.stat-card { background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.07); border:1px solid rgba(0,0,0,.05); padding:1.2rem 1.4rem; }
.stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.acr-tabs .nav-link { font-size:.82rem; font-weight:600; color:#64748b; border-radius:9px; padding:.4rem .9rem; }
.acr-tabs .nav-link.active { background:#6366f1; color:#fff; }
.review-card { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); border:1px solid #f1f5f9; overflow:hidden; margin-bottom:.85rem; transition:box-shadow .18s; }
.review-card:hover { box-shadow:0 6px 24px rgba(99,102,241,.12); }
.review-card-body { padding:1rem 1.2rem; }
.r-thumb { width:64px; height:48px; border-radius:8px; object-fit:cover; flex-shrink:0; }
.r-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem; border-radius:100px; font-size:.7rem; font-weight:700; }
.r-badge.pending  { background:#fef9c3; color:#92400e; border:1px solid #fde68a; }
.r-badge.approved { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
.r-badge.rejected { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; }
.r-badge.revision_needed { background:#fce7f3; color:#9d174d; border:1px solid #fbcfe8; }
.detail-panel { background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.1); border:1px solid #e2e8f0; }
.dp-header { background:linear-gradient(135deg,#1a1a2e,#16213e); border-radius:16px 16px 0 0; padding:1.4rem 1.6rem; }
.dp-section { padding:1.2rem 1.6rem; border-bottom:1px solid #f1f5f9; }
.dp-section:last-child { border-bottom:none; }
.dp-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:.4rem; }
.action-btn { border:none; border-radius:10px; padding:.55rem 1.1rem; font-size:.82rem; font-weight:700; cursor:pointer; transition:filter .15s, box-shadow .15s; display:inline-flex; align-items:center; gap:.35rem; }
.action-btn:disabled { opacity:.55; cursor:not-allowed; }
.action-btn.approve { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; box-shadow:0 4px 14px rgba(22,163,74,.28); }
.action-btn.approve:hover:not(:disabled) { filter:brightness(1.08); }
.action-btn.reject  { background:linear-gradient(135deg,#dc2626,#9f1239); color:#fff; box-shadow:0 4px 14px rgba(220,38,38,.22); }
.action-btn.reject:hover:not(:disabled)  { filter:brightness(1.08); }
.action-btn.revision { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow:0 4px 14px rgba(245,158,11,.25); }
.action-btn.revision:hover:not(:disabled) { filter:brightness(1.08); }
.action-btn.send-comment { background:#eef2ff; color:#6366f1; }
.action-btn.send-comment:hover:not(:disabled) { background:#e0e7ff; }
.empty-state { text-align:center; padding:4rem 2rem; color:#94a3b8; }
.history-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; margin-top:.2rem; }
</style>

<!-- HERO -->
<div class="acr-hero">
    <div class="container-xl position-relative" style="z-index:2">
        <nav class="mb-2">
            <ol class="breadcrumb mb-0" style="font-size:.76rem">
                <li class="breadcrumb-item"><a href="?view=admin_dashboard" class="text-white-50 text-decoration-none">Admin</a></li>
                <li class="breadcrumb-item active" style="color:rgba(255,255,255,.5)">Course Reviews</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;background:rgba(255,255,255,.1);font-size:1.4rem;color:#a5b4fc">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <h4 class="text-white fw-bold mb-0">Course Review Queue</h4>
                <p class="text-white-50 small mb-0">Review, approve, or request changes for instructor-submitted courses</p>
            </div>
            <div class="ms-auto">
                <span id="heroQueueCount" class="badge" style="background:rgba(245,158,11,.25);color:#fbbf24;font-size:.82rem;border-radius:20px;padding:.45rem .9rem;border:1px solid rgba(245,158,11,.35)">
                    <i class="bi bi-hourglass-split me-1"></i><span id="heroQueueNum">—</span> Pending
                </span>
            </div>
        </div>
    </div>
</div>

<div class="acr-canvas">

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef9c3"><i class="bi bi-hourglass-split" style="color:#92400e"></i></div>
                <div><div class="fw-bold fs-5" id="sPending">—</div><div class="text-muted small">Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7"><i class="bi bi-check-circle-fill" style="color:#15803d"></i></div>
                <div><div class="fw-bold fs-5" id="sApprovedToday">—</div><div class="text-muted small">Approved Today</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fee2e2"><i class="bi bi-x-circle-fill" style="color:#b91c1c"></i></div>
                <div><div class="fw-bold fs-5" id="sRejected">—</div><div class="text-muted small">Rejected</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#eef2ff"><i class="bi bi-collection-play-fill" style="color:#6366f1"></i></div>
                <div><div class="fw-bold fs-5" id="sTotal">—</div><div class="text-muted small">Total Requests</div></div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <!-- LEFT: list -->
        <div class="col-lg-5">

            <!-- Tabs + Search -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                <ul class="nav acr-tabs gap-1" id="statusTabs">
                    <li class="nav-item"><button class="nav-link active" data-s="">All</button></li>
                    <li class="nav-item"><button class="nav-link" data-s="pending">Pending</button></li>
                    <li class="nav-item"><button class="nav-link" data-s="approved">Approved</button></li>
                    <li class="nav-item"><button class="nav-link" data-s="rejected">Rejected</button></li>
                    <li class="nav-item"><button class="nav-link" data-s="revision_needed">Revision</button></li>
                </ul>
                <div class="input-group input-group-sm" style="width:200px">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="acrSearch" class="form-control" placeholder="Search…">
                </div>
            </div>

            <div id="reviewList">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div id="reviewPager" class="d-flex justify-content-center gap-1 mt-3"></div>
        </div>

        <!-- RIGHT: detail panel -->
        <div class="col-lg-7">
            <div id="detailPanel" class="detail-panel" style="display:none;position:sticky;top:80px">

                <div class="dp-header">
                    <div class="d-flex align-items-start gap-3">
                        <img id="dp_thumb" src="" class="rounded-3 object-fit-cover" style="width:72px;height:54px;flex-shrink:0" onerror="this.src='../assets/img/logo.svg'">
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-white fw-bold text-truncate" id="dp_title" style="font-size:1rem"></div>
                            <div class="text-white-50 small mt-1" id="dp_instructor"></div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span id="dp_status_badge" class="r-badge"></span>
                                <span class="text-white-50 small" id="dp_submitted"></span>
                            </div>
                        </div>
                        <a id="dp_preview_link" href="#" target="_blank"
                           class="btn btn-sm btn-outline-light" style="border-radius:8px;font-size:.75rem;flex-shrink:0">
                            <i class="bi bi-eye me-1"></i>Preview
                        </a>
                    </div>
                </div>

                <!-- Course stats -->
                <div class="dp-section">
                    <div class="dp-label">Course Info</div>
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="text-center">
                            <div class="fw-bold text-indigo-600" id="dp_chapters" style="color:#6366f1;font-size:1.1rem"></div>
                            <div class="text-muted small">Chapters</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" id="dp_lessons" style="color:#0891b2;font-size:1.1rem"></div>
                            <div class="text-muted small">Lessons</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" id="dp_free_lessons" style="color:#16a34a;font-size:1.1rem"></div>
                            <div class="text-muted small">Free Preview</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" id="dp_price" style="color:#d97706;font-size:1.1rem"></div>
                            <div class="text-muted small">Price</div>
                        </div>
                    </div>
                </div>

                <!-- Instructor note -->
                <div class="dp-section" id="dp_note_section" style="display:none">
                    <div class="dp-label">Instructor's Message</div>
                    <p class="mb-0 small text-secondary" id="dp_instructor_note" style="white-space:pre-wrap"></p>
                </div>

                <!-- Admin comment -->
                <div class="dp-section">
                    <div class="dp-label">Admin Comment / Feedback</div>
                    <textarea id="dp_comment" class="form-control form-control-sm" rows="4"
                              placeholder="Leave feedback for the instructor (optional for approval, required for rejection or revision request)…"
                              style="border-radius:10px;resize:none"></textarea>
                </div>

                <!-- Previous admin comment -->
                <div class="dp-section" id="dp_prev_comment_section" style="display:none">
                    <div class="dp-label">Previous Comment</div>
                    <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                        <p class="mb-0 small text-secondary" id="dp_prev_comment" style="white-space:pre-wrap"></p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="dp-section" id="dp_actions">
                    <div class="dp-label mb-3">Decision</div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="action-btn approve" id="btnApprove" onclick="decideReview('approve')">
                            <i class="bi bi-check-circle-fill"></i> Approve & Publish
                        </button>
                        <button class="action-btn revision" id="btnRevision" onclick="decideReview('revision_needed')">
                            <i class="bi bi-arrow-repeat"></i> Request Revision
                        </button>
                        <button class="action-btn reject" id="btnReject" onclick="decideReview('reject')">
                            <i class="bi bi-x-circle-fill"></i> Reject
                        </button>
                        <button class="action-btn send-comment" id="btnComment" onclick="decideReview('comment')">
                            <i class="bi bi-chat-dots"></i> Send Comment Only
                        </button>
                    </div>
                    <div id="dp_decided_note" class="mt-3 p-3 rounded-3 small d-none"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d"></div>
                </div>

            </div>

            <!-- Empty state -->
            <div id="detailEmpty" class="detail-panel p-5 text-center" style="position:sticky;top:80px">
                <div style="font-size:3rem;color:#e2e8f0"><i class="bi bi-shield-check"></i></div>
                <p class="text-muted mt-3 mb-0">Select a review request from the left to view details and take action.</p>
            </div>
        </div>
    </div>
</div>

<script>
const REVIEW_AJAX = 'ajax/ajax_course_review.php';
let currentPage = 1, currentFilter = '', currentSearch = '', activeReviewId = null;

/* ── Load stats ── */
function loadStats() {
    fetch(`${REVIEW_AJAX}?action=stats`)
    .then(r => r.json()).then(r => {
        if (r.status !== 'success') return;
        document.getElementById('sPending').textContent        = r.pending;
        document.getElementById('sApprovedToday').textContent  = r.approved_today;
        document.getElementById('sRejected').textContent       = r.rejected;
        document.getElementById('sTotal').textContent          = r.total;
        document.getElementById('heroQueueNum').textContent    = r.pending;
    });
}

/* ── Load list ── */
function loadList(page) {
    page = page || 1; currentPage = page;
    document.getElementById('reviewList').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

    const url = `${REVIEW_AJAX}?action=list&filter_status=${encodeURIComponent(currentFilter)}&search=${encodeURIComponent(currentSearch)}&page=${page}`;
    fetch(url).then(r => r.json()).then(r => {
        if (r.status !== 'success') return;
        if (!r.data.length) {
            document.getElementById('reviewList').innerHTML = `<div class="empty-state"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No review requests found</div>`;
            document.getElementById('reviewPager').innerHTML = '';
            return;
        }

        document.getElementById('reviewList').innerHTML = r.data.map(req => `
            <div class="review-card" onclick="openDetail(${req.id})" style="cursor:pointer" id="rcard_${req.id}">
                <div class="review-card-body d-flex align-items-start gap-3">
                    <img src="${req.thumbnail ? '../uploads/'+req.thumbnail : '../assets/img/logo.svg'}"
                         class="r-thumb" onerror="this.src='../assets/img/logo.svg'">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate" style="font-size:.88rem">${esc(req.title)}</div>
                        <div class="text-muted small">${esc(req.first_name)} ${esc(req.last_name)}</div>
                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                            <span class="r-badge ${req.status}">${badgeLabel(req.status)}</span>
                            <span class="text-muted" style="font-size:.7rem"><i class="bi bi-collection me-1"></i>${req.chapters} ch · ${req.lessons} lessons</span>
                            <span class="text-muted" style="font-size:.7rem"><i class="bi bi-clock me-1"></i>${timeAgo(req.submitted_at)}</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted mt-1" style="font-size:.75rem;flex-shrink:0"></i>
                </div>
            </div>
        `).join('');

        // Pager
        const pages = Math.ceil(r.total / r.per);
        let pg = '';
        for (let p = 1; p <= pages; p++) {
            pg += `<button class="btn btn-sm ${p===page?'btn-primary':'btn-outline-secondary'}" onclick="loadList(${p})">${p}</button>`;
        }
        document.getElementById('reviewPager').innerHTML = pg;
    });
}

/* ── Open detail panel ── */
function openDetail(id) {
    activeReviewId = id;
    document.querySelectorAll('.review-card').forEach(c => c.style.background = '');
    const card = document.getElementById('rcard_' + id);
    if (card) card.style.background = '#f5f7ff';

    document.getElementById('detailPanel').style.display = 'block';
    document.getElementById('detailEmpty').style.display = 'none';
    document.getElementById('dp_title').textContent = 'Loading…';
    document.getElementById('dp_comment').value = '';

    fetch(`${REVIEW_AJAX}?action=get&id=${id}`)
    .then(r => r.json()).then(r => {
        if (r.status !== 'success') return;
        const d = r.data;

        const thumb = d.thumbnail ? `../uploads/${d.thumbnail}` : '../assets/img/logo.svg';
        document.getElementById('dp_thumb').src = thumb;
        document.getElementById('dp_title').textContent = d.title;
        document.getElementById('dp_instructor').textContent = `${d.first_name} ${d.last_name} · ${d.email_address}`;
        document.getElementById('dp_submitted').textContent = 'Submitted: ' + fmtDate(d.submitted_at);
        document.getElementById('dp_chapters').textContent = d.chapters;
        document.getElementById('dp_lessons').textContent = d.lessons;
        document.getElementById('dp_free_lessons').textContent = d.free_lessons;
        document.getElementById('dp_price').textContent = d.price > 0 ? 'TZS ' + Number(d.price).toLocaleString() : 'Free';
        document.getElementById('dp_preview_link').href = `?view=view_course_details&course_id=${d.course_id}`;

        const sb = document.getElementById('dp_status_badge');
        sb.textContent = badgeLabel(d.status);
        sb.className = 'r-badge ' + d.status;

        if (d.instructor_note) {
            document.getElementById('dp_instructor_note').textContent = d.instructor_note;
            document.getElementById('dp_note_section').style.display = '';
        } else {
            document.getElementById('dp_note_section').style.display = 'none';
        }

        if (d.admin_comment) {
            document.getElementById('dp_prev_comment').textContent = d.admin_comment;
            document.getElementById('dp_prev_comment_section').style.display = '';
        } else {
            document.getElementById('dp_prev_comment_section').style.display = 'none';
        }

        // hide action buttons if already decided
        const decided = d.status !== 'pending' && d.status !== 'revision_needed';
        document.getElementById('dp_decided_note').classList.toggle('d-none', !decided);
        if (decided) {
            const msgs = { approved:'This course has been approved and is now live.', rejected:'This course has been rejected.' };
            const el = document.getElementById('dp_decided_note');
            el.textContent = msgs[d.status] || 'Review completed.';
            el.style.background = d.status === 'approved' ? '#f0fdf4' : '#fff1f2';
            el.style.borderColor = d.status === 'approved' ? '#bbf7d0' : '#fecaca';
            el.style.color       = d.status === 'approved' ? '#15803d' : '#b91c1c';
            ['btnApprove','btnRevision','btnReject'].forEach(b => document.getElementById(b).style.display = 'none');
        } else {
            ['btnApprove','btnRevision','btnReject'].forEach(b => document.getElementById(b).style.display = '');
        }
    });
}

/* ── Decision ── */
function decideReview(action) {
    const comment = document.getElementById('dp_comment').value.trim();
    if ((action === 'reject' || action === 'revision_needed') && !comment) {
        Swal.fire('Comment Required', 'Please provide feedback to the instructor when rejecting or requesting revision.', 'warning');
        return;
    }

    const labels = { approve:'Approve & Publish', reject:'Reject', revision_needed:'Request Revision', comment:'Send Comment' };
    const colors = { approve:'#16a34a', reject:'#dc2626', revision_needed:'#d97706', comment:'#6366f1' };

    Swal.fire({
        title: labels[action] + '?',
        html: comment ? `<p class="text-muted small">"${esc(comment.substring(0,100))}${comment.length>100?'…':''}"</p>` : '',
        icon: action === 'approve' ? 'success' : action === 'reject' ? 'error' : 'warning',
        showCancelButton: true,
        confirmButtonText: labels[action],
        confirmButtonColor: colors[action],
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(res => {
        if (!res.isConfirmed) return;

        const btns = ['btnApprove','btnRevision','btnReject','btnComment'];
        btns.forEach(b => { const el=document.getElementById(b); if(el){el.disabled=true;} });

        fetch(REVIEW_AJAX, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action, id: activeReviewId, comment })
        }).then(r => r.json()).then(r => {
            btns.forEach(b => { const el=document.getElementById(b); if(el){el.disabled=false;} });
            if (r.status === 'success') {
                Swal.fire({ icon:'success', title:'Done!', text:r.message, timer:1800, showConfirmButton:false });
                loadStats();
                loadList(currentPage);
                if (action !== 'comment') openDetail(activeReviewId);
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        });
    });
}

/* ── Helpers ── */
function badgeLabel(s) {
    return { pending:'Pending', approved:'Approved', rejected:'Rejected', revision_needed:'Revision Needed' }[s] || s;
}
function esc(str) { return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(d) { return d ? new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—'; }
function timeAgo(d) {
    if (!d) return '';
    const diff = (Date.now() - new Date(d)) / 1000;
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

/* ── Tabs ── */
document.querySelectorAll('#statusTabs .nav-link').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#statusTabs .nav-link').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.s;
        currentPage = 1;
        loadList(1);
    });
});

/* ── Search ── */
let searchTimer;
document.getElementById('acrSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentSearch = this.value; loadList(1); }, 350);
});

/* ── Init ── */
loadStats();
loadList(1);
</script>
