<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Laporan Performance Mesin</title>
<style>
@page{size:A4 landscape;margin:10mm}
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;color:#182230;font-size:9px;margin:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.header{border-bottom:3px solid #0b7285;padding-bottom:8px;margin-bottom:10px}
.brand{font-size:12px;font-weight:800;letter-spacing:1px;color:#0b7285}
h1{font-size:19px;margin:4px 0}
.meta{font-size:9px;color:#667085}
.notice{margin:9px 0;padding:8px 10px;border:1px solid #e7c84a;background:#fff9db;color:#7a5b00;border-radius:5px}
.cards{display:grid;grid-template-columns:repeat(6,1fr);gap:6px;margin:10px 0}
.card{border:1px solid #d9e0e7;border-radius:5px;background:#f8fafc;padding:7px 8px}
.label{font-size:7px;color:#667085;text-transform:uppercase}.value{font-size:12px;font-weight:800;margin-top:3px}
h2{font-size:11px;margin:10px 0 5px}
table{width:100%;border-collapse:collapse;table-layout:fixed}
.machine{width:13%}.station{width:14%}.metric{width:7%}.condition{width:10%}
th{background:#0b7285;color:#fff;padding:6px 4px;border:1px solid #07566a;font-size:7px;text-align:center;line-height:1.15}
td{border:1px solid #dfe4ea;padding:5px 4px;font-size:7.5px;line-height:1.2;vertical-align:middle;word-break:break-word}
td.num{text-align:right;white-space:nowrap}td.name{text-align:left}td.cond{text-align:center;font-weight:700}
.footer{margin-top:9px;border-top:1px solid #ddd;padding-top:6px;color:#667085;font-size:7px}
@media print{thead{display:table-header-group}tr{page-break-inside:avoid}}
</style>
</head>
<body>
<div class="header">
  <div class="brand">SIMPM · PG RENDENG</div>
  <h1>Laporan Performance Mesin — {{ $period === 'year' ? 'Tahunan' : ($period === 'quarter' ? 'Kuartalan' : 'Bulanan') }}</h1>
  <div class="meta">Periode {{ \Carbon\Carbon::parse($periodStart)->locale('id')->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($periodEnd)->locale('id')->translatedFormat('d M Y') }} · {{ count($rows) }} mesin</div>
</div>

@if(!$hasProductionData)
<div class="notice"><strong>Data produksi belum tersedia untuk periode ini.</strong> Nilai performa tidak dibuat menjadi 0 karena belum ada observasi yang dapat dihitung.</div>
@endif

@php
$avg=function($key) use ($rows){
  $values=array_values(array_filter(array_map(fn($r)=>is_numeric($r[$key] ?? null)?(float)$r[$key]:null,$rows),fn($v)=>$v!==null));
  return count($values)?array_sum($values)/count($values):null;
};
$fmt=function($v,$unit=''){return is_numeric($v)?number_format((float)$v,1,',','.').$unit:'—';};
@endphp

<div class="cards">
@foreach([['OEE','oee','%'],['Availability','availability','%'],['Performance','performance','%'],['Quality','quality','%'],['MTTR','mttr',' jam'],['MTBF','mtbf',' jam']] as $c)
<div class="card"><div class="label">{{ $c[0] }}</div><div class="value">{{ $fmt($avg($c[1]),$avg($c[1])!==null?$c[2]:'') }}</div></div>
@endforeach
</div>

<h2>Performance Mesin</h2>
<table>
<colgroup><col class="machine"><col class="station"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="metric"><col class="condition"></colgroup>
<thead><tr><th>Mesin</th><th>Stasiun</th><th>OEE</th><th>Availability</th><th>Performance</th><th>Quality</th><th>Reliability</th><th>MTTR</th><th>MTBF</th><th>Downtime</th><th>Kondisi</th></tr></thead>
<tbody>
@forelse($rows as $row)
<tr>
<td class="name">{{ $row['machine'] }}</td><td class="name">{{ $row['station'] }}</td>
<td class="num">{{ $fmt($row['oee'] ?? null,'%') }}</td><td class="num">{{ $fmt($row['availability'] ?? null,'%') }}</td><td class="num">{{ $fmt($row['performance'] ?? null,'%') }}</td><td class="num">{{ $fmt($row['quality'] ?? null,'%') }}</td><td class="num">{{ $fmt($row['reliability'] ?? null,'%') }}</td>
<td class="num">{{ $fmt($row['mttr'] ?? null) }}</td><td class="num">{{ $fmt($row['mtbf'] ?? null) }}</td><td class="num">{{ $fmt($row['downtime'] ?? null,' jam') }}</td><td class="cond">{{ $row['kondisi'] ?? 'Belum ada data' }}</td>
</tr>
@empty
<tr><td colspan="11" style="text-align:center;padding:15px">Tidak ada data mesin untuk periode yang dipilih.</td></tr>
@endforelse
</tbody>
</table>

<div class="footer">Sumber: database SIMPM dan PerformanceCalculator · dibuat {{ now()->format('d/m/Y H:i') }}</div>
<script>window.addEventListener('load',function(){setTimeout(function(){window.print()},250)});</script>
</body>
</html>
