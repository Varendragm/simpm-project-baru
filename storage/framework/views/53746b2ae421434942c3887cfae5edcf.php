      <section class="screen" id="scr-sup2-riwayat">
        <div class="callout sync">Riwayat ini tersinkron otomatis dari laporan perbaikan yang diselesaikan Teknisi di SIPPM, digabungkan dengan hasil PM yang divalidasi Supervisor.</div>
        <div class="filter-row">
          <select class="fsel" id="rwStasiunSel" onchange="onRiwayatStationChange(this)"></select>
          <select class="fsel" id="rwMesinSel" onchange="onRiwayatFilterChange()"></select>
          <select class="fsel" id="rwKategoriSel" onchange="onRiwayatFilterChange()">
            <option value="all">Semua Kategori</option>
            <option value="Mekanik">Mekanik</option>
            <option value="Elektrik">Elektrik</option>
            <option value="Instrumentasi">Instrumentasi</option>
          </select>
          <select class="fsel" id="rwPeriodeSel" onchange="onRiwayatFilterChange()">
            <option value="all">Semua Periode</option>
            <option value="2026-08">Agustus 2026</option>
            <option value="2026-07">Juli 2026</option>
          </select>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Riwayat Maintenance Seluruh Mesin</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>No. Laporan</th><th>Tanggal</th><th>Stasiun</th><th>Mesin</th><th>Jenis</th><th>Pekerjaan</th><th>Pelaksana</th><th>Downtime</th><th>Hasil</th></tr></thead>
              <tbody id="rwTbody"></tbody>
            </table>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/sup-riwayat.blade.php ENDPATH**/ ?>