      <section class="screen" id="scr-tek2-profil">
        <div class="profile-card">
          <div class="pc-avatar">BS</div>
          <div class="pc-info">
            <div class="pc-name-row"><span class="pc-name">Budi Santoso</span><span class="pc-status">Aktif</span></div>
            <div class="pc-role">Teknisi · Maintenance — PG Rendeng</div>
            <div class="pc-meta">
              <div>PM Selesai Bulan Ini<strong>9</strong></div>
              <div>Rata-rata Perbaikan<strong>2.4j</strong></div>
              <div>Bergabung Sejak<strong>2021</strong></div>
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Ubah Data Diri</h3></div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field"><label>Nama Lengkap</label><input type="text" value="Budi Santoso"></div>
              <div class="field"><label>Username</label><input type="text" value="budi.teknisi" disabled style="background:#F5F6F7;"></div>
              <div class="field"><label>No. HP</label><input type="text" value="0814-9988-7766"></div>
              <div class="field"><label>Bagian / Keahlian</label><select><option>Mekanik</option><option>Elektrik</option><option>Instrumentasi</option></select></div>
            </div>
            <div class="action-bar">
              <button class="btn btn-blue">Simpan Perubahan</button>
              <button class="btn btn-outline" onclick="go('tek2-dashboard')">Batal</button>
            </div>
          </div>
        </div>
        <div class="panel" style="margin-top:16px;">
          <div class="panel-head"><h3>Ubah Password</h3></div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field"><label>Password Saat Ini</label><input type="password" id="tekPassOld" placeholder="••••••••"></div>
              <div></div>
              <div class="field"><label>Password Baru</label><input type="password" id="tekPassNew" oninput="checkPasswordMatch('tek')" placeholder="Minimal 8 karakter"></div>
              <div class="field"><label>Konfirmasi Password Baru</label><input type="password" id="tekPassConfirm" oninput="checkPasswordMatch('tek')" placeholder="Ulangi password baru"></div>
            </div>
            <div class="callout" id="tekPassMsg" style="display:none;"></div>
            <div class="action-bar">
              <button class="btn btn-blue" id="tekPassBtn" onclick="changePassword('tek')" disabled>Simpan Password Baru</button>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/tek-profil.blade.php ENDPATH**/ ?>