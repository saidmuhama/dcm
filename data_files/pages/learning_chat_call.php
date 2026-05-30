<?php
$me     = $_SESSION['usr_code'] ?? '';
$myRole = (int)($_SESSION['user_role'] ?? 0);
$myName = $_SESSION['name'] ?? 'Me';
if (!$me) { echo "<script>window.location.replace('?view=kill-session-user')</script>"; exit; }
?>
<!-- ─── Chat Styles ──────────────────────────────────────────────────── -->
<style>
/* ═══════════════════════════════════════════════════════════════════
   DCM CHAT — Premium UI  2026
═══════════════════════════════════════════════════════════════════ */
:root {
  --ch-sidebar-w:   320px;
  --ch-info-w:      290px;
  --ch-header-h:    66px;
  --ch-radius:      16px;
  --ch-primary:     #6366f1;
  --ch-primary-d:   #4f46e5;
  --ch-primary-lt:  #ede9fe;
  --ch-mine-bg:     #d9fdd3;
  --ch-mine-bg2:    #c3f0ba;
  --ch-their-bg:    #ffffff;
  --ch-bg:          #efeae2;
  --ch-sidebar-bg:  #ffffff;
  --ch-header-bg:   #ffffff;
  --ch-shadow:      0 8px 40px rgba(99,102,241,.12);
  --ch-trans:       .22s cubic-bezier(.4,0,.2,1);
}

/* ── Keyframes ──────────────────────────────────────────────────── */
@keyframes ch-slide-in-left  { from{opacity:0;transform:translateX(-18px) scale(.96)} to{opacity:1;transform:none} }
@keyframes ch-slide-in-right { from{opacity:0;transform:translateX(18px) scale(.96)}  to{opacity:1;transform:none} }
@keyframes ch-fade-up        { from{opacity:0;transform:translateY(12px)}             to{opacity:1;transform:none} }
@keyframes ch-pop            { 0%{transform:scale(.85)} 60%{transform:scale(1.06)} 100%{transform:scale(1)} }
@keyframes ch-pulse-ring     { 0%{box-shadow:0 0 0 0 rgba(99,102,241,.4)} 70%{box-shadow:0 0 0 8px transparent} 100%{box-shadow:0 0 0 0 transparent} }
@keyframes ch-blink          { 0%,80%,100%{transform:scale(0);opacity:.3} 40%{transform:scale(1);opacity:1} }
@keyframes ch-mic-pulse      { 0%,100%{transform:scale(1)} 50%{transform:scale(1.18)} }
@keyframes ch-shimmer        { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
@keyframes ch-online-ping    { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.9);opacity:0} }
@keyframes ch-badge-pop      { 0%{transform:scale(0)} 60%{transform:scale(1.25)} 100%{transform:scale(1)} }
@keyframes ch-conv-in        { from{opacity:0;transform:translateX(-12px)} to{opacity:1;transform:none} }
@keyframes ch-spinner-glow   { 0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,.3)} 50%{box-shadow:0 0 12px 4px rgba(99,102,241,.15)} }

/* ── Outer wrapper ──────────────────────────────────────────────── */
.dcm-chat-wrap {
  display: flex;
  height: calc(100vh - 128px);
  min-height: 520px;
  border-radius: var(--ch-radius);
  overflow: hidden;
  box-shadow: var(--ch-shadow), 0 2px 8px rgba(0,0,0,.06);
  background: var(--ch-bg);
  animation: ch-fade-up .4s var(--ch-trans) both;
}

/* ── Scrollbar polish ───────────────────────────────────────────── */
.dcm-chat-wrap *::-webkit-scrollbar       { width: 5px; height: 5px }
.dcm-chat-wrap *::-webkit-scrollbar-track { background: transparent }
.dcm-chat-wrap *::-webkit-scrollbar-thumb { background: rgba(99,102,241,.2); border-radius: 10px }
.dcm-chat-wrap *::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,.4) }

/* ══════════════════════════════════════════════════════════════════
   LEFT SIDEBAR
══════════════════════════════════════════════════════════════════ */
.dcm-chat-sidebar {
  width: var(--ch-sidebar-w);
  min-width: var(--ch-sidebar-w);
  display: flex;
  flex-direction: column;
  background: var(--ch-sidebar-bg);
  border-right: 1px solid rgba(0,0,0,.06);
}

.dcm-chat-sidebar-head {
  padding: 16px 16px 12px;
  background: linear-gradient(135deg, #f8f7ff 0%, #ffffff 100%);
  border-bottom: 1px solid rgba(99,102,241,.08);
}

.dcm-sidebar-title {
  font-size: .95rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--ch-primary), #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.dcm-sidebar-action-btn {
  width: 34px; height: 34px;
  border-radius: 10px;
  border: none;
  background: var(--ch-primary-lt);
  color: var(--ch-primary);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background var(--ch-trans), transform var(--ch-trans), box-shadow var(--ch-trans);
}
.dcm-sidebar-action-btn:hover {
  background: var(--ch-primary);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99,102,241,.35);
}

.dcm-conv-search {
  border: 1.5px solid rgba(99,102,241,.15) !important;
  border-radius: 12px !important;
  background: #f8f7ff !important;
  padding: 7px 14px !important;
  font-size: .82rem !important;
  transition: border-color var(--ch-trans), box-shadow var(--ch-trans) !important;
}
.dcm-conv-search:focus {
  border-color: var(--ch-primary) !important;
  box-shadow: 0 0 0 3px rgba(99,102,241,.12) !important;
  background: #fff !important;
}

.dcm-chat-conv-list { flex: 1; overflow-y: auto; }

/* Conversation item */
.dcm-conv-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 16px;
  cursor: pointer;
  border-bottom: 1px solid rgba(0,0,0,.04);
  transition: background var(--ch-trans), transform var(--ch-trans);
  animation: ch-conv-in .3s var(--ch-trans) both;
  position: relative;
}
.dcm-conv-item:hover  { background: #f5f3ff; }
.dcm-conv-item.active { background: linear-gradient(135deg, #ede9fe, #f0f7ff); }
.dcm-conv-item.active::before {
  content: '';
  position: absolute;
  left: 0; top: 20%; bottom: 20%;
  width: 3px;
  border-radius: 0 3px 3px 0;
  background: linear-gradient(180deg, var(--ch-primary), #8b5cf6);
}

/* Stagger animation for conv list items */
.dcm-conv-item:nth-child(1){animation-delay:.04s}
.dcm-conv-item:nth-child(2){animation-delay:.07s}
.dcm-conv-item:nth-child(3){animation-delay:.10s}
.dcm-conv-item:nth-child(4){animation-delay:.13s}
.dcm-conv-item:nth-child(5){animation-delay:.16s}
.dcm-conv-item:nth-child(6){animation-delay:.19s}
.dcm-conv-item:nth-child(7){animation-delay:.22s}
.dcm-conv-item:nth-child(8){animation-delay:.25s}

.dcm-conv-avatar {
  width: 46px; height: 46px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800;
  font-size: .78rem;
  flex-shrink: 0;
  position: relative;
  box-shadow: 0 2px 8px rgba(0,0,0,.12);
  transition: transform var(--ch-trans);
}
.dcm-conv-item:hover .dcm-conv-avatar { transform: scale(1.06); }

/* Online pulse ring */
.dcm-online-dot {
  width: 12px; height: 12px;
  background: #22c55e;
  border-radius: 50%;
  border: 2.5px solid #fff;
  position: absolute;
  bottom: 0; right: 0;
  box-shadow: 0 0 0 0 rgba(34,197,94,.4);
  animation: ch-pulse-ring 2.5s infinite;
}
.dcm-online-dot::after {
  content: '';
  position: absolute;
  inset: -3px;
  border-radius: 50%;
  background: rgba(34,197,94,.25);
  animation: ch-online-ping 2s infinite;
}

.dcm-conv-body    { flex: 1; min-width: 0; }
.dcm-conv-name    { font-weight: 700; font-size: .84rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1e1b4b; }
.dcm-conv-preview { font-size: .74rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
.dcm-conv-item.active .dcm-conv-name    { color: var(--ch-primary); }
.dcm-conv-item.active .dcm-conv-preview { color: #7c3aed; }

.dcm-conv-meta { text-align: right; flex-shrink: 0; }
.dcm-conv-time  { font-size: .68rem; color: #9ca3af; white-space: nowrap; }
.dcm-conv-item.active .dcm-conv-time { color: var(--ch-primary); }

.dcm-conv-badge {
  background: linear-gradient(135deg, var(--ch-primary), #8b5cf6);
  color: #fff;
  font-size: .62rem;
  font-weight: 700;
  border-radius: 20px;
  padding: 2px 7px;
  min-width: 20px;
  text-align: center;
  margin-top: 4px;
  display: inline-block;
  animation: ch-badge-pop .3s var(--ch-trans) both;
  box-shadow: 0 2px 8px rgba(99,102,241,.4);
}

/* Empty sidebar state */
.dcm-sidebar-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  padding: 40px 20px;
  color: #9ca3af;
}
.dcm-sidebar-empty i { font-size: 2.5rem; opacity: .2; margin-bottom: 12px; }

/* Sidebar skeleton loader */
.dcm-skel {
  background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: ch-shimmer 1.4s infinite;
  border-radius: 8px;
}

/* ══════════════════════════════════════════════════════════════════
   MAIN CHAT AREA
══════════════════════════════════════════════════════════════════ */
.dcm-chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  background: var(--ch-bg);
  position: relative;
  /* Subtle chat wallpaper pattern */
  background-image: radial-gradient(circle, rgba(99,102,241,.035) 1px, transparent 1px);
  background-size: 24px 24px;
}

/* Empty / welcome state */
.dcm-chat-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(145deg, #f8f7ff 0%, #eff6ff 50%, #fdf4ff 100%);
  text-align: center;
  padding: 40px;
  animation: ch-fade-up .5s var(--ch-trans) both;
}
.dcm-empty-icon-wrap {
  width: 96px; height: 96px;
  border-radius: 28px;
  background: linear-gradient(135deg, var(--ch-primary-lt), #dbeafe);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
  box-shadow: 0 8px 32px rgba(99,102,241,.18);
  animation: ch-pop .6s var(--ch-trans) both;
}
.dcm-empty-icon-wrap i { font-size: 2.8rem; color: var(--ch-primary); }

/* ── HEADER ─────────────────────────────────────────────────────── */
.dcm-chat-window { display: flex; flex-direction: column; height: 100%; }

.dcm-chat-header {
  height: var(--ch-header-h);
  display: flex;
  align-items: center;
  padding: 0 16px;
  gap: 10px;
  background: var(--ch-header-bg);
  border-bottom: 1px solid rgba(0,0,0,.06);
  flex-shrink: 0;
  box-shadow: 0 1px 8px rgba(0,0,0,.05);
  position: relative;
  z-index: 2;
  animation: ch-fade-up .3s var(--ch-trans) both;
}

.dcm-chat-header-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800;
  font-size: .76rem;
  flex-shrink: 0;
  box-shadow: 0 2px 10px rgba(0,0,0,.15);
  transition: transform var(--ch-trans);
  cursor: pointer;
}
.dcm-chat-header-avatar:hover { transform: scale(1.08); }

.dcm-header-action {
  width: 36px; height: 36px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: #6b7280;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background var(--ch-trans), color var(--ch-trans), transform var(--ch-trans);
  font-size: 1rem;
}
.dcm-header-action:hover {
  background: var(--ch-primary-lt);
  color: var(--ch-primary);
  transform: scale(1.1);
}

/* ── MESSAGES ───────────────────────────────────────────────────── */
.dcm-chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

/* Message wrapper with entrance animation */
.dcm-msg-wrap {
  display: flex;
  flex-direction: column;
  max-width: 68%;
  animation: ch-fade-up .25s var(--ch-trans) both;
}
.dcm-msg-wrap.mine   { align-self: flex-end;  align-items: flex-end;  animation-name: ch-slide-in-right; }
.dcm-msg-wrap.theirs { align-self: flex-start; align-items: flex-start; animation-name: ch-slide-in-left; }
.dcm-msg-wrap + .dcm-msg-wrap { margin-top: 1px; }
.dcm-msg-wrap.mine + .dcm-msg-wrap.theirs,
.dcm-msg-wrap.theirs + .dcm-msg-wrap.mine { margin-top: 8px; }

.dcm-msg-sender {
  font-size: .69rem;
  font-weight: 700;
  margin-bottom: 3px;
  padding-left: 6px;
  color: var(--ch-primary);
  letter-spacing: .01em;
}

/* Bubble */
.dcm-msg-bubble {
  padding: 9px 14px;
  border-radius: 18px;
  max-width: 100%;
  word-break: break-word;
  font-size: .875rem;
  line-height: 1.5;
  position: relative;
  transition: box-shadow var(--ch-trans);
}
.dcm-msg-bubble:hover { box-shadow: 0 3px 12px rgba(0,0,0,.1) !important; }

/* Mine — green WhatsApp-style with tail */
.mine .dcm-msg-bubble {
  background: linear-gradient(145deg, var(--ch-mine-bg), var(--ch-mine-bg2));
  border-bottom-right-radius: 4px;
  box-shadow: 0 1px 4px rgba(0,0,0,.08);
}
.mine .dcm-msg-bubble::after {
  content: '';
  position: absolute;
  bottom: 0; right: -7px;
  width: 0; height: 0;
  border-left: 8px solid var(--ch-mine-bg2);
  border-top: 6px solid transparent;
  border-bottom: 0 solid transparent;
}

/* Theirs — white with left tail */
.theirs .dcm-msg-bubble {
  background: var(--ch-their-bg);
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 4px rgba(0,0,0,.08), 0 0 0 1px rgba(0,0,0,.03);
}
.theirs .dcm-msg-bubble::after {
  content: '';
  position: absolute;
  bottom: 0; left: -7px;
  width: 0; height: 0;
  border-right: 8px solid var(--ch-their-bg);
  border-top: 6px solid transparent;
  border-bottom: 0 solid transparent;
}

/* Meta row */
.dcm-msg-meta {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: .66rem;
  color: #9ca3af;
  padding: 1px 6px 0;
  margin-top: 1px;
}
.mine .dcm-msg-meta { justify-content: flex-end; }

.dcm-msg-tick      { color: #9ca3af;  transition: color .3s; font-size: .75rem; }
.dcm-msg-tick.read { color: #3b9ae6; }

/* Message menu button */
.dcm-msg-menu-btn {
  opacity: 0;
  transition: opacity .18s;
  font-size: .7rem;
  color: #9ca3af;
  padding: 0 2px !important;
  line-height: 1 !important;
}
.dcm-msg-wrap:hover .dcm-msg-menu-btn { opacity: 1; }

/* Date separator */
.dcm-date-sep {
  text-align: center;
  margin: 14px 0 10px;
  position: relative;
}
.dcm-date-sep::before {
  content: '';
  position: absolute;
  top: 50%; left: 10%; right: 10%;
  height: 1px;
  background: rgba(0,0,0,.08);
}
.dcm-date-sep span {
  position: relative;
  background: rgba(255,255,255,.85);
  backdrop-filter: blur(4px);
  border-radius: 20px;
  padding: 3px 14px;
  font-size: .71rem;
  color: #6b7280;
  font-weight: 600;
  box-shadow: 0 1px 4px rgba(0,0,0,.07);
  letter-spacing: .03em;
}

/* System message */
.dcm-system-msg { text-align: center; margin: 6px 0; }
.dcm-system-msg span {
  background: rgba(255,255,255,.72);
  backdrop-filter: blur(4px);
  font-size: .72rem;
  color: #6b7280;
  padding: 3px 12px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  font-style: italic;
}

/* ── Media in bubbles ───────────────────────────────────────────── */
.dcm-msg-img {
  max-width: 260px; max-height: 220px;
  border-radius: 12px;
  cursor: zoom-in;
  display: block;
  transition: transform var(--ch-trans), box-shadow var(--ch-trans);
  box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.dcm-msg-img:hover { transform: scale(1.02); box-shadow: 0 6px 20px rgba(0,0,0,.18); }

.dcm-msg-video { max-width: 300px; border-radius: 12px; display: block; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
/* Audio card — 16:9 thumbnail */
.dcm-audio-card {
  width: 280px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,.1);
}
.dcm-audio-thumb {
  position: relative;
  width: 100%;
  padding-top: 56.25%; /* 16:9 */
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4c1d95 70%, #6d28d9 100%);
  overflow: hidden;
}
.dcm-audio-thumb-inner {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
}
/* Animated waveform bars */
.dcm-audio-wave {
  display: flex;
  align-items: center;
  gap: 3px;
  height: 36px;
}
.dcm-audio-wave span {
  display: block;
  width: 4px;
  border-radius: 4px;
  background: rgba(255,255,255,.7);
  animation: ch-wave 1.2s ease-in-out infinite;
  transform-origin: bottom;
}
.dcm-audio-wave span:nth-child(1)  { height: 14px; animation-delay: 0s }
.dcm-audio-wave span:nth-child(2)  { height: 26px; animation-delay: .1s }
.dcm-audio-wave span:nth-child(3)  { height: 32px; animation-delay: .2s }
.dcm-audio-wave span:nth-child(4)  { height: 20px; animation-delay: .3s }
.dcm-audio-wave span:nth-child(5)  { height: 36px; animation-delay: .15s }
.dcm-audio-wave span:nth-child(6)  { height: 24px; animation-delay: .25s }
.dcm-audio-wave span:nth-child(7)  { height: 30px; animation-delay: .05s }
.dcm-audio-wave span:nth-child(8)  { height: 18px; animation-delay: .35s }
.dcm-audio-wave span:nth-child(9)  { height: 28px; animation-delay: .2s }
.dcm-audio-wave span:nth-child(10) { height: 12px; animation-delay: .1s }
@keyframes ch-wave {
  0%,100% { transform: scaleY(.45); opacity:.5 }
  50%      { transform: scaleY(1);   opacity:1 }
}
/* Pause the animation when the native audio is not playing */
.dcm-audio-card.paused .dcm-audio-wave span { animation-play-state: paused; }
.dcm-audio-label {
  font-size: .72rem;
  font-weight: 700;
  color: rgba(255,255,255,.85);
  letter-spacing: .06em;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 5px;
}
.dcm-audio-native {
  display: block;
  width: 100%;
  border-radius: 0;
  background: #fff;
  height: 36px;
}

.dcm-msg-file {
  display: flex; align-items: center; gap: 10px;
  min-width: 180px; max-width: 280px;
  padding: 2px 0;
}
.dcm-msg-file-icon { font-size: 1.8rem; flex-shrink: 0; }
.dcm-msg-file-info { min-width: 0; flex: 1; }
.dcm-msg-file-name { font-size: .79rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1e1b4b; }
.dcm-msg-file-size { font-size: .68rem; color: #9ca3af; }

/* Reply quote inside bubble */
.dcm-reply-bubble {
  border-left: 3px solid var(--ch-primary);
  padding: 5px 10px;
  margin-bottom: 6px;
  background: rgba(99,102,241,.07);
  border-radius: 0 8px 8px 0;
  font-size: .75rem;
  color: #4f46e5;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* ── TYPING INDICATOR ───────────────────────────────────────────── */
.dcm-typing-bar {
  padding: 5px 18px 6px;
  font-size: .75rem;
  color: #6b7280;
  background: rgba(255,255,255,.85);
  backdrop-filter: blur(6px);
  border-top: 1px solid rgba(0,0,0,.04);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  animation: ch-fade-up .2s both;
}
.dcm-typing-bubble {
  display: flex;
  align-items: center;
  gap: 3px;
  background: #fff;
  border-radius: 20px;
  padding: 6px 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,.08);
}
.dcm-typing-dot {
  width: 7px; height: 7px;
  background: linear-gradient(135deg, var(--ch-primary), #8b5cf6);
  border-radius: 50%;
  animation: ch-blink 1.4s infinite both;
}
.dcm-typing-dot:nth-child(2) { animation-delay: .18s; }
.dcm-typing-dot:nth-child(3) { animation-delay: .36s; }

/* ── FOOTER / INPUT ─────────────────────────────────────────────── */
.dcm-chat-footer {
  background: rgba(255,255,255,.95);
  backdrop-filter: blur(8px);
  border-top: 1px solid rgba(0,0,0,.06);
  padding: 10px 12px;
  flex-shrink: 0;
  position: relative;
}

.dcm-reply-preview, .dcm-file-preview {
  background: linear-gradient(135deg, #f5f3ff, #eff6ff);
  border-radius: 10px;
  padding: 7px 12px;
  margin-bottom: 8px;
  border-left: 3px solid var(--ch-primary);
  animation: ch-fade-up .2s both;
  box-shadow: 0 1px 4px rgba(99,102,241,.1);
}

.dcm-chat-input-row {
  display: flex;
  align-items: flex-end;
  gap: 6px;
  background: #f3f4f6;
  border-radius: 24px;
  padding: 4px 6px 4px 4px;
  border: 1.5px solid rgba(99,102,241,.1);
  transition: border-color var(--ch-trans), box-shadow var(--ch-trans);
}
.dcm-chat-input-row:focus-within {
  border-color: rgba(99,102,241,.3);
  box-shadow: 0 0 0 3px rgba(99,102,241,.08);
  background: #fff;
}

.dcm-msg-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 7px 10px;
  resize: none;
  outline: none;
  max-height: 120px;
  font-size: .875rem;
  line-height: 1.45;
  color: #1e1b4b;
}
.dcm-msg-input::placeholder { color: #9ca3af; }

.dcm-icon-btn {
  color: #6b7280;
  padding: 6px;
  border-radius: 50%;
  border: none;
  background: transparent;
  line-height: 1;
  cursor: pointer;
  transition: color var(--ch-trans), background var(--ch-trans), transform var(--ch-trans);
  flex-shrink: 0;
}
.dcm-icon-btn:hover {
  color: var(--ch-primary);
  background: var(--ch-primary-lt);
  transform: scale(1.12);
}

.dcm-send-btn {
  width: 38px; height: 38px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  border: none;
  background: linear-gradient(135deg, var(--ch-primary), #8b5cf6);
  color: #fff;
  cursor: pointer;
  box-shadow: 0 3px 12px rgba(99,102,241,.4);
  transition: transform var(--ch-trans), box-shadow var(--ch-trans);
}
.dcm-send-btn:hover {
  transform: scale(1.1) translateY(-1px);
  box-shadow: 0 6px 20px rgba(99,102,241,.5);
}
.dcm-send-btn:active { transform: scale(.95); }

.dcm-mic-btn.recording {
  color: #ef4444 !important;
  animation: ch-mic-pulse .8s infinite;
}

/* ── EMOJI PICKER ───────────────────────────────────────────────── */
.dcm-emoji-picker {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 8px;
  width: 320px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 40px rgba(0,0,0,.14), 0 0 0 1px rgba(0,0,0,.04);
  z-index: 100;
  overflow: hidden;
  animation: ch-pop .22s var(--ch-trans) both;
}
.dcm-ep-tabs {
  display: flex;
  gap: 2px;
  padding: 8px 8px 0;
  border-bottom: 1px solid #f0f0f5;
  background: #fafaf9;
}
.dcm-ep-tab {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 1.1rem;
  padding: 5px 2px 6px;
  border-radius: 8px 8px 0 0;
  cursor: pointer;
  transition: background .12s;
  line-height: 1;
}
.dcm-ep-tab:hover   { background: var(--ch-primary-lt); }
.dcm-ep-tab.active  {
  background: #fff;
  box-shadow: 0 -2px 0 var(--ch-primary) inset;
}
.dcm-ep-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 1px;
  padding: 8px;
  max-height: 200px;
  overflow-y: auto;
}
.dcm-ep-em {
  font-size: 1.35rem;
  cursor: pointer;
  padding: 4px;
  border-radius: 7px;
  line-height: 1;
  transition: background .1s, transform .1s;
  user-select: none;
}
.dcm-ep-em:hover {
  background: var(--ch-primary-lt);
  transform: scale(1.3);
}

/* ══════════════════════════════════════════════════════════════════
   RIGHT INFO PANEL
══════════════════════════════════════════════════════════════════ */
.dcm-chat-info {
  width: var(--ch-info-w);
  min-width: var(--ch-info-w);
  background: #fff;
  border-left: 1px solid rgba(0,0,0,.06);
  display: flex;
  flex-direction: column;
  animation: ch-slide-in-right .3s var(--ch-trans) both;
}
.dcm-chat-info-head {
  padding: 18px 16px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(0,0,0,.06);
  font-weight: 700;
  font-size: .88rem;
  background: linear-gradient(135deg, #f8f7ff, #fff);
}
.dcm-chat-info-body { flex: 1; overflow-y: auto; padding: 14px; }

.dcm-member-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 4px;
  border-radius: 10px;
  transition: background var(--ch-trans);
}
.dcm-member-item:hover { background: #f5f3ff; }
.dcm-member-avatar {
  width: 38px; height: 38px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: .7rem;
  flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(0,0,0,.1);
}

/* ── LIGHTBOX ───────────────────────────────────────────────────── */
.dcm-lightbox {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.92);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: zoom-out;
  animation: ch-fade-up .18s both;
}
.dcm-lightbox img {
  max-width: 92vw;
  max-height: 92vh;
  border-radius: 8px;
  box-shadow: 0 0 60px rgba(0,0,0,.6);
  animation: ch-pop .25s var(--ch-trans) both;
}

/* ══════════════════════════════════════════════════════════════════
   LOADING SKELETON
══════════════════════════════════════════════════════════════════ */
.dcm-conv-skel {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0,0,0,.04);
}
.dcm-skel-circle { width:46px;height:46px;border-radius:50%;flex-shrink:0; }
.dcm-skel-line-sm { height:10px;border-radius:6px;width:55%; }
.dcm-skel-line-xs { height:8px;border-radius:6px;width:80%;margin-top:6px; }
.dcm-skel { background:linear-gradient(90deg,#f0f0f5 25%,#e8e8f0 50%,#f0f0f5 75%);background-size:200% 100%;animation:ch-shimmer 1.5s infinite; }

/* ══════════════════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════════════════ */
@media(max-width:991px) {
  :root { --ch-sidebar-w: 270px; --ch-info-w: 0px; }
  .dcm-chat-info { display: none; }
}
@media(max-width:767px) {
  :root { --ch-sidebar-w: 100%; }
  .dcm-chat-wrap { height: calc(100vh - 112px); border-radius: 0; }
  .dcm-chat-sidebar {
    position: absolute;
    z-index: 10;
    height: 100%;
    transition: transform .28s cubic-bezier(.4,0,.2,1);
  }
  .dcm-chat-sidebar.hidden { transform: translateX(-100%); }
  .dcm-chat-info { display: none !important; }
  .dcm-msg-wrap  { max-width: 84%; }
  .dcm-emoji-picker { width: 280px; }
}
</style>

<!-- ─── Breadcrumb ───────────────────────────────────────────────────── -->
<div class="container-fluid px-3 px-md-4 mt-3 mb-2">
    <div class="d-flex align-items-center gap-2">
        <h5 class="mb-0">Messages</h5>
        <span class="badge bg-theme-1 ms-1" id="globalUnreadBadge" style="display:none"></span>
        <nav aria-label="breadcrumb" class="ms-auto d-none d-md-block">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="?view=3002">Home</a></li>
                <li class="breadcrumb-item active">Messages</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ─── Chat Layout ──────────────────────────────────────────────────── -->
<div class="container-fluid px-3 px-md-4 pb-3">
<div class="dcm-chat-wrap" id="dcmChatWrap">

    <!-- ═══ LEFT SIDEBAR — Conversations ═══ -->
    <div class="dcm-chat-sidebar" id="dcmChatSidebar">
        <div class="dcm-chat-sidebar-head">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="dcm-sidebar-title flex-grow-1">Messages</span>
                <button class="dcm-sidebar-action-btn" title="New Message" onclick="dcmChat.openNewChatModal()"><i class="bi bi-pencil-square"></i></button>
                <button class="dcm-sidebar-action-btn" title="New Group" onclick="dcmChat.openCreateGroupModal()"><i class="bi bi-people-fill"></i></button>
            </div>
            <input class="form-control dcm-conv-search" id="convSearch" placeholder="🔍 Search conversations…" oninput="dcmChat.filterConvs(this.value)">
        </div>
        <div class="dcm-chat-conv-list" id="convList">
            <!-- skeleton loaders -->
            <?php for($i=0;$i<5;$i++): ?>
            <div class="dcm-conv-skel">
                <div class="dcm-skel-circle dcm-skel"></div>
                <div class="flex-grow-1"><div class="dcm-skel-line-sm dcm-skel"></div><div class="dcm-skel-line-xs dcm-skel"></div></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- ═══ CENTER — Chat Window ═══ -->
    <div class="dcm-chat-main" id="dcmChatMain">
        <!-- Empty state -->
        <div class="dcm-chat-empty" id="chatEmpty">
            <div class="dcm-empty-icon-wrap">
                <i class="bi bi-chat-heart-fill"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color:#1e1b4b">Your messages</h5>
            <p class="text-muted mb-3" style="font-size:.88rem">Send private messages or start a group chat with your classmates and teachers</p>
            <button class="btn btn-sm rounded-pill px-4 py-2 fw-semibold" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 16px rgba(99,102,241,.4)" onclick="dcmChat.openNewChatModal()"><i class="bi bi-pencil-square me-2"></i>New Message</button>
        </div>

        <!-- Active chat -->
        <div class="dcm-chat-window d-none" id="chatWindow">
            <!-- Header -->
            <div class="dcm-chat-header">
                <button class="btn btn-sm btn-link p-1 d-lg-none me-1" onclick="dcmChat.showSidebar()"><i class="bi bi-arrow-left fs-5"></i></button>
                <div class="dcm-chat-header-avatar" id="chatHeaderAvatar"></div>
                <div class="flex-grow-1 ms-1" style="min-width:0">
                    <div class="fw-semibold text-truncate" id="chatHeaderName" style="font-size:.9rem"></div>
                    <div class="text-muted" id="chatHeaderSub" style="font-size:.73rem"></div>
                </div>
                <div class="d-flex align-items-center gap-1 ms-auto">
                    <button class="dcm-header-action" onclick="dcmChat.toggleInfo()" title="Details"><i class="bi bi-info-circle-fill"></i></button>
                    <div class="dropdown">
                        <button class="dcm-header-action caret-none" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end small shadow-lg border-0 rounded-3 py-1" id="convContextMenu"></ul>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="dcm-chat-messages" id="chatMessages" onscroll="dcmChat.onScroll(this)">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary" id="msgSpinner"></div></div>
            </div>

            <!-- Typing indicator -->
            <div class="dcm-typing-bar d-none" id="typingBar">
                <div class="dcm-typing-bubble">
                    <div class="dcm-typing-dot"></div>
                    <div class="dcm-typing-dot"></div>
                    <div class="dcm-typing-dot"></div>
                </div>
                <span id="typingText" class="small text-muted"></span>
            </div>

            <!-- Footer -->
            <div class="dcm-chat-footer" style="position:relative">
                <!-- Reply preview -->
                <div class="dcm-reply-preview d-none" id="replyPreview">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-reply-fill text-theme-1"></i>
                        <div class="flex-grow-1 small text-truncate" id="replyPreviewText"></div>
                        <button class="btn btn-sm btn-link p-0 text-muted" onclick="dcmChat.cancelReply()"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
                <!-- File preview -->
                <div class="dcm-file-preview d-none" id="filePreview">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark text-theme-1" id="filePreviewIcon" style="font-size:1.1rem"></i>
                        <span class="small flex-grow-1 text-truncate" id="filePreviewName"></span>
                        <button class="btn btn-sm btn-link p-0 text-muted" onclick="dcmChat.cancelFile()"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="progress mt-1 d-none" id="uploadProgress" style="height:3px">
                        <div class="progress-bar bg-theme-1" id="uploadProgressBar" style="width:0%"></div>
                    </div>
                </div>
                <!-- Input row -->
                <div class="dcm-chat-input-row">
                    <button class="dcm-icon-btn" onclick="dcmChat.toggleEmoji(event)" title="Emoji"><i class="bi bi-emoji-smile fs-5"></i></button>
                    <button class="dcm-icon-btn" onclick="dcmChat.triggerFile()" title="Attach file"><i class="bi bi-paperclip fs-5"></i></button>
                    <input type="file" id="fileInput" class="d-none" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt" onchange="dcmChat.onFileChosen(this)">
                    <textarea class="dcm-msg-input" id="msgInput" placeholder="Type a message…" rows="1"
                        onkeydown="dcmChat.onKey(event)" oninput="dcmChat.onTyping(this)"></textarea>
                    <button class="dcm-icon-btn" id="micBtn"
                        onmousedown="dcmChat.startRec(event)" onmouseup="dcmChat.stopRec()"
                        ontouchstart="dcmChat.startRec(event)" ontouchend="dcmChat.stopRec()"
                        title="Hold for voice note"><i class="bi bi-mic-fill fs-5"></i></button>
                    <button class="dcm-send-btn" onclick="dcmChat.send()" title="Send"><i class="bi bi-send-fill" style="font-size:.85rem"></i></button>
                </div>
                <!-- Emoji panel -->
                <div class="dcm-emoji-picker d-none" id="emojiPicker"></div>
            </div>
        </div>
    </div>

    <!-- ═══ RIGHT — Info Panel ═══ -->
    <div class="dcm-chat-info d-none" id="chatInfoPanel">
        <div class="dcm-chat-info-head">
            <span id="infoPanelTitle">Details</span>
            <button class="btn btn-sm btn-link text-muted p-0" onclick="dcmChat.toggleInfo()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dcm-chat-info-body" id="infoPanelBody"></div>
    </div>
</div>
</div>

<!-- ═══ NEW CHAT MODAL ═══ -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-2">
                <h6 class="modal-title"><i class="bi bi-pencil-square me-2 text-theme-1"></i>New Message</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <input class="form-control mb-3" id="newChatSearch" placeholder="Search users by name or email…" oninput="dcmChat.searchUsers(this.value,'newChatResults')">
                <div id="newChatResults" style="max-height:320px;overflow-y:auto"></div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ CREATE GROUP MODAL ═══ -->
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-2">
                <h6 class="modal-title"><i class="bi bi-people-fill me-2 text-theme-1"></i>New Group Chat</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input class="form-control mb-3" id="groupName" placeholder="Group name (e.g. Math Class 10B)">
                <input class="form-control mb-2" id="groupSearch" placeholder="Add members by name…" oninput="dcmChat.searchUsers(this.value,'groupResults',true)">
                <div id="groupSelectedMembers" class="d-flex flex-wrap gap-1 mb-2"></div>
                <div id="groupResults" style="max-height:240px;overflow-y:auto"></div>
            </div>
            <div class="modal-footer pt-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-theme btn-sm" onclick="dcmChat.createGroup()"><i class="bi bi-check2 me-1"></i>Create Group</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ ADD MEMBER MODAL ═══ -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-2">
                <h6 class="modal-title"><i class="bi bi-person-plus-fill me-2 text-theme-1"></i>Add Member</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input class="form-control mb-2" id="addMemberSearch" placeholder="Search users…" oninput="dcmChat.searchUsers(this.value,'addMemberResults',false,true)">
                <div id="addMemberResults" style="max-height:300px;overflow-y:auto"></div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ IMAGE LIGHTBOX ═══ -->
<div class="dcm-lightbox d-none" id="imgLightbox" onclick="this.classList.add('d-none')">
    <img src="" id="lightboxImg" alt="">
</div>

<!-- ═══ JavaScript ═══ -->
<script>
(function() {
'use strict';

/* ── EMOJIS ─────────────────────────────────────────────────────── */
const EMOJI_CATS = {
  '😊 Faces':   [...'😀😁😂🤣😃😄😅😆😇😈😉😊😋😌😍🥰😎😏😐😑😒😓😔😕🙃😖😗😘😙😚😛😜😝😞😟😠😡😢😣😤😥😦😧😨😩😪😫😬😭😮😯😰😱😲😳😴😵🥴🤐🤑🤒🤓🤔🤕🤗🤘🤙🥺🤩🥳🤪🥸🫠🫡🫢🫣🫤🫨'],
  '👋 People':  [...'👋🤚🖐✋🖖🤙👌🤌🤏✌🤞🤟🤘🤙👈👉👆🖕👇☝👍👎✊👊🤛🤜👏🙌🫶🤲🫱🫲🫳🫴🫸🫷💅🤳💪🦾🦿🦵🦶👂🦻👃🫀🫁🧠🦷🦴👀👅💋🫦'],
  '❤️ Hearts':  [...'❤🧡💛💚💙💜🖤🤍🤎💔❤‍🔥❤‍🩹💕💞💓💗💖💘💝💟☮✝☪🕉✡🔯'],
  '🎉 Symbols': [...'✅❌⭕🔥💯💢💥💦💨💫⭐🌟✨🎯🔔🔕🎵🎶🎊🎉🎁🎈🏆🥇🥈🥉🎓📣📢💡💬💭🗯🗨💤🚀🛸🌈🌊🌸🌺🌻🍀🌴🌵🔑🗝🔒🔓💎💰🎲'],
  '👍 Hands':   [...'👍👎🙏💪👏🤝🫶🫁🤲🤜🤛✊👊✌🤞🫰🤙☝🖕👉👈👆👇'],
  '📚 Study':   [...'📚📖📝✏️🖊🖋📓📔📒📕📗📘📙📃📄📑🗒🗓📊📈📉📋🗂📂📁🗃🗄🗑🔍🔎🔬🔭💻🖥🖨⌨🖱📱📲☎📞📟📠🎓🏫🏆🏅🎯✅❎🧮🔢💯'],
  '😺 Animals': [...'🐶🐱🐭🐹🐰🦊🐻🐼🐨🐯🦁🐮🐷🐸🐵🙈🙉🙊🐔🐧🐦🦅🦆🦉🦇🐺🐗🦄🐝🐛🦋🐌🐞🐜🦟🦗🦂🐢🐍🦎🦖🦕🐙🦑🦐🦞🦀🐡🐠🐟🐬🐳🐋🦈'],
};
const EMOJIS = Object.values(EMOJI_CATS).flat(); // flat list for quick insert

/* ── STATE ──────────────────────────────────────────────────────── */
const ME        = <?= json_encode($me) ?>;
const MY_NAME   = <?= json_encode($myName) ?>;
const AJAX_BASE = 'ajax/ajax_chat.php';
const UPLOAD_URL= 'ajax/ajax_chat_upload.php';

let state = {
    convId:        null,
    convType:      'direct',
    convName:      '',
    lastMsgId:     0,
    convs:         [],
    pollingTimer:  null,
    typingTimer:   null,
    typingSent:    false,
    heartbeatTimer:null,
    replyTo:       null,
    pendingFile:   null,
    mediaRecorder: null,
    audioChunks:   [],
    isRecording:   false,
    loadingOlder:  false,
    hasMore:       true,
    groupMembers:  [],   // for create group modal
    readBy:        [],
    msgCache:      {},   // id->msg for reply preview
};

/* ── HELPERS ────────────────────────────────────────────────────── */
function ajax(url, opts={}) {
    return fetch(url, {credentials:'same-origin',...opts}).then(r=>r.json());
}
function post(action, data) {
    return ajax(AJAX_BASE, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({action, ...data}),
    });
}
function get(action, params={}) {
    const q = new URLSearchParams({action,...params}).toString();
    return ajax(AJAX_BASE+'?'+q);
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatTime(ts) {
    if (!ts) return '';
    const d = new Date(ts.replace(' ','T'));
    if (isNaN(d)) return '';
    const now = new Date(), today = new Date(now.getFullYear(),now.getMonth(),now.getDate());
    const msgDay = new Date(d.getFullYear(),d.getMonth(),d.getDate());
    const h = d.getHours().toString().padStart(2,'0');
    const m = d.getMinutes().toString().padStart(2,'0');
    if (msgDay >= today) return `${h}:${m}`;
    const diff = Math.round((today-msgDay)/(86400000));
    if (diff === 1) return 'Yesterday';
    if (diff < 7) return ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d.getDay()];
    return `${d.getDate()}/${d.getMonth()+1}/${d.getFullYear()}`;
}
function formatFullTime(ts) {
    if (!ts) return '';
    const d = new Date(ts.replace(' ','T'));
    return d.toLocaleString();
}
function formatDateSep(ts) {
    if (!ts) return '';
    const d = new Date(ts.replace(' ','T'));
    const now = new Date(), today = new Date(now.getFullYear(),now.getMonth(),now.getDate());
    const msgDay = new Date(d.getFullYear(),d.getMonth(),d.getDate());
    const diff = Math.round((today-msgDay)/(86400000));
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Yesterday';
    return d.toLocaleDateString('en-GB',{day:'2-digit',month:'long',year:'numeric'});
}
function fileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes+'B';
    if (bytes < 1048576) return Math.round(bytes/1024)+'KB';
    return (bytes/1048576).toFixed(1)+'MB';
}
function fileIcon(name) {
    const ext = (name||'').split('.').pop().toLowerCase();
    const map = {pdf:'bi-file-pdf text-danger',doc:'bi-file-word text-primary',docx:'bi-file-word text-primary',
        xls:'bi-file-excel text-success',xlsx:'bi-file-excel text-success',zip:'bi-file-zip text-warning',
        txt:'bi-file-text',mp3:'bi-music-note',ogg:'bi-music-note',wav:'bi-music-note'};
    return 'bi '+( map[ext] || 'bi-file-earmark text-secondary');
}
function avatar(initials, color, size=44, fontSize='.78rem') {
    return `<div style="width:${size}px;height:${size}px;border-radius:50%;background:${esc(color)};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:${fontSize};color:#fff;flex-shrink:0">${esc(initials)}</div>`;
}
function roleBadge(label) {
    const map = {'Teacher':'text-bg-primary','Admin':'text-bg-danger','Org Admin':'text-bg-warning','Student':'text-bg-success'};
    const cls = map[label]||'text-bg-secondary';
    return `<span class="badge ${cls} fw-normal" style="font-size:.6rem">${esc(label)}</span>`;
}
function scrollBottom(force=false) {
    const el = document.getElementById('chatMessages');
    if (!el) return;
    if (force || el.scrollTop + el.clientHeight > el.scrollHeight - 120) {
        el.scrollTop = el.scrollHeight;
    }
}
function showToast(msg, type='info') {
    const t = document.createElement('div');
    const bg = type==='error'?'bg-danger':type==='success'?'bg-success':'bg-theme-1';
    t.className = `position-fixed bottom-0 end-0 m-3 alert ${bg} text-white py-2 px-3 rounded-3 shadow`;
    t.style.cssText = 'z-index:9999;font-size:.84rem;max-width:320px';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(()=>t.remove(), 3500);
}

/* ── CONVERSATIONS LIST ─────────────────────────────────────────── */
const dcmChat = window.dcmChat = {

loadConvs() {
    get('get_conversations').then(res => {
        if (res.status !== 'success') return;
        state.convs = res.data;
        renderConvList(res.data);
    });
},

filterConvs(q) {
    q = q.toLowerCase();
    const filtered = q ? state.convs.filter(c=>(c.name||'').toLowerCase().includes(q)) : state.convs;
    renderConvList(filtered);
},

openConv(convId) {
    if (state.convId === convId) return;
    state.convId = convId;

    // Sidebar: mark active
    document.querySelectorAll('.dcm-conv-item').forEach(el=>el.classList.toggle('active', parseInt(el.dataset.id)===convId));

    // Hide mobile sidebar
    document.getElementById('dcmChatSidebar').classList.add('hidden');

    // Show window
    document.getElementById('chatEmpty').classList.add('d-none');
    document.getElementById('chatWindow').classList.remove('d-none');

    // Reset state
    state.lastMsgId  = 0;
    state.replyTo    = null;
    state.hasMore    = true;
    state.readBy     = [];
    document.getElementById('replyPreview').classList.add('d-none');
    document.getElementById('chatMessages').innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';
    document.getElementById('typingBar').classList.add('d-none');

    // Find conv info
    const conv = state.convs.find(c=>c.id===convId);
    if (conv) renderConvHeader(conv);

    // Load messages
    this.loadMsgs(convId, true);

    // Start polling
    clearInterval(state.pollingTimer);
    state.pollingTimer = setInterval(()=>this.poll(), 2000);

    // Mark read
    setTimeout(()=>post('mark_read',{conv_id:convId}), 500);

    // Update badge
    if (conv) {
        conv.unread_count = 0;
        renderConvList(state.convs);
    }

    // Build context menu
    buildContextMenu(conv);

    // Info panel: close on conv switch
    document.getElementById('chatInfoPanel').classList.add('d-none');
},

loadMsgs(convId, reset=false) {
    const params = {conv_id: convId};
    if (!reset && state.lastMsgId > 0) {
        // Get older (find smallest id in DOM)
        const firstId = parseInt(document.querySelector('[data-msg-id]')?.dataset.msgId||0);
        if (firstId > 0) params.before_id = firstId;
    }
    state.loadingOlder = true;
    get('get_messages', params).then(res => {
        state.loadingOlder = false;
        if (res.status !== 'success' || convId !== state.convId) return;

        const box = document.getElementById('chatMessages');
        if (reset) {
            box.innerHTML = '';
        }

        if (res.data.length === 0 && reset) {
            box.innerHTML = '<div class="text-center py-5 text-muted small">No messages yet. Say hello! 👋</div>';
            state.hasMore = false;
            return;
        }

        state.hasMore  = res.has_more;
        state.readBy   = res.read_by || [];

        if (res.data.length > 0) {
            const lastId = res.data[res.data.length-1].id;
            if (lastId > state.lastMsgId) state.lastMsgId = lastId;
        }

        const scrollOldH = box.scrollHeight;
        const frag = buildMsgFragment(res.data, reset);
        if (reset) {
            box.appendChild(frag);
            scrollBottom(true);
        } else {
            box.insertBefore(frag, box.firstChild);
            // Maintain scroll position after prepend
            box.scrollTop = box.scrollHeight - scrollOldH;
        }
    }).catch(()=>{ state.loadingOlder=false; });
},

poll() {
    if (!state.convId) return;
    get('poll', {conv_id:state.convId, last_id:state.lastMsgId}).then(res=>{
        if (res.status!=='success') return;

        // New messages
        if (res.messages && res.messages.length > 0) {
            const box = document.getElementById('chatMessages');
            const placeholder = box.querySelector('.spinner-border, .text-muted.small');
            if (placeholder) placeholder.closest('div.text-center, div.py-5')?.remove();

            const frag = buildMsgFragment(res.messages, false, true);
            box.appendChild(frag);
            const lastId = res.messages[res.messages.length-1].id;
            if (lastId > state.lastMsgId) state.lastMsgId = lastId;
            scrollBottom();
            post('mark_read',{conv_id:state.convId});
        }

        // Typing
        const typingBar = document.getElementById('typingBar');
        const typingText = document.getElementById('typingText');
        if (res.typing && res.typing.length > 0) {
            const names = res.typing.join(', ');
            typingText.textContent = names + (res.typing.length===1?' is typing':'are typing');
            typingBar.classList.remove('d-none');
        } else {
            typingBar.classList.add('d-none');
        }

        // Read receipts
        if (res.read_by) {
            state.readBy = res.read_by;
            updateReadTicks();
        }

        // Global unread badge
        if (typeof res.total_unread !== 'undefined') {
            const badge = document.getElementById('globalUnreadBadge');
            if (res.total_unread > 0) {
                badge.textContent = res.total_unread > 99 ? '99+' : res.total_unread;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        }
    });
},

onScroll(el) {
    if (el.scrollTop < 80 && state.hasMore && !state.loadingOlder && state.convId) {
        this.loadMsgs(state.convId, false);
    }
},

/* ── SEND ──────────────────────────────────────────────────────── */
send() {
    if (!state.convId) return;

    const input = document.getElementById('msgInput');

    if (state.pendingFile) {
        // Upload then send
        this.uploadAndSend();
        return;
    }

    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    input.style.height = '';

    const payload = {
        conv_id: state.convId,
        body:    text,
        type:    'text',
    };
    if (state.replyTo) {
        payload.reply_to = state.replyTo.id;
        this.cancelReply();
    }

    post('send_message', payload).then(res => {
        if (res.status === 'success') {
            const box = document.getElementById('chatMessages');
            const placeholder = box.querySelector('.py-5');
            if (placeholder) placeholder.remove();
            const frag = buildMsgFragment([res.message], false, true);
            box.appendChild(frag);
            if (res.message.id > state.lastMsgId) state.lastMsgId = res.message.id;
            scrollBottom(true);
            updateConvPreview(state.convId, res.message);
        }
    });

    state.typingSent = false;
    clearTimeout(state.typingTimer);
    post('heartbeat',{typing_in:null});
},

uploadAndSend() {
    const file     = state.pendingFile;
    const fileType = state.pendingFileType;
    const formData = new FormData();
    formData.append('file', file, file.name || 'voice_note.ogg');

    const prog = document.getElementById('uploadProgress');
    const bar  = document.getElementById('uploadProgressBar');
    prog.classList.remove('d-none');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', UPLOAD_URL);
    xhr.withCredentials = true;
    xhr.upload.onprogress = e => {
        if (e.lengthComputable) bar.style.width = Math.round(e.loaded/e.total*100)+'%';
    };
    xhr.onload = () => {
        prog.classList.add('d-none');
        this.cancelFile();
        let res;
        try { res = JSON.parse(xhr.responseText); } catch(e) { showToast('Upload failed','error'); return; }
        if (res.status !== 'success') { showToast(res.message||'Upload failed','error'); return; }

        const payload = {
            conv_id:   state.convId,
            type:      res.type,
            body:      res.file_name,
            file_path: res.file_path,
            file_name: res.file_name,
            file_size: res.file_size,
        };
        if (state.replyTo) { payload.reply_to = state.replyTo.id; this.cancelReply(); }

        post('send_message', payload).then(r => {
            if (r.status === 'success') {
                const box = document.getElementById('chatMessages');
                const placeholder = box.querySelector('.py-5');
                if (placeholder) placeholder.remove();
                const frag = buildMsgFragment([r.message], false, true);
                box.appendChild(frag);
                if (r.message.id > state.lastMsgId) state.lastMsgId = r.message.id;
                scrollBottom(true);
                updateConvPreview(state.convId, r.message);
            }
        });
    };
    xhr.onerror = () => { prog.classList.add('d-none'); showToast('Upload failed','error'); };
    xhr.send(formData);
},

onKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.send();
    }
},

onTyping(el) {
    // Auto-grow textarea
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120)+'px';

    // Typing indicator
    if (!state.typingSent && el.value.trim()) {
        state.typingSent = true;
        post('heartbeat',{typing_in:state.convId});
    }
    clearTimeout(state.typingTimer);
    state.typingTimer = setTimeout(()=>{
        state.typingSent = false;
        post('heartbeat',{typing_in:null});
    }, 3000);
},

/* ── FILE & VOICE ──────────────────────────────────────────────── */
triggerFile() {
    document.getElementById('fileInput').click();
},

onFileChosen(input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    state.pendingFile     = file;
    state.pendingFileType = file.type.startsWith('image/') ? 'image' :
                            file.type.startsWith('audio/') ? 'audio' :
                            file.type.startsWith('video/') ? 'video' : 'file';

    const fp   = document.getElementById('filePreview');
    const icon = document.getElementById('filePreviewIcon');
    const name = document.getElementById('filePreviewName');
    icon.className = 'bi '+( state.pendingFileType==='image' ? 'bi-image text-theme-1' :
                              state.pendingFileType==='audio' ? 'bi-music-note-beamed text-success' :
                              state.pendingFileType==='video' ? 'bi-camera-video text-danger' : 'bi-file-earmark text-theme-1');
    name.textContent = file.name + ' (' + fileSize(file.size) + ')';
    fp.classList.remove('d-none');
    input.value = '';
},

cancelFile() {
    state.pendingFile     = null;
    state.pendingFileType = null;
    document.getElementById('filePreview').classList.add('d-none');
    document.getElementById('uploadProgress').classList.add('d-none');
    document.getElementById('uploadProgressBar').style.width = '0%';
},

startRec(e) {
    e.preventDefault();
    if (!navigator.mediaDevices) { showToast('Microphone not available','error'); return; }
    document.getElementById('micBtn').querySelector('i').className = 'bi bi-record-circle-fill fs-5 text-danger';
    document.getElementById('micBtn').title = 'Recording… release to send';

    navigator.mediaDevices.getUserMedia({audio:true}).then(stream=>{
        state.audioChunks  = [];
        state.isRecording  = true;
        state.mediaRecorder = new MediaRecorder(stream);
        state.mediaRecorder.ondataavailable = e=>state.audioChunks.push(e.data);
        state.mediaRecorder.onstop = ()=>{
            stream.getTracks().forEach(t=>t.stop());
            if (!state.isRecording) return; // cancelled
            const blob = new Blob(state.audioChunks, {type:'audio/ogg; codecs=opus'});
            if (blob.size < 500) { showToast('Recording too short','error'); return; }
            const file = new File([blob],'voice_note.ogg',{type:'audio/ogg'});
            state.pendingFile = file;
            state.pendingFileType = 'audio';
            const fp = document.getElementById('filePreview');
            document.getElementById('filePreviewIcon').className = 'bi bi-mic-fill text-success';
            document.getElementById('filePreviewName').textContent = 'Voice Note ('+ fileSize(blob.size)+')';
            fp.classList.remove('d-none');
        };
        state.mediaRecorder.start();
    }).catch(()=>{ showToast('Microphone access denied','error'); this.stopRec(); });
},

stopRec() {
    document.getElementById('micBtn').querySelector('i').className = 'bi bi-mic-fill fs-5';
    document.getElementById('micBtn').title = 'Hold for voice note';
    if (state.mediaRecorder && state.mediaRecorder.state !== 'inactive') {
        state.mediaRecorder.stop();
    }
},

/* ── REPLY ─────────────────────────────────────────────────────── */
setReply(msgId) {
    const msg = state.msgCache[msgId];
    if (!msg) return;
    state.replyTo = msg;
    const prev = document.getElementById('replyPreview');
    document.getElementById('replyPreviewText').textContent = (msg.is_mine ? 'You: ' : (msg.sender_name+': ')) + (msg.body || msg.type);
    prev.classList.remove('d-none');
    document.getElementById('msgInput').focus();
},

cancelReply() {
    state.replyTo = null;
    document.getElementById('replyPreview').classList.add('d-none');
},

deleteMsg(msgId) {
    if (!confirm('Delete this message?')) return;
    post('delete_message',{msg_id:msgId}).then(res=>{
        if (res.status==='success') {
            const el = document.querySelector(`[data-msg-id="${msgId}"]`);
            if (el) {
                el.querySelector('.dcm-msg-bubble').innerHTML = '<em class="text-muted" style="font-size:.8rem">Message deleted</em>';
            }
        }
    });
},

/* ── EMOJI ─────────────────────────────────────────────────────── */
_buildEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    if (picker.dataset.built) return;
    picker.dataset.built = '1';

    // Tab bar
    const catNames = Object.keys(EMOJI_CATS);
    let html = `<div class="dcm-ep-tabs" id="epTabs">`;
    catNames.forEach((name, i) => {
        const icon = name.split(' ')[0];
        html += `<button class="dcm-ep-tab${i===0?' active':''}" data-cat="${i}" title="${name.split(' ').slice(1).join(' ')}">${icon}</button>`;
    });
    html += `</div>`;

    // Grid per category
    catNames.forEach((name, i) => {
        const emojis = EMOJI_CATS[name];
        html += `<div class="dcm-ep-grid${i===0?'':' d-none'}" data-grid="${i}">`;
        html += emojis.map(em => `<span class="dcm-ep-em" data-em="${encodeURIComponent(em)}">${em}</span>`).join('');
        html += `</div>`;
    });
    picker.innerHTML = html;

    // Tab switching
    picker.addEventListener('click', e => {
        const tab = e.target.closest('.dcm-ep-tab');
        if (tab) {
            const idx = tab.dataset.cat;
            picker.querySelectorAll('.dcm-ep-tab').forEach(t => t.classList.remove('active'));
            picker.querySelectorAll('.dcm-ep-grid').forEach(g => g.classList.add('d-none'));
            tab.classList.add('active');
            picker.querySelector(`[data-grid="${idx}"]`).classList.remove('d-none');
            return;
        }
        const em = e.target.closest('.dcm-ep-em');
        if (em) {
            dcmChat.insertEmoji(decodeURIComponent(em.dataset.em));
        }
    });
},

toggleEmoji(e) {
    e.stopPropagation();
    const picker = document.getElementById('emojiPicker');
    this._buildEmojiPicker();
    picker.classList.toggle('d-none');
},

insertEmoji(em) {
    const input = document.getElementById('msgInput');
    const pos   = input.selectionStart ?? input.value.length;
    input.value = input.value.slice(0, pos) + em + input.value.slice(pos);
    input.setSelectionRange(pos + [...em].length, pos + [...em].length);
    input.focus();
    document.getElementById('emojiPicker').classList.add('d-none');
},

/* ── NEW CHAT MODAL ────────────────────────────────────────────── */
openNewChatModal() {
    document.getElementById('newChatSearch').value = '';
    document.getElementById('newChatResults').innerHTML = '';
    new bootstrap.Modal('#newChatModal').show();
    setTimeout(()=>document.getElementById('newChatSearch').focus(), 300);
    this.searchUsers('', 'newChatResults');
},

startDirectChat(withUser) {
    bootstrap.Modal.getInstance('#newChatModal')?.hide();
    get('get_or_create_direct',{with:withUser}).then(res=>{
        if (res.status==='success') {
            this.loadConvs();
            setTimeout(()=>this.openConv(res.conv_id), 300);
        }
    });
},

/* ── CREATE GROUP MODAL ────────────────────────────────────────── */
openCreateGroupModal() {
    state.groupMembers = [];
    document.getElementById('groupName').value = '';
    document.getElementById('groupSearch').value = '';
    document.getElementById('groupResults').innerHTML = '';
    document.getElementById('groupSelectedMembers').innerHTML = '';
    new bootstrap.Modal('#createGroupModal').show();
    setTimeout(()=>document.getElementById('groupName').focus(), 300);
    this.searchUsers('','groupResults',true);
},

toggleGroupMember(usr) {
    const idx = state.groupMembers.findIndex(m=>m.usr_code===usr.usr_code);
    if (idx > -1) {
        state.groupMembers.splice(idx,1);
    } else {
        state.groupMembers.push(usr);
    }
    renderGroupSelectedMembers();
    // Re-render search results to update checkmarks
    const q = document.getElementById('groupSearch').value;
    this.searchUsers(q,'groupResults',true);
},

createGroup() {
    const name = document.getElementById('groupName').value.trim();
    if (!name) { showToast('Please enter a group name','error'); return; }
    if (state.groupMembers.length === 0) { showToast('Add at least one member','error'); return; }

    post('create_group',{name, members: state.groupMembers.map(m=>m.usr_code)}).then(res=>{
        if (res.status === 'success') {
            bootstrap.Modal.getInstance('#createGroupModal')?.hide();
            this.loadConvs();
            setTimeout(()=>this.openConv(res.conv_id), 300);
            showToast('Group created!','success');
        } else {
            showToast(res.message||'Failed to create group','error');
        }
    });
},

/* ── ADD MEMBER MODAL ──────────────────────────────────────────── */
openAddMemberModal() {
    document.getElementById('addMemberSearch').value = '';
    document.getElementById('addMemberResults').innerHTML = '';
    new bootstrap.Modal('#addMemberModal').show();
    setTimeout(()=>document.getElementById('addMemberSearch').focus(), 300);
    this.searchUsers('','addMemberResults',false,true);
},

addMember(usrCode) {
    post('add_participant',{conv_id:state.convId, usr_code:usrCode}).then(res=>{
        bootstrap.Modal.getInstance('#addMemberModal')?.hide();
        if (res.status==='success') {
            showToast('Member added','success');
            this.loadMsgs(state.convId, true);
        } else {
            showToast(res.message||'Failed','error');
        }
    });
},

removeMember(usrCode) {
    if (!confirm('Remove this member from the group?')) return;
    post('remove_participant',{conv_id:state.convId, usr_code:usrCode}).then(res=>{
        if (res.status==='success') {
            showToast('Member removed','success');
            this.loadInfoPanel();
        } else {
            showToast(res.message||'Failed','error');
        }
    });
},

leaveGroup() {
    if (!confirm('Leave this group?')) return;
    post('leave_group',{conv_id:state.convId}).then(res=>{
        if (res.status==='success') {
            clearInterval(state.pollingTimer);
            state.convId = null;
            document.getElementById('chatEmpty').classList.remove('d-none');
            document.getElementById('chatWindow').classList.add('d-none');
            document.getElementById('chatInfoPanel').classList.add('d-none');
            document.getElementById('dcmChatSidebar').classList.remove('hidden');
            this.loadConvs();
            showToast('You left the group','success');
        }
    });
},

/* ── SEARCH USERS ──────────────────────────────────────────────── */
searchUsers(q, targetId, forGroup=false, forAddMember=false) {
    const params = {q};
    if (forAddMember && state.convId) params.exclude_conv = state.convId;
    get('get_users', params).then(res=>{
        if (res.status !== 'success') return;
        const el = document.getElementById(targetId);
        if (!el) return;
        if (res.data.length === 0) {
            el.innerHTML = '<p class="text-muted small text-center py-3">No users found</p>';
            return;
        }
        el.innerHTML = res.data.map(u=>{
            const isSelected = forGroup && state.groupMembers.some(m=>m.usr_code===u.usr_code);
            const onlineHtml = u.online ? '<span class="dcm-online-dot" style="position:relative;display:inline-block;width:8px;height:8px;background:#22c55e;border-radius:50%;margin-left:4px"></span>' : '';
            if (forGroup) {
                return `<div class="d-flex align-items-center gap-2 py-2 px-1 rounded-2 mb-1 ${isSelected?'bg-light':''}" style="cursor:pointer" onclick='dcmChat.toggleGroupMember(${JSON.stringify(u)})'>
                    ${avatar(u.initials, u.color, 36, '.68rem')}
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-medium" style="font-size:.83rem">${esc(u.name)}${onlineHtml}</div>
                        <div>${roleBadge(u.role_label)}</div>
                    </div>
                    ${isSelected ? '<i class="bi bi-check-circle-fill text-theme-1 fs-5"></i>' : '<i class="bi bi-circle text-muted fs-5"></i>'}
                </div>`;
            } else if (forAddMember) {
                return `<div class="d-flex align-items-center gap-2 py-2 px-1 mb-1" style="cursor:pointer" onclick="dcmChat.addMember('${esc(u.usr_code)}')">
                    ${avatar(u.initials, u.color, 36, '.68rem')}
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-medium" style="font-size:.83rem">${esc(u.name)}${onlineHtml}</div>
                        <div>${roleBadge(u.role_label)}</div>
                    </div>
                    <button class="btn btn-sm btn-outline-theme rounded-pill px-2 py-0" style="font-size:.72rem">Add</button>
                </div>`;
            } else {
                return `<div class="d-flex align-items-center gap-2 py-2 px-1 mb-1" style="cursor:pointer" onclick="dcmChat.startDirectChat('${esc(u.usr_code)}')">
                    ${avatar(u.initials, u.color, 38, '.72rem')}
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-medium" style="font-size:.84rem">${esc(u.name)}${onlineHtml}</div>
                        <div>${roleBadge(u.role_label)}</div>
                    </div>
                    <i class="bi bi-chat-dots text-muted"></i>
                </div>`;
            }
        }).join('');
    });
},

/* ── INFO PANEL ────────────────────────────────────────────────── */
toggleInfo() {
    const panel = document.getElementById('chatInfoPanel');
    const isHidden = panel.classList.contains('d-none');
    panel.classList.toggle('d-none');
    if (isHidden) this.loadInfoPanel();
},

loadInfoPanel() {
    if (!state.convId) return;
    const panel = document.getElementById('infoPanelBody');
    panel.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-theme-1"></div></div>';

    if (state.convType === 'group') {
        get('get_group_info',{conv_id:state.convId}).then(res=>{
            if (res.status!=='success') return;
            const conv = res.conv;
            const isAdmin = res.my_role === 'admin';
            let html = `<div class="text-center mb-3">
                ${avatar(conv.name?.substring(0,2).toUpperCase()||'GR', avatarColor(conv.name||''), 60, '1rem')}
                <div class="fw-bold mt-2">${esc(conv.name)}</div>
                <div class="text-muted small">${res.members.length} members</div>
                ${isAdmin ? `<button class="btn btn-sm btn-outline-theme mt-2 rounded-pill" onclick="dcmChat.promptRename()"><i class="bi bi-pencil me-1"></i>Rename</button>` : ''}
            </div>
            <div class="fw-semibold small text-uppercase text-muted mb-2" style="letter-spacing:.06em">Members</div>`;

            html += res.members.map(m=>`
                <div class="dcm-member-item">
                    <div class="dcm-member-avatar" style="background:${esc(m.color)};color:#fff">${esc(m.initials)}</div>
                    <div class="flex-grow-1 min-w-0">
                        <div style="font-size:.82rem;font-weight:600">${esc(m.name)} ${m.is_me?'<span class="text-muted">(you)</span>':''}</div>
                        <div>${roleBadge(m.role_label)} ${m.conv_role==='admin'?'<span class="badge text-bg-secondary fw-normal" style="font-size:.6rem">Admin</span>':''}</div>
                    </div>
                    ${isAdmin && !m.is_me ? `<button class="btn btn-sm btn-link text-danger p-0" onclick="dcmChat.removeMember('${esc(m.usr_code)}')"><i class="bi bi-x-circle"></i></button>` : ''}
                </div>`).join('');

            if (isAdmin) {
                html += `<button class="btn btn-sm btn-outline-theme w-100 mt-3 rounded-pill" onclick="dcmChat.openAddMemberModal()"><i class="bi bi-person-plus me-1"></i>Add Member</button>`;
            }
            html += `<button class="btn btn-sm btn-outline-danger w-100 mt-2 rounded-pill" onclick="dcmChat.leaveGroup()"><i class="bi bi-box-arrow-left me-1"></i>Leave Group</button>`;

            panel.innerHTML = html;
            document.getElementById('infoPanelTitle').textContent = 'Group Info';
        });
    } else {
        // Direct chat: show user details
        const conv = state.convs.find(c=>c.id===state.convId);
        if (!conv) return;
        const onlineTxt = conv.online ? '<span class="text-success small"><i class="bi bi-circle-fill me-1" style="font-size:.4rem"></i>Online</span>'
                                       : '<span class="text-muted small">Last seen: '+(conv.last_seen ? formatFullTime(conv.last_seen) : 'unknown')+'</span>';
        panel.innerHTML = `<div class="text-center mb-3">
            ${avatar(conv.initials||'?', conv.color||'#999', 60, '1rem')}
            <div class="fw-bold mt-2">${esc(conv.name||'')}</div>
            ${roleBadge(conv.role_label||'')}
            <div class="mt-1">${onlineTxt}</div>
        </div>`;
        document.getElementById('infoPanelTitle').textContent = 'Contact Info';
    }
},

promptRename() {
    const name = prompt('New group name:', document.getElementById('chatHeaderName').textContent.trim());
    if (!name || !name.trim()) return;
    post('update_group',{conv_id:state.convId, name:name.trim()}).then(res=>{
        if (res.status==='success') {
            showToast('Group renamed','success');
            document.getElementById('chatHeaderName').textContent = name.trim();
            const conv = state.convs.find(c=>c.id===state.convId);
            if (conv) conv.name = name.trim();
            this.loadConvs();
            this.loadInfoPanel();
        }
    });
},

/* ── MOBILE ─────────────────────────────────────────────────────── */
showSidebar() {
    clearInterval(state.pollingTimer);
    state.convId = null;
    document.getElementById('dcmChatSidebar').classList.remove('hidden');
    document.getElementById('chatEmpty').classList.remove('d-none');
    document.getElementById('chatWindow').classList.add('d-none');
    document.getElementById('chatInfoPanel').classList.add('d-none');
    this.loadConvs();
},

/* ── INIT ──────────────────────────────────────────────────────── */
init() {
    this.loadConvs();

    // Global heartbeat (presence while page open)
    state.heartbeatTimer = setInterval(()=>{
        if (document.hidden) return;
        ajax(AJAX_BASE+'?action=heartbeat', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'heartbeat',typing_in:null}),credentials:'same-origin'});
    }, 25000);

    // Close emoji on outside click
    document.addEventListener('click', e=>{
        if (!e.target.closest('#emojiPicker') && !e.target.closest('.dcm-icon-btn')) {
            document.getElementById('emojiPicker')?.classList.add('d-none');
        }
    });

    // Open conv from URL param
    const urlParams = new URLSearchParams(window.location.search);
    const openWith  = urlParams.get('chat_with');
    if (openWith) {
        get('get_or_create_direct',{with:openWith}).then(res=>{
            if (res.status==='success') {
                this.loadConvs();
                setTimeout(()=>this.openConv(res.conv_id), 400);
            }
        });
    }

    // Cleanup on SPA navigation away
    if (window._dcmChatCleanup) window._dcmChatCleanup();
    window._dcmChatCleanup = ()=>{
        clearInterval(state.pollingTimer);
        clearInterval(state.heartbeatTimer);
        clearTimeout(state.typingTimer);
    };
}

}; // end dcmChat

/* ── RENDER HELPERS ─────────────────────────────────────────────── */
function renderConvList(convs) {
    const box = document.getElementById('convList');
    if (convs.length === 0) {
        box.innerHTML = '<div class="dcm-sidebar-empty"><i class="bi bi-chat-dots"></i><p class="small mb-2 fw-medium">No conversations yet</p><p class="small opacity-75">Start a new message or create a group</p></div>';
        return;
    }
    box.innerHTML = convs.map(c => {
        const onlineDot = c.online ? '<span class="dcm-online-dot"></span>' : '';
        const ava = `<div class="dcm-conv-avatar" style="background:${esc(c.color||'#6366f1')};color:#fff">${esc(c.initials||'?')}${onlineDot}</div>`;

        const preview = (() => {
            if (!c.last_message) return '<em>No messages yet</em>';
            const t = c.last_msg_type || 'text';
            const icons = {image:'📷 Photo',audio:'🎤 Voice note',video:'📹 Video',file:'📎 File',system:''};
            if (t !== 'text' && icons[t] !== undefined) return icons[t] || c.last_message;
            return esc(c.last_message);
        })();

        const badge = c.unread_count > 0 ? `<div class="dcm-conv-badge">${c.unread_count > 99 ? '99+' : c.unread_count}</div>` : '';
        const isActive = c.id === state.convId;

        return `<div class="dcm-conv-item${isActive?' active':''}" data-id="${c.id}" onclick="dcmChat.openConv(${c.id})">
            ${ava}
            <div class="dcm-conv-body">
                <div class="dcm-conv-name">${esc(c.name||'Unnamed')}</div>
                <div class="dcm-conv-preview">${preview}</div>
            </div>
            <div class="dcm-conv-meta">
                <div class="dcm-conv-time">${formatTime(c.last_message_at)}</div>
                ${badge}
            </div>
        </div>`;
    }).join('');
}

function renderConvHeader(conv) {
    const ava = document.getElementById('chatHeaderAvatar');
    ava.style.cssText = `background:${conv.color||'#6366f1'};color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;flex-shrink:0`;
    ava.textContent = conv.initials || '?';

    document.getElementById('chatHeaderName').textContent = conv.name || '';
    state.convType = conv.type;
    state.convName = conv.name || '';

    const sub = document.getElementById('chatHeaderSub');
    if (conv.type === 'group') {
        sub.textContent = (conv.member_count||'?') + ' members';
    } else {
        sub.innerHTML = conv.online
            ? '<span class="text-success"><i class="bi bi-circle-fill me-1" style="font-size:.4rem"></i>Online</span>'
            : (conv.last_seen ? 'Last seen: '+formatFullTime(conv.last_seen) : 'Offline');
    }
}

function buildContextMenu(conv) {
    const menu = document.getElementById('convContextMenu');
    let items = [];
    if (conv?.type === 'group') {
        items.push(`<li><a class="dropdown-item" href="#" onclick="dcmChat.openAddMemberModal();return false"><i class="bi bi-person-plus me-2"></i>Add Member</a></li>`);
        items.push(`<li><a class="dropdown-item" href="#" onclick="dcmChat.promptRename();return false"><i class="bi bi-pencil me-2"></i>Rename Group</a></li>`);
        items.push(`<li><hr class="dropdown-divider"></li>`);
        items.push(`<li><a class="dropdown-item text-danger" href="#" onclick="dcmChat.leaveGroup();return false"><i class="bi bi-box-arrow-left me-2"></i>Leave Group</a></li>`);
    }
    items.push(`<li><a class="dropdown-item" href="#" onclick="dcmChat.toggleInfo();return false"><i class="bi bi-info-circle me-2"></i>View Info</a></li>`);
    menu.innerHTML = items.join('');
}

function buildMsgFragment(msgs, reset=false, isNew=false) {
    const frag = document.createDocumentFragment();
    let lastDate = '';
    let lastSender = '';
    let lastMine = null;

    if (!reset) {
        // When appending new messages, get last known date from DOM
        const lastSep = document.querySelector('.dcm-date-sep:last-of-type');
        if (lastSep) lastDate = lastSep.dataset.date || '';
        const lastBubble = document.querySelector('[data-msg-id]:last-of-type');
        if (lastBubble) {
            lastSender = lastBubble.dataset.sender || '';
            lastMine   = lastBubble.dataset.mine === 'true';
        }
    }

    msgs.forEach((msg, idx) => {
        state.msgCache[msg.id] = msg;

        // Date separator
        const msgDate = formatDateSep(msg.created_at);
        if (msgDate !== lastDate) {
            lastDate = msgDate;
            const sep = document.createElement('div');
            sep.className = 'dcm-date-sep';
            sep.dataset.date = msgDate;
            sep.innerHTML = `<span>${esc(msgDate)}</span>`;
            frag.appendChild(sep);
            lastSender = ''; lastMine = null;
        }

        // System messages
        if (msg.type === 'system') {
            const sys = document.createElement('div');
            sys.className = 'dcm-system-msg';
            sys.innerHTML = `<span>${esc(msg.body||'')}</span>`;
            frag.appendChild(sys);
            lastSender = ''; lastMine = null;
            return;
        }

        const isMine   = msg.is_mine;
        const isGroup  = state.convType === 'group';
        const showSender = isGroup && !isMine && (msg.sender_code !== lastSender || lastMine !== isMine);

        const wrap = document.createElement('div');
        wrap.className = `dcm-msg-wrap ${isMine ? 'mine' : 'theirs'}`;
        wrap.dataset.msgId  = msg.id;
        wrap.dataset.sender = msg.sender_code;
        wrap.dataset.mine   = isMine;

        let inner = '';

        // Sender name (group, others only)
        if (showSender) {
            const info = userInfo(msg.sender_code, msg.sender_name);
            inner += `<div class="dcm-msg-sender">${esc(info.name)}</div>`;
        }

        // Reply reference
        if (msg.reply_to && state.msgCache[msg.reply_to]) {
            const ref = state.msgCache[msg.reply_to];
            inner += `<div class="dcm-reply-bubble">${esc(ref.sender_name||'')}: ${esc(ref.body || ref.type || '')}</div>`;
        }

        // Bubble
        inner += `<div class="dcm-msg-bubble" ondblclick="dcmChat.setReply(${msg.id})">`;

        if (msg.deleted) {
            inner += `<em class="text-muted" style="font-size:.8rem">Message deleted</em>`;
        } else if (msg.type === 'image') {
            const src = esc(msg.file_path||'');
            inner += `<img src="${src}" class="dcm-msg-img" onclick="dcmChat.openLightbox('${src}')" alt="Image" onerror="this.src='data_files/uploads/course_default.png'">`;
        } else if (msg.type === 'audio') {
            const label = (msg.body && msg.body !== 'Voice Note') ? esc(msg.body) : 'Voice Note';
            const cardId = `acard_${msg.id}`;
            inner += `<div class="dcm-audio-card paused" id="${cardId}">
                <div class="dcm-audio-thumb">
                    <div class="dcm-audio-thumb-inner">
                        <div class="dcm-audio-wave">
                            <span></span><span></span><span></span><span></span><span></span>
                            <span></span><span></span><span></span><span></span><span></span>
                        </div>
                        <div class="dcm-audio-label"><i class="bi bi-music-note-beamed"></i>${label}</div>
                    </div>
                </div>
                <audio controls class="dcm-audio-native" src="${esc(msg.file_path||'')}"
                    onplay="document.getElementById('${cardId}').classList.remove('paused')"
                    onpause="document.getElementById('${cardId}').classList.add('paused')"
                    onended="document.getElementById('${cardId}').classList.add('paused')">
                </audio>
            </div>`;
        } else if (msg.type === 'video') {
            inner += `<video controls class="dcm-msg-video" src="${esc(msg.file_path||'')}"></video>`;
        } else if (msg.type === 'file') {
            const icon = fileIcon(msg.file_name||'');
            inner += `<div class="dcm-msg-file">
                <i class="${esc(icon)} dcm-msg-file-icon"></i>
                <div class="dcm-msg-file-info">
                    <div class="dcm-msg-file-name">${esc(msg.file_name||'File')}</div>
                    <div class="dcm-msg-file-size">${fileSize(msg.file_size)}</div>
                </div>
                <a href="${esc(msg.file_path||'')}" download class="btn btn-sm btn-link p-0"><i class="bi bi-download"></i></a>
            </div>`;
        } else {
            // Text: linkify URLs
            const linked = linkify(esc(msg.body||''));
            inner += linked;
        }

        inner += `</div>`; // end bubble

        // Timestamp + status
        const time  = formatTime(msg.created_at);
        const ticks = getReadTick(msg, isMine);
        inner += `<div class="dcm-msg-meta">
            <span class="dcm-msg-time">${time}</span>
            ${isMine ? ticks : ''}
            ${isMine ? `<div class="dropdown d-inline-block">
                <button class="btn btn-link btn-sm p-0 dcm-msg-menu-btn text-muted" style="font-size:.7rem;opacity:0;transition:opacity .2s" data-bs-toggle="dropdown"><i class="bi bi-chevron-down"></i></button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:120px;font-size:.8rem">
                    <li><a class="dropdown-item" href="#" onclick="dcmChat.setReply(${msg.id});return false"><i class="bi bi-reply me-2"></i>Reply</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="dcmChat.deleteMsg(${msg.id});return false"><i class="bi bi-trash me-2"></i>Delete</a></li>
                </ul>
            </div>` : `<div class="dropdown d-inline-block">
                <button class="btn btn-link btn-sm p-0 dcm-msg-menu-btn text-muted" style="font-size:.7rem;opacity:0;transition:opacity .2s" data-bs-toggle="dropdown"><i class="bi bi-chevron-down"></i></button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:120px;font-size:.8rem">
                    <li><a class="dropdown-item" href="#" onclick="dcmChat.setReply(${msg.id});return false"><i class="bi bi-reply me-2"></i>Reply</a></li>
                </ul>
            </div>`}
        </div>`;

        wrap.innerHTML = inner;

        // Show menu on hover
        const menuBtn = wrap.querySelector('.dcm-msg-menu-btn');
        if (menuBtn) {
            wrap.addEventListener('mouseenter',()=>menuBtn.style.opacity='1');
            wrap.addEventListener('mouseleave',()=>menuBtn.style.opacity='0');
        }

        frag.appendChild(wrap);
        lastSender = msg.sender_code;
        lastMine   = isMine;
    });

    return frag;
}

function userInfo(code, name) {
    return { name: name || code || 'Unknown' };
}

function getReadTick(msg, isMine) {
    if (!isMine) return '';
    const maxRead = state.readBy.length > 0
        ? Math.max(...state.readBy.map(r => parseInt(r.last_read_msg_id)||0))
        : 0;
    const isRead  = maxRead >= msg.id;
    const cls     = isRead ? 'dcm-msg-tick read' : 'dcm-msg-tick';
    return `<i class="bi bi-check-all ${cls}" title="${isRead?'Read':'Sent'}"></i>`;
}

function updateReadTicks() {
    const maxRead = state.readBy.length > 0
        ? Math.max(...state.readBy.map(r => parseInt(r.last_read_msg_id)||0))
        : 0;
    document.querySelectorAll('.dcm-msg-wrap.mine[data-msg-id]').forEach(el=>{
        const id  = parseInt(el.dataset.msgId);
        const tick = el.querySelector('.dcm-msg-tick');
        if (tick) {
            tick.classList.toggle('read', maxRead >= id);
        }
    });
}

function updateConvPreview(convId, msg) {
    const conv = state.convs.find(c=>c.id===convId);
    if (!conv) return;
    conv.last_message    = msg.type==='text' ? msg.body : msg.type;
    conv.last_msg_type   = msg.type;
    conv.last_message_at = msg.created_at;
    conv.unread_count    = 0;
    // Move to top
    state.convs = [conv, ...state.convs.filter(c=>c.id!==convId)];
    renderConvList(state.convs);
}

function renderGroupSelectedMembers() {
    const el = document.getElementById('groupSelectedMembers');
    el.innerHTML = state.groupMembers.map(m=>
        `<span class="badge rounded-pill text-bg-light border d-flex align-items-center gap-1" style="font-size:.72rem">
            <span>${esc(m.name)}</span>
            <button class="btn btn-link btn-sm p-0 text-danger" style="line-height:1" onclick='dcmChat.toggleGroupMember(${JSON.stringify(m)})'>×</button>
        </span>`
    ).join('');
}

function linkify(text) {
    return text.replace(/(https?:\/\/[^\s<>"']+)/g, '<a href="$1" target="_blank" rel="noopener" class="text-theme-1">$1</a>');
}

dcmChat.openLightbox = function(src) {
    const lb = document.getElementById('imgLightbox');
    document.getElementById('lightboxImg').src = src;
    lb.classList.remove('d-none');
};

/* ── INIT ────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', ()=>dcmChat.init());
if (document.readyState !== 'loading') dcmChat.init();

})();
</script>
