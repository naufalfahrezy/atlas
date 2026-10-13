# ATLAS UI/UX Design Specification

**Product:** ATLAS - Aplikasi Terpadu Logistik, Aset, dan Sistem Presensi  
**Design direction:** Modern SaaS enterprise web application  
**Primary design system:** Atlassian Design System (ADS)  
**Themes:** Light dan Dark  
**Companion document:** `prd.md`

## 1. Tujuan

Dokumen ini menjadi baseline UI/UX ATLAS. Seluruh halaman publik dan aplikasi internal harus konsisten, accessible, responsive, data-oriented, dan sesuai pola enterprise SaaS.

Atlassian Design System digunakan sebagai sumber utama untuk foundations, design tokens, spacing, typography, radius, elevation, navigation, forms, tables, status, messaging, loading, overlays, accessibility, dan motion. ATLAS tidak menyalin identitas merek Atlassian. Logo, copy, ilustrasi, dan identitas produk tetap milik ATLAS/Telkom University Surabaya.

## 2. Kecocokan ADS untuk ATLAS

ADS cocok untuk ATLAS karena ATLAS merupakan aplikasi enterprise internal dengan banyak tabel, workflow approval, status dan riwayat, form kompleks, notifikasi, panel detail, navigasi multi-role, serta kebutuhan desktop dan mobile. Gunakan ADS sebagai fondasi utama dan jangan mencampur design system lain tanpa kebutuhan yang jelas.

## 3. Integrasi Teknis dengan Stack ATLAS

Stack tugas akhir menggunakan CodeIgniter pada Aplikasi Web dan API Gateway serta Laravel pada backend. Banyak package Atlassian Design System berorientasi React.

Aturan implementasi:

1. Jangan mengubah ATLAS menjadi React SPA hanya demi memakai Atlaskit.
2. Foundations dan token ADS menjadi sumber kebenaran desain.
3. `@atlaskit/tokens` dan `@atlaskit/css-reset` dapat digunakan melalui frontend asset build jika kompatibel.
4. React component Atlaskit hanya dipakai jika proyek memang memiliki React mount point yang disetujui.
5. Untuk server-rendered CodeIgniter, buat komponen HTML/CSS/JS yang mengikuti visual, states, spacing, accessibility, dan behavior ADS.
6. Jangan mengurangi keyboard support, focus state, semantics, atau error handling.
7. Jangan menambah framework frontend baru tanpa kebutuhan yang disetujui.

Targetnya adalah **ADS-consistent UI**, bukan memaksa dependency yang bertentangan dengan arsitektur penelitian.

## 4. Design Principles

### 4.1 Calm enterprise SaaS
UI harus tenang, rapi, dan profesional. Hindari dekorasi yang tidak membantu tugas.

### 4.2 Data first
Tabel, status, form, approval, dan workflow lebih penting daripada ornamen.

### 4.3 Clear hierarchy
Setiap halaman memiliki page title, supporting text bila perlu, primary action yang jelas, secondary action yang tidak bersaing, dan grouping yang mudah dipindai.

### 4.4 Role-aware
Menu dan dashboard berubah berdasarkan role.

### 4.5 Status is visible
Status pengajuan, aset, presensi, dan dokumen selalu mudah ditemukan.

### 4.6 Accessible by default
Keyboard, focus, labels, contrast, responsive behavior, dan motion preferences wajib diperhatikan.

## 5. Visual Identity

Karakter visual ATLAS:
- clean;
- neutral surface;
- modern SaaS;
- primary interaction menggunakan semantic ADS blue/selected token;
- border tipis;
- shadow minimal;
- radius moderat;
- whitespace terukur;
- status menggunakan Lozenge atau semantic messaging;
- dashboard tidak dipenuhi card dekoratif.

Identitas institusi dapat muncul pada logo/wordmark, landing page, footer, dan branding kecil pada sidebar/header. Warna merah Telkom bukan primary action karena berpotensi bertabrakan dengan semantic danger. Jika digunakan, posisikan sebagai brand accent non-semantic.

## 6. Design Tokens

Prioritaskan semantic tokens ADS daripada raw hex.

Kategori token:
- color;
- spacing;
- typography;
- border;
- radius;
- elevation;
- motion.

Contoh penggunaan:
- page background -> semantic surface token;
- primary text -> semantic text token;
- subtle text -> subtle text token;
- selected nav -> selected token;
- success -> success token;
- warning -> warning token;
- danger -> danger token.

Nilai resmi dapat berubah. Ikuti versi ADS yang dipakai.

## 7. Color System

### 7.1 Light mode
- background utama sangat terang;
- surface/card default;
- text high contrast;
- secondary text subtle;
- border subtle;
- primary interactive ADS blue/selected;
- green untuk success;
- amber/yellow untuk warning;
- red untuk danger;
- blue untuk info.

### 7.2 Dark mode
- gunakan dark theme token ADS;
- jangan sekadar invert hex;
- surface harus memiliki hierarchy;
- border tetap terlihat;
- status tetap kontras;
- overlay lebih jelas dari base surface.

### 7.3 Theme behavior
Default mengikuti system preference. User dapat memilih Light, Dark, atau System. Preferensi disimpan lokal dan dapat disimpan ke profile jika dibutuhkan.

## 8. Typography

### 8.1 Font
Preferred: `Atlassian Sans`, hanya jika diperoleh melalui distribusi resmi dan penggunaannya diperbolehkan.

Fallback:

```css
font-family:
  "Atlassian Sans",
  -apple-system,
  BlinkMacSystemFont,
  "Segoe UI",
  Roboto,
  Helvetica,
  Arial,
  sans-serif;
```

Monospace:

```css
font-family:
  "Atlassian Mono",
  ui-monospace,
  SFMono-Regular,
  Menlo,
  Monaco,
  Consolas,
  monospace;
```

Jangan mengambil font dari sumber tidak resmi.

### 8.2 App typography scale

| Use | Size | Line height | Weight |
|---|---:|---:|---|
| Heading XXL | 32 px | 36 px | Bold |
| Heading XL | 28 px | 32 px | Bold |
| Heading L | 24 px | 28 px | Bold |
| Heading M | 20 px | 24 px | Bold |
| Heading S | 16 px | 20 px | Bold |
| Heading XS | 14 px | 20 px | Bold |
| Body L | 16 px | 24 px | Regular |
| Body M | 14 px | 20 px | Regular |
| Body S | 12 px | 16 px | Regular |

Landing page boleh menggunakan headline 40-48 px pada desktop sebagai token ATLAS khusus. Mobile 32-36 px.

## 9. Spacing

Gunakan basis 8 px.

Scale utama: 4, 8, 12, 16, 24, 32, 40, 48, 64, 80 px.

- form field gap: 16 px;
- card content gap: 12-16 px;
- page section gap: 24-32 px;
- landing section gap: 64-80 px desktop, 40-48 px mobile.

Hindari spacing random.

## 10. Radius

| Use | Radius |
|---|---:|
| Badge/detail kecil | 2 px |
| Lozenge/tag/compact | 4 px |
| Button/input/select/nav | 6 px |
| Card/menu/floating container | 8 px |
| Marketing card/preview | 12-16 px sebagai ATLAS extension |

Internal app dominan 6-8 px. Jangan membuat semua elemen pill.

## 11. Border and Focus

- default border: 1 px;
- selected state: 2 px bila menggunakan border selected;
- focus: visible 2 px dengan visual offset 2 px;
- focus tidak boleh hanya bergantung pada warna.

Keyboard user harus selalu tahu posisi focus.

## 12. Elevation and Shadow

Gunakan shadow hanya untuk dropdown, popup, modal, overlay, dan panel layer. Untuk grouping, prioritaskan whitespace, border, lalu surface. Jangan memberi shadow pada semua card.

## 13. Navigation Architecture

### 13.1 Top Navigation
Tinggi 56 px.

Isi:
- side nav toggle;
- ATLAS/app identity;
- optional breadcrumbs/search;
- notification;
- theme/user menu.

### 13.2 Side Navigation
Desktop default 320 px, minimum 240 px.

Struktur:
- SideNavHeader;
- SideNavBody;
- SideNavFooter.

#### Pemohon
- Dashboard
- Pengajuan
- Riwayat
- Notifikasi

#### SSC
- Dashboard
- Verifikasi Pengajuan
- Riwayat Verifikasi
- Notifikasi

#### Staf Unit Logistik dan Laboratorium
- Dashboard
- Pengguna & Hak Akses
- Aset
- Peminjaman
- Persetujuan
- Serah Terima
- Anggaran
- Jadwal Praktikum
- Presensi & BAP
- Honorarium
- Laporan
- Notifikasi

#### Bagian Kemahasiswaan
- Dashboard
- Persetujuan
- Riwayat
- Notifikasi

#### Asisten Praktikum
- Dashboard
- Jadwal
- Presensi
- Riwayat Presensi

### 13.3 Responsive navigation
- viewport < 1024 px: side nav collapsed dan dibuka sebagai overlay;
- viewport < 768 px: overlay side nav maksimal 320 px atau 90% viewport;
- tidak menggunakan bottom navigation kecuali requirements berubah.

## 14. Page Layout

Desktop:

```text
+----------------------------------------------------------------+
| Toggle | ATLAS | Search(optional)            Bell | Theme | User |
+----------------------+-----------------------------------------+
| Side Navigation      | Breadcrumbs                             |
|                      | Page Title                 Primary CTA   |
|                      +-----------------------------------------+
|                      | Filters / Toolbar                       |
|                      | Main Content                            |
+----------------------+-----------------------------------------+
```

Padding:
- desktop 24-32 px;
- tablet 16-24 px;
- mobile 16 px.

Form width 640-760 px. Detail page dapat 960-1200 px. Data table memakai area penuh.

## 15. Landing Page Design

Landing page memakai ADS foundation tetapi boleh lebih expressive daripada aplikasi internal.

### 15.1 Header
- 64-72 px;
- logo kiri;
- nav;
- tombol Masuk;
- sticky jika diperlukan.

### 15.2 Hero
Desktop dua kolom: copy kiri dan product preview kanan. Mobile satu kolom. Hero boleh memakai dark neutral/navy surface, tetapi interactive state tetap semantic ADS.

### 15.3 Section order
1. Hero
2. Benefits
3. Modules
4. Borrowing workflow
5. Role-based access
6. Product preview
7. FAQ
8. CTA
9. Footer

Tidak ada pricing, public sign-up, testimonial palsu, atau metric yang tidak dapat dibuktikan.

## 16. Core Components

Gunakan komponen/pola ADS sebagai referensi utama.

### Navigation
- Navigation System
- Breadcrumbs
- Tabs
- Pagination

### Forms
- Form
- Text Field
- Text Area
- Select
- Checkbox
- Radio
- Date Time Picker
- Dropdown Menu
- Button
- Focus Ring

### Data display
- Dynamic Table
- Table
- Tag
- Badge
- Lozenge
- Tooltip

### Messaging
- Flag
- Inline Message
- Section Message
- Banner
- Empty State

### Overlay
- Modal Dialog
- Popup
- Drawer/Panel

### Loading
- Skeleton
- Spinner
- Progress Bar

### Journey
- Progress Indicator
- Progress Tracker

Jangan menggunakan card sebagai default untuk semua konten.

## 17. Notification UX

### 17.1 Transient feedback
Gunakan **Flag**.

Posisi:
- bottom-left desktop;
- bottom area mobile dengan safe spacing.

Contoh:
- Pengajuan berhasil dikirim.
- Perubahan berhasil disimpan.
- QR berhasil divalidasi.

Success sederhana boleh auto-dismiss. Error penting tidak hilang terlalu cepat. Action opsional seperti `Lihat pengajuan`.

### 17.2 Persistent notification
Bell di TopNav.

Desktop:
- right-side Panel;
- sekitar 365 px.

Mobile:
- full-height overlay atau page.

Item:
- title;
- message;
- time;
- read/unread;
- link to entity.

### 17.3 Contextual messaging
- Inline Message untuk field/local issue;
- Section Message untuk section;
- Banner hanya untuk critical system-level.

Jangan memakai toast top-right sebagai pola utama.

## 18. Status Language

Status selalu memiliki teks.

| Status | Label | Appearance |
|---|---|---|
| MENUNGGU_VERIFIKASI_SSC | Menunggu Verifikasi SSC | in progress |
| PERLU_PERBAIKAN | Perlu Perbaikan | moved/warning |
| MENUNGGU_APPROVAL_LOGISTIK | Menunggu Approval Logistik | in progress |
| DITOLAK_LOGISTIK | Ditolak Logistik | removed |
| MENUNGGU_PERSETUJUAN_KEMAHASISWAAN | Menunggu Persetujuan Kemahasiswaan | new/in progress |
| DITOLAK_KEMAHASISWAAN | Ditolak Kemahasiswaan | removed |
| DISETUJUI_FINAL | Disetujui | success |
| SIAP_DIAMBIL | Siap Diambil | success |
| SUDAH_DIAMBIL | Sedang Dipinjam | moved |
| DIKEMBALIKAN | Dikembalikan | success |
| SELESAI | Selesai | default/success |

Gunakan Lozenge atau semantic equivalent dari ADS.

## 19. Buttons

Hierarchy:
1. Primary
2. Default/secondary
3. Subtle
4. Link
5. Danger

Rules:
- satu primary action dominan per area;
- destructive menggunakan danger;
- Approve dan Reject dibedakan jelas;
- loading mencegah double submit;
- icon-only wajib tooltip dan accessible label.

## 20. Forms

### 20.1 Layout
- single column default;
- label di atas;
- helper text;
- required marker konsisten;
- form panjang dibagi section.

### 20.2 Validation
- error dekat field;
- form panjang punya error summary;
- focus ke error pertama setelah submit gagal;
- server validation tetap source of truth.

### 20.3 Borrowing form
1. Informasi kegiatan
2. Waktu penggunaan
3. Barang/ruangan
4. Dokumen F01
5. Review
6. Submit

F01 harus dibedakan jelas dari generated F02/F03.

## 21. Tables

ATLAS adalah data-heavy application.

Desktop:
- Dynamic Table;
- sticky header bila panjang;
- pagination;
- sort;
- filter;
- row actions;
- empty state.

Column priority:
- identifier;
- nama;
- status;
- date/time;
- owner/requester;
- action.

Secondary actions gunakan overflow menu.

Mobile:
- prioritaskan key columns;
- detail pada detail page/panel;
- horizontal scroll hanya bila benar-benar perlu;
- workflow list boleh menjadi stacked list.

## 22. Search and Filters

Toolbar:
- search;
- filter status;
- filter periode;
- filter kategori;
- reset;
- primary action kanan.

Active filters tampil sebagai removable tags/chips. Jika memungkinkan, filter state disimpan di URL query.

## 23. Detail Page Pattern

Gunakan:
- breadcrumbs;
- page title;
- ticket/status;
- primary action;
- metadata;
- tabs jika perlu.

Borrowing detail tabs:
- Ringkasan
- Item
- Dokumen
- Riwayat
- Approval

## 24. Borrowing Workflow UI

Gunakan Progress Tracker:
1. Pengajuan
2. Verifikasi SSC
3. Approval Logistik
4. Persetujuan Kemahasiswaan
5. Pengambilan
6. Selesai

Riwayat detail sebagai activity list/timeline.

Partial approval tampilkan requested qty, approved qty, replacement item, dan notes. Gunakan label `Verifikasi SSC`, bukan `Approval SSC`.

## 25. SSC Verification UI

Layout:
- main: pengajuan dan dokumen;
- action area/panel: verifikasi.

Actions:
- Kembalikan untuk Perbaikan
- Teruskan ke Logistik

Return memerlukan notes. Forward dapat menampilkan summary sebelum confirm.

## 26. Logistics Approval UI

Header:
- ticket;
- pemohon;
- status;
- waktu.

Main:
- item table;
- availability;
- requested qty;
- approved qty;
- replacement selector;
- notes.

Actions:
- Setujui
- Tolak

Partial approval ditentukan pada item.

## 27. Student Affairs Approval UI

Tampilkan ringkasan, F01, keputusan Logistik, approved items, catatan, dan history.

Actions:
- Setujui
- Tolak
- Catatan

Tidak menampilkan edit availability.

## 28. QR and Handover UI

### Pemohon
- QR card;
- ticket;
- status;
- pickup instruction.

### Staf
- scan/input QR;
- validation result;
- borrower summary;
- item summary;
- F01 action;
- Generate F02;
- Generate F03;
- condition fields;
- Confirm Handover.

QR invalid menggunakan Section Message danger, bukan hanya modal generik.

## 29. Asset UI

List:
- kode;
- nama;
- kategori;
- lokasi;
- kondisi;
- availability;
- updated;
- action.

Detail:
- summary;
- status;
- location;
- history;
- borrowing reference.

Gunakan Lozenge untuk status.

## 30. Budget UI

Dashboard:
- total rencana;
- realisasi;
- sisa;
- periode.

List:
- kategori;
- periode;
- rencana;
- realisasi;
- variance;
- document.

Gunakan chart hanya jika membantu pemahaman.

## 31. Practicum and Attendance UI

### Schedule management
- table atau calendar/table hybrid;
- filter mata kuliah, kelas, ruang, asisten.

### Assistant mobile attendance
- jadwal saat ini;
- status waktu;
- status lokasi;
- Mulai Presensi;
- permission explanation;
- camera;
- preview;
- submit.

Jangan meminta lokasi sebelum user memulai presensi. Jika lokasi tidak sesuai, tampilkan status jelas dan jangan simpan sebagai valid.

## 32. BAP and Honorarium UI

BAP validation:
- schedule;
- attendance;
- photo;
- BAP;
- validation status;
- notes.

Honorarium:
- period;
- assistant;
- validated sessions;
- rate;
- total.

Gunakan format Rupiah konsisten.

## 33. Dashboard

Dashboard tidak boleh menjadi kumpulan card tanpa hierarchy.

Urutan:
1. page header;
2. primary task;
3. summary metric;
4. queue/list yang membutuhkan tindakan;
5. secondary summary.

Staf dapat melihat Menunggu Approval, Pengambilan Hari Ini, Aset Dipinjam, Presensi Perlu Validasi, approval terbaru, jadwal hari ini, dan ringkasan anggaran. Card metric cukup 3-4 per row desktop.

## 34. Empty States

Empty state menjawab apa yang kosong, mengapa, dan tindakan berikutnya.

Contoh:
`Belum ada pengajuan peminjaman.`
CTA: `Buat pengajuan`

## 35. Loading

Gunakan Skeleton untuk page/table/card, Spinner untuk action pendek, dan Progress untuk upload. Hindari full-page spinner jika skeleton lebih informatif.

## 36. Modal, Panel, Drawer

Modal untuk confirm, destructive action, atau short form. Right Panel untuk notification center dan optional quick detail. Full page untuk complex edit, long form, atau main approval workflow. Hindari modal di atas panel.

## 37. Responsive Breakpoints

| Breakpoint | Width |
|---|---|
| xxs | 320-479 |
| xs | 480-767 |
| s | 768-1023 |
| m | 1024-1439 |
| l | 1440-1767 |
| xl | 1768+ |

### Desktop >= 1024
- side nav inline;
- table penuh;
- optional panel.

### Tablet 768-1023
- side nav collapsed;
- overlay nav;
- content 1-2 kolom.

### Mobile < 768
- side nav overlay;
- single column;
- form full width;
- data table disederhanakan;
- tidak ada page overflow horizontal.

## 38. Mobile Action Pattern

Untuk task kritis, primary action harus mudah dijangkau. Sticky bottom action area boleh digunakan pada presensi atau approval dengan safe-area padding dan tanpa menutupi error/field.

## 39. Accessibility

Minimum:
- semantic HTML;
- keyboard navigation;
- visible focus;
- label form;
- accessible icon button name;
- contrast;
- reduced motion;
- text + color untuk status;
- error text;
- accessible table header;
- skip link;
- usable pada zoom 200%.

## 40. Motion

Interaction 50-150 ms. Transition 150-400 ms.

Gunakan untuk panel, modal, dan subtle state transition. Jangan gunakan animasi berulang, slow error feedback, atau motion sebagai satu-satunya indikator. Respect `prefers-reduced-motion`.

## 41. Content and Microcopy

Gunakan Bahasa Indonesia yang singkat dan spesifik.

Contoh label:
- Simpan
- Kirim Pengajuan
- Teruskan ke Logistik
- Kembalikan untuk Perbaikan
- Setujui
- Tolak
- Validasi QR
- Konfirmasi Serah Terima
- Tandai Dibaca

Hindari `OK` untuk action penting.

## 42. Date, Time, Number

- Date: `12 Oktober 2026`
- Compact: `12 Okt 2026`
- Time: `13.45 WIB`
- Currency: `Rp1.250.000`

## 43. Iconography

- 16 px inline;
- 20 px standard;
- 24 px feature/action tertentu;
- icon-only punya tooltip dan accessible label;
- jangan mencampur icon style tanpa aturan.

## 44. Landing Page Wireframe

```text
[Top Nav]
ATLAS        Fitur  Alur  Tentang  FAQ                     [Masuk]

[Hero]
Kelola operasional logistik dan praktikum dalam satu sistem
Supporting copy
[Masuk ke ATLAS] [Pelajari fitur]
                                         [Product preview]

[Benefits]
Aset Terstruktur | Peminjaman Terlacak | Presensi Tervalidasi | Laporan

[Modules]
Aset | Peminjaman | Approval | Anggaran | Praktikum | Honorarium

[Borrowing Workflow]
F01 -> SSC -> Logistik -> Kemahasiswaan -> QR -> Serah Terima

[Role-based Access]
Pemohon | SSC | Logistik | Kemahasiswaan | Asisten

[FAQ]

[CTA]
Siap mengakses ATLAS?
[Masuk]

[Footer]
```

## 45. Internal App Shell Wireframe

```text
+------------------------------------------------------------------+
| Toggle | ATLAS | Search(optional)              Bell | Theme | User |
+----------------------+-------------------------------------------+
| Side Navigation      | Breadcrumbs                               |
| Dashboard            | Page Title                   Primary CTA   |
| Aset                 +-------------------------------------------+
| Peminjaman           | Filters / Toolbar                         |
| ...                  | Main Data / Form / Workflow               |
+----------------------+-------------------------------------------+
```

## 46. Page Inventory by Role

### Public
- Landing
- Login
- Access Denied
- Error

### Pemohon
- Dashboard
- New Borrowing
- F01 Upload
- Borrowing List
- Borrowing Detail
- Correction
- QR Detail
- Notifications

### SSC
- Dashboard
- Verification Queue
- Verification Detail
- Verification History
- Notifications

### Staf Unit Logistik dan Laboratorium
- Dashboard
- Users
- User Detail
- Assets
- Asset Detail
- Approval Queue
- Logistics Approval Detail
- QR Validation
- Handover
- Return
- Budgets
- Budget Detail
- Practicum Schedules
- Attendance Validation
- BAP Validation
- Honorarium
- Reports
- Notifications

### Bagian Kemahasiswaan
- Dashboard
- Approval Queue
- Approval Detail
- Approval History
- Notifications

### Asisten Praktikum
- Dashboard
- My Schedule
- Attendance
- Attendance History

## 47. Component Mapping

| Screen | Primary pattern |
|---|---|
| Landing | Page layout, Button, Link, Stack/Box, FAQ |
| Login | Form, Text Field, Button, Inline Message |
| Dashboard | Page Header, Lozenge, Dynamic Table, Empty State |
| Asset list | Dynamic Table, Filters, Pagination |
| Borrowing detail | Page Header, Progress Tracker, Tabs, Lozenge |
| SSC verify | Form, Section Message, Buttons, Panel |
| Logistics approval | Table, Select, Text Field, Lozenge |
| Kemahasiswaan | Page Header, Section Message, Buttons |
| Notifications | Panel, Flag, Badge |
| Presensi | Form, Section Message, Button, Progress |
| Reports | Filters, Dynamic Table, Pagination |

## 48. Theme-specific QA

Light:
- subtle border;
- minimal shadow.

Dark:
- readable tables;
- selected/hover state distinct;
- status tetap jelas;
- overlay hierarchy jelas.

Test tables, modals, flags, lozenges, upload, QR, dan camera/presensi.

## 49. QR Contrast

QR harus berada pada scanning surface yang kontras. Dalam dark mode, QR dapat tetap memakai white surface agar mudah dipindai. Jangan gunakan gradient di belakang QR yang mengurangi readability.

## 50. Design QA Checklist

Sebelum page selesai:
- menggunakan token;
- heading hierarchy benar;
- primary action jelas;
- role restriction benar;
- loading ada;
- empty state ada;
- error state ada;
- keyboard usable;
- focus visible;
- mobile tidak overflow;
- dark mode diperiksa;
- notification konsisten;
- status text + semantic indicator;
- destructive action confirmed;
- copy konsisten.

## 51. Design System Governance

Jika komponen belum tersedia:
1. cek ADS component;
2. cek ADS pattern/foundation;
3. baru buat custom ATLAS component;
4. gunakan ADS tokens;
5. dokumentasikan state;
6. dokumentasikan responsive behavior;
7. dokumentasikan accessibility.

Jangan membuat one-off CSS tanpa alasan.

## 52. Recommended ATLAS Component Layer

```text
AtlasButton
AtlasField
AtlasStatus
AtlasTable
AtlasPageHeader
AtlasEmptyState
AtlasFlag
AtlasSectionMessage
AtlasPanel
AtlasProgressTracker
```

Pada CodeIgniter, komponen dapat berupa view partial/helper dengan CSS/JS tokenized. Jika React dipakai secara lokal, page business logic tidak boleh bergantung langsung pada detail package eksternal.

## 53. Official ADS References

- About: https://atlassian.design/get-started/about-atlassian-design-system
- Foundations: https://atlassian.design/foundations
- Tokens: https://atlassian.design/tokens/design-tokens/
- Typography: https://atlassian.design/foundations/typography
- Spacing: https://atlassian.design/foundations/spacing
- Radius: https://atlassian.design/foundations/radius
- Border: https://atlassian.design/foundations/border
- Components: https://atlassian.design/components
- Navigation: https://atlassian.design/components/navigation-system/layout
- Flag: https://atlassian.design/components/flag
- Motion: https://atlassian.design/foundations/motion

## 54. Final Design Direction

ATLAS harus terlihat seperti **modern enterprise SaaS product**, bukan portal akademik lama dan bukan marketing page yang terlalu dekoratif.

Ciri final:
- clean app shell;
- strong navigation;
- professional typography;
- semantic status;
- dense but readable tables;
- clear workflow;
- light/dark themes;
- responsive side nav;
- contextual panel;
- bottom-left transient flags;
- right-side persistent notification panel;
- minimal shadow;
- 6-8 px radius pada aplikasi;
- 8 px spacing system;
- mobile-first treatment untuk presensi;
- no visual clutter.

Landing page boleh lebih expressive. Setelah login, antarmuka fokus pada kecepatan kerja, data, dan workflow.
