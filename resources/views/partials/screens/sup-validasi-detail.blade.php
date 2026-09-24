      <section class="screen" id="scr-sup2-validasi-detail">
        <div class="breadcrumb-mini" id="validasiDetailBreadcrumb">Stasiun / Mesin</div>
        <div class="panel">
          <div class="panel-head">
            <h3 id="validasiDetailTitle">Validasi Laporan Perbaikan</h3>
            <span class="badge b-purple" id="validasiDetailBadge">Menunggu</span>
          </div>
          <div class="panel-body">
            <div class="detail-grid">
              <div>
                <div class="kv"><span class="k">Jenis PM</span><span class="v" id="vdJenisPM">-</span></div>
                <div class="kv"><span class="k">Pemeriksa</span><span class="v" id="vdPemeriksa">-</span></div>
              </div>
              <div>
                <div class="kv"><span class="k">Jadwal</span><span class="v mono" id="vdJadwal">-</span></div>
                <div class="kv"><span class="k">Dikirim untuk Validasi</span><span class="v mono" id="vdDikirim">-</span></div>
              </div>
            </div>

            <div class="detail-grid" style="margin-top:6px;">
              <div>
                <div class="kv"><span class="k">Kategori Pekerjaan</span><span class="v" id="vdKategori">-</span></div>
                <div class="kv"><span class="k">Tingkat Prioritas</span><span class="v" id="vdPrioritas">-</span></div>
              </div>
              <div>
                <div class="kv"><span class="k">Waktu Pengerjaan</span><span class="v mono" id="vdWaktu">-</span></div>
                <div class="kv"><span class="k">Lampiran Foto</span><span class="v" id="vdFoto">-</span></div>
              </div>
            </div>

            <div id="vdFotoPreviewWrap" style="display:none;margin-top:14px;">
              <div class="k" style="font-size:12px;color:var(--ink-soft);margin-bottom:8px;">Foto Pemeriksaan</div>
              <img id="vdFotoPreview" src="" alt="Foto pemeriksaan teknisi" style="display:block;max-width:520px;width:100%;max-height:320px;object-fit:contain;border:1px solid var(--line);border-radius:8px;background:#F5F6F7;">
            </div>

            <div id="vdParamMount"></div>

            <div class="field">
              <label>Deskripsi Temuan / Kondisi</label>
              <textarea id="vdDeskripsi" disabled style="background:#F5F6F7;"></textarea>
            </div>
            <div class="field">
              <label>Tindakan yang Dilakukan</label>
              <textarea id="vdTindakan" disabled style="background:#F5F6F7;"></textarea>
            </div>

            <div class="field">
              <label>Komponen / Spare Part Digunakan</label>
              <table class="sparepart-table">
                <thead><tr><th>Nama Komponen</th><th>Kode Part</th><th class="col-qty">Jumlah</th><th class="col-unit">Satuan</th></tr></thead>
                <tbody id="vdSparepartBody"></tbody>
              </table>
            </div>

            <div class="field">
              <label>Rekomendasi / Tindak Lanjut dari Teknisi</label>
              <textarea id="vdRekomendasi" disabled style="background:#F5F6F7;"></textarea>
            </div>

            <div class="validate-box">
              <h4>Hasil Validasi Supervisor</h4>
              <div class="vopt-row">
                <div class="vopt on" id="voptApprove" onclick="setValidationResult('approve')">Disetujui</div>
                <div class="vopt" id="voptReject" onclick="setValidationResult('reject')">Ditolak</div>
              </div>
              <div class="field">
                <label>Catatan Supervisor</label>
                <textarea id="vdCatatanSupervisor" placeholder="cth. Hasil pemeriksaan sudah sesuai standar, lanjutkan ke jadwal berikutnya."></textarea>
              </div>
            </div>

            <div class="validate-box" style="background:#F5F6F7;">
              <h4>Jadwal PM Berikutnya</h4>
              <p style="margin:0;color:var(--ink-soft);font-size:13px;line-height:1.6;">
                Jadwal berikutnya dibuat otomatis berdasarkan <strong>interval PM yang sudah ditetapkan saat jadwal dibuat</strong>.
                Jika interval <strong>Tidak berulang</strong>, sistem tidak membuat jadwal baru.
              </p>
            </div>

            <div class="action-bar">
              <button class="btn btn-blue" onclick="finalizeValidation()">Simpan Validasi</button>
              <button class="btn btn-outline" onclick="go('sup2-validasi')">Kembali</button>
            </div>
          </div>
        </div>
      </section>

      