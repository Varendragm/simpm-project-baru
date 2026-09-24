/* SIMPM Sesi 3 hardening layer.
 * Loaded after the legacy UI script so operational data remains the source of truth.
 */
(function () {
  function todayIso() {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
  }

  function syncCurrentTechnician() {
    const user = window.__SIMPM_BOOTSTRAP__ && window.__SIMPM_BOOTSTRAP__.currentUser;
    if (typeof currentRole !== 'undefined' && currentRole === 'teknisi' && user) {
      USERS = USERS || {};
      USERS.teknisi = user;
    }
  }

  // Keep the master machine state and calculated condition semantically separate.
  // Existing UI calls that pass a master status still receive a safe visual state;
  // condition-aware screens should pass machine.kondisi.
  if (typeof statusMeta === 'function') {
    const legacyStatusMeta = statusMeta;
    statusMeta = function (status) {
      if (status === 'aktif') {
        return {label: 'Aktif', badgeClass: 'badge b-green', tier: 'normal', color: 'var(--green)'};
      }
      if (status === 'nonaktif') {
        return {label: 'Nonaktif', badgeClass: 'badge b-gray', tier: 'warning', color: 'var(--ink-soft)'};
      }
      return legacyStatusMeta(status);
    };
  }

  // Reports must use the real examination date, not the old demo date.
  if (typeof buildReportFromForm === 'function') {
    buildReportFromForm = function () {
      const original = window.__simpmOriginalBuildReportFromForm;
      if (original) return original();
      return null;
    };
  }

  // Preserve the existing report builder's fields while replacing only its date/user
  // source. The implementation mirrors the form contract used by the backend.
  window.__simpmBuildReportWithLiveDate = function () {
    const katEl = document.querySelector('#tekKategoriRow .vopt.on');
    const prioEl = document.querySelector('#tekPrioritasRow .prio.on');
    const kategori = katEl ? katEl.dataset.kat : 'mekanik';
    const prioritas = prioEl ? prioEl.dataset.p : 'sedang';
    const parameter = {};

    if (kategori === 'mekanik') {
      parameter.getaran = document.getElementById('tekMekGetaran')?.value || '';
      parameter.suhu = document.getElementById('tekMekSuhu')?.value || '';
      parameter.pelumasan = document.getElementById('tekMekPelumasan')?.value || '';
      parameter.torsi = document.getElementById('tekMekTorsi')?.value || '';
    } else if (kategori === 'elektrik') {
      parameter.tegangan = document.getElementById('tekElTegangan')?.value || '';
      parameter.arus = document.getElementById('tekElArus')?.value || '';
      parameter.isolasi = document.getElementById('tekElIsolasi')?.value || '';
      parameter.kondisi = document.getElementById('tekElKondisi')?.value || '';
    } else {
      parameter.nilaiBaca = document.getElementById('tekInsNilaiBaca')?.value || '';
      parameter.nilaiStandar = document.getElementById('tekInsNilaiStandar')?.value || '';
      parameter.deviasi = document.getElementById('tekInsDeviasi')?.value || '';
      parameter.statusKalibrasi = document.getElementById('tekInsStatus')?.value || '';
    }

    const spareparts = [];
    document.querySelectorAll('#tekSparepartBody tr').forEach(function (tr) {
      const inputs = tr.querySelectorAll('input,select');
      if (inputs.length >= 4 && inputs[0].value.trim()) {
        spareparts.push({nama: inputs[0].value, kode: inputs[1].value, qty: inputs[2].value, satuan: inputs[3].value});
      }
    });

    if (typeof validateTekChecklist === 'function' && !validateTekChecklist()) return null;

    const pemeriksa = (typeof currentUser !== 'undefined' && currentUser?.name)
      || (typeof USERS !== 'undefined' && USERS?.teknisi?.name)
      || '';

    return {
      tanggalPemeriksaan: todayIso(),
      dikirim: todayIso(),
      pemeriksa,
      kategori,
      prioritas,
      waktuMulai: document.getElementById('tekWaktuMulai')?.value || '',
      waktuSelesai: document.getElementById('tekWaktuSelesai')?.value || '',
      parameter,
      checklist: typeof getTekChecklist === 'function' ? getTekChecklist() : {},
      deskripsi: document.getElementById('tekDeskripsi')?.value || '',
      tindakan: document.getElementById('tekTindakan')?.value || '',
      spareparts,
      rekomendasi: document.getElementById('tekRekomendasi')?.value || '',
      jadwalBerikutnya: document.getElementById('tekJadwalBerikutnya')?.value || '',
      status: 'menunggu',
      catatanSupervisor: ''
    };
  };

  // Replace the legacy date-bound report builder with the live-date version.
  buildReportFromForm = window.__simpmBuildReportWithLiveDate;

  if (typeof refreshBootstrap === 'function') {
    const originalRefreshBootstrap = refreshBootstrap;
    refreshBootstrap = function () {
      return originalRefreshBootstrap().then(function (data) {
        syncCurrentTechnician();
        if (typeof setRole === 'function' && typeof currentRole !== 'undefined') {
          setRole(currentRole);
        }
        return data;
      });
    };
  }

  document.addEventListener('DOMContentLoaded', function () {
    syncCurrentTechnician();
    if (typeof setRole === 'function' && typeof currentRole !== 'undefined') {
      setRole(currentRole);
    }
  });
})();
