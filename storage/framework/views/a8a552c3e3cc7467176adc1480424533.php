      <section class="screen" id="scr-sup2-profil">
        <div class="profile-card">
          <div class="pc-avatar">SH</div>
          <div class="pc-info">
            <div class="pc-name-row"><span class="pc-name">Sri Handayani</span><span class="pc-status">Aktif</span></div>
            <div class="pc-role">Supervisor · Produksi — PG Rendeng</div>
            <div class="pc-meta">
              <div>Jadwal PM Dikelola<strong>7</strong></div>
              <div>Menunggu Validasi<strong id="pcSupValidasi">0</strong></div>
              <div>Bergabung Sejak<strong>2019</strong></div>
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Ubah Data Diri</h3></div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field"><label>Nama Lengkap</label><input type="text" value="Sri Handayani"></div>
              <div class="field"><label>Username</label><input type="text" value="sri.supervisor" disabled style="background:#F5F6F7;"></div>
              <div class="field"><label>No. HP</label><input type="text" value="0813-2244-5566"></div>
              <div class="field"><label>Bagian</label><input type="text" value="Produksi — Gilingan" disabled style="background:#F5F6F7;"></div>
            </div>
            <div class="action-bar">
              <button class="btn btn-blue">Simpan Perubahan</button>
              <button class="btn btn-outline" onclick="go('sup2-dashboard')">Batal</button>
            </div>
          </div>
        </div>
        <div class="panel" style="margin-top:16px;">
          <div class="panel-head"><h3>Ubah Password</h3></div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field"><label>Password Saat Ini</label><input type="password" id="supPassOld" placeholder="••••••••"></div>
              <div></div>
              <div class="field"><label>Password Baru</label><input type="password" id="supPassNew" oninput="checkPasswordMatch('sup')" placeholder="Minimal 8 karakter"></div>
              <div class="field"><label>Konfirmasi Password Baru</label><input type="password" id="supPassConfirm" oninput="checkPasswordMatch('sup')" placeholder="Ulangi password baru"></div>
            </div>
            <div class="callout" id="supPassMsg" style="display:none;"></div>
            <div class="action-bar">
              <button class="btn btn-blue" id="supPassBtn" onclick="changePassword('sup')" disabled>Simpan Password Baru</button>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/sup-profil.blade.php ENDPATH**/ ?>