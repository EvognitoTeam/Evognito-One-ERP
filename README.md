# EvognitoOne V2

EvognitoOne V2 adalah platform ERP modular berbasis web yang dirancang untuk menyatukan berbagai kebutuhan operasional perusahaan dalam satu workspace. Sistem ini menggunakan konsep **multi-tenant**, sehingga satu instalasi EvognitoOne dapat digunakan oleh banyak organisasi tanpa mencampurkan data antar perusahaan.

Berbeda dengan versi sebelumnya yang memisahkan aplikasi menjadi beberapa sistem, EvognitoOne V2 menggunakan pendekatan **modular monolith**. Setiap fungsi bisnis tetap dipisahkan sebagai modul, tetapi seluruh modul berada dalam satu aplikasi, menggunakan satu sistem login, satu identitas pengguna, satu struktur organisasi, dan satu workspace.

---

## Gambaran Sistem

Struktur umum EvognitoOne V2:

```text
EvognitoOne V2
│
├── Core
│   ├── Authentication
│   ├── Organizations
│   ├── Users
│   ├── Employees
│   ├── Branches
│   ├── Departments
│   ├── Positions
│   ├── Roles & Permissions
│   ├── Modules
│   ├── Subscription & Billing
│   ├── Notifications
│   ├── Audit Logs
│   └── Integrations
│
├── HRIS
│   ├── Employees
│   ├── Attendance
│   ├── Leave
│   ├── Calendar
│   ├── Holidays
│   ├── Payroll
│   └── HR Configuration
│
├── Sales Force
│   ├── Sales Team
│   ├── Customers
│   ├── Products
│   ├── Visit Management
│   ├── Visit Attendance
│   ├── Live Monitoring
│   ├── Sales Records
│   ├── Reports
│   └── Sales Configuration
│
├── Performance
│   ├── Master Data Import
│   ├── Products
│   ├── Employee Product Assignment
│   ├── KPI by Product
│   ├── Competencies
│   ├── Review Cycle
│   ├── Appraisals
│   ├── Approval
│   ├── Results
│   ├── Reports
│   └── Performance Configuration
│
└── Module lain yang dapat ditambahkan
    ├── LMS
    ├── Sites
    ├── IoT / Kansiku
    └── Modul operasional lainnya
```

---

## Konsep Multi-Tenant

Setiap perusahaan direpresentasikan sebagai **Organization**.

Contoh struktur URL:

```text
/{organization}
/{organization}/hris
/{organization}/sales
/{organization}/performance
```

Contoh:

```text
/acme-nusantara
/acme-nusantara/hris
/acme-nusantara/sales
/acme-nusantara/performance
```

Setiap data operasional memiliki konteks `organization_id`, sehingga data HRIS, Sales Force, Performance, dan konfigurasi organisasi tetap terisolasi dari organisasi lain.

User dapat memiliki akses ke lebih dari satu organisasi melalui membership, sedangkan Superadmin dapat mengelola platform secara global.

---

## Workspace

Setelah login, user masuk ke Workspace EvognitoOne.

Workspace berfungsi sebagai **App Launcher** untuk seluruh modul yang dimiliki organisasi.

Contoh aplikasi yang dapat muncul:

```text
HRIS
Performance
Sales Force
LMS
Sites
Kansiku
```

Setiap modul memiliki status entitlement. Modul yang belum dimiliki organisasi tetap dapat ditampilkan sebagai aplikasi terkunci atau `Payment Required`.

Internal module tidak menggunakan login atau OAuth terpisah. User tetap berada dalam session EvognitoOne yang sama saat berpindah dari HRIS ke Sales Force atau Performance.

---

# Core

Core adalah fondasi seluruh EvognitoOne V2.

Core bertanggung jawab terhadap data dan fungsi yang digunakan oleh banyak modul.

## Organization

Organization merupakan representasi perusahaan atau tenant.

Organization menyimpan konteks utama seperti:

```text
Nama organisasi
Slug
Status
Konfigurasi
Subscription
Module entitlement
```

Setiap modul selalu berjalan di dalam konteks organization aktif.

---

## User

User adalah identitas login.

User menyimpan informasi yang berkaitan dengan akun seperti:

```text
Nama
Email
Password
Status akun
Superadmin flag
```

User tidak sama dengan Employee.

Satu user dapat terhubung dengan employee tertentu untuk kebutuhan operasional seperti attendance, performance, atau sales.

---

## Employee

Employee adalah identitas karyawan dalam perusahaan.

Employee digunakan bersama oleh berbagai modul.

Contoh:

```text
Core Employee
├── HRIS
│   ├── Attendance
│   ├── Leave
│   └── Payroll
│
├── Performance
│   ├── KPI
│   └── Appraisal
│
└── Sales Force
    ├── Sales Team
    ├── Visit
    └── Tracking
```

Dengan konsep ini, sistem tidak membuat data karyawan baru untuk setiap modul.

---

## Struktur Organisasi

Core menyediakan struktur seperti:

```text
Organization
├── Branch
├── Department
├── Position
└── Employee
```

Modul lain hanya menggunakan struktur tersebut sesuai kebutuhan.

Sebagai contoh:

- HRIS menggunakan Position untuk jadwal kerja.
- Performance menggunakan Position untuk competency.
- Sales Force menggunakan Employee untuk Sales Team.

---

## Role & Permission

EvognitoOne menggunakan role dan permission agar akses tidak bergantung pada nama role yang di-hardcode.

Contoh permission:

```text
hris.attendance.view
hris.leave.approve
sales.visit.manage
sales.monitoring.view
performance.review
performance.approve_level1
performance.approve_level2
```

Dengan pendekatan ini, organisasi dapat memiliki role berbeda tanpa perlu mengubah source code.

---

# HRIS

HRIS digunakan untuk mengelola aktivitas sumber daya manusia.

---

## HRIS Overview

Dashboard HRIS menampilkan ringkasan kondisi tenaga kerja, attendance, leave, dan aktivitas penting lainnya.

Dashboard menjadi pusat navigasi menuju berbagai fungsi HR.

---

## Employee

Employee management menggunakan data Core Employee.

HRIS hanya menambahkan data khusus kepegawaian jika memang tidak relevan untuk modul lain.

Contoh:

```text
Employment Type
Join Date
End Date
Leave Quota
Debt Minutes
Supervisor
HR Notes
```

---

## Attendance

Attendance menyimpan satu record attendance per employee per hari.

Data utama attendance mencakup:

```text
Attendance Date
Clock In
Clock Out
Scheduled Clock In
Scheduled Clock Out
Status
Late Status
Late Minutes
Work Minutes
Method
GPS
Geofence
Photo
Work Location
Notes
```

Sumber attendance dapat berasal dari:

```text
Manual
Web
Mobile
Device
```

---

## Position Schedule

Jam kerja tidak disimpan langsung di Core Position.

HRIS memiliki konfigurasi jadwal tersendiri:

```text
Clock In
Clock Out
Mobile Position
Late Tolerance
```

Sehingga Position tetap dapat digunakan oleh modul lain tanpa membawa logika HRIS.

---

## Work Location & Geofence

HRIS mendukung lokasi kerja dengan koordinat GPS.

Setiap Work Location dapat memiliki:

```text
Latitude
Longitude
Radius
Geofence Enabled
Branch
Address
```

Karyawan dapat memiliki lokasi kerja yang ditentukan secara langsung.

Jika assignment khusus tidak tersedia, sistem dapat menggunakan fallback berdasarkan branch.

---

## Attendance Policy

Attendance Policy menentukan aturan attendance organisasi.

Contoh:

```text
Hari kerja
Wajib lokasi
Wajib foto masuk
Wajib foto pulang
Maksimum GPS accuracy
Mode geofence
Batas clock-in
Early clock-out
Attendance pada hari libur
Manual adjustment
```

---

## Daily Attendance Map

HRIS menyediakan peta attendance harian untuk seluruh employee.

Peta dapat menampilkan:

```text
Clock In
Clock Out
Work Location
Radius Geofence
Inside Geofence
Outside Geofence
Unknown Location
Route Clock In → Clock Out
```

Employee dapat dipilih dari daftar untuk memfokuskan map.

---

## Attendance Report

Attendance Report menggunakan konsep **filter-driven export**.

Filter dapat mencakup:

```text
Tanggal
Date Range
Employee
Branch
Position
Late
Status
Geofence
Method
Search
```

Export selalu mengikuti filter yang sedang aktif.

Tersedia:

```text
Detailed CSV
Summary CSV
```

---

## Leave

Leave digunakan untuk:

```text
Cuti
Izin
Sakit
```

Data Leave mencakup:

```text
Employee
Tanggal mulai
Tanggal selesai
Jam mulai / selesai
Jenis
Detail jenis
Alasan
Emergency information
Attachment
Status approval
Notes
```

Cuti tahunan dapat mengurangi leave quota setelah disetujui.

---

## Calendar

HRIS Calendar menggabungkan:

```text
Holiday
Approved Leave
Annual Leave Schedule
```

Sehingga HR dapat melihat kalender aktivitas karyawan secara terpusat.

---

## Annual Leave Schedule

Annual Leave Schedule memungkinkan HR menjadwalkan cuti tahunan terlebih dahulu.

Quota dapat langsung dikurangi saat schedule dibuat dan dikembalikan saat schedule dibatalkan.

---

## Holidays

Holiday dapat berupa:

```text
Tanggal tunggal
Rentang tanggal
Recurring holiday
```

Holiday ikut digunakan saat perhitungan payroll dan workday.

---

## Payroll

Payroll menggunakan data attendance, leave, holiday, dan compensation employee.

Komponen dasar payroll:

```text
Basic Salary
Fixed Allowance
Daily Bonus
Annual Bonus
Late Deduction
Absence Deduction
Other Deduction
Gross Salary
Net Salary
```

Payroll disusun berdasarkan Payroll Period.

---

# Sales Force

Sales Force digunakan untuk operasional sales lapangan.

---

## Sales Team

Sales Team menggunakan Core Employee.

Data khusus Sales Force dapat mencakup:

```text
Supervisor
Area
Field Sales Status
Daily Visit Target
Sales Settings
```

Satu employee tetap menjadi satu identitas yang sama dengan HRIS dan Performance.

---

## Customers

Customer menyimpan:

```text
Customer Code
Customer Name
Contact
Phone
Email
Address
City
Area
Latitude
Longitude
Geofence Radius
Status
Notes
```

Customer tidak dibuat unik hanya berdasarkan nama.

Dua customer dengan nama sama tetapi alamat atau lokasi berbeda tetap dapat disimpan.

---

## Products

Sales Force memiliki produk yang digunakan dalam aktivitas kunjungan dan sales record.

Satu Visit Schedule dapat memiliki lebih dari satu product.

---

## Visit Management

Visit Schedule digunakan untuk membuat jadwal kunjungan sales ke customer.

Data visit dapat mencakup:

```text
Salesman
Customer
Area
Start Date
End Date
Planned Start Time
Planned End Time
Products
Purpose
Notes
Status
```

Visit mendukung **date range**, bukan hanya satu tanggal.

Contoh:

```text
26-09-2026
sampai
01-10-2026
```

Hal ini berguna untuk assignment perjalanan luar kota.

---

## Visit Attendance

Salesman dapat melakukan:

```text
Visit Clock In
Visit Clock Out
```

Visit Attendance dapat memvalidasi:

```text
Tanggal schedule
Jam operasional
Status visit
GPS
Customer geofence
```

Visit yang sudah selesai tidak dapat di-clock-in ulang.

---

## Receipt / Kwitansi

Salesman dapat mengunggah kwitansi atau bukti dari mobile.

Kwitansi kemudian dapat dilihat dari detail visit di web.

---

## Live Monitoring

Live Monitoring menampilkan lokasi sales yang sedang bekerja di lapangan.

Sistem dirancang menggunakan **Adaptive Live Location Tracking**.

Artinya lokasi tidak dikirim dengan interval yang sama terus-menerus.

Interval dapat berubah berdasarkan kondisi:

```text
Bergerak
Diam
Jarak berpindah
Status visit
Jam operasional
Accuracy GPS
```

Tujuannya adalah menjaga tracking tetap berguna tanpa membuat perangkat cepat panas atau boros baterai.

---

## Daily Route & Tracking History

Data tracking dapat digunakan untuk:

```text
Live Map
Daily Route
Location History
Visit Context
Customer Context
```

Tracking tetap berada dalam konteks organization dan employee.

---

## Sales Records

Sales Records digunakan untuk data transaksi atau historical sales.

Data dapat diimport dari:

```text
Excel
CSV
```

Import dapat mencakup informasi seperti:

```text
Product
Customer
Invoice
Delivery Order
Salesman
Quantity
Packaging
Price
Gross Amount
DPP
Market Sector
Category
Department
```

Data yang sudah masuk dapat digunakan untuk analitik dan reporting.

---

# Performance Management

Performance digunakan untuk penilaian kinerja employee.

Versi V2 menggunakan pendekatan **KPI berbasis Produk**.

---

## Konsep KPI Berbasis Produk

Pada sistem lama, KPI dapat dihubungkan langsung ke PIC/karyawan.

Pada V2, konfigurasi KPI ditentukan berdasarkan Product.

Strukturnya:

```text
Employee
   ↓
Employee Product Assignment
   ↓
Product
   ↓
KPI Product Assignment
   ↓
Performance Appraisal
```

Contoh:

```text
KPI: Audit Staff

Produk A
├── Bobot: 20%
├── Target: 100
└── Target Waktu: 30 hari

Produk B
├── Bobot: 15%
├── Target: 150
└── Target Waktu: 25 hari
```

KPI yang sama dapat memiliki target dan bobot berbeda pada product yang berbeda.

---

## Product Master

Performance memiliki Product Master untuk mengelompokkan KPI.

Employee dapat memiliki lebih dari satu product.

Contoh:

```text
Employee A
├── Product A
├── Product B
└── Product C
```

Salah satu dapat ditandai sebagai **Primary Product**.

Primary Product menjadi sumber KPI utama saat appraisal dibuat.

---

## Employee Product Assignment

Relasi employee dengan product menyimpan:

```text
Employee
Product
Primary Product
Effective From
Effective Until
Status
```

Dengan effective date, perubahan tanggung jawab product employee dapat disimpan tanpa kehilangan historical context.

---

## KPI Library

KPI Library menyimpan definisi KPI secara umum.

Contoh:

```text
KPI Code
KPI Name
Description
Unit
Default Frequency
Default Calculation Type
```

Target dan bobot tidak harus disimpan di KPI Library karena nilai tersebut dapat berbeda untuk setiap Product.

---

## KPI by Product

Konfigurasi KPI per Product mencakup:

```text
Weight
Target
Target Time
Frequency
Calculation Type
Quantity Weight
Time Weight
Maximum Achievement
Notes
```

Constraint utama:

```text
Organization + KPI + Product
```

Sehingga satu KPI hanya memiliki satu konfigurasi aktif untuk satu Product.

---

## Perhitungan KPI

Performance mendukung dua tipe utama:

```text
HIGHER_BETTER
LOWER_BETTER
```

### Higher Better

Digunakan ketika semakin besar pencapaian semakin baik.

```text
Achievement = Actual / Target × 100
```

Contoh:

```text
Target = 100
Actual = 120

Achievement = 120%
```

### Lower Better

Digunakan ketika nilai lebih kecil lebih baik.

```text
Achievement = Target / Actual × 100
```

Contoh penggunaan:

```text
Lead Time
Error Rate
Complaint Rate
Processing Time
```

---

## Target Waktu

Selain nilai target, KPI dapat memiliki:

```text
Target Waktu
Aktual Waktu
```

Contoh:

```text
Target       = 100
Aktual       = 120

Target Waktu = 30 hari
Aktual Waktu = 25 hari
```

Sistem dapat menggabungkan capaian kuantitas dan waktu.

Default:

```text
Bobot Kuantitas = 70%
Bobot Waktu     = 30%
```

Konfigurasi ini dapat berbeda per KPI Product.

---

## Maximum Achievement

Achievement dapat dibatasi agar nilai tidak tumbuh tanpa batas.

Default:

```text
150%
```

Contoh:

```text
Actual Achievement = 180%
Max Achievement    = 150%

Nilai yang digunakan = 150%
```

---

## Competency

Competency tidak mengikuti Product.

Competency lebih cocok dikaitkan dengan:

```text
Position
Level
Organization
```

Contoh:

```text
Leadership
Communication
Problem Solving
Teamwork
Discipline
```

Setiap competency dapat memiliki:

```text
Standard Level
Weight
Definition
Level 1 Description
Level 2 Description
Level 3 Description
Level 4 Description
Level 5 Description
```

---

## Final Performance Score

Secara default:

```text
Competency = 40%
KPI        = 60%
```

Contoh:

```text
Competency Achievement = 85
KPI Achievement        = 92

Competency Total
= 85 × 40%
= 34

KPI Total
= 92 × 60%
= 55.2

Final Score
= 89.2
```

Bobot tersebut dapat diubah pada Performance Configuration.

---

## Appraisal

Appraisal adalah proses penilaian seorang employee dalam suatu review cycle.

Saat appraisal dibuat, sistem mengambil konfigurasi employee dan product saat itu lalu membuat **snapshot**.

Snapshot dapat mencakup:

```text
Employee Name
Employee Number
Position
Department
Product
KPI
KPI Weight
KPI Target
KPI Target Time
Calculation Type
Competency
```

Tujuan snapshot adalah menjaga historical appraisal.

Jika konfigurasi KPI Product berubah setelah penilaian dibuat, penilaian lama tidak ikut berubah.

---

## Review Cycle

Review Cycle menentukan periode penilaian.

Contoh:

```text
Annual Review 2026

Period:
01-01-2026
sampai
31-12-2026

Review Window:
01-01-2027
sampai
31-01-2027
```

Status cycle dapat berupa:

```text
Draft
Active
Review
Closed
```

---

## Approval Workflow

Workflow Performance dapat mengikuti:

```text
Draft
  ↓
Reviewer Level 1
  ↓
Reviewer Level 2
  ↓
Finalized
  ↓
Employee Acknowledgement
```

Level approval dapat dikontrol melalui Performance Policy.

---

## Employee Acknowledgement

Setelah appraisal final, employee dapat memberikan acknowledgement.

Data acknowledgement dapat mencakup:

```text
Employee Comment
Signature
Signed At
Status
```

Acknowledgement menunjukkan bahwa employee sudah menerima atau mengetahui hasil penilaian.

---

# Master Data Import

Performance memiliki halaman khusus **Import Data**.

Import tidak bergantung pada nama sheet tertentu.

Sistem memeriksa seluruh sheet dalam workbook dan memilih sheet berdasarkan **header yang dikenali**.

Contoh header yang digunakan:

```text
NIK Karyawan
Nama
Nama Perusahaan
Kode Perusahaan
Jabatan
Level
Atasan Level 1
Atasan Level 2
Produk
Home Based
Status Karyawan
```

Urutan kolom tidak harus tetap.

Selama nama header dikenali, importer dapat memetakan kolom yang sesuai.

---

## Staging Data

Data Excel tidak langsung seluruhnya dimasukkan ke Core Employee.

Data terlebih dahulu disimpan ke staging.

Staging dapat menyimpan:

```text
NIK
Nama
Perusahaan
Kode Perusahaan
Jabatan
Level
Atasan Level 1
Atasan Level 2
Produk
Home Based
Area
Status Karyawan
Tanggal Masuk
Nomor Telepon
Email
Raw Data
```

Pendekatan staging memungkinkan data divalidasi dan digunakan ulang untuk proses berikutnya.

---

## Import Product

Nilai pada kolom Product digunakan untuk membentuk Product Master.

Nama Product dinormalisasi agar variasi kapitalisasi tidak menghasilkan duplikasi.

Contoh:

```text
SHELL MCO
Shell MCO
shell mco
```

dapat dikenali sebagai Product yang sama.

Untuk typo atau nama yang ambigu, sistem tidak otomatis menggabungkannya agar data tidak salah dikoreksi.

---

## Import Employee

Staging yang sama nantinya dapat digunakan untuk membuat atau memperbarui Core Employee.

Alur yang disarankan:

```text
Pass 1
├── Create / Update Employee
├── Resolve Position
└── Resolve Product

Pass 2
├── Resolve Atasan Level 1
├── Resolve Atasan Level 2
└── Create Employee Product Assignment
```

Atasan tidak langsung dihubungkan pada pass pertama karena data atasan dapat muncul pada baris setelah bawahannya.

---

# Localization

EvognitoOne V2 mendukung multi-language.

Bahasa utama saat ini:

```text
Bahasa Indonesia
English
```

Language adalah setting global, bukan per module.

Saat bahasa berubah, HRIS, Sales Force, Performance, Workspace, dan modul lain dapat mengikuti locale yang sama.

Nama organisasi, nama employee, customer, product, dan data yang diinput user tidak diterjemahkan otomatis.

---

# Billing & Module Entitlement

EvognitoOne menggunakan sistem module entitlement.

Setiap Organization dapat memiliki kombinasi module berbeda.

Contoh:

```text
Organization A
├── HRIS
└── Performance

Organization B
├── HRIS
├── Sales Force
└── Performance
```

Module dapat berasal dari:

```text
Subscription Plan
Standalone Module
Bundle
```

Billing dapat menggunakan pendekatan:

```text
Fixed Price
Per User
Hybrid
Additional Fee
```

Entitlement menentukan apakah module dapat dibuka oleh organization.

---

# Notification

Core menyediakan notification layer yang dapat digunakan lintas modul.

Contoh penggunaan:

```text
Leave membutuhkan approval
Performance appraisal menunggu reviewer
Sales visit berubah
Billing akan jatuh tempo
Attendance membutuhkan koreksi
```

Dengan notification berada di Core, setiap module tidak perlu membangun sistem notification sendiri.

---

# Audit Log

Audit Log digunakan untuk mencatat perubahan penting.

Contoh:

```text
User
Organization
Action
Entity
Entity ID
Old Value
New Value
IP Address
Timestamp
```

Audit Log penting untuk proses seperti:

```text
Approval
Payroll
Performance
Employee Data
Billing
Configuration
```

---

# Mobile API

EvognitoOne dapat menyediakan API untuk aplikasi Flutter.

API digunakan untuk kebutuhan lapangan seperti:

```text
Login
Attendance
Visit Schedule
Visit Attendance
Live Location Tracking
Receipt Upload
Notifications
```

Mobile API menggunakan identity dan organization yang sama dengan aplikasi web.

API bukan aplikasi bisnis terpisah, tetapi interface menuju Core dan module yang sama.

---

# Prinsip Arsitektur EvognitoOne V2

EvognitoOne V2 mengikuti beberapa prinsip utama.

### Satu Identitas

Employee tidak dibuat ulang pada setiap module.

```text
1 Core Employee
→ HRIS
→ Sales Force
→ Performance
→ LMS
```

### Satu Workspace

User berpindah module tanpa login ulang.

### Data Module Tetap Terpisah

Core hanya menyimpan data yang benar-benar digunakan bersama.

Data yang hanya dimiliki HRIS tetap berada di HRIS.

Data Sales tetap berada di Sales.

Data Performance tetap berada di Performance.

### Tenant Isolation

Semua data bisnis harus selalu terikat dengan Organization.

### Historical Snapshot

Data transaksi historis seperti Payroll atau Performance Appraisal harus menyimpan snapshot agar perubahan master data di masa depan tidak mengubah data lama.

### Configurable Business Rules

Aturan yang mungkin berbeda antar perusahaan sebaiknya menjadi configuration atau policy, bukan hardcoded.

Contoh:

```text
Attendance Policy
Performance Policy
Tracking Policy
Visit Policy
Payroll Configuration
```

---

# Ringkasan

EvognitoOne V2 dibangun sebagai satu platform operasional perusahaan dengan konsep modular.

```text
EvognitoOne Core
│
├── Identity
├── Organization
├── Employee
├── Access Control
├── Billing
├── Notification
├── Audit
│
├── HRIS
├── Sales Force
├── Performance
├── LMS
├── Sites
└── Future Modules
```

Tujuan utamanya adalah agar perusahaan tidak perlu mengelola sistem terpisah untuk HR, attendance, sales, performance, dan aktivitas operasional lain.

Seluruh module berbagi satu struktur organisasi, satu employee identity, satu authentication system, satu workspace, dan satu sumber data utama, tetapi masing-masing module tetap memiliki business logic dan data domain sendiri.
