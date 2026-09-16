/* =====================================================================
   MASTER CRUD — Stasiun & Mesin
   ---------------------------------------------------------------------
   UI tetap mengikuti mockup. Status mesin adalah status master manual,
   sedangkan kondisi mesin berasal dari perhitungan performa otomatis.
   ===================================================================== */
(function () {
  function handleApiError(prefix, err) {
    const message = typeof err === 'string' ? err : (err && err.message) || 'Terjadi kesalahan.';
    alert(prefix + ': ' + message);
  }

  window.simpanStasiun = function (e) {
    if (e && e.preventDefault) e.preventDefault();

    const id = document.getElementById('fStasiunId').value.trim();
    const data = {
      id: id || ('st-' + Date.now()),
      code: document.getElementById('fStasiunKode').value.trim(),
      name: document.getElementById('fStasiunNama').value.trim(),
      location: document.getElementById('fStasiunLokasi').value.trim(),
      description: document.getElementById('fStasiunDeskripsi').value.trim(),
      status: document.getElementById('fStasiunStatus').value
    };

    const method = id ? 'PUT' : 'POST';
    const url = id ? '/api/stations/' + encodeURIComponent(id) : '/api/stations';

    apiFetch(url, { method: method, body: JSON.stringify(data) })
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        closeModal();
        renderMasterStasiunMesin();
      })
      .catch(function (err) { handleApiError('Gagal menyimpan stasiun', err); });

    return false;
  };

  window.simpanMesin = function (e) {
    if (e && e.preventDefault) e.preventDefault();

    const id = document.getElementById('fMesinId').value.trim();
    const data = {
      id: id || ('m-' + Date.now()),
      stationId: document.getElementById('fMesinStasiun').value,
      code: document.getElementById('fMesinKode').value.trim(),
      name: document.getElementById('fMesinNama').value.trim(),
      type: document.getElementById('fMesinJenis').value.trim(),
      status: document.getElementById('fMesinStatus').value,
      capacity: document.getElementById('fMesinKapasitas').value.trim(),
      year: document.getElementById('fMesinTahun').value.trim(),
      notes: document.getElementById('fMesinKeterangan').value.trim()
    };

    const method = id ? 'PUT' : 'POST';
    const url = id ? '/api/machines/' + encodeURIComponent(id) : '/api/machines';

    apiFetch(url, { method: method, body: JSON.stringify(data) })
      .then(function () { return refreshBootstrap(); })
      .then(function () {
        closeModal();
        renderMasterStasiunMesin();
      })
      .catch(function (err) { handleApiError('Gagal menyimpan mesin', err); });

    return false;
  };

  window.hapusStasiun = function (id) {
    const station = getStation(id);
    if (!station) return;
    if (!confirm('Hapus stasiun "' + station.name + '"?')) return;

    apiFetch('/api/stations/' + encodeURIComponent(id), { method: 'DELETE' })
      .then(function () { return refreshBootstrap(); })
      .then(function () { renderMasterStasiunMesin(); })
      .catch(function (err) { handleApiError('Gagal menghapus stasiun', err); });
  };

  window.hapusMesin = function (id) {
    const machine = getMachine(id);
    if (!machine) return;
    if (!confirm('Hapus mesin "' + machine.name + '"?')) return;

    apiFetch('/api/machines/' + encodeURIComponent(id), { method: 'DELETE' })
      .then(function () { return refreshBootstrap(); })
      .then(function () { renderMasterStasiunMesin(); })
      .catch(function (err) { handleApiError('Gagal menghapus mesin', err); });
  };

  function conditionMeta(kondisi) {
    if (kondisi === 'normal') return { badgeClass: 'badge b-green', label: 'Normal' };
    if (kondisi === 'perbaikan') return { badgeClass: 'badge b-red', label: 'Dalam Perbaikan' };
    return { badgeClass: 'badge b-yellow', label: 'Perlu Perhatian' };
  }

  window.renderMasterStasiunMesin = function () {
    const totalStEl = document.getElementById('masterTotalStasiun');
    const totalMsEl = document.getElementById('masterTotalMesin');
    if (totalStEl) totalStEl.textContent = STATIONS.length;
    if (totalMsEl) totalMsEl.textContent = MACHINES.length;

    const wrap = document.getElementById('masterStasiunList');
    if (!wrap) return;

    if (!STATIONS.length) {
      wrap.innerHTML = '<div class="empty-state">Belum ada stasiun. Klik "+ Tambah Stasiun" untuk memulai.</div>';
      return;
    }

    wrap.innerHTML = STATIONS.map(function (st) {
      const machines = getMachinesByStation(st.id);
      const statusBadge = st.status === 'aktif'
        ? '<span class="badge b-green">Aktif</span>'
        : '<span class="badge b-gray">Nonaktif</span>';

      const rows = machines.length ? machines.map(function (m) {
        const condition = conditionMeta(m.kondisi);
        const status = m.status === 'aktif'
          ? '<span class="badge b-green">Aktif</span>'
          : '<span class="badge b-gray">Nonaktif</span>';
        return '<tr>'
          + '<td class="mono">' + escapeHtml(m.code) + '</td>'
          + '<td><strong>' + escapeHtml(m.name) + '</strong></td>'
          + '<td>' + escapeHtml(m.type || '-') + '</td>'
          + '<td class="mono">' + escapeHtml(m.capacity || '-') + '</td>'
          + '<td class="mono">' + escapeHtml(m.year || '-') + '</td>'
          + '<td>' + status + ' ' + '<span class="' + condition.badgeClass + '">' + condition.label + '</span></td>'
          + '<td style="white-space:nowrap;">'
          + '<button class="btn btn-outline btn-sm" onclick="openModal(\'edit\',\'machine\',\'' + m.id + '\')">Edit</button> '
          + '<button class="btn btn-outline btn-sm" onclick="openDetailMesin(\'' + m.id + '\')">Detail</button> '
          + '<button class="btn btn-outline btn-sm" onclick="hapusMesin(\'' + m.id + '\')">Hapus</button>'
          + '</td>'
          + '</tr>';
      }).join('') : '<tr><td colspan="7" class="empty-state">Belum ada mesin pada stasiun ini.</td></tr>';

      return '<div class="station-block">'
        + '<div class="station-block-head">'
          + '<div>'
            + '<div class="station-block-title">' + escapeHtml(st.name) + ' <span class="mono" style="font-weight:400;color:var(--ink-soft);font-size:12px;">' + escapeHtml(st.code) + '</span></div>'
            + '<div class="station-block-sub">' + escapeHtml(st.location || '-') + ' · ' + machines.length + ' mesin</div>'
          + '</div>'
          + '<div class="station-block-actions">'
            + statusBadge
            + '<button class="btn btn-outline btn-sm" onclick="openModal(\'edit\',\'station\',\'' + st.id + '\')">Edit Stasiun</button>'
            + '<button class="btn btn-outline btn-sm" onclick="hapusStasiun(\'' + st.id + '\')">Hapus Stasiun</button>'
            + '<button class="btn btn-blue btn-sm" onclick="tambahMesinDiStasiun(\'' + st.id + '\')">+ Tambah Mesin</button>'
          + '</div>'
        + '</div>'
        + (st.description ? '<div class="station-block-desc">' + escapeHtml(st.description) + '</div>' : '')
        + '<div class="station-block-body"><div class="table-scroll"><table><thead><tr><th>Kode</th><th>Nama Mesin</th><th>Jenis</th><th>Kapasitas</th><th>Tahun</th><th>Status / Kondisi</th><th></th></tr></thead><tbody>' + rows + '</tbody></table></div></div>'
      + '</div>';
    }).join('');
  };

  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
      if (typeof refreshBootstrap === 'function') {
        refreshBootstrap().then(function () {
          renderMasterStasiunMesin();
        }).catch(function () {
          renderMasterStasiunMesin();
        });
      }
    }, 0);
  });
})();
