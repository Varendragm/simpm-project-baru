      <section class="screen" id="scr-sup2-monitoring">
        <div class="filter-row">
          <select class="fsel" id="monStasiunSel" onchange="onMonitoringStationChange(this)"></select>
          <select class="fsel" id="monMesinSel" onchange="onMonitoringMachineChange(this)"></select>
        </div>
        <div class="panel chart-panel">
          <div class="panel-head">
            <div><h3>OEE per Mesin</h3><div class="sub">Mengikuti filter stasiun/mesin di atas — grafik menyesuaikan otomatis jumlah mesin</div></div>
            </div>
          <div class="panel-body"><div class="chart-mount" id="monitoringChartMount"></div></div>
        </div>
        <div class="panel">
          <div class="panel-head"><h3>Performa Mesin</h3></div>
          <div class="panel-body" style="padding:0;">
            <div class="table-scroll">
            <table>
              <thead><tr><th>Mesin</th><th>Status</th><th>Availability</th><th>OEE</th><th>Downtime Bulan Ini</th><th>Jumlah Perbaikan</th><th></th></tr></thead>
              <tbody id="monitoringTbody"></tbody>
            </table>
            </div>
          </div>
        </div>
      </section>

      <?php /**PATH C:\xampp\htdocs\simpm-pgrendeng\resources\views/partials/screens/sup-monitoring.blade.php ENDPATH**/ ?>