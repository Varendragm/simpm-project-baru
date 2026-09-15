# SIMPM · PG Rendeng — Sistem Monitoring Performa Mesin & Maintenance

Implementasi Laravel dari mockup `mockup-simpm-pgrendeng-v19.html`. Seluruh CSS, markup,
navigasi SPA, chart SVG, modal, filter, dan animasi transisi (splash → login → welcome →
app shell) diambil **persis** dari mockup — tidak ada desain baru. Yang diubah hanyalah
sumber datanya: dari array JS statis menjadi database sungguhan lewat Eloquent/API Laravel.

## Arsitektur singkat

- **Splash & Login** (`/login`) — halaman penuh terpisah (`resources/views/auth/login.blade.php`),
  animasi ditangani `public/js/login.js`, otentikasi sungguhan ke `POST /login`.
- **App shell** (`/app`, wajib login) — satu halaman berisi sidebar + topbar + 22 layar
  (dashboard/monitoring/jadwal/validasi/riwayat/laporan/profil × 3 role), persis seperti
  mockup yang berbasis SPA. Navigasi antar layar tetap client-side (`go()`), tetapi
  **datanya diambil dari database** lewat `window.__SIMPM_BOOTSTRAP__` + `GET /api/bootstrap`.
- Perubahan data (tambah stasiun/mesin, buat jadwal PM, kirim laporan pemeriksaan,
  validasi supervisor, ganti password) dikirim ke endpoint API sungguhan dan disimpan ke DB.

```
app/Http/Controllers/          AuthController, AppController, Api/* (Bootstrap, Station,
                                Machine, PmSchedule, Profile)
app/Http/Controllers/Concerns  BuildsBootstrapData (dipakai bersama AppController & API)
app/Models/                    User, Station, Machine, MachinePerformance, PmSchedule,
                                ValidationHistory, MaintenanceHistory
database/migrations/           skema tabel (id string untuk stations/machines/pm_schedules
                                mengikuti pola id mockup, cth. "st-gilingan", "m-g01", "pm-002")
database/seeders/SimpmSeeder   data dummy IDENTIK dengan mockup (3 stasiun, 9 mesin, 7 jadwal
                                PM, riwayat validasi & maintenance, 3 akun demo)
resources/views/partials/      layout (app-shell, modal-master, splash, login-panel, welcome,
                                logout-splash) + 22 partial screens/*.blade.php (isi persis mockup)
public/css/app.css             CSS mockup, tidak diubah
public/js/app.js               JS mockup, hanya bagian sumber data & mutasi yang diadaptasi
public/js/login.js             logic splash/login/welcome + panggilan auth sungguhan
routes/web.php                 halaman + API (session + CSRF, bukan token-based)
```

## Cara menjalankan

Butuh **PHP ≥ 8.2** dan **Composer** terpasang di komputer Anda (project ini dibuat di
lingkungan tanpa PHP sehingga belum sempat dijalankan/diuji langsung di sini — ikuti langkah
di bawah, dan jika ada error kecil saat instalasi pertama, umumnya cukup dari daftar
Troubleshooting di bagian bawah).

```bash
cd simpm-pgrendeng

# 1) Install dependency Laravel
composer install

# 2) File .env & APP_KEY sudah disediakan (siap pakai, DB default = SQLite).
#    Jika ingin generate ulang key:
php artisan key:generate

# 3) Buat skema database + isi data dummy
php artisan migrate --seed

# 4) Jalankan server
php artisan serve
```

Buka **http://127.0.0.1:8000** di browser.

### Akun demo (password sama untuk semua: `password`)

| Role       | Username         |
|------------|------------------|
| Supervisor | sri.supervisor   |
| Teknisi    | budi.teknisi     |
| Manajer    | wahyu.manajer    |

Pilih tab peran di halaman login untuk auto-isi username, lalu masukkan password `password`.

## Menjalankan test

```bash
php artisan test
```

Mencakup: alur login/logout, akses `/app` untuk guest vs user login, bentuk response
`GET /api/bootstrap`, pembuatan stasiun/mesin, alur penuh jadwal PM → laporan teknisi →
validasi supervisor (termasuk cek riwayat validasi & maintenance otomatis terbentuk),
serta ganti password.

## Database

Default memakai **SQLite** (`database/database.sqlite`, sudah dibuat kosong) — tidak perlu
setup server DB terpisah. Jika ingin MySQL, edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simpm_pgrendeng
DB_USERNAME=root
DB_PASSWORD=
```

lalu buat database `simpm_pgrendeng` dan jalankan ulang `php artisan migrate --seed`.

## Catatan implementasi penting

1. **Tidak ada desain baru.** Semua file di `resources/views/partials/**/*.blade.php`
   adalah hasil pemecahan otomatis dari mockup asli (per komentar section), bukan ditulis
   ulang manual — supaya markup, class, id, dan atribut `onclick` persis sama.
2. **CSS (`public/css/app.css`) disalin apa adanya**, termasuk logo base64 dan CSS variables,
   sehingga warna, spacing, border, badge, dan responsive breakpoint identik dengan mockup.
3. **Chart OEE/trend/bar** tetap dibangun sebagai SVG oleh JavaScript (`buildBarChartSVG`,
   `buildLineChartSVG`, dst.) persis seperti mockup — hanya datanya sekarang berasal dari
   `MACHINE_PERFORMANCE` yang diisi dari tabel `machine_performances`.
4. **Role switch di sidebar** (pil Supervisor/Teknisi/Manajer) tetap berfungsi sebagai fitur
   pratinjau lintas peran seperti pada mockup asli — ini bukan re-autentikasi, murni
   client-side, konsisten dengan perilaku mockup.
5. **Scrolling & responsive**: karena CSS/markup tidak diubah, perilaku scroll vertikal
   halaman, scroll sidebar saat menu melebihi tinggi layar, scroll horizontal tabel, dan
   breakpoint mobile mengikuti mockup 1:1.
6. **Tanggal "hari ini" demo** dikunci ke `2026-08-18` (sama seperti `DEMO_TODAY` di mockup)
   supaya status jadwal PM/badge/urutan data awal terlihat sama persis saat pertama dijalankan.
   Ini dipakai baik di `public/js/app.js` maupun `App\Http\Controllers\Api\PmScheduleController::DEMO_TODAY`.

## Troubleshooting

- **`could not find driver` (SQLite)** — aktifkan ekstensi `pdo_sqlite` di `php.ini`, atau
  ganti ke MySQL seperti petunjuk di atas.
- **`Class "PDO" not found` / migrate gagal** — pastikan ekstensi `pdo_mysql`/`pdo_sqlite`
  PHP sudah aktif.
- **Halaman putih / 500 setelah composer install** — jalankan `php artisan config:clear`
  lalu pastikan folder `storage/` dan `bootstrap/cache/` bisa ditulis (`chmod -R 775`).
- **Login selalu gagal** — pastikan sudah menjalankan `php artisan migrate --seed` (akun
  demo dibuat oleh seeder, bukan otomatis ada di database kosong).
