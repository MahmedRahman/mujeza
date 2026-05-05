@extends('layouts.app')

@section('title', 'المحادثات')
@section('page_title', 'المحادثات - واتساب')

@section('content')
<style>
    .content { padding: 0 !important; }

    #chat-app {
        display: grid;
        grid-template-columns: 460px 1fr;
        height: calc(100vh - 112px);
        min-height: 520px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }

    #chat-sidebar {
        display: flex;
        flex-direction: column;
        background: #fff;
        min-height: 0;
        height: 100%;
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
    .sidebar-hd-right {
        display: flex;
        align-items: center;
        gap: 10px;
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

    #chats-list {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
        padding: 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        align-content: start;
    }
    #chats-list::-webkit-scrollbar { width: 4px; }
    #chats-list::-webkit-scrollbar-thumb { background: #d4af37; border-radius: 4px; }

    .chat-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 13px 13px 11px;
        cursor: pointer;
        background: #fff;
        transition: border-color .12s, box-shadow .12s, transform .12s;
        min-width: 0;
    }
    .chat-card.active {
        border-color: #d4af37;
        box-shadow: 0 0 0 2px rgba(212, 175, 55, .20);
        background: #fffcf2;
    }

    .chat-card:hover {
        border-color: #d4af37;
        box-shadow: 0 6px 16px rgba(17, 24, 39, .08);
        transform: translateY(-1px);
    }

    .chat-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }

    .chat-card-name {
        font-weight: 700;
        font-size: 15px;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #0f172a;
    }

    .chat-card-phone {
        margin-top: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 4px 10px;
    }

    .chat-card-date {
        font-size: 10px;
        color: #9ca3af;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .chat-card-preview {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 8px;
        line-height: 1.4;
        min-height: 30px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .chat-card-footer {
        margin-top: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }
    .switch-wrap {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 11px;
        color: #6b7280;
        user-select: none;
    }
    .switch {
        position: relative;
        width: 38px;
        height: 22px;
        display: inline-block;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .switch-slider {
        position: absolute;
        inset: 0;
        background: #d1d5db;
        border-radius: 999px;
        transition: .18s;
    }
    .switch-slider::before {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        left: 3px;
        top: 3px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,.15);
        transition: .18s;
    }
    .switch input:checked + .switch-slider {
        background: #16a34a;
    }
    .switch input:checked + .switch-slider::before {
        transform: translateX(16px);
    }
    .switch input:disabled + .switch-slider {
        opacity: .6;
        cursor: not-allowed;
    }

    #chat-main {
        display: flex;
        flex-direction: column;
        min-height: 0;
        border-right: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    #chat-main-header {
        padding: 13px 16px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex-shrink: 0;
    }
    #chat-main-title {
        font-size: 14px;
        font-weight: 800;
        color: #111827;
    }
    #chat-main-subtitle {
        font-size: 12px;
        color: #6b7280;
    }
    #messages-area {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .msg-row {
        display: flex;
        width: 100%;
    }
    .msg-row.sent { justify-content: flex-end; }
    .msg-row.recv { justify-content: flex-start; }
    .msg-bubble {
        max-width: 74%;
        border-radius: 14px;
        padding: 9px 12px 7px;
        font-size: 13px;
        line-height: 1.45;
        word-break: break-word;
        white-space: pre-wrap;
    }
    .msg-bubble.sent {
        background: #d4af37;
        color: #111;
        border-bottom-right-radius: 4px;
    }
    .msg-bubble.recv {
        background: #fff;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 4px;
    }
    .msg-time {
        margin-top: 4px;
        font-size: 10px;
        color: #6b7280;
        text-align: left;
        direction: ltr;
    }
    .date-sep {
        align-self: center;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        color: #6b7280;
        background: #eef2f7;
        margin: 2px 0;
    }

    @media (max-width: 1180px) {
        #chat-app { grid-template-columns: 400px 1fr; }
        #chats-list { grid-template-columns: 1fr; }
    }
    @media (max-width: 900px) {
        #chat-app { grid-template-columns: 1fr; }
        #chat-main { min-height: 420px; border-right: 0; border-top: 1px solid #e5e7eb; }
    }
</style>

<div id="chat-app">
    <div id="chat-sidebar">
        <div class="sidebar-hd">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:18px;">💬</span>
                <span style="font-weight:800;font-size:14px;">المحادثات</span>
            </div>
            <div class="sidebar-hd-right">
                <label class="switch-wrap" title="تشغيل/إيقاف الرد الآلي لكل المحادثات">
                    <span id="global-auto-reply-label">الرد الآلي: متوقف</span>
                    <span class="switch">
                        <input id="global-auto-reply-toggle" type="checkbox" onchange="toggleGlobalAutoReply(this)">
                        <span class="switch-slider"></span>
                    </span>
                </label>
                <span id="status-badge" style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:#f3f4f6;color:#9ca3af;">...</span>
            </div>
        </div>

        <div class="sidebar-search">
            <input id="search-input" type="text" placeholder="🔍  بحث في المحادثات..." onkeyup="filterChats()">
        </div>

        <div id="chats-list">
            <div style="grid-column:1/-1;text-align:center;padding:40px 10px;color:#9ca3af;font-size:13px;">جاري التحميل...</div>
        </div>
    </div>

    <div id="chat-main">
        <div id="chat-main-header">
            <div id="chat-main-title">اختر محادثة</div>
            <div id="chat-main-subtitle">اضغط على أي كارد لعرض الرسائل</div>
        </div>
        <div id="messages-area">
            <div style="margin:auto;text-align:center;color:#9ca3af;font-size:13px;">لا توجد محادثة محددة</div>
        </div>
    </div>
</div>

<script>
let allChats = [];
let globalAutoReplyEnabled = false;
let currentChatId = null;
let pollTimer = null;
let lastMessageSignature = '';

document.addEventListener('DOMContentLoaded', () => {
    checkStatus();
    loadAutoReplySettings();
    loadChats();
});

async function checkStatus() {
    try {
        const r = await fetch('{{ route("whatsapp.status") }}');
        const d = await r.json();
        const b = document.getElementById('status-badge');
        if (d.connected) {
            b.style.cssText = 'font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:#dcfce7;color:#166534;';
            b.textContent = '● متصل';
        } else {
            b.style.cssText = 'font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:#fee2e2;color:#991b1b;';
            b.textContent = '● غير متصل';
        }
    } catch (e) {}
}

async function loadChats() {
    try {
        const r = await fetch('{{ route("whatsapp.chats") }}');
        if (!r.ok) {
            showChatsError('تعذر تحميل المحادثات');
            return;
        }
        allChats = await r.json();
        renderCurrentList();
        if (currentChatId) {
            const selected = getChatById(currentChatId);
            if (selected) {
                renderChatHeader(selected);
            } else {
                currentChatId = null;
                clearInterval(pollTimer);
                document.getElementById('chat-main-title').textContent = 'اختر محادثة';
                document.getElementById('chat-main-subtitle').textContent = 'اضغط على أي كارد لعرض الرسائل';
                document.getElementById('messages-area').innerHTML = '<div style="margin:auto;text-align:center;color:#9ca3af;font-size:13px;">لا توجد محادثة محددة</div>';
            }
        }
    } catch (e) {
        showChatsError('خطأ في الاتصال');
    }
}

async function loadAutoReplySettings() {
    try {
        const r = await fetch('{{ route("whatsapp.auto_reply.settings") }}');
        if (!r.ok) return;
        const data = await r.json();
        globalAutoReplyEnabled = Boolean(data.global_enabled);
        renderGlobalAutoReplyState();
    } catch (e) {}
}

function renderChats(chats) {
    const el = document.getElementById('chats-list');
    if (!chats.length) {
        el.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px 10px;color:#9ca3af;font-size:13px;">لا توجد محادثات</div>';
        return;
    }

    el.innerHTML = chats.map(c => {
        const phone = getChatPhone(c);
        const safeName = esc(c.name || 'بدون اسم');
        const safePhone = esc(phone);
        const phonePrefix = phone === 'غير متاح' ? '' : '📞 ';
        const safeDate = esc(formatChatDate(c.timestamp));
        const isActive = currentChatId === c.id ? ' active' : '';
        return `<div class="chat-card${isActive}" data-chat-id="${esc(c.id)}" onclick="openChat(this.dataset.chatId)">
            <div class="chat-card-top">
                <div style="min-width:0;">
                    <div class="chat-card-name">${safeName}</div>
                    <div class="chat-card-phone">${phonePrefix}${safePhone}</div>
                </div>
                <span class="chat-card-date">${safeDate}</span>
            </div>
            <div class="chat-card-preview">${esc(c.last_message)}</div>
            <div class="chat-card-footer">
                <label class="switch-wrap" title="تشغيل الرد الآلي لهذا الرقم فقط" onclick="event.stopPropagation()">
                    <span>${c.auto_reply_enabled ? 'مفعل' : 'متوقف'}</span>
                    <span class="switch">
                        <input type="checkbox" data-chat-id="${esc(c.id)}" ${c.auto_reply_enabled ? 'checked' : ''} onchange="toggleChatAutoReply(event)" onclick="event.stopPropagation()">
                        <span class="switch-slider"></span>
                    </span>
                </label>
                ${c.unread > 0 ? `<span style="background:#d4af37;color:#111;border-radius:12px;padding:1px 7px;font-size:11px;font-weight:700;flex-shrink:0;">${c.unread}</span>` : ''}
            </div>
        </div>`;
    }).join('');
}

function showChatsError(msg) {
    document.getElementById('chats-list').innerHTML =
        `<div style="grid-column:1/-1;text-align:center;padding:24px 12px;">
            <div style="color:#dc2626;font-weight:700;margin-bottom:8px;">⚠ ${esc(msg)}</div>
            <a href="{{ route('settings.index') }}" style="font-size:12px;color:#1d4ed8;">الإعدادات</a>
         </div>`;
}

function filterChats() {
    renderCurrentList();
}

function renderGlobalAutoReplyState() {
    const input = document.getElementById('global-auto-reply-toggle');
    const label = document.getElementById('global-auto-reply-label');
    if (!input || !label) return;
    input.checked = globalAutoReplyEnabled;
    label.textContent = globalAutoReplyEnabled ? 'الرد الآلي: مفعل' : 'الرد الآلي: متوقف';
}

async function toggleGlobalAutoReply(el) {
    const nextValue = Boolean(el.checked);
    const prevValue = globalAutoReplyEnabled;
    el.disabled = true;

    try {
        const r = await fetch('{{ route("whatsapp.auto_reply.global") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ enabled: nextValue }),
        });

        if (!r.ok) {
            throw new Error('request_failed');
        }

        globalAutoReplyEnabled = nextValue;
        renderGlobalAutoReplyState();
        await loadChats();
    } catch (e) {
        globalAutoReplyEnabled = prevValue;
        renderGlobalAutoReplyState();
        alert('تعذر تحديث حالة الرد الآلي العامة.');
    } finally {
        el.disabled = false;
    }
}

async function toggleChatAutoReply(event) {
    const input = event.target;
    const chatId = String(input.dataset.chatId ?? '');
    if (!chatId) return;

    const nextValue = Boolean(input.checked);
    const prevValue = !nextValue;
    input.disabled = true;

    try {
        const r = await fetch('{{ route("whatsapp.auto_reply.chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ chat_id: chatId, enabled: nextValue }),
        });

        if (!r.ok) {
            throw new Error('request_failed');
        }

        allChats = allChats.map(c => c.id === chatId ? { ...c, auto_reply_enabled: nextValue, auto_reply_overridden: true } : c);
        renderCurrentList();
    } catch (e) {
        input.checked = prevValue;
        alert('تعذر تحديث حالة الرد الآلي لهذا الرقم.');
    } finally {
        input.disabled = false;
    }
}

function renderCurrentList() {
    const q = document.getElementById('search-input').value.toLowerCase();
    const filtered = allChats.filter(c =>
        String(c.name ?? '').toLowerCase().includes(q)
        || String(c.id ?? '').toLowerCase().includes(q)
        || getChatPhone(c).toLowerCase().includes(q)
    );
    renderChats(filtered);
}

function getChatById(chatId) {
    return allChats.find(c => c.id === chatId) || null;
}

function openChat(chatId) {
    const chat = getChatById(chatId);
    if (!chat) return;

    currentChatId = chatId;
    renderCurrentList();
    renderChatHeader(chat);
    loadMessages(true);

    clearInterval(pollTimer);
    pollTimer = setInterval(() => loadMessages(false), 4000);
}

function renderChatHeader(chat) {
    const phone = getChatPhone(chat);
    document.getElementById('chat-main-title').textContent = String(chat.name || 'عميل واتساب');
    document.getElementById('chat-main-subtitle').textContent = phone === 'غير متاح'
        ? 'رقم الهاتف غير متاح'
        : `📞 ${phone}`;
}

async function loadMessages(forceRefresh) {
    if (!currentChatId) return;
    const area = document.getElementById('messages-area');

    if (forceRefresh) {
        area.innerHTML = '<div style="margin:auto;text-align:center;color:#9ca3af;font-size:13px;">جاري تحميل الرسائل...</div>';
    }

    try {
        const r = await fetch(`{{ route('whatsapp.messages') }}?chat_id=${encodeURIComponent(currentChatId)}`);
        if (!r.ok) return;
        const msgs = await r.json();
        const signature = `${msgs.length}:${msgs[msgs.length - 1]?.id ?? ''}`;

        if (!forceRefresh && signature === lastMessageSignature) {
            return;
        }

        lastMessageSignature = signature;
        renderMessages(msgs);
    } catch (e) {}
}

function renderMessages(msgs) {
    const area = document.getElementById('messages-area');
    if (!Array.isArray(msgs) || msgs.length === 0) {
        area.innerHTML = '<div style="margin:auto;text-align:center;color:#9ca3af;font-size:13px;">لا توجد رسائل في هذه المحادثة</div>';
        return;
    }

    const sortedMessages = [...msgs].sort((a, b) => {
        const tsA = Number(a?.timestamp ?? 0);
        const tsB = Number(b?.timestamp ?? 0);
        if (tsA !== tsB) return tsA - tsB;
        return String(a?.id ?? '').localeCompare(String(b?.id ?? ''));
    });

    let html = '';
    let lastDate = '';
    for (const m of sortedMessages) {
        const dateLabel = formatDateLabel(m.timestamp);
        if (dateLabel !== lastDate) {
            html += `<div class="date-sep">${esc(dateLabel)}</div>`;
            lastDate = dateLabel;
        }

        const cls = m.from_me ? 'sent' : 'recv';
        html += `<div class="msg-row ${cls}">
            <div class="msg-bubble ${cls}">
                ${esc(m.text || '')}
                <div class="msg-time">${esc(formatMessageTime(m.timestamp))}</div>
            </div>
        </div>`;
    }

    area.innerHTML = html;
    area.scrollTop = area.scrollHeight;
}

function esc(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function getChatPhone(chat) {
    const apiPhone = String(chat?.phone ?? '').trim();
    if (apiPhone) return apiPhone;
    return 'غير متاح';
}

function formatChatDate(ts) {
    if (!ts) return '';
    const d = new Date(ts * 1000);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) {
        return d.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
    }
    return d.toLocaleDateString('ar-EG', { day: 'numeric', month: 'numeric' });
}

function formatMessageTime(ts) {
    if (!ts) return '';
    return new Date(ts * 1000).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
}

function formatDateLabel(ts) {
    if (!ts) return '';
    const d = new Date(ts * 1000);
    const now = new Date();
    const diff = Math.floor((now - d) / 86400000);
    if (diff === 0) return 'اليوم';
    if (diff === 1) return 'أمس';
    return d.toLocaleDateString('ar-EG', { day: 'numeric', month: 'long' });
}
</script>
@endsection
