(function(){
'use strict';
function esc(v){return String(v==null?'':v).replace(/[&<>\"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;'}[c];});}
function pick(){var p=document.getElementById('manReportPeriod'),m=document.getElementById('manReportMachine');return {period:p?p.value:'month',machine:m?m.value:'all'};}
function rows(){var s=pick();return apiFetch('/api/manager/report?period='+encodeURIComponent(s.period)+'&machine_id='+encodeURIComponent(s.machine));}
function n(v,d){return v==null||v===''||isNaN(Number(v))?'—':Number(v).toFixed(d==null?1:d);}
function period(d){return formatTanggalID(d.periodStart)+' s/d '+formatTanggalID(d.periodEnd);}
function title(d){var p=pick().period;return 'Laporan Performance Mesin — '+(p==='year'?'Tahunan':p==='quarter'?'Kuartalan':'Bulanan');}
function status(t){var e=document.getElementById('manReportStatus');if(e){e.textContent=t;e.style.display='block';}}
function avg(r,k){var a=r.map(function(x){return Number(x[k]);}).filter(function(x){return isFinite(x);});return a.length?a.reduce(function(x,y){return x+y;},0)/a.length:null;}
function makeReportHtml(d){
 var r=d.rows||[], created=new Date().toLocaleString('id-ID');
 var h='<!doctype html><html><head><meta charset="utf-8"><title>'+esc(title(d))+'</title><style>@page{size:A4 landscape;margin:12mm}*{box-sizing:border-box}body{font-family:Arial,Helvetica,sans-serif;color:#182230;font-size:9px;margin:0}.head{border-bottom:3px solid #0b7285;padding-bottom:10px}.brand{font-weight:800;color:#0b7285;letter-spacing:1px;font-size:11px}.title{font-size:19px;font-weight:800;margin:3px 0}.sub{color:#667085}.cards{display:grid;grid-template-columns:repeat(6,1fr);gap:7px;margin:12px 0}.card{border:1px solid #d9e0e7;border-radius:5px;padding:8px;background:#f8fafc}.label{font-size:7px;color:#667085;text-transform:uppercase}.value{font-size:13px;font-weight:800;margin-top:3px}.section{font-size:11px;font-weight:800;margin:10px 0 6px}.table-wrap{width:100%;overflow:hidden}table{width:100%;border-collapse:collapse;table-layout:fixed}th{background:#0b7285;color:#fff;padding:6px 4px;font-size:7px;text-align:left}td{border-bottom:1px solid #dfe4ea;padding:5px 4px;font-size:7.5px;word-break:break-word}.r{text-align:right}.cond{font-weight:700}.foot{margin-top:10px;border-top:1px solid #ddd;padding-top:6px;color:#667085;font-size:7px}.empty{padding:18px;text-align:center;border:1px dashed #cbd5e1;color:#667085}</style></head><body><div class="head"><div class="brand">SIMPM · PG RENDENG</div><div class="title">'+esc(title(d))+'</div><div class="sub">Periode '+esc(period(d))+' · '+r.length+' mesin</div></div><div class="cards"><div class="card"><div class="label">OEE</div><div class="value">'+n(avg(r,'oee'))+'%</div></div><div class="card"><div class="label">Availability</div><div class="value">'+n(avg(r,'availability'))+'%</div></div><div class="card"><div class="label">Performance</div><div class="value">'+n(avg(r,'performance'))+'%</div></div><div class="card"><div class="label">Quality</div><div class="value">'+n(avg(r,'quality'))+'%</div></div><div class="card"><div class="label">MTTR</div><div class="value">'+n(avg(r,'mttr'))+' jam</div></div><div class="card"><div class="label">MTBF</div><div class="value">'+n(avg(r,'mtbf'))+' jam</div></div></div><div class="section">Performance Mesin</div>';
 if(!r.length){h+='<div class="empty">Tidak ada data mesin untuk periode yang dipilih.</div>';}else{h+='<div class="table-wrap"><table><thead><tr><th style="width:12%">Mesin</th><th style="width:12%">Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR</th><th>MTBF</th><th>Downtime</th><th style="width:10%">Kondisi</th></tr></thead><tbody>';
 h+=r.map(function(x){return '<tr><td>'+esc(x.machine)+'</td><td>'+esc(x.station)+'</td><td class="r">'+n(x.oee)+'%</td><td class="r">'+n(x.availability)+'%</td><td class="r">'+n(x.performance)+'%</td><td class="r">'+n(x.quality)+'%</td><td class="r">'+n(x.reliability)+'%</td><td class="r">'+n(x.mttr)+'</td><td class="r">'+n(x.mtbf)+'</td><td class="r">'+n(x.downtime)+' jam</td><td class="cond">'+esc(x.kondisi||'—')+'</td></tr>';}).join('');
 h+='</tbody></table></div>';}
 h+='<div class="foot">Sumber: database SIMPM dan PerformanceCalculator · dibuat '+esc(created)+'</div></body></html>';
 return h;
}
window.exportManagerExcel=function(){
 status('Menyiapkan file Excel...');
 rows().then(function(d){
  var r=d.rows||[];
  var h='<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial}h2{color:#0b7285}table{border-collapse:collapse;width:100%}th{background:#0b7285;color:#fff;border:1px solid #777;padding:7px}td{border:1px solid #aaa;padding:6px} .num{text-align:right}</style></head><body><h2>SIMPM · PG RENDENG</h2><h3>'+esc(title(d))+'</h3><p>Periode: '+esc(period(d))+' · '+r.length+' mesin</p><table><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR (jam)</th><th>MTBF (jam)</th><th>Downtime (jam)</th><th>Kondisi</th></tr>';
  h+=r.map(function(x){return '<tr><td>'+esc(x.machine)+'</td><td>'+esc(x.station)+'</td><td class="num">'+n(x.oee)+'%</td><td class="num">'+n(x.availability)+'%</td><td class="num">'+n(x.performance)+'%</td><td class="num">'+n(x.quality)+'%</td><td class="num">'+n(x.reliability)+'%</td><td class="num">'+n(x.mttr)+'</td><td class="num">'+n(x.mtbf)+'</td><td class="num">'+n(x.downtime)+'</td><td>'+esc(x.kondisi||'—')+'</td></tr>';}).join('');
  h+='</table></body></html>';
  var a=document.createElement('a'),u=URL.createObjectURL(new Blob([h],{type:'application/vnd.ms-excel;charset=utf-8'}));a.href=u;a.download='laporan-manager-'+d.period+'-'+d.periodEnd+'.xls';document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(u);},1000);status('Excel berhasil dibuat. Jika angka OEE/MTTR kosong, periksa apakah periode tersebut memiliki data produksi/maintenance.');
 }).catch(function(e){status('Gagal membuat Excel: '+e);});
};
window.printManagerPdf=function(){
 status('Menyiapkan PDF...');
 var w=window.open('about:blank','_blank','width=1200,height=850');
 if(!w){alert('Popup diblokir browser. Izinkan popup untuk membuat PDF.');return;}
 rows().then(function(d){
   var html=makeReportHtml(d);
   var url=URL.createObjectURL(new Blob([html],{type:'text/html;charset=utf-8'}));
   w.location.href=url;
   var timer=setInterval(function(){
     try{if(w.document&&w.document.readyState==='complete'){clearInterval(timer);setTimeout(function(){w.focus();w.print();URL.revokeObjectURL(url);status('PDF siap. Pilih Save as PDF pada dialog cetak.');},350);}}catch(e){}
   },100);
   setTimeout(function(){clearInterval(timer);},10000);
 }).catch(function(e){try{w.document.body.innerHTML='<p style="font-family:Arial;color:#b42318;padding:30px">Gagal membuat laporan: '+esc(e)+'</p>';}catch(_e){}status('Gagal membuat PDF: '+e);});
};
document.addEventListener('DOMContentLoaded',function(){var e=document.getElementById('manReportPeriodLabel');if(e)e.textContent='Pilih periode dan mesin untuk membuat laporan dari data kalkulasi server.';});
})();
