      <section class="screen" id="scr-man-maintenance">
        <div class="panel chart-panel">
          <div class="panel-head"><div><h3>Jumlah Maintenance per Kategori per Bulan</h3><div class="sub">Empat bulan terakhir berdasarkan riwayat maintenance database</div></div></div>
          <div class="panel-body">
            <div class="chart-mount" id="manMaintenanceChart"></div>
            <div class="formula-box"><strong>Formula:</strong> <code>Jumlah kategori = COUNT(MaintenanceHistory) dikelompokkan berdasarkan kategori dan bulan</code><span class="fnote">Grafik tidak lagi menggunakan angka hardcoded; setiap perubahan riwayat maintenance akan memengaruhi grafik.</span></div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Kategori Maintenance Paling Sering</h3><div class="sub" style="margin-left:18px;">Berdasarkan kolom kategori pada riwayat maintenance</div></div>
          <div class="panel-body" style="padding:6px 18px 14px;" id="manKomponenRanking"></div>
        </div>
      </section>

      