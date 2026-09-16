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

  syncCurrentUser(window.__SIMPM_BOOTSTRAP__);
  const originalApplyBootstrap = window.applyBootstrap;
  if (typeof originalApplyBootstrap === 'function') {
    window.applyBootstrap = function (data) {
      originalApplyBootstrap(data);
      syncCurrentUser(data);
    };
  }

  function technicianFromForm() {
    const name = (document.getElementById('jtTeknisi')?.value || '').trim();
    const known = window.USERS && window.USERS.teknisi ? window.USERS.teknisi : null;
    return { name: name, id: known && known.name === name ? known.id : null };
  }

  window.simpanJadwalBaru = function () {
    const machineId = document.getElementById('jtMesin').value;
    if (!machineId) { alert('Pilih mesin terlebih dahulu.'); return; }

    const technician = technicianFromForm();
    if (!technician.name) { alert('Pilih atau isi teknisi terlebih dahulu.'); return; }
    if (!technician.id) { alert('Teknisi tidak ditemukan sebagai akun pengguna.'); return; }

    const payload = {
      id: 'pm-' + Date.now(),
      machineId: machineId,
      jenis: document.getElementById('jtJenis').value.trim() || 'Pemeriksaan rutin',
      teknisi: technician.name,
      teknisiUserId: technician.id,
      tanggal: document.getElementById('jtTanggal').value,
      interval: document.getElementById('jtInterval').value,
      estimasi: document.getElementById('jtDurasi').value.trim() || '-',
      prioritas: jtPrioritasVal || 'sedang',
      catatan: document.getElementById('jtCatatan').value.trim()
    };

    apiFetch('/api/pm-schedules', { method: 'POST', body: JSON.stringify(payload) })
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        go('sup2-maintenance');
        renderJadwalFilters();
        renderJadwalTable();
      })
      .catch(function (err) { alert('Gagal menyimpan jadwal: ' + err); });
  };

  window.submitLaporanForValidation = function () {
    const btn = document.getElementById('tekSubmitBtn');
    if (btn && btn.disabled) return;
    if (!currentTeknisiPmId) { alert('Jadwal pemeriksaan tidak ditemukan.'); return; }

    const report = buildReportFromForm();
    const pmId = currentTeknisiPmId;
    apiFetch('/api/pm-schedules/' + encodeURIComponent(pmId) + '/laporan', { method: 'POST', body: JSON.stringify(report) })
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        const callout = document.getElementById('tekValidasiCallout');
        if (callout) {
          callout.style.display = 'block';
          callout.style.background = 'var(--purple-soft)';
          callout.style.borderColor = '#CBB9E5';
          callout.style.color = '#4A2F73';
          callout.textContent = 'Laporan sudah dikirim dan sedang menunggu validasi Supervisor. Anda akan diberi tahu setelah divalidasi.';
        }
        const badge = document.getElementById('tekDetailStatusBadge');
        if (badge) { badge.textContent = 'Menunggu Validasi'; badge.className = 'badge b-purple'; }
        renderTeknisiDashboard();
      })
      .catch(function (err) { alert('Gagal mengirim laporan: ' + err); });
  };

  window.finalizeValidation = function () {
    if (!currentValidasiPmId) { go('sup2-validasi'); return; }
    const approve = document.getElementById('voptApprove').classList.contains('on');
    const catatan = document.getElementById('vdCatatanSupervisor').value.trim();
    const pmId = currentValidasiPmId;
    apiFetch('/api/pm-schedules/' + encodeURIComponent(pmId) + '/validasi', {
      method: 'POST', body: JSON.stringify({ approve: approve, catatan: catatan })
    })
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        go('sup2-validasi');
        renderValidasiQueue();
        renderValidasiHistory();
        renderJadwalTable();
      })
      .catch(function (err) { alert('Gagal menyimpan validasi: ' + err); });
  };
})();
