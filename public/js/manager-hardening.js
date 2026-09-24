/* SIMPM Manager hardening: removes manager-side mock data and adds real reporting actions. */
(function(){
  'use strict';

  function pad(n){ return String(n).padStart(2,'0'); }
  function monthKey(d){ return d.getFullYear()+'-'+pad(d.getMonth()+1); }
  function monthLabel(key){
    var p=key.split('-');
    var names=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return names[Number(p[1])-1]+' '+p[0];
  }
  function refDate(){
    var dates=[];
    (MAINTENANCE_HISTORY||[]).forEach(function(h){ if(h.tanggal) dates.push(new Date(h.tanggal+'T00:00:00')); });
    Object.keys(MACHINE_PERFORMANCE||{}).forEach(function(id){ var p=MACHINE_PERFORMANCE[id]; if(p&&p.periodEnd) dates.push(new Date(p.periodEnd+'T00:00:00')); });
    dates=dates.filter(function(d){ return !isNaN(d.getTime()); });
    return dates.length ? new Date(Math.max.apply(null,dates.map(function(d){return d.getTime();}))) : new Date();
  }
  function periodLabel(){ var d=refDate(); return d.toLocaleDateString('id-ID',{month:'long',year:'numeric'}); }
  function num(v){ return v===null || v===undefined || v==='' ? null : Number(v); }
  function display(v,suffix){ return v===null || v===undefined || isNaN(Number(v)) ? '—' : Number(v).toFixed(2)+suffix; }
  function setText(id,text){ var e=document.getElementById(id); if(e) e.textContent=text; }

  window.renderManDashboard=function(){
    var machines=MACHINES||[];
    var perfs=machines.map(function(m){ return getPerf(m.id); });
    function avg(key){ var a=perfs.map(function(p){return num(p[key]);}).filter(function(v){return v!==null && !isNaN(v);}); return a.length ? a.reduce(function(x,y){return x+y;},0)/a.length : null; }
    var avgOee=avg('oee'), avgAvail=avg('availability'), avgPerf=avg('performance'), avgQuality=avg('quality'), avgRel=avg('reliability');
    var downtime=perfs.reduce(function(s,p){return s+(num(p.downtimeBulanIni)||0);},0);
    var failures=perfs.reduce(function(s,p){return s+(num(p.perbaikanBulanIni)||0);},0);
    var condition={normal:0,perhatian:0,perbaikan:0};
    machines.forEach(function(m){ if(condition[m.kondisi]!==undefined) condition[m.kondisi]++; });
    setText('manDashOee',avgOee===null?'—':avgOee.toFixed(2)+'%');
    setText('manDashAvail',avgAvail===null?'—':avgAvail.toFixed(2)+'%');
    setText('manDashPerformance',avgPerf===null?'—':avgPerf.toFixed(2)+'%');
    setText('manDashQuality',avgQuality===null?'—':avgQuality.toFixed(2)+'%');
    setText('manDashReliability',avgRel===null?'—':avgRel.toFixed(2)+'%');
    setText('manDashDowntime',downtime.toFixed(2)+' jam');
    setText('manDashPerbaikan',String(failures));
    setText('manDashNormal',String(condition.normal));
    setText('manDashPerhatian',String(condition.perhatian));
    setText('manDashPerbaikanKondisi',String(condition.perbaikan));
    var worst=machines.slice().sort(function(a,b){return (num(getPerf(b.id).downtimeBulanIni)||0)-(num(getPerf(a.id).downtimeBulanIni)||0);})[0];
    setText('manDashWorst',worst?worst.name:'—');
    setText('manDashPeriod',periodLabel());
    var sorted=machines.slice().sort(function(a,b){return (num(getPerf(b.id).downtimeBulanIni)||0)-(num(getPerf(a.id).downtimeBulanIni)||0);});
    if(typeof mount==='function') mount('manDashDowntimeChart',buildBarChartSVG(sorted.map(function(m){return {label:m.name,value:num(getPerf(m.id).downtimeBulanIni)||0,color:statusMeta(m.kondisi).color};}),{unit:'j',height:220,shortLabel:true}));
    var wrap=document.getElementById('manDashRankList');
    if(wrap) wrap.innerHTML=sorted.slice(0,6).map(function(m,i){var p=getPerf(m.id);return '<div class="rank-row"><div class="rank-num'+(i<2?' top':'')+'">'+(i+1)+'</div><div class="rank-info"><div class="rn">'+escapeHtml(m.name)+'</div><div class="rs">OEE '+(p.oee==null?'—':p.oee+'%')+' · '+(p.perbaikanBulanIni||0)+' failure</div></div><div class="rank-val">'+(p.downtimeBulanIni||0)+' jam</div></div>';}).join('');
  };

  window.renderManPerformaTabs=function(){
    var wrap=document.getElementById('manPerformaTabs'); if(!wrap)return;
    var html='<button class="mtab'+(currentManPerformaId==='all'?' on':'')+'" data-mesin="all" onclick="switchManPerforma(\'all\', this)">Semua Mesin</button>';
    (MACHINES||[]).forEach(function(m){html+='<button class="mtab'+(currentManPerformaId===m.id?' on':'')+'" data-mesin="'+m.id+'" onclick="switchManPerforma(\''+m.id+'\', this)">'+escapeHtml(m.name)+'</button>';});
    wrap.innerHTML=html;
  };

  window.switchManPerforma=function(machineId){
    currentManPerformaId=machineId;
    document.querySelectorAll('#manPerformaTabs .mtab').forEach(function(x){x.classList.toggle('on',x.getAttribute('data-mesin')===machineId);});
    var machines=MACHINES||[];
    var items=machines.map(function(m){var p=getPerf(m.id);return {label:m.name,value:num(p.oee)||0,color:statusMeta(m.kondisi).color,opacity:(machineId==='all'||machineId===m.id)?0.9:0.18};});
    if(typeof mount==='function') mount('manPerformaChartMount',buildBarChartSVG(items,{unit:'%',height:210,shortLabel:true}));
    var tbody=document.getElementById('manPerformaTbody');
    if(tbody) tbody.innerHTML=machines.map(function(m){var p=getPerf(m.id);var cls=machineId!=='all'?(machineId===m.id?'row-focus':'row-dimmed'):'';return '<tr class="'+cls+'"><td><strong>'+escapeHtml(m.name)+'</strong></td><td class="mono">'+display(p.oee,'%')+'</td><td class="mono">'+display(p.availability,'%')+'</td><td class="mono">'+display(p.performance,'%')+'</td><td class="mono">'+display(p.quality,'%')+'</td><td class="mono">'+display(p.reliability,'%')+'</td><td class="mono">'+display(p.mttr,' jam')+'</td><td class="mono">'+display(p.mtbf,' jam')+'</td><td class="mono">'+display(p.downtimeBulanIni,' jam')+'</td></tr>';}).join('');
    setText('manPerformaSub','Periode data: '+periodLabel()+' — seluruh mesin');
  };

  window.renderKomponenRanking=function(){
    var wrap=document.getElementById('manKomponenRanking'); if(!wrap)return;
    var counts={};
    (MAINTENANCE_HISTORY||[]).forEach(function(h){var k=(h.kategori||'Tidak dikategorikan').trim()||'Tidak dikategorikan';counts[k]=(counts[k]||0)+1;});
    var arr=Object.keys(counts).map(function(k){return {label:k,count:counts[k]};}).sort(function(a,b){return b.count-a.count;}).slice(0,8);
    wrap.innerHTML=arr.length?arr.map(function(c,i){return '<div class="rank-row"><div class="rank-num'+(i===0?' top':'')+'">'+(i+1)+'</div><div class="rank-info"><div class="rn">'+escapeHtml(c.label)+'</div><div class="rs">Riwayat maintenance</div></div><div class="rank-val">'+c.count+'×</div></div>';}).join(''):'<div class="empty-state">Belum ada data maintenance.</div>';
  };

  function renderMaintenanceChart(){
    var mountEl=document.getElementById('manMaintenanceChart'); if(!mountEl)return;
    var ref=refDate(); var months=[]; for(var i=3;i>=0;i--){var d=new Date(ref.getFullYear(),ref.getMonth()-i,1);months.push(monthKey(d));}
    var counts={}; (MAINTENANCE_HISTORY||[]).forEach(function(h){if(!h.tanggal)return;var mk=h.tanggal.slice(0,7);if(months.indexOf(mk)<0)return;var cat=(h.kategori||'Lainnya').trim()||'Lainnya';counts[mk]=counts[mk]||{};counts[mk][cat]=(counts[mk][cat]||0)+1;});
    var cats={}; months.forEach(function(m){Object.keys(counts[m]||{}).forEach(function(c){cats[c]=true;});});
    var top=Object.keys(cats).sort(function(a,b){return months.reduce(function(s,m){return s+(counts[m]&&counts[m][b]||0);},0)-months.reduce(function(s,m){return s+(counts[m]&&counts[m][a]||0);},0);}).slice(0,3);
    if(!top.length){mountEl.innerHTML='<div class="empty-state">Belum ada data maintenance untuk periode ini.</div>';return;}
    var max=1;months.forEach(function(m){top.forEach(function(c){max=Math.max(max,(counts[m]&&counts[m][c])||0);});});
    var W=640,H=240,L=48,R=18,T=18,B=42,iw=W-L-R,ih=H-T-B,gap=18,groupW=iw/months.length,barW=Math.min(24,(groupW-gap)/top.length);var svg='<svg viewBox="0 0 '+W+' '+H+'" style="width:100%;height:auto;">';
    for(var s=0;s<=4;s++){var y=T+ih-(ih*s/4);svg+='<line x1="'+L+'" y1="'+y+'" x2="'+(W-R)+'" y2="'+y+'" stroke="var(--line-soft)" stroke-dasharray="3 3"/><text x="'+(L-8)+'" y="'+(y+4)+'" class="chart-value-label" text-anchor="end">'+Math.round(max*s/4)+'</text>';}
    var colors=['var(--amber)','var(--blue)','var(--purple)']; months.forEach(function(m,mi){var gx=L+mi*groupW+groupW/2-(top.length*barW+(top.length-1)*5)/2;top.forEach(function(c,ci){var v=(counts[m]&&counts[m][c])||0,h=ih*v/max,x=gx+ci*(barW+5),y=T+ih-h;svg+='<rect x="'+x.toFixed(1)+'" y="'+y.toFixed(1)+'" width="'+barW.toFixed(1)+'" height="'+Math.max(2,h).toFixed(1)+'" rx="2" fill="'+colors[ci]+'"/><text x="'+(x+barW/2).toFixed(1)+'" y="'+(y-5).toFixed(1)+'" class="chart-value-label" text-anchor="middle">'+v+'</text>';});svg+='<text x="'+(L+mi*groupW+groupW/2)+'" y="'+(H-14)+'" class="chart-axis-label" text-anchor="middle">'+escapeHtml(monthLabel(m))+'</text>';});svg+='</svg><div class="chart-legend">'+top.map(function(c,i){return '<div class="chart-legend-item"><span class="sw" style="background:'+colors[i]+';"></span>'+escapeHtml(c)+'</div>';}).join('')+'</div>';mountEl.innerHTML=svg;
  }

  function selectedMachine(){var el=document.getElementById('manReportMachine');return el?el.value:'all';}
  function reportRows(){var mk=document.getElementById('manReportPeriod');var period=mk?mk.value:'month';var ref=refDate();var start,end;
    if(period==='year'){start=new Date(ref.getFullYear(),0,1);end=new Date(ref.getFullYear(),11,31);} else if(period==='quarter'){var q=Math.floor(ref.getMonth()/3);start=new Date(ref.getFullYear(),q*3,1);end=new Date(ref.getFullYear(),q*3+3,0);} else {start=new Date(ref.getFullYear(),ref.getMonth(),1);end=new Date(ref.getFullYear(),ref.getMonth()+1,0);}
    var machine=selectedMachine();
    return (MACHINES||[]).filter(function(m){return machine==='all'||m.id===machine;}).map(function(m){var p=getPerf(m.id);return {machine:m.name,station:(getStation(m.stationId)||{}).name||'-',oee:p.oee,availability:p.availability,performance:p.performance,quality:p.quality,reliability:p.reliability,mttr:p.mttr,mtbf:p.mtbf,downtime:p.downtimeBulanIni,kondisi:m.kondisi||'-',periodStart:start.toISOString().slice(0,10),periodEnd:end.toISOString().slice(0,10)};});
  }
  function escCsv(v){return '"'+String(v===null||v===undefined?'':v).replace(/"/g,'""')+'"';}
  window.exportManagerExcel=function(){var rows=reportRows();var headers=['Mesin','Stasiun','OEE (%)','Availability (%)','Performance (%)','Quality (%)','Reliability (%)','MTTR (jam)','MTBF (jam)','Downtime (jam)','Kondisi','Periode Mulai','Periode Selesai'];var csv='\ufeff'+headers.map(escCsv).join(';')+'\n'+rows.map(function(r){return [r.machine,r.station,r.oee,r.availability,r.performance,r.quality,r.reliability,r.mttr,r.mtbf,r.downtime,r.kondisi,r.periodStart,r.periodEnd].map(escCsv).join(';');}).join('\n');var blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='laporan-manager-'+periodLabel().replace(/\s+/g,'-').toLowerCase()+'.csv';a.click();setTimeout(function(){URL.revokeObjectURL(a.href);},1000);};

  window.printManagerPdf=function(){var rows=reportRows();var html='<!doctype html><html><head><meta charset="utf-8"><title>Laporan Manager SIMPM</title><style>body{font-family:Arial,sans-serif;padding:28px;color:#111}h1{font-size:20px;margin:0 0 6px}p{color:#555}table{width:100%;border-collapse:collapse;margin-top:18px;font-size:11px}th,td{border:1px solid #bbb;padding:6px;text-align:left}th{background:#eee}.meta{margin:14px 0}.footer{margin-top:24px;font-size:10px;color:#666}@media print{button{display:none}}</style></head><body><h1>Laporan Performance Mesin — SIMPM PG Rendeng</h1><p>Periode data: '+escapeHtml(periodLabel())+'</p><div class="meta">Jumlah mesin: '+rows.length+'</div><table><thead><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Avail.</th><th>Perf.</th><th>Quality</th><th>Reliability</th><th>MTTR</th><th>MTBF</th><th>Downtime</th><th>Kondisi</th></tr></thead><tbody>'+rows.map(function(r){return '<tr><td>'+escapeHtml(r.machine)+'</td><td>'+escapeHtml(r.station)+'</td><td>'+display(r.oee,'%')+'</td><td>'+display(r.availability,'%')+'</td><td>'+display(r.performance,'%')+'</td><td>'+display(r.quality,'%')+'</td><td>'+display(r.reliability,'%')+'</td><td>'+display(r.mttr,' jam')+'</td><td>'+display(r.mtbf,' jam')+'</td><td>'+display(r.downtime,' jam')+'</td><td>'+escapeHtml(r.kondisi)+'</td></tr>';}).join('')+'</tbody></table><div class="footer">Generated from SIMPM database/calculator. Cetak dialog dapat digunakan untuk menyimpan sebagai PDF.</div><script>window.onload=function(){window.print();};<\/script></body></html>';var w=window.open('','_blank','width=1200,height=800');if(!w){alert('Popup diblokir browser. Izinkan popup untuk mencetak PDF.');return;}w.document.write(html);w.document.close();};

  window.renderManagerHardening=function(){
    try{renderManDashboard();renderManPerformaTabs();switchManPerforma(typeof currentManPerformaId==='undefined'?'all':currentManPerformaId);renderKomponenRanking();renderMaintenanceChart();setText('manReportPeriodLabel','Periode data: '+periodLabel());var mm=document.getElementById('manReportMachine');if(mm){mm.innerHTML='<option value="all">Semua Mesin</option>'+(MACHINES||[]).map(function(m){return '<option value="'+m.id+'">'+escapeHtml(m.name)+'</option>';}).join('');}}catch(e){console.error('Manager hardening:',e);}
  };

  document.addEventListener('DOMContentLoaded',function(){setTimeout(window.renderManagerHardening,0);});
})();
