      <section class="screen" id="scr-man-maintenance">
        <div class="panel chart-panel">
          <div class="panel-head">
            <div><h3>Jumlah Perbaikan per Kategori per Bulan</h3><div class="sub">Seluruh pabrik</div></div>
            </div>
          <div class="panel-body">
            <svg viewBox="0 0 600 220" style="width:100%;height:auto;">
              <line x1="40" y1="190" x2="580" y2="190" stroke="var(--line)"/>
              <line x1="40" y1="30" x2="580" y2="30" stroke="var(--line-soft)" stroke-dasharray="3 3"/>
              <line x1="40" y1="110" x2="580" y2="110" stroke="var(--line-soft)" stroke-dasharray="3 3"/>
              <text x="30" y="34" class="chart-value-label" text-anchor="end">12</text>
              <text x="30" y="114" class="chart-value-label" text-anchor="end">6</text>
              <text x="30" y="194" class="chart-value-label" text-anchor="end">0</text>
              <rect x="86" y="70" width="16" height="120" fill="var(--amber)"/>
              <rect x="104" y="137" width="16" height="53" fill="var(--blue)"/>
              <rect x="122" y="150" width="16" height="40" fill="var(--purple)"/>
              <rect x="226" y="110" width="16" height="80" fill="var(--amber)"/>
              <rect x="244" y="97" width="16" height="93" fill="var(--blue)"/>
              <rect x="262" y="163" width="16" height="27" fill="var(--purple)"/>
              <rect x="366" y="83" width="16" height="107" fill="var(--amber)"/>
              <rect x="384" y="123" width="16" height="67" fill="var(--blue)"/>
              <rect x="402" y="123" width="16" height="67" fill="var(--purple)"/>
              <rect x="506" y="123" width="16" height="67" fill="var(--amber)"/>
              <rect x="524" y="110" width="16" height="80" fill="var(--blue)"/>
              <rect x="542" y="137" width="16" height="53" fill="var(--purple)"/>
              <text x="112" y="205" class="chart-axis-label" text-anchor="middle">Mei</text>
              <text x="252" y="205" class="chart-axis-label" text-anchor="middle">Jun</text>
              <text x="392" y="205" class="chart-axis-label" text-anchor="middle">Jul</text>
              <text x="532" y="205" class="chart-axis-label" text-anchor="middle">Agu</text>
            </svg>
            <div class="chart-legend">
              <div class="chart-legend-item"><span class="sw" style="background:var(--amber);"></span>Mekanik</div>
              <div class="chart-legend-item"><span class="sw" style="background:var(--blue);"></span>Elektrik</div>
              <div class="chart-legend-item"><span class="sw" style="background:var(--purple);"></span>Instrumentasi</div>
            </div>
            <div class="formula-box">
              <strong>Formula:</strong> <code>Jumlah per Kategori = COUNT(Laporan Perbaikan) dikelompokkan per kategori &amp; bulan, seluruh pabrik</code>
              <span class="fnote">Terhubung langsung ke laporan yang dikirim seluruh Teknisi — bertambah otomatis tanpa perlu input manual.</span>
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Komponen Paling Sering Diganti</h3><div class="sub" style="margin-left:18px;">Diambil dari kolom "Pekerjaan" pada riwayat SIPPM</div></div>
          <div class="panel-body" style="padding:6px 18px 14px;" id="manKomponenRanking"></div>
        </div>
      </section>

      