<div id="loginScreen" class="login-wrap">
  <div class="login-shell">
    <div class="login-side">
      <div class="login-side-top">
        <div class="lm">SIM<span>PM</span></div>
        <div class="ls">Sistem Monitoring Performa Mesin &amp; Maintenance — memantau kondisi mesin, efektivitas perbaikan, dan tren performa giling PG Rendeng secara berkelanjutan.</div>
        <div class="login-side-flow">
          <div class="login-flow-step"><span class="n">1</span> Data kerusakan &amp; perbaikan tersinkron otomatis dari SIPPM</div>
          <div class="login-flow-step"><span class="n">2</span> Performa mesin dipantau lewat indikator OEE, availability &amp; downtime</div>
          <div class="login-flow-step"><span class="n">3</span> Supervisor &amp; Manajer memantau tren, Teknisi menjalankan preventive maintenance</div>
        </div>
      </div>
      <div class="login-side-foot">
        Modul kedua dari ekosistem sistem informasi pabrik PG Rendeng.
        <div class="login-side-link">↳ Terhubung dengan SIPPM (Sistem Pelaporan Kerusakan Mesin)</div>
      </div>
    </div>
    <div class="login-form-panel">
      <div class="login-form-heading">
        <h1>Masuk ke SIMPM</h1>
        <p>Pilih peran, lalu masukkan username &amp; kata sandi</p>
      </div>
      <div class="login-body">
        <div class="login-role-tabs">
          <button class="lrt on" data-role="supervisor" onclick="selectLoginRole('supervisor')">Supervisor</button>
          <button class="lrt" data-role="teknisi" onclick="selectLoginRole('teknisi')">Teknisi</button>
          <button class="lrt" data-role="manajer" onclick="selectLoginRole('manajer')">Manajer</button>
        </div>
        <div class="field">
          <label>Username</label>
          <input type="text" id="loginUsername" value="sri.supervisor">
        </div>
        <div class="field">
          <label>Kata Sandi</label>
          <input type="password" id="loginPassword" value="password">
        </div>
        <button class="btn btn-blue" id="loginSubmitBtn" style="justify-content:center;margin-top:4px;" onclick="enterApp()">Masuk sebagai <span id="loginRoleLabel">Supervisor</span></button>
        <div class="login-note">Satu akun = satu peran. Akun Supervisor &amp; Teknisi sama seperti pada SIPPM — tidak perlu mendaftar ulang.</div>
      </div>
    </div>
  </div>
</div>