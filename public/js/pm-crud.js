/* =====================================================================
   PREVENTIVE MAINTENANCE — BACKEND BRIDGE
   Sumber kebenaran jadwal = database Laravel.
   Penugasan teknisi menggunakan user ID, bukan nama sebagai identitas.
   ===================================================================== */
(function () {
  function currentUser() {
    return window.SIMPM_CURRENT_USER || (window.__SIMPM_BOOTSTRAP__ && window.__SIMPM_BOOTSTRAP__.currentUser) || null;
  }

  function syncCurrentUser(data) {
    window.SIMPM_CURRENT_USER = data && data.currentUser ? data.currentUser : null;
  }

  function renderTechnicianOptions() {
    const select = document.getElementById('jtTeknisi');
    if (!select) return;
    const technicians = Array.isArray(window.TECHNICIANS) ? window.TECHNICIANS : [];
    const previous = select.value;
    select.innerHTML = technicians.length
      ? technicians.map(function (u) {
          return '<option value="' + escapeHtml(String(u.id)) + '">' + escapeHtml(u.name) + '</option>';
        }).join('')
      : '<option value="">Tidak ada akun teknisi</option>';
    if (technicians.some(function (u) { return String(u.id) === String(previous); })) {
      select.value = previous;
    }
  }

  function technicianFromForm() {
    const select = document.getElementById('jtTeknisi');
    const id = select ? select.value : '';
    const technicians = Array.isArray(window.TECHNICIANS) ? window.TECHNICIANS : [];
    const user = technicians.find(function (u) { return String(u.id) === String(id); });
    return {
      name: user ? user.name : '',
      id: user ? user.id : null
    };
  }

  // applyBootstrap sudah dijalankan oleh app.js sebelum bridge ini dimuat.
  function syncBootstrap(data) {
    syncCurrentUser(data);
    window.TECHNICIANS = data && Array.isArray(data.technicians) ? data.technicians : [];
    renderTechnicianOptions();
  }

  function apiUpload(url, formData) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    }).then(function (res) {
      if (!res.ok) return res.json().catch(function () { return {}; }).then(function (body) {
        throw (body.message || ('Request gagal (' + res.status + ')'));
      });
      return res.json();
    });
  }

  window.previewTekFoto = function (input) {
    const file = input && input.files && input.files[0];
    const label = document.getElementById('tekFotoLabel');
    const wrap = document.getElementById('tekFotoPreviewWrap');
    const img = document.getElementById('tekFotoPreview');
    if (!file) {
      if (label) label.textContent = 'Belum ada foto';
      if (wrap) wrap.style.display = 'none';
      return;
    }
    if (label) label.textContent = file.name;
    if (wrap && img) {
      const reader = new FileReader();
      reader.onload = function (e) {
        img.src = e.target.result;
        wrap.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  };


  syncBootstrap(window.__SIMPM_BOOTSTRAP__);
  const originalDetailRenderer = window.renderTeknisiDetailJadwal;
  if (typeof originalDetailRenderer === 'function') {
    window.renderTeknisiDetailJadwal = function (pmId) {
      originalDetailRenderer(pmId);
      const sc = PM_SCHEDULES.find(function (p) { return p.id === pmId; });
      if (!sc || !sc.report) return;
      const r = sc.report;
      const fotoInput = document.getElementById('tekFotoInput');
      const fotoLabel = document.getElementById('tekFotoLabel');
      const fotoWrap = document.getElementById('tekFotoPreviewWrap');
      const fotoImg = document.getElementById('tekFotoPreview');
      if (r.fotoUrl && fotoImg && fotoWrap) {
        fotoImg.src = r.fotoUrl;
        fotoWrap.style.display = 'block';
        if (fotoLabel) fotoLabel.textContent = 'Foto tersimpan';
      }

      if (sc.status === 'ditolak') {
        const scope = document.getElementById('scr-tek2-detail-jadwal');
        const callout = document.getElementById('tekValidasiCallout');
        const actionBar = document.getElementById('tekActionBar');
        if (scope) scope.querySelectorAll('input, textarea, select').forEach(function (f) {
          f.disabled = false; f.style.background = '';
        });
        if (callout) {
          callout.style.display = 'block';
          callout.style.background = 'var(--red-soft)';
          callout.style.borderColor = '#E7B3A8';
          callout.style.color = '#9A2A16';
          callout.innerHTML = '<strong>Laporan ditolak Supervisor.</strong> Perbaiki laporan sesuai catatan: ' + escapeHtml(r.catatanSupervisor || 'Tidak ada catatan.') + ' Lalu kirim ulang untuk validasi.';
        }
        if (actionBar) {
          actionBar.innerHTML = '<button class="btn btn-amber" id="tekSubmitBtn" onclick="submitLaporanForValidation()">Perbaiki & Kirim Ulang untuk Validasi</button>'
            + '<button class="btn btn-outline" onclick="go(\'tek2-dashboard\')">Kembali</button>';
        }
      }
    };
  }

  const originalApplyBootstrap = window.applyBootstrap;
  if (typeof originalApplyBootstrap === 'function') {
    window.applyBootstrap = function (data) {
      originalApplyBootstrap(data);
      syncBootstrap(data);
    };
  }

  window.simpanJadwalBaru = function () {
    const machineId = document.getElementById('jtMesin').value;
    if (!machineId) {
      alert('Pilih mesin terlebih dahulu.');
      return;
    }

    const technician = technicianFromForm();
    if (!technician.name || !technician.id) {
      alert('Pilih teknisi dari akun pengguna yang tersedia.');
      return;
    }

    const payload = {
      id: 'pm-' + Date.now(),
      machineId: machineId,
      jenis: document.getElementById('jtJenis').value.trim() || 'Pemeriksaan rutin',
      teknisi: technician.name,
      teknisiUserId: technician.id,
      tanggal: document.getElementById('jtTanggal').value,
      interval: document.getElementById('jtInterval').value,
      estimasi: document.getElementById('jtDurasi').value.trim() || '-',
      prioritas: window.jtPrioritasVal || 'sedang',
      catatan: document.getElementById('jtCatatan').value.trim()
    };

    apiFetch('/api/pm-schedules', {
      method: 'POST',
      body: JSON.stringify(payload)
    })
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        go('sup2-maintenance');
        renderJadwalFilters();
        renderJadwalTable();
      })
      .catch(function (err) {
        alert('Gagal menyimpan jadwal: ' + err);
      });
  };

  window.submitLaporanForValidation = function () {
    const btn = document.getElementById('tekSubmitBtn');
    if (btn && btn.disabled) return;
    if (typeof currentTeknisiPmId === 'undefined' || !currentTeknisiPmId) {
      alert('Jadwal pemeriksaan tidak ditemukan.');
      return;
    }

    const report = buildReportFromForm();
    const pmId = currentTeknisiPmId;
    const fileInput = document.getElementById('tekFotoInput');
    const file = fileInput && fileInput.files ? fileInput.files[0] : null;
    const formData = new FormData();
    formData.append('report', JSON.stringify(report));
    if (file) formData.append('foto', file);

    if (btn) btn.disabled = true;
    apiUpload('/api/pm-schedules/' + encodeURIComponent(pmId) + '/laporan', formData)
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        const callout = document.getElementById('tekValidasiCallout');
        if (callout) {
          callout.style.display = 'block';
          callout.style.background = 'var(--purple-soft)';
          callout.style.borderColor = '#CBB9E5';
          callout.style.color = '#4A2F73';
          callout.textContent = 'Laporan sudah dikirim dan sedang menunggu validasi Supervisor.';
        }
        const badge = document.getElementById('tekDetailStatusBadge');
        if (badge) {
          badge.textContent = 'Menunggu Validasi';
          badge.className = 'badge b-purple';
        }
        const scope = document.getElementById('scr-tek2-detail-jadwal');
        if (scope) scope.querySelectorAll('input, textarea, select').forEach(function (f) {
          f.disabled = true;
          f.style.background = '#F5F6F7';
        });
        if (typeof renderTechnicianSubmissionCard === 'function') renderTechnicianSubmissionCard();
        if (typeof renderTeknisiDashboard === 'function') renderTeknisiDashboard();
      })
      .catch(function (err) {
        if (btn) btn.disabled = false;
        alert('Gagal mengirim laporan: ' + err);
      });
  };

  window.finalizeValidation = function () {
    if (typeof currentValidasiPmId === 'undefined' || !currentValidasiPmId) {
      go('sup2-validasi');
      return;
    }

    const approve = document.getElementById('voptApprove').classList.contains('on');
    const catatan = document.getElementById('vdCatatanSupervisor').value.trim();
    if (!approve && !catatan) {
      alert('Berikan catatan Supervisor agar Teknisi mengetahui bagian yang harus diperbaiki.');
      document.getElementById('vdCatatanSupervisor').focus();
      return;
    }

    const pmId = currentValidasiPmId;
    apiFetch('/api/pm-schedules/' + encodeURIComponent(pmId) + '/validasi', {
      method: 'POST',
      body: JSON.stringify({
        approve: approve,
        catatan: catatan,
        teknisiBerikutnya: document.getElementById('vdTeknisiBerikutnya').value,
        intervalBerikutnya: document.getElementById('vdIntervalBerikutnya').value,
        tanggalBerikutnya: document.getElementById('vdTanggalBerikutnya').value,
        durasiBerikutnya: document.getElementById('vdDurasiBerikutnya').value
      })
    })
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        go('sup2-validasi');
        renderValidasiQueue();
        renderValidasiHistory();
        renderJadwalTable();
        if (typeof renderTechnicianSubmissionCard === 'function') renderTechnicianSubmissionCard();
      })
      .catch(function (err) {
        alert('Gagal menyimpan validasi: ' + err);
      });
  };;
})();
