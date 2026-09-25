(function(){
'use strict';

function esc(v){return String(v==null?'':v).replace(/[&<>\"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;'}[c];});}
function periodValue(){var e=document.getElementById('lapPeriodeSel');var v=e?e.value:'agu';return v==='q3'?'quarter':v==='th'?'year':'month';}
function stationValue(){var e=document.getElementById('lapStasiunSel');return e&&e.value?e.value:'all';}
function machineValue(){var e=document.getElementById('lapMesinSel');return e&&e.value?e.value:'all';}
function status(t){var e=document.getElementById('supReportExportStatus');if(e){e.textContent=t;e.style.display='block';}}
function getReport(){var q='period='+encodeURIComponent(periodValue())+'&station_id='+encodeURIComponent(stationValue())+'&machine_id='+encodeURIComponent(machineValue());return apiFetch('/api/manager/report?'+q);}
function num(v,d){return v==null||v===''||isNaN(Number(v))?'—':Number(v).toFixed(d==null?1:d);}
function periodText(d){return formatTanggalID(d.periodStart)+' s/d '+formatTanggalID(d.periodEnd);}

window.printSupervisorReport=function(){
  var q='period='+encodeURIComponent(periodValue())+'&station_id='+encodeURIComponent(stationValue())+'&machine_id='+encodeURIComponent(machineValue());
  var url='/manager/report/print?'+q+'&_='+Date.now();
  status('Membuka laporan PDF...');
  var w=window.open(url,'_blank','noopener,noreferrer');
  if(!w){
    status('Popup diblokir browser. Izinkan popup untuk membuka laporan PDF.');
    alert('Popup diblokir browser. Izinkan popup untuk membuat PDF.');
    return;
  }
  setTimeout(function(){status('Laporan PDF sudah dibuka. Gunakan Print/Cetak lalu Save as PDF.');},700);
};

window.exportSupervisorExcel=function(){
  status('Menyiapkan Excel...');
  getReport().then(function(d){
    var r=d.rows||[];
    var html='<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial;font-size:10pt;color:#182230}h2{color:#0b7285;margin-bottom:4px}h3{margin-top:4px}p{margin:4px 0 10px}table{border-collapse:collapse;width:100%;table-layout:fixed}col.machine{width:16%}col.station{width:16%}col.metric{width:7%}col.condition{width:12%}th{background:#0b7285;color:#fff;border:1px solid #777;padding:7px 5px;text-align:center;vertical-align:middle}td{border:1px solid #aaa;padding:6px 5px;vertical-align:middle;word-break:break-word}td.num{text-align:right;white-space:nowrap}td.condition{text-align:center;white-space:nowrap}.notice{padding:8px;background:#fff9db;border:1px solid #e7c84a}</style></head><body>';
    html+='<h2>SIMPM · PG RENDENG</h2><h3>Laporan Performance Mesin — Supervisor</h3><p>Periode: '+esc(periodText(d))+' · '+r.length+' mesin</p>';
    if(!d.hasProductionData)html+='<p class="notice"><strong>Data produksi belum tersedia untuk periode ini.</strong> Nilai performa tidak dibuat menjadi 0.</p>';
    html+='<table><colgroup><col class="machine"><col class="station"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="condition"></colgroup><thead><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR (jam)</th><th>MTBF (jam)</th><th>Downtime (jam)</th><th>Kondisi</th></tr></thead><tbody>';
    html+=r.map(function(x){return '<tr><td>'+esc(x.machine)+'</td><td>'+esc(x.station)+'</td><td class="num">'+num(x.oee)+'%</td><td class="num">'+num(x.availability)+'%</td><td class="num">'+num(x.performance)+'%</td><td class="num">'+num(x.quality)+'%</td><td class="num">'+num(x.reliability)+'%</td><td class="num">'+num(x.mttr)+'</td><td class="num">'+num(x.mtbf)+'</td><td class="num">'+num(x.downtime)+'</td><td class="condition">'+esc(x.kondisi||'Belum ada data')+'</td></tr>';}).join('');
    html+='</tbody></table></body></html>';
    var a=document.createElement('a'),u=URL.createObjectURL(new Blob([html],{type:'application/vnd.ms-excel;charset=utf-8'}));
    a.href=u;a.download='laporan-supervisor-'+d.period+'-'+d.periodEnd+'.xls';document.body.appendChild(a);a.click();a.remove();
    setTimeout(function(){URL.revokeObjectURL(u);},1000);status('Excel berhasil dibuat.');
  }).catch(function(e){status('Gagal membuat Excel: '+e);});
};
})();
