(function(){
  'use strict';
  function esc(v){return '"'+String(v==null?'':v).replace(/"/g,'""')+'"';}
  function selected(){
    var p=document.getElementById('manReportPeriod');
    var m=document.getElementById('manReportMachine');
    return {period:p?p.value:'month',machine:m?m.value:'all'};
  }
  function fetchRows(){
    var s=selected();
    return apiFetch('/api/manager/report?period='+encodeURIComponent(s.period)+'&machine_id='+encodeURIComponent(s.machine));
  }
  function fmt(v,s){return v==null||v===''||isNaN(Number(v))?'—':Number(v).toFixed(2)+s;}
  function download(name,text,type){var b=new Blob([text],{type:type});var a=document.createElement('a');a.href=URL.createObjectURL(b);a.download=name;document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(a.href);},1000);}
  function periodTitle(data){
    return data.periodStart+' s/d '+data.periodEnd;
  }
  window.exportManagerExcel=function(){
    fetchRows().then(function(data){
      var headers=['Mesin','Stasiun','OEE (%)','Availability (%)','Performance (%)','Quality (%)','Reliability (%)','MTTR (jam)','MTBF (jam)','Downtime (jam)','Kondisi','Periode Mulai','Periode Selesai'];
      var csv='\ufeff'+headers.map(esc).join(';')+'\n'+(data.rows||[]).map(function(r){return [r.machine,r.station,r.oee,r.availability,r.performance,r.quality,r.reliability,r.mttr,r.mtbf,r.downtime,r.kondisi,r.periodStart,r.periodEnd].map(esc).join(';');}).join('\n');
      download('laporan-manager-'+data.period+'-'+data.periodEnd+'.csv',csv,'text/csv;charset=utf-8;');
    }).catch(function(e){alert('Gagal membuat laporan: '+e);});
  };
  window.printManagerPdf=function(){
    fetchRows().then(function(data){
      var rows=data.rows||[];
      var html='<!doctype html><html><head><meta charset="utf-8"><title>Laporan Manager SIMPM</title><style>body{font-family:Arial,sans-serif;padding:28px;color:#111}h1{font-size:20px;margin:0 0 6px}p{color:#555}table{width:100%;border-collapse:collapse;margin-top:18px;font-size:10px}th,td{border:1px solid #aaa;padding:5px}th{background:#eee}.meta{margin:12px 0;font-size:12px}.footer{margin-top:20px;font-size:10px;color:#666}@media print{body{padding:10px}}</style></head><body><h1>Laporan Performance Mesin — SIMPM PG Rendeng</h1><p>Periode: '+periodTitle(data)+'</p><div class="meta">Jumlah mesin: '+rows.length+'</div><table><thead><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR</th><th>MTBF</th><th>Downtime</th><th>Kondisi</th></tr></thead><tbody>'+rows.map(function(r){return '<tr><td>'+escapeHtml(r.machine)+'</td><td>'+escapeHtml(r.station)+'</td><td>'+fmt(r.oee,'%')+'</td><td>'+fmt(r.availability,'%')+'</td><td>'+fmt(r.performance,'%')+'</td><td>'+fmt(r.quality,'%')+'</td><td>'+fmt(r.reliability,'%')+'</td><td>'+fmt(r.mttr,' jam')+'</td><td>'+fmt(r.mtbf,' jam')+'</td><td>'+fmt(r.downtime,' jam')+'</td><td>'+escapeHtml(r.kondisi)+'</td></tr>';}).join('')+'</tbody></table><div class="footer">Sumber: database SIMPM dan PerformanceCalculator.</div><script>window.onload=function(){window.print();};<\/script></body></html>';
      var w=window.open('','_blank','width=1200,height=800');
      if(!w){alert('Popup diblokir browser. Izinkan popup untuk mencetak PDF.');return;}
      w.document.write(html);w.document.close();
    }).catch(function(e){alert('Gagal membuat laporan: '+e);});
  };
  function refreshLabel(){
    var el=document.getElementById('manReportPeriodLabel');
    if(el) el.textContent='Pilih periode dan mesin, lalu unduh laporan dari data kalkulasi server.';
  }
  document.addEventListener('DOMContentLoaded',function(){setTimeout(refreshLabel,50);});
})();
