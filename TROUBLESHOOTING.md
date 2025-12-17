# QUICK FIX - Setup Database & URL

## 🚨 Error "Not Found" - Solusi:

### Masalah 1: Database Belum Di-Import

#### Cara Import Database:

**Opsi A: Via phpMyAdmin (Recommended)**

1. **Buka phpMyAdmin**
   - URL: `http://localhost:8000/phpmyadmin` atau `http://localhost/phpmyadmin`

2. **Buat Database Baru**
   - Klik tab "Databases"
   - Nama database: `e_admin_tu`
   - Collation: `utf8mb4_unicode_ci`
   - Klik "Create"

3. **Import Schema**
   - Klik database `e_admin_tu` yang baru dibuat
   - Klik tab "Import"
   - Klik "Choose File"
   - Pilih file: `C:\xampp\htdocs\e-TU\database\schema.sql`
   - Scroll ke bawah, klik "Go"
   - Tunggu sampai selesai (ada notifikasi sukses)

**Opsi B: Via Command Line (MySQL)**

```bash
# Buka Command Prompt
cd C:\xampp\mysql\bin

# Login ke MySQL
mysql -u root -p

# (Jika diminta password, tekan Enter saja kalau tidak ada password)

# Buat database
CREATE DATABASE e_admin_tu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Keluar dari MySQL
exit

# Import schema
mysql -u root -p e_admin_tu < C:\xampp\htdocs\e-TU\database\schema.sql
```

**Opsi C: Manual Execute**

1. Buka phpMyAdmin
2. Pilih database `e_admin_tu`
3. Klik tab "SQL"
4. Buka file `schema.sql` dengan Notepad
5. Copy semua isi file
6. Paste ke SQL editor
7. Klik "Go"

---

### Masalah 2: URL Port 8000

Anda menggunakan **port 8000** bukan port default 80.

**URL yang benar:**

❌ SALAH: `http://localhost/e-TU/`
✅ BENAR: `http://localhost:8000/e-TU/`

**Untuk Login:**
```
http://localhost:8000/e-TU/login.php
```

**Untuk Dashboard:**
```
http://localhost:8000/e-TU/index.php
```

**Untuk Surat Masuk:**
```
http://localhost:8000/e-TU/modules/persuratan/surat-masuk.php
```

---

### Masalah 3: Apache DocumentRoot

Jika tetap error, cek DocumentRoot Apache Anda:

1. **Buka XAMPP Control Panel**
2. **Klik "Config"** di Apache
3. **Pilih "httpd.conf"**
4. **Cari** `DocumentRoot` (Ctrl+F)
5. **Pastikan** ada line seperti:
   ```
   DocumentRoot "C:/xampp/htdocs"
   ```

6. Jika berbeda, sesuaikan path aplikasi atau pindahkan folder `e-TU`

---

## ✅ Checklist Before Testing

- [ ] Apache running (hijau di XAMPP)
- [ ] MySQL running (hijau di XAMPP)
- [ ] Database `e_admin_tu` sudah dibuat
- [ ] Schema.sql sudah di-import
- [ ] URL menggunakan port 8000: `http://localhost:8000/e-TU/login.php`

---

## 🧪 Test Steps

1. **Test Login**
   - Buka: `http://localhost:8000/e-TU/login.php`
   - Login: `admin` / `admin123`
   - Harus redirect ke dashboard

2. **Test Dashboard**
   - Buka: `http://localhost:8000/e-TU/dashboard.php`
   - Harus muncul dashboard dengan statistik

3. **Test Landing Page**
   - Buka: `http://localhost:8000/e-TU/index.php`
   - Harus muncul landing page publik dengan fitur Cek Keuangan

4. **Test Pembayaran Siswa**
   - Buka: `http://localhost:8000/e-TU/modules/keuangan/pembayaran.php`
   - Harus muncul halaman dengan tabel tagihan siswa

---

## 🔍 Jika Masih Error

**Cek Error Log:**
```
C:\xampp\apache\logs\error.log
```

**Atau screenshot error dan kirim ke saya!**

---

## 💡 Quick Database Check

Setelah import, cek apakah tabel sudah ada:

1. Buka phpMyAdmin
2. Klik database `e_admin_tu`
3. Harus ada tabel-tabel:
   - `users`, `roles`, `user_roles`
   - `siswa`, `kelas`, `mutasi_siswa`
   - `pegawai`, `jabatan`, `cuti`, `riwayat_pegawai`
   - `surat_masuk`, `surat_keluar`, `disposisi`, `template_surat`
   - `tagihan_siswa`, `pembayaran_log`
   - `transaksi_kas`, `rab`
   - `landing_content`, `buku_tamu`, `alumni_requests`
   - dll (total ~30+ tabel)

---

**Sudah import database dan pakai URL yang benar?** Test lagi! 🚀
