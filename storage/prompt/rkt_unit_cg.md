Siap. Saya sudah cek **Updated Plan** yang baru. Menurut saya, **sudah layak dijadikan basis Coding Guide**. Struktur dependency-nya sudah jelas dari reconnaissance → lineage → business rule → backend → DataTables → view → JS → route → menu → testing → documentation. Bahkan Definition of Done dan recommended coding order sudah tersedia.

Ada **dua catatan kecil** yang saya tidak anggap blocker:

1. Pada RKT-004 tertulis pemeriksaan nilai unik field `jenis (rab_type)`. Ini perlu Devin luruskan saat Coding Guide agar tidak salah antara nama field `rab_type` dan `jenis`.
2. RKT-011 menyebut data controller tersedia di `window object`; ini sebaiknya **diverifikasi terhadap pattern aktual `dayaserap`**, bukan dianggap sebagai keharusan.

Yang paling penting, Plan sudah menangkap konflik yang sangat penting: `jumlah_biaya_revisi` ternyata tidak tersedia sehingga tidak boleh kita paksakan ke implementasi. Ini tanda bahwa proses Update Plan memang bekerja sebagaimana yang kita inginkan.

Jadi **kita tidak perlu update Plan lagi**. Sekarang waktunya membuat **Coding Guide**.

---

# Prompt Final Devin — Generate Coding Guide

Saya sarankan Anda copy-paste prompt berikut ke Devin **dalam Plan Mode**.

````text
# GENERATE CODING GUIDE — STATISTIK RKT UNIT

## ROLE

Bertindak sebagai:

- Senior Laravel Engineer
- Software Architect
- Technical Writer
- Coding Mentor

Saya akan melakukan coding sendiri berdasarkan guide yang Anda hasilkan.

Anda TIDAK mengerjakan implementasi di repository.

Tugas Anda adalah menghasilkan Coding Guide yang sangat praktis sehingga saya dapat:

1. membaca instruksi,
2. copy-paste code/query yang disediakan,
3. menyesuaikan hanya jika memang diperlukan,
4. menjalankan verification,
5. memahami logika di balik code/query,
6. menyelesaikan satu task,
7. melakukan Git commit menggunakan commit message yang sudah Anda rekomendasikan.

---

# BAHASA

Seluruh Coding Guide WAJIB menggunakan Bahasa Indonesia.

Gunakan istilah teknis asli untuk:

- nama file
- nama class
- nama method
- nama variable
- nama tabel
- nama field
- nama route
- Laravel
- Blade
- JavaScript
- DataTables
- AJAX
- SQL
- Query Builder
- Git
- dan identifier teknis lainnya.

Jangan menerjemahkan identifier atau source code.

Penjelasan, reasoning, komentar, instruksi, dan dokumentasi harus menggunakan Bahasa Indonesia.

---

# SUMBER UTAMA

Gunakan:

`statistik-rktunit-updated-plan-c4c5ad.md`

sebagai sumber utama.

Plan tersebut sudah melalui proses review terhadap codebase.

Jangan kembali membuat architecture plan dari nol.

Jangan mengubah business requirement tanpa alasan.

Jika saat membuat Coding Guide Anda menemukan sesuatu yang belum terverifikasi dari codebase, JANGAN mengarang.

Gunakan label:

`NEEDS VERIFICATION`

dan jelaskan apa yang harus diperiksa sebelum coding dilanjutkan.

---

# TUJUAN CODING GUIDE

Saya ingin Coding Guide yang bersifat:

> CODE-FIRST, EXPLANATION-ALONGSIDE

Artinya saya tidak ingin tutorial teoritis yang panjang sebelum code.

Untuk setiap task:

1. jelaskan apa yang dilakukan,
2. jelaskan mengapa dilakukan,
3. berikan code/query konkret,
4. jelaskan code/query tersebut,
5. berikan expected result,
6. berikan langkah verification,
7. berikan checkpoint,
8. berikan rekomendasi Git commit.

Saya harus dapat mengikuti guide dari RKT-001 sampai selesai tanpa harus menebak-nebak langkah berikutnya.

---

# PRINSIP PENTING

## 1. Jangan memberikan pseudocode jika code konkret dapat diberikan

Jika pattern existing codebase sudah diketahui, berikan implementation code yang mengikuti pattern tersebut.

## 2. Jangan mengarang codebase

Jika sebuah file, method, helper, variable, route, model, atau pattern belum diverifikasi:

Jangan mengasumsikannya.

Tandai:

`NEEDS VERIFICATION`

## 3. Reuse existing pattern

Prioritaskan pattern dari:

`/statistik/dayaserap`

dan:

`RekatByUnitController`

sebagaimana ditentukan dalam Updated Plan.

Jangan membuat architecture baru jika pattern existing sudah memadai.

## 4. Jangan over-engineer

Implementasikan hanya yang dibutuhkan oleh Plan.

## 5. Jangan menghilangkan verification

Setiap task harus dapat diverifikasi secara independen.

---

# STRUKTUR CODING GUIDE

Buat Coding Guide berdasarkan task yang sudah ada:

- RKT-001
- RKT-002
- RKT-003
- RKT-004
- RKT-005
- RKT-006
- RKT-007
- RKT-008
- RKT-009
- RKT-010
- RKT-011
- RKT-012
- RKT-013
- RKT-014
- RKT-015

Jangan mengubah numbering kecuali ada alasan teknis yang sangat kuat.

Jika satu task ternyata terlalu besar untuk diimplementasikan dan diverifikasi secara aman, pecah menjadi subtask:

RKT-007.1
RKT-007.2
RKT-007.3

Tetapi pertahankan RKT-007 sebagai parent task.

---

# FORMAT WAJIB SETIAP TASK

Gunakan struktur berikut.

---

# RKT-XXX — [Nama Task]

## 1. Tujuan

Jelaskan secara singkat apa yang akan dicapai.

---

## 2. Konteks & Logika

Jelaskan:

- masalah yang sedang diselesaikan,
- posisi task dalam arsitektur,
- mengapa task ini diperlukan,
- hubungan dengan task sebelumnya.

Gunakan diagram sederhana jika membantu.

Contoh:

Data Backup
→ Base Query
→ Aggregation
→ Controller
→ View

---

## 3. Dependency

Tuliskan task yang harus selesai terlebih dahulu.

Contoh:

`Dependency: RKT-003, RKT-005, RKT-006`

Jelaskan mengapa dependency tersebut diperlukan.

---

## 4. File yang Digunakan

Tampilkan:

### File existing yang harus diperiksa

```text
path/to/file
````

### File yang akan dibuat/diubah

```text
path/to/file
```

Jelaskan fungsi masing-masing file.

---

## 5. Langkah Implementasi

Pecah menjadi langkah kecil.

Contoh:

### Step 1 — Buka Controller

Buka:

```text
app/Http/Controllers/StatistikController.php
```

Cari:

```php
public function dayaSerap(...)
```

Perhatikan pattern berikut:

- ...
- ...
- ...

### Step 2 — Tambahkan Method

Tambahkan method setelah:

```php
...
```

Gunakan code berikut:

```php
...
```

---

# 6. CODE SIAP COPY-PASTE

Ini bagian yang sangat penting.

Berikan source code konkret yang dapat langsung saya copy-paste.

Jangan hanya memberikan:

```text
// implement query here
```

Jika codebase memungkinkan code konkret diberikan, berikan code lengkap.

Untuk code yang panjang, pecah menjadi blok yang logis.

Setiap code block harus diberi konteks:

### Paste di:

```text
app/Http/Controllers/StatistikController.php
```

### Posisi:

Setelah method:

```text
dayaSerap()
```

atau jelaskan posisi secara spesifik.

---

# 7. PENJELASAN CODE

Setelah setiap blok code, jelaskan:

### Apa yang dilakukan?

...

### Mengapa dilakukan?

...

### Bagaimana logikanya?

...

### Business rule apa yang diterapkan?

...

### Data grain apa yang digunakan?

Contoh:

> Sebelum agregasi: satu row = satu detail RKT.
>
> Setelah GROUP BY: satu row = satu unit.

Ini WAJIB dijelaskan untuk query agregasi.

---

# 8. PENJELASAN QUERY

Untuk setiap SQL/Query Builder yang signifikan, jelaskan secara khusus:

### Source

Tabel apa yang digunakan?

### JOIN

Mengapa JOIN dilakukan?

### Filter

Mengapa WHERE condition tersebut digunakan?

### Aggregation

Apa yang di-SUM / COUNT / GROUP BY?

### Grain

Pada level apa data berada?

### Risiko duplicate

Apakah JOIN berpotensi menggandakan row?

Jika iya, jelaskan cara mendeteksinya.

### Expected result

Berapa kira-kira bentuk hasil query?

---

# 9. EXPECTED RESULT

Setelah code dijalankan, jelaskan hasil yang seharusnya terlihat.

Contoh:

```text
$backup
→ 1 record backup terbaru
```

atau:

```text
Collection
→ 1 row per unit kerja
```

atau:

```text
HTTP 200
→ JSON DataTables valid
```

---

# 10. VERIFICATION

Berikan langkah verification yang konkret.

Jika query:

- cara menjalankan query,
- sample checking,
- expected result,
- cara membandingkan dengan data existing.

Jika controller:

- cara memanggil method,
- expected output.

Jika view:

- URL,
- apa yang harus terlihat.

Jika JavaScript:

- browser console,
- network request,
- DataTables response.

Jika route:

- URL yang harus dibuka.

---

# 11. TROUBLESHOOTING

Untuk task yang memiliki risiko, berikan minimal:

### Symptom

...

### Kemungkinan penyebab

...

### Cara memeriksa

...

### Solusi

...

Jangan membuat troubleshooting generik.

Fokus pada risiko yang memang relevan dengan task.

---

# 12. CHECKPOINT

Berikan checklist yang bisa langsung dimasukkan ke Notion.

Contoh:

- [ ] File sudah diubah
- [ ] Code berhasil dijalankan
- [ ] Query menghasilkan data
- [ ] Hasil diverifikasi
- [ ] Tidak ada error di log
- [ ] Tidak ada regression

---

# 13. GIT COMMIT

INI WAJIB.

Setiap task harus memiliki rekomendasi Git commit.

Jangan membuat saya mengarang commit message sendiri.

Gunakan Conventional Commits.

Format:

### Commit Type

```text
feat
```

### Commit Scope

```text
statistik
```

### Commit Message

```text
feat(statistik): add RKT unit base query
```

### Alasan

Jelaskan mengapa commit message tersebut tepat.

### Command

```bash
git add <file>
git commit -m "feat(statistik): add RKT unit base query"
```

### Catatan

Commit harus merepresentasikan perubahan aktual dari task.

Jangan membuat commit message generik seperti:

```text
update RKT
```

Gunakan message yang spesifik.

---

# ATURAN KHUSUS GIT

## Satu Task = Satu Checkpoint

Sebisa mungkin:

`RKT-007 selesai`
→ verification selesai
→ commit.

Kemudian:

`RKT-008 selesai`
→ verification selesai
→ commit.

Dengan demikian Git history akan menggambarkan perkembangan implementasi.

Jika task bersifat review/reconnaissance dan tidak mengubah repository:

jelaskan:

> `No commit required`

Jangan memaksakan commit yang tidak memiliki perubahan source/documentation.

Jika task menghasilkan perubahan dokumentasi yang memang akan disimpan di repository, berikan commit message untuk perubahan tersebut.

---

# 14. ROLLBACK / RECOVERY

Untuk task yang berisiko, tambahkan:

### Risiko

...

### Jika gagal

...

### Recovery

...

Jika perubahan sudah di-commit:

```bash
git revert <commit>
```

Jangan menyarankan destructive command seperti `git reset --hard` kecuali benar-benar diperlukan dan jelaskan risikonya.

---

# ATURAN KHUSUS QUERY DATABASE

Untuk semua query yang menyentuh data finansial:

WAJIB menjelaskan:

1. source table,
2. relationship,
3. filtering,
4. grain,
5. aggregation,
6. null handling,
7. duplicate risk,
8. formula,
9. verification.

Jangan menganggap hasil SUM benar hanya karena query berhasil.

Harus ada cara memvalidasi hasil agregasi.

---

# ATURAN KHUSUS STATISTIK FINANSIAL

Jelaskan formula secara eksplisit.

Contoh:

```text
Total Realisasi
= SUM(jumlah_amprahan + jumlah_realisasi)
```

Jika menggunakan:

```text
COALESCE(...)
```

jelaskan mengapa.

Untuk persentase:

```text
Persentase Realisasi
= Total Realisasi / Total Biaya × 100
```

Jelaskan juga:

- division by zero,
- NULL,
- realisasi > 100%.

Jangan mengubah formula yang telah ditentukan Plan tanpa menandai perubahan tersebut.

---

# ATURAN KHUSUS RKT-005

RKT-005 adalah business-rule checkpoint.

Jangan langsung coding sebelum formula dari RKT-005 jelas.

Pastikan:

- field yang digunakan benar-benar tersedia,
- formula dapat dihitung,
- formula konsisten dengan business rule.

Field `jumlah_biaya_revisi` yang telah dinyatakan tidak tersedia jangan digunakan kembali.

Jika penggantinya adalah `sisa_pengalihan`, jelaskan dengan jelas bahwa itu merupakan perubahan definisi dan jangan menyamakan kedua field tersebut tanpa dasar.

---

# ATURAN KHUSUS RKT-006

Filter Draft harus mengikuti hasil verifikasi RKT-006.

Jangan memilih antara:

- `is_draft`
- logic semula-menjadi

berdasarkan asumsi.

Gunakan hasil verification dari task tersebut.

Jika belum dapat dipastikan, berhenti dan tandai:

`NEEDS VERIFICATION`

---

# ATURAN KHUSUS RKT-007

Method:

```text
rktUnit()
```

adalah foundation utama.

Coding Guide harus memecah implementasinya menjadi langkah yang aman.

Jangan memberikan satu blok controller raksasa tanpa penjelasan.

Pisahkan secara konseptual:

1. backup selection,
2. base dataset,
3. master JOIN,
4. aggregation,
5. status statistics,
6. top/bottom units,
7. > 100% units,
8. data untuk view.

Jika ternyata lebih aman dipecah menjadi subtask, gunakan:

```text
RKT-007.1
RKT-007.2
RKT-007.3
...
```

---

# ATURAN KHUSUS RKT-008

Untuk DataTables:

jelaskan:

- parameter filter,
- mapping filter,
- query condition,
- JSON response,
- pagination,
- search,
- ordering.

Jika existing DataTables menggunakan server-side processing, ikuti pattern tersebut.

Jangan mengasumsikan client-side atau server-side tanpa memeriksa existing implementation.

---

# ATURAN KHUSUS RKT-009

JavaScript harus mengikuti pattern:

```text
statistik-dayaserap.js
```

Jelaskan bagian mana yang:

1. direuse,
2. dimodifikasi,
3. baru.

Untuk AJAX:

jelaskan:

```text
Dropdown
→ AJAX request
→ Controller
→ JSON
→ DataTables
```

---

# ATURAN KHUSUS RKT-010 & RKT-011

Untuk Blade dan JavaScript integration:

Jangan mengasumsikan `window object` diperlukan.

Verifikasi pattern aktual existing page terlebih dahulu.

Jika pattern existing tidak menggunakan `window object`, gunakan pattern existing.

---

# ATURAN KHUSUS RKT-012 & RKT-013

Berikan code lengkap yang dapat langsung digunakan untuk:

- route,
- menu.

Tetapi tetap periksa pattern existing terlebih dahulu.

---

# RKT-014 TESTING

Testing jangan hanya berupa:

"pastikan halaman berjalan."

Buat checklist konkret:

## Functional

- [ ] Page dapat dibuka
- [ ] Backup terbaru benar
- [ ] Semua card muncul
- [ ] Filter bekerja
- [ ] DataTables bekerja
- [ ] Search bekerja
- [ ] Pagination bekerja
- [ ] Responsive bekerja
- [ ] Card-action bekerja

## Data Accuracy

- [ ] Total biaya
- [ ] Total realisasi
- [ ] Total sisa
- [ ] Persentase
- [ ] Top 5
- [ ] Bottom 5
- [ ] RAB distribution
- [ ] Funding distribution
- [ ] > 100%

## Edge Cases

- [ ] NULL
- [ ] 0
- [ ] realisasi = pagu
- [ ] realisasi > pagu
- [ ] tidak ada realisasi
- [ ] draft
- [ ] backup kosong

Untuk setiap test penting, jelaskan expected result.

---

# RKT-015 DOCUMENTATION

Dokumentasi harus diverifikasi terhadap implementation final.

Jangan menyalin query/formula dari Plan jika implementation aktual berbeda.

Card dokumentasi harus mencerminkan:

- query aktual,
- formula aktual,
- tabel aktual,
- business rule aktual.

---

# NOTION-READY OUTPUT

Pada akhir setiap task, berikan blok checklist yang dapat langsung saya copy ke Notion.

Format:

```text
## RKT-007 — Buat Method rktUnit()

- [ ] Baca pattern dayaSerap()
- [ ] Buat base query
- [ ] Verifikasi backup terbaru
- [ ] Implementasikan aggregation
- [ ] Implementasikan status statistics
- [ ] Implementasikan top/bottom unit
- [ ] Implementasikan >100%
- [ ] Test hasil
- [ ] Review log
- [ ] Commit
```

---

# FINAL SUMMARY

Setelah seluruh Coding Guide selesai, berikan:

## 1. Coding Sequence

Urutan RKT task.

## 2. Dependency Map

Diagram dependency.

## 3. Commit Sequence

Daftar seluruh commit yang direkomendasikan.

Contoh:

```text
RKT-001 → no commit
RKT-002 → no commit
RKT-003 → chore(statistik): document RKT backup fields
RKT-004 → chore(statistik): document RAB type values
RKT-005 → docs(statistik): define RKT statistics formulas
RKT-006 → docs(statistik): define RKT draft filter rule
RKT-007 → feat(statistik): add RKT unit statistics
...
```

Jangan membuat commit jika task memang tidak menghasilkan repository change.

## 4. Definition of Done

Gunakan Definition of Done dari Updated Plan.

## 5. Risk Register

Daftar risiko teknis yang masih harus diperhatikan.

---

# IMPORTANT FINAL RULE

Jangan melakukan coding di repository.

Jangan membuat atau mengubah file.

Jangan menghasilkan patch.

Jangan melakukan commit.

Tugas Anda hanya menghasilkan:

> **CODING GUIDE LENGKAP, BAHASA INDONESIA, CODE-FIRST, SIAP COPY-PASTE, DENGAN PENJELASAN LOGIKA, VERIFICATION, CHECKPOINT, DAN REKOMENDASI GIT COMMIT UNTUK SETIAP TASK.**

```

## Kenapa saya puas dengan Plan ini

Ada satu perubahan yang menurut saya sangat bagus dibanding plan awal: sekarang kita **tidak pura-pura sudah tahu semua business rule**. Contohnya `jumlah_biaya_revisi` sudah ditandai conflict, `rab_type` masih perlu diverifikasi, dan draft backup punya business-rule checkpoint sendiri. :contentReference[oaicite:4]{index=4}

Itu penting karena Coding Guide nanti akan mengubah keputusan Plan menjadi **code nyata**. Kalau sebuah keputusan masih abu-abu, lebih baik Devin mengatakan *NEEDS VERIFICATION* daripada memberi kita code yang terlihat meyakinkan tetapi salah.

Dan saya sangat suka dependency `RKT-003 → RKT-005 → RKT-007`: field diverifikasi dulu, formula ditentukan, baru controller dibuat. :contentReference[oaicite:5]{index=5} :contentReference[oaicite:6]{index=6}

### Satu prinsip yang ingin saya pertahankan

Jangan mengejar Coding Guide yang **pendek**. Kejar Coding Guide yang **menghilangkan keputusan yang tidak perlu Anda pikirkan lagi**.

Target kita adalah:

> **Anda membuka Notion → baca satu task → copy code → pahami 5 menit → test → checklist → commit → lanjut task berikutnya.**

Itu akan membuat proses coding RKT ini jauh lebih terstruktur dan sekaligus memberi Anda **Git history yang bersih dan bisa ditelusuri**.
```
