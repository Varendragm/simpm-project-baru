      <section class="screen" id="scr-sup2-dashboard">
        <div class="grid-stats">
          <div class="stat-card"><div class="stat-num" id="dashTotalMesin">0</div><div class="stat-label">Total Mesin</div></div>
          <div class="stat-card green"><div class="stat-num" id="dashMesinNormal">0</div><div class="stat-label">Mesin Normal</div></div>
          <div class="stat-card amber"><div class="stat-num" id="dashMesinWarning">0</div><div class="stat-label">Mesin Perlu Perhatian</div></div>
          <div class="stat-card blue"><div class="stat-num" id="dashMaintHariIni">0</div><div class="stat-label">Maintenance Hari Ini</div></div>
          <div class="stat-card red"><div class="stat-num" id="dashTotalDowntime">0<span class="unit">jam</span></div><div class="stat-label">Total Downtime Bulan Ini</div></div>
        </div>

        <div class="panel chart-panel">
          <div class="panel-head">
            <div><h3>Performa Mesin</h3><div class="sub">Agustus 2026 · rata-rata seluruh mesin</div></div>
          </div>
          <div class="panel-body">
            <div class="mini-indicator-row">
              <div class="mini-indicator"><div class="mi-label">OEE</div><div class="stat-num" id="dashOee" style="font-size:20px;">0<span class="unit">%</span></div></div>
              <div class="mini-indicator"><div class="mi-label">Availability</div><div class="stat-num" id="dashAvail" style="font-size:20px;">0<span class="unit">%</span></div></div>
              <div class="mini-indicator"><div class="mi-label">Reliability</div><div class="stat-num" id="dashRel" style="font-size:20px;">0<span class="unit">%</span></div></div>
              <div class="mini-indicator"><div class="mi-label">MTTR</div><div class="stat-num" id="dashMttr" style="font-size:20px;">0<span class="unit">jam</span></div></div>
              <div class="mini-indicator"><div class="mi-label">MTBF</div><div class="stat-num" id="dashMtbf" style="font-size:20px;">0<span class="unit">jam</span></div></div>
            </div>
            <div class="sub" style="margin:14px 0 6px;">Downtime per mesin bulan ini</div>
            <div class="chart-mount" id="dashDowntimeChart"></div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head">
            <h3>Mesin Perlu Perhatian</h3>
            <button class="btn btn-outline btn-sm" onclick="go('sup2-monitoring')">Lihat Semua Mesin</button>
          </div>
          <div class="panel-body" style="padding:6px 18px;" id="dashRankList"></div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>Jadwal Maintenance Terdekat</h3><button class="btn btn-outline btn-sm" onclick="go('sup2-maintenance')">Lihat Semua</button></div>
          <div class="panel-body" style="padding:4px 18px;"><ul class="mini-list" id="dashJadwalTerdekat"></ul></div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>Pemeriksaan Menunggu Validasi</h3><button class="btn btn-outline btn-sm" onclick="go('sup2-validasi')">Buka Validasi Pemeriksaan</button></div>
          <div class="panel-body" style="padding:4px 18px;"><ul class="mini-list" id="dashMenungguValidasi"></ul></div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/sup-dashboard.blade.php ENDPATH**/ ?>