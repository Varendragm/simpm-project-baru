
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
   DATA MODEL
   ---------------------------------------------------------------------
   Struktur di bawah ini merepresentasikan tabel STASIUN & MESIN yang
   nantinya diambil dari database. Semua halaman (Performa, Jadwal PM,
   Validasi, Riwayat, Laporan, Dashboard) membaca dari array-array ini
   lewat fungsi render — bukan dari HTML hardcode. Mengganti isi array
   ini dengan hasil fetch() dari API tidak mengubah cara halaman bekerja.

   Kontrak integrasi dengan SIPPM (Sistem Pelaporan Kerusakan Mesin):
   setiap "Laporan Kerusakan" dari SIPPM diharapkan memiliki field
   {machineId, kategori, pekerjaan, pelaksana, downtimeMenit, tanggal}
   — bentuk yang sama dengan satu entri MAINTENANCE_HISTORY di bawah.
   Saat integrasi database dilakukan, laporan baru dari SIPPM cukup
   di-push ke MAINTENANCE_HISTORY (atau sumber setara di backend) agar
   otomatis muncul di Riwayat Maintenance, Performa Mesin, dan Laporan.
   ===================================================================== */

/* =====================================================================
   DATA MODEL — dimuat dari backend Laravel (lihat routes/api.php &
   App\Http\Controllers\Api\BootstrapController). Nama variabel & bentuk
   datanya sengaja dibuat identik dengan mockup asli supaya seluruh fungsi
   render di bawah ini tidak perlu diubah sama sekali.
   ===================================================================== */
let STATIONS = [];
let MACHINES = [];
let MACHINE_PERFORMANCE = {};
let PM_SCHEDULES = [];
let VALIDATION_HISTORY = [];
let MAINTENANCE_HISTORY = [];
let USERS = {};

function applyBootstrap(data){
  STATIONS = data.stations || [];
  MACHINES = data.machines || [];
  MACHINE_PERFORMANCE = data.machinePerformance || {};
  PM_SCHEDULES = data.pmSchedules || [];
  VALIDATION_HISTORY = data.validationHistory || [];
  MAINTENANCE_HISTORY = data.maintenanceHistory || [];
  USERS = data.users || USERS;
}
if(window.__SIMPM_BOOTSTRAP__){ applyBootstrap(window.__SIMPM_BOOTSTRAP__); }

function csrfToken(){
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}
function apiFetch(url, options){
  options = options || {};
  options.headers = Object.assign({
    'Content-Type':'application/json',
    'Accept':'application/json',
    'X-CSRF-TOKEN': csrfToken(),
    'X-Requested-With':'XMLHttpRequest'
  }, options.headers||{});
  return fetch(url, options).then(function(res){
    if(!res.ok){ return res.json().catch(function(){ return {}; }).then(function(body){ throw (body.message || ('Request gagal ('+res.status+')')); }); }
    return res.json().catch(function(){ return {}; });
  });
}
function refreshBootstrap(){
  return apiFetch('/api/bootstrap').then(function(data){ applyBootstrap(data); });
}

const DEMO_TODAY = '2026-08-18';
const WEEK_LABELS = ['Mgg 1','Mgg 2','Mgg 3','Mgg 4','Mgg 5','Mgg 6'];
const WEEKLY_AVAILABILITY_TREND = [86,88,84,91,93,91];
const LAPORAN_MONTH_LABELS = ['Mei','Jun','Jul','Agu'];
const MONTHLY_OEE_TREND = [74,76,77,78];
const MONTHLY_DOWNTIME_TREND_CUM = [18,34,55,80];

const STATUS_META = {
  normal:{label:'Normal', badgeClass:'badge b-green', tier:'normal', color:'var(--green)'},
  perhatian:{label:'Perlu Perhatian', badgeClass:'badge b-red', tier:'warning', color:'var(--red)'},
  perbaikan:{label:'Dalam Perbaikan', badgeClass:'badge b-amber', tier:'critical', color:'var(--amber)'}
};
const PM_STATUS_META = {
  terjadwal:{label:'Terjadwal', badgeClass:'badge b-blue'},
  'menunggu-validasi':{label:'Menunggu Validasi', badgeClass:'badge b-purple'},
  selesai:{label:'Selesai', badgeClass:'badge b-green'},
  ditolak:{label:'Ditolak', badgeClass:'badge b-red'}
};
const PRIO_META = {
  rendah:{label:'Rendah', cls:'urg rendah'},
  sedang:{label:'Sedang', cls:'urg sedang'},
  tinggi:{label:'Tinggi', cls:'urg tinggi'},
  kritis:{label:'Kritis', cls:'urg tinggi'}
};
const BULAN_ID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

/* ---------- Helper lookup & format ---------- */
function getStation(id){ return STATIONS.find(function(s){ return s.id===id; }); }
function getMachine(id){ return MACHINES.find(function(m){ return m.id===id; }); }
function getMachinesByStation(stationId){ return MACHINES.filter(function(m){ return m.stationId===stationId; }); }
function getStationOfMachine(machine){ return machine ? getStation(machine.stationId) : null; }
function getPerf(machineId){ return MACHINE_PERFORMANCE[machineId] || {oee:0, availability:0, reliability:0, mttr:0, mtbf:0, downtimeBulanIni:0, perbaikanBulanIni:0, trendOee:[0,0,0,0,0,0]}; }
function statusMeta(status){ return STATUS_META[status] || STATUS_META.normal; }
function escapeHtml(str){
  if(str === undefined || str === null) return '';
  return String(str).replace(/[&<>"']/g, function(c){
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
  });
}
function formatTanggalID(iso){
  if(!iso) return '-';
  const parts = iso.split('-');
  if(parts.length < 3) return iso;
  const y = parts[0], m = parseInt(parts[1],10), d = parts[2];
  return d + ' ' + (BULAN_ID[m-1] || '') + ' ' + y;
}
function monthPrefix(iso){ return iso ? iso.slice(0,7) : ''; }
function machineLabel(m){ return m ? m.name : '(mesin tidak ditemukan)'; }
function stationLabel(st){ return st ? st.name : '(stasiun tidak ditemukan)'; }

/* =====================================================================
   BUILDER GRAFIK SVG GENERIK
   ---------------------------------------------------------------------
   Grafik dibangun dari data, bukan dikodekan per mesin — jumlah mesin
   atau stasiun bisa bertambah tanpa mengubah fungsi ini.
   ===================================================================== */
function buildBarChartSVG(items, opts){
  opts = opts || {};
  const width = opts.width || 600, height = opts.height || 220;
  const padL = 42, padR = 20, padT = 26, padB = 34;
  const innerW = width - padL - padR, innerH = height - padT - padB;
  if(!items.length){
    return '<div class="empty-state">Belum ada data untuk ditampilkan.</div>';
  }
  const rawMax = Math.max.apply(null, items.map(function(i){ return i.value || 0; }));
  const max = opts.maxValue || Math.max(rawMax * 1.2, 5);
  const n = items.length;
  const gap = n > 8 ? 10 : 18;
  const barW = Math.min(46, (innerW - gap*(n-1)) / n);
  const usedW = barW*n + gap*(n-1);
  const startX = padL + Math.max(0,(innerW-usedW)/2);
  let bars = '', labels = '', gridlines = '';
  const steps = 4;
  for(let s=0; s<=steps; s++){
    const y = padT + innerH - (innerH*s/steps);
    const val = (max*s/steps);
    gridlines += '<line x1="'+padL+'" y1="'+y.toFixed(1)+'" x2="'+(width-padR)+'" y2="'+y.toFixed(1)+'" stroke="var(--line-soft)" stroke-dasharray="3 3"/>';
    gridlines += '<text x="'+(padL-8)+'" y="'+(y+4).toFixed(1)+'" class="chart-value-label" text-anchor="end">'+Math.round(val)+(opts.unit||'')+'</text>';
  }
  items.forEach(function(it, i){
    const x = startX + i*(barW+gap);
    const h = Math.max(2, innerH * ((it.value||0)/max));
    const y = padT + innerH - h;
    const op = (it.opacity===undefined) ? 1 : it.opacity;
    bars += '<rect x="'+x.toFixed(1)+'" y="'+y.toFixed(1)+'" width="'+barW.toFixed(1)+'" height="'+h.toFixed(1)+'" rx="2" fill="'+(it.color||'var(--blue)')+'" fill-opacity="'+op+'"/>';
    bars += '<text x="'+(x+barW/2).toFixed(1)+'" y="'+(y-7).toFixed(1)+'" class="chart-value-label" text-anchor="middle" opacity="'+op+'">'+it.value+(opts.unit||'')+'</text>';
    const shortLabel = (opts.shortLabel && it.label.length > 10) ? it.label.slice(0,9)+'…' : it.label;
    labels += '<text x="'+(x+barW/2).toFixed(1)+'" y="'+(height-14)+'" class="chart-axis-label" text-anchor="middle">'+escapeHtml(shortLabel)+'</text>';
  });
  return '<svg viewBox="0 0 '+width+' '+height+'" style="width:100%;height:auto;">'
    + '<line x1="'+padL+'" y1="'+(padT+innerH).toFixed(1)+'" x2="'+(width-padR)+'" y2="'+(padT+innerH).toFixed(1)+'" stroke="var(--line)"/>'
    + gridlines + bars + labels
    + '</svg>';
}

function buildLineChartSVG(values, opts){
  opts = opts || {};
  const width = opts.width || 600, height = opts.height || 210;
  const padL = 42, padR = 20, padT = 14, padB = 34;
  const innerW = width - padL - padR, innerH = height - padT - padB;
  if(!values.length) return '<div class="empty-state">Belum ada data untuk ditampilkan.</div>';
  const min = (opts.min !== undefined) ? opts.min : Math.min.apply(null, values);
  const max = (opts.max !== undefined) ? opts.max : Math.max.apply(null, values);
  const n = values.length;
  const stepX = n > 1 ? innerW/(n-1) : 0;
  const pts = values.map(function(v, i){
    const x = padL + stepX*i;
    const t = (max > min) ? (v-min)/(max-min) : 0.5;
    const y = padT + innerH - t*innerH;
    return [x, y];
  });
  const ptsStr = pts.map(function(p){ return p[0].toFixed(1)+','+p[1].toFixed(1); }).join(' ');
  let gridlines = '';
  const steps = 4;
  for(let s=0; s<=steps; s++){
    const y = padT + innerH - innerH*s/steps;
    const val = min + (max-min)*s/steps;
    gridlines += '<line x1="'+padL+'" y1="'+y.toFixed(1)+'" x2="'+(width-padR)+'" y2="'+y.toFixed(1)+'" stroke="var(--line-soft)" stroke-dasharray="3 3"/>';
    gridlines += '<text x="'+(padL-8)+'" y="'+(y+4).toFixed(1)+'" class="chart-value-label" text-anchor="end">'+Math.round(val)+(opts.unit||'')+'</text>';
  }
  let area = '';
  if(opts.area){
    const first = pts[0], last = pts[pts.length-1];
    area = '<path d="M'+first[0].toFixed(1)+','+(padT+innerH).toFixed(1)+' L'+pts.map(function(p){ return p[0].toFixed(1)+','+p[1].toFixed(1); }).join(' L')+' L'+last[0].toFixed(1)+','+(padT+innerH).toFixed(1)+' Z" fill="'+(opts.color||'var(--blue)')+'" opacity="0.18"/>';
  }
  const dots = pts.map(function(p){ return '<circle cx="'+p[0].toFixed(1)+'" cy="'+p[1].toFixed(1)+'" r="4"/>'; }).join('');
  let axisLabels = '';
  if(opts.labels){
    opts.labels.forEach(function(lab, i){
      if(pts[i]) axisLabels += '<text x="'+pts[i][0].toFixed(1)+'" y="'+(height-14)+'" class="chart-axis-label" text-anchor="middle">'+escapeHtml(lab)+'</text>';
    });
  }
  return '<svg viewBox="0 0 '+width+' '+height+'" style="width:100%;height:auto;">'
    + '<line x1="'+padL+'" y1="'+(padT+innerH).toFixed(1)+'" x2="'+(width-padR)+'" y2="'+(padT+innerH).toFixed(1)+'" stroke="var(--line)"/>'
    + gridlines + area
    + '<polyline points="'+ptsStr+'" fill="none" stroke="'+(opts.color||'var(--blue)')+'" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>'
    + '<g fill="'+(opts.color||'var(--blue)')+'">'+dots+'</g>'
    + axisLabels
    + '</svg>';
}

/* =====================================================================
   NAVIGASI
   ===================================================================== */
const NAV = {
  supervisor: [
    {group:'Utama', items:[
      {id:'sup2-dashboard', label:'Dashboard', ic:'dashboard'},
    ]},
    {group:'Monitoring', items:[
      {id:'sup2-monitoring', label:'Performa Mesin', ic:'monitor'},
      {id:'sup2-detail-mesin', label:'Detail Mesin', ic:'detail'},
    ]},
    {group:'Pemeliharaan', items:[
      {id:'sup2-maintenance', label:'Jadwal Preventive', ic:'schedule'},
      {id:'sup2-jadwal-tambah', label:'Tambah Jadwal', ic:'add', hidden:true},
      {id:'sup2-validasi', label:'Validasi Pemeriksaan', ic:'check'},
      {id:'sup2-validasi-detail', label:'Detail Validasi', ic:'check', hidden:true},
      {id:'sup2-riwayat', label:'Riwayat Maintenance', ic:'history'},
    ]},
    {group:'Data', items:[
      {id:'sup2-master', label:'Stasiun & Mesin', ic:'station'},
    ]},
    {group:'Laporan', items:[
      {id:'sup2-laporan', label:'Laporan & Grafik', ic:'report'},
    ]},
    {group:'Akun', items:[
      {id:'sup2-profil', label:'Profil Saya', ic:'profile'},
    ]}
  ],
  teknisi: [
    {group:'Menu', items:[
      {id:'tek2-dashboard', label:'Jadwal Maintenance Saya', ic:'dashboard'},
      {id:'tek2-detail-jadwal', label:'Detail Jadwal', ic:'detail', hidden:true},
      {id:'tek2-kalender', label:'Kalender Jadwal', ic:'calendar'},
      {id:'tek2-riwayat', label:'Riwayat Perbaikan Saya', ic:'history'},
    ]},
    {group:'Performa', items:[
      {id:'tek2-performa', label:'Performa Mesin Saya', ic:'report'},
    ]},
    {group:'Akun', items:[
      {id:'tek2-profil', label:'Profil Saya', ic:'profile'},
    ]}
  ],
  manajer: [
    {group:'Eksekutif', items:[
      {id:'man-dashboard', label:'Dashboard Eksekutif', ic:'dashboard'},
      {id:'man-performa', label:'Performa Seluruh Mesin', ic:'monitor'},
      {id:'man-maintenance', label:'Analisis Maintenance', ic:'schedule'},
    ]},
    {group:'Laporan', items:[
      {id:'man-laporan', label:'Unduh Laporan', ic:'report'},
    ]},
    {group:'Akun', items:[
      {id:'man-profil', label:'Profil Saya', ic:'profile'},
    ]}
  ]
};

const TITLES = {
  'sup2-dashboard':['Supervisor','Dashboard'],
  'sup2-master':['Supervisor','Stasiun & Mesin'],
  'sup2-monitoring':['Supervisor','Performa Mesin'],
  'sup2-detail-mesin':['Supervisor','Detail Mesin'],
  'sup2-maintenance':['Supervisor','Jadwal Preventive'],
  'sup2-jadwal-tambah':['Supervisor','Tambah Jadwal Maintenance'],
  'sup2-validasi':['Supervisor','Validasi Pemeriksaan'],
  'sup2-validasi-detail':['Supervisor','Detail Validasi Pemeriksaan'],
  'sup2-riwayat':['Supervisor','Riwayat Maintenance'],
  'sup2-laporan':['Supervisor','Laporan & Grafik'],
  'sup2-profil':['Supervisor','Profil Saya'],
  'tek2-dashboard':['Teknisi','Jadwal Maintenance Saya'],
  'tek2-detail-jadwal':['Teknisi','Detail Jadwal PM'],
  'tek2-kalender':['Teknisi','Kalender Jadwal'],
  'tek2-riwayat':['Teknisi','Riwayat Perbaikan Saya'],
  'tek2-performa':['Teknisi','Performa Mesin Saya'],
  'tek2-profil':['Teknisi','Profil Saya'],
  'man-dashboard':['Manajer','Dashboard Eksekutif'],
  'man-performa':['Manajer','Performa Seluruh Mesin'],
  'man-maintenance':['Manajer','Analisis Maintenance'],
  'man-laporan':['Manajer','Unduh Laporan'],
  'man-profil':['Manajer','Profil Saya'],
};

let currentRole = (window.__SIMPM_BOOTSTRAP__ && window.__SIMPM_BOOTSTRAP__.currentRole) || 'supervisor';
let currentDetailMachineId = null;
let currentValidasiPmId = null;
let currentTeknisiPmId = null;
let currentManPerformaId = 'all';
let jtPrioritasVal = 'sedang';

const FLAT_ORDER = {};
Object.keys(NAV).forEach(function(role){
  const arr = [];
  NAV[role].forEach(function(group){
    group.items.forEach(function(item){ arr.push(item.id); });
  });
  FLAT_ORDER[role] = arr;
});
let lastScreenId = null;

const ICONS = {
  'dashboard': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1.5" y="1.5" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.3"/><rect x="8.5" y="1.5" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.3"/><rect x="1.5" y="8.5" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.3"/><rect x="8.5" y="8.5" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.3"/></svg>',
  'monitor': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="8.5" width="3" height="5.5" rx="0.6" stroke="currentColor" stroke-width="1.3"/><rect x="6.5" y="5" width="3" height="9" rx="0.6" stroke="currentColor" stroke-width="1.3"/><rect x="11" y="2" width="3" height="12" rx="0.6" stroke="currentColor" stroke-width="1.3"/></svg>',
  'detail': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2.5" y="1.5" width="11" height="13" rx="1.2" stroke="currentColor" stroke-width="1.3"/><path d="M5 5h6M5 8h6M5 11h3.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>',
  'station': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1.5" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/><rect x="9.5" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/><rect x="1.5" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/><rect x="9.5" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/><path d="M4 7v2M12 7v2M6.5 4.5h3M6.5 11.5h3" stroke="currentColor" stroke-width="1.2"/></svg>',
  'schedule': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.6 2.4a3 3 0 0 0-3.9 3.9L2 11l2 2 4.7-4.7a3 3 0 0 0 3.9-3.9l-2 2-1.4-1.4 2-2Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'add': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 2.5v11M2.5 8h11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
  'check': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'history': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.3"/><path d="M8 4.8V8l2.4 1.4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'report': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 11.5 6 7l2.5 2.5L14 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.5 4H14v3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'calendar': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="3" width="12" height="11" rx="1.3" stroke="currentColor" stroke-width="1.3"/><path d="M2 6.3h12M5.3 1.7v2.6M10.7 1.7v2.6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>',
  'profile': '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="2.2" stroke="currentColor" stroke-width="1.3"/><path d="M8 1.8v1.6M8 12.6v1.6M14.2 8h-1.6M3.4 8H1.8M12.2 3.8l-1.1 1.1M4.9 11.1l-1.1 1.1M12.2 12.2l-1.1-1.1M4.9 4.9 3.8 3.8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>'
};

function renderNav(){
  const area = document.getElementById('navArea');
  area.innerHTML = '';
  NAV[currentRole].forEach(function(group){
    const gl = document.createElement('div');
    gl.className='nav-group-label';
    gl.textContent = group.group;
    area.appendChild(gl);
    group.items.forEach(function(item){
      if(item.hidden) return;
      const b = document.createElement('button');
      b.className='nav-item';
      b.id = 'nav-'+item.id;
      b.innerHTML = '<span class="ic">'+(ICONS[item.ic] || item.ic)+'</span>'+item.label;
      b.onclick = function(){ go(item.id); };
      area.appendChild(b);
    });
  });
}

function setRole(role){
  currentRole = role;
  lastScreenId = null;
  document.querySelectorAll('.role-pill').forEach(function(p){ p.classList.toggle('active', p.dataset.role===role); });
  renderNav();
  const u = USERS[role];
  document.getElementById('userName').textContent = u.name;
  document.getElementById('userRoleLabel').textContent = u.sub;
  document.getElementById('userAvatar').textContent = u.avatar;
  const firstScreen = NAV[role][0].items[0].id;
  go(firstScreen);
}

function logoutApp(){
  const u = USERS[currentRole];
  document.getElementById('logoutAvatar').textContent = u.avatar;
  document.getElementById('logoutName').textContent = 'Terima Kasih, ' + u.name.split(' ')[0];
  const splash = document.getElementById('logoutSplash');
  splash.style.display = 'flex';
  splash.classList.remove('logout-leaving');
  setTimeout(function(){
    splash.classList.add('logout-leaving');
    setTimeout(function(){
      splash.style.display = 'none';
      splash.classList.remove('logout-leaving');
      apiFetch('/logout', {method:'POST'}).catch(function(){}).then(function(){
        window.location.href = '/login';
      });
    }, 600);
  }, 1600);
}

function go(screenId){
  document.querySelectorAll('.screen').forEach(function(s){ s.classList.remove('active','nav-down','nav-up'); });
  const el = document.getElementById('scr-'+screenId);
  if(el){
    const order = FLAT_ORDER[currentRole] || [];
    const oldIdx = lastScreenId ? order.indexOf(lastScreenId) : -1;
    const newIdx = order.indexOf(screenId);
    const dir = (oldIdx !== -1 && newIdx !== -1 && newIdx < oldIdx) ? 'nav-up' : 'nav-down';
    el.classList.add('active', dir);
  }
  lastScreenId = screenId;
  document.querySelectorAll('.nav-item').forEach(function(n){ n.classList.remove('active'); });
  const navBtn = document.getElementById('nav-'+screenId);
  if(navBtn) navBtn.classList.add('active');
  const t = TITLES[screenId];
  if(t){
    document.getElementById('crumb').textContent = t[0];
    document.getElementById('pageTitle').textContent = t[1];
  }
  try{ renderScreenData(screenId); }catch(err){ console.error('renderScreenData error for', screenId, err); }
  if(el) animateScreenNumbers(el);
  if(window.matchMedia('(max-width:900px)').matches){
    document.getElementById('appShell').classList.remove('sidebar-toggled');
  }
}

/* =====================================================================
   ROUTER DATA — dipanggil setiap kali berpindah layar agar data selalu
   segar (mis. setelah menambah stasiun/mesin baru lewat modal).
   ===================================================================== */
function renderScreenData(screenId){
  switch(screenId){
    case 'sup2-dashboard': renderDashboardSupervisor(); break;
    case 'sup2-master': renderMasterStasiunMesin(); break;
    case 'sup2-monitoring': renderMonitoringFilters(); renderMonitoringTable(); renderMonitoringChart(); break;
    case 'sup2-detail-mesin': renderDetailFilters(); renderDetailMesin(currentDetailMachineId || (MACHINES[0] && MACHINES[0].id)); break;
    case 'sup2-maintenance': renderJadwalFilters(); renderJadwalTable(); break;
    case 'sup2-jadwal-tambah': renderJadwalTambahForm(); break;
    case 'sup2-validasi': renderValidasiQueue(); renderValidasiHistory(); break;
    case 'sup2-validasi-detail': renderValidasiDetail(currentValidasiPmId); break;
    case 'sup2-riwayat': renderRiwayatFilters(); renderRiwayatTable(); break;
    case 'sup2-laporan': renderLaporanFilters(); renderLaporan(); break;
    case 'tek2-dashboard': renderTeknisiDashboard(); break;
    case 'tek2-detail-jadwal': renderTeknisiDetailJadwal(currentTeknisiPmId); break;
    case 'tek2-kalender': renderTeknisiCalendar(); break;
    case 'tek2-riwayat': renderTeknisiRiwayat(); break;
    case 'man-dashboard': renderDashboardManajer(); break;
    case 'man-performa': renderManPerformaTabs(); switchManPerforma(currentManPerformaId, null); break;
    case 'man-maintenance': renderKomponenRanking(); break;
    default: break;
  }
}

/* =====================================================================
   SELECT OPTIONS BUILDER (Stasiun -> Mesin cascading)
   ===================================================================== */
function stationOptionsHTML(includeAll, selectedId){
  let html = includeAll ? '<option value="all">Semua Stasiun</option>' : '';
  STATIONS.forEach(function(st){
    html += '<option value="'+st.id+'"'+(st.id===selectedId?' selected':'')+'>'+escapeHtml(st.name)+'</option>';
  });
  return html;
}
function machineOptionsHTML(stationId, includeAll, selectedId){
  let list = (!stationId || stationId==='all') ? MACHINES : getMachinesByStation(stationId);
  let html = includeAll ? '<option value="all">Semua Mesin</option>' : '';
  list.forEach(function(m){
    html += '<option value="'+m.id+'"'+(m.id===selectedId?' selected':'')+'>'+escapeHtml(m.name)+'</option>';
  });
  return html;
}

/* =====================================================================
   MASTER STASIUN & MESIN
   ===================================================================== */
function renderMasterStasiunMesin(){
  const totalStEl = document.getElementById('masterTotalStasiun');
  const totalMsEl = document.getElementById('masterTotalMesin');
  if(totalStEl) totalStEl.textContent = STATIONS.length;
  if(totalMsEl) totalMsEl.textContent = MACHINES.length;

  const wrap = document.getElementById('masterStasiunList');
  if(!wrap) return;
  if(!STATIONS.length){
    wrap.innerHTML = '<div class="empty-state">Belum ada stasiun. Klik "+ Tambah Stasiun" untuk memulai.</div>';
    return;
  }
  wrap.innerHTML = STATIONS.map(function(st){
    const machines = getMachinesByStation(st.id);
    const statusBadge = st.status==='aktif' ? '<span class="badge b-green">Aktif</span>' : '<span class="badge b-gray">Nonaktif</span>';
    const rows = machines.length ? machines.map(function(m){
      const meta = statusMeta(m.status);
      return '<tr>'
        +'<td class="mono">'+escapeHtml(m.code)+'</td>'
        +'<td><strong>'+escapeHtml(m.name)+'</strong></td>'
        +'<td>'+escapeHtml(m.type||'-')+'</td>'
        +'<td class="mono">'+escapeHtml(m.capacity||'-')+'</td>'
        +'<td class="mono">'+escapeHtml(m.year||'-')+'</td>'
        +'<td><span class="'+meta.badgeClass+'">'+meta.label+'</span></td>'
        +'<td style="white-space:nowrap;">'
          +'<button class="btn btn-outline btn-sm" onclick="openModal(\'edit\',\'machine\',\''+m.id+'\')">Edit</button> '
          +'<button class="btn btn-outline btn-sm" onclick="openDetailMesin(\''+m.id+'\')">Detail</button>'
        +'</td>'
      +'</tr>';
    }).join('') : '<tr><td colspan="7" class="empty-state">Belum ada mesin pada stasiun ini.</td></tr>';

    return '<div class="station-block">'
      +'<div class="station-block-head">'
        +'<div>'
          +'<div class="station-block-title">'+escapeHtml(st.name)+' <span class="mono" style="font-weight:400;color:var(--ink-soft);font-size:12px;">'+escapeHtml(st.code)+'</span></div>'
          +'<div class="station-block-sub">'+escapeHtml(st.location||'-')+' · '+machines.length+' mesin</div>'
        +'</div>'
        +'<div class="station-block-actions">'
          + statusBadge
          +'<button class="btn btn-outline btn-sm" onclick="openModal(\'edit\',\'station\',\''+st.id+'\')">Edit Stasiun</button>'
          +'<button class="btn btn-blue btn-sm" onclick="tambahMesinDiStasiun(\''+st.id+'\')">+ Tambah Mesin</button>'
        +'</div>'
      +'</div>'
      + (st.description ? '<div class="station-block-desc">'+escapeHtml(st.description)+'</div>' : '')
      +'<div class="station-block-body"><div class="table-scroll"><table><thead><tr><th>Kode</th><th>Nama Mesin</th><th>Jenis</th><th>Kapasitas</th><th>Tahun</th><th>Status</th><th></th></tr></thead><tbody>'+rows+'</tbody></table></div></div>'
    +'</div>';
  }).join('');
}
function tambahMesinDiStasiun(stationId){
  window._modalDefaultStationId = stationId;
  openModal('add','machine', null);
}

/* =====================================================================
   MODAL — Tambah / Edit Stasiun & Mesin
   ===================================================================== */
function openModal(mode, kind, id){
  document.getElementById('modalOverlay').classList.add('active');
  const isStation = kind==='station';
  document.getElementById('formStasiun').style.display = isStation ? 'block':'none';
  document.getElementById('formMesin').style.display = isStation ? 'none':'block';
  if(isStation){
    document.getElementById('modalTitle').textContent = mode==='edit' ? 'Edit Stasiun' : 'Tambah Stasiun';
    const st = (mode==='edit') ? getStation(id) : null;
    document.getElementById('fStasiunId').value = st ? st.id : '';
    document.getElementById('fStasiunKode').value = st ? st.code : '';
    document.getElementById('fStasiunNama').value = st ? st.name : '';
    document.getElementById('fStasiunLokasi').value = st ? st.location : '';
    document.getElementById('fStasiunDeskripsi').value = st ? st.description : '';
    document.getElementById('fStasiunStatus').value = st ? st.status : 'aktif';
  } else {
    document.getElementById('modalTitle').textContent = mode==='edit' ? 'Edit Mesin' : 'Tambah Mesin';
    const m = (mode==='edit') ? getMachine(id) : null;
    const defaultStation = m ? m.stationId : (window._modalDefaultStationId || (STATIONS[0] && STATIONS[0].id));
    document.getElementById('fMesinStasiun').innerHTML = stationOptionsHTML(false, defaultStation);
    document.getElementById('fMesinId').value = m ? m.id : '';
    document.getElementById('fMesinKode').value = m ? m.code : '';
    document.getElementById('fMesinNama').value = m ? m.name : '';
    document.getElementById('fMesinJenis').value = m ? m.type : '';
    document.getElementById('fMesinStatus').value = m ? m.status : 'normal';
    document.getElementById('fMesinKapasitas').value = m ? m.capacity : '';
    document.getElementById('fMesinTahun').value = m ? m.year : '';
    document.getElementById('fMesinKeterangan').value = m ? m.notes : '';
  }
  window._modalDefaultStationId = null;
}
function closeModal(){
  document.getElementById('modalOverlay').classList.remove('active');
}
function simpanStasiun(e){
  if(e && e.preventDefault) e.preventDefault();
  const idField = document.getElementById('fStasiunId').value;
  const id = idField || ('st-'+Date.now());
  const data = {
    id: id,
    code: document.getElementById('fStasiunKode').value.trim() || id.toUpperCase(),
    name: document.getElementById('fStasiunNama').value.trim() || 'Stasiun Baru',
    location: document.getElementById('fStasiunLokasi').value.trim(),
    description: document.getElementById('fStasiunDeskripsi').value.trim(),
    status: document.getElementById('fStasiunStatus').value
  };
  const idx = STATIONS.findIndex(function(s){ return s.id===id; });
  if(idx>=0) STATIONS[idx]=data; else STATIONS.push(data);
  renderMasterStasiunMesin();
  apiFetch('/api/stations', {method:'POST', body:JSON.stringify(data)})
    .then(refreshBootstrap)
    .then(renderMasterStasiunMesin)
    .catch(function(err){ alert('Gagal menyimpan stasiun: '+err); })
    .then(closeModal);
  return false;
}
function simpanMesin(e){
  if(e && e.preventDefault) e.preventDefault();
  const idField = document.getElementById('fMesinId').value;
  const id = idField || ('m-'+Date.now());
  const data = {
    id: id,
    stationId: document.getElementById('fMesinStasiun').value,
    code: document.getElementById('fMesinKode').value.trim() || id.toUpperCase(),
    name: document.getElementById('fMesinNama').value.trim() || 'Mesin Baru',
    type: document.getElementById('fMesinJenis').value.trim(),
    status: document.getElementById('fMesinStatus').value,
    capacity: document.getElementById('fMesinKapasitas').value.trim(),
    year: document.getElementById('fMesinTahun').value.trim(),
    notes: document.getElementById('fMesinKeterangan').value.trim()
  };
  const idx = MACHINES.findIndex(function(m){ return m.id===id; });
  if(idx>=0) MACHINES[idx]=data; else {
    MACHINES.push(data);
    if(!MACHINE_PERFORMANCE[id]) MACHINE_PERFORMANCE[id] = {oee:0, availability:0, reliability:0, mttr:0, mtbf:0, downtimeBulanIni:0, perbaikanBulanIni:0, trendOee:[0,0,0,0,0,0]};
  }
  renderMasterStasiunMesin();
  apiFetch('/api/machines', {method:'POST', body:JSON.stringify(data)})
    .then(refreshBootstrap)
    .then(renderMasterStasiunMesin)
    .catch(function(err){ alert('Gagal menyimpan mesin: '+err); })
    .then(closeModal);
  return false;
}

/* =====================================================================
   PERFORMA MESIN (Monitoring)
   ===================================================================== */
let monitoringFilter = {stationId:'all', machineId:'all'};
function renderMonitoringFilters(){
  const stSel = document.getElementById('monStasiunSel');
  const mSel = document.getElementById('monMesinSel');
  if(!stSel || !mSel) return;
  stSel.innerHTML = stationOptionsHTML(true, monitoringFilter.stationId);
  mSel.innerHTML = machineOptionsHTML(monitoringFilter.stationId, true, monitoringFilter.machineId);
}
function onMonitoringStationChange(sel){
  monitoringFilter.stationId = sel.value;
  monitoringFilter.machineId = 'all';
  renderMonitoringFilters();
  renderMonitoringTable();
  renderMonitoringChart();
}
function onMonitoringMachineChange(sel){
  monitoringFilter.machineId = sel.value;
  renderMonitoringTable();
  renderMonitoringChart();
}
function filteredMonitoringMachines(){
  let list = MACHINES;
  if(monitoringFilter.stationId && monitoringFilter.stationId!=='all'){
    list = list.filter(function(m){ return m.stationId===monitoringFilter.stationId; });
  }
  if(monitoringFilter.machineId && monitoringFilter.machineId!=='all'){
    list = list.filter(function(m){ return m.id===monitoringFilter.machineId; });
  }
  return list;
}
function renderMonitoringTable(){
  const tbody = document.getElementById('monitoringTbody');
  if(!tbody) return;
  const list = filteredMonitoringMachines();
  tbody.innerHTML = list.length ? list.map(function(m){
    const perf = getPerf(m.id);
    const meta = statusMeta(m.status);
    return '<tr>'
      +'<td><strong>'+escapeHtml(m.name)+'</strong><div style="font-size:11px;color:var(--ink-soft);">'+escapeHtml(stationLabel(getStationOfMachine(m)))+'</div></td>'
      +'<td><span class="'+meta.badgeClass+'">'+meta.label+'</span></td>'
      +'<td><div class="progress-track" style="width:90px;display:inline-block;vertical-align:middle;"><div class="progress-fill '+(meta.tier==='normal'?'green':(meta.tier==='warning'?'red':'amber'))+'" style="width:'+perf.availability+'%;"></div></div> <span class="mono" style="font-size:11.5px;">'+perf.availability+'%</span></td>'
      +'<td class="mono">'+perf.oee+'%</td>'
      +'<td class="mono">'+perf.downtimeBulanIni+' jam</td>'
      +'<td class="mono">'+perf.perbaikanBulanIni+'</td>'
      +'<td><button class="btn btn-outline btn-sm" onclick="openDetailMesin(\''+m.id+'\')">Detail</button></td>'
    +'</tr>';
  }).join('') : '<tr><td colspan="7" class="empty-state">Tidak ada mesin yang sesuai dengan filter.</td></tr>';
}
function renderMonitoringChart(){
  const mount = document.getElementById('monitoringChartMount');
  if(!mount) return;
  const list = filteredMonitoringMachines();
  const items = list.map(function(m){
    const perf = getPerf(m.id);
    return {label:m.name, value:perf.oee, color:statusMeta(m.status).color};
  });
  mount.innerHTML = buildBarChartSVG(items, {unit:'%', height:200, shortLabel:true});
}

/* =====================================================================
   DETAIL MESIN (Stasiun -> Mesin)
   ===================================================================== */
function renderDetailFilters(){
  const stSel = document.getElementById('detailStasiunSel');
  const mSel = document.getElementById('detailMesinSel');
  if(!stSel || !mSel) return;
  const activeMachine = getMachine(currentDetailMachineId) || MACHINES[0];
  const stationId = activeMachine ? activeMachine.stationId : (STATIONS[0] && STATIONS[0].id);
  stSel.innerHTML = stationOptionsHTML(false, stationId);
  mSel.innerHTML = machineOptionsHTML(stationId, false, activeMachine ? activeMachine.id : null);
}
function onDetailStationChange(sel){
  const machines = getMachinesByStation(sel.value);
  const firstId = machines.length ? machines[0].id : null;
  currentDetailMachineId = firstId;
  document.getElementById('detailMesinSel').innerHTML = machineOptionsHTML(sel.value, false, firstId);
  renderDetailMesin(firstId);
}
function onDetailMachineChange(sel){
  currentDetailMachineId = sel.value;
  renderDetailMesin(sel.value);
}
function openDetailMesin(machineId){
  currentDetailMachineId = machineId;
  go('sup2-detail-mesin');
}
function renderDetailMesin(machineId){
  const m = getMachine(machineId);
  if(!m){
    const wrap = document.getElementById('scr-sup2-detail-mesin');
    if(wrap) wrap.querySelector('.panel-body') && (wrap.querySelector('.panel-body').innerHTML = '<div class="empty-state">Belum ada mesin terdaftar. Tambahkan mesin melalui Stasiun & Mesin.</div>');
    return;
  }
  currentDetailMachineId = machineId;
  const st = getStationOfMachine(m);
  const perf = getPerf(m.id);
  const meta = statusMeta(m.status);
  const fillClass = meta.tier==='normal' ? 'green' : (meta.tier==='warning' ? 'amber' : 'red');

  document.getElementById('detailMesinNama').textContent = m.name;
  const badge = document.getElementById('detailMesinBadge');
  badge.textContent = meta.label; badge.className = meta.badgeClass;

  document.getElementById('detailMesinKode').textContent = m.code;
  document.getElementById('detailMesinStasiun').textContent = stationLabel(st);
  document.getElementById('detailMesinJenis').textContent = m.type || '-';

  const nextPm = PM_SCHEDULES.filter(function(p){ return p.machineId===m.id && p.status==='terjadwal'; })
    .sort(function(a,b){ return a.tanggal.localeCompare(b.tanggal); })[0];
  document.getElementById('detailNextPM').textContent = nextPm ? (formatTanggalID(nextPm.tanggal)+' — '+nextPm.jenis) : 'Belum ada jadwal';

  setPerfBar('pfOee','pvOee', perf.oee, fillClass);
  setPerfBar('pfAvail','pvAvail', perf.availability, fillClass);
  setPerfBar('pfRel','pvRel', perf.reliability, 'blue-fill');

  document.getElementById('detailMTTR').textContent = perf.mttr + ' jam';
  document.getElementById('detailMTBF').textContent = perf.mtbf + ' jam';
  document.getElementById('detailDowntime').textContent = perf.downtimeBulanIni + ' jam';

  document.getElementById('detailChartTitle').textContent = 'Tren OEE Mingguan — ' + m.name;
  document.getElementById('detailHistoryTitle').textContent = 'Riwayat Maintenance — ' + m.name;
  const chartMount = document.getElementById('detailChartMount');
  if(chartMount) chartMount.innerHTML = buildLineChartSVG(perf.trendOee, {color:meta.color, min:0, max:100, unit:'%', labels:WEEK_LABELS, height:210});

  const hist = MAINTENANCE_HISTORY.filter(function(h){ return h.machineId===m.id; })
    .slice().sort(function(a,b){ return b.tanggal.localeCompare(a.tanggal); });
  const histBody = document.getElementById('detailHistoryBody');
  histBody.innerHTML = hist.length ? hist.map(function(h){
    return '<tr><td class="mono">'+escapeHtml(h.noLaporan)+'</td><td><span class="badge b-gray">'+escapeHtml(h.kategori)+'</span></td><td>'+escapeHtml(h.pekerjaan)+'</td><td class="mono">'+h.downtimeMenit+' mnt</td><td>'+escapeHtml(h.pelaksana)+'</td><td class="mono">'+formatTanggalID(h.tanggal)+'</td></tr>';
  }).join('') : '<tr><td colspan="6" class="empty-state">Belum ada riwayat maintenance untuk mesin ini.</td></tr>';
}
function setPerfBar(fillId, valId, value, colorClass){
  const fill = document.getElementById(fillId);
  const val = document.getElementById(valId);
  if(fill){ fill.style.width = Math.max(0, Math.min(100, value)) + '%'; fill.className = 'progress-fill' + (colorClass==='blue-fill' ? '' : ' '+colorClass); }
  if(val) val.textContent = value + '%';
}

/* =====================================================================
   ANIMASI ANGKA & GRAFIK
   ===================================================================== */
function animateScreenNumbers(root){
  /* Nilai KPI dan grafik ditampilkan langsung tanpa animasi hitung/gambar-ulang,
     supaya angka terasa stabil dan tidak "meloncat" — sesuai kebutuhan
     tampilan sistem informasi pemeliharaan yang ringkas dan profesional. */
}

/* ---------- Ubah Password (Profil) ---------- */
function checkPasswordMatch(prefix){
  const np = document.getElementById(prefix+'PassNew').value;
  const cp = document.getElementById(prefix+'PassConfirm').value;
  const msg = document.getElementById(prefix+'PassMsg');
  const btn = document.getElementById(prefix+'PassBtn');
  function show(bg,border,color,text){
    msg.style.display = 'block';
    msg.style.background = bg; msg.style.borderColor = border; msg.style.color = color;
    msg.textContent = text;
  }
  if(!np && !cp){ msg.style.display = 'none'; btn.disabled = true; return; }
  if(np.length > 0 && np.length < 8){
    show('var(--red-soft)','#E7B3A8','#9A2A16','Password baru minimal 8 karakter.');
    btn.disabled = true; return;
  }
  if(cp.length > 0 && np !== cp){
    show('var(--red-soft)','#E7B3A8','#9A2A16','Konfirmasi password baru belum cocok.');
    btn.disabled = true; return;
  }
  if(np.length >= 8 && np === cp){
    show('var(--green-soft)','#BFE0C9','#155C34','Password cocok dan siap disimpan.');
    btn.disabled = false; return;
  }
  msg.style.display = 'none';
  btn.disabled = true;
}
function changePassword(prefix){
  const btn = document.getElementById(prefix+'PassBtn');
  if(btn.disabled) return;
  const oldPass = document.getElementById(prefix+'PassOld').value;
  const newPass = document.getElementById(prefix+'PassNew').value;
  const msg = document.getElementById(prefix+'PassMsg');
  function showMsg(ok, text){
    msg.style.display = 'block';
    if(ok){ msg.style.background='var(--green-soft)'; msg.style.borderColor='#BFE0C9'; msg.style.color='#155C34'; }
    else { msg.style.background='var(--red-soft)'; msg.style.borderColor='#E7B3A8'; msg.style.color='#9A2A16'; }
    msg.textContent = text;
  }
  apiFetch('/api/profile/password', {method:'POST', body:JSON.stringify({current_password:oldPass, password:newPass})})
    .then(function(){
      ['PassOld','PassNew','PassConfirm'].forEach(function(k){
        const f = document.getElementById(prefix+k);
        if(f) f.value = '';
      });
      showMsg(true, 'Password berhasil diperbarui.');
      btn.disabled = true;
    })
    .catch(function(err){ showMsg(false, typeof err==='string' ? err : 'Password saat ini salah.'); });
}

/* =====================================================================
   JADWAL PREVENTIVE MAINTENANCE
   ===================================================================== */
let jadwalFilter = {stationId:'all', machineId:'all', status:'all', periode:'all'};
function renderJadwalFilters(){
  const stSel = document.getElementById('jdStasiunSel');
  const mSel = document.getElementById('jdMesinSel');
  if(!stSel || !mSel) return;
  stSel.innerHTML = stationOptionsHTML(true, jadwalFilter.stationId);
  mSel.innerHTML = machineOptionsHTML(jadwalFilter.stationId, true, jadwalFilter.machineId);
}
function onJadwalStationChange(sel){
  jadwalFilter.stationId = sel.value;
  jadwalFilter.machineId = 'all';
  renderJadwalFilters();
  renderJadwalTable();
}
function onJadwalFilterChange(){
  jadwalFilter.machineId = document.getElementById('jdMesinSel').value;
  jadwalFilter.status = document.getElementById('jdStatusSel').value;
  jadwalFilter.periode = document.getElementById('jdPeriodeSel').value;
  renderJadwalTable();
}
function renderJadwalTable(){
  const tbody = document.getElementById('jadwalTbody');
  if(!tbody) return;
  let list = PM_SCHEDULES.slice();
  if(jadwalFilter.stationId && jadwalFilter.stationId!=='all'){
    list = list.filter(function(p){ const m=getMachine(p.machineId); return m && m.stationId===jadwalFilter.stationId; });
  }
  if(jadwalFilter.machineId && jadwalFilter.machineId!=='all'){
    list = list.filter(function(p){ return p.machineId===jadwalFilter.machineId; });
  }
  if(jadwalFilter.status && jadwalFilter.status!=='all'){
    list = list.filter(function(p){ return p.status===jadwalFilter.status; });
  }
  if(jadwalFilter.periode && jadwalFilter.periode!=='all'){
    list = list.filter(function(p){ return monthPrefix(p.tanggal)===jadwalFilter.periode; });
  }
  list.sort(function(a,b){ return a.tanggal.localeCompare(b.tanggal); });
  tbody.innerHTML = list.length ? list.map(function(p){
    const m = getMachine(p.machineId);
    const st = getStationOfMachine(m);
    const meta = PM_STATUS_META[p.status] || PM_STATUS_META.terjadwal;
    const prio = PRIO_META[p.prioritas] || PRIO_META.sedang;
    let actionBtn;
    if(p.status==='menunggu-validasi'){
      actionBtn = '<button class="btn btn-amber btn-sm" onclick="openValidasiDetail(\''+p.id+'\')">Validasi</button>';
    } else {
      actionBtn = '<button class="btn btn-outline btn-sm" onclick="openDetailMesin(\''+p.machineId+'\')">Lihat</button>';
    }
    return '<tr>'
      +'<td>'+escapeHtml(stationLabel(st))+'</td>'
      +'<td><strong>'+escapeHtml(machineLabel(m))+'</strong></td>'
      +'<td>'+escapeHtml(p.jenis)+'</td>'
      +'<td>'+escapeHtml(p.teknisi)+'</td>'
      +'<td class="mono">'+formatTanggalID(p.tanggal)+'</td>'
      +'<td>'+escapeHtml(p.interval)+'</td>'
      +'<td><span class="'+prio.cls+'">'+prio.label+'</span></td>'
      +'<td><span class="'+meta.badgeClass+'">'+meta.label+'</span></td>'
      +'<td>'+actionBtn+'</td>'
    +'</tr>';
  }).join('') : '<tr><td colspan="9" class="empty-state">Tidak ada jadwal yang sesuai dengan filter.</td></tr>';
}

/* ---------- Tambah Jadwal ---------- */
function renderJadwalTambahForm(){
  const stSel = document.getElementById('jtStasiun');
  if(!stSel) return;
  const firstStation = STATIONS[0] && STATIONS[0].id;
  stSel.innerHTML = stationOptionsHTML(false, firstStation);
  document.getElementById('jtMesin').innerHTML = machineOptionsHTML(firstStation, false, null);
  document.getElementById('jtJenis').value = '';
  document.getElementById('jtTeknisi').value = 'Budi Santoso';
  document.getElementById('jtTanggal').value = '2026-08-20';
  document.getElementById('jtInterval').value = 'Mingguan';
  document.getElementById('jtDurasi').value = '';
  document.getElementById('jtCatatan').value = '';
  setJtPrioritas('sedang', document.querySelector('#jtPrioRow .prio[data-p="sedang"]'));
}
function onJadwalTambahStationChange(sel){
  document.getElementById('jtMesin').innerHTML = machineOptionsHTML(sel.value, false, null);
}
function setJtPrioritas(val, el){
  jtPrioritasVal = val;
  document.querySelectorAll('#jtPrioRow .prio').forEach(function(v){ v.classList.remove('on'); });
  if(el) el.classList.add('on');
}
function simpanJadwalBaru(){
  const machineId = document.getElementById('jtMesin').value;
  if(!machineId){ alert('Pilih mesin terlebih dahulu.'); return; }
  const jenis = document.getElementById('jtJenis').value.trim() || 'Pemeriksaan rutin';
  const newSchedule = {
    id: 'pm-'+Date.now(),
    machineId: machineId,
    jenis: jenis,
    teknisi: document.getElementById('jtTeknisi').value,
    tanggal: document.getElementById('jtTanggal').value || DEMO_TODAY,
    interval: document.getElementById('jtInterval').value,
    estimasi: document.getElementById('jtDurasi').value.trim() || '-',
    prioritas: jtPrioritasVal,
    status: 'terjadwal',
    catatan: document.getElementById('jtCatatan').value.trim()
  };
  PM_SCHEDULES.push(newSchedule);
  go('sup2-maintenance');
  apiFetch('/api/pm-schedules', {method:'POST', body:JSON.stringify(newSchedule)})
    .then(refreshBootstrap)
    .then(function(){ if(document.getElementById('scr-sup2-maintenance').classList.contains('active')) renderJadwalTable(); })
    .catch(function(err){ alert('Gagal menyimpan jadwal: '+err); });
}

/* =====================================================================
   VALIDASI PEMERIKSAAN
   ===================================================================== */
function renderValidasiQueue(){
  const queue = PM_SCHEDULES.filter(function(p){ return p.status==='menunggu-validasi'; });
  const el1 = document.getElementById('pcSupValidasi2');
  const el2 = document.getElementById('pcSupValidasi');
  if(el1) el1.textContent = queue.length;
  if(el2) el2.textContent = queue.length;
  const tbody = document.getElementById('sup2ValidasiTbody');
  if(!tbody) return;
  tbody.innerHTML = queue.length ? queue.map(function(p){
    const m = getMachine(p.machineId);
    const st = getStationOfMachine(m);
    return '<tr>'
      +'<td>'+escapeHtml(stationLabel(st))+'</td>'
      +'<td><strong>'+escapeHtml(machineLabel(m))+'</strong></td>'
      +'<td>'+escapeHtml(p.jenis)+'</td>'
      +'<td>'+escapeHtml(p.teknisi)+'</td>'
      +'<td class="mono">'+(p.report ? formatTanggalID(p.report.tanggalPemeriksaan) : formatTanggalID(p.tanggal))+'</td>'
      +'<td><span class="badge b-purple">Menunggu</span></td>'
      +'<td><button class="btn btn-amber btn-sm" onclick="openValidasiDetail(\''+p.id+'\')">Validasi</button></td>'
    +'</tr>';
  }).join('') : '<tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:22px;">Tidak ada pemeriksaan yang menunggu validasi saat ini.</td></tr>';
}
function renderValidasiHistory(){
  const tbody = document.getElementById('sup2ValidasiHistoryTbody');
  if(!tbody) return;
  const list = VALIDATION_HISTORY.slice().sort(function(a,b){ return b.tanggal.localeCompare(a.tanggal); });
  tbody.innerHTML = list.length ? list.map(function(v){
    const m = getMachine(v.machineId);
    const hasilBadge = v.hasil==='disetujui' ? '<span class="badge b-green">Disetujui</span>' : '<span class="badge b-red">Ditolak</span>';
    return '<tr>'
      +'<td>'+escapeHtml(stationLabel(getStationOfMachine(m)))+'</td>'
      +'<td><strong>'+escapeHtml(machineLabel(m))+'</strong></td>'
      +'<td>'+escapeHtml(v.jenis)+'</td>'
      +'<td>'+escapeHtml(v.teknisi)+'</td>'
      +'<td>'+escapeHtml(v.divalidasiOleh)+'</td>'
      +'<td class="mono">'+formatTanggalID(v.tanggal)+'</td>'
      +'<td>'+hasilBadge+'</td>'
    +'</tr>';
  }).join('') : '<tr><td colspan="7" class="empty-state">Belum ada riwayat validasi.</td></tr>';
}
function openValidasiDetail(pmId){
  currentValidasiPmId = pmId;
  go('sup2-validasi-detail');
}
function katParamLabel(kategori){
  return {mekanik:'Mekanik', elektrik:'Elektrik', instrumentasi:'Instrumentasi'}[kategori] || kategori;
}
function renderKatSectionReadonly(kategori, parameter){
  parameter = parameter || {};
  let fields = [];
  if(kategori==='mekanik'){
    fields = [['Getaran Terukur', (parameter.getaran||'-')+' mm/s'], ['Suhu Bearing / Gearbox', (parameter.suhu||'-')+'°C'], ['Kondisi Pelumasan', parameter.pelumasan||'-'], ['Torsi Pengencangan Baut', (parameter.torsi||'-')+' Nm']];
  } else if(kategori==='elektrik'){
    fields = [['Tegangan Terukur', (parameter.tegangan||'-')+' V'], ['Arus Terukur', (parameter.arus||'-')+' A'], ['Tahanan Isolasi', (parameter.isolasi||'-')+' MΩ'], ['Kondisi Kontaktor / Panel', parameter.kondisi||'-']];
  } else {
    fields = [['Nilai Terbaca Sensor', parameter.nilaiBaca||'-'], ['Nilai Standar Kalibrasi', parameter.nilaiStandar||'-'], ['Deviasi (%)', parameter.deviasi||'-'], ['Status Kalibrasi', parameter.statusKalibrasi||'-']];
  }
  let html = '<div class="kat-section" style="margin-top:14px;"><div class="kat-section-head">'+katParamLabel(kategori)+' — Parameter Teknis <span class="tag">Dilaporkan Teknisi</span></div><div class="detail-grid">';
  html += '<div>' + fields.slice(0,2).map(function(f){ return '<div class="kv"><span class="k">'+f[0]+'</span><span class="v mono">'+escapeHtml(f[1])+'</span></div>'; }).join('') + '</div>';
  html += '<div>' + fields.slice(2,4).map(function(f){ return '<div class="kv"><span class="k">'+f[0]+'</span><span class="v mono">'+escapeHtml(f[1])+'</span></div>'; }).join('') + '</div>';
  html += '</div></div>';
  return html;
}
function renderValidasiDetail(pmId){
  const sc = PM_SCHEDULES.find(function(p){ return p.id===pmId; }) || PM_SCHEDULES.find(function(p){ return p.status==='menunggu-validasi'; });
  const panel = document.getElementById('scr-sup2-validasi-detail');
  if(!sc || !sc.report){
    if(panel) panel.querySelector('.panel-body').innerHTML = '<div class="empty-state">Tidak ada laporan pemeriksaan yang perlu divalidasi saat ini.</div>';
    return;
  }
  currentValidasiPmId = sc.id;
  const m = getMachine(sc.machineId);
  const st = getStationOfMachine(m);
  const r = sc.report;
  document.getElementById('validasiDetailTitle').textContent = 'Validasi Laporan Perbaikan — ' + machineLabel(m);
  document.getElementById('validasiDetailBreadcrumb').innerHTML = escapeHtml(stationLabel(st)) + ' <span class="sep">/</span> ' + escapeHtml(machineLabel(m));
  const badge = document.getElementById('validasiDetailBadge');
  if(r.status==='menunggu'){ badge.textContent='Menunggu'; badge.className='badge b-purple'; }
  else if(r.status==='disetujui'){ badge.textContent='Disetujui'; badge.className='badge b-green'; }
  else { badge.textContent='Ditolak'; badge.className='badge b-red'; }

  document.getElementById('vdJenisPM').textContent = sc.jenis;
  document.getElementById('vdPemeriksa').textContent = r.pemeriksa;
  document.getElementById('vdJadwal').textContent = formatTanggalID(sc.tanggal);
  document.getElementById('vdDikirim').textContent = r.dikirim || formatTanggalID(r.tanggalPemeriksaan);
  document.getElementById('vdKategori').innerHTML = '<span class="badge b-amber">'+katParamLabel(r.kategori)+'</span>';
  const prio = PRIO_META[r.prioritas] || PRIO_META.sedang;
  document.getElementById('vdPrioritas').innerHTML = '<span class="'+prio.cls+'">'+prio.label+'</span>';
  let durasiMenit = 0;
  if(r.waktuMulai && r.waktuSelesai){
    const a=r.waktuMulai.split(':').map(Number), b=r.waktuSelesai.split(':').map(Number);
    durasiMenit = (b[0]*60+b[1]) - (a[0]*60+a[1]);
    if(durasiMenit<0) durasiMenit += 24*60;
  }
  document.getElementById('vdWaktu').textContent = (r.waktuMulai||'-')+' – '+(r.waktuSelesai||'-')+' ('+durasiMenit+' menit)';
  const fotoWrap = document.getElementById('vdFotoPreviewWrap');
  const fotoImg = document.getElementById('vdFotoPreview');
  if (r.fotoUrl) {
    document.getElementById('vdFoto').textContent = 'Foto terlampir';
    if (fotoImg && fotoWrap) {
      fotoImg.src = r.fotoUrl;
      fotoWrap.style.display = 'block';
    }
  } else {
    document.getElementById('vdFoto').textContent = 'Tidak ada foto';
    if (fotoWrap) fotoWrap.style.display = 'none';
    if (fotoImg) fotoImg.removeAttribute('src');
  }

  document.getElementById('vdParamMount').innerHTML = renderKatSectionReadonly(r.kategori, r.parameter);
  document.getElementById('vdDeskripsi').value = r.deskripsi || '';
  document.getElementById('vdTindakan').value = r.tindakan || '';
  document.getElementById('vdRekomendasi').value = r.rekomendasi || '';

  const spBody = document.getElementById('vdSparepartBody');
  if(r.spareparts && r.spareparts.length){
    spBody.innerHTML = r.spareparts.map(function(sp){
      return '<tr><td>'+escapeHtml(sp.nama)+'</td><td>'+escapeHtml(sp.kode)+'</td><td>'+escapeHtml(sp.qty)+'</td><td>'+escapeHtml(sp.satuan)+'</td></tr>';
    }).join('');
  } else {
    spBody.innerHTML = '<tr><td colspan="4" style="color:var(--ink-soft);padding:8px 0;">Tidak ada komponen yang diganti pada pekerjaan ini.</td></tr>';
  }

  document.getElementById('voptApprove').classList.add('on');
  document.getElementById('voptReject').classList.remove('on');
  document.getElementById('voptReject').classList.remove('reject');
  document.getElementById('vdCatatanSupervisor').value = r.catatanSupervisor || '';
  document.getElementById('vdTeknisiBerikutnya').value = r.pemeriksa;
  document.getElementById('vdIntervalBerikutnya').value = 'Mingguan';
  document.getElementById('vdTanggalBerikutnya').value = r.jadwalBerikutnya || '';
  document.getElementById('vdDurasiBerikutnya').value = sc.estimasi || '';
}
function setValidationResult(kind){
  const a = document.getElementById('voptApprove');
  const r = document.getElementById('voptReject');
  a.classList.toggle('on', kind==='approve');
  r.classList.toggle('on', kind==='reject');
  r.classList.toggle('reject', kind==='reject');
}
function finalizeValidation(){
  const sc = PM_SCHEDULES.find(function(p){ return p.id===currentValidasiPmId; });
  if(!sc || !sc.report) { go('sup2-validasi'); return; }
  const isApprove = document.getElementById('voptApprove').classList.contains('on');
  const catatan = document.getElementById('vdCatatanSupervisor').value;
  sc.report.catatanSupervisor = catatan;
  sc.report.status = isApprove ? 'disetujui' : 'ditolak';
  sc.status = isApprove ? 'selesai' : 'ditolak';
  VALIDATION_HISTORY.unshift({
    machineId: sc.machineId, jenis: sc.jenis, teknisi: sc.report.pemeriksa,
    divalidasiOleh: USERS.supervisor.name, tanggal: DEMO_TODAY,
    hasil: isApprove ? 'disetujui' : 'ditolak'
  });
  if(isApprove){
    MAINTENANCE_HISTORY.unshift({
      noLaporan:'PM-'+sc.id.toUpperCase(), machineId: sc.machineId, kategori: (sc.report.kategori||'').charAt(0).toUpperCase()+(sc.report.kategori||'').slice(1),
      pekerjaan: sc.jenis, pelaksana: sc.report.pemeriksa,
      downtimeMenit: (function(){ if(!sc.report.waktuMulai||!sc.report.waktuSelesai) return 0; const a=sc.report.waktuMulai.split(':').map(Number), b=sc.report.waktuSelesai.split(':').map(Number); let d=(b[0]*60+b[1])-(a[0]*60+a[1]); if(d<0) d+=24*60; return d; })(),
      hasil:'Baik', catatan: catatan, tanggal: DEMO_TODAY
    });
  }
  go('sup2-validasi');
  apiFetch('/api/pm-schedules/'+sc.id+'/validasi', {method:'POST', body:JSON.stringify({approve:isApprove, catatan:catatan})})
    .then(refreshBootstrap)
    .then(function(){ if(document.getElementById('scr-sup2-validasi').classList.contains('active')){ renderValidasiQueue(); renderValidasiHistory(); } })
    .catch(function(err){ alert('Gagal menyimpan validasi: '+err); });
}

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
  if(riwayatFilter.stationId && riwayatFilter.stationId!=='all'){
    list = list.filter(function(h){ const m=getMachine(h.machineId); return m && m.stationId===riwayatFilter.stationId; });
  }
  if(riwayatFilter.machineId && riwayatFilter.machineId!=='all'){
    list = list.filter(function(h){ return h.machineId===riwayatFilter.machineId; });
  }
  if(riwayatFilter.kategori && riwayatFilter.kategori!=='all'){
    list = list.filter(function(h){ return h.kategori===riwayatFilter.kategori; });
  }
  if(riwayatFilter.periode && riwayatFilter.periode!=='all'){
    list = list.filter(function(h){ return monthPrefix(h.tanggal)===riwayatFilter.periode; });
  }
  list.sort(function(a,b){ return b.tanggal.localeCompare(a.tanggal); });
  tbody.innerHTML = list.length ? list.map(function(h){
    const m = getMachine(h.machineId);
    const st = getStationOfMachine(m);
    return '<tr>'
      +'<td class="mono">'+escapeHtml(h.noLaporan)+'</td>'
      +'<td class="mono">'+formatTanggalID(h.tanggal)+'</td>'
      +'<td>'+escapeHtml(stationLabel(st))+'</td>'
      +'<td><strong>'+escapeHtml(machineLabel(m))+'</strong></td>'
      +'<td><span class="badge b-gray">'+escapeHtml(h.kategori)+'</span></td>'
      +'<td>'+escapeHtml(h.pekerjaan)+'</td>'
      +'<td>'+escapeHtml(h.pelaksana)+'</td>'
      +'<td class="mono">'+h.downtimeMenit+' mnt</td>'
      +'<td><span class="badge b-green">'+escapeHtml(h.hasil)+'</span></td>'
    +'</tr>';
  }).join('') : '<tr><td colspan="9" class="empty-state">Tidak ada riwayat yang sesuai dengan filter.</td></tr>';
}

/* =====================================================================
   LAPORAN & GRAFIK
   ===================================================================== */
let laporanFilter = {stationId:'all', machineId:'all'};
function renderLaporanFilters(){
  const stSel = document.getElementById('lapStasiunSel');
  const mSel = document.getElementById('lapMesinSel');
  if(!stSel || !mSel) return;
  stSel.innerHTML = stationOptionsHTML(true, laporanFilter.stationId);
  mSel.innerHTML = machineOptionsHTML(laporanFilter.stationId, true, laporanFilter.machineId);
}
function onLaporanStationChange(sel){
  laporanFilter.stationId = sel.value;
  laporanFilter.machineId = 'all';
  renderLaporanFilters();
  renderLaporan();
}
function onLaporanFilterChange(){
  laporanFilter.machineId = document.getElementById('lapMesinSel').value;
  renderLaporan();
}
function filteredLaporanMachines(){
  let list = MACHINES;
  if(laporanFilter.stationId && laporanFilter.stationId!=='all'){
    list = list.filter(function(m){ return m.stationId===laporanFilter.stationId; });
  }
  if(laporanFilter.machineId && laporanFilter.machineId!=='all'){
    list = list.filter(function(m){ return m.id===laporanFilter.machineId; });
  }
  return list;
}
function renderLaporan(){
  const machines = filteredLaporanMachines();
  const machineIds = machines.map(function(m){ return m.id; });
  const hist = MAINTENANCE_HISTORY.filter(function(h){ return machineIds.indexOf(h.machineId)!==-1; });

  const totalDowntimeJam = machines.reduce(function(sum,m){ return sum + getPerf(m.id).downtimeBulanIni; }, 0);
  const avgMttr = machines.length ? (machines.reduce(function(s,m){ return s+getPerf(m.id).mttr; },0)/machines.length) : 0;
  const avgMtbf = machines.length ? (machines.reduce(function(s,m){ return s+getPerf(m.id).mtbf; },0)/machines.length) : 0;
  const avgOee = machines.length ? (machines.reduce(function(s,m){ return s+getPerf(m.id).oee; },0)/machines.length) : 0;
  const countNormal = machines.filter(function(m){ return statusMeta(m.status).tier==='normal'; }).length;
  const countWarning = machines.filter(function(m){ return statusMeta(m.status).tier==='warning'; }).length;
  const countCritical = machines.filter(function(m){ return statusMeta(m.status).tier==='critical'; }).length;

  setText('lapTotalMaintenance', hist.length);
  setText('lapTotalDowntime', totalDowntimeJam.toFixed(1));
  setText('lapAvgMttr', avgMttr.toFixed(1));
  setText('lapAvgMtbf', Math.round(avgMtbf));
  setText('lapAvgOee', Math.round(avgOee));
  setText('lapMesinNormal', countNormal);
  setText('lapMesinWarning', countWarning);
  setText('lapMesinCritical', countCritical);

  mount('lapOeeChart', buildBarChartSVG(machines.map(function(m){ return {label:m.name, value:getPerf(m.id).oee, color:statusMeta(m.status).color}; }), {unit:'%', height:220, shortLabel:true}));
  mount('lapDowntimeChart', buildBarChartSVG(machines.map(function(m){ return {label:m.name, value:getPerf(m.id).downtimeBulanIni, color:'var(--amber)'}; }), {unit:'j', height:220, shortLabel:true}));
  mount('lapTrenDowntimeChart', buildLineChartSVG(MONTHLY_DOWNTIME_TREND_CUM, {color:'var(--amber)', min:0, max:Math.max.apply(null, MONTHLY_DOWNTIME_TREND_CUM)*1.2, unit:'j', labels:LAPORAN_MONTH_LABELS, area:true, height:200}));

  const perStasiun = {};
  hist.forEach(function(h){
    const m = getMachine(h.machineId);
    const st = getStationOfMachine(m);
    const key = st ? st.name : 'Lainnya';
    if(!perStasiun[key]) perStasiun[key] = {jumlah:0, downtimeMenit:0};
    perStasiun[key].jumlah += 1;
    perStasiun[key].downtimeMenit += h.downtimeMenit || 0;
  });
  const stasiunRows = Object.keys(perStasiun).sort(function(a,b){ return perStasiun[b].jumlah - perStasiun[a].jumlah; });
  const tbody = document.getElementById('lapMaintPerStasiunBody');
  if(tbody){
    tbody.innerHTML = stasiunRows.length ? stasiunRows.map(function(name){
      const row = perStasiun[name];
      return '<tr><td>'+escapeHtml(name)+'</td><td class="mono">'+row.jumlah+'</td><td class="mono">'+(row.downtimeMenit/60).toFixed(1)+' jam</td></tr>';
    }).join('') : '<tr><td colspan="3" class="empty-state">Tidak ada data pada periode/filter ini.</td></tr>';
  }
}
function setText(id, val){ const el = document.getElementById(id); if(el) el.textContent = val; }
function mount(id, html){ const el = document.getElementById(id); if(el) el.innerHTML = html; }
function setStatValue(id, val, unit){
  const el = document.getElementById(id);
  if(!el) return;
  el.innerHTML = val + (unit ? '<span class="unit">'+unit+'</span>' : '');
}

/* =====================================================================
   DASHBOARD SUPERVISOR
   ===================================================================== */
function renderDashboardSupervisor(){
  const countNormal = MACHINES.filter(function(m){ return statusMeta(m.status).tier==='normal'; }).length;
  const countWarning = MACHINES.filter(function(m){ return statusMeta(m.status).tier!=='normal'; }).length;
  const totalDowntime = MACHINES.reduce(function(s,m){ return s+getPerf(m.id).downtimeBulanIni; }, 0);
  const maintHariIni = PM_SCHEDULES.filter(function(p){ return p.tanggal===DEMO_TODAY && p.status!=='selesai'; }).length;

  setText('dashTotalMesin', MACHINES.length);
  setText('dashMesinNormal', countNormal);
  setText('dashMesinWarning', countWarning);
  setText('dashMaintHariIni', maintHariIni);
  setStatValue('dashTotalDowntime', totalDowntime.toFixed(1), 'jam');

  const avgOee = Math.round(MACHINES.reduce(function(s,m){ return s+getPerf(m.id).oee; },0)/MACHINES.length);
  const avgAvail = Math.round(MACHINES.reduce(function(s,m){ return s+getPerf(m.id).availability; },0)/MACHINES.length);
  const avgRel = Math.round(MACHINES.reduce(function(s,m){ return s+getPerf(m.id).reliability; },0)/MACHINES.length);
  const avgMttr = (MACHINES.reduce(function(s,m){ return s+getPerf(m.id).mttr; },0)/MACHINES.length);
  const avgMtbf = Math.round(MACHINES.reduce(function(s,m){ return s+getPerf(m.id).mtbf; },0)/MACHINES.length);
  setStatValue('dashOee', avgOee, '%'); setStatValue('dashAvail', avgAvail, '%'); setStatValue('dashRel', avgRel, '%');
  setStatValue('dashMttr', avgMttr.toFixed(1), 'jam'); setStatValue('dashMtbf', avgMtbf, 'jam');

  const sortedByDowntime = MACHINES.slice().sort(function(a,b){ return getPerf(b.id).downtimeBulanIni - getPerf(a.id).downtimeBulanIni; });
  const top6 = sortedByDowntime.slice(0,6);
  mount('dashDowntimeChart', buildBarChartSVG(top6.map(function(m){ return {label:m.name, value:getPerf(m.id).downtimeBulanIni, color:statusMeta(m.status).color}; }), {unit:'j', height:190, shortLabel:true}));

  const rankWrap = document.getElementById('dashRankList');
  if(rankWrap){
    const perlu = sortedByDowntime.filter(function(m){ return statusMeta(m.status).tier!=='normal'; }).slice(0,4);
    const list = perlu.length ? perlu : sortedByDowntime.slice(0,3);
    rankWrap.innerHTML = list.map(function(m, i){
      const perf = getPerf(m.id);
      const meta = statusMeta(m.status);
      return '<div class="rank-row"><div class="rank-num'+(i<2?' top':'')+'">'+(i+1)+'</div><div class="rank-info"><div class="rn">'+escapeHtml(m.name)+'</div><div class="rs">'+escapeHtml(stationLabel(getStationOfMachine(m)))+' · '+perf.perbaikanBulanIni+' kali perbaikan bulan ini</div></div><div class="rank-val"><span class="'+meta.badgeClass+'">'+meta.label+'</span></div></div>';
    }).join('');
  }

  const jadwalTerdekat = PM_SCHEDULES.filter(function(p){ return p.status==='terjadwal'; }).sort(function(a,b){ return a.tanggal.localeCompare(b.tanggal); }).slice(0,4);
  mount('dashJadwalTerdekat', jadwalTerdekat.length ? jadwalTerdekat.map(function(p){
    const m = getMachine(p.machineId);
    return '<li><div><div class="ml-title">'+escapeHtml(machineLabel(m))+'</div><div class="ml-sub">'+escapeHtml(p.jenis)+'</div></div><div class="ml-right mono">'+formatTanggalID(p.tanggal)+'</div></li>';
  }).join('') : '<li class="empty-state">Tidak ada jadwal mendatang.</li>');

  const menungguValidasi = PM_SCHEDULES.filter(function(p){ return p.status==='menunggu-validasi'; });
  mount('dashMenungguValidasi', menungguValidasi.length ? menungguValidasi.map(function(p){
    const m = getMachine(p.machineId);
    return '<li><div><div class="ml-title">'+escapeHtml(machineLabel(m))+'</div><div class="ml-sub">'+escapeHtml(p.jenis)+' · '+escapeHtml(p.teknisi)+'</div></div><div class="ml-right"><button class="btn btn-amber btn-sm" onclick="openValidasiDetail(\''+p.id+'\')">Validasi</button></div></li>'
  }).join('') : '<li class="empty-state">Tidak ada pemeriksaan menunggu validasi.</li>');
}
/* =====================================================================
   TEKNISI
   ===================================================================== */
function renderTeknisiDashboard(){
  const me = USERS.teknisi.name;
  const mine = PM_SCHEDULES.filter(function(p){ return p.teknisi===me; });
  setText('tekStatMingguIni', mine.filter(function(p){ return p.status!=='selesai'; }).length);
  setText('tekStatJatuhTempo', mine.filter(function(p){ return p.tanggal===DEMO_TODAY && p.status!=='selesai'; }).length);
  const selesaiBulanIni = mine.filter(function(p){ return p.status==='selesai'; }).length + MAINTENANCE_HISTORY.filter(function(h){ return h.pelaksana===me; }).length;
  setText('tekStatSelesaiBulan', selesaiBulanIni);

  const tbody = document.getElementById('tekJadwalTbody');
  if(!tbody) return;
  const sorted = mine.slice().sort(function(a,b){ return a.tanggal.localeCompare(b.tanggal); });
  tbody.innerHTML = sorted.length ? sorted.map(function(p){
    const m = getMachine(p.machineId);
    let displayMeta = PM_STATUS_META[p.status] || PM_STATUS_META.terjadwal;
    if(p.status==='terjadwal' && p.tanggal===DEMO_TODAY){ displayMeta = {label:'Jatuh Tempo Hari Ini', badgeClass:'badge b-amber'}; }
    const btnLabel = p.status==='terjadwal' ? 'Buka' : 'Lihat';
    const btnClass = (p.status==='terjadwal' && p.tanggal===DEMO_TODAY) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
    return '<tr><td><strong>'+escapeHtml(machineLabel(m))+'</strong></td><td>'+escapeHtml(p.jenis)+'</td><td class="mono">'+formatTanggalID(p.tanggal)+'</td><td><span class="'+displayMeta.badgeClass+'">'+displayMeta.label+'</span></td><td><button class="'+btnClass+'" onclick="openTeknisiDetail(\''+p.id+'\')">'+btnLabel+'</button></td></tr>';
  }).join('') : '<tr><td colspan="5" class="empty-state">Tidak ada jadwal maintenance ditugaskan.</td></tr>';
}

function openTeknisiDetail(pmId){
  currentTeknisiPmId = pmId;
  go('tek2-detail-jadwal');
}

function sparepartRowHTML(data){
  data = data || {};
  const satuan = data.satuan || 'pcs';
  function opt(v){ return '<option'+(satuan===v?' selected':'')+'>'+v+'</option>'; }
  return '<tr>'
    +'<td><input type="text" placeholder="cth. Bearing 6205" value="'+escapeHtml(data.nama||'')+'"></td>'
    +'<td><input type="text" placeholder="cth. BR-6205" value="'+escapeHtml(data.kode||'')+'"></td>'
    +'<td><input type="text" value="'+escapeHtml(data.qty||'1')+'"></td>'
    +'<td><select>'+opt('pcs')+opt('set')+opt('liter')+opt('kg')+opt('meter')+'</select></td>'
    +'<td class="col-del"><button type="button" class="sp-del-btn" onclick="hapusBarisSparepart(this)" title="Hapus baris">✕</button></td>'
  +'</tr>';
}
function resetSparepartRows(){
  const body = document.getElementById('tekSparepartBody');
  if(body) body.innerHTML = sparepartRowHTML();
}
function renderSparepartRows(list){
  const body = document.getElementById('tekSparepartBody');
  if(!body) return;
  if(!list || !list.length){ body.innerHTML = sparepartRowHTML(); return; }
  body.innerHTML = list.map(sparepartRowHTML).join('');
}

function renderTeknisiDetailJadwal(pmId){
  const sc = pmId ? PM_SCHEDULES.find(function(p){ return p.id===pmId; }) : null;
  const fallback = PM_SCHEDULES.find(function(p){ return p.id==='pm-002'; }) || PM_SCHEDULES[0];
  const active = sc || fallback;
  if(!active) return;
  currentTeknisiPmId = active.id;
  const m = getMachine(active.machineId);
  const st = getStationOfMachine(m);
  document.getElementById('tekDetailMesinNama').textContent = (st?st.name+' — ':'') + machineLabel(m);
  document.getElementById('tekDetailJenisPM').textContent = active.jenis;
  document.getElementById('tekDetailInterval').textContent = active.interval;
  document.getElementById('tekDetailJadwal').textContent = formatTanggalID(active.tanggal);
  document.getElementById('tekDetailDurasi').textContent = active.estimasi || '-';

  const badge = document.getElementById('tekDetailStatusBadge');
  let meta = PM_STATUS_META[active.status] || PM_STATUS_META.terjadwal;
  if(active.status==='terjadwal' && active.tanggal===DEMO_TODAY){ meta = {label:'Jatuh Tempo Hari Ini', badgeClass:'badge b-amber'}; }
  badge.textContent = meta.label; badge.className = meta.badgeClass;

  const callout = document.getElementById('tekValidasiCallout');
  const scope = document.getElementById('scr-tek2-detail-jadwal');
  const fields = scope.querySelectorAll('input, textarea, select');
  const actionBar = document.getElementById('tekActionBar');

  if(active.status === 'terjadwal'){
    callout.style.display = 'none';
    fields.forEach(function(f){ f.disabled=false; f.style.background=''; });
    document.querySelectorAll('#tekKategoriRow .vopt, #tekPrioritasRow .prio').forEach(function(v){ v.style.pointerEvents=''; v.style.opacity=''; });
    setTekKategori('mekanik', document.querySelector('#tekKategoriRow .vopt[data-kat="mekanik"]'));
    setTekPrioritas('sedang', document.querySelector('#tekPrioritasRow .prio[data-p="sedang"]'));
    document.getElementById('tekWaktuMulai').value=''; document.getElementById('tekWaktuSelesai').value='';
    document.getElementById('tekDowntimeVal').textContent='—';
    ['tekMekGetaran','tekMekSuhu','tekMekTorsi'].forEach(function(id){ const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('tekMekPelumasan').value='Baik';
    document.getElementById('tekDeskripsi').value='';
    document.getElementById('tekTindakan').value='';
    document.getElementById('tekRekomendasi').value='';
    document.getElementById('tekJadwalBerikutnya').value='';
    resetSparepartRows();
    document.querySelectorAll('#tekSparepartBody .sp-del-btn').forEach(function(b){ b.style.display=''; });
    const addBtn = document.querySelector('.sp-add-btn'); if(addBtn) addBtn.style.display='';
    actionBar.innerHTML = '<button class="btn btn-amber" id="tekSubmitBtn" onclick="submitLaporanForValidation()">Kirim Laporan untuk Validasi Supervisor</button>'
      + '<button class="btn btn-outline" onclick="go(\'tek2-dashboard\')">Kembali</button>';
  } else if(active.report){
    const r = active.report;
    if(active.status==='selesai'){
      callout.style.display='block'; callout.style.background='var(--green-soft)'; callout.style.borderColor='#BFE0C9'; callout.style.color='#155C34';
      callout.innerHTML = 'Laporan ini sudah divalidasi dan disetujui Supervisor.';
    } else if(active.status==='ditolak'){
      callout.style.display='block'; callout.style.background='var(--red-soft)'; callout.style.borderColor='#E7B3A8'; callout.style.color='#9A2A16';
      callout.innerHTML = 'Laporan ini dikembalikan Supervisor untuk diperbaiki. Catatan: '+escapeHtml(r.catatanSupervisor||'-');
    } else {
      callout.style.display='block'; callout.style.background='var(--purple-soft)'; callout.style.borderColor='#CBB9E5'; callout.style.color='#4A2F73';
      callout.innerHTML = 'Laporan sudah dikirim dan sedang menunggu validasi Supervisor. Anda akan diberi tahu setelah divalidasi.';
    }
    setTekKategori(r.kategori, document.querySelector('#tekKategoriRow .vopt[data-kat="'+r.kategori+'"]'));
    setTekPrioritas(r.prioritas, document.querySelector('#tekPrioritasRow .prio[data-p="'+r.prioritas+'"]'));
    document.getElementById('tekWaktuMulai').value = r.waktuMulai||'';
    document.getElementById('tekWaktuSelesai').value = r.waktuSelesai||'';
    hitungDowntimeTek();
    if(r.kategori==='mekanik'){
      document.getElementById('tekMekGetaran').value=r.parameter.getaran||'';
      document.getElementById('tekMekSuhu').value=r.parameter.suhu||'';
      document.getElementById('tekMekPelumasan').value=r.parameter.pelumasan||'Baik';
      document.getElementById('tekMekTorsi').value=r.parameter.torsi||'';
    } else if(r.kategori==='elektrik'){
      document.getElementById('tekElTegangan').value=r.parameter.tegangan||'';
      document.getElementById('tekElArus').value=r.parameter.arus||'';
      document.getElementById('tekElIsolasi').value=r.parameter.isolasi||'';
      document.getElementById('tekElKondisi').value=r.parameter.kondisi||'Baik';
    } else if(r.kategori==='instrumentasi'){
      document.getElementById('tekInsNilaiBaca').value=r.parameter.nilaiBaca||'';
      document.getElementById('tekInsNilaiStandar').value=r.parameter.nilaiStandar||'';
      document.getElementById('tekInsDeviasi').value=r.parameter.deviasi||'';
      document.getElementById('tekInsStatus').value=r.parameter.statusKalibrasi||'Sesuai Standar';
    }
    document.getElementById('tekDeskripsi').value = r.deskripsi||'';
    document.getElementById('tekTindakan').value = r.tindakan||'';
    document.getElementById('tekRekomendasi').value = r.rekomendasi||'';
    document.getElementById('tekJadwalBerikutnya').value = r.jadwalBerikutnya||'';
    renderSparepartRows(r.spareparts||[]);
    fields.forEach(function(f){ f.disabled=true; f.style.background='#F5F6F7'; });
    document.querySelectorAll('#tekKategoriRow .vopt, #tekPrioritasRow .prio').forEach(function(v){ v.style.pointerEvents='none'; v.style.opacity='.85'; });
    document.querySelectorAll('#tekSparepartBody .sp-del-btn').forEach(function(b){ b.style.display='none'; });
    const addBtn = document.querySelector('.sp-add-btn'); if(addBtn) addBtn.style.display='none';
    actionBar.innerHTML = '<button class="btn btn-outline" onclick="go(\'tek2-dashboard\')">Kembali ke Jadwal Saya</button>';
  }
}

function setTekKategori(kat, el){
  document.querySelectorAll('#tekKategoriRow .vopt').forEach(function(v){ v.classList.remove('on'); });
  if(el) el.classList.add('on');
  ['Mekanik','Elektrik','Instrumentasi'].forEach(function(name){
    const block = document.getElementById('tekBlok'+name);
    if(block) block.style.display = 'none';
  });
  const map = {mekanik:'tekBlokMekanik', elektrik:'tekBlokElektrik', instrumentasi:'tekBlokInstrumentasi'};
  const active = document.getElementById(map[kat]);
  if(active) active.style.display = 'block';
}
function setTekPrioritas(p, el){
  document.querySelectorAll('#tekPrioritasRow .prio').forEach(function(v){ v.classList.remove('on'); });
  if(el) el.classList.add('on');
}
function hitungDowntimeTek(){
  const mulai = document.getElementById('tekWaktuMulai').value;
  const selesai = document.getElementById('tekWaktuSelesai').value;
  const out = document.getElementById('tekDowntimeVal');
  if(!mulai || !selesai || !out){ if(out) out.textContent='—'; return; }
  const p1 = mulai.split(':').map(Number), p2 = selesai.split(':').map(Number);
  let diff = (p2[0]*60+p2[1]) - (p1[0]*60+p1[1]);
  if(diff < 0) diff += 24*60;
  const jam = Math.floor(diff/60), mnt = diff%60;
  out.textContent = (jam > 0 ? jam+' jam ' : '') + mnt + ' menit';
}
function tambahBarisSparepart(){
  const body = document.getElementById('tekSparepartBody');
  body.insertAdjacentHTML('beforeend', sparepartRowHTML());
}
function hapusBarisSparepart(btn){
  const body = document.getElementById('tekSparepartBody');
  if(body.querySelectorAll('tr').length > 1){ btn.closest('tr').remove(); }
}
function buildReportFromForm(){
  const katEl = document.querySelector('#tekKategoriRow .vopt.on');
  const prioEl = document.querySelector('#tekPrioritasRow .prio.on');
  const kategori = katEl ? katEl.dataset.kat : 'mekanik';
  const prioritas = prioEl ? prioEl.dataset.p : 'sedang';
  const parameter = {};
  if(kategori==='mekanik'){
    parameter.getaran = document.getElementById('tekMekGetaran').value;
    parameter.suhu = document.getElementById('tekMekSuhu').value;
    parameter.pelumasan = document.getElementById('tekMekPelumasan').value;
    parameter.torsi = document.getElementById('tekMekTorsi').value;
  } else if(kategori==='elektrik'){
    parameter.tegangan = document.getElementById('tekElTegangan').value;
    parameter.arus = document.getElementById('tekElArus').value;
    parameter.isolasi = document.getElementById('tekElIsolasi').value;
    parameter.kondisi = document.getElementById('tekElKondisi').value;
  } else {
    parameter.nilaiBaca = document.getElementById('tekInsNilaiBaca').value;
    parameter.nilaiStandar = document.getElementById('tekInsNilaiStandar').value;
    parameter.deviasi = document.getElementById('tekInsDeviasi').value;
    parameter.statusKalibrasi = document.getElementById('tekInsStatus').value;
  }
  const spareparts = [];
  document.querySelectorAll('#tekSparepartBody tr').forEach(function(tr){
    const inputs = tr.querySelectorAll('input,select');
    if(inputs.length>=4 && inputs[0].value.trim()){
      spareparts.push({nama:inputs[0].value, kode:inputs[1].value, qty:inputs[2].value, satuan:inputs[3].value});
    }
  });
  return {
    tanggalPemeriksaan: DEMO_TODAY, dikirim: DEMO_TODAY, pemeriksa: USERS.teknisi.name,
    kategori: kategori, prioritas: prioritas,
    waktuMulai: document.getElementById('tekWaktuMulai').value, waktuSelesai: document.getElementById('tekWaktuSelesai').value,
    parameter: parameter,
    deskripsi: document.getElementById('tekDeskripsi').value,
    tindakan: document.getElementById('tekTindakan').value,
    spareparts: spareparts,
    rekomendasi: document.getElementById('tekRekomendasi').value,
    jadwalBerikutnya: document.getElementById('tekJadwalBerikutnya').value,
    status: 'menunggu', catatanSupervisor: ''
  };
}
function submitLaporanForValidation(){
  const btn = document.getElementById('tekSubmitBtn');
  if(btn && btn.disabled) return;
  if(currentTeknisiPmId){
    const sc = PM_SCHEDULES.find(function(p){ return p.id===currentTeknisiPmId; });
    if(sc){
      sc.report = buildReportFromForm();
      sc.status = 'menunggu-validasi';
      apiFetch('/api/pm-schedules/'+sc.id+'/laporan', {method:'POST', body:JSON.stringify(sc.report)})
        .then(refreshBootstrap)
        .catch(function(err){ alert('Gagal mengirim laporan: '+err); });
    }
  }
  document.getElementById('tekValidasiCallout').style.display = 'block';
  document.getElementById('tekValidasiCallout').style.background = 'var(--purple-soft)';
  document.getElementById('tekValidasiCallout').style.borderColor = '#CBB9E5';
  document.getElementById('tekValidasiCallout').style.color = '#4A2F73';
  document.getElementById('tekValidasiCallout').innerHTML = 'Laporan sudah dikirim dan sedang menunggu validasi Supervisor. Anda akan diberi tahu setelah divalidasi.';
  const badge = document.getElementById('tekDetailStatusBadge');
  if(badge){ badge.textContent = 'Menunggu Validasi'; badge.className = 'badge b-purple'; }
  document.querySelectorAll('#scr-tek2-detail-jadwal input, #scr-tek2-detail-jadwal textarea, #scr-tek2-detail-jadwal select').forEach(function(f){
    f.disabled = true; f.style.background = '#F5F6F7';
  });
  document.querySelectorAll('#tekKategoriRow .vopt, #tekPrioritasRow .prio').forEach(function(v){ v.style.pointerEvents = 'none'; v.style.opacity = '.85'; });
  document.querySelectorAll('#tekSparepartBody .sp-del-btn').forEach(function(b){ b.style.display = 'none'; });
  const addBtn = document.querySelector('.sp-add-btn'); if(addBtn) addBtn.style.display = 'none';
  document.getElementById('tekActionBar').innerHTML = '<button class="btn btn-outline" onclick="go(\'tek2-dashboard\')">Kembali ke Jadwal Saya</button>';
}

function renderTeknisiCalendar(){
  const me = USERS.teknisi.name;
  document.querySelectorAll('#tekCalGrid .cal-cell[data-day]').forEach(function(cell){
    const day = cell.getAttribute('data-day');
    const iso = '2026-08-' + (day.length===1 ? '0'+day : day);
    const daynum = cell.querySelector('.cal-daynum');
    let evts = '';
    PM_SCHEDULES.filter(function(p){ return p.teknisi===me && p.tanggal===iso; }).forEach(function(p){
      const m = getMachine(p.machineId);
      const cls = p.status==='selesai' ? 'done' : (iso===DEMO_TODAY ? 'due' : '');
      const label = p.status==='selesai' ? 'Selesai' : p.jenis.split(' ').slice(0,2).join(' ');
      evts += '<div class="cal-evt '+cls+'" onclick="openTeknisiDetail(\''+p.id+'\')">'+escapeHtml(machineLabel(m))+' · '+escapeHtml(label)+'</div>';
    });
    MAINTENANCE_HISTORY.filter(function(h){ return h.pelaksana===me && h.tanggal===iso; }).forEach(function(h){
      const m = getMachine(h.machineId);
      evts += '<div class="cal-evt done" onclick="go(\'tek2-riwayat\')">'+escapeHtml(machineLabel(m))+' · Selesai</div>';
    });
    cell.innerHTML = '';
    if(daynum) cell.appendChild(daynum);
    cell.insertAdjacentHTML('beforeend', evts);
  });
}

function renderTeknisiRiwayat(){
  const me = USERS.teknisi.name;
  const tbody = document.getElementById('tekRiwayatTbody');
  if(!tbody) return;
  const list = MAINTENANCE_HISTORY.filter(function(h){ return h.pelaksana===me; }).slice().sort(function(a,b){ return b.tanggal.localeCompare(a.tanggal); });
  tbody.innerHTML = list.length ? list.map(function(h){
    const m = getMachine(h.machineId);
    return '<tr><td class="mono">'+escapeHtml(h.noLaporan)+'</td><td>'+escapeHtml(machineLabel(m))+'</td><td>'+escapeHtml(h.pekerjaan)+'</td><td class="mono">'+h.downtimeMenit+' menit</td><td class="mono">'+formatTanggalID(h.tanggal)+'</td></tr>';
  }).join('') : '<tr><td colspan="5" class="empty-state">Belum ada riwayat perbaikan.</td></tr>';
}

/* =====================================================================
   MANAJER
   ===================================================================== */
function renderDashboardManajer(){
  const avgOee = Math.round(MACHINES.reduce(function(s,m){ return s+getPerf(m.id).oee; },0)/MACHINES.length);
  const avgAvail = Math.round(MACHINES.reduce(function(s,m){ return s+getPerf(m.id).availability; },0)/MACHINES.length);
  const totalDowntime = MACHINES.reduce(function(s,m){ return s+getPerf(m.id).downtimeBulanIni; }, 0);
  const totalPerbaikan = MACHINES.reduce(function(s,m){ return s+getPerf(m.id).perbaikanBulanIni; }, 0);
  const sortedByDowntime = MACHINES.slice().sort(function(a,b){ return getPerf(b.id).downtimeBulanIni - getPerf(a.id).downtimeBulanIni; });
  const worst = sortedByDowntime[0];

  setStatValue('manDashOee', avgOee, '%');
  setStatValue('manDashAvail', avgAvail, '%');
  setStatValue('manDashDowntime', totalDowntime.toFixed(1), 'jam');
  setText('manDashPerbaikan', totalPerbaikan);
  setText('manDashWorst', worst ? worst.name : '-');

  mount('manDashDowntimeChart', buildBarChartSVG(sortedByDowntime.slice(0,8).map(function(m){ return {label:m.name, value:getPerf(m.id).downtimeBulanIni, color:statusMeta(m.status).color}; }), {unit:'j', height:220, shortLabel:true}));

  const rankWrap = document.getElementById('manDashRankList');
  if(rankWrap){
    rankWrap.innerHTML = sortedByDowntime.slice(0,4).map(function(m,i){
      const perf = getPerf(m.id);
      return '<div class="rank-row"><div class="rank-num'+(i<2?' top':'')+'">'+(i+1)+'</div><div class="rank-info"><div class="rn">'+escapeHtml(m.name)+'</div><div class="rs">OEE '+perf.oee+'% · '+perf.perbaikanBulanIni+' perbaikan bulan ini</div></div><div class="rank-val" style="color:'+(i<2?'var(--red)':'inherit')+';">'+perf.downtimeBulanIni+' jam</div></div>';
    }).join('');
  }
}

function renderManPerformaTabs(){
  const wrap = document.getElementById('manPerformaTabs');
  if(!wrap) return;
  let html = '<button class="mtab'+(currentManPerformaId==='all'?' on':'')+'" data-mesin="all" onclick="switchManPerforma(\'all\', this)">Semua Mesin</button>';
  MACHINES.forEach(function(m){
    html += '<button class="mtab'+(currentManPerformaId===m.id?' on':'')+'" data-mesin="'+m.id+'" onclick="switchManPerforma(\''+m.id+'\', this)">'+escapeHtml(m.name)+'</button>';
  });
  wrap.innerHTML = html;
}
function switchManPerforma(machineId, el){
  currentManPerformaId = machineId;
  document.querySelectorAll('#manPerformaTabs .mtab').forEach(function(x){ x.classList.toggle('on', x.getAttribute('data-mesin')===machineId); });
  const items = MACHINES.map(function(m){
    const perf = getPerf(m.id);
    const meta = statusMeta(m.status);
    const dim = (machineId!=='all' && machineId!==m.id);
    return {label:m.name, value:perf.oee, color:meta.color, opacity: dim?0.18:0.9};
  });
  mount('manPerformaChartMount', buildBarChartSVG(items, {unit:'%', height:200, shortLabel:true}));

  const tbody = document.getElementById('manPerformaTbody');
  if(tbody){
    tbody.innerHTML = MACHINES.map(function(m){
      const perf = getPerf(m.id);
      let cls = '';
      if(machineId!=='all'){ cls = (machineId===m.id) ? 'row-focus' : 'row-dimmed'; }
      return '<tr class="'+cls+'" data-mesin="'+m.id+'"><td><strong>'+escapeHtml(m.name)+'</strong></td><td class="mono">'+perf.oee+'%</td><td class="mono">'+perf.availability+'%</td><td class="mono">'+perf.mttr+' jam</td><td class="mono">'+perf.mtbf+' jam</td><td class="mono">'+perf.downtimeBulanIni+' jam</td></tr>';
    }).join('');
  }

  const title = document.getElementById('manPerformaTitle');
  const sub = document.getElementById('manPerformaSub');
  if(title && sub){
    if(machineId==='all'){
      title.textContent = 'Perbandingan OEE Antar Mesin';
      sub.textContent = 'Rata-rata Agustus 2026 — seluruh mesin';
    } else {
      const m = getMachine(machineId);
      title.textContent = 'Perbandingan OEE — Disorot ' + (m?m.name:'');
      sub.textContent = 'Rata-rata Agustus 2026 — mesin lain ditampilkan pudar untuk perbandingan';
    }
  }
}

function renderKomponenRanking(){
  const wrap = document.getElementById('manKomponenRanking');
  if(!wrap) return;
  const keywords = ['Bearing','Kontaktor','Sensor','Panel','Baut','Kabel','Seal'];
  const counts = {};
  MAINTENANCE_HISTORY.forEach(function(h){
    keywords.forEach(function(k){
      if(h.pekerjaan.indexOf(k)!==-1){ counts[k]=(counts[k]||0)+1; }
    });
  });
  const arr = Object.keys(counts).map(function(k){ return {label:k, count:counts[k]}; }).sort(function(a,b){ return b.count-a.count; }).slice(0,5);
  wrap.innerHTML = arr.length ? arr.map(function(c,i){
    return '<div class="rank-row"><div class="rank-num'+(i===0?' top':'')+'">'+(i+1)+'</div><div class="rank-info"><div class="rn">'+escapeHtml(c.label)+'</div></div><div class="rank-val">'+c.count+'×</div></div>';
  }).join('') : '<div class="empty-state">Belum ada data komponen.</div>';
}

document.querySelectorAll('.mtab').forEach(function(b){
  if(!b.hasAttribute('onclick')){
    b.addEventListener('click', function(){
      const row = b.closest('.machine-tab-row');
      row.querySelectorAll('.mtab').forEach(function(x){ x.classList.remove('on'); });
      b.classList.add('on');
    });
  }
});

/* =====================================================================
   INISIALISASI HALAMAN APP (/app) — appShell langsung tampil karena
   transisi splash & welcome sudah terjadi di halaman login sebelumnya.
   ===================================================================== */
document.addEventListener('DOMContentLoaded', function(){
  const shell = document.getElementById('appShell');
  if(shell){
    shell.style.display = 'flex';
    setRole(currentRole);
  }
});

