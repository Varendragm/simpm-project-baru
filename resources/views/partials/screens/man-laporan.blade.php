      <section class="screen" id="scr-man-laporan">
        <div class="panel">
          <div class="panel-head"><div><h3>Unduh Laporan Manager</h3><div class="sub" id="manReportPeriodLabel">Periode data: —</div></div></div>
          <div class="panel-body">
            <div class="form-grid" style="margin-bottom:16px;">
              <div class="field"><label>Periode laporan</label><select id="manReportPeriod"><option value="month">Bulan data terbaru</option><option value="quarter">Kuartal data terbaru</option><option value="year">Tahun data terbaru</option></select></div>
              <div class="field"><label>Cakupan mesin</label><select id="manReportMachine"><option value="all">Semua Mesin</option></select></div>
            </div>
            <div class="export-box">
              <button type="button" class="export-opt" onclick="printManagerPdf()"><div class="eo-ic">PDF</div><div class="eo-title">Cetak / Simpan PDF</div><div class="eo-sub">Ringkasan OEE, availability, performance, quality, reliability, MTTR, MTBF &amp; kondisi mesin</div></button>
              <button type="button" class="export-opt" onclick="exportManagerExcel()"><div class="eo-ic">XLS</div><div class="eo-title">Unduh Excel</div><div class="eo-sub">Data performance mesin sesuai periode dan cakupan yang dipilih</div></button>
            </div>
            <div class="callout" style="margin-top:16px;">Excel diunduh sebagai file CSV UTF-8 yang kompatibel dengan Microsoft Excel. PDF menggunakan dialog cetak browser sehingga dapat dipilih <strong>Save as PDF</strong>.</div>
          </div>
        </div>
      </section>

      