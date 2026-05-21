<?php
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('error_log', 'error_log.txt');

session_start();
include('config/db.php');
include('config/header-config.php');
include('config/dump.php');
include('config/modules.php');

// App::sendSMS('255765131788', 'Testing if message get Sent');
$username       = $_SESSION['usr_code'];
$fullname       = $_SESSION['name'];

$user_role      = $_SESSION['user_role'];
$usr_code       = $_SESSION['usr_code'];
$signup_success = App::signupStatus($usr_code);
$roleTitle      = App::getWhatFromWHere('role_title','tbl_user_roles', 'id',$user_role);
$userProfileImage = @App::getUserProfileImage($usr_code,$user_role);

// Load module permissions for this role
if ($user_role == 5) {
    $user_perms = ['*']; // super admin: unrestricted
} else {
    $pstmt = $db->prepare("SELECT module_key FROM tbl_module_permissions WHERE role_id = ? AND is_enabled = 1");
    $pstmt->bind_param("i", $user_role);
    $pstmt->execute();
    $user_perms = array_column($pstmt->get_result()->fetch_all(MYSQLI_ASSOC), 'module_key');
}      
if(!isset($_SESSION['usr_code'])){
    header('Location: ../');
    exit;
}

// Force light mode before any HTML output so the Set-Cookie header is sent correctly
setcookie('adminuiuxlayoutmode', 'light-mode', time() + 86400 * 365, '/');

// ── AJAX fragment mode (lazy navigation) ──────────────────────
if (!empty($_GET['_dcm_ajax'])) {
    include('pages/controller.php');
    exit;
}

if ($signup_success !== 'Completed' && ($user_role=='1')): ?>
<script>
    const params = new URLSearchParams(window.location.search);
    if (params.get("view") !== "student-profile-completion-8872") {
        window.location.href = "../data_files/?view=student-profile-completion-8872";
    }
</script>
<?php endif; 

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Digital Class - Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&amp;family=SUSE:wght@100..800&amp;display=swap" rel="stylesheet">
    <style>
        :root
        {
            --adminuiux-content-font: "Open Sans", sans-serif;
            --adminuiux-content-font-weight: 400;
            --adminuiux-title-font: "SUSE", sans-serif;
            --adminuiux-title-font-weight: 600
        }
    </style>
    <script>
    // Enforce light mode: clear cookie immediately so appd9fa.js reads light-mode
    (function(){
        var exp = new Date(); exp.setFullYear(exp.getFullYear()+1);
        document.cookie = 'adminuiuxlayoutmode=light-mode;path=/;expires='+exp.toUTCString();
        document.documentElement.classList.remove('dark-mode');
        document.documentElement.classList.add('light-mode-forced');
    })();
    </script>
    <script defer="defer" src="../assets/js/appd9fa.js?6b22e6ee1626676f5950"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link href="../assets/css/appd9fa.css?6b22e6ee1626676f5950" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    <link href="../assets/css/dcm-system.css" rel="stylesheet">
</head>

<body
    class="main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-standard theme-blue adminuiux-header-fill-theme adminuiux-sidebar-fill-white bg-gradient-1 scrollup"
    data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-bs-spy="scroll"
    data-bs-target="#list-example" data-bs-smooth-scroll="true" tabindex="0"
    data-sidebarlayout="adminuiux-sidebar-standard" data-headerlayout="adminuiux-header-standard"
    data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-fill-theme">
    <div class="pageloader">
        <div class="container h-100">
            <div class="row justify-content-center align-items-center text-center h-100">
                <div class="col-12 mb-auto pt-4"></div>
                <div class="col-auto"><img src="../assets/img/logo.svg" alt="" class="height-60 mb-3">
                    <p class="h6 mb-0">Digital Class</p>
                    <p class="h3 mb-4">Learning</p>
                    <div class="loader11 mb-2 mx-auto"></div>
                </div>
                <div class="col-12 mt-auto pb-4">
                    <p class="text-secondary">Please wait...</p>
                </div>
            </div>
        </div>
    </div>
    <header class="adminuiux-header">
        <nav class="navbar navbar-expand-lg fixed-top">
            <?php include('pages/nav_page.php'); ?>
        </nav>
        <div class="adminuiux-search-full">
            <div class="row gx-2 align-items-center">
                <?php include('pages/app_view.php'); ?>
            </div>
        </div>
    </header>
    <div class="adminuiux-wrap">
        <div class="adminuiux-sidebar shadow-sm">
            <div class="adminuiux-sidebar-inner">
                <?php include('pages/side_menu.php'); ?>
            </div>
        </div>
        <main class="adminuiux-content has-sidebar" onclick="contentClick()">
            <?php include('pages/controller.php'); ?>
        </main>
    </div>
    <div class="offcanvas offcanvas-end shadow border-0" tabindex="-1" id="theming" data-bs-scroll="true"
        data-bs-backdrop="false" aria-labelledby="theminglabel">
        <?php include('pages/personalize.php'); ?>
    </div>
    <footer class="adminuiux-footer has-adminuiux-sidebar mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md col-lg py-2"><span class="small">Copyright @<?php echo date('Y'); ?>, <a
                            href="https://digitalclassmedia.com/" target="_blank">Digital Class</a></span></div>
                <div class="col-12 col-md-auto col-lg-auto align-self-center">
                    <ul class="nav small">
                        <li class="nav-item"><a class="nav-link" href="../data_files/?view=help_instrauctions">Help</a></li>
                        <li class="nav-item">|</li>
                        <li class="nav-item"><a class="nav-link" href="../data_files/?view=terms_of_use">Terms of Use</a></li>
                        <li class="nav-item">|</li>
                        <li class="nav-item"><a class="nav-link" href="../data_files/?view=privacy_policy">Privacy Policy</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <div class="position-fixed bottom-0 end-0 m-3 z-index-5"><button
            class="btn btn-square btn-theme shadow rounded-circle" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#theming" aria-controls="theming"><i class="bi bi-palette"></i></button><br><button
            class="btn btn-theme btn-square shadow mt-2 d-none rounded-circle" id="backtotop"><i
                class="bi bi-arrow-up"></i></button>
    </div>
    
    
    <?php include('pages/modal_lunch.php'); ?>
    <script src="../assets/js/learning/learning-dashboard.js"></script>
    <script src="../assets/js/learning/learning-student-add.js"></script>
    <script src="../assets/js/learning/learning-student-progress.js"></script>
    <script src="../assets/js/learning/learning-teacher-profile.js"></script>
    <script src="../assets/js/learning/dcm-nav.js"></script>

    <!-- ═══════════════════════════════════════════════════════
         DCM Real-Time: WebSocket client + Notification system
    ════════════════════════════════════════════════════════════ -->
    <style>
    #dcmNotifBtn{position:relative;background:none;border:none;padding:.3rem .5rem;color:inherit;cursor:pointer;line-height:1;border-radius:8px;transition:background .15s}
    #dcmNotifBtn:hover{background:rgba(0,0,0,.07)}
    #dcmNotifBadge{position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;border-radius:20px;font-size:.58rem;font-weight:700;padding:.05rem .28rem;min-width:15px;line-height:1.6;text-align:center;display:none;border:2px solid #fff}
    #dcmWsDot{width:7px;height:7px;border-radius:50%;display:inline-block;background:#94a3b8;margin-left:3px;vertical-align:middle;transition:background .4s}
    #dcmWsDot.on{background:#22c55e}
    #dcmWsDot.wait{background:#f59e0b;animation:ws-blink 1s infinite}
    #dcmWsDot.off{background:#ef4444}
    @keyframes ws-blink{0%,100%{opacity:1}50%{opacity:.3}}
    #dcmNotifPanel{position:fixed;top:58px;right:12px;width:360px;max-width:95vw;background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.16);border:1px solid #e2e8f0;z-index:9999;display:none;overflow:hidden;max-height:520px;flex-direction:column}
    #dcmNotifPanel.show{display:flex}
    .dnhd{padding:.85rem 1.1rem .7rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.5rem;flex-shrink:0}
    .dnhd h6{margin:0;font-size:.88rem;font-weight:700;color:#1e293b;flex:1}
    #dcmNList{overflow-y:auto;flex:1}
    .dni{display:flex;gap:.75rem;padding:.75rem 1rem;border-bottom:1px solid #f8fafc;cursor:pointer;transition:background .12s;text-decoration:none;color:inherit}
    .dni:hover{background:#f8f9ff}
    .dni.u{background:#fafbff;border-left:3px solid #6366f1}
    .dnico{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
    .dntit{font-size:.82rem;font-weight:600;color:#1e293b;margin-bottom:.15rem}
    .dnbod{font-size:.76rem;color:#64748b;line-height:1.4}
    .dntim{font-size:.68rem;color:#94a3b8;margin-top:.2rem}
    .dnemp{padding:2rem 1rem;text-align:center;color:#94a3b8;font-size:.84rem}
    #dcmToasts{position:fixed;bottom:1.25rem;right:1.25rem;z-index:10000;display:flex;flex-direction:column;gap:.5rem;pointer-events:none}
    .dct{background:#fff;border-radius:14px;padding:.9rem 1.1rem;box-shadow:0 6px 30px rgba(0,0,0,.13);border:1px solid #e2e8f0;display:flex;gap:.75rem;align-items:flex-start;pointer-events:auto;max-width:340px;animation:tcIn .3s ease}
    @keyframes tcIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
    .dcticon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0}
    .dcttit{font-size:.82rem;font-weight:700;color:#1e293b;margin-bottom:.1rem}
    .dctbod{font-size:.76rem;color:#64748b;line-height:1.35}
    .dctx{margin-left:auto;background:none;border:none;color:#94a3b8;cursor:pointer;padding:0;font-size:1rem;flex-shrink:0;line-height:1}
    </style>

    <!-- Bell button injected into navbar -->
    <script>
    (function(){
        const nav = document.querySelector('.navbar .ms-auto');
        if(!nav) return;
        const wrap = document.createElement('div');
        wrap.style.cssText='display:inline-flex;align-items:center;gap:3px;margin:0 2px';
        wrap.innerHTML=`<button id="dcmNotifBtn" title="Notifications" onclick="dcmToggle(event)"><i class="bi bi-bell" style="font-size:1.1rem"></i><span id="dcmNotifBadge"></span></button><span id="dcmWsDot" class="off" title="Real-time offline"></span>`;
        nav.insertBefore(wrap, nav.firstChild);
    })();
    </script>

    <!-- Notification panel -->
    <div id="dcmNotifPanel">
        <div class="dnhd">
            <i class="bi bi-bell-fill" style="color:#6366f1"></i>
            <h6>Notifications</h6>
            <button class="btn btn-sm btn-link text-muted p-0" style="font-size:.74rem" onclick="dcmMarkAll()">Mark all read</button>
            <button class="btn btn-sm btn-link text-muted p-0 ms-1" onclick="dcmToggle()" style="font-size:.9rem">✕</button>
        </div>
        <div id="dcmNList"><div class="dnemp"><i class="bi bi-bell-slash d-block mb-2" style="font-size:1.4rem;opacity:.3"></i>No notifications yet</div></div>
    </div>
    <div id="dcmToasts"></div>

    <script>
    /* ── DCM Real-time client ──────────────────────────────── */
    const _NOTIF = 'ajax/ajax_notifications.php';
    const _TOKEN = 'ajax/ajax_ws_token.php';
    const _WSURL = 'ws://127.0.0.1:8765';

    let _ws=null, _wsRetry=0, _token=null, _uc=null, _role=0;
    let _count=0, _open=false, _poll=null;

    function _ago(ts){const d=Math.floor((Date.now()-new Date(ts))/1000);return d<60?'just now':d<3600?Math.floor(d/60)+'m ago':d<86400?Math.floor(d/3600)+'h ago':Math.floor(d/86400)+'d ago';}
    function _e(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

    function _badge(n){
        _count=n;
        const b=document.getElementById('dcmNotifBadge');
        if(!b)return;
        b.textContent=n>99?'99+':n;
        b.style.display=n>0?'block':'none';
    }
    function _dot(s){
        const d=document.getElementById('dcmWsDot');
        if(!d)return;
        d.className=s;
        d.title={on:'Real-time: live',wait:'Real-time: connecting…',off:'Real-time: offline (polling)'}[s]||'';
    }
    function _toast(n){
        const w=document.getElementById('dcmToasts');
        if(!w)return;
        const div=document.createElement('div');
        div.className='dct';
        div.innerHTML=`<div class="dcticon" style="background:${n.color}18;color:${n.color}"><i class="bi ${n.icon}"></i></div><div style="flex:1;min-width:0"><div class="dcttit">${_e(n.title)}</div>${n.body?`<div class="dctbod">${_e((n.body||'').substring(0,110))}</div>`:''}</div><button class="dctx" onclick="this.closest('.dct').remove()">×</button>`;
        if(n.link){div.style.cursor='pointer';div.addEventListener('click',e=>{if(!e.target.classList.contains('dctx'))window.location.href=n.link;});}
        w.appendChild(div);
        setTimeout(()=>div.remove(),7000);
    }
    function _render(rows){
        const l=document.getElementById('dcmNList');
        if(!l)return;
        if(!rows.length){l.innerHTML='<div class="dnemp"><i class="bi bi-bell-slash d-block mb-2" style="font-size:1.4rem;opacity:.3"></i>No notifications yet</div>';return;}
        l.innerHTML=rows.map(r=>`<a class="dni ${r.is_read==0?'u':''}" href="${r.link||'#'}" onclick="dcmRead(${r.id},this,event)"><div class="dnico" style="background:${r.color}18;color:${r.color}"><i class="bi ${r.icon}"></i></div><div style="flex:1;min-width:0"><div class="dntit">${_e(r.title)}</div>${r.body?`<div class="dnbod">${_e((r.body||'').substring(0,90))}</div>`:''}<div class="dntim">${_ago(r.created_at)}</div></div></a>`).join('');
    }
    function _fetchList(){fetch(_NOTIF+'?action=list').then(r=>r.json()).then(r=>{if(r.status==='success'){_badge(r.unread);_render(r.data);}}).catch(()=>{});}
    function _fetchCount(){fetch(_NOTIF+'?action=count').then(r=>r.json()).then(r=>{if(r.status==='success')_badge(r.count);}).catch(()=>{});}

    window.dcmToggle=function(e){if(e)e.stopPropagation();const p=document.getElementById('dcmNotifPanel');_open=!p.classList.contains('show');p.classList.toggle('show',_open);if(_open)_fetchList();};
    document.addEventListener('click',e=>{if(_open&&!e.target.closest('#dcmNotifPanel')&&!e.target.closest('#dcmNotifBtn')){document.getElementById('dcmNotifPanel')?.classList.remove('show');_open=false;}});

    window.dcmRead=function(id,el,e){
        el.classList.remove('u');
        fetch(_NOTIF,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'mark_read',id})}).then(()=>{if(_count>0)_badge(_count-1);}).catch(()=>{});
    };
    window.dcmMarkAll=function(){
        document.querySelectorAll('.dni.u').forEach(el=>el.classList.remove('u'));
        fetch(_NOTIF,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'mark_read'})}).then(()=>_badge(0)).catch(()=>{});
    };

    /* ── WebSocket ─────────────────────────────────────────── */
    function _wsConnect(){
        if(_ws&&(_ws.readyState===0||_ws.readyState===1))return;
        _dot('wait');
        _ws=new WebSocket(_WSURL);
        _ws.onopen=()=>{
            if(_token)_ws.send(JSON.stringify({action:'auth',token:_token,user_code:_uc,role:_role}));
        };
        _ws.onmessage=evt=>{
            let m;try{m=JSON.parse(evt.data);}catch{return;}
            if(m.type==='auth_ok'){_dot('on');_wsRetry=0;if(_poll){clearInterval(_poll);_poll=null;}}
            if(m.type==='auth_fail'){_dot('off');_ws.close();}
            if(m.type==='notification'){
                _badge(_count+1);
                _toast(m);
                if(_open)_fetchList();
                /* Refresh admin side-menu review badge */
                if(_role==5&&m.notif_type==='course_submitted'){
                    const sb=document.getElementById('sideReviewBadge');
                    if(sb){sb.textContent=(parseInt(sb.textContent)||0)+1;sb.style.display='';}
                }
            }
        };
        _ws.onerror=()=>_dot('off');
        _ws.onclose=()=>{
            _dot('off');
            const delay=Math.min(1000*Math.pow(1.7,_wsRetry++),32000);
            setTimeout(_wsConnect,delay);
            if(!_poll)_poll=setInterval(_fetchCount,30000);
        };
    }
    /* Client-side heartbeat so the server doesn't close idle connections */
    setInterval(()=>{if(_ws&&_ws.readyState===1)_ws.send(JSON.stringify({action:'ping'}));},25000);

    /* ── Boot ──────────────────────────────────────────────── */
    _fetchCount();
    fetch(_TOKEN).then(r=>r.json()).then(r=>{
        if(r.status!=='success')return;
        _token=r.token;_uc=r.user_code;_role=r.role;
        _wsConnect();
    }).catch(()=>{_dot('off');_poll=setInterval(_fetchCount,30000);});
    </script>
    <script>
    // Final light-mode enforcement — runs after all deferred scripts and jQuery.ready handlers
    (function enforce(){
        var h = document.documentElement;
        if (h.classList.contains('dark-mode')) {
            h.classList.remove('dark-mode');
        }
        // Keep cookie locked to light-mode
        var exp = new Date(); exp.setFullYear(exp.getFullYear()+1);
        document.cookie = 'adminuiuxlayoutmode=light-mode;path=/;expires='+exp.toUTCString();
    })();
    // Also run after jQuery ready in case appd9fa.js sets the class there
    if (typeof $ !== 'undefined') {
        $(function(){ $('html').removeClass('dark-mode'); $('body').removeClass('dark-mode'); });
    }
    </script>
</body>

</html>