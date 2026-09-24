(function(){
'use strict';
function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
function pick(){var p=document.getElementById('manReportPeriod'),m=document.getElementById('manReportMachine');return {period:p?p.value:'month',machine:m?m.value:'all'};}
function rows(){var s=pick();return apiFetch('/api/manager/report?period='+encodeURIComponent(s.period)+'&machine_id='+encodeURIComponent(s.machine));}
function n(v,d){return v==null||v===''||isNaN(Number(v))?'—':Number(v).toFixed(d==null?1:d);}
function period(d){return formatTanggalID(d.periodStart)+' s/d '+formatTanggalID(d.periodEnd);}
function title(d){var p=pick().period;return 'Laporan Performance Mesin — '+(p==='year'?'Tahunan':p==='quarter'?'Kuartalan':'Bulanan');}
function status(t){var e=document.getElementById('manReportStatus');if(e){e.textContent=t;e.style.display='block';}}
window.exportManagerExcel=function(){
 status('Menyiapkan file Excel...');
 rows().then(function(d){
  var r=d.rows||[];
  var h='<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial}table{border-collapse:collapse;width:100%}th{background:#dce6f1;border:1px solid #777;padding:6px}td{border:1px solid #aaa;padding:5px}</style></head><body><h2>'+esc(title(d))+'</h2><p>PG Rendeng · '+esc(period(d))+' · '+r.length+' mesin</p><table><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR</th><th>MTBF</th><th>Downtime</th><th>Kondisi</th></tr>';
  h+=r.map(function(x){return '<tr><td>'+esc(x.machine)+'</td><td>'+esc(x.station)+'</td><td>'+n(x.oee)+'%</td><td>'+n(x.availability)+'%</td><td>'+n(x.performance)+'%</td><td>'+n(x.quality)+'%</td><td>'+n(x.reliability)+'%</td><td>'+n(x.mttr)+'</td><td>'+n(x.mtbf)+'</td><td>'+n(x.downtime)+'</td><td>'+esc(x.kondisi||'—')+'</td></tr>';}).join('');
  h+='</table></body></html>';
  var a=document.createElement('a'),u=URL.createObjectURL(new Blob([h],{type:'application/vnd.ms-excel;charset=utf-8'}));a.href=u;a.download='laporan-manager-'+d.period+'-'+d.periodEnd+'.xls';document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(u);},1000);status('Excel berhasil dibuat dalam format .xls yang dapat dibuka di Microsoft Excel.');
 }).catch(function(e){status('Gagal membuat Excel: '+e);});
};
window.printManagerPdf=function(){
 var w=window.open('','_blank','width=1200,height=850');
 if(!w){alert('Popup diblokir browser. Izinkan popup untuk membuat PDF.');return;}
 w.document.write('<p style="font-family:Arial;padding:30px">Menyiapkan laporan...</p>');w.document.close();status('Menyiapkan PDF...');
 rows().then(function(d){
  var r=d.rows||[];
  var avg=function(k){var a=r.map(function(x){return Number(x[k]);}).filter(function(x){return isFinite(x);});return a.length?a.reduce(function(x,y){return x+y;},0)/a.length:null;};
  var h='<!doctype html><html><head><meta charset="utf-8"><title>'+esc(title(d))+'</title><style>@page{size:A4 landscape;margin:14mm}*{box-sizing:border-box}body{font-family:Arial;color:#182230;font-size:9px}.head{border-bottom:3px solid #1f4e79;padding-bottom:10px}.brand{font-weight:bold;color:#1f4e79;letter-spacing:1px}.title{font-size:20px;font-weight:bold;margin:3px 0}.sub{color:#667085}.cards{display:grid;grid-template-columns:repeat(6,1fr);gap:7px;margin:12px 0}.card{border:1px solid #d9e0e7;padding:8px;background:#f8fafc}.label{font-size:7px;color:#667085;text-transform:uppercase}.value{font-size:14px;font-weight:bold;margin-top:3px}table{width:100%;border-collapse:collapse}th{background:#1f4e79;color:white;padding:6px 4px;font-size:7px;text-align:left}td{border-bottom:1px solid #ddd;padding:5px 4px;font-size:8px}.r{text-align:right}.foot{margin-top:10px;border-top:1px solid #ddd;padding-top:6px;color:#667085;font-size:7px}</style></head><body><div class="head"><div class="brand">SIMPM · PG RENDENG</div><div class="title">'+esc(title(d))+'</div><div class="sub">Periode '+esc(period(d))+' · '+r.length+' mesin</div></div><div class="cards"><div class="card"><div class="label">OEE</div><div class="value">'+n(avg('oee'))+'%</div></div><div class="card"><div class="label">Availability</div><div class="value">'+n(avg('availability'))+'%</div></div><div class="card"><div class="label">Performance</div><div class="value">'+n(avg('performance'))+'%</div></div><div class="card"><div class="label">Quality</div><div class="value">'+n(avg('quality'))+'%</div></div><div class="card"><div class="label">MTTR</div><div class="value">'+n(avg('mttr'))+' jam</div></div><div class="card"><div class="label">MTBF</div><div class="value">'+n(avg('mtbf'))+' jam</div></div></div><table><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR</th><th>MTBF</th><th>Downtime</th><th>Kondisi</th></tr>';
  h+=r.map(function(x){return '<tr><td>'+esc(x.machine)+'</td><td>'+esc(x.station)+'</td><td class="r">'+n(x.oee)+'%</td><td class="r">'+n(x.availability)+'%</td><td class="r">'+n(x.performance)+'%</td><td class="r">'+n(x.quality)+'%</td><td class="r">'+n(x.reliability)+'%</td><td class="r">'+n(x.mttr)+'</td><td class="r">'+n(x.mtbf)+'</td><td class="r">'+n(x.downtime)+'</td><td>'+esc(x.kondisi||'—')+'</td></tr>';}).join('');
  h+='</table><div class="foot">Sumber: database SIMPM dan PerformanceCalculator · dibuat '+esc(new Date().toLocaleString('id-ID'))+'</div></body></html>';
  w.document.open();w.document.write(h);w.document.close();w.focus();setTimeout(function(){w.print();},400);status('PDF siap. Pilih Save as PDF pada dialog cetak.');
 }).catch(function(e){w.document.body.innerHTML='<p style="font-family:Arial;color:#b42318">Gagal membuat laporan: '+esc(e)+'</p>';status('Gagal membuat PDF: '+e);});
};
document.addEventListener('DOMContentLoaded',function(){var e=document.getElementById('manReportPeriodLabel');if(e)e.textContent='Pilih periode dan mesin untuk membuat laporan dari data kalkulasi server.';});
})();
