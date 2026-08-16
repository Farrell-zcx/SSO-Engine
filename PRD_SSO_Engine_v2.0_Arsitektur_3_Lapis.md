# PRD SSO Engine v2.0
# Arsitektur Provisioning & Otorisasi Multi-Aplikasi — Hasil Konsultasi
### Aplikasi Internal PT Desnet

| Field | Keterangan |
|---|---|
| Dokumen induk | PRD SSO Engine v1.1 (5 Agustus 2026) & Addendum v1.2 (12 Agustus 2026) |
| Versi dokumen ini | 2.0 — revisi arsitektur |
| Tanggal | 12 Agustus 2026 |
| Disusun oleh | Farrel (Intern, Divisi Aplikasi) |
| Dikonsultasikan & dikonfirmasi oleh | Ade Kurniawan (Deputy Manager Aplikasi), via chat & telepon 12 Agustus 2026 |
| Status | Perubahan arsitektur resmi — menggantikan sebagian model provisioning di v1.1/v1.2 |

---

## Changelog v2.0

Dokumen ini **mengubah arsitektur otorisasi** yang sebelumnya dirancang di v1.1 dan addendum v1.2, berdasarkan hasil konsultasi langsung dengan Pak Ade. Perubahan utama:

- **Model otorisasi berubah dari 2 lapis menjadi 3 lapis.** Sebelumnya SSO Engine hanya mengurus identitas, dan Client App sepenuhnya mengurus akses+role. Sekarang SSO Engine mendapat tanggung jawab tambahan: menentukan **aplikasi mana saja yang boleh diakses** seorang user, sebelum masuk ke urusan role di Client App.
- **Cara eksekusi berubah dari CLI menjadi Dashboard Admin berbasis web ("Kelola Pengguna").** Setiap aplikasi (SSO Engine, Inventory, MyMember) memiliki fitur Kelola Pengguna sendiri-sendiri, bukan command line.
- **Tabel baru `user_application_access`** ditambahkan di SSO Engine untuk menyimpan mapping "user X diizinkan akses aplikasi Y".
- **Tabel baru `sso_admins`** ditambahkan (sebelumnya di addendum v1.2 masuk kategori "ditunda ke Sprint 2" — sekarang menjadi kebutuhan wajib karena dashboard butuh pembatasan siapa yang boleh mengaksesnya).
- **Flow `/authorize` di SSO Engine berubah**, menambahkan pengecekan akses aplikasi sebelum redirect ke Client App.
- **`pending_role_assignments`** (diperkenalkan di addendum v1.2) tetap dipakai, tapi kini menjadi lapis kedua (role), bukan lapis pertama, dan diakses lewat dashboard "Kelola Pengguna" masing-masing aplikasi — bukan lagi lewat `artisan` command.
- Mekanisme aktivasi/reset password via email link (addendum v1.2, Bagian 6) **tidak berubah** dan tetap dipakai apa adanya.

---

## 1. Latar Belakang

PRD v1.1 dan addendum v1.2 dirancang dengan asumsi SSO Engine murni "penerbit identitas" — tidak ikut campur soal otorisasi/akses, dan seluruh keputusan role diserahkan ke masing-masing Client App lewat CLI (`sso:create-user`, `sso:assign-role`).

Saat dikonsultasikan ke Pak Ade (12 Agustus 2026) soal cara eksekusi provisioning karyawan baru — apakah tetap CLI atau dibuatkan dashboard — muncul arahan yang mengubah lebih dari sekadar cara eksekusi:

> "setiap aplikasi tetap punya kelola pengguna dengan struktur masing-masing, tp ketika dia mau aktifkan sso nanti dibantu oleh admin sso, dan admin sso bisa maping dia diijinkan akses ke aplikasi apa saja" — Ade Kurniawan, dikonfirmasi "betul" pada follow-up tertulis.

Arahan ini menambahkan satu lapis otorisasi yang sebelumnya tidak ada di desain manapun: **kontrol akses aplikasi di level SSO**, terpisah dari **kontrol role di level masing-masing aplikasi**. Dokumen ini merancang ulang arsitektur berdasarkan arahan tersebut secara lengkap.

---

## 2. Model Otorisasi: 3 Lapis

Ini adalah perubahan paling mendasar dari seluruh dokumen sebelumnya. Otorisasi kini dibagi menjadi tiga lapis independen, masing-masing dikelola pihak berbeda:

| Lapis | Pertanyaan yang dijawab | Dikelola di | Dikelola oleh |
|---|---|---|---|
| **1. Identitas** | "Siapa orang ini?" | SSO Engine — tabel `users` | Admin SSO |
| **2. Akses Aplikasi** | "Aplikasi apa saja yang boleh dia buka?" | SSO Engine — tabel `user_application_access` | Admin SSO |
| **3. Role/Otorisasi Internal** | "Begitu masuk aplikasi X, dia jadi apa?" | Masing-masing Client App — tabel `pending_role_assignments` / `users` lokal | Admin masing-masing aplikasi |

### Analogi

Bayangkan gedung kantor bertingkat, dengan Inventory di lantai 2 dan MyMember di lantai 3:

- **Resepsionis lobby (Admin SSO)** menerbitkan kartu akses karyawan baru (Lapis 1), dan memprogram kartu itu agar bisa membuka pintu **lantai mana saja** yang diizinkan — misalnya hanya lantai 2, atau lantai 2 dan 3 sekaligus (Lapis 2).
- **Kepala tiap lantai (Admin Inventory / Admin MyMember)** menentukan, begitu karyawan itu masuk ke lantainya, dia duduk di meja/jabatan apa (Lapis 3).

Karyawan tidak bisa membuka pintu lantai manapun hanya bermodal kartu akses (Lapis 1) — kartunya harus diprogram dulu oleh resepsionis untuk lantai tersebut (Lapis 2). Dan begitu masuk lantai, dia belum otomatis punya meja kerja sampai kepala lantai menentukannya (Lapis 3).

### Mengapa Ini Tetap Konsisten dengan Prinsip Awal

Prinsip inti dari PRD v1.1 — "SSO tidak mengurus hak akses ke fitur internal aplikasi" — **tetap dipegang**. Yang berubah bukan itu, melainkan ditambahkannya kewenangan baru yang sifatnya lebih mendasar dari role: bukan "hak akses ke fitur", tapi "hak untuk membuka aplikasi itu sama sekali". SSO Engine tetap tidak tahu dan tidak mengurus apa itu "staff_gudang" atau "approver" — itu murni domain Inventory.

---

## 3. Skema Database

### 3.1 SSO Engine — Tabel Baru: `user_application_access`

Menyimpan mapping user ke aplikasi yang diizinkan.

```sql
CREATE TABLE user_application_access (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(36) NOT NULL,          -- FK ke users.id
    application_id VARCHAR(36) NOT NULL,   -- FK ke applications.id
    granted_by INT NOT NULL,               -- FK ke sso_admins, siapa yang kasih akses
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,              -- diisi kalau akses dicabut, tanpa hapus baris (audit trail)
    UNIQUE (user_id, application_id)
);
```

`revoked_at` sengaja dipakai alih-alih hapus baris langsung — supaya ada jejak kalau suatu saat akses seseorang pernah dicabut, bukan sekadar hilang tanpa keterangan.

### 3.2 SSO Engine — Tabel Baru: `sso_admins`

Menyimpan siapa saja yang berwenang mengoperasikan dashboard "Kelola Pengguna" di SSO Engine (Lapis 1 & 2). Tabel ini sebelumnya dicatat sebagai item yang ditunda di addendum v1.2, tapi sekarang jadi kebutuhan wajib karena dashboard perlu pembatasan akses yang jelas — tanpa ini, siapa pun yang berhasil login ke SSO bisa saja mengakses dashboard admin.

```sql
CREATE TABLE sso_admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(36) NOT NULL UNIQUE,   -- FK ke users.id — admin tetap 1 identitas SSO yang sama
    granted_by INT NULL,                   -- siapa yang mengangkat jadi admin (NULL untuk admin pertama/seed awal)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3.3 Client App (Inventory) — Tabel yang Sudah Ada, Tetap Dipakai: `pending_role_assignments`

Tidak berubah secara struktur dari addendum v1.2, hanya berubah **cara mengisinya** — dari `artisan` command menjadi form di dashboard "Kelola Pengguna" milik Inventory.

```sql
CREATE TABLE pending_role_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    role VARCHAR(50) NOT NULL,
    assigned_by INT NOT NULL,
    status ENUM('pending', 'used', 'expired') DEFAULT 'pending',
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3.4 SSO Engine — Tabel yang Sudah Ada, Tidak Berubah

`users`, `applications`, `refresh_tokens`, `password_resets` — seluruhnya tetap sesuai definisi di PRD v1.1 dan addendum v1.2.

### 3.5 Diagram Relasi (Ringkas)

```
SSO Engine                                   Client App (Inventory)
┌───────────────┐                            ┌───────────────────────────┐
│ users          │                           │ users (lokal, hasil JIT)   │
│  id            │◄──┐                       │  id                        │
│  email         │   │                       │  email                     │
│  password_hash │   │                       │  role                      │
└───────────────┘    │                       └───────────────────────────┘
                      │                                    ▲
┌───────────────────────┐                                  │
│ user_application_access│                                 │
│  user_id (FK users)    │                                 │
│  application_id (FK)   │──┐                               │
│  granted_by            │  │                               │
└───────────────────────┘  │                                │
                            │                    ┌───────────────────────────┐
┌───────────────┐          │                     │ pending_role_assignments   │
│ applications   │◄─────────┘                     │  email                     │
│  id            │                                │  role                      │
│  client_id     │                                │  status                    │
└───────────────┘                                └───────────────────────────┘

┌───────────────┐
│ sso_admins     │   (siapa yang boleh operasikan dashboard di atas)
│  user_id (FK)  │
└───────────────┘
```

---

## 4. Dashboard "Kelola Pengguna" — Rincian per Aplikasi

### 4.1 SSO Engine — Dashboard Kelola Pengguna (Lapis 1 & 2)

Diakses hanya oleh user yang terdaftar di `sso_admins`. Fitur:

| Fitur | Deskripsi |
|---|---|
| Tambah pengguna baru | Form input email (validasi domain `@desnet.co.id`) + username. Menggantikan `php spark sso:create-user`. Setelah submit, sistem otomatis mengirim email aktivasi (mekanisme sama seperti addendum v1.2 Bagian 6). |
| Lihat daftar pengguna | Tabel seluruh user SSO — email, status (aktif/belum aktivasi password), tanggal dibuat. |
| Kelola akses aplikasi per pengguna | Untuk tiap user, admin bisa mencentang/mencabut aplikasi mana saja yang diizinkan (Inventory, MyMember, dst) — menulis ke `user_application_access`. |
| Cabut akses aplikasi | Set `revoked_at` pada baris terkait, tanpa menghapus data (audit trail). |
| Kelola admin SSO lain | Opsional, untuk menambah/mencabut siapa saja yang punya akses ke dashboard ini sendiri (menulis ke `sso_admins`). |

### 4.2 Inventory — Dashboard Kelola Pengguna (Lapis 3)

Diakses oleh user dengan role `admin` di Inventory (role ini sendiri berasal dari hasil Lapis 3 sebelumnya). Fitur:

| Fitur | Deskripsi |
|---|---|
| Tentukan role untuk email tertentu | Form input email + pilih role (`admin` / `staff`). Menggantikan `php artisan sso:assign-role`. Menulis ke `pending_role_assignments`, sama seperti sebelumnya. |
| Lihat daftar pengguna Inventory | Tabel user yang sudah pernah login (dari tabel `users` lokal Inventory) beserta role masing-masing. |
| Ubah role pengguna existing | Untuk user yang sudah pernah login, admin bisa mengubah role-nya langsung di tabel `users` lokal (bukan lewat `pending_role_assignments` lagi, karena user itu sudah aktif). |

**Prasyarat penting:** Form ini hanya bermakna jika email yang dimasukkan **sudah diberi akses ke aplikasi Inventory** oleh Admin SSO (Lapis 2). Jika belum, admin Inventory tetap bisa mengisi form ini (menyiapkan role di depan), tapi karyawan tetap tidak akan bisa login ke Inventory sampai Admin SSO memberikan akses di Lapis 2. Kedua lapis ini independen — urutan pengisiannya boleh dalam urutan apa pun.

### 4.3 MyMember — Dashboard Kelola Pengguna (Lapis 3, Disederhanakan)

Karena MyMember hanya memiliki 1 role (`admin`), dashboard ini tidak memerlukan pemilihan role — cukup menampilkan daftar siapa saja yang sudah diberi akses ke MyMember (dari Lapis 2) dan siapa yang sudah aktif login. Tidak ada tabel `pending_role_assignments` yang diperlukan di sisi MyMember, konsisten dengan keputusan di addendum v1.2.

---

## 5. Perubahan Flow `/authorize` di SSO Engine

Ini adalah perubahan teknis paling penting di endpoint yang sudah diuji 38 skenario Postman — perlu diuji ulang khusus pada bagian yang berubah.

### 5.1 Flow Sebelumnya (PRD v1.1)

```
1. User login ke SSO Engine (atau sudah punya sesi aktif)
2. Client App redirect ke /authorize?client_id=inventory-app&...
3. SSO Engine cek client_id valid & redirect_uri cocok
4. SSO Engine generate authorization code, redirect balik ke Client App
```

### 5.2 Flow Baru (v2.0) — Tambahan Pengecekan Akses Aplikasi

```
1. User login ke SSO Engine (atau sudah punya sesi aktif)
2. Client App redirect ke /authorize?client_id=inventory-app&...
3. SSO Engine cek client_id valid & redirect_uri cocok           (sama seperti sebelumnya)
4. [BARU] SSO Engine cek tabel user_application_access:
     apakah ada baris untuk (user_id ini, application_id dari client_id ini)
     dengan revoked_at IS NULL?
     - Ada  -> lanjut ke langkah 5
     - Tidak ada -> tolak di sini juga (redirect ke halaman error
       "Anda belum memiliki akses ke aplikasi ini, hubungi admin SSO")
5. SSO Engine generate authorization code, redirect balik ke Client App
```

**Poin penting:** penolakan akses aplikasi terjadi **di SSO Engine, sebelum authorization code pernah diterbitkan** — bukan di Client App setelah menerima token. Ini membuat penolakan terjadi lebih awal dan lebih tegas dibanding model `pending_role_assignments` semata (yang baru menolak di sisi Client App, setelah token sudah diterbitkan SSO).

### 5.3 Dampak ke JIT Provisioning di Client App

Karena penyaringan akses aplikasi sudah terjadi di SSO Engine (langkah 4 di atas), Client App **tidak perlu lagi khawatir menerima token dari user yang sama sekali tidak berhak** — token yang sampai ke Client App otomatis berarti user tersebut sudah lolos Lapis 2. JIT Provisioning di Client App tetap menjalankan pengecekan Lapis 3 seperti pada addendum v1.2 (cek `pending_role_assignments`), tidak berubah.

Dengan kata lain, sekarang ada 2 gerbang berlapis:

```
SSO Engine (/authorize)          Client App (JIT Provisioning)
   Gerbang 1: boleh akses            Gerbang 2: role apa
   aplikasi ini?                     di aplikasi ini?
   [cek user_application_access]     [cek pending_role_assignments]
        |                                  |
   tidak lolos -> ditolak di SSO      tidak lolos -> ditolak di Client App,
   (token tidak pernah terbit)        (token ada, tapi tidak dibuatkan user lokal)
```

---

## 6. Flow End-to-End Onboarding Karyawan Baru (Revisi Final)

1. **Admin SSO** membuka dashboard Kelola Pengguna di SSO Engine, klik "Tambah Pengguna", isi email + username karyawan baru.
   - Sistem membuat baris di `users`, mengirim email aktivasi (link 10 menit, mekanisme addendum v1.2 Bagian 6).
2. **Admin SSO**, di halaman yang sama atau terpisah, mencentang aplikasi mana saja yang boleh diakses karyawan ini (misal: Inventory saja, atau Inventory + MyMember).
   - Sistem menulis baris ke `user_application_access` untuk tiap aplikasi yang dicentang.
3. **Admin Inventory** (independen, tidak harus menunggu langkah 1–2 selesai) membuka dashboard Kelola Pengguna Inventory, isi email karyawan + pilih role (`admin`/`staff`).
   - Sistem menulis ke `pending_role_assignments` milik Inventory.
4. **Karyawan** membuka email aktivasi, klik link, menyetel password sendiri.
5. **Karyawan** mengakses Inventory → diarahkan ke SSO Engine → sudah login (karena baru saja set password) atau login manual.
6. SSO Engine menjalankan **Gerbang 1**: cek `user_application_access` untuk (karyawan ini, Inventory). Jika langkah 2 sudah dilakukan → lolos, authorization code diterbitkan.
7. Inventory menerima token, menjalankan **Gerbang 2**: cek `pending_role_assignments`. Jika langkah 3 sudah dilakukan → user lokal dibuat dengan role sesuai, status `used`.
8. Karyawan berhasil masuk Inventory dengan role yang benar.

**Jika langkah 2 atau 3 belum dilakukan saat karyawan mencoba login:** ditolak pada gerbang yang bersangkutan (deny by default, konsisten dengan prinsip di addendum v1.2). Karyawan diarahkan menghubungi admin terkait — SSO atau Inventory, tergantung gerbang mana yang menolaknya, sehingga pesan error sebaiknya dibedakan supaya admin tahu langkah mana yang terlewat.

---

## 7. API Contract Tambahan

### 7.1 Dashboard SSO Engine (dilindungi middleware `sso_admin`)

| Method & Path | Fungsi |
|---|---|
| `POST /admin/users` | Membuat user baru + trigger email aktivasi (setara `sso:create-user`) |
| `GET /admin/users` | Daftar seluruh user SSO beserta status aktivasi |
| `POST /admin/users/{id}/access` | Memberi akses ke aplikasi tertentu — body `{ "application_id": "..." }`, menulis ke `user_application_access` |
| `DELETE /admin/users/{id}/access/{application_id}` | Mencabut akses (set `revoked_at`, bukan hapus baris) |
| `GET /admin/users/{id}/access` | Daftar aplikasi yang diizinkan untuk user tertentu |

Seluruh endpoint di atas wajib melalui middleware yang memverifikasi bahwa `user_id` pemanggil terdaftar di `sso_admins` — bukan sekadar user SSO biasa yang sudah login.

### 7.2 Perubahan pada `/authorize`

Tidak ada perubahan bentuk request/response dari sisi Client App. Perubahan murni internal (penambahan langkah pengecekan di Bagian 5.2). Response error baru ditambahkan untuk kasus akses ditolak di Gerbang 1:

```json
{
  "error": "application_access_denied",
  "message": "Anda belum memiliki akses ke aplikasi ini. Hubungi admin SSO."
}
```

### 7.3 Dashboard Inventory (dilindungi middleware role `admin` lokal Inventory)

| Method & Path | Fungsi |
|---|---|
| `POST /admin/kelola-pengguna` | Menentukan role untuk email tertentu (setara `sso:assign-role`), menulis ke `pending_role_assignments` |
| `GET /admin/kelola-pengguna` | Daftar user Inventory beserta role masing-masing |
| `PATCH /admin/kelola-pengguna/{id}` | Mengubah role user yang sudah aktif |

---

## 8. Kebutuhan Keamanan Tambahan

| Kebutuhan | Alasan |
|---|---|
| Middleware `sso_admin` di seluruh endpoint dashboard SSO Engine | Tanpa ini, sembarang user SSO yang berhasil login bisa mengakses dashboard admin dan memberi dirinya sendiri akses ke aplikasi apa pun. |
| Seed `sso_admins` pertama dilakukan manual (via migration/seeder), bukan lewat dashboard | Dashboard butuh setidaknya 1 admin yang sudah ada untuk bisa menambah admin lain — admin pertama harus disiapkan di luar sistem (chicken-and-egg problem). |
| Audit log untuk aksi `POST /admin/users/{id}/access` dan pencabutannya | Pemberian/pencabutan akses aplikasi adalah aksi sensitif (setara memberi kunci masuk); wajib punya jejak siapa yang melakukan dan kapan — kolom `granted_by`/`granted_at`/`revoked_at` sudah menjadi bagian dari desain tabel (Bagian 3.1), bukan tabel `audit_logs` terpisah untuk versi ini. |
| Validasi `application_id` pada `user_application_access` harus merujuk aplikasi yang benar-benar terdaftar di `applications` | Mencegah admin SSO tidak sengaja memberi akses ke `application_id` yang salah ketik/tidak valid. |
| Middleware role `admin` lokal tetap berlaku di dashboard Inventory | Dashboard Kelola Pengguna Inventory bukan untuk semua role — hanya role `admin` Inventory yang boleh mengaksesnya, `staff` tidak. |

---

## 9. Dampak terhadap Sprint & Rencana Kerja

Perubahan ini menambah cakupan pekerjaan dibanding rencana Sprint 1 semula (yang berbasis CLI). Item tambahan yang perlu dikerjakan:

- Tabel `user_application_access`, `sso_admins` beserta migration-nya (SSO Engine).
- Middleware `sso_admin` di SSO Engine.
- Endpoint & UI dashboard Kelola Pengguna di SSO Engine (Bagian 7.1).
- Perubahan logika `/authorize` (Bagian 5.2) beserta pengujian ulang skenario terkait dari 38 skenario Postman yang sudah ada.
- Endpoint & UI dashboard Kelola Pengguna di Inventory (Bagian 7.3), menggantikan `artisan sso:assign-role`.
- (Opsional, menyusul) Dashboard serupa di MyMember, mengikuti Bagian 4.3.

Mekanisme aktivasi/reset password via email (addendum v1.2 Bagian 6) **tidak perlu dikerjakan ulang** — sudah dirancang final dan tidak terpengaruh perubahan arsitektur ini.

CLI (`sso:create-user`, `sso:assign-role`) yang sudah dirancang di addendum v1.2 dapat tetap dipertahankan sebagai **alat internal/darurat** bagi developer (misalnya untuk seeding data saat testing), tapi bukan lagi menjadi antarmuka utama yang digunakan admin sehari-hari.

---

## 10. Ringkasan Keputusan Final

| Pertanyaan | Keputusan |
|---|---|
| Berapa lapis otorisasi yang ada sekarang? | 3 lapis: identitas (SSO), akses aplikasi (SSO), role internal (masing-masing aplikasi). |
| Siapa yang menentukan aplikasi apa saja yang boleh diakses seorang karyawan? | Admin SSO, melalui dashboard Kelola Pengguna di SSO Engine, menulis ke `user_application_access`. |
| Siapa yang menentukan role karyawan di dalam suatu aplikasi? | Admin aplikasi tersebut (misal Admin Inventory), melalui dashboard Kelola Pengguna aplikasi itu sendiri — tidak berubah dari addendum v1.2, hanya berpindah dari CLI ke web. |
| Bagaimana cara eksekusi provisioning sekarang? | Dashboard web ("Kelola Pengguna"), bukan CLI. CLI tetap ada sebagai alat internal/darurat developer. |
| Siapa yang boleh mengakses dashboard Kelola Pengguna di SSO Engine? | Hanya user yang terdaftar di tabel `sso_admins`. |
| Apa yang terjadi kalau karyawan mencoba login ke aplikasi yang belum diizinkan Admin SSO? | Ditolak di SSO Engine sendiri (Gerbang 1), sebelum authorization code pernah diterbitkan ke aplikasi tersebut. |
| Apa yang terjadi kalau karyawan sudah diizinkan aplikasi tapi role belum ditentukan admin aplikasi? | Ditolak di sisi aplikasi (Gerbang 2), token sudah ada tapi user lokal tidak pernah dibuat — konsisten dengan prinsip deny by default. |
| Apakah mekanisme aktivasi/reset password berubah? | Tidak. Tetap sesuai addendum v1.2 — email link, berlaku 10 menit, berlaku untuk semua karyawan. |

---

*Dokumen ini menggantikan bagian provisioning-eksekusi (CLI) pada addendum v1.2, namun tetap mewarisi seluruh desain mekanisme password (Bagian 6 addendum v1.2) dan prinsip dasar arsitektur dari PRD v1.1. Ketiga dokumen — PRD v1.1, addendum v1.2, dan PRD v2.0 ini — sebaiknya dibaca sebagai satu rangkaian keputusan, bukan saling menggantikan penuh.*
