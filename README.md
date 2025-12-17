# E-ADMIN TU MA AL IHSAN - Sistem Informasi Intranet Staf Tata Usaha

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![Version](https://img.shields.io/badge/version-1.0.0-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)

## 📋 Tentang Aplikasi

**E-ADMIN TU MA AL IHSAN** adalah sistem informasi intranet berbasis web yang dirancang khusus untuk mendigitalisasi 8 tugas pokok dan fungsi (Tupoksi) Tata Usaha di institusi pendidikan.

### ✨ Fitur Utama

- 🎯 **8 Modul Utama** sesuai Tupoksi TU
- ✉️ **Surat Generator Otomatis** dengan template dinamis
- 📨 **Disposisi Digital** untuk tracking surat masuk
- 🔐 **Role-Based Access Control (RBAC)**
- 📂 **Arsip Digital Terpusat** dengan full-text search
- 📊 **Dashboard Analitik** real-time
- 🎨 **UI Modern** dengan Tailwind CSS

## 🚀 Teknologi

- **Backend**: PHP 8.1+ (OOP)
- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript
- **Database**: MySQL 8.0 / MariaDB 10.6
- **Authentication**: PHP Session-based

## 📦 Instalasi

### Persyaratan Sistem

- PHP 8.1 atau lebih tinggi
- MySQL 8.0 / MariaDB 10.6 atau lebih tinggi
- Apache/Nginx Web Server
- XAMPP/WAMP/LAMP (untuk development)

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/your-repo/e-admin-tu.git
   cd e-admin-tu
   ```

2. **Konfigurasi Database**
   - Buat database baru di MySQL/MariaDB
   - Import file `database/schema.sql`
   ```bash
   mysql -u root -p e_admin_tu < database/schema.sql
   ```

3. **Konfigurasi Koneksi**
   - Edit file `config/database.php`
   - Sesuaikan kredensial database Anda

4. **Jalankan Aplikasi**
   - Akses melalui browser: `http://localhost/e-TU/`

5. **Login Demo**
   - Username: `admin`
   - Password: `admin123`

## 📚 Struktur Proyek

```
e-TU/
├── config/
│   ├── database.php          # Konfigurasi database
│   └── session.php            # Session management
├── includes/
│   ├── header.php             # Header template
│   └── sidebar.php            # Sidebar navigation
├── modules/
│   ├── kepegawaian/           # Modul Kepegawaian
│   ├── keuangan/              # Modul Keuangan
│   ├── sarpras/               # Modul Sarana Prasarana
│   ├── kehumasan/             # Modul Kehumasan
│   ├── persuratan/            # Modul Persuratan & Kearsipan
│   ├── kesiswaan/             # Modul Kesiswaan
│   ├── layanan/               # Modul Layanan Khusus
│   └── tik/                   # Modul TIK & Pengaturan
├── database/
│   └── schema.sql             # Database schema
├── index.php                  # Dashboard homepage
├── login.php                  # Login page
└── logout.php                 # Logout handler
```

## 🎯 Modul Aplikasi

### 1. Kepegawaian
- Data Pegawai
- Manajemen Cuti
- Riwayat Kepegawaian
- Absensi Pegawai

### 2. Keuangan
- **Pembayaran Siswa** dengan riwayat lengkap
- **Buku Kas** terintegrasi otomatis
- **Kuitansi Digital** siap cetak
- Rencana Anggaran Biaya (RAB)
- Laporan Keuangan
- Cek Tagihan Publik (Landing Page)

### 3. Sarana Prasarana
- Inventaris Aset
- Peminjaman Aset
- Maintenance Aset
- Laporan Inventaris

### 4. Kehumasan
- Agenda Kegiatan
- Dokumentasi
- Press Release
- Bank Data Media

### 5. Persuratan & Kearsipan ⭐
- **Surat Generator** (Fitur Unggulan)
- Surat Masuk/Keluar
- Disposisi Digital
- Arsip Digital
- Template Surat

### 6. Kesiswaan
- Data Siswa
- Manajemen Kelas
- Mutasi Siswa
- Presensi

### 7. Layanan Khusus
- Perpustakaan
- UKS (Unit Kesehatan Sekolah)
- Kantin
- Koperasi

### 8. TIK & Pengaturan
- Manajemen User
- Role & Permission
- Log Aktivitas
- Pengaturan Sistem

### 9. Portal Informasi (Landing Page)
- **Konten Dinamis** (Hero, About, Contact)
- **Cek Keuangan Siswa** dengan toggle admin
- **Layanan Surat Online** untuk alumni/publik
- **Buku Tamu Digital**
- Manajemen gambar dan teks

## 🔒 Keamanan

- ✅ Password hashing dengan bcrypt
- ✅ Prepared statements untuk mencegah SQL Injection
- ✅ Session timeout (30 menit idle)
- ✅ CSRF protection
- ✅ Role-based access control

## 📖 Dokumentasi

Dokumentasi lengkap tersedia di folder `docs/`:
- [Functional Requirements Specification](docs/functional_requirements_specification.md)

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan buat pull request atau laporkan issue.

## 📄 Lisensi

Project ini dilisensikan under MIT License.

## 👨‍💻 Developer

Dikembangkan dengan ❤️ untuk digitalisasi administrasi pendidikan.

## 📞 Kontak & Support

Untuk pertanyaan dan support, silakan buka issue di repository ini.

---

**E-ADMIN TU MA AL IHSAN** - Mendigitalisasi Administrasi, Meningkatkan Efisiensi
