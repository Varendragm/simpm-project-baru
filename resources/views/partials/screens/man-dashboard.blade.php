      <section class="screen" id="scr-man-dashboard">
        <div class="callout">Tampilan Manajer bersifat ringkas dan hanya-baca (read-only) — untuk pengambilan keputusan strategis, tanpa input operasional harian.</div>
        <div class="grid-stats">
          <div class="stat-card blue"><div class="stat-num" id="manDashOee">0<span class="unit">%</span></div><div class="stat-label">OEE Pabrik (Rata-rata)</div></div>
          <div class="stat-card green"><div class="stat-num" id="manDashAvail">0<span class="unit">%</span></div><div class="stat-label">Availability Pabrik</div></div>
          <div class="stat-card red"><div class="stat-num" id="manDashDowntime">0<span class="unit">jam</span></div><div class="stat-label">Total Downtime Bulan Ini</div></div>
          <div class="stat-card amber"><div class="stat-num" id="manDashPerbaikan">0</div><div class="stat-label">Total Perbaikan Bulan Ini</div></div>
          <div class="stat-card red"><div class="stat-num" id="manDashWorst">-</div><div class="stat-label">Mesin Paling Bermasalah</div></div>
        </div>

        <div class="panel chart-panel">
          <div class="panel-head">
            <div><h3>Downtime per Mesin — Bulan Ini</h3><div class="sub">Seluruh mesin pabrik, diurutkan dari downtime tertinggi</div></div>
            </div>
          <div class="panel-body">
            <div class="chart-mount" id="manDashDowntimeChart"></div>
            <div class="formula-box">
              <strong>Formula:</strong> <code>Downtime Bulanan (jam) = Σ Durasi Perbaikan per mesin dalam 1 bulan</code>
              <span class="fnote">Grafik ini menyesuaikan otomatis untuk seluruh mesin terdaftar setiap kali ada laporan perbaikan baru dari Teknisi.</span>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>Ranking Mesin Bermasalah</h3></div>
          <div class="panel-body" style="padding:6px 18px;" id="manDashRankList"></div>
        </div>
      </section>

      