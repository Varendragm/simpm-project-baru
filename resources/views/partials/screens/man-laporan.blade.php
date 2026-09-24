<section class="screen" id="scr-man-laporan">
  <div class="panel">
    <div class="panel-head">
      <div>
        <h3>Unduh Laporan Manager</h3>
        <div class="sub" id="manReportPeriodLabel">Pilih periode dan mesin untuk membuat laporan.</div>
      </div>
    </div>
    <div class="panel-body">
      <div class="form-grid" style="margin-bottom:16px;">
        <div class="field">
          <label>Periode laporan</label>
          <select id="manReportPeriod">
            <option value="month">Bulan data terbaru</option>
            <option value="quarter">Kuartal data terbaru</option>
            <option value="year">Tahun data terbaru</option>
          </select>
        </div>
        <div class="field">
          <label>Cakupan mesin</label>
          <select id="manReportMachine"><option value="all">Semua Mesin</option></select>
        </div>
      </div>
      <div class="export-box">
        <button type="button" class="export-opt" onclick="printManagerPdf()">
          <div class="eo-ic">PDF</div>
          <div class="eo-title">Buat Laporan PDF</div>
          <div class="eo-sub">Laporan A4 landscape dengan ringkasan KPI dan tabel performa mesin.</div>
        </button>
        <button type="button" class="export-opt" onclick="exportManagerExcel()">
          <div class="eo-ic">XLS</div>
          <div class="eo-title">Unduh Excel</div>
          <div class="eo-sub">File .xls yang dapat langsung dibuka dengan Microsoft Excel sesuai filter laporan.</div>
        </button>
      </div>
      <div id="manReportStatus" class="callout" style="display:none;margin-top:16px;"></div>
      <div class="callout" style="margin-top:12px;">
        <strong>Sumber data:</strong> database SIMPM + PerformanceCalculator. Angka pada laporan mengikuti periode dan mesin yang dipilih.
      </div>
    </div>
  </div>
</section>
