      <section class="screen" id="scr-sup2-detail-mesin">
        <div class="filter-row" style="margin-bottom:8px;">
          <select class="fsel" id="detailStasiunSel" onchange="onDetailStationChange(this)"></select>
          <select class="fsel" id="detailMesinSel" onchange="onDetailMachineChange(this)"></select>
        </div>

        <div class="panel">
          <div class="panel-head">
            <h3 id="detailMesinNama">—</h3>
            <span class="badge b-green" id="detailMesinBadge">Normal</span>
          </div>
          <div class="panel-body">
            <div class="detail-grid">
              <div>
                <div class="kv"><span class="k">Kode Mesin</span><span class="v mono" id="detailMesinKode">-</span></div>
                <div class="kv"><span class="k">Stasiun</span><span class="v" id="detailMesinStasiun">-</span></div>
              </div>
              <div>
                <div class="kv"><span class="k">Jenis Mesin</span><span class="v" id="detailMesinJenis">-</span></div>
                <div class="kv"><span class="k">Jadwal Preventive Berikutnya</span><span class="v mono" id="detailNextPM">-</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>Performa</h3><div class="sub">Agustus 2026</div></div>
          <div class="panel-body">
            <div class="perf-bar-row">
              <div class="perf-bar-label">OEE</div>
              <div class="progress-track"><div class="progress-fill" id="pfOee"></div></div>
              <div class="perf-bar-value" id="pvOee">0%</div>
            </div>
            <div class="perf-bar-row">
              <div class="perf-bar-label">Availability</div>
              <div class="progress-track"><div class="progress-fill" id="pfAvail"></div></div>
              <div class="perf-bar-value" id="pvAvail">0%</div>
            </div>
            <div class="perf-bar-row">
              <div class="perf-bar-label">Reliability</div>
              <div class="progress-track"><div class="progress-fill" id="pfRel"></div></div>
              <div class="perf-bar-value" id="pvRel">0%</div>
            </div>
            <div class="detail-grid" style="margin-top:16px;">
              <div>
                <div class="kv"><span class="k">MTTR</span><span class="v mono" id="detailMTTR">-</span></div>
                <div class="kv"><span class="k">MTBF</span><span class="v mono" id="detailMTBF">-</span></div>
              </div>
              <div>
                <div class="kv"><span class="k">Total Downtime Bulan Ini</span><span class="v mono" id="detailDowntime">-</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel chart-panel">
          <div class="panel-head">
            <div><h3 id="detailChartTitle">Tren OEE Mingguan</h3><div class="sub">6 minggu terakhir</div></div>
          </div>
          <div class="panel-body">
            <div class="chart-mount" id="detailChartMount"></div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3 id="detailHistoryTitle">Riwayat Maintenance</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>No. Laporan</th><th>Kategori</th><th>Komponen Diganti</th><th>Downtime</th><th>Teknisi</th><th>Tanggal</th></tr></thead>
              <tbody id="detailHistoryBody"></tbody>
            </table>
            </div>
          </div>
        </div>
      </section>

      