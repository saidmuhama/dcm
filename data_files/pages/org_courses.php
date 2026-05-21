<?php
// Org Admin — Course Catalog — role 4 only
if (($user_role ?? 0) != 4) { include('403.php'); return; }

$me = $_SESSION['usr_code'];
$orgRow = $db->query("
    SELECT o.org_code, o.org_name
    FROM tbl_organizations o
    INNER JOIN tbl_org_members m ON m.org_code = o.org_code
    WHERE m.usr_code = '$me' AND m.org_role = 'admin' AND m.status = 'active'
      AND o.deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();

if (!$orgRow) {
    echo '<div class="alert alert-warning m-4">No organization linked to your account.</div>';
    return;
}
?>
<style>
/* ── Org Courses ─────────────────────────────────────────── oc-* ── */
.oc-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#312e81 100%);padding:2rem 1.5rem 3.5rem;position:relative;overflow:hidden}
.oc-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.oc-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:rgba(255,255,255,.55);margin-bottom:.9rem}
.oc-breadcrumb a{color:rgba(255,255,255,.55);text-decoration:none}.oc-breadcrumb a:hover{color:#fff}
.oc-breadcrumb .sep{opacity:.4}
.oc-hero-title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.oc-hero-sub{font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:1.2rem}
.oc-stat-pills{display:flex;flex-wrap:wrap;gap:.6rem}
.oc-stat-pill{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:2rem;padding:.35rem .85rem;color:#fff;font-size:.8rem;display:flex;align-items:center;gap:.45rem}
.oc-stat-pill i{opacity:.7}
/* body panel */
.oc-body{background:#f8fafc;margin-top:-1.8rem;border-radius:1.2rem 1.2rem 0 0;padding:1.5rem;min-height:60vh;position:relative;z-index:1}
/* filter bar */
.oc-filter-bar{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-bottom:1.4rem}
.oc-search{flex:1;min-width:200px;font-size:.83rem;border-radius:.5rem;border:1px solid #e2e8f0;padding:.4rem .75rem;background:#fff;outline:none}
.oc-search:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.oc-filter-bar select{font-size:.82rem;border-radius:.5rem;border:1px solid #e2e8f0;padding:.38rem .7rem;background:#fff;outline:none;min-width:140px}
.oc-filter-bar select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.oc-tab-btns{display:flex;gap:0;background:#f1f5f9;border-radius:.5rem;padding:3px}
.oc-tab-btn{border:none;background:transparent;border-radius:.4rem;padding:.32rem .85rem;font-size:.8rem;font-weight:600;color:#64748b;cursor:pointer;transition:all .15s}
.oc-tab-btn.active{background:#fff;color:#6366f1;box-shadow:0 1px 4px rgba(0,0,0,.1)}
/* grid */
.oc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1rem}
/* card */
.oc-card{background:#fff;border-radius:1rem;border:1px solid #e8ecf3;overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .15s,transform .15s}
.oc-card:hover{box-shadow:0 8px 24px rgba(99,102,241,.12);transform:translateY(-2px)}
.oc-card-img{position:relative;aspect-ratio:16/9;overflow:hidden;background:#e2e8f0;flex-shrink:0}
.oc-card-img img{width:100%;height:100%;object-fit:cover;display:block}
.oc-card-img .oc-img-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#e0e7ff,#ede9fe);color:#6366f1;font-size:2.5rem}
.oc-sub-badge{position:absolute;top:.55rem;left:.55rem;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:.68rem;font-weight:700;padding:.22rem .6rem;border-radius:2rem;display:flex;align-items:center;gap:.3rem}
.oc-card-body{padding:.9rem 1rem .7rem;flex:1;display:flex;flex-direction:column}
.oc-card-cat{font-size:.7rem;font-weight:600;color:#6366f1;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem}
.oc-card-title{font-size:.9rem;font-weight:700;color:#1e293b;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:.4rem}
.oc-card-instructor{font-size:.75rem;color:#64748b;margin-bottom:.6rem;display:flex;align-items:center;gap:.3rem}
.oc-card-meta{display:flex;gap:.8rem;margin-bottom:.7rem}
.oc-card-meta-item{display:flex;align-items:center;gap:.25rem;font-size:.74rem;color:#94a3b8}
.oc-card-price{font-size:1rem;font-weight:800;color:#1e293b;display:flex;align-items:baseline;gap:.4rem}
.oc-card-price .oc-free{color:#16a34a;font-size:.85rem}
.oc-card-price .oc-orig{font-size:.76rem;color:#94a3b8;text-decoration:line-through;font-weight:400}
.oc-card-enrolled{font-size:.74rem;color:#059669;display:flex;align-items:center;gap:.25rem;margin-top:.25rem}
.oc-card-foot{padding:.6rem 1rem .85rem;border-top:1px solid #f1f5f9;display:flex;gap:.5rem}
.oc-btn-sub{flex:1;border:none;border-radius:.55rem;padding:.45rem;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:.35rem}
.oc-btn-sub.subscribe{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.oc-btn-sub.subscribe:hover{opacity:.88}
.oc-btn-sub.unsubscribe{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
.oc-btn-sub.unsubscribe:hover{background:#fee2e2}
.oc-btn-detail{border:1px solid #e2e8f0;background:#fff;border-radius:.55rem;padding:.45rem .7rem;font-size:.82rem;cursor:pointer;color:#475569;display:flex;align-items:center;gap:.3rem;transition:all .15s}
.oc-btn-detail:hover{border-color:#6366f1;color:#6366f1}
/* skeleton */
.oc-skel{border-radius:.4rem;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:ocSkel 1.4s infinite;display:block}
@keyframes ocSkel{0%{background-position:200% 0}100%{background-position:-200% 0}}
/* empty */
.oc-empty{text-align:center;padding:4rem 1rem;color:#94a3b8;grid-column:1/-1}
.oc-empty i{font-size:3.5rem;display:block;margin-bottom:1rem;opacity:.35}
/* detail modal */
.oc-modal-thumb{width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:.6rem;margin-bottom:1rem;background:#e2e8f0}
.oc-modal-thumb-placeholder{width:100%;aspect-ratio:16/9;border-radius:.6rem;margin-bottom:1rem;background:linear-gradient(135deg,#e0e7ff,#ede9fe);display:flex;align-items:center;justify-content:center;font-size:4rem;color:#6366f1}
.oc-detail-meta{display:flex;flex-wrap:wrap;gap:.5rem;margin:.7rem 0}
.oc-detail-pill{background:#f1f5f9;border-radius:2rem;padding:.28rem .75rem;font-size:.78rem;color:#374151;display:flex;align-items:center;gap:.3rem}
</style>

<!-- Hero -->
<div class="oc-hero">
    <div class="oc-breadcrumb">
        <a href="?view=org_dashboard"><i class="bi bi-house-fill"></i></a>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($orgRow['org_name']) ?></span>
        <span class="sep">/</span>
        <span style="color:#fff">Course Catalog</span>
    </div>
    <div class="oc-hero-title"><i class="bi bi-collection-play-fill me-2" style="color:#a5b4fc"></i>Course Catalog</div>
    <div class="oc-hero-sub">Browse and subscribe to courses for your organization members</div>
    <div class="oc-stat-pills">
        <div class="oc-stat-pill"><i class="bi bi-journals"></i><span id="ocPillTotal">—</span> Available Courses</div>
        <div class="oc-stat-pill"><i class="bi bi-check2-circle"></i><span id="ocPillSubbed">—</span> Subscribed</div>
        <div class="oc-stat-pill"><i class="bi bi-people-fill"></i><span id="ocPillLearners">—</span> Members Learning</div>
    </div>
</div>

<!-- Body -->
<div class="oc-body">

    <!-- Filter bar -->
    <div class="oc-filter-bar">
        <input type="text" class="oc-search" id="ocSearch" placeholder="Search courses…" oninput="ocDebounce()">
        <select id="ocCatFilter" onchange="ocLoad()">
            <option value="">All Categories</option>
        </select>
        <div class="oc-tab-btns">
            <button class="oc-tab-btn active" id="ocTabAll" onclick="ocSetTab('all', this)">All</button>
            <button class="oc-tab-btn" id="ocTabSub" onclick="ocSetTab('subscribed', this)">Subscribed</button>
        </div>
    </div>

    <!-- Grid -->
    <div class="oc-grid" id="ocGrid">
        <?php for($i=0;$i<8;$i++): ?>
        <div class="oc-card" style="pointer-events:none">
            <div class="oc-skel" style="aspect-ratio:16/9;border-radius:0"></div>
            <div class="oc-card-body">
                <div class="oc-skel" style="width:60px;height:.65rem;margin-bottom:.4rem"></div>
                <div class="oc-skel" style="width:90%;height:.85rem;margin-bottom:.3rem"></div>
                <div class="oc-skel" style="width:65%;height:.85rem;margin-bottom:.6rem"></div>
                <div class="oc-skel" style="width:40%;height:.7rem;margin-bottom:.7rem"></div>
                <div style="display:flex;gap:.6rem">
                    <div class="oc-skel" style="width:50px;height:.7rem"></div>
                    <div class="oc-skel" style="width:50px;height:.7rem"></div>
                </div>
            </div>
            <div class="oc-card-foot">
                <div class="oc-skel" style="flex:1;height:32px;border-radius:.55rem"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Course Detail Modal -->
<div class="modal fade" id="ocDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;overflow:hidden">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h6 class="modal-title fw-bold fs-6" id="ocDetailTitle">Course Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-0" id="ocDetailBody"></div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-sm text-white fw-semibold" id="ocDetailSubBtn"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;padding:.42rem 1.2rem;border-radius:.5rem;min-width:120px">
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const OC_AJAX = '../data_files/ajax/ajax_org_admin.php';
let ocCourses    = [];
let ocTab        = 'all';
let ocDebTimer   = null;
let ocDetailCourse = null;

async function ocInit() {
    const cr = await fetch(`${OC_AJAX}?action=list_categories`).then(x=>x.json()).catch(()=>({}));
    const sel = document.getElementById('ocCatFilter');
    (cr.categories ?? []).forEach(c => sel.appendChild(new Option(c.title, c.id)));
    ocLoad();
}

async function ocLoad() {
    const q    = document.getElementById('ocSearch').value.trim();
    const cat  = document.getElementById('ocCatFilter').value;
    const sub  = ocTab === 'subscribed' ? 1 : 0;
    const params = new URLSearchParams({ action:'browse_courses', q, cat, subscribed: sub });
    const r = await fetch(`${OC_AJAX}?${params}`).then(x=>x.json()).catch(()=>({}));
    ocCourses = r.courses ?? [];
    ocUpdatePills();
    ocRender(ocCourses);
}

function ocUpdatePills() {
    const all    = ocCourses.length;
    const subbed = ocCourses.filter(c => c.is_subscribed == 1).length;
    const learners = ocCourses.reduce((s,c) => s + (parseInt(c.enrolled_members)||0), 0);
    // Only update pills on first full load (all tab, no filter)
    if (ocTab === 'all' && !document.getElementById('ocSearch').value && !document.getElementById('ocCatFilter').value) {
        document.getElementById('ocPillTotal').textContent   = all;
        document.getElementById('ocPillSubbed').textContent  = subbed;
        document.getElementById('ocPillLearners').textContent = learners;
    }
}

function ocRender(courses) {
    const grid = document.getElementById('ocGrid');
    if (!courses.length) {
        grid.innerHTML = `<div class="oc-empty">
            <i class="bi bi-collection-play"></i>
            <div style="font-size:1rem;font-weight:600;color:#475569;margin-bottom:.4rem">
                ${ocTab==='subscribed' ? 'No subscribed courses yet' : 'No courses found'}
            </div>
            <div style="font-size:.85rem">${ocTab==='subscribed' ? 'Browse all courses and subscribe to make them available for your members.' : 'Try a different search or category.'}</div>
        </div>`;
        return;
    }
    grid.innerHTML = courses.map(c => ocCardHtml(c)).join('');
}

function ocCardHtml(c) {
    const isSub   = c.is_subscribed == 1;
    const hasCert = false;
    const price   = parseFloat(c.price) || 0;
    const disc    = parseFloat(c.discount) || 0;
    const finalP  = disc > 0 ? price * (1 - disc / 100) : price;
    const priceHtml = price === 0
        ? `<span class="oc-free">Free</span>`
        : disc > 0
            ? `<span>TZS ${finalP.toLocaleString()}</span><span class="oc-orig">TZS ${price.toLocaleString()}</span>`
            : `<span>TZS ${price.toLocaleString()}</span>`;

    const imgHtml = c.thumbnail
        ? `<img src="../data_files/${ocEsc(c.thumbnail)}" alt="" loading="lazy" onerror="this.parentNode.innerHTML='<div class=oc-img-placeholder><i class=bi-collection-play></i></div>'">`
        : `<div class="oc-img-placeholder"><i class="bi bi-collection-play"></i></div>`;

    return `<div class="oc-card">
        <div class="oc-card-img">
            ${imgHtml}
            ${isSub ? `<span class="oc-sub-badge"><i class="bi bi-check2"></i>Subscribed</span>` : ''}
        </div>
        <div class="oc-card-body">
            ${c.category_name ? `<div class="oc-card-cat">${ocEsc(c.category_name)}</div>` : ''}
            <div class="oc-card-title">${ocEsc(c.title)}</div>
            <div class="oc-card-instructor"><i class="bi bi-person-circle"></i>${ocEsc(c.instructor_name||'Unknown Instructor')}</div>
            <div class="oc-card-meta">
                <div class="oc-card-meta-item"><i class="bi bi-collection"></i>${c.chapters||0} chapters</div>
                <div class="oc-card-meta-item"><i class="bi bi-play-circle"></i>${c.lessons||0} lessons</div>
            </div>
            <div class="oc-card-price">${priceHtml}</div>
            ${isSub && c.enrolled_members > 0 ? `<div class="oc-card-enrolled"><i class="bi bi-people-fill"></i>${c.enrolled_members} member${c.enrolled_members!=1?'s':''} enrolled</div>` : ''}
        </div>
        <div class="oc-card-foot">
            <button class="oc-btn-detail" onclick='ocOpenDetail(${JSON.stringify(c).replace(/'/g,"&#39;")})'>
                <i class="bi bi-info-circle"></i>Details
            </button>
            <button class="oc-btn-sub ${isSub?'unsubscribe':'subscribe'}" onclick='ocToggleSub(event, ${JSON.stringify(c).replace(/'/g,"&#39;")})'>
                ${isSub ? '<i class="bi bi-x-circle"></i>Unsubscribe' : '<i class="bi bi-plus-circle-fill"></i>Subscribe'}
            </button>
        </div>
    </div>`;
}

function ocOpenDetail(c) {
    ocDetailCourse = c;
    const isSub  = c.is_subscribed == 1;
    const price  = parseFloat(c.price) || 0;
    const disc   = parseFloat(c.discount) || 0;
    const finalP = disc > 0 ? price * (1 - disc / 100) : price;

    document.getElementById('ocDetailTitle').textContent = c.title;
    document.getElementById('ocDetailBody').innerHTML = `
        ${c.thumbnail
            ? `<img src="../data_files/${ocEsc(c.thumbnail)}" class="oc-modal-thumb" alt="">`
            : `<div class="oc-modal-thumb-placeholder"><i class="bi bi-collection-play"></i></div>`}
        <p class="text-muted small" style="line-height:1.6">${ocEsc(c.description||'No description available.')}</p>
        <div class="oc-detail-meta">
            <div class="oc-detail-pill"><i class="bi bi-person-circle"></i>${ocEsc(c.instructor_name||'—')}</div>
            ${c.category_name ? `<div class="oc-detail-pill"><i class="bi bi-tag"></i>${ocEsc(c.category_name)}</div>` : ''}
            <div class="oc-detail-pill"><i class="bi bi-collection"></i>${c.chapters||0} chapters</div>
            <div class="oc-detail-pill"><i class="bi bi-play-circle"></i>${c.lessons||0} lessons</div>
            <div class="oc-detail-pill"><i class="bi bi-currency-exchange"></i>${price===0?'Free':'TZS '+finalP.toLocaleString()}</div>
            ${isSub && c.enrolled_members > 0 ? `<div class="oc-detail-pill" style="color:#059669"><i class="bi bi-people-fill"></i>${c.enrolled_members} enrolled</div>` : ''}
        </div>
        ${isSub && c.expires_at ? `<div class="text-muted small"><i class="bi bi-calendar2-check me-1"></i>Access expires: <strong>${ocFmtDate(c.expires_at)}</strong></div>` : ''}
    `;
    const btn = document.getElementById('ocDetailSubBtn');
    btn.innerHTML = isSub ? '<i class="bi bi-x-circle me-1"></i>Unsubscribe' : '<i class="bi bi-plus-circle-fill me-1"></i>Subscribe Now';
    btn.style.background = isSub ? '' : 'linear-gradient(135deg,#6366f1,#8b5cf6)';
    btn.style.background = isSub ? 'linear-gradient(135deg,#dc2626,#ef4444)' : 'linear-gradient(135deg,#6366f1,#8b5cf6)';
    btn.onclick = () => ocToggleSubFromModal(c);
    new bootstrap.Modal(document.getElementById('ocDetailModal')).show();
}

async function ocToggleSub(event, c) {
    event.stopPropagation();
    await ocDoToggle(c);
}

async function ocToggleSubFromModal(c) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('ocDetailModal'));
    modal?.hide();
    await ocDoToggle(c);
}

async function ocDoToggle(c) {
    const isSub = c.is_subscribed == 1;

    if (isSub) {
        const result = await Swal.fire({
            title: 'Unsubscribe from course?',
            html: `<p class="mb-1">Remove access to <strong>${ocEsc(c.title)}</strong>?</p><p class="text-muted small">Members who are already enrolled will lose access.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Unsubscribe',
            reverseButtons: true,
            customClass: { popup: 'rounded-4' }
        });
        if (!result.isConfirmed) return;
        const r = await ocPost({ action: 'unsubscribe_course', course_id: c.id });
        if (r.status === 'success') { ocToast('Course unsubscribed'); ocLoad(); }
        else ocToast(r.message || 'Error', 'danger');
    } else {
        const result = await Swal.fire({
            title: 'Subscribe to course?',
            html: `<p class="mb-1">Add <strong>${ocEsc(c.title)}</strong> to your organization?</p><p class="text-muted small">Members will be able to enroll in this course.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Subscribe',
            reverseButtons: true,
            customClass: { popup: 'rounded-4' }
        });
        if (!result.isConfirmed) return;
        const r = await ocPost({ action: 'subscribe_course', course_id: c.id });
        if (r.status === 'success') { ocToast('Course subscribed — members can now enroll!', 'success'); ocLoad(); }
        else ocToast(r.message || 'Error', 'danger');
    }
}

function ocSetTab(tab, el) {
    ocTab = tab;
    document.querySelectorAll('.oc-tab-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    ocLoad();
}

function ocDebounce() {
    clearTimeout(ocDebTimer);
    ocDebTimer = setTimeout(ocLoad, 320);
}

/* ── helpers ── */
const ocEsc    = s => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const ocFmtDate = s => s ? new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';

async function ocPost(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => fd.append(k, v ?? ''));
    return fetch(OC_AJAX, { method:'POST', body:fd }).then(x=>x.json()).catch(()=>({status:'error',message:'Network error'}));
}

function ocToast(msg, type = 'success') {
    const colors = { success:'#16a34a', danger:'#dc2626', warning:'#d97706', info:'#0891b2' };
    const icons  = { success:'bi-check-circle-fill', danger:'bi-x-circle-fill', warning:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
    let c = document.getElementById('ocToastWrap');
    if (!c) {
        c = Object.assign(document.createElement('div'), { id:'ocToastWrap' });
        c.style.cssText = 'position:fixed;bottom:1.2rem;right:1.2rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem';
        document.body.appendChild(c);
    }
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.65rem 1rem;border-radius:.65rem;font-size:.84rem;box-shadow:0 4px 16px rgba(0,0,0,.18);display:flex;align-items:center;gap:.5rem;max-width:340px;animation:ocFadeUp .2s ease`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.success}" style="flex-shrink:0"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

if (!document.getElementById('ocFadeKf')) {
    const s = document.createElement('style');
    s.id = 'ocFadeKf';
    s.textContent = '@keyframes ocFadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(s);
}

ocInit();
</script>
