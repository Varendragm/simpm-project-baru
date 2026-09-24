      <section class="screen" id="scr-man-dashboard">
        <div class="callout">Tampilan Manajer bersifat ringkas dan hanya-baca (read-only). Semua KPI dan grafik diambil dari data database serta PerformanceCalculator.</div>
        <div class="grid-stats">
          <div class="stat-card blue"><div class="stat-num" id="manDashOee">—</div><div class="stat-label">OEE Pabrik (Rata-rata)</div></div>
          <div class="stat-card green"><div class="stat-num" id="manDashAvail">—</div><div class="stat-label">Availability Pabrik</div></div>
          <div class="stat-card blue"><div class="stat-num" id="manDashPerformance">—</div><div class="stat-label">Performance Pabrik</div></div>
          <div class="stat-card green"><div class="stat-num" id="manDashQuality">—</div><div class="stat-label">Quality Pabrik</div></div>
          <div class="stat-card blue"><div class="stat-num" id="manDashReliability">—</div><div class="stat-label">Reliability Rata-rata</div></div>
          <div class="stat-card red"><div class="stat-num" id="manDashDowntime">—</div><div class="stat-label">Downtime Periode</div></div>
          <div class="stat-card amber"><div class="stat-num" id="manDashPerbaikan">—</div><div class="stat-label">Failure Periode</div></div>
          <div class="stat-card red"><div class="stat-num" id="manDashWorst">—</div><div class="stat-label">Downtime Tertinggi</div></div>
        </div>

        <div class="panel">
          <div class="panel-head"><div><h3>Status Kondisi Mesin</h3><div class="sub">Periode data: <span id="manDashPeriod">—</span></div></div></div>
          <div class="panel-body">
            <div class="grid-stats" style="margin:0;">
              <div class="stat-card green"><div class="stat-num" id="manDashNormal">0</div><div class="stat-label">Normal</div></div>
              <div class="stat-card amber"><div class="stat-num" id="manDashPerhatian">0</div><div class="stat-label">Perlu Perhatian</div></div>
              <div class="stat-card red"><div class="stat-num" id="manDashPerbaikanKondisi">0</div><div class="stat-label">Dalam Perbaikan</div></div>
            </div>
          </div>
        </div>

        <div class="panel chart-panel">
          <div class="panel-head"><div><h3>Downtime per Mesin</h3><div class="sub">Seluruh mesin, berdasarkan maintenance unplanned pada periode data terbaru</div></div></div>
          <div class="panel-body">
            <div class="chart-mount" id="manDashDowntimeChart"></div>
            <div class="formula-box"><strong>Formula:</strong> <code>Downtime = Σ downtime unplanned dari maintenance corrective/breakdown</code><span class="fnote">Nilai mengikuti riwayat maintenance yang tersedia di database.</span></div>
          </div>
        </div>

        <div class="panel"><div class="panel-head"><h3>Ranking Mesin Berdasarkan Downtime</h3></div><div class="panel-body" style="padding:6px 18px;" id="manDashRankList"></div></div>
      </section>

      