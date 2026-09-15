      <section class="screen" id="scr-sup2-laporan">
        <div class="filter-row">
          <select class="fsel" id="lapPeriodeSel">
            <option value="agu">Periode: Mei – Agustus 2026</option>
            <option value="q3">Kuartal Berjalan</option>
            <option value="th">Tahun Berjalan</option>
          </select>
          <select class="fsel" id="lapStasiunSel" onchange="onLaporanStationChange(this)"></select>
          <select class="fsel" id="lapMesinSel" onchange="onLaporanFilterChange()"></select>
        </div>

        <div class="grid-stats">
          <div class="stat-card blue"><div class="stat-num" id="lapTotalMaintenance">0</div><div class="stat-label">Total Maintenance</div></div>
          <div class="stat-card red"><div class="stat-num" id="lapTotalDowntime">0<span class="unit">jam</span></div><div class="stat-label">Total Downtime</div></div>
          <div class="stat-card amber"><div class="stat-num" id="lapAvgMttr">0<span class="unit">jam</span></div><div class="stat-label">Rata-rata MTTR</div></div>
          <div class="stat-card"><div class="stat-num" id="lapAvgMtbf">0<span class="unit">jam</span></div><div class="stat-label">Rata-rata MTBF</div></div>
          <div class="stat-card blue"><div class="stat-num" id="lapAvgOee">0<span class="unit">%</span></div><div class="stat-label">OEE Rata-rata</div></div>
          <div class="stat-card green"><div class="stat-num" id="lapMesinNormal">0</div><div class="stat-label">Mesin Normal</div></div>
          <div class="stat-card red"><div class="stat-num" id="lapMesinWarning">0</div><div class="stat-label">Mesin Warning</div></div>
          <div class="stat-card amber"><div class="stat-num" id="lapMesinCritical">0</div><div class="stat-label">Mesin Critical</div></div>
        </div>

        <div class="panel chart-panel">
          <div class="panel-head"><div><h3>OEE per Mesin</h3><div class="sub">Mengikuti filter di atas</div></div></div>
          <div class="panel-body"><div class="chart-mount" id="lapOeeChart"></div></div>
        </div>

        <div class="panel chart-panel">
          <div class="panel-head"><div><h3>Downtime per Mesin</h3><div class="sub">Total jam downtime bulan berjalan</div></div></div>
          <div class="panel-body"><div class="chart-mount" id="lapDowntimeChart"></div></div>
        </div>

        <div class="panel chart-panel">
          <div class="panel-head"><div><h3>Tren Downtime</h3><div class="sub">Kumulatif jam downtime, 4 bulan terakhir</div></div></div>
          <div class="panel-body"><div class="chart-mount" id="lapTrenDowntimeChart"></div></div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>Ringkasan Maintenance per Stasiun</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>Stasiun</th><th>Jumlah Maintenance</th><th>Total Downtime</th></tr></thead>
              <tbody id="lapMaintPerStasiunBody"></tbody>
            </table>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>Ekspor Laporan</h3></div>
          <div class="panel-body">
            <div class="export-box">
              <div class="export-opt"><div class="eo-ic"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 1.5h5.5L12.5 4.5V14.5H4V1.5Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="M9.3 1.6V4.7H12.4" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="M5.8 8.2H10.7M5.8 10.4H10.7M5.8 12.6H9" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/></svg></div><div class="eo-title">Laporan PDF</div><div class="eo-sub">Ringkasan performa &amp; grafik siap cetak</div></div>
              <div class="export-opt"><div class="eo-ic"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="2.5" width="12" height="11" rx="1" stroke="currentColor" stroke-width="1.2"/><path d="M2 6h12M6.3 2.5v11" stroke="currentColor" stroke-width="1.1"/></svg></div><div class="eo-title">Data Excel</div><div class="eo-sub">Riwayat maintenance mentah untuk analisis lanjutan</div></div>
            </div>
          </div>
        </div>
      </section>

      