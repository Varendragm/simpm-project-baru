      <section class="screen" id="scr-sup2-jadwal-tambah">
        <div class="panel">
          <div class="panel-head"><h3>Tambah Jadwal Maintenance Preventif</h3></div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field"><label>Stasiun</label><select id="jtStasiun" onchange="onJadwalTambahStationChange(this)"></select></div>
              <div class="field"><label>Mesin</label><select id="jtMesin"></select></div>
              <div class="field"><label>Jenis Pemeriksaan / PM</label><input type="text" id="jtJenis" placeholder="cth. Pelumasan bearing, Kalibrasi sensor"></div>
              <div class="field"><label>Teknisi Ditugaskan</label>
                <select id="jtTeknisi"><option>Budi Santoso</option><option>Rudi Hartono</option></select>
              </div>
              <div class="field"><label>Tanggal Mulai</label><input type="date" id="jtTanggal" value="2026-08-20"></div>
              <div class="field"><label>Interval Pengulangan</label>
                <select id="jtInterval"><option>Harian</option><option selected>Mingguan</option><option>Bulanan</option><option>Tidak berulang</option></select>
              </div>
              <div class="field"><label>Estimasi Durasi</label><input type="text" id="jtDurasi" placeholder="cth. 45 menit"></div>
              <div class="field">
                <label>Prioritas</label>
                <div class="prio-row" id="jtPrioRow" style="margin-bottom:0;">
                  <div class="prio" data-p="rendah" onclick="setJtPrioritas('rendah',this)">Rendah</div>
                  <div class="prio on" data-p="sedang" onclick="setJtPrioritas('sedang',this)">Sedang</div>
                  <div class="prio" data-p="tinggi" onclick="setJtPrioritas('tinggi',this)">Tinggi</div>
                </div>
              </div>
              <div class="field span2"><label>Catatan / Checklist</label><textarea id="jtCatatan" placeholder="Rincian item yang perlu diperiksa..."></textarea></div>
            </div>
            <div class="action-bar">
              <button class="btn btn-blue" onclick="simpanJadwalBaru()">Simpan Jadwal</button>
              <button class="btn btn-outline" onclick="go('sup2-maintenance')">Batal</button>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/sup-jadwal-tambah.blade.php ENDPATH**/ ?>