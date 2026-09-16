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

  window.renderTeknisiDashboard = function () {
    const user = me();
    const mine = mySchedules();
    const name = user ? user.name : '-';

    setText('tekStatMingguIni', mine.filter(function (p) { return p.status !== 'selesai'; }).length);
    setText('tekStatJatuhTempo', mine.filter(function (p) { return p.tanggal === DEMO_TODAY && p.status !== 'selesai'; }).length);
    const selesaiBulanIni = mine.filter(function (p) { return p.status === 'selesai'; }).length
      + MAINTENANCE_HISTORY.filter(function (h) { return h.pelaksana === name; }).length;
    setText('tekStatSelesaiBulan', selesaiBulanIni);

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
