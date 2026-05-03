@extends('layouts.app')

@section('title', 'المحادثات')
@section('page_title', 'المحادثات - واتساب')

@section('content')
<style>
    .content { padding: 0 !important; }

    #chat-app {
        display: grid;
        grid-template-columns: 300px 1fr;
        height: calc(100vh - 112px);
        min-height: 520px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }

    /* ── Sidebar ─────────────────────────────── */
    #chat-sidebar {
        display: flex;
        flex-direction: column;
        border-left: 1px solid #e5e7eb;
        background: #fff;
        min-height: 0;
        overflow: hidden;
    }
    .sidebar-hd {
        padding: 14px 16px;
        background: #f8f2de;
        border-bottom: 1px solid #efe3b7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .sidebar-search {
        padding: 8px 12px;
        border-bottom: 1px solid #f0f0f0;
        flex-shrink: 0;
    }
    .sidebar-search input {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 7px 14px;
        font-family: inherit;
        font-size: 13px;
        background: #f9fafb;
        box-sizing: border-box;
        outline: none;
    }
    .sidebar-search input:focus { border-color: #d4af37; background: #fff; }

    #chats-list { flex: 1; overflow-y: auto; min-height: 0; }
    #chats-list::-webkit-scrollbar { width: 4px; }
    #chats-list::-webkit-scrollbar-thumb { background: #d4af37; border-radius: 4px; }

    .chat-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f5f5f5;
        transition: background .12s;
    }
    .chat-row:hover { background: #fffcf2; }
    .chat-row.active { background: #fff8e1; border-right: 3px solid #d4af37; }
    .chat-avatar {
        width: 42px; height: 42px;
        border-radius: 50%;
        background: #d4af37;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 17px; color: #111;
        flex-shrink: 0;
    }
    .chat-row-info { flex: 1; min-width: 0; }
    .chat-row-name {
        font-weight: 700; font-size: 13px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        color: #111827;
    }
    .chat-row-preview {
        font-size: 11px; color: #9ca3af;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        margin-top: 2px;
    }

    /* ── Main ──────────────────────────────────── */
    #chat-main {
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
        background: #f9fafb;
    }
    #chat-header {
        padding: 12px 18px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    /* ── Messages ───────────────────────────────── */
    #messages-area {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        /* LTR so flex-end = right, flex-start = left */
        direction: ltr;
    }
    #messages-area::-webkit-scrollbar { width: 4px; }
    #messages-area::-webkit-scrollbar-thumb { background: #d4af37; border-radius: 4px; }

    .msg-row { display: flex; width: 100%; }
    .msg-row.sent  { justify-content: flex-end;   }
    .msg-row.recv  { justify-content: flex-start;  }

    .msg-bubble {
        max-width: 68%;
        padding: 9px 13px 6px;
        font-size: 14px;
        line-height: 1.55;
        word-break: break-word;
        white-space: pre-wrap;
        direction: rtl;
        text-align: right;
    }
    .msg-bubble.sent {
        background: #d4af37;
        color: #111;
        border-radius: 16px 16px 3px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .msg-bubble.recv {
        background: #ffffff;
        color: #1f2937;
        border-radius: 16px 16px 16px 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        border: 1px solid #e5e7eb;
    }
    .msg-time {
        font-size: 10px;
        margin-top: 4px;
        direction: ltr;
        text-align: right;
    }
    .msg-bubble.sent  .msg-time { color: #7c6a0e; }
    .msg-bubble.recv  .msg-time { color: #9ca3af; }

    /* date separator */
    .date-sep {
        text-align: center;
        font-size: 11px;
        color: #9ca3af;
        margin: 8px 0 4px;
        direction: rtl;
    }

    /* ── Input ───────────────────────────────────── */
    #msg-bar {
        padding: 10px 14px;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 8px;
        align-items: flex-end;
        flex-shrink: 0;
    }
    #msg-input {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 9px 16px;
        font-family: inherit;
        font-size: 14px;
        resize: none;
        max-height: 100px;
        line-height: 1.5;
        box-sizing: border-box;
        outline: none;
        direction: rtl;
        background: #f9fafb;
        transition: border .15s;
    }
    #msg-input:focus { border-color: #d4af37; background: #fff; }
    #send-btn {
        border: none;
        background: #d4af37;
        color: #111;
        width: 42px; height: 42px;
        border-radius: 50%;
        font-size: 18px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: background .15s;
    }
    #send-btn:hover { background: #c9a22a; }
    #send-btn:disabled { background: #e5e7eb; color: #9ca3af; cursor: default; }
</style>

<div id="chat-app">

    {{-- ══ Sidebar ══ --}}
    <div id="chat-sidebar">
        <div class="sidebar-hd">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:18px;">💬</span>
                <span style="font-weight:800;font-size:14px;">المحادثات</span>
            </div>
            <span id="status-badge" style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:#f3f4f6;color:#9ca3af;">...</span>
        </div>

        <div class="sidebar-search">
            <input id="search-input" type="text" placeholder="🔍  بحث في المحادثات..." onkeyup="filterChats()">
        </div>

        <div id="chats-list">
            <div style="text-align:center;padding:40px 10px;color:#9ca3af;font-size:13px;">جاري التحميل...</div>
        </div>
    </div>

    {{-- ══ Main ══ --}}
    <div id="chat-main">

        <div id="chat-header">
            <div id="chat-avatar" style="width:42px;height:42px;border-radius:50%;background:#d4af37;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;color:#111;flex-shrink:0;">
                💬
            </div>
            <div style="min-width:0;flex:1;">
                <div id="chat-name" style="font-weight:800;font-size:15px;color:#111827;">اختر محادثة</div>
                <div id="chat-phone" style="font-size:11px;color:#6b7280;margin-top:1px;"></div>
            </div>
            <div id="chat-typing" style="font-size:12px;color:#16a34a;display:none;">يكتب...</div>
        </div>

        <div id="messages-area">
            <div style="margin:auto;text-align:center;color:#9ca3af;font-size:14px;direction:rtl;">
                👈 اختر محادثة لعرض الرسائل
            </div>
        </div>

        <div id="msg-bar">
            <button onclick="sendMessage()" id="send-btn" title="إرسال">➤</button>
            <textarea id="msg-input" placeholder="اكتب رسالة..." rows="1"
                oninput="autoResize(this)" onkeydown="handleEnter(event)"></textarea>
        </div>
    </div>
</div>

<script>
let currentChatId   = null;
let currentChatName = '';
let allChats        = [];
let pollTimer       = null;
let lastMessageId   = null;
let lastCount       = 0;

// ── Bootstrap ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    checkStatus();
    loadChats();
});

// ── Connection status ──────────────────────────────────────────────────────
async function checkStatus() {
    try {
        const r = await fetch('{{ route("whatsapp.status") }}');
        const d = await r.json();
        const b = document.getElementById('status-badge');
        if (d.connected) {
            b.style.cssText = 'font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:#dcfce7;color:#166534;';
            b.textContent   = '● متصل';
        } else {
            b.style.cssText = 'font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:#fee2e2;color:#991b1b;';
            b.textContent   = '● غير متصل';
        }
    } catch(e) {}
}

// ── Load chats list ────────────────────────────────────────────────────────
async function loadChats() {
    try {
        const r = await fetch('{{ route("whatsapp.chats") }}');
        if (!r.ok) { showChatsError('تعذر تحميل المحادثات'); return; }
        allChats = await r.json();
        renderChats(allChats);
    } catch(e) { showChatsError('خطأ في الاتصال'); }
}

function renderChats(chats) {
    const el = document.getElementById('chats-list');
    if (!chats.length) {
        el.innerHTML = '<div style="text-align:center;padding:40px 10px;color:#9ca3af;font-size:13px;">لا توجد محادثات</div>';
        return;
    }
    el.innerHTML = chats.map(c => {
        const active  = currentChatId === c.id ? ' active' : '';
        const initials = esc(c.name).charAt(0).toUpperCase();
        return `<div class="chat-row${active}" data-id="${esc(c.id)}" onclick="openChat('${esc(c.id)}','${esc(c.name)}')">
            <div class="chat-avatar">${initials}</div>
            <div class="chat-row-info">
                <div class="chat-row-name">${esc(c.name)}</div>
                <div class="chat-row-preview">${esc(c.last_message)}</div>
            </div>
            ${c.unread > 0 ? `<span style="background:#d4af37;color:#111;border-radius:12px;padding:1px 7px;font-size:11px;font-weight:700;flex-shrink:0;">${c.unread}</span>` : ''}
        </div>`;
    }).join('');
}

function showChatsError(msg) {
    document.getElementById('chats-list').innerHTML =
        `<div style="text-align:center;padding:24px 12px;">
            <div style="color:#dc2626;font-weight:700;margin-bottom:8px;">⚠ ${esc(msg)}</div>
            <a href="{{ route('settings.index') }}" style="font-size:12px;color:#1d4ed8;">الإعدادات</a>
         </div>`;
}

function filterChats() {
    const q = document.getElementById('search-input').value.toLowerCase();
    renderChats(allChats.filter(c =>
        c.name.toLowerCase().includes(q) || c.id.toLowerCase().includes(q)
    ));
}

// ── Open a chat ────────────────────────────────────────────────────────────
async function openChat(id, name) {
    currentChatId   = id;
    currentChatName = name;
    lastMessageId   = null;
    lastCount       = 0;

    document.getElementById('chat-name').textContent  = name;
    document.getElementById('chat-phone').textContent = id.replace(/@.+/, '');
    document.getElementById('chat-avatar').textContent = name.charAt(0).toUpperCase();

    document.querySelectorAll('.chat-row').forEach(el => el.classList.remove('active'));
    const row = document.querySelector(`.chat-row[data-id="${CSS.escape(id)}"]`);
    if (row) row.classList.add('active');

    document.getElementById('messages-area').innerHTML =
        '<div style="margin:auto;text-align:center;color:#9ca3af;font-size:13px;direction:rtl;">جاري تحميل الرسائل...</div>';

    await loadMessages();

    clearInterval(pollTimer);
    pollTimer = setInterval(loadMessages, 3000);
}

// ── Load / poll messages ───────────────────────────────────────────────────
async function loadMessages() {
    if (!currentChatId) return;
    try {
        const r = await fetch(`{{ route('whatsapp.messages') }}?chat_id=${encodeURIComponent(currentChatId)}`);
        if (!r.ok) return;
        const msgs = await r.json();
        renderMessages(msgs);
    } catch(e) {}
}

// ── Render messages ────────────────────────────────────────────────────────
function renderMessages(msgs) {
    const area = document.getElementById('messages-area');

    if (!msgs.length) {
        area.innerHTML = '<div style="margin:auto;text-align:center;color:#9ca3af;font-size:13px;direction:rtl;">لا توجد رسائل بعد</div>';
        lastCount = 0; lastMessageId = null;
        return;
    }

    const newestId = msgs[msgs.length - 1]?.id;
    if (msgs.length === lastCount && newestId === lastMessageId) return;
    lastCount     = msgs.length;
    lastMessageId = newestId;

    let html = '';
    let lastDate = '';

    msgs.forEach(m => {
        const dateStr = formatDate(m.timestamp);
        if (dateStr !== lastDate) {
            html += `<div class="date-sep">${dateStr}</div>`;
            lastDate = dateStr;
        }

        const cls    = m.from_me ? 'sent' : 'recv';
        const tick   = m.from_me ? '<span style="color:#7c6a0e;">✓✓</span> ' : '';

        html += `<div class="msg-row ${cls}">
            <div class="msg-bubble ${cls}">
                ${esc(m.text)}
                <div class="msg-time">${tick}${formatTime(m.timestamp)}</div>
            </div>
        </div>`;
    });

    area.innerHTML = html;
    area.scrollTop = area.scrollHeight;
}

// ── Send message ───────────────────────────────────────────────────────────
async function sendMessage() {
    if (!currentChatId) return;
    const input = document.getElementById('msg-input');
    const text  = input.value.trim();
    if (!text) return;

    const btn = document.getElementById('send-btn');
    btn.disabled = true;

    try {
        const r = await fetch('{{ route("whatsapp.send") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ chat_id: currentChatId, text })
        });

        if (r.ok) {
            input.value = '';
            input.style.height = 'auto';
            lastCount = 0; lastMessageId = null; // force re-render
            setTimeout(loadMessages, 800);
        } else {
            const err = await r.json();
            alert('خطأ: ' + (err.error ?? 'فشل الإرسال'));
        }
    } catch(e) { alert('خطأ في الاتصال'); }

    btn.disabled = false;
}

// ── Helpers ────────────────────────────────────────────────────────────────
function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 100) + 'px';
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatTime(ts) {
    if (!ts) return '';
    return new Date(ts * 1000).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
}

function formatDate(ts) {
    if (!ts) return '';
    const d    = new Date(ts * 1000);
    const now  = new Date();
    const diff = Math.floor((now - d) / 86400000);
    if (diff === 0) return 'اليوم';
    if (diff === 1) return 'أمس';
    return d.toLocaleDateString('ar-EG', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>
@endsection
