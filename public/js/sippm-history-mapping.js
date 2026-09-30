/*
 * SIPPM history mapping
 * ---------------------
 * SIPPM corrective-maintenance history owns its station/machine names.
 * Do not require SIPPM machines to exist in SIMPM's persistent master data.
 * This override is intentionally limited to the Supervisor Riwayat table.
 */
(function(){
  function historyStationName(h){
    if (h && h.stationName) return h.stationName;
    const m = h ? getMachine(h.machineId) : null;
    return stationLabel(getStationOfMachine(m));
  }

  function historyMachineName(h){
    if (h && h.machineName) return h.machineName;
    return machineLabel(h ? getMachine(h.machineId) : null);
  }

  function isSippmHistory(h){
    return !!(h && (h.source === 'sippm' || h.machineName || h.stationName));
  }

  window.renderRiwayatTable = function(){
    const tbody = document.getElementById('rwTbody');
    if(!tbody) return;

    let list = MAINTENANCE_HISTORY.slice();

    if(riwayatFilter.stationId && riwayatFilter.stationId!=='all'){
      list = list.filter(function(h){
        if(isSippmHistory(h)) return false;
        const m = getMachine(h.machineId);
        return m && m.stationId===riwayatFilter.stationId;
      });
    }

    if(riwayatFilter.machineId && riwayatFilter.machineId!=='all'){
      list = list.filter(function(h){
        if(isSippmHistory(h)) return false;
        return h.machineId===riwayatFilter.machineId;
      });
    }

    if(riwayatFilter.kategori && riwayatFilter.kategori!=='all'){
      list = list.filter(function(h){ return h.kategori===riwayatFilter.kategori; });
    }

    if(riwayatFilter.periode && riwayatFilter.periode!=='all'){
      list = list.filter(function(h){ return monthPrefix(h.tanggal)===riwayatFilter.periode; });
    }

    list.sort(function(a,b){ return b.tanggal.localeCompare(a.tanggal); });

    tbody.innerHTML = list.length ? list.map(function(h){
      return '<tr>'
        +'<td class="mono">'+escapeHtml(h.noLaporan)+'</td>'
        +'<td class="mono">'+formatTanggalID(h.tanggal)+'</td>'
        +'<td>'+escapeHtml(historyStationName(h))+'</td>'
        +'<td><strong>'+escapeHtml(historyMachineName(h))+'</strong></td>'
        +'<td><span class="badge b-gray">'+escapeHtml(h.kategori)+'</span></td>'
        +'<td>'+escapeHtml(h.pekerjaan)+'</td>'
        +'<td>'+escapeHtml(h.pelaksana)+'</td>'
        +'<td class="mono">'+h.downtimeMenit+' mnt</td>'
        +'<td><span class="badge b-green">'+escapeHtml(h.hasil)+'</span></td>'
      +'</tr>';
    }).join('') : '<tr><td colspan="9" class="empty-state">Tidak ada riwayat yang sesuai dengan filter.</td></tr>';
  };
})();
