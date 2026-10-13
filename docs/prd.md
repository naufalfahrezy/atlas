# ATLAS Product Requirements Document (PRD)

**Product:** ATLAS - Aplikasi Terpadu Logistik, Aset, dan Sistem Presensi  
**Status:** Baseline pengembangan  
**Basis:** Buku Tugas Akhir Muhammad Naufal Fahrezy v4(7) dan keputusan revisi terbaru  
**Target:** Web responsive, desktop-first, mendukung mobile browser  
**Bahasa:** Indonesia  
**Metode:** Personal Extreme Programming (PXP)

## 1. Tujuan Dokumen

Dokumen ini menjadi baseline pengembangan website ATLAS agar implementasi tidak keluar dari ruang lingkup tugas akhir. PRD mendefinisikan tujuan produk, aktor, hak akses, modul, alur bisnis, aturan bisnis, arsitektur, kebutuhan data, pola komunikasi, notifikasi, penyimpanan berkas, batasan teknis, serta acceptance criteria tingkat produk.

PRD menentukan apa yang harus dibangun. `design.md` menentukan bagaimana antarmuka dan pengalaman pengguna dirancang.

## 2. Ringkasan Produk

ATLAS adalah aplikasi web terintegrasi untuk mendukung proses operasional Unit Logistik dan Laboratorium Telkom University Surabaya. Sistem mencakup:

1. pengelolaan pengguna, peran, dan hak akses;
2. pengelolaan aset logistik;
3. peminjaman barang, ruangan, dan fasilitas;
4. verifikasi administratif oleh Student Service Center (SSC);
5. multi-tier approval oleh Unit Logistik dan Laboratorium serta Bagian Kemahasiswaan;
6. partial approval dan barang pengganti;
7. QR Code untuk pengambilan dan serah terima;
8. serah terima dan pengembalian;
9. pencatatan dan rekapitulasi anggaran pengadaan;
10. pengelolaan jadwal praktikum;
11. presensi Asisten Praktikum dengan validasi waktu, lokasi, dan foto;
12. pengelolaan BAP;
13. rekapitulasi honorarium;
14. notifikasi perubahan status;
15. pencarian, filter, rekap, dan laporan operasional.

Arsitektur utama:
- CodeIgniter untuk Aplikasi Web ATLAS dan API Gateway;
- Laravel untuk layanan backend;
- Supabase PostgreSQL sebagai basis data utama;
- Supabase Storage untuk file;
- RabbitMQ untuk notifikasi asinkron;
- Docker untuk containerization.

## 3. Sasaran Produk

- Mengurangi pencatatan berulang.
- Menyediakan satu sumber status pengajuan yang dapat ditelusuri.
- Memisahkan verifikasi administratif dari persetujuan.
- Mendukung partial approval dan barang pengganti.
- Menghubungkan aset dengan peminjaman, serah terima, dan pengembalian.
- Menghubungkan jadwal dengan presensi, BAP, dan honorarium.
- Menyediakan validasi lokasi dan foto presensi.
- Menyediakan rekap anggaran tanpa mengubah aset secara otomatis.
- Menyediakan audit trail dan notifikasi.
- Menjaga pemisahan domain melalui microservices.

## 4. Scope

### 4.1 In scope

- Landing page publik.
- Login dan akses berbasis peran.
- Pengelolaan akun oleh Staf Unit Logistik dan Laboratorium.
- Pengelolaan aset per unit fisik dengan ID unik.
- Input aset secara satuan maupun bulk dengan ID batch input yang unik.
- Peminjaman fasilitas.
- F01 sebagai dokumen unggahan.
- Form digital pengganti F02 manual.
- Nomor tiket otomatis.
- Verifikasi SSC.
- Approval Logistik.
- Partial approval.
- Barang pengganti.
- Approval Kemahasiswaan.
- QR Code setelah final approval.
- Validasi QR.
- F02 generated on demand.
- F03 generated on demand.
- Serah terima dan pengembalian.
- Anggaran pengadaan.
- Jadwal praktikum.
- Presensi berbasis waktu dan lokasi.
- Foto langsung.
- BAP.
- Honorarium.
- Notifikasi internal.
- Laporan.
- Audit trail.
- Light dan dark mode.

### 4.2 Out of scope

- Native mobile application.
- Penyusutan, penghapusan, dan maintenance aset.
- Integrasi inventaris resmi kampus.
- Pembelian/transaksi pengadaan.
- Pembayaran atau pencairan dana.
- Integrasi perbankan atau sistem keuangan resmi kampus.
- Anggaran yang otomatis membuat/mengubah aset.
- Tracking lokasi terus-menerus.
- Kubernetes.
- GraphQL.
- Hasura.
- Message broker sebagai transaksi utama.
- F02/F03 sebagai file permanen.
- Load/stress testing berskala besar sebagai kriteria penelitian.

## 5. Aktor dan Hak Akses

Tidak ada role Administrator terpisah. **Staf Unit Logistik dan Laboratorium merupakan role dengan kewenangan tertinggi.**

### 5.1 Guest

Akses:
- landing page;
- informasi fitur;
- alur singkat;
- FAQ;
- login.

Tidak dapat melihat data internal.

### 5.2 Pemohon Peminjaman Fasilitas

Akses:
- dashboard;
- unduh F01;
- unggah F01;
- form digital;
- submit pengajuan;
- nomor tiket;
- status dan riwayat;
- catatan perbaikan SSC;
- resubmit;
- hasil Logistik;
- hasil Kemahasiswaan;
- QR Code setelah final approval;
- notifikasi terkait pengajuan.

Tidak dapat melakukan verifikasi, approval, validasi QR, atau mengubah aset.

### 5.3 Student Service Center (SSC)

Akses:
- antrean verifikasi;
- detail pengajuan;
- F01;
- data form;
- catatan verifikasi;
- Kembalikan untuk Perbaikan;
- Teruskan ke Logistik;
- riwayat verifikasi.

SSC **bukan approval tier**.

### 5.4 Staf Unit Logistik dan Laboratorium

Role tertinggi.

Akses:
- pengguna, role, permission;
- aset dan riwayat aset;
- cek ketersediaan;
- antrean approval;
- approve/reject Logistik;
- partial approval;
- replacement item;
- validasi QR;
- F01;
- generate F02;
- generate F03;
- pengambilan;
- serah terima;
- pengembalian;
- kondisi awal/akhir;
- status aset;
- anggaran;
- dokumen anggaran;
- jadwal praktikum;
- validasi presensi/BAP;
- tarif honorarium;
- rekap honorarium;
- laporan;
- notifikasi.

Jika dibutuhkan, fungsi staf pengelola anggaran atau staf pengelola laboratorium dapat diimplementasikan sebagai permission scope di bawah role ini.

### 5.5 Bagian Kemahasiswaan

Akses:
- antrean pengajuan setelah approval Logistik;
- detail pengajuan;
- F01 dan data relevan;
- hasil keputusan Logistik;
- approve;
- reject;
- catatan;
- riwayat.

### 5.6 Asisten Praktikum

Akses:
- dashboard jadwal;
- jadwal miliknya;
- presensi;
- izin lokasi saat presensi;
- kamera untuk foto langsung;
- status/riwayat presensi sendiri.

## 6. Landing Page Publik

Landing page adalah halaman awal domain ATLAS dan tidak menampilkan data internal.

Struktur:
1. Header: logo ATLAS, Fitur, Alur, Tentang, FAQ, Masuk.
2. Hero: headline, penjelasan, CTA Masuk, CTA Pelajari Fitur, product preview.
3. Ringkasan manfaat: aset, peminjaman, presensi, laporan.
4. Modul utama.
5. Alur peminjaman ringkas.
6. Penjelasan akses berbasis peran.
7. FAQ.
8. CTA akhir.
9. Footer.

Tidak ada pricing dan tidak ada self-registration publik.

## 7. Modul Fungsional

### 7.1 Auth and User

- login/logout;
- profile;
- CRUD akun oleh Staf Unit Logistik dan Laboratorium;
- aktivasi/nonaktif akun;
- role dan permission;
- menu sesuai role;
- route guard;
- server-side authorization.

### 7.2 Asset Management

Setiap baris pada domain aset merepresentasikan **satu unit aset fisik**, bukan agregasi jumlah. Setiap aset menggunakan kolom `id` bertipe UUID sebagai primary key dan sekaligus ID unik aset. UUID dibuat otomatis oleh PostgreSQL menggunakan `gen_random_uuid()` dan tidak diinput maupun diedit oleh pengguna.

Setiap proses penambahan aset, baik satuan maupun bulk, membentuk satu **batch input**. Batch juga menggunakan kolom `id` bertipe UUID sebagai ID Batch. Apabila satu batch berisi 10 aset, sistem membuat satu record `asset_batches` dan 10 record `assets`. Kesepuluh aset memiliki UUID yang berbeda, tetapi seluruhnya mengacu pada `batch_id` yang sama.

Data minimum aset:
- `id` sebagai UUID unik aset;
- `batch_id` sebagai referensi ke batch input;
- nama;
- kategori;
- lokasi;
- kondisi;
- status ketersediaan;
- catatan;
- timestamp;
- pengguna yang membuat atau memperbarui data.

Data minimum batch:
- `id` sebagai UUID unik batch;
- metode input (`SINGLE` atau `BULK_FORM`);
- catatan batch;
- pengguna pembuat;
- timestamp.

Fitur:
- list/search/filter/sort;
- pencarian berdasarkan ID Aset, ID Batch, nama aset, kategori, dan lokasi;
- detail aset;
- detail batch dan daftar aset di dalam batch;
- tambah aset satuan;
- tambah aset secara bulk;
- edit atribut aset yang diperbolehkan;
- riwayat perubahan kondisi, lokasi, dan status aset;
- cek ketersediaan aset pada periode tertentu.

Business rules:
- UUID aset dan UUID batch dibuat otomatis oleh database;
- UUID tidak dapat diedit melalui fungsi aplikasi;
- setiap aset wajib terhubung ke tepat satu batch input;
- satu batch dapat berisi satu atau lebih aset;
- penambahan satu aset tetap membentuk satu batch dengan metode `SINGLE`;
- penambahan bulk membentuk satu batch dengan metode `BULK_FORM`;
- proses bulk harus bersifat atomik, sehingga seluruh aset dalam batch disimpan bersama atau seluruh transaksi dibatalkan apabila terdapat kesalahan;
- jumlah aset dihitung dari jumlah record aset, bukan dari kolom agregat jumlah;
- `batch_id` merepresentasikan asal transaksi input aset dan tidak dapat dipindahkan setelah aset terbentuk;
- perubahan kondisi, lokasi, atau status ketersediaan dicatat pada riwayat aset.

### 7.3 Borrowing

Flow wajib:
1. login;
2. unduh F01;
3. lengkapi F01;
4. unggah F01;
5. isi form digital;
6. validasi;
7. submit;
8. sistem simpan;
9. nomor tiket unik;
10. status `MENUNGGU_VERIFIKASI_SSC`;
11. event notifikasi.

Data form:
- identitas pemohon;
- kegiatan;
- tanggal;
- waktu;
- barang/ruangan;
- jumlah;
- keterangan;
- F01.

Nomor tiket harus unik dan immutable. Format contoh: `ATL-YYYYMMDD-XXXX`.

### 7.4 Verifikasi SSC

Jika return:
- status `PERLU_PERBAIKAN`;
- catatan wajib;
- pemohon menerima notifikasi.

Jika forward:
- status `MENUNGGU_APPROVAL_LOGISTIK`.

SSC tidak dapat approve final, partial approve, atau memilih replacement item.

### 7.5 Approval Logistik

Prasyarat: lolos SSC.

Aksi:
- cek availability;
- approve;
- reject;
- partial approval;
- replacement item;
- notes.

Business rules:
- approved qty <= requested qty;
- replacement harus valid dan tersedia;
- keputusan per item disimpan;
- jika lolos, status `MENUNGGU_PERSETUJUAN_KEMAHASISWAAN`.

### 7.6 Approval Kemahasiswaan

Prasyarat: approval Logistik selesai.

Jika approve:
- status `DISETUJUI_FINAL`;
- QR dapat diterbitkan.

Jika reject:
- status `DITOLAK_KEMAHASISWAAN`;
- QR tidak diterbitkan.

### 7.7 QR Handover

QR hanya diterbitkan setelah final approval.

Payload QR menggunakan token aman, bukan data sensitif lengkap.

Validasi:
- token valid;
- ticket ditemukan;
- final approval valid;
- belum dibatalkan;
- tidak ada duplicate handover.

#### Dokumen

**F01**
- file asli;
- disimpan di Supabase Storage.

**F02**
- tidak disimpan sebagai file;
- generated on demand dari pengajuan dan item yang disetujui.

**F03**
- tidak disimpan sebagai file;
- generated on demand dari pengajuan dan data serah terima.

F02/F03 dapat dirender sebagai HTML printable atau PDF sementara.

#### Handover/return

Catat:
- waktu;
- staf;
- item;
- jumlah;
- kondisi awal;
- catatan;
- waktu return;
- kondisi akhir.

QR Handover Service mengelola proses, Asset Service tetap pemilik status aset.

### 7.8 Procurement Budget

Scope:
- kategori;
- periode;
- rencana;
- realisasi;
- dokumen pendukung;
- rekap.

Business rule:
- tidak otomatis membuat atau memperbarui aset.

### 7.9 Practicum Schedule

Data:
- mata kuliah;
- kelas;
- ruangan;
- tanggal;
- jam;
- Asisten Praktikum;
- status.

Jadwal menjadi sumber validasi presensi.

### 7.10 Attendance

Flow:
1. buka jadwal;
2. Mulai Presensi;
3. minta lokasi;
4. validasi area;
5. buka kamera;
6. foto langsung;
7. simpan;
8. tampilkan status.

Privasi:
- lokasi hanya diakses saat presensi;
- tidak ada background tracking.

### 7.11 BAP

- data BAP;
- relasi jadwal;
- relasi presensi;
- validasi;
- status;
- catatan koreksi.

### 7.12 Honorarium

- tarif dan effective period;
- sesi tervalidasi;
- perhitungan;
- rekap per Asisten/periode.

Perubahan tarif baru tidak mengubah histori lama.

### 7.13 Notification

Persistent notification dengan read/unread.

Producer:
- Borrowing Service;
- Approval Service;
- QR Handover Service.

Consumer:
- Notification Service.

Trigger minimum:
- submitted;
- SSC returned;
- SSC verified;
- logistics decision;
- student affairs decision;
- pickup/handover status.

Kegagalan notifikasi tidak boleh me-rollback transaksi utama.

### 7.14 Reporting

Laporan:
- aset;
- peminjaman;
- anggaran;
- presensi;
- BAP;
- honorarium.

Fitur:
- search;
- filter;
- date range;
- sorting;
- pagination;
- export bila diputuskan saat implementasi.

## 8. Borrowing State Model

Status minimum:

1. `MENUNGGU_VERIFIKASI_SSC`
2. `PERLU_PERBAIKAN`
3. `MENUNGGU_APPROVAL_LOGISTIK`
4. `DITOLAK_LOGISTIK`
5. `MENUNGGU_PERSETUJUAN_KEMAHASISWAAN`
6. `DITOLAK_KEMAHASISWAAN`
7. `DISETUJUI_FINAL`
8. `SIAP_DIAMBIL`
9. `SUDAH_DIAMBIL`
10. `DIKEMBALIKAN`
11. `SELESAI`

Partial approval adalah keputusan item dan histori approval, tidak harus menjadi status global.

## 9. Audit Trail

Audit minimum:
- entity type;
- entity ID;
- action;
- old status;
- new status;
- actor user ID;
- actor role;
- timestamp;
- notes;
- correlation/request ID jika tersedia.

Wajib untuk submit, resubmit, SSC, approval, partial approval, replacement, QR, handover, return, pembuatan batch aset, pembuatan aset, dan perubahan status aset.

## 10. Arsitektur Teknis

### 10.1 Frontend/Gateway
- CodeIgniter Aplikasi Web.
- CodeIgniter API Gateway.
- Browser -> Aplikasi Web -> API Gateway -> Laravel Service.

Tidak ada direct business request browser -> Laravel Service.

### 10.2 Services
1. API Gateway Service
2. Auth and User Service
3. Asset Service
4. Borrowing Service
5. Approval Service
6. QR Handover Service
7. Procurement Budget Service
8. Practicum Schedule Service
9. Attendance and BAP Service
10. Honorarium Service
11. Reporting Service
12. Notification Service

### 10.3 Data
- Supabase PostgreSQL.
- Data dikelompokkan berdasarkan domain.
- Akses lintas domain melalui service contract bila melibatkan business logic.

### 10.4 Storage
Bucket:
- `borrowing-f01`
- `attendance-photos`
- `budget-documents`
- `public-assets`

Tidak ada bucket F02/F03.

### 10.5 RabbitMQ
- AMQP;
- async notification only;
- Notification Service consumer.

### 10.6 Docker
Container terpisah untuk service dan RabbitMQ. Supabase tetap external managed service.

## 11. API Contract Baseline

Prefix: `/api/v1`

### Auth and User
- `POST /auth/login`
- `POST /auth/logout`
- `GET /me`
- `GET /users`
- `POST /users`
- `GET /users/{id}`
- `PATCH /users/{id}`
- `PATCH /users/{id}/status`
- `PATCH /users/{id}/role`

### Asset
- `GET /assets`
- `POST /assets`
- `POST /assets/bulk`
- `GET /assets/{id}`
- `PATCH /assets/{id}`
- `GET /assets/{id}/history`
- `GET /assets/availability`
- `PATCH /assets/{id}/status`
- `GET /asset-batches`
- `GET /asset-batches/{id}`

`POST /assets` membuat satu batch berisi satu aset. `POST /assets/bulk` membuat satu batch dan beberapa aset individual secara atomik dalam satu transaksi. Response pembuatan bulk minimal mengembalikan `batch_id`, jumlah aset yang berhasil dibuat, dan daftar UUID aset pada field `id`.

### Borrowing
- `GET /borrowings`
- `POST /borrowings`
- `GET /borrowings/{id}`
- `GET /borrowings/ticket/{ticket}`
- `POST /borrowings/{id}/f01`
- `POST /borrowings/{id}/submit`
- `PATCH /borrowings/{id}/resubmit`
- `GET /borrowings/{id}/history`
- `POST /borrowings/{id}/ssc-verification`

### Approval
- `GET /approvals/logistics`
- `POST /approvals/{borrowingId}/logistics`
- `GET /approvals/student-affairs`
- `POST /approvals/{borrowingId}/student-affairs`

### QR Handover
- `GET /handover/{borrowingId}/qr`
- `POST /handover/validate`
- `GET /handover/{borrowingId}/f02`
- `GET /handover/{borrowingId}/f03`
- `POST /handover/{borrowingId}`
- `POST /returns/{borrowingId}`

### Budget
- `GET /budgets`
- `POST /budgets`
- `GET /budgets/{id}`
- `PATCH /budgets/{id}`
- `DELETE /budgets/{id}`
- `POST /budgets/{id}/documents`
- `GET /budgets/summary`

### Schedule
- `GET /practicum-schedules`
- `POST /practicum-schedules`
- `GET /practicum-schedules/{id}`
- `PATCH /practicum-schedules/{id}`

### Attendance/BAP
- `GET /attendances/me`
- `POST /attendances/check-in`
- `GET /attendances/{id}`
- `POST /attendances/{id}/validate`
- `GET /baps`
- `POST /baps`
- `PATCH /baps/{id}`
- `POST /baps/{id}/validate`

### Honorarium
- `GET /honorarium-rates`
- `POST /honorarium-rates`
- `PATCH /honorarium-rates/{id}`
- `GET /honorariums/recap`
- `POST /honorariums/calculate`

### Notification
- `GET /notifications`
- `PATCH /notifications/{id}/read`
- `PATCH /notifications/read-all`

### Reporting
- `GET /reports/assets`
- `GET /reports/borrowings`
- `GET /reports/budgets`
- `GET /reports/attendance`
- `GET /reports/bap`
- `GET /reports/honorariums`

Endpoint final dapat berubah selama domain ownership dan business rule tidak berubah.

## 12. Supabase Authentication and Authorization

Flow:
1. login dari Aplikasi Web;
2. Auth and User Service berinteraksi dengan Supabase Auth;
3. session/JWT diterbitkan;
4. session disimpan aman;
5. API Gateway memvalidasi identity;
6. service melakukan authorization.

Jangan mengandalkan hide/show menu saja.

## 13. Logical Database Schema

### User
- `profiles`
- `roles`
- `permissions`
- `role_permissions`

### Asset
- `asset_batches`
- `assets`
- `asset_status_history`

`asset_batches` menyimpan identitas dan metadata transaksi input aset. `assets` menyimpan satu record untuk setiap unit aset fisik dan memiliki foreign key ke `asset_batches`. `asset_status_history` menyimpan riwayat perubahan kondisi, lokasi, dan status ketersediaan setiap aset.

### Borrowing
- `borrowing_requests`
- `borrowing_items`
- `borrowing_documents`
- `borrowing_status_history`
- `ssc_verifications`

`borrowing_documents` hanya untuk metadata F01 atau dokumen persist lain yang memang disetujui.

### Approval
- `approval_actions`
- `approval_item_decisions`
- `replacement_items`

### Handover
- `handover_tokens`
- `handover_records`
- `handover_items`
- `return_records`

### Budget
- `procurement_budget_entries`
- `procurement_budget_documents`

### Practicum
- `practicum_schedules`
- `attendance_records`
- `bap_records`

### Honorarium
- `honorarium_rates`
- `honorarium_recaps`
- `honorarium_recap_items`

### Notification
- `notifications`

### Audit
- `audit_logs`

## 14. Database Change Management tanpa Framework Migration

ATLAS tidak menggunakan Laravel migration atau CodeIgniter migration sebagai sumber perubahan skema.

Setiap update skema dibuat sebagai SQL change script.

Struktur:

```text
database/
  sql/
    20261012_001_initial_schema.sql
    20261012_002_add_borrowing_history.sql
  rollback/
    20261012_002_add_borrowing_history_rollback.sql
  snapshots/
    current_schema.sql
```

Template:

```sql
-- ATLAS SQL CHANGE
-- Purpose:
-- Affected domain:
-- Affected services:
-- Preconditions:
-- Rollback file:
-- Created at:

BEGIN;

-- SQL statements

COMMIT;
```

Aturan:
- gunakan `IF EXISTS`/`IF NOT EXISTS` bila aman;
- perubahan berisiko harus diuji di development;
- sediakan rollback SQL bila memungkinkan;
- SQL dapat dijalankan melalui Supabase SQL Editor;
- `database/sql` menjadi histori perubahan;
- `current_schema.sql` dapat digunakan sebagai snapshot;
- tidak menggunakan `php artisan migrate` untuk source of truth skema;
- SQL dapat mencakup RLS, index, grant, revoke, trigger, dan function.

Secret tidak pernah disimpan di repository.

## 15. Storage Rules

### F01
- private;
- type/size validation;
- hanya role berwenang;
- metadata di database.

### Attendance photo
- private;
- terkait attendance record;
- foto direct capture.

### Budget documents
- private;
- terkait budget entry.

### Public assets
- logo dan aset landing non-sensitif.

## 16. Notification Event Contract

Contoh:

```json
{
  "event_id": "uuid",
  "event_type": "borrowing.ssc.verified",
  "occurred_at": "ISO-8601",
  "actor_id": "uuid",
  "borrowing_id": "uuid",
  "ticket_number": "ATL-...",
  "recipient_ids": ["uuid"],
  "payload": {
    "old_status": "MENUNGGU_VERIFIKASI_SSC",
    "new_status": "MENUNGGU_APPROVAL_LOGISTIK",
    "message_key": "ssc_verified"
  }
}
```

Event minimum:
- `borrowing.submitted`
- `borrowing.ssc.returned`
- `borrowing.ssc.verified`
- `borrowing.logistics.approved`
- `borrowing.logistics.rejected`
- `borrowing.student_affairs.approved`
- `borrowing.student_affairs.rejected`
- `handover.ready`
- `handover.completed`
- `return.completed`

Notification consumer harus idempotent berdasarkan `event_id`.

## 17. Search, Filter, Pagination

List besar wajib menyediakan:
- search;
- filter;
- sort;
- pagination;
- loading;
- empty state;
- error state.

Pada modul aset, search minimal mendukung UUID aset, UUID batch, nama aset, kategori, dan lokasi. Filter batch dapat digunakan untuk menampilkan seluruh aset yang berasal dari satu transaksi input. Pada UI, UUID dapat ditampilkan secara ringkas, tetapi pencarian dan salin ID tetap menggunakan nilai UUID lengkap.

Filter tidak boleh memperluas akses role.

## 18. F02/F03 Generation

### F02
Sumber data:
- ticket;
- pemohon;
- kegiatan;
- jadwal;
- requested item;
- approved item;
- approved qty;
- replacement item.

### F03
Sumber data:
- pengajuan;
- final approval;
- handover items;
- pickup time;
- condition;
- processing staff.

F02/F03 tidak disimpan sebagai file permanen.

## 19. Error Handling

Sistem harus menangani:
- login gagal;
- access denied;
- F01 belum ada;
- file invalid;
- form tidak lengkap;
- item tidak tersedia;
- invalid workflow state;
- QR invalid/already used;
- lokasi ditolak;
- kamera ditolak;
- di luar area;
- jadwal tidak aktif;
- upload gagal;
- service unavailable;
- notification processing failure.

Jangan tampilkan stack trace atau secret ke user.

## 20. Security Baseline

- authentication;
- server authorization;
- CSRF;
- input validation;
- upload validation;
- signed/private Storage URL;
- output escaping;
- rate limiting untuk endpoint sensitif;
- session timeout;
- audit log;
- env-based secret;
- HTTPS;
- Supabase service role hanya server-side;
- request/correlation ID bila memungkinkan.

## 21. Accessibility and Usability

- keyboard accessible;
- visible focus;
- explicit labels;
- text + color untuk status;
- responsive;
- alasan penggunaan lokasi dan kamera;
- approval action tidak ambigu;
- destructive action confirmation.

Detail ada di `design.md`.

## 22. Dashboard per Role

### Pemohon
- pengajuan aktif;
- perlu perbaikan;
- disetujui;
- status terakhir;
- quick action;
- notifikasi.

### SSC
- menunggu verifikasi;
- selesai diverifikasi;
- antrean terbaru.

### Staf Unit Logistik dan Laboratorium
- aset tersedia/dipinjam;
- approval menunggu;
- pengambilan hari ini;
- pengembalian;
- jadwal;
- presensi perlu validasi;
- anggaran;
- laporan.

### Kemahasiswaan
- menunggu approval;
- disetujui;
- ditolak;
- riwayat.

### Asisten
- jadwal hari ini;
- presensi tersedia;
- riwayat.

## 23. Acceptance Criteria Produk

Baseline dinyatakan terpenuhi jika:
1. role dan hak akses bekerja sesuai kewenangan;
2. setiap unit aset fisik disimpan sebagai satu record aset dengan UUID unik;
3. setiap transaksi input aset memiliki satu record batch dengan UUID unik;
4. input satuan membuat satu batch `SINGLE` yang berisi satu aset;
5. input bulk membuat satu batch `BULK_FORM` dan beberapa record aset secara atomik;
6. seluruh aset hasil bulk dapat ditelusuri melalui `batch_id` yang sama;
7. UUID aset, UUID batch, dan hubungan `batch_id` tidak dapat diubah melalui fungsi aplikasi;
8. Guest hanya dapat mengakses halaman publik;
9. pengajuan F01 dan form digital dapat dikirim;
10. nomor tiket dibuat otomatis;
11. SSC dapat mengembalikan atau meneruskan pengajuan tanpa menjadi approval tier;
12. Logistik hanya memproses pengajuan yang telah lolos verifikasi SSC;
13. partial approval dan replacement item tercatat;
14. Kemahasiswaan hanya memproses pengajuan setelah keputusan Logistik;
15. QR hanya diterbitkan setelah final approval;
16. QR dapat divalidasi oleh Logistik;
17. F01 dapat ditampilkan kembali;
18. F02 dan F03 generated on demand;
19. handover dan return memperbarui kondisi atau status aset sesuai business rule;
20. pencatatan anggaran tidak otomatis mengubah data aset;
21. jadwal menjadi dasar validasi presensi;
22. presensi memeriksa waktu, lokasi, dan foto;
23. BAP dapat dihubungkan dengan presensi;
24. honorarium menggunakan data tervalidasi dan tarif periode;
25. notifikasi asinkron bekerja sesuai event;
26. audit trail tersedia untuk perubahan penting;
27. laporan dibatasi berdasarkan hak akses;
28. light dan dark mode tidak mengubah fungsi;
29. flow utama dapat diselesaikan pada desktop dan mobile.

## 24. Testing Alignment

### Black-box
Equivalence Partitioning untuk valid/invalid.

### API
Uji auth, authorization, validation, status code, response schema.

### Integration
Submit -> Ticket -> SSC -> Logistik -> Kemahasiswaan -> QR -> Handover -> Asset Update -> Notification.

### Usability
Peran:
- Pemohon;
- SSC;
- Staf Unit Logistik dan Laboratorium;
- Kemahasiswaan;
- Asisten Praktikum.

## 25. Development Iterations

- Iterasi 1: US-01, US-02. Auth, permission, aset per unit, UUID aset, batch input berbasis UUID, dan bulk asset creation.
- Iterasi 2: US-03, US-04, US-05. F01, form, ticket, status, SSC.
- Iterasi 3: US-06 sampai US-12. Approval, QR, handover, return, notification.
- Iterasi 4: US-13. Anggaran.
- Iterasi 5: US-14, US-15. Jadwal, presensi.
- Iterasi 6: US-16, US-17. BAP, honorarium, reporting, integrasi.

## 26. Definition of Done

Fitur selesai jika:
- user story terpenuhi;
- acceptance criteria terpenuhi;
- role/permission benar;
- server validation tersedia;
- loading/empty/error state ada;
- audit tersedia untuk proses kritis;
- responsif;
- keyboard usable;
- endpoint diuji;
- tidak scope creep;
- perubahan DB memiliki SQL change script.

## 27. Change Control

Scope change bila:
- menambah aktor bisnis baru;
- menambah approval tier;
- SSC menjadi approver;
- budget mengubah aset;
- F02/F03 menjadi permanent file;
- menambah pembayaran;
- menambah maintenance/depreciation;
- GraphQL/Hasura;
- RabbitMQ menjadi transaksi utama;
- Kubernetes.

Perubahan tersebut harus dikonfirmasi sebelum implementasi.

## 28. Source of Truth

Urutan prioritas:
1. requirements dan keputusan revisi terbaru;
2. PRD ini;
3. user story/acceptance criteria;
4. `design.md`;
5. kode.

Jika kode berbeda dengan business rule, business rule tidak otomatis berubah.

## 29. Referensi

Basis utama:
- `Buku Tugas Akhir - Muhammad Naufal Fahrezy v4(7).docx`

UI/UX:
- `design.md`
