# API Project

# Fitur
- **Refresh Token**: Expired setelah **7 minggu**. => bisa diubah di file jwt.php pada folder config
- **Akses Token**: Expired setelah **2 jam**. => bisa diubah di file jwt.php pada folder config
- **Refresh Akses Token**: Untuk mendapatkan akses token baru, kirimkan refresh token ke endpoint berikut:
  ```http
  POST /api/access/refresh-token
  ```
  Jika pengguna api ini memasukkan aksestoken untuk refreshtoken maka tidak akan bisa!
  Setelah melakukan refresh token, maka aksestoken yang lama tidak bisa digunakan!

- **Reset password User**: untuk melakukan reset password dengan token yang dikirimkan ke email asli mengunakan smtp gmail.
- **Get User yang Sedang Login**: Harus menggunakan akses token.
- **Manajemen Stok**: Stok alat akan berkurang secara otomatis jika disewa.
- **Cache Optimization**: Memanfaatkan cache untuk meningkatkan performa API.
- **Middleware**: Cek apakah yang dimasukkan di bearer untuk mengelola data adalah aksestoken, jika refreshtoken maka gagal, karena refreshtoken hanya untuk mendapatkan aksestoken baru.


# Instalasi & Setup
1. Clone repositori:
   ```bash
   git clone https://github.com/xredbintang/my-api-finalproject.git
   cd repository
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Konfigurasi `.env`:
   ```env
   APP_KEY=
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```
   dan juga

    ```env
    MAIL_MAILER=smtp
    MAIL_SCHEME=null
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=emailanda@gmail.com
    MAIL_PASSWORD=app password dari google //tanpa spasi dan "-"
    MAIL_FROM_ADDRESS="emailanda@gmail.com"
    MAIL_FROM_NAME="RESET PASSWORD" //bisa disesuaikan
    ```
    Cara mendapatkan app password : Masuk ke setting google account / manage, pada bagian security, search "APP PASSWORD" lalu masukkan nama projek (terserah) dan anda akan langsung         mendapatkan app passwordnya 
   
4. Jalankan migrasi:
   ```bash
   php artisan migrate
   ```
5. Jalankan server:
   ```bash
   php artisan serve
   ```

# Autentikasi
API ini menggunakan **JWT (JSON Web Token)** untuk autentikasi. Semua request ke endpoint yang membutuhkan autentikasi harus menyertakan **Bearer Token** di header:
```http
Authorization: Bearer {your_access_token}
```

# Endpoint
##  Refresh Token
**Mendapatkan akses token baru menggunakan refresh token**
```http
POST /api/access/refresh-token
```
**Body:**
```json
{
  "refresh_token": "your_refresh_token"
}
```

#  Reset password (User)
```http
POST /api/access/password-reset/request
```
**Body:**
```json
{
  "email": youremail@gmail.com
}
```
```http
POST /api/access/password-reset/
```
**Body:**
```json
{
    "token": token dari email / request
    "email": youremail@gmail.com
    "password": password baru
    "password_confrimation": konfirmasi password baru
}
```

# Get User yang Sedang Login
**Mendapatkan informasi user yang sedang login**
```http
GET /api/user
```
**Header:**
```http
Authorization: Bearer {your_access_token}
```

#  Sewa Alat pada alat detail (Mengurangi Stok)
```http
POST /api/detail/penyewaan
```
**Body:**
```json
{
  "penyewaan_detail_jumlah": 2
}
```

# yang Digunakan
- Laravel
- MySQL
- JWT Authentication

# Ini adalah tugas final dari sekawan
