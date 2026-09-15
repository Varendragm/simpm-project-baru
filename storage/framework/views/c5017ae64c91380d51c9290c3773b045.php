      <div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this) closeModal()">
        <div class="modal-box">
          <div class="modal-head">
            <h3 id="modalTitle">Tambah Stasiun</h3>
            <button type="button" class="modal-close" onclick="closeModal()" aria-label="Tutup">✕</button>
          </div>
          <div class="modal-body">
            <form id="formStasiun" class="modal-form" onsubmit="return simpanStasiun(event)">
              <input type="hidden" id="fStasiunId">
              <div class="form-grid">
                <div class="field"><label>Kode Stasiun</label><input type="text" id="fStasiunKode" placeholder="cth. STG" required></div>
                <div class="field"><label>Nama Stasiun</label><input type="text" id="fStasiunNama" placeholder="cth. Stasiun Gilingan" required></div>
                <div class="field span2"><label>Lokasi</label><input type="text" id="fStasiunLokasi" placeholder="cth. Area Produksi — Depan"></div>
                <div class="field span2"><label>Deskripsi</label><textarea id="fStasiunDeskripsi" placeholder="Deskripsi singkat fungsi stasiun"></textarea></div>
                <div class="field"><label>Status</label>
                  <select id="fStasiunStatus"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select>
                </div>
              </div>
              <div class="action-bar">
                <button type="submit" class="btn btn-blue">Simpan Stasiun</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
              </div>
            </form>
            <form id="formMesin" class="modal-form" style="display:none;" onsubmit="return simpanMesin(event)">
              <input type="hidden" id="fMesinId">
              <div class="form-grid">
                <div class="field"><label>Stasiun</label><select id="fMesinStasiun" required></select></div>
                <div class="field"><label>Kode Mesin</label><input type="text" id="fMesinKode" placeholder="cth. G05" required></div>
                <div class="field"><label>Nama Mesin</label><input type="text" id="fMesinNama" placeholder="cth. Gilingan 05" required></div>
                <div class="field"><label>Jenis Mesin</label><input type="text" id="fMesinJenis" placeholder="cth. Unit Gilingan Tebu"></div>
                <div class="field"><label>Status</label>
                  <select id="fMesinStatus"><option value="normal">Normal</option><option value="perhatian">Perlu Perhatian</option><option value="perbaikan">Dalam Perbaikan</option></select>
                </div>
                <div class="field"><label>Kapasitas</label><input type="text" id="fMesinKapasitas" placeholder="cth. 120 TCD"></div>
                <div class="field"><label>Tahun Pemasangan</label><input type="text" id="fMesinTahun" placeholder="cth. 2010"></div>
                <div class="field span2"><label>Keterangan</label><textarea id="fMesinKeterangan" placeholder="Catatan tambahan (opsional)"></textarea></div>
              </div>
              <div class="action-bar">
                <button type="submit" class="btn btn-blue">Simpan Mesin</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
              </div>
            </form>
          </div>
        </div>
      </div>


    </div>
  </div>
</div><?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/modal-master.blade.php ENDPATH**/ ?>