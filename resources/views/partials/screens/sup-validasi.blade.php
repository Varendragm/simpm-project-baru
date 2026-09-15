      <section class="screen" id="scr-sup2-validasi">
        <div class="callout">Laporan perbaikan yang dikirim Teknisi (kategori Mekanik / Elektrik / Instrumentasi) harus divalidasi Supervisor sebelum berstatus Selesai. Supervisor juga dapat menyesuaikan jadwal PM berikutnya pada layar validasi.</div>
        <div class="grid-stats">
          <div class="stat-card purple"><div class="stat-num" id="pcSupValidasi2">0</div><div class="stat-label">Menunggu Validasi</div></div>
          <div class="stat-card green"><div class="stat-num">11</div><div class="stat-label">Divalidasi Bulan Ini</div></div>
          <div class="stat-card red"><div class="stat-num">0</div><div class="stat-label">Dikembalikan / Ditolak</div></div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Menunggu Validasi</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>Stasiun</th><th>Mesin</th><th>Jenis PM</th><th>Teknisi</th><th>Tanggal Pemeriksaan</th><th>Status Validasi</th><th></th></tr></thead>
              <tbody id="sup2ValidasiTbody"></tbody>
            </table>
            </div>
          </div>
        </div>
        <div class="panel" style="margin-top:16px;">
          <div class="panel-head"><h3>Riwayat Validasi Terbaru</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>Stasiun</th><th>Mesin</th><th>Jenis PM</th><th>Teknisi</th><th>Divalidasi Oleh</th><th>Tanggal</th><th>Hasil</th></tr></thead>
              <tbody id="sup2ValidasiHistoryTbody"></tbody>
            </table>
            </div>
          </div>
        </div>
      </section>

      