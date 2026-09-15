<div class="app" id="appShell" style="display:none;">

  <aside class="sidebar">
    <div class="brand">
      <div class="brand-text">
        <div class="brand-mark">SIM<span>PM</span></div>
        <div class="brand-sub">PG Rendeng · Modul 2</div>
      </div>
    </div>
    <div class="brand-link" style="padding:0 18px 14px;margin-top:0;border-bottom:1px solid rgba(255,255,255,.08);"><span class="sq"></span>Tersinkron dari SIPPM</div>

    <div class="role-switch">
      <div class="role-switch-label">Tampilkan sebagai</div>
      <div class="role-pills">
        <button class="role-pill active" data-role="supervisor" onclick="setRole('supervisor')"><span class="dot"></span>Supervisor</button>
        <button class="role-pill" data-role="teknisi" onclick="setRole('teknisi')"><span class="dot"></span>Teknisi</button>
        <button class="role-pill" data-role="manajer" onclick="setRole('manajer')"><span class="dot"></span>Manajer</button>
      </div>
    </div>

    <nav class="nav" id="navArea"></nav>

    <div class="sidebar-foot">
      SIMPM v1.0 &middot; PG Rendeng
    </div>
  </aside>

  <div class="sidebar-backdrop" onclick="toggleSidebar()"></div>

  <div class="main">
    <div class="topbar">
      <div style="display:flex;align-items:center;">
        <button class="hamburger-btn" onclick="toggleSidebar()" title="Tampilkan/sembunyikan sidebar" aria-label="Tampilkan/sembunyikan sidebar">
          <span class="hb-lines"><span></span><span></span><span></span></span>
        </button>
        <div>
          <div class="topbar-crumb" id="crumb">Supervisor</div>
          <div class="topbar-title" id="pageTitle">Dashboard Performa</div>
        </div>
      </div>
      <div class="topbar-user">
        <div style="text-align:right;">
          <div style="font-weight:600;" id="userName">Sri Handayani</div>
          <div style="font-size:11px;color:var(--ink-soft);" id="userRoleLabel">Supervisor · Produksi</div>
        </div>
        <div class="avatar" id="userAvatar">SH</div>
        <button class="btn btn-outline btn-sm" id="logoutBtn" onclick="logoutApp()" title="Keluar dari akun" style="margin-left:6px;"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;flex-shrink:0;"><path d="M8 2v5.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><path d="M5 3.6a5.2 5.2 0 1 0 6 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg> Keluar</button>
      </div>
    </div>

    <div class="content">
      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/app-shell.blade.php ENDPATH**/ ?>