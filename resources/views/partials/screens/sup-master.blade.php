      <section class="screen" id="scr-sup2-master">
        <div class="grid-stats" style="margin-bottom:16px;">
          <div class="stat-card"><div class="stat-num" id="masterTotalStasiun">0</div><div class="stat-label">Total Stasiun</div></div>
          <div class="stat-card"><div class="stat-num" id="masterTotalMesin">0</div><div class="stat-label">Total Mesin</div></div>
        </div>
        <div class="panel-head" style="padding:0 0 14px;border:none;">
          <div>
            <h3 style="font-family:var(--font-display);font-size:17px;font-weight:600;margin:0;">Stasiun & Mesin</h3>
            <div class="sub">Kelola data stasiun dan mesin yang digunakan dalam sistem pemeliharaan.</div>
          </div>
          <button class="btn btn-blue btn-sm" onclick="openModal('add','station', null)">+ Tambah Stasiun</button>
        </div>
        <div id="masterStasiunList"></div>
      </section>

      