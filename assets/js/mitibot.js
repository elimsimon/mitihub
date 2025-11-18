(function(){
  // MitiBot v1 - Forestry-focused assistant widget
  const API_PATH = '/api/mitibot.php';
  const WIDGET_ID = 'mitibot-root';
  const STYLE_ID = 'mitibot-style';

  const css = `
  .mitibot-fab{position:fixed;right:18px;bottom:18px;z-index:9999;background:#16a34a;color:#fff;border:none;border-radius:50px;padding:12px 16px;display:flex;gap:8px;align-items:center;box-shadow:0 8px 24px rgba(0,0,0,.15);cursor:pointer;font-weight:600}
  .mitibot-fab .dot{width:8px;height:8px;border-radius:50%;background:#fff;opacity:.8}
  .mitibot-panel{position:fixed;right:18px;bottom:78px;width:360px;max-width:calc(100vw - 24px);height:520px;max-height:calc(100vh - 120px);background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 12px 34px rgba(0,0,0,.18);display:none;flex-direction:column;z-index:9999}
  .mitibot-panel.open{display:flex}
  .mitibot-header{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#0f172a;color:#fff}
  .mitibot-title{display:flex;gap:8px;align-items:center;font-weight:700}
  .mitibot-body{flex:1;overflow:auto;background:#f8fafc;padding:10px}
  .mitibot-msg{margin:6px 0;max-width:92%}
  .mitibot-msg.user{margin-left:auto;background:#d1fae5}
  .mitibot-msg.assistant{margin-right:auto;background:#eef2ff}
  .mitibot-msg .bubble{border-radius:10px;padding:8px 10px;border:1px solid #e5e7eb;}
  .mitibot-footer{padding:10px;border-top:1px solid #e5e7eb;background:#fff}
  .mitibot-input{display:flex;gap:8px}
  .mitibot-input input{flex:1;border:1px solid #e5e7eb;border-radius:20px;padding:10px 12px}
  .mitibot-input button{background:#0ea5e9;color:#fff;border:none;border-radius:20px;padding:10px 14px;font-weight:600}
  .mitibot-suggestions{display:flex;flex-wrap:wrap;gap:6px;margin:6px 0}
  .mitibot-chip{background:#f1f5f9;border:1px solid #e2e8f0;color:#0f172a;border-radius:16px;padding:6px 10px;font-size:12px;cursor:pointer}
  .mitibot-typing{opacity:.7;font-size:12px;margin:4px 2px}
  .mitibot-body h3{margin:6px 0 4px;font-size:14px}
  .mitibot-body ul{margin:4px 0 6px 18px}
  @media (max-width: 600px){ .mitibot-panel{right:0;bottom:0;width:100vw;height:70vh;border-radius:12px 12px 0 0} }
  `;

  function ensureStyles(){
    if (document.getElementById(STYLE_ID)) return;
    const s = document.createElement('style'); s.id = STYLE_ID; s.textContent = css; document.head.appendChild(s);
  }

  function createUI(){
    if (document.getElementById(WIDGET_ID)) return;
    const root = document.createElement('div'); root.id = WIDGET_ID;
    root.innerHTML = `
      <button class="mitibot-fab" type="button" aria-label="Open MitiBot"><span class="dot"></span><span>MitiBot</span></button>
      <div class="mitibot-panel" role="dialog" aria-label="MitiBot chat" aria-modal="true">
        <div class="mitibot-header">
          <div class="mitibot-title">🌿 MitiBot <small style="opacity:.7;font-weight:400;margin-left:6px;">Indigenous Tree Assistant</small></div>
          <button class="mitibot-close" style="background:transparent;border:0;color:#fff;font-size:18px">✕</button>
        </div>
        <div class="mitibot-body">
          <div class="mitibot-msg assistant"><div class="bubble">
            <strong>Welcome!</strong><br>
            I help with tree survival rates, planting conditions, drought-resistant indigenous species, soil suitability, spacing, disease identification, and best practices.
          </div></div>
          <div class="mitibot-suggestions"></div>
          <div class="mitibot-typing" style="display:none">MitiBot is typing…</div>
        </div>
        <div class="mitibot-footer">
          <div class="mitibot-input">
            <input type="text" placeholder="Ask about species, spacing, soil, survival…" maxlength="600" />
            <button type="button">Send</button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(root);

    const fab = root.querySelector('.mitibot-fab');
    const panel = root.querySelector('.mitibot-panel');
    const closeBtn = root.querySelector('.mitibot-close');
    const input = root.querySelector('input');
    const sendBtn = root.querySelector('.mitibot-input button');
    const body = root.querySelector('.mitibot-body');
    const typing = root.querySelector('.mitibot-typing');
    const sugg = root.querySelector('.mitibot-suggestions');

    const quickIntents = [
      'Which indigenous species are drought-resistant?',
      'Give spacing for Grevillea and Cypress',
      'How to improve survival rate in dry season?',
      'What soil is best for avocado and mango?',
      'Identify common diseases for grevillea seedlings',
      'Best planting conditions at altitude 1800m'
    ];

    quickIntents.forEach(t=>{
      const c = document.createElement('span'); c.className='mitibot-chip'; c.textContent=t; c.addEventListener('click', ()=> { input.value=t; input.focus(); }); sugg.appendChild(c);
    });

    function addMsg(role, html){
      const m = document.createElement('div'); m.className = 'mitibot-msg '+role;
      const b = document.createElement('div'); b.className='bubble'; b.innerHTML = html;
      m.appendChild(b); body.appendChild(m); body.scrollTop = body.scrollHeight;
    }

    function sanitize(text){
      const div = document.createElement('div'); div.textContent = text; return div.innerHTML;
    }

    async function send(){
      const q = input.value.trim(); if (!q) return; input.value=''; addMsg('user', sanitize(q));
      typing.style.display='block';
      try {
        const res = await fetch(API_PATH, {
          method:'POST', credentials:'include', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ message:q, context:{ path: location.pathname } })
        });
        const data = await res.json();
        const html = data && data.html ? data.html : '<p>Sorry, I could not process that. Please try again.</p>';
        addMsg('assistant', html);
        if (Array.isArray(data?.suggestions)) {
          sugg.innerHTML=''; data.suggestions.forEach(s=>{ const c=document.createElement('span'); c.className='mitibot-chip'; c.textContent=s; c.addEventListener('click',()=>{ input.value=s; input.focus(); }); sugg.appendChild(c); });
        }
      } catch (e) {
        addMsg('assistant', '<p>Network error. I will be back shortly.</p>');
      } finally {
        typing.style.display='none';
      }
    }

    sendBtn.addEventListener('click', send);
    input.addEventListener('keydown', (e)=>{ if (e.key==='Enter') send(); });
    fab.addEventListener('click', ()=>{ panel.classList.toggle('open'); if (panel.classList.contains('open')) input.focus(); });
    closeBtn.addEventListener('click', ()=> panel.classList.remove('open'));
  }

  function init(){ ensureStyles(); createUI(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
