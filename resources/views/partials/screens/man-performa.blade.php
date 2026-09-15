      <section class="screen" id="scr-man-performa">
        <div class="machine-tab-row" id="manPerformaTabs"></div>
        <div class="panel chart-panel">
          <div class="panel-head">
            <div><h3 id="manPerformaTitle">Perbandingan OEE Antar Mesin</h3><div class="sub" id="manPerformaSub">Rata-rata Agustus 2026 — seluruh mesin</div></div>
            </div>
          <div class="panel-body">
            <div class="chart-mount" id="manPerformaChartMount"></div>
            <div class="formula-box">
              <strong>Formula:</strong> <code>OEE = Availability × Performance × Quality × 100%</code>
              <span class="fnote">Persentase tiap mesin dihitung ulang otomatis mengikuti data availability, performa, dan kualitas produksi terbaru dari SIPPM.</span>
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Ringkasan Performa per Mesin</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>Mesin</th><th>OEE</th><th>Availability</th><th>MTTR</th><th>MTBF</th><th>Downtime Bulan Ini</th></tr></thead>
              <tbody id="manPerformaTbody"></tbody>
            </table>
            </div>
          </div>
        </div>
      </section>

      