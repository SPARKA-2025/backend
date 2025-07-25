# API Slot Parkir Documentation

## Overview
Terdapat dua endpoint untuk membuat slot parkir dengan tujuan yang berbeda. Kedua endpoint menggunakan **form-data** untuk konsistensi dengan project lainnya.

### 1. Endpoint Berdasarkan Fakultas
**URL:** `POST /api/admin/slot-parkir/{id_fakultas}/create`

**Deskripsi:** Membuat slot parkir berdasarkan fakultas (id_blok sebagai fakultas)

**Parameter URL:**
- `id_fakultas`: ID fakultas/blok

**Content-Type:** `multipart/form-data`

**Request Body (Form-Data):**
```
slot_name: A1
x: 10
y: 20
id_part: 5
status: Kosong
```

**Response Success:**
```json
{
  "status": "success",
  "pesan": "Data Berhasil Ditambahkan",
  "data": [
    {
      "id": 1,
      "slot_name": "A1",
      "x": "10",
      "y": "20",
      "id_blok": 1,
      "id_part": 5,
      "status": "Kosong"
    }
  ]
}
```

### 2. Endpoint Berdasarkan Part dan Blok Spesifik
**URL:** `POST /api/slot-parkir/{id_part}/{id_blok}/create-all-slot`

**Deskripsi:** Membuat slot parkir untuk part dan blok yang spesifik

**Parameter URL:**
- `id_part`: ID part spesifik
- `id_blok`: ID blok spesifik

**Content-Type:** `multipart/form-data`

**Request Body (Form-Data):**
```
slot_name: B1
x: 30
y: 40
status: Kosong
```

**Response Success:**
```json
{
  "status": "success",
  "pesan": "Semua data berhasil ditambahkan",
  "data": [
    {
      "id": 2,
      "slot_name": "B1",
      "x": "30",
      "y": "40",
      "id_blok": 2,
      "id_part": 5,
      "status": "Kosong"
    }
  ]
}
```

## Perbedaan Utama

| Aspek | Endpoint Fakultas | Endpoint Part-Blok |
|-------|------------------|--------------------|
| **URL Pattern** | `/admin/slot-parkir/{id_fakultas}/create` | `/slot-parkir/{id_part}/{id_blok}/create-all-slot` |
| **Content-Type** | `multipart/form-data` | `multipart/form-data` |
| **Parameter** | id_fakultas di URL | id_part dan id_blok di URL |
| **id_part** | Harus disertakan dalam form-data | Otomatis dari URL |
| **Validasi** | Validasi konsistensi id_part dengan id_blok | Validasi konsistensi id_part dengan id_blok |
| **Use Case** | Membuat slot untuk fakultas tertentu | Membuat slot untuk kombinasi part-blok spesifik |

## Konflik Routing

**Tidak ada konflik routing** karena:
1. Pattern URL berbeda:
   - `/admin/slot-parkir/{id}/create` (1 parameter)
   - `/slot-parkir/{id}/{id}/create-all-slot` (2 parameter + suffix berbeda)

2. Prefix berbeda:
   - Endpoint pertama menggunakan prefix `/admin/`
   - Endpoint kedua tanpa prefix `/admin/`

## Validasi Data Consistency

Kedua endpoint menggunakan validasi yang sama di model `Slot_Parkir`:
- Memastikan `id_part` konsisten dengan `id_blok`
- Mencegah data inkonsisten masuk ke database
- Memberikan error message yang jelas jika terjadi inkonsistensi

## Error Responses

**Validation Error:**
```json
{
  "status": "error",
  "pesan": "Data Gagal Ditambahkan",
  "data": "Data slot tidak konsisten. id_part tidak sesuai dengan id_blok"
}
```

**Not Found Error:**
```json
{
  "status": "error",
  "pesan": "Data blok tidak ditemukan"
}
```

## Catatan Penting

1. **Tidak Ada Konflik Routing**
   - Kedua endpoint memiliki pola URL yang berbeda
   - Endpoint pertama menggunakan prefix `/admin/slot-parkir`
   - Endpoint kedua menggunakan prefix `/slot-parkir`

2. **Validasi Data**
   - Kedua endpoint menggunakan model `Slot_Parkir` yang sama
   - Validasi konsistensi data dilakukan untuk memastikan integritas data
   - Jika data tidak konsisten, akan mengembalikan pesan error yang jelas

3. **Format Request**
   - Request body menggunakan format `multipart/form-data`
   - Data dikirim sebagai individual fields seperti pola UserController
   - Field yang diperlukan: `slot_name`, `x`, `y`
   - Field `id_part` diperlukan untuk endpoint fakultas
   - Field `status` bersifat opsional, default "Kosong"

4. **Error Handling**
   - Jika terjadi error, response akan berisi pesan error yang jelas
   - Status code 400 (Bad Request) untuk error validasi
   - Status code 500 (Internal Server Error) untuk error server

## Rekomendasi Penggunaan

1. **Gunakan endpoint fakultas** (`/admin/slot-parkir/{id_fakultas}/create`) ketika:
   - Membuat slot dari perspektif admin fakultas
   - Perlu fleksibilitas memilih part yang berbeda dalam satu request
   - Menggunakan interface admin

2. **Gunakan endpoint part-blok** (`/slot-parkir/{id_part}/{id_blok}/create-all-slot`) ketika:
   - Membuat slot untuk kombinasi part-blok yang sudah pasti
   - Batch creation untuk area spesifik
   - Integration dengan sistem eksternal yang sudah mengetahui part dan blok

## Testing dengan Postman

### Endpoint 1: Faculty-based Creation
**Method:** POST  
**URL:** `http://localhost/api/admin/slot-parkir/1/create`  
**Content-Type:** `multipart/form-data`

**Form-Data:**
```
slot_name: A1
x: 10
y: 20
id_part: 5
status: Kosong
```

### Endpoint 2: Part-Block Specific Creation
**Method:** POST  
**URL:** `http://localhost/api/slot-parkir/5/1/create-all-slot`  
**Content-Type:** `multipart/form-data`

**Form-Data:**
```
slot_name: B1
x: 30
y: 40
status: Kosong
```