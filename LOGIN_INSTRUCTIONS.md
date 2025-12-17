## INSTRUKSI LOGIN E-ADMIN TU MA AL IHSAN

### ✅ Cara Login (Tanpa Database)

Aplikasi ini bisa dijalankan **tanpa perlu setup database** untuk demo!

#### 📌 Langkah-langkah:

1. **Pastikan XAMPP/WAMP Apache sudah running**
   - Start Apache dari XAMPP Control Panel
   - MySQL tidak wajib untuk demo login

2. **Akses halaman login**
   - Buka browser
   - Ketik: `http://localhost/e-TU/login.php`
   
3. **Login dengan credentials demo:**
   ```
   Username: admin
   Password: admin123
   ```

4. **Klik tombol "Masuk"**
   - Anda akan langsung diarahkan ke dashboard
   - Flash message "Selamat datang, Administrator!" akan muncul

---

### 🔍 Troubleshooting

#### Problem: "Tidak bisa login"

**Solusi 1: Pastikan path sudah benar**
- URL harus: `http://localhost/e-TU/login.php`
- Bukan: `http://localhost/e-TU` (tanpa login.php)

**Solusi 2: Pastikan Apache running**
```bash
# Cek di XAMPP Control Panel
# Apache harus berstatus "Running" (hijau)
```

**Solusi 3: Clear browser cache**
- Tekan Ctrl + Shift + Delete
- Clear cache and cookies
- Refresh halaman

**Solusi 4: Pastikan credentials benar**
- Username: `admin` (huruf kecil semua)
- Password: `admin123` (huruf kecil semua, tanpa spasi)

**Solusi 5: Cek error PHP**
- Buka file: `C:\xampp\apache\logs\error.log`
- Lihat error terakhir

---

### 🗄️ Setup Database (Opsional)

Jika ingin menggunakan database MySQL:

1. **Buka phpMyAdmin**
   - URL: `http://localhost/phpmyadmin`

2. **Buat database baru**
   - Nama: `e_admin_tu`
   - Collation: `utf8mb4_unicode_ci`

3. **Import schema**
   - Klik tab "Import"
   - Choose file: `C:\xampp\htdocs\e-TU\database\schema.sql`
   - Klik "Go"

4. **Konfigurasi database** (jika perlu)
   - Edit: `C:\xampp\htdocs\e-TU\config\database.php`
   - Sesuaikan:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'e_admin_tu');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // Kosongkan jika tidak ada password
     ```

5. **Login dengan database**
   - Setelah import, gunakan credentials yang sama
   - Username: `admin`
   - Password: `admin123`

---

### 📱 Demo Mode Features

Dalam mode demo (tanpa database), Anda tetap bisa:
- ✅ Login dan logout
- ✅ Melihat dashboard
- ✅ Navigasi semua menu
- ✅ Test UI/UX responsive design

Yang tidak bisa dilakukan tanpa database:
- ❌ Menyimpan data baru
- ❌ Edit data
- ❌ Generate surat aktual
- ❌ CRUD operations

---

### 🎯 Quick Test Login

1. Buka: `http://localhost/e-TU/login.php`
2. Isi form:
   - Username: `admin`
   - Password: `admin123`
3. Klik "Masuk"
4. ✅ Berhasil jika redirect ke dashboard

---

### 📞 Masih Gagal Login?

**Cek hal berikut:**

1. ✅ XAMPP Apache running?
2. ✅ URL benar: `http://localhost/e-TU/login.php`?
3. ✅ Credentials: `admin` / `admin123` (case sensitive)?
4. ✅ Browser sudah di-refresh (F5)?
5. ✅ Tidak ada error di console browser (F12)?

**Error yang umum:**

| Error | Solusi |
|-------|--------|
| "This site can't be reached" | Apache belum running |
| "Not Found" | Path folder salah, cek `htdocs/e-TU/` |
| "Username atau password salah" | Typo di credentials |
| "Session timeout" | Refresh halaman dan login lagi |

---

Jika masih ada masalah, screenshot error yang muncul dan beritahu saya! 🙏
