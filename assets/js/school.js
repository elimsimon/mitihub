(function(){
  const BASE = (window.APP_BASE_URL || '').replace(/\/$/, '');

  // Simple IndexedDB wrapper
  const idbName = 'mitihub-school';
  const version = 1;
  let dbp = null;
  function openDB(){
    if (dbp) return dbp;
    dbp = new Promise((resolve, reject)=>{
      const req = indexedDB.open(idbName, version);
      req.onupgradeneeded = (e)=>{
        const db = req.result;
        if (!db.objectStoreNames.contains('species')) db.createObjectStore('species');
        if (!db.objectStoreNames.contains('stats')) db.createObjectStore('stats');
        if (!db.objectStoreNames.contains('trees')) db.createObjectStore('trees');
        if (!db.objectStoreNames.contains('adoptable')) db.createObjectStore('adoptable');
        if (!db.objectStoreNames.contains('journals')) db.createObjectStore('journals');
        if (!db.objectStoreNames.contains('outbox')) db.createObjectStore('outbox', { keyPath: 'id', autoIncrement: true });
      };
      req.onsuccess = ()=> resolve(req.result);
      req.onerror = ()=> reject(req.error);
    });
    return dbp;
  }
  async function idbGet(store, key){ const db = await openDB(); return new Promise((res,rej)=>{ const tx=db.transaction(store,'readonly'); const st=tx.objectStore(store); const r=st.get(key); r.onsuccess=()=>res(r.result); r.onerror=()=>rej(r.error); }); }
  async function idbSet(store, key, val){ const db = await openDB(); return new Promise((res,rej)=>{ const tx=db.transaction(store,'readwrite'); tx.oncomplete=()=>res(true); tx.onerror=()=>rej(tx.error); tx.objectStore(store).put(val, key); }); }

  // API helpers with offline cache
  const api = {
    async getJSON(path, cacheStore, cacheKey){
      const url = BASE + '/' + path.replace(/^\//,'');
      try {
        const r = await fetch(url, { credentials: 'include' });
        if (!r.ok) throw new Error('HTTP '+r.status);
        const data = await r.json();
        if (cacheStore) await idbSet(cacheStore, cacheKey || 'all', data);
        return data;
      } catch (e) {
        if (cacheStore) {
          const cached = await idbGet(cacheStore, cacheKey || 'all');
          if (cached) return cached;
        }
        throw e;
      }
    },
  };

  // Public app API
  const app = {
    async loadDashboard(){
      const els = {
        planted: document.querySelector('[data-stat="trees_planted"]'),
        adopted: document.querySelector('[data-stat="trees_adopted"]'),
        survival: document.querySelector('[data-stat="survival_rate"]'),
        points: document.querySelector('[data-stat="points"]'),
        badges: document.querySelector('[data-stat="badges"]'),
      };
      try {
        const stats = await api.getJSON('school/api/stats.php', 'stats', 'me');
        if (els.planted) els.planted.textContent = stats.trees_planted ?? 0;
        if (els.adopted) els.adopted.textContent = stats.trees_adopted ?? 0;
        if (els.survival) els.survival.textContent = (stats.survival_rate ?? 0).toFixed(1) + '%';
        if (els.points) els.points.textContent = (stats.points ?? 0) + ' pts';
        if (els.badges) {
          els.badges.innerHTML = '';
          (stats.badges||[]).forEach(b=>{
            const span = document.createElement('span');
            span.className='badge'; span.textContent=b; els.badges.appendChild(span);
          });
          if ((stats.badges||[]).length===0) els.badges.innerHTML = '<span class="muted small">No badges yet</span>';
        }
      } catch(err){
        console.warn('Stats load failed', err);
      }
    },

    async loadSpecies(selectEl){
      try {
        const species = await api.getJSON('school/api/species.php', 'species', 'all');
        if (!selectEl) return species;
        selectEl.innerHTML = '<option value="">Select species</option>' + species.map(s=>`<option value="${s.id}">${s.name}</option>`).join('');
        return species;
      } catch (e) { console.warn('Species load failed', e); return []; }
    },

    async loadPointsPage(){
      try {
        const stats = await api.getJSON('school/api/stats.php', 'stats', 'me');
        const ptsEl = document.querySelector('[data-points-total]');
        const bdgEl = document.querySelector('[data-badges]');
        const lvlEl = document.querySelector('[data-level]');
        const brEl = document.querySelector('[data-points-breakdown]');
        if (ptsEl) ptsEl.textContent = stats.points ?? 0;
        if (lvlEl) lvlEl.textContent = stats.level || '—';
        if (bdgEl) {
          bdgEl.innerHTML = '';
          (stats.badges||[]).forEach(b=>{ const s=document.createElement('span'); s.className='badge'; s.textContent=b; bdgEl.appendChild(s); });
          if ((stats.badges||[]).length===0) bdgEl.innerHTML = '<span class="muted small">No badges earned yet.</span>';
        }
        if (brEl && stats.points_breakdown) {
          const pb = stats.points_breakdown;
          brEl.innerHTML = '';
          const ul = document.createElement('ul'); ul.className='muted small';
          ul.innerHTML = `
            <li>Planting: +10 × = ${pb.planting||0}</li>
            <li>Adoption: +5 × = ${pb.adoption||0}</li>
            <li>Survival 6 months: +15 × = ${pb.survival_6m||0}</li>
            <li>Survival 1 year: +25 × = ${pb.survival_1y||0}</li>
            <li>Health update: +2/update × = ${pb.health_updates||0}</li>
          `;
          brEl.appendChild(ul);
        }
      } catch (e) { console.warn('Points page load failed', e); }
    },

    initQrScanner(containerId, onDecoded){
      const el = document.getElementById(containerId || 'qr-reader');
      if (!el) return;
      // Try html5-qrcode if available
      if (window.Html5Qrcode) {
        const qr = new Html5Qrcode(el.id);
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        qr.start({ facingMode: 'environment' }, config, (decodedText)=>{
          try { onDecoded && onDecoded(decodedText); } catch(e){}
        }).catch(err=>{ console.warn('QR start failed', err); });
      } else {
        el.innerHTML = '<div class="muted small">QR library not loaded. Please connect to the internet to load the scanner.</div>';
      }
    }
  };

  window.MitiHubSchool = app;
})();
