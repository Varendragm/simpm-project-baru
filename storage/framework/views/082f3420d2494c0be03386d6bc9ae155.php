      <section class="screen" id="scr-tek2-detail-jadwal">
        <div class="panel">
          <div class="panel-head">
            <h3>Detail Jadwal PM — <span id="tekDetailMesinNama">-</span></h3>
            <span class="badge b-amber" id="tekDetailStatusBadge">Jatuh Tempo Hari Ini</span>
          </div>
          <div class="panel-body">
            <div class="callout" id="tekValidasiCallout" style="display:none;">Laporan sudah dikirim dan sedang menunggu validasi Supervisor. Anda akan diberi tahu setelah divalidasi.</div>
            <div class="detail-grid">
              <div>
                <div class="kv"><span class="k">Jenis PM</span><span class="v" id="tekDetailJenisPM">-</span></div>
                <div class="kv"><span class="k">Interval</span><span class="v" id="tekDetailInterval">-</span></div>
              </div>
              <div>
                <div class="kv"><span class="k">Jadwal</span><span class="v mono" id="tekDetailJadwal">-</span></div>
                <div class="kv"><span class="k">Estimasi Durasi</span><span class="v mono" id="tekDetailDurasi">-</span></div>
              </div>
            </div>

            <div style="margin-top:18px;">
              <div class="k" style="font-size:12px;color:var(--ink-soft);margin-bottom:8px;">Kategori Pekerjaan</div>
              <div class="vopt-row" id="tekKategoriRow">
                <div class="vopt on" data-kat="mekanik" onclick="setTekKategori('mekanik', this)">Mekanik</div>
                <div class="vopt" data-kat="elektrik" onclick="setTekKategori('elektrik', this)">Elektrik</div>
                <div class="vopt" data-kat="instrumentasi" onclick="setTekKategori('instrumentasi', this)">Instrumentasi</div>
              </div>
            </div>

            <div>
              <div class="k" style="font-size:12px;color:var(--ink-soft);margin-bottom:8px;">Tingkat Prioritas Temuan</div>
              <div class="prio-row" id="tekPrioritasRow">
                <div class="prio" data-p="rendah" onclick="setTekPrioritas('rendah', this)">Rendah</div>
                <div class="prio on" data-p="sedang" onclick="setTekPrioritas('sedang', this)">Sedang</div>
                <div class="prio" data-p="tinggi" onclick="setTekPrioritas('tinggi', this)">Tinggi</div>
                <div class="prio" data-p="kritis" onclick="setTekPrioritas('kritis', this)">Kritis</div>
              </div>
            </div>

            <div class="form-grid" style="margin-top:6px;">
              <div class="field"><label>Waktu Mulai</label><input type="time" id="tekWaktuMulai" oninput="hitungDowntimeTek()"></div>
              <div class="field"><label>Waktu Selesai</label><input type="time" id="tekWaktuSelesai" oninput="hitungDowntimeTek()"></div>
            </div>
            <div class="downtime-readout">Durasi Downtime Perbaikan (otomatis): <strong id="tekDowntimeVal">—</strong></div>

            <!-- Blok parameter teknis spesifik per kategori pekerjaan -->
            <div class="kat-section" id="tekBlokMekanik">
              <div class="kat-section-head">Parameter Teknis — Mekanik <span class="tag">Kategori aktif</span></div>
              <div class="form-grid">
                <div class="field"><label>Getaran Terukur (mm/s)</label><input type="text" id="tekMekGetaran" placeholder="cth. 4.2"></div>
                <div class="field"><label>Suhu Bearing / Gearbox (°C)</label><input type="text" id="tekMekSuhu" placeholder="cth. 62"></div>
                <div class="field"><label>Kondisi Pelumasan</label>
                  <select id="tekMekPelumasan"><option>Baik</option><option>Perlu Ditambah</option><option>Perlu Diganti</option><option>Terjadi Kebocoran</option></select>
                </div>
                <div class="field"><label>Torsi Pengencangan Baut (Nm)</label><input type="text" id="tekMekTorsi" placeholder="cth. 45 — kosongkan jika tidak ada pengencangan"></div>
              </div>
            </div>
            <div class="kat-section" id="tekBlokElektrik" style="display:none;">
              <div class="kat-section-head">Parameter Teknis — Elektrik <span class="tag">Kategori aktif</span></div>
              <div class="form-grid">
                <div class="field"><label>Tegangan Terukur (V)</label><input type="text" id="tekElTegangan" placeholder="cth. 380"></div>
                <div class="field"><label>Arus Terukur (A)</label><input type="text" id="tekElArus" placeholder="cth. 12.5"></div>
                <div class="field"><label>Tahanan Isolasi (MΩ)</label><input type="text" id="tekElIsolasi" placeholder="cth. 50"></div>
                <div class="field"><label>Kondisi Kontaktor / Panel</label>
                  <select id="tekElKondisi"><option>Baik</option><option>Kontak Aus / Perlu Diganti</option><option>Terminal Kendur</option><option>Indikasi Panas Berlebih</option></select>
                </div>
              </div>
            </div>
            <div class="kat-section" id="tekBlokInstrumentasi" style="display:none;">
              <div class="kat-section-head">Parameter Teknis — Instrumentasi <span class="tag">Kategori aktif</span></div>
              <div class="form-grid">
                <div class="field"><label>Nilai Terbaca Sensor</label><input type="text" id="tekInsNilaiBaca" placeholder="cth. 74.8"></div>
                <div class="field"><label>Nilai Standar Kalibrasi</label><input type="text" id="tekInsNilaiStandar" placeholder="cth. 75.0"></div>
                <div class="field"><label>Deviasi (%)</label><input type="text" id="tekInsDeviasi" placeholder="cth. 0.3"></div>
                <div class="field"><label>Status Kalibrasi</label>
                  <select id="tekInsStatus"><option>Sesuai Standar</option><option>Perlu Kalibrasi Ulang</option><option>Sensor Perlu Diganti</option></select>
                </div>
              </div>
            </div>

            <div class="field">
              <label>Deskripsi Temuan / Kondisi</label>
              <textarea id="tekDeskripsi" placeholder="cth. Suhu gearbox 62°C, dalam batas normal. Terdengar sedikit getaran pada dudukan motor."></textarea>
            </div>
            <div class="field">
              <label>Tindakan yang Dilakukan</label>
              <textarea id="tekTindakan" placeholder="cth. Mengencangkan baut dudukan, menambah pelumas gearbox."></textarea>
            </div>

            <div class="field">
              <label>Komponen / Spare Part Digunakan</label>
              <table class="sparepart-table" id="tekSparepartTable">
                <thead><tr><th>Nama Komponen</th><th>Kode Part</th><th class="col-qty">Jumlah</th><th class="col-unit">Satuan</th><th class="col-del"></th></tr></thead>
                <tbody id="tekSparepartBody">
                  <tr>
                    <td><input type="text" placeholder="cth. Bearing 6205"></td>
                    <td><input type="text" placeholder="cth. BR-6205"></td>
                    <td><input type="text" value="1"></td>
                    <td><select><option>pcs</option><option>set</option><option>liter</option><option>kg</option><option>meter</option></select></td>
                    <td class="col-del"><button type="button" class="sp-del-btn" onclick="hapusBarisSparepart(this)" title="Hapus baris">✕</button></td>
                  </tr>
                </tbody>
              </table>
              <button type="button" class="btn btn-outline btn-sm sp-add-btn" onclick="tambahBarisSparepart()">+ Tambah Komponen</button>
            </div>

            <div class="form-grid">
              <div class="field">
                <label>Lampiran Foto</label>
                <div style="display:flex;align-items:center;gap:8px;height:38px;">
                  <button type="button" class="btn btn-outline btn-sm">Ambil Foto</button>
                  <span style="font-size:11px;color:var(--ink-soft);">Belum ada foto</span>
                </div>
              </div>
              <div class="field"><label>Jadwal PM Berikutnya Disarankan</label><input type="date" id="tekJadwalBerikutnya"></div>
            </div>
            <div class="field">
              <label>Rekomendasi / Tindak Lanjut</label>
              <textarea id="tekRekomendasi" placeholder="cth. Perlu penggantian bearing pada PM berikutnya jika getaran masih meningkat."></textarea>
            </div>

            <div class="action-bar" id="tekActionBar">
              <button class="btn btn-amber" id="tekSubmitBtn" onclick="submitLaporanForValidation()">Kirim Laporan untuk Validasi Supervisor</button>
              <button class="btn btn-outline" onclick="go('tek2-dashboard')">Kembali</button>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/tek-detail-jadwal.blade.php ENDPATH**/ ?>