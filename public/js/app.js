
function toggleSidebar(){
  document.getElementById('appShell').classList.toggle('sidebar-toggled');
}
document.addEventListener('click', function(e){
  if(window.matchMedia('(max-width:760px)').matches){
    const fb = e.target.closest('.formula-box');
    if(fb){ fb.classList.toggle('fb-open'); }
  }
});

/* =====================================================================
   RIWAYAT MAINTENANCE
   ===================================================================== */
let riwayatFilter = {stationId:'all', machineId:'all', kategori:'all', periode:'all'};
function renderRiwayatFilters(){
  const stSel = document.getElementById('rwStasiunSel');
  const mSel = document.getElementById('rwMesinSel');
  if(!stSel || !mSel) return;
  stSel.innerHTML = stationOptionsHTML(true, riwayatFilter.stationId);
  mSel.innerHTML = machineOptionsHTML(riwayatFilter.stationId, true, riwayatFilter.machineId);
}
function onRiwayatStationChange(sel){
  riwayatFilter.stationId = sel.value;
  riwayatFilter.machineId = 'all';
  renderRiwayatFilters();
  renderRiwayatTable();
}
function onRiwayatFilterChange(){
  riwayatFilter.machineId = document.getElementById('rwMesinSel').value;
  riwayatFilter.kategori = document.getElementById('rwKategoriSel').value;
  riwayatFilter.periode = document.getElementById('rwPeriodeSel').value;
  renderRiwayatTable();
}
function renderRiwayatTable(){
  const tbody = document.getElementById('rwTbody');
  if(!tbody) return;
  let list = MAINTENANCE_HISTORY.slice();
  if(riwayatFilter.kategori && riwayatFilter.kategori!=='all'){
    list = list.filter(function(h){ return h.kategori===riwayatFilter.kategori; });
  }
  if(riwayatFilter.periode && riwayatFilter.periode!=='all'){
    list = list.filter(function(h){ return monthPrefix(h.tanggal)===riwayatFilter.periode; });
  }
  list.sort(function(a,b){ return b.tanggal.localeCompare(a.tanggal); });
  tbody.innerHTML = list.length ? list.map(function(h){
    const isSippm = h.source === 'sippm';
    const m = isSippm ? null : getMachine(h.machineId);
    const st = isSippm ? null : getStationOfMachine(m);
    const stationName = isSippm ? (h.stationName || '-') : stationLabel(st);
    const machineName = isSippm ? (h.machineName || '-') : machineLabel(m);
    return '<tr>'
      +'<td class="mono">'+escapeHtml(h.noLaporan)+'</td>'
      +'<td class="mono">'+formatTanggalID(h.tanggal)+'</td>'
      +'<td>'+escapeHtml(stationName)+'</td>'
      +'<td><strong>'+escapeHtml(machineName)+'</strong></td>'
      +'<td><span class="badge b-gray">'+escapeHtml(h.kategori)+'</span></td>'
      +'<td>'+escapeHtml(h.pekerjaan)+'</td>'
      +'<td>'+escapeHtml(h.pelaksana)+'</td>'
      +'<td class="mono">'+h.downtimeMenit+' mnt</td>'
      +'<td><span class="badge b-green">'+escapeHtml(h.hasil)+'</span></td>'
    +'</tr>';
  }).join('') : '<tr><td colspan="9" class="empty-state">Tidak ada riwayat yang sesuai dengan filter.</td></tr>';
}
