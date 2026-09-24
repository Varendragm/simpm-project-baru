      <section class="screen" id="scr-tek2-dashboard">
        <div class="grid-stats">
          <div class="stat-card blue"><div class="stat-num" id="tekStatMingguIni">0</div><div class="stat-label">Jadwal PM Aktif</div></div>
          <div class="stat-card amber"><div class="stat-num" id="tekStatJatuhTempo">0</div><div class="stat-label">Jatuh Tempo Hari Ini</div></div>
          <div class="stat-card green"><div class="stat-num" id="tekStatSelesaiBulan">0</div><div class="stat-label">PM Selesai Bulan Ini</div></div>
        </div>
        <div id="tekSubmissionCard" style="display:none;margin-bottom:16px;"></div>
        <div class="panel">
          <div class="panel-head"><h3>Jadwal Maintenance Preventif Saya</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>Mesin</th><th>Jenis PM</th><th>Jadwal</th><th>Status</th><th></th></tr></thead>
              <tbody id="tekJadwalTbody"></tbody>
            </table>
            </div>
          </div>
        </div>
      </section>

      