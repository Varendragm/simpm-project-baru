      <section class="screen" id="scr-sup2-maintenance">
        <div class="callout">Jadwal preventive maintenance dibuat manual oleh Supervisor, terpisah dari laporan kerusakan reaktif di SIPPM. Riwayat pelaksanaan akan otomatis masuk ke Riwayat Maintenance.</div>
        <div class="filter-row">
          <select class="fsel" id="jdStasiunSel" onchange="onJadwalStationChange(this)"></select>
          <select class="fsel" id="jdMesinSel" onchange="onJadwalFilterChange()"></select>
          <select class="fsel" id="jdStatusSel" onchange="onJadwalFilterChange()">
            <option value="all">Semua Status</option>
            <option value="terjadwal">Terjadwal</option>
            <option value="menunggu-validasi">Menunggu Validasi</option>
            <option value="selesai">Selesai</option>
            <option value="ditolak">Ditolak</option>
          </select>
          <select class="fsel" id="jdPeriodeSel" onchange="onJadwalFilterChange()">
            <option value="all">Semua Periode</option>
            <option value="2026-08">Agustus 2026</option>
            <option value="2026-07">Juli 2026</option>
          </select>
        </div>
        <div class="panel">
          <div class="panel-head">
            <h3>Jadwal Maintenance Preventif</h3>
            <button class="btn btn-blue btn-sm" onclick="go('sup2-jadwal-tambah')">+ Tambah Jadwal</button>
          </div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>Stasiun</th><th>Mesin</th><th>Jenis PM</th><th>Teknisi</th><th>Jadwal</th><th>Interval</th><th>Prioritas</th><th>Status</th><th></th></tr></thead>
              <tbody id="jadwalTbody"></tbody>
            </table>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/sup-jadwal.blade.php ENDPATH**/ ?>