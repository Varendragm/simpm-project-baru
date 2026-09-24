/* =====================================================================
   TEKNISI PM — ACCOUNT-BASED FILTER
   Jadwal milik teknisi yang sedang login ditentukan oleh teknisiUserId.
   Fallback nama hanya untuk data lama yang belum memiliki ID.
   ===================================================================== */
(function () {
  function me() {
    return window.SIMPM_CURRENT_USER || (window.__SIMPM_BOOTSTRAP__ && window.__SIMPM_BOOTSTRAP__.currentUser) || null;
  }

  function isMine(schedule) {
    const user = me();
    if (!user || user.role !== 'teknisi') return false;
    if (schedule.teknisiUserId !== null && schedule.teknisiUserId !== undefined) {
      return String(schedule.teknisiUserId) === String(user.id);
    }
    return schedule.teknisi === user.name;
  }

  function mySchedules() {
    return PM_SCHEDULES.filter(isMine);
  }

  window.renderTechnicianSubmissionCard = function () {
    const el = document.getElementById('tekSubmissionCard');
    if (!el) return;
    const user = me();
    const reports = mySchedules().filter(function (p) { return p.report && p.status !== 'terjadwal'; })
      .sort(function (a, b) {
        return String(b.report.dikirim || b.report.tanggalPemeriksaan || '').localeCompare(String(a.report.dikirim || a.report.tanggalPemeriksaan || ''));
      });
    if (!reports.length) {
      el.style.display = 'none';
      el.innerHTML = '';
      return;
    }
    el.style.display = 'grid';
    el.style.gap = '10px';
    // Dashboard hanya menampilkan status laporan terakhir; riwayat lengkap tetap di menu Riwayat.
    el.innerHTML = reports.slice(0, 1).map(function (p) {
      const m = getMachine(p.machineId);
      const rejected = p.status === 'ditolak';
      const approved = p.status === 'selesai';
      const title = rejected ? 'Perlu Perbaikan Laporan' : (approved ? 'Laporan Terkonfirmasi' : 'Laporan Terkirim');
      const tone = rejected ? 'var(--red-soft)' : (approved ? 'var(--green-soft)' : 'var(--purple-soft)');
      const border = rejected ? '#E7B3A8' : (approved ? '#BFE0C9' : '#CBB9E5');
      const status = rejected ? 'Ditolak Supervisor' : (approved ? 'Disetujui Supervisor' : 'Menunggu Validasi Supervisor');
      const action = rejected ? 'Perbaiki & Lanjut' : 'Lanjut ke Preview';
      return '<div class="callout" style="display:block;background:' + tone + ';border-color:' + border + ';">'
        + '<div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">'
        + '<div><strong>' + title + '</strong><div style="margin-top:4px;font-size:12px;">' + escapeHtml(machineLabel(m)) + ' · ' + escapeHtml(p.jenis) + '</div>'
        + '<div style="margin-top:3px;font-size:11px;color:var(--ink-soft);">' + status + (rejected && p.report.catatanSupervisor ? ' · Catatan: ' + escapeHtml(p.report.catatanSupervisor) : '') + '</div></div>'
        + '<button class="btn btn-outline btn-sm" onclick="openTeknisiDetail(\'' + p.id + '\')">' + action + '</button>'
        + '</div></div>';
    }).join('');
  };

  window.renderTeknisiDashboard = function () {
    const user = me();
    const mine = mySchedules();
    const name = user ? user.name : '-';

    setText('tekStatMingguIni', mine.filter(function (p) { return p.status !== 'selesai'; }).length);
    setText('tekStatJatuhTempo', mine.filter(function (p) { return p.tanggal === DEMO_TODAY && p.status !== 'selesai'; }).length);
    const selesaiBulanIni = mine.filter(function (p) { return p.status === 'selesai'; }).length
      + MAINTENANCE_HISTORY.filter(function (h) { return h.pelaksana === name; }).length;
    setText('tekStatSelesaiBulan', selesaiBulanIni);

    renderTechnicianSubmissionCard();

    const tbody = document.getElementById('tekJadwalTbody');
    if (!tbody) return;
    const sorted = mine.slice().sort(function (a, b) { return a.tanggal.localeCompare(b.tanggal); });
    tbody.innerHTML = sorted.length ? sorted.map(function (p) {
      const m = getMachine(p.machineId);
      let displayMeta = PM_STATUS_META[p.status] || PM_STATUS_META.terjadwal;
      if (p.status === 'terjadwal' && p.tanggal === DEMO_TODAY) displayMeta = { label: 'Jatuh Tempo Hari Ini', badgeClass: 'badge b-amber' };
      const btnLabel = p.status === 'terjadwal' ? 'Buka' : 'Lihat';
      const btnClass = (p.status === 'terjadwal' && p.tanggal === DEMO_TODAY) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
      return '<tr><td><strong>' + escapeHtml(machineLabel(m)) + '</strong></td><td>' + escapeHtml(p.jenis) + '</td><td class="mono">' + formatTanggalID(p.tanggal) + '</td><td><span class="' + displayMeta.badgeClass + '">' + displayMeta.label + '</span></td><td><button class="' + btnClass + '" onclick="openTeknisiDetail(\'' + p.id + '\')">' + btnLabel + '</button></td></tr>';
    }).join('') : '<tr><td colspan="5" class="empty-state">Tidak ada jadwal maintenance ditugaskan ke akun Anda.</td></tr>';
  };

  const oldApplyBootstrapTek = window.applyBootstrap;
  if (typeof oldApplyBootstrapTek === 'function') {
    window.applyBootstrap = function (data) {
      oldApplyBootstrapTek(data);
      renderTechnicianSubmissionCard();
    };
  }

  window.renderTeknisiCalendar = function () {
    const mine = mySchedules();
    document.querySelectorAll('#tekCalGrid .cal-cell[data-day]').forEach(function (cell) {
      const day = cell.getAttribute('data-day');
      const iso = '2026-08-' + (day.length === 1 ? '0' + day : day);
      const daynum = cell.querySelector('.cal-daynum');
      let evts = '';
      mine.filter(function (p) { return p.tanggal === iso; }).forEach(function (p) {
        const m = getMachine(p.machineId);
        const cls = p.status === 'selesai' ? 'done' : (iso === DEMO_TODAY ? 'due' : '');
        const label = p.status === 'selesai' ? 'Selesai' : p.jenis.split(' ').slice(0, 2).join(' ');
        evts += '<div class="cal-evt ' + cls + '" onclick="openTeknisiDetail(\'' + p.id + '\')">' + escapeHtml(machineLabel(m)) + ' · ' + escapeHtml(label) + '</div>';
      });
      const name = me() ? me().name : '';
      MAINTENANCE_HISTORY.filter(function (h) { return h.pelaksana === name && h.tanggal === iso; }).forEach(function (h) {
        const m = getMachine(h.machineId);
        evts += '<div class="cal-evt done" onclick="go(\'tek2-riwayat\')">' + escapeHtml(machineLabel(m)) + ' · Selesai</div>';
      });
      cell.innerHTML = '';
      if (daynum) cell.appendChild(daynum);
      cell.insertAdjacentHTML('beforeend', evts);
    });
  };

  window.renderTeknisiRiwayat = function () {
    const user = me();
    const tbody = document.getElementById('tekRiwayatTbody');
    if (!tbody) return;
    const list = MAINTENANCE_HISTORY.filter(function (h) {
      return user && h.pelaksana === user.name;
    }).slice().sort(function (a, b) { return b.tanggal.localeCompare(a.tanggal); });
    tbody.innerHTML = list.length ? list.map(function (h) {
      const m = getMachine(h.machineId);
      return '<tr><td class="mono">' + escapeHtml(h.noLaporan) + '</td><td>' + escapeHtml(machineLabel(m)) + '</td><td>' + escapeHtml(h.pekerjaan) + '</td><td class="mono">' + h.downtimeMenit + ' menit</td><td class="mono">' + formatTanggalID(h.tanggal) + '</td></tr>';
    }).join('') : '<tr><td colspan="5" class="empty-state">Belum ada riwayat perbaikan untuk akun Anda.</td></tr>';
  };
})();
