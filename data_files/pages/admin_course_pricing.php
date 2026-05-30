<?php
if (($user_role ?? 0) != 5) { include('403.php'); return; }

$courseId = (int)($_GET['course_id'] ?? 0);
if (!$courseId) {
    echo '<div class="container-fluid px-3 py-4"><div class="alert alert-warning">No course_id specified. <a href="?view=admin_courses">Back to Courses</a></div></div>';
    return;
}

$courseTitle = '';
$stmt = $db->prepare("SELECT course_title FROM tbl_courses WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $courseId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
    echo '<div class="container-fluid px-3 py-4"><div class="alert alert-danger">Course not found.</div></div>';
    return;
}
$courseTitle = htmlspecialchars($row['course_title']);
?>
<style>
/* ═══ HERO ═══════════════════════════════════════════════════════ */
.aprc-hero{position:relative;overflow:hidden;border-radius:20px;background:linear-gradient(135deg,#0f0c29 0%,#302b63 45%,#24243e 100%);padding:2rem 2rem 1.75rem;margin:0 1rem 1.5rem;color:#fff}
.aprc-hero-orb{position:absolute;border-radius:50%;filter:blur(50px);pointer-events:none}
.aprc-hero-orb-1{width:220px;height:220px;background:rgba(99,102,241,.35);top:-60px;right:-40px;animation:aprcOrb1 6s ease-in-out infinite alternate}
.aprc-hero-orb-2{width:140px;height:140px;background:rgba(16,185,129,.3);bottom:-40px;right:160px;animation:aprcOrb2 8s ease-in-out infinite alternate}
.aprc-hero-orb-3{width:100px;height:100px;background:rgba(236,72,153,.25);top:20px;left:55%;animation:aprcOrb3 7s ease-in-out infinite alternate}
@keyframes aprcOrb1{from{transform:translate(0,0) scale(1)} to{transform:translate(20px,-15px) scale(1.15)}}
@keyframes aprcOrb2{from{transform:translate(0,0) scale(1)} to{transform:translate(-15px,20px) scale(1.2)}}
@keyframes aprcOrb3{from{transform:translate(0,0) scale(1)} to{transform:translate(15px,-10px) scale(.9)}}
.aprc-hero-content{position:relative;z-index:2;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap}
.aprc-hero-icon{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;border:1px solid rgba(255,255,255,.18);box-shadow:0 8px 32px rgba(99,102,241,.4)}
.aprc-hero-title{font-size:1.4rem;font-weight:800;line-height:1.1;letter-spacing:-.02em}
.aprc-hero-title span{background:linear-gradient(90deg,#a78bfa,#4ade80,#93c5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.aprc-hero-sub{font-size:.82rem;opacity:.6;margin-top:.2rem}
.aprc-hero-pills{display:flex;gap:.5rem;margin-top:.9rem;flex-wrap:wrap}
.aprc-hero-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;font-size:.72rem;font-weight:700;padding:.25rem .75rem;border-radius:20px;display:flex;align-items:center;gap:.35rem}
.aprc-hero-actions{margin-left:auto;display:flex;gap:.6rem;flex-shrink:0}
.aprc-hero-btn{padding:.55rem 1.2rem;border-radius:12px;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;border:none;display:flex;align-items:center;gap:.4rem}
.aprc-hero-btn-secondary{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)}
.aprc-hero-btn-secondary:hover{background:rgba(255,255,255,.2)}

/* ═══ SECTION CARDS ═══════════════════════════════════════════════ */
.aprc-card{background:#fff;border-radius:18px;box-shadow:0 2px 16px rgba(0,0,0,.06);margin:0 1rem 1.5rem;overflow:hidden}
.aprc-card-header{background:linear-gradient(135deg,#f8f7ff,#f1f5f9);padding:1rem 1.5rem;border-bottom:2px solid #e0e7ff;display:flex;align-items:center;gap:.75rem}
.aprc-card-header h6{margin:0;font-weight:800;font-size:.9rem;color:#1e1b4b}
.aprc-card-header .badge-section{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:.68rem;font-weight:700;padding:.2rem .6rem;border-radius:20px}
.aprc-card-body{padding:1.5rem}
.aprc-form-label{font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:.3rem}
.aprc-input{border:1.5px solid #e0e7ff;border-radius:10px;font-size:.85rem;padding:.5rem .85rem;transition:all .2s;background:#fff;width:100%}
.aprc-input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.aprc-save-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.55rem 1.4rem;font-size:.84rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.35)}
.aprc-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}
.aprc-save-btn:disabled{opacity:.6;transform:none;cursor:not-allowed}

/* ═══ TIER TABLE ═══════════════════════════════════════════════════ */
.aprc-tier-table{width:100%;border-collapse:collapse;margin-bottom:1rem}
.aprc-tier-table th{background:#f8f7ff;padding:.6rem .85rem;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;border-bottom:2px solid #e0e7ff;white-space:nowrap}
.aprc-tier-table td{padding:.55rem .85rem;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.aprc-tier-table tr:last-child td{border-bottom:none}
.aprc-tier-table input{border:1.5px solid #e0e7ff;border-radius:8px;padding:.3rem .55rem;font-size:.82rem;width:100%;transition:border-color .15s}
.aprc-tier-table input:focus{outline:none;border-color:#6366f1}
.aprc-tier-del{width:28px;height:28px;border-radius:7px;border:1.5px solid #fee2e2;background:#fff5f5;color:#ef4444;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .18s;font-size:.8rem}
.aprc-tier-del:hover{background:#ef4444;color:#fff;border-color:#ef4444}
.aprc-add-tier-btn{background:#f8f7ff;border:1.5px dashed #a5b4fc;color:#6366f1;border-radius:10px;padding:.45rem 1rem;font-size:.8rem;font-weight:700;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:.35rem}
.aprc-add-tier-btn:hover{background:#ede9fe;border-color:#6366f1}

/* ═══ DISCOUNT BADGES ═══════════════════════════════════════════════ */
.aprc-disc-badge{display:inline-flex;align-items:center;gap:.4rem;background:#f0fdf4;border:1px solid #86efac;border-radius:20px;padding:.3rem .85rem;font-size:.75rem;font-weight:700;color:#166534;margin:.25rem}
.aprc-disc-pct{background:#ede9fe;border-color:#c4b5fd;color:#5b21b6}
</style>

<div class="container-fluid px-0">

<!-- HERO -->
<div class="aprc-hero mx-3 mt-3 mb-0">
    <div class="aprc-hero-orb aprc-hero-orb-1"></div>
    <div class="aprc-hero-orb aprc-hero-orb-2"></div>
    <div class="aprc-hero-orb aprc-hero-orb-3"></div>
    <div class="aprc-hero-content">
        <div class="aprc-hero-icon"><i class="bi bi-currency-dollar"></i></div>
        <div>
            <div class="aprc-hero-title">Course <span>Pricing Config</span></div>
            <div class="aprc-hero-sub" id="aprcCourseTitle"><?= $courseTitle ?></div>
            <div class="aprc-hero-pills">
                <span class="aprc-hero-pill"><i class="bi bi-tag-fill"></i><span id="aprcPillPrice">Loading…</span></span>
                <span class="aprc-hero-pill"><i class="bi bi-layers"></i><span id="aprcPillTiers">— tiers</span></span>
                <span class="aprc-hero-pill"><i class="bi bi-percent"></i><span id="aprcPillDisc">— discounts</span></span>
            </div>
        </div>
        <div class="aprc-hero-actions d-none d-md-flex">
            <a href="?view=admin_course_detail&id=<?= $courseId ?>" class="aprc-hero-btn aprc-hero-btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Course
            </a>
        </div>
    </div>
</div>

<div class="mt-3" id="aprcContent">
    <div class="text-center py-5 text-muted">
        <div class="spinner-border spinner-border-sm me-2"></div> Loading pricing data…
    </div>
</div>

</div><!-- /.container-fluid -->

<!-- ══════ SCRIPT ══════ -->
<script>
(function () {
    var COURSE_ID = <?= (int)$courseId ?>;
    var AJAX      = 'ajax/ajax_course_pricing.php';
    var _data     = {};

    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, function(c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    function api(action, body, method) {
        method = method || 'GET';
        if (method === 'GET') {
            var qs = new URLSearchParams(Object.assign({action: action}, body || {}));
            return fetch(AJAX + '?' + qs).then(function(r){ return r.json(); });
        }
        return fetch(AJAX, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(Object.assign({action: action}, body || {}))
        }).then(function(r){ return r.json(); });
    }

    function load() {
        api('get_pricing', {course_id: COURSE_ID}).then(function(res) {
            if (res.status !== 'success') {
                document.getElementById('aprcContent').innerHTML =
                    '<div class="alert alert-danger mx-3">' + esc(res.message) + '</div>';
                return;
            }
            _data = res;
            updatePills();
            renderAll();
        });
    }

    function updatePills() {
        var c = _data.course || {};
        var p = parseFloat(c.price) || 0;
        document.getElementById('aprcPillPrice').textContent  = 'TZS ' + p.toLocaleString();
        document.getElementById('aprcPillTiers').textContent  = (_data.tiers  || []).length + ' tiers';
        document.getElementById('aprcPillDisc').textContent   = (_data.discounts || []).length + ' discounts';
    }

    function renderAll() {
        var c  = _data.course    || {};
        var t  = _data.tiers     || [];
        var ds = _data.discounts || [];

        var html = '';

        // ── Section 1: Individual Pricing ──────────────────────────────
        html += '<div class="aprc-card">';
        html += '<div class="aprc-card-header"><i class="bi bi-tag-fill text-primary me-1"></i><h6>Individual Pricing</h6><span class="badge-section ms-auto">Section 1</span></div>';
        html += '<div class="aprc-card-body">';
        html += '<div class="row g-3">';
        html += '<div class="col-md-3"><label class="aprc-form-label">Retail Price (TZS)</label><input class="aprc-input" type="number" min="0" step="0.01" id="fPrice" value="' + (parseFloat(c.price)||0) + '"></div>';
        html += '<div class="col-md-3"><label class="aprc-form-label">Org Price (TZS)</label><input class="aprc-input" type="number" min="0" step="0.01" id="fOrgPrice" value="' + (parseFloat(c.org_price)||0) + '"></div>';
        html += '<div class="col-md-2"><label class="aprc-form-label">Org Discount (%)</label><input class="aprc-input" type="number" min="0" max="100" step="0.1" id="fOrgDiscount" value="' + (parseFloat(c.org_discount)||0) + '"></div>';
        html += '<div class="col-md-2"><label class="aprc-form-label">Min Seats</label><input class="aprc-input" type="number" min="0" id="fMinSeats" value="' + (parseInt(c.min_seats)||0) + '"></div>';
        html += '<div class="col-md-2"><label class="aprc-form-label">Max Seats</label><input class="aprc-input" type="number" min="0" id="fMaxSeats" value="' + (parseInt(c.max_seats)||0) + '"></div>';
        html += '<div class="col-12"><label class="aprc-form-label">Pricing Notes</label><textarea class="aprc-input" rows="2" id="fPricingNotes" placeholder="Internal notes about pricing strategy…">' + esc(c.pricing_notes || '') + '</textarea></div>';
        html += '</div>';
        html += '<div class="mt-3 d-flex gap-2">';
        html += '<button class="aprc-save-btn" id="btnSavePrice" onclick="aprcMgr.savePrice()"><i class="bi bi-check-circle-fill"></i> Save Pricing</button>';
        html += '</div>';
        html += '</div></div>';

        // ── Section 2: Institutional Tier Table ────────────────────────
        html += '<div class="aprc-card">';
        html += '<div class="aprc-card-header"><i class="bi bi-layers-fill text-success me-1"></i><h6>Institutional Pricing Tiers</h6><span class="badge-section ms-auto">Section 2</span></div>';
        html += '<div class="aprc-card-body">';
        html += '<p class="text-muted" style="font-size:.8rem">Define seat-based pricing tiers for organisations. Rows are sorted by minimum seats.</p>';
        html += '<div class="table-responsive"><table class="aprc-tier-table"><thead><tr>';
        html += '<th>Tier Label</th><th>Min Seats</th><th>Max Seats</th><th>Price/Seat (TZS)</th><th>Valid From</th><th>Valid To</th><th></th>';
        html += '</tr></thead><tbody id="tierBody">';
        html += renderTierRows(t);
        html += '</tbody></table></div>';
        html += '<div class="d-flex gap-2 flex-wrap">';
        html += '<button class="aprc-add-tier-btn" onclick="aprcMgr.addTier()"><i class="bi bi-plus-lg"></i> Add Tier</button>';
        html += '<button class="aprc-save-btn" id="btnSaveTiers" onclick="aprcMgr.saveTiers()"><i class="bi bi-check-circle-fill"></i> Save Tiers</button>';
        html += '</div>';
        html += '</div></div>';

        // ── Section 3: Active Discounts ────────────────────────────────
        html += '<div class="aprc-card">';
        html += '<div class="aprc-card-header"><i class="bi bi-percent text-warning me-1"></i><h6>Active Discounts</h6><span class="badge-section ms-auto">Section 3</span></div>';
        html += '<div class="aprc-card-body">';
        if (!ds.length) {
            html += '<p class="text-muted" style="font-size:.85rem"><i class="bi bi-info-circle me-1"></i>No active discounts apply to this course.</p>';
        } else {
            ds.forEach(function(d) {
                var isPct = d.discount_type === 'percentage';
                var val   = isPct ? d.discount_value + '%' : 'TZS ' + parseFloat(d.discount_value).toLocaleString();
                var exp   = d.valid_to ? ' · expires ' + d.valid_to : '';
                html += '<span class="aprc-disc-badge ' + (isPct ? 'aprc-disc-pct' : '') + '">';
                html += '<i class="bi bi-tag-fill"></i> <strong>' + esc(d.code) + '</strong> — ' + esc(d.name) + ' (' + val + ')';
                html += ' <small class="opacity-75">' + d.usage_count + ' uses' + exp + '</small>';
                html += '</span>';
            });
        }
        html += '<div class="mt-3"><a href="?view=admin_bundles" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-arrow-right me-1"></i>Manage Bundles & Discounts</a></div>';
        html += '</div></div>';

        document.getElementById('aprcContent').innerHTML = html;
    }

    function renderTierRows(tiers) {
        if (!tiers.length) {
            return '<tr id="tierEmpty"><td colspan="7" class="text-center text-muted py-3" style="font-size:.82rem"><i class="bi bi-info-circle me-1"></i>No tiers yet. Click "Add Tier" to create one.</td></tr>';
        }
        return tiers.map(function(t, i) {
            return buildTierRow(i, t);
        }).join('');
    }

    function buildTierRow(idx, t) {
        t = t || {};
        return '<tr data-tier-idx="' + idx + '">' +
            '<td><input type="text" placeholder="e.g. Small Group" value="' + esc(t.tier_label||'') + '" data-field="tier_label"></td>' +
            '<td><input type="number" min="1" placeholder="1" value="' + (parseInt(t.min_seats)||'') + '" data-field="min_seats"></td>' +
            '<td><input type="number" min="0" placeholder="unlimited" value="' + (parseInt(t.max_seats)||'') + '" data-field="max_seats"></td>' +
            '<td><input type="number" min="0" step="0.01" placeholder="0.00" value="' + (parseFloat(t.price_per_seat)||'') + '" data-field="price_per_seat"></td>' +
            '<td><input type="date" value="' + esc(t.valid_from||'') + '" data-field="valid_from"></td>' +
            '<td><input type="date" value="' + esc(t.valid_to||'') + '" data-field="valid_to"></td>' +
            '<td><button class="aprc-tier-del" onclick="aprcMgr.removeTier(this)" title="Remove"><i class="bi bi-x-lg"></i></button></td>' +
            '</tr>';
    }

    function getTiersFromDOM() {
        var rows = document.querySelectorAll('#tierBody tr[data-tier-idx]');
        var result = [];
        rows.forEach(function(row) {
            var get = function(f){ return row.querySelector('[data-field="' + f + '"]').value.trim(); };
            result.push({
                tier_label:    get('tier_label'),
                min_seats:     parseInt(get('min_seats'))  || 0,
                max_seats:     parseInt(get('max_seats'))  || 0,
                price_per_seat:parseFloat(get('price_per_seat')) || 0,
                valid_from:    get('valid_from'),
                valid_to:      get('valid_to'),
            });
        });
        return result;
    }

    /* Public API attached to window */
    window.aprcMgr = {
        savePrice: function() {
            var btn = document.getElementById('btnSavePrice');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
            api('save_individual_price', {
                course_id:     COURSE_ID,
                price:         parseFloat(document.getElementById('fPrice').value)        || 0,
                org_price:     parseFloat(document.getElementById('fOrgPrice').value)     || 0,
                org_discount:  parseFloat(document.getElementById('fOrgDiscount').value)  || 0,
                min_seats:     parseInt(document.getElementById('fMinSeats').value)       || 0,
                max_seats:     parseInt(document.getElementById('fMaxSeats').value)       || 0,
                pricing_notes: document.getElementById('fPricingNotes').value,
            }, 'POST').then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Save Pricing';
                if (res.status === 'success') {
                    Swal.fire({icon:'success', title:'Saved!', text: res.message, timer:1800, showConfirmButton:false});
                    load();
                } else {
                    Swal.fire({icon:'error', title:'Error', text: res.message});
                }
            });
        },

        saveTiers: function() {
            var btn = document.getElementById('btnSaveTiers');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
            var tiers = getTiersFromDOM();
            api('save_tiers', {course_id: COURSE_ID, tiers: tiers}, 'POST').then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Save Tiers';
                if (res.status === 'success') {
                    Swal.fire({icon:'success', title:'Tiers Saved!', text: res.message, timer:1800, showConfirmButton:false});
                    load();
                } else {
                    Swal.fire({icon:'error', title:'Error', text: res.message});
                }
            });
        },

        addTier: function() {
            var tbody = document.getElementById('tierBody');
            var empty = document.getElementById('tierEmpty');
            if (empty) empty.remove();
            var idx   = tbody.querySelectorAll('tr[data-tier-idx]').length;
            var tmp   = document.createElement('tbody');
            tmp.innerHTML = buildTierRow(idx, {});
            tbody.appendChild(tmp.firstChild);
        },

        removeTier: function(btn) {
            var row   = btn.closest('tr');
            var tbody = document.getElementById('tierBody');
            row.remove();
            if (!tbody.querySelectorAll('tr[data-tier-idx]').length) {
                tbody.innerHTML = '<tr id="tierEmpty"><td colspan="7" class="text-center text-muted py-3" style="font-size:.82rem"><i class="bi bi-info-circle me-1"></i>No tiers yet.</td></tr>';
            }
            // Re-index
            tbody.querySelectorAll('tr[data-tier-idx]').forEach(function(r, i){ r.setAttribute('data-tier-idx', i); });
        }
    };

    load();
})();
</script>
