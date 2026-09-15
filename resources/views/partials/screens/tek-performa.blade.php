      <section class="screen" id="scr-tek2-performa">
        <div class="grid-stats">
          <div class="stat-card blue"><div class="stat-num">2.4<span class="unit">jam</span></div><div class="stat-label">Rata-rata Waktu Perbaikan Saya</div></div>
          <div class="stat-card green"><div class="stat-num">12</div><div class="stat-label">Perbaikan Diselesaikan (3 Bulan)</div></div>
          <div class="stat-card amber"><div class="stat-num">2</div><div class="stat-label">Mesin Sering Ditangani</div></div>
        </div>
        <div class="panel chart-panel">
          <div class="panel-head">
            <div><h3>Waktu Penyelesaian Perbaikan per Minggu</h3><div class="sub">Dihitung dari selisih waktu mulai &amp; selesai pada form hasil penanganan</div></div>
            </div>
          <div class="panel-body">
            <svg viewBox="0 0 600 200" style="width:100%;height:auto;">
              <line x1="40" y1="170" x2="580" y2="170" stroke="var(--line)"/>
              <line x1="40" y1="10" x2="580" y2="10" stroke="var(--line-soft)" stroke-dasharray="3 3"/>
              <line x1="40" y1="90" x2="580" y2="90" stroke="var(--line-soft)" stroke-dasharray="3 3"/>
              <text x="30" y="14" class="chart-value-label" text-anchor="end">4j</text>
              <text x="30" y="94" class="chart-value-label" text-anchor="end">2j</text>
              <text x="30" y="174" class="chart-value-label" text-anchor="end">0j</text>
              <polyline points="40,130 148,110 256,140 364,90 472,100 580,80" fill="none" stroke="var(--green)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
              <g fill="var(--green)">
                <circle cx="40" cy="130" r="4"/><circle cx="148" cy="110" r="4"/><circle cx="256" cy="140" r="4"/>
                <circle cx="364" cy="90" r="4"/><circle cx="472" cy="100" r="4"/><circle cx="580" cy="80" r="4"/>
              </g>
              <text x="40" y="188" class="chart-axis-label" text-anchor="middle">Mgg 1</text>
              <text x="148" y="188" class="chart-axis-label" text-anchor="middle">Mgg 2</text>
              <text x="256" y="188" class="chart-axis-label" text-anchor="middle">Mgg 3</text>
              <text x="364" y="188" class="chart-axis-label" text-anchor="middle">Mgg 4</text>
              <text x="472" y="188" class="chart-axis-label" text-anchor="middle">Mgg 5</text>
              <text x="580" y="188" class="chart-axis-label" text-anchor="middle">Mgg 6</text>
            </svg>
            <div class="chart-legend"><div class="chart-legend-item"><span class="ln" style="background:var(--green);"></span>Waktu rata-rata penyelesaian perbaikan (jam) — tren menurun berarti makin cepat</div></div>
            <div class="formula-box">
              <strong>Formula:</strong> <code>Waktu Penyelesaian = Waktu Selesai − Waktu Mulai (per laporan, dirata-ratakan per minggu)</code>
              <span class="fnote">Nilai mingguan diperbarui otomatis dari kolom waktu mulai/selesai pada setiap laporan hasil penanganan yang Anda kirim.</span>
            </div>
          </div>
        </div>
      </section>

      