// public/assets/js/chat.js
document.addEventListener('DOMContentLoaded', () => {
  try {
    const BASE = (window.CHAT_BASE || '.').replace(/\/+$/, '');

    /* ===== 1) LAUNCHER (nút tròn + menu) ===== */
    let launcher = document.getElementById('chat-launcher');
    if (!launcher) {
      launcher = document.createElement('div');
      launcher.id = 'chat-launcher';
      launcher.className = 'chat-launcher';
      document.body.appendChild(launcher);
    }
    launcher.innerHTML = `
      <button class="fab" type="button" aria-label="Chat">💬</button>
      <div class="menu" id="chatModeMenu">
        <h6>Chọn chế độ</h6>
        <button type="button" data-mode="support">Liên hệ CSKH</button>
        <button type="button" data-mode="bot" disabled>Chat tự động (sắp có)</button>
      </div>
    `;
    const fab  = launcher.querySelector('.fab');
    const menu = launcher.querySelector('#chatModeMenu');

    const openMenu  = () => { menu.classList.add('open'); };
    const closeMenu = () => { menu.classList.remove('open'); };
    const toggleMenu= () => { menu.classList.toggle('open'); };

    fab.addEventListener('click', (e) => { e.preventDefault(); toggleMenu(); });
    document.addEventListener('click', (e) => { if (!launcher.contains(e.target)) closeMenu(); });

    /* ===== 2) PANEL CSKH ===== */
    let bubble = document.getElementById('support-bubble');
    if (!bubble) {
      bubble = document.createElement('div');
      bubble.id = 'support-bubble';
      bubble.className = 'chat-bubble';
      document.body.appendChild(bubble);
    }
    bubble.innerHTML = `
      <div class="chat-panel" style="display:none">
        <div class="chat-head">
          <strong>Hỗ trợ • CSKH</strong>
          <button class="chat-close" type="button" aria-label="Đóng">✕</button>
        </div>
        <div class="chat-body"><div class="chat-messages" id="chatMessages"></div></div>
        <div class="chat-foot">
          <form id="chatForm" autocomplete="off">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." />
            <button type="submit">Gửi</button>
          </form>
        </div>
      </div>
    `;
    const panel  = bubble.querySelector('.chat-panel');
    const closeB = bubble.querySelector('.chat-close');
    const msgBox = bubble.querySelector('#chatMessages');
    const formEl = bubble.querySelector('#chatForm');
    const input  = bubble.querySelector('#chatInput');

    const openPanel  = () => { panel.style.display = 'block'; };
    const closePanel = () => { panel.style.display = 'none'; };
    closeB.addEventListener('click', closePanel);

    // Nút navbar (nếu có) → mở menu để chọn
    ['openSupportChatBtn','openSupportChatLink'].forEach(id=>{
      const el = document.getElementById(id);
      if (el) el.addEventListener('click', e => { e.preventDefault(); openMenu(); });
    });

    // Từ menu launcher:
    launcher.querySelector('button[data-mode="support"]').addEventListener('click', (e)=>{
      e.preventDefault(); closeMenu(); openPanel(); if (!state.chatId) openChat();
    });
    launcher.querySelector('button[data-mode="bot"]').addEventListener('click', (e)=>{
      e.preventDefault(); closeMenu(); alert('Chat tự động (AI) sẽ sớm có!');
    });

    /* ===== 3) STATE & HELPERS ===== */
    const state = { chatId:null, lastId:0, timer:null, isSending:false, stopPull:false };

    const escapeHtml = s => (s || '').replace(/[&<>"']/g, p => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[p]));
    const render = (list=[]) => {
      list.forEach(m=>{
        const d = document.createElement('div');
        d.className = 'msg ' + (m.sender_type === 'user' ? 'user' : 'staff');
        d.innerHTML = `${escapeHtml(m.message)}<small>${m.ts || ''}</small>`;
        msgBox.appendChild(d);
      });
      if (list.length) msgBox.scrollTop = msgBox.scrollHeight;
    };

    /* ===== 4) API ===== */
    async function openChat(){
      try{
        const r = await fetch(`${BASE}/api/chat/open.php`, { credentials: 'same-origin' });
        const j = await r.json();
        if (j && j.ok) { state.chatId = j.chat_id; startPull(); }
      }catch(e){ console.error('openChat error', e); }
    }
    async function pull(){
      if (!state.chatId || state.stopPull) return;
      try{
        const r = await fetch(`${BASE}/api/chat/pull.php?chat_id=${state.chatId}&since_id=${state.lastId}`, { credentials: 'same-origin' });
        const j = await r.json();
        if (j && j.ok) {
          render(j.messages || []);
          state.lastId = j.last_id || state.lastId;
        }
      }catch(e){ console.error('pull error', e); }
    }
    function startPull(){ pull(); if (state.timer) clearInterval(state.timer); state.timer = setInterval(pull, 2000); }

    /* ===== 5) SEND (không bị nhân đôi) ===== */
    formEl.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if (state.isSending) return;

      const text = input.value.trim();
      if (!text) return;

      if (!state.chatId) await openChat();

      state.isSending = true;
      state.stopPull  = true;
      if (state.timer) { clearInterval(state.timer); state.timer = null; }

      try{
        const r = await fetch(`${BASE}/api/chat/send.php`, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ chat_id: state.chatId, message: text }).toString()
        });
        const j = await r.json().catch(()=>null);

        if (j && j.ok) {
          if (j.id) {
            const newId = parseInt(j.id, 10) || 0;
            if (newId > state.lastId) state.lastId = newId;
          }
          render([{ sender_type:'user', message:text,
            ts:new Date().toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'}) }]);
          input.value = '';
          await pull();
        }
      }catch(err){ console.error('send error', err); }
      finally{
        state.isSending = false;
        state.stopPull  = false;
        if (!state.timer) state.timer = setInterval(pull, 2000);
      }
    });

  } catch (err) {
    console.error('chat.js init error', err);
  }
});
