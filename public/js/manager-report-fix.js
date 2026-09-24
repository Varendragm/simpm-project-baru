(function(){
'use strict';
function esc(v){return String(v==null?'':v).replace(/[&<>\"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;'}[c];});}
function pick(){var p=document.getElementById('manReportPeriod'),m=document.getElementById('manReportMachine');return {period:p?p.value:'month',machine:m?m.value:'all'};}
function rows(){var s=pick();return apiFetch('/api/manager/report?period='+encodeURIComponent(s.period)+'&machine_id='+encodeURIComponent(s.machine));}
function tableN(v,d){return v==null||v===''||isNaN(Number(v))?'—':Number(v).toFixed(d==null?1:d);}
function avg(r,k){var a=r.map(function(x){return Number(x[k]);}).filter(function(x){return isFinite(x);});return a.length?a.reduce(function(x,y){return x+y;},0)/a.length:null;}
function fmt(v,unit){return v==null||v===''||isNaN(Number(v))?'Belum ada data':Number(v).toFixed(1)+(unit||'');}
function period(d){return formatTanggalID(d.periodStart)+' s/d '+formatTanggalID(d.periodEnd);}
function title(d){var p=pick().period;return 'Laporan Performance Mesin — '+(p==='year'?'Tahunan':p==='quarter'?'Kuartalan':'Bulanan');}
function status(t){var e=document.getElementById('manReportStatus');if(e){e.textContent=t;e.style.display='block';}}
window.exportManagerExcel=function(){
 status('Menyiapkan file Excel...');
 rows().then(function(d){var r=d.rows||[];var h='<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial;font-size:10pt;color:#182230}h2{color:#0b7285;margin-bottom:4px}h3{margin-top:4px}table{border-collapse:collapse;width:100%;table-layout:fixed}col.machine{width:18%}col.station{width:18%}col.metric{width:7%}col.condition{width:12%}th{background:#0b7285;color:#fff;border:1px solid #777;padding:7px 5px;text-align:center;vertical-align:middle}td{border:1px solid #aaa;padding:6px 5px;vertical-align:middle;word-break:break-word}td.num{text-align:right;white-space:nowrap}td.condition{text-align:center;white-space:nowrap}.notice{padding:8px;background:#fff9db;border:1px solid #e7c84a}</style></head><body><h2>SIMPM · PG RENDENG</h2><h3>'+esc(title(d))+'</h3><p>Periode: '+esc(period(d))+' · '+r.length+' mesin</p>';if(!d.hasProductionData)h+='<p class="notice"><strong>Data produksi belum tersedia untuk periode ini.</strong> Nilai performa tidak dibuat menjadi 0.</p>';h+='<table><colgroup><col class="machine"><col class="station"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="condition"></colgroup><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR (jam)</th><th>MTBF (jam)</th><th>Downtime (jam)</th><th>Kondisi</th></tr>';h+=r.map(function(x){return '<tr><td>'+esc(x.machine)+'</td><td>'+esc(x.station)+'</td><td class="num">'+tableN(x.oee)+'%</td><td class="num">'+tableN(x.availability)+'%</td><td class="num">'+tableN(x.performance)+'%</td><td class="num">'+tableN(x.quality)+'%</td><td class="num">'+tableN(x.reliability)+'%</td><td class="num">'+tableN(x.mttr)+'</td><td class="num">'+tableN(x.mtbf)+'</td><td class="num">'+tableN(x.downtime)+'</td><td class="condition">'+esc(x.kondisi||'Belum ada data')+'</td></tr>';}).join('');h+='</table></body></html>';var a=document.createElement('a'),u=URL.createObjectURL(new Blob([h],{type:'application/vnd.ms-excel;charset=utf-8'}));a.href=u;a.download='laporan-manager-'+d.period+'-'+d.periodEnd+'.xls';document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(u);},1000);status('Excel berhasil dibuat.');}).catch(function(e){status('Gagal membuat Excel: '+e);});
};
window.printManagerPdf=function(){
 var s=pick();
 var url='/manager/report/print?period='+encodeURIComponent(s.period)+'&machine_id='+encodeURIComponent(s.machine)+'&_='+Date.now();
 status('Membuka laporan PDF...');
 var w=window.open(url,'_blank','noopener,noreferrer');
 if(!w){status('Popup diblokir browser. Izinkan popup untuk membuka laporan PDF.');alert('Popup diblokir browser. Izinkan popup untuk membuat PDF.');return;}
 setTimeout(function(){status('Laporan PDF sudah dibuka. Gunakan Save as PDF pada dialog cetak.');},700);
};
document.addEventListener('DOMContentLoaded',function(){var e=document.getElementById('manReportPeriodLabel');if(e)e.textContent='Pilih periode dan mesin untuk membuat laporan dari data kalkulasi server.';});
})();
