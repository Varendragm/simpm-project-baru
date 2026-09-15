      <section class="screen" id="scr-man-profil">
        <div class="profile-card">
          <div class="pc-avatar">WP</div>
          <div class="pc-info">
            <div class="pc-name-row"><span class="pc-name">Ir. Wahyu Prasetyo, M.T.</span><span class="pc-status">Aktif</span></div>
            <div class="pc-role">Manajer Produksi · PG Rendeng</div>
            <div class="pc-meta">
              <div>Stasiun Dipantau<strong>3</strong></div>
              <div>OEE Rata-rata<strong>78%</strong></div>
              <div>Bergabung Sejak<strong>2015</strong></div>
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Ubah Data Diri</h3></div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field"><label>Nama Lengkap</label><input type="text" value="Ir. Wahyu Prasetyo, M.T."></div>
              <div class="field"><label>Username</label><input type="text" value="wahyu.manajer" disabled style="background:#F5F6F7;"></div>
              <div class="field"><label>No. HP</label><input type="text" value="0811-3344-5567"></div>
              <div class="field"><label>Jabatan</label><input type="text" value="Manajer Produksi" disabled style="background:#F5F6F7;"></div>
            </div>
            <div class="action-bar">
              <button class="btn btn-blue">Simpan Perubahan</button>
              <button class="btn btn-outline" onclick="go('man-dashboard')">Batal</button>
            </div>
          </div>
        </div>
        <div class="panel" style="margin-top:16px;">
          <div class="panel-head"><h3>Ubah Password</h3></div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field"><label>Password Saat Ini</label><input type="password" id="manPassOld" placeholder="••••••••"></div>
              <div></div>
              <div class="field"><label>Password Baru</label><input type="password" id="manPassNew" oninput="checkPasswordMatch('man')" placeholder="Minimal 8 karakter"></div>
              <div class="field"><label>Konfirmasi Password Baru</label><input type="password" id="manPassConfirm" oninput="checkPasswordMatch('man')" placeholder="Ulangi password baru"></div>
            </div>
            <div class="callout" id="manPassMsg" style="display:none;"></div>
            <div class="action-bar">
              <button class="btn btn-blue" id="manPassBtn" onclick="changePassword('man')" disabled>Simpan Password Baru</button>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/man-profil.blade.php ENDPATH**/ ?>