````text
# CODING GUIDE COMPLETENESS AUDIT & REVISION

Saya menemukan masalah pada Coding Guide yang Anda hasilkan.

Pada:

RKT-010 — Buat View statistik/rktunit.blade.php

Anda memberikan instruksi seperti:

"Implementasikan Card 2: Statistik Status"

tetapi tidak memberikan code Blade konkret yang dapat langsung saya copy-paste.

Ini tidak sesuai dengan requirement Coding Guide yang telah kita tetapkan:

> CODE-FIRST, EXPLANATION-ALONGSIDE

Saya ingin Anda melakukan audit dan revisi terhadap SELURUH Coding Guide.

---

## 1. TUJUAN AUDIT

Pastikan setiap task yang membutuhkan implementasi code benar-benar menyediakan:

1. File yang harus dibuka/diubah
2. Posisi code
3. Code konkret siap copy-paste
4. Penjelasan logic
5. Variable/data source yang digunakan
6. Expected result
7. Verification
8. Checkpoint
9. Git commit

Jangan hanya memberikan instruksi seperti:

"Implementasikan..."
"Tambahkan..."
"Buat..."
"Gunakan pattern..."

jika sebenarnya saya membutuhkan code untuk melakukan pekerjaan tersebut.

---

# 2. KHUSUS RKT-010

Lakukan review penuh terhadap:

RKT-010 — Buat View statistik/rktunit.blade.php

Pastikan setiap komponen UI berikut memiliki CODE SIAP COPY-PASTE:

### Card 1 — KPI Utama

Berikan:

- markup Blade lengkap
- variable yang digunakan
- formatting rupiah
- formatting percentage
- conditional state jika ada
- penjelasan

### Card 2 — Statistik Status

WAJIB berikan:

- markup Blade lengkap
- setiap status yang ditampilkan
- variable yang digunakan
- cara variable tersebut berasal dari Controller
- badge/label/class yang digunakan
- count/value yang ditampilkan
- percentage jika memang ada
- empty state jika diperlukan

Jangan hanya menulis:

"Implementasikan Card 2: Statistik Status."

Saya membutuhkan code aktual.

### Card 3 — Top/Bottom Unit

Berikan markup Blade lengkap untuk:

- Top 5
- Bottom 5

Jika keduanya menggunakan component/pattern yang sama, berikan code lengkap dan jelaskan bagian yang berbeda.

### Card 4 — Distribusi RAB

Berikan markup Blade lengkap.

### Card 5 — Distribusi Sumber Dana

Berikan markup Blade lengkap.

### Card 6 — Unit dengan Realisasi >100%

Berikan markup Blade lengkap.

### DataTables

Berikan markup:

- table
- thead
- tbody/pattern yang digunakan
- id/class
- filter container
- action container

sesuai pattern existing codebase.

### Filter

Berikan markup lengkap untuk seluruh filter yang memang menjadi requirement.

### Card Actions

Berikan markup lengkap jika memang digunakan.

### Empty/Error State

Jika existing project memiliki pattern untuk empty/error state, gunakan pattern tersebut dan berikan code konkretnya.

---

# 3. JANGAN MENGARANG UI PATTERN

Sebelum memberikan code:

Periksa existing:

`statistik/dayaserap`

dan view/component terkait.

Identifikasi:

- struktur card
- class CSS
- icon
- badge
- typography
- spacing
- table
- filter
- action
- empty state

Gunakan pattern existing.

Jika pattern tertentu belum dapat diverifikasi:

`NEEDS VERIFICATION`

Jangan mengarang class atau component baru.

---

# 4. AUDIT SELURUH CODING GUIDE

Jangan hanya memperbaiki RKT-010.

Periksa RKT-001 sampai RKT-015.

Cari setiap instruksi yang berbentuk:

"Implementasikan X"

tetapi tidak menyediakan code konkret.

Untuk setiap temuan, ubah menjadi:

### CODE SIAP COPY-PASTE

```php
...
````

atau:

```blade
...
```

atau:

```javascript
...
```

atau:

```sql
...
```

sesuai kebutuhan.

---

# 5. BEDAKAN TIGA KONDISI

## A. Code dapat diberikan

Berikan code lengkap.

## B. Code bergantung pada hasil task sebelumnya

Berikan code dengan variable/output yang sudah ditentukan sebelumnya dan jelaskan dependency.

## C. Code belum dapat diberikan karena belum terverifikasi

Jangan mengarang.

Gunakan:

`NEEDS VERIFICATION`

dan jelaskan apa yang harus diperiksa.

---

# 6. KHUSUS VARIABLE BACKEND → BLADE

Buat mapping eksplisit.

Contoh:

| Blade Variable    | Controller Source           | Digunakan Untuk       |
| ----------------- | --------------------------- | --------------------- |
| `$totalBiaya`     | `$stats['total_biaya']`     | Card KPI              |
| `$totalRealisasi` | `$stats['total_realisasi']` | Card KPI              |
| `$statusStats`    | `$stats['status']`          | Card Statistik Status |

Gunakan nama variable aktual dari Coding Guide/Plan.

Jangan membuat nama variable baru hanya untuk kenyamanan.

---

# 7. SETIAP CARD HARUS MEMILIKI CONTRACT

Untuk setiap card:

### Input

Variable apa yang dibutuhkan?

### Transformation

Apakah Blade melakukan formatting atau conditional logic?

### Output

Apa yang terlihat user?

Contoh:

```text
Controller
    ↓
$statusStats
    ↓
Blade
    ↓
Card Statistik Status
```

---

# 8. CODE-FIRST RULE

Jika saya dapat langsung copy-paste code dari Coding Guide, maka berikan code.

Jangan membuat saya menerjemahkan:

"buat card dengan struktur tiga kolom"

menjadi HTML sendiri.

Coding Guide harus menghilangkan pekerjaan coding yang sebenarnya dapat dilakukan oleh Anda.

---

# 9. FINAL AUDIT TABLE

Di akhir revisi, buat tabel:

| Task    | Memerlukan Code? | Code Tersedia? | Siap Copy-Paste? | Status |
| ------- | ---------------- | -------------- | ---------------- | ------ |
| RKT-007 | Ya               | Ya             | Ya               | READY  |
| RKT-008 | Ya               | Ya             | Ya               | READY  |
| RKT-009 | Ya               | Ya             | Ya               | READY  |
| RKT-010 | Ya               | Ya             | Ya               | READY  |
| ...     | ...              | ...            | ...              | ...    |

Target:

> Semua task yang membutuhkan implementation code harus berstatus READY.

Jika ada yang belum READY, jelaskan alasannya.

---

# 10. JANGAN CODING DI REPOSITORY

Jangan mengubah source code.

Jangan melakukan commit.

Hanya revisi Coding Guide.

---

# OUTPUT

Berikan:

1. Audit Findings
2. Revised Coding Guide
3. RKT-010 lengkap dengan seluruh code Blade
4. Mapping Controller → Blade
5. Final Completeness Audit
6. Commit Sequence

Gunakan Bahasa Indonesia.

Tetap pertahankan prinsip:

> CODE-FIRST, EXPLANATION-ALONGSIDE.

```

```
