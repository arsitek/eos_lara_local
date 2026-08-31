# Daya Serap — Data, Formula & Business Logic Documentation

## 1. Purpose

The `/statistik/dayaserap` dashboard is a budget execution monitoring dashboard that provides:

- **Budget execution tracking**: Monitoring of pagu allocation vs actual expenditure per unit and funding source
- **External reporting support**: Data formatted for reporting to Kementerian/Dewan Pengawas
- **Daya serap analysis**: Understanding of remaining budget (sisa) and realization rates
- **Unit-level aggregation**: Summary statistics across all units with percentage calculations

**Important distinction**: This dashboard is NOT automatically identical in grain or meaning to `/statistik/rktunit`. The two dashboards serve different purposes:

- **dayaSerap**: Budget execution monitoring (per unit + sumber dana)
- **rktUnit**: Activity-based performance tracking (per unit + sumber dana + jenis RAB + kegiatan)

Differences in reported numbers are acceptable and expected due to different data grains and business definitions.

---

## 2. Data Scope

### Backup Selection Logic

The dashboard operates on a single backup snapshot selected by the following criteria:

```sql
SELECT id, keterangan, tahun
FROM tb_duplikasi_rkat
WHERE is_deleted = false
  AND duplikasi_ke = 0
  AND peruntukan = 'RKAT Awal'
ORDER BY created_at DESC
LIMIT 1
```

### Year Derivation

For backup data, the year is extracted from the backup tahun field:

```php
$backupTahun = $backupInfo[0]->tahun;  // e.g., "Definitif_2026"
$backupTahunAngka = end(explode('_', $backupTahun));  // e.g., "2026"
```

For current year data (not backup), the year is obtained from `getTahunData()` helper.

### Current Year Data

The dashboard also supports viewing current year data (not from backup) via `getAlokasiData()` method, which uses:
- `tb_alokasi` for allocation data
- `getRekapRealisasi($tahun, 'unit')` for realization data

---

## 3. Source Tables

### tb_duplikasi_rkat
- **Role**: Backup metadata and selection
- **Type**: Metadata/config table
- **Important keys**: `id`, `keterangan`, `tahun`, `created_at`
- **Relevant filters**: `is_deleted`, `duplikasi_ke`, `peruntukan`

### tb_backup_alokasi
- **Role**: Allocation (pagu) data per unit and sumber dana for backup snapshots
- **Type**: Fact table (allocation facts)
- **Important keys**: `id_duplikasi`, `kode_sd`, `idunit`, `pagu`, `pagu_tambahan`
- **Relevant filters**: `id_duplikasi`

### tb_alokasi
- **Role**: Current year allocation (pagu) data per unit and sumber dana
- **Type**: Fact table (allocation facts)
- **Important keys**: `kd_sumberdana`, `unit_kerja`, `pagu`, `pagu_tambahan`, `tahun`
- **Relevant filters**: `is_deleted`, `tahun`

### tb_sumberdana
- **Role**: Master table for funding sources (sumber dana)
- **Type**: Dimension table
- **Important keys**: `kd_sumberdana`, `sumberdana`, `tahun`
- **Relevant filters**: `tahun`, `is_show`, `is_deleted`
- **Cardinality concerns**: Contains data across multiple years - year-aware JOIN recommended

### tb_unit_api
- **Role**: Master table for organizational units
- **Type**: Dimension table
- **Important keys**: `idunit`, `nama`, `tahun`
- **Relevant filters**: `tahun`
- **Cardinality concerns**: Each `idunit` has one record per year - year-aware JOIN recommended

### tb_backup_rkat
- **Role**: Header table for RKT backup snapshots
- **Type**: Fact table (backup metadata)
- **Important keys**: `id`, `id_duplikasi`, `tahun`, `sd` (sumber dana), `idunit`
- **Relevant filters**: `id_duplikasi`, `tahun`

### tb_backup_rkat_detail
- **Role**: Detail table containing realization data (jumlah_amprahan, jumlah_realisasi)
- **Type**: Fact table (realization facts)
- **Important keys**: `id_rekat`, `id_duplikasi`, `jumlah_amprahan`, `jumlah_realisasi`
- **Relevant filters**: `id_duplikasi`, `is_deleted`

---

## 4. Relational Grain

### Data Levels

The Daya Serap dashboard operates at a coarser grain than RKT Unit:

| Level | Description |
|-------|-------------|
| Unit-Sumber Dana | Primary grain for Daya Serap (unit.idunit + backupRkat.sd) |
| Unit Aggregation | Final unit-level summary (aggregated across sumber dana) |
| Institution Aggregation | Final total across all units |

### GROUP BY Grain

The main SQL query in `getAlokasiBackup()` groups by:

```sql
GROUP BY unit.idunit, backupRkat.sd
```

This means:
- Each combination of unit and sumber dana is one analytical group
- This is coarser than rktUnit which also groups by `jenis` and `id_mak`
- Multiple detail records from `tb_backup_rkat_detail` are aggregated into one unit-sumber dana combination

### Comparison with rktUnit

| Aspect | dayaSerap | rktUnit |
|--------|-----------|---------|
| **Grain** | unit + sumber dana | unit + sumber dana + jenis RAB + kegiatan (id_mak) |
| **Pagu Source** | tb_backup_alokasi | tb_backup_rkat_detail (SUM(jumlah_biaya)) |
| **Realization Source** | tb_backup_rkat_detail (SUM) | tb_backup_rkat_detail (SUM) |
| **Purpose** | Budget execution monitoring | Activity-based performance tracking |

---

## 5. Financial Formulas

### Actual Realization

```php
realisasi = SUM(COALESCE(jumlah_amprahan, 0) + COALESCE(jumlah_realisasi, 0))
```

This is calculated at the unit-sumber dana grain via SQL GROUP BY.

### Pagu Alokasi

```php
pagu_alokasi = COALESCE(pagu, 0) + COALESCE(pagu_tambahan, 0)
```

For backup data, this comes from `tb_backup_alokasi`.
For current year data, this comes from `tb_alokasi`.

### Daya Serap (Remaining Budget)

```php
daya_serap = pagu_alokasi - realisasi
```

Note: This is the inverse of "sisa" in rktUnit. In dayaSerap, "daya serap" means the remaining budget that can still be used.

### Persentase (Percentage) per Unit-Sumber Dana

```php
persentase = (realisasi / pagu_alokasi) × 100
```

### Rata-rata Persentase per Unit

```php
rata_rata_persentase = Σ(persentase) / jumlah_sumber_dana
```

**Important**: This is an **arithmetic average** of the sumber dana percentages, NOT a weighted aggregate.

### Total per Unit

```php
total_pagu_alokasi = Σ(pagu_alokasi) for all sumber dana
total_realisasi = Σ(realisasi) for all sumber dana
total_daya_serap = Σ(daya_serap) for all sumber dana
```

### Rata-rata Persentase Semua Unit

```php
rata_rata_semua = Σ(rata_rata_persentase per unit) / jumlah_unit
```

**Important**: This is also an arithmetic average, not a weighted aggregate.

---

## 6. Status Business Rules

The Daya Serap dashboard does NOT use the same status classification as rktUnit (Sudah/Belum/Draft). Instead, it focuses on:

- **Budget execution**: How much of the allocated budget has been used
- **Over-budget detection**: Units with persentase > 100%
- **Remaining budget**: Daya serap (sisa) that can still be utilized

### Over-Budget Detection

Units are flagged when:

```php
persentase > 100
```

This is displayed in the "Daftar Unit dengan Persentase Daya Serap > 100%" section.

---

## 7. Master JOIN Rules

### Current Implementation (Potential Issues)

#### tb_sumberdana in getAlokasiBackup()

```sql
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = ba.kode_sd 
    AND sd.is_show = 'true' 
    AND sd.is_deleted = 'false'
```

**Issue**: No year filter on `sd.tahun`. This could cause incorrect data association if the same `kd_sumberdana` exists across multiple years.

**Recommended fix**:
```sql
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = ba.kode_sd 
    AND sd.tahun = RIGHT(backupTahun, 4)  -- or use $backupTahunAngka
    AND sd.is_show = 'true' 
    AND sd.is_deleted = 'false'
```

#### tb_unit_api in getAlokasiBackup()

```sql
INNER JOIN tb_unit_api unit ON unit.idunit = ba.idunit
```

**Issue**: No year filter on `unit.tahun`. This is the same issue that caused 2× multiplication in rktUnit (FIX-008).

**Recommended fix**:
```sql
INNER JOIN tb_unit_api unit 
    ON unit.idunit = ba.idunit 
    AND unit.tahun = RIGHT(backupTahun, 4)  -- or use $backupTahunAngka
```

#### tb_sumberdana and tb_unit_api in Realisasi Query

```sql
INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
```

**Issue**: Both JOINs lack year filters and use incorrect table alias (`sumberdana` instead of `tb_sumberdana`).

**Recommended fix**:
```sql
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = backupRkat.sd
    AND sd.tahun = RIGHT(backupTahun, 4)
INNER JOIN tb_unit_api unit 
    ON unit.idunit = backupRkat.idunit
    AND unit.tahun = RIGHT(backupTahun, 4)
```

### Why Year-Aware JOIN is Critical

As discovered in rktUnit (FIX-008), `tb_unit_api` contains:
- One record per idunit per year
- Joining without year filter causes 2× (or more) financial multiplication

The same issue likely exists in dayaSerap and should be fixed to prevent similar data corruption.

---

## 8. Critical Anti-Patterns

### DO NOT: JOIN tb_unit_api only by idunit

```php
// WRONG (current implementation in getAlokasiBackup)
INNER JOIN tb_unit_api unit ON unit.idunit = ba.idunit
```

**Why**: This can multiply financial facts when the master table contains multiple records per idunit (e.g., one per year).

**Correct**: Always include year filtering:
```php
INNER JOIN tb_unit_api unit 
    ON unit.idunit = ba.idunit
    AND unit.tahun = RIGHT(backupTahun, 4)
```

### DO NOT: JOIN tb_sumberdana only by kd_sumberdana

```php
// WRONG (current implementation in getAlokasiBackup)
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = ba.kode_sd 
    AND sd.is_show = 'true' 
    AND sd.is_deleted = 'false'
```

**Why**: The code exists across multiple years. Joining without year filtering can cause incorrect data association.

**Correct**: Always include year filtering:
```php
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = ba.kode_sd 
    AND sd.tahun = RIGHT(backupTahun, 4)
    AND sd.is_show = 'true' 
    AND sd.is_deleted = 'false'
```

### DO NOT: Use incorrect table aliases

```php
// WRONG (in realisasi query)
INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
```

**Why**: The table name is `tb_sumberdana`, not `sumberdana`. This could cause SQL errors or incorrect data.

**Correct**: Use the correct table name:
```php
INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
```

---

## 9. Data Integrity Considerations

### Potential Issues

1. **Missing year filters**: Current implementation lacks year-aware JOINs for master tables, which could cause data corruption similar to rktUnit FIX-008.

2. **Incorrect table alias**: The realisasi query uses `sumberdana` instead of `tb_sumberdana`, which may cause SQL errors.

3. **Arithmetic average vs weighted aggregate**: The dashboard uses arithmetic average for percentages, which is appropriate for this use case (external reporting), but different from rktUnit's weighted aggregate.

### Recommended Fixes

1. Add year filters to all master table JOINs
2. Fix table alias from `sumberdana` to `tb_sumberdana`
3. Consider adding data validation to detect over-budget scenarios early

---

## 10. Daya Serap vs RKT Unit

### Fundamental Difference

| Aspect | dayaSerap | rktUnit |
|--------|-----------|---------|
| **Purpose** | Budget execution monitoring | Activity-based performance tracking |
| **Stakeholder** | External (Kementerian/Dewan) | Internal (Unit Kerja) |
| **Grain** | unit + sumber dana | unit + sumber dana + jenis RAB + kegiatan |
| **Pagu Source** | tb_backup_alokasi | tb_backup_rkat_detail (SUM) |
| **Percentage Calculation** | Arithmetic average | Weighted aggregate |
| **Reporting Standard** | Standar Akuntansi Pemerintahan (SAP) | Internal operational monitoring |

### Why Numbers Differ

The two dashboards will naturally produce different numbers because:

1. **Different grain**: dayaSerap aggregates at unit-sumber dana level, rktUnit at unit-sumber dana-jenis-kegiatan level
2. **Different pagu source**: dayaSerap uses allocation table, rktUnit uses detail table
3. **Different percentage calculation**: dayaSerap uses arithmetic average, rktUnit uses weighted aggregate

These differences are **expected and correct** for their respective purposes.

---

## 11. UI Semantics

### Top KPI

| Component | Meaning |
|-----------|---------|
| Total pagu alokasi | Total budget allocation across all units and sumber dana |
| Total realisasi (Anggaran) | Total actual expenditure across all units and sumber dana |
| Total daya serap | Total remaining budget (pagu - realisasi) |
| Rata-rata persentase | Arithmetic average of unit-level realization rates |

### Progress Bar

The progress bar shows:
- **Pagu**: 100% width (baseline)
- **Realisasi**: Width = `(total_realisasi / total_pagu_alokasi) × 100`, clamped to 0-100%

### Unit Rankings

- **5 Unit dengan Daya Serap Terendah**: Units with lowest realization rates (ascending order)
- **Daftar Unit dengan Persentase > 100%**: Units where realisasi exceeds pagu (over-budget)

---

## 12. SQL Query Patterns

### Allocation Query (Backup)

```sql
SELECT 
    sd.sumberdana, 
    ba.*, 
    unit.nama 
FROM tb_backup_alokasi ba
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = ba.kode_sd 
    AND sd.is_show = 'true' 
    AND sd.is_deleted = 'false'
INNER JOIN tb_unit_api unit ON unit.idunit = ba.idunit 
WHERE ba.id_duplikasi = ?
ORDER BY sd.kd_sumberdana, ba.idunit
```

**Recommended fix** (add year filters):
```sql
SELECT 
    sd.sumberdana, 
    ba.*, 
    unit.nama 
FROM tb_backup_alokasi ba
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = ba.kode_sd 
    AND sd.tahun = RIGHT(backupTahun, 4)
    AND sd.is_show = 'true' 
    AND sd.is_deleted = 'false'
INNER JOIN tb_unit_api unit 
    ON unit.idunit = ba.idunit 
    AND unit.tahun = RIGHT(backupTahun, 4)
WHERE ba.id_duplikasi = ?
ORDER BY sd.kd_sumberdana, ba.idunit
```

### Realization Query (Backup)

```sql
SELECT 
    unit.nama AS nama_unit, 
    unit.idunit AS unit_kerja_rkt,
    sd.kd_sumberdana, 
    sd.sumberdana,
    SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) 
        + COALESCE(backupRkatDet.jumlah_realisasi, 0)) AS jumlah_amprah
FROM tb_backup_rkat backupRkat
INNER JOIN tb_backup_rkat_detail backupRkatDet 
    ON backupRkatDet.id_rekat = backupRkat.id_rekat
INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
WHERE (backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ?)
  AND backupRkat.tahun = ?
GROUP BY unit.idunit, backupRkat.sd
```

**Recommended fix** (add year filters and fix table alias):
```sql
SELECT 
    unit.nama AS nama_unit, 
    unit.idunit AS unit_kerja_rkt,
    sd.kd_sumberdana, 
    sd.sumberdana,
    SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) 
        + COALESCE(backupRkatDet.jumlah_realisasi, 0)) AS jumlah_amprah
FROM tb_backup_rkat backupRkat
INNER JOIN tb_backup_rkat_detail backupRkatDet 
    ON backupRkatDet.id_rekat = backupRkat.id_rekat
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = backupRkat.sd
    AND sd.tahun = RIGHT(backupTahun, 4)
INNER JOIN tb_unit_api unit 
    ON unit.idunit = backupRkat.idunit
    AND unit.tahun = RIGHT(backupTahun, 4)
WHERE (backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ?)
  AND backupRkat.tahun = ?
GROUP BY unit.idunit, backupRkat.sd
```

---

## 13. Test / Validation Checklist

Use this checklist when modifying the Daya Serap dashboard:

### Data Source & JOINs
- [ ] Correct backup selected (is_deleted=false, duplikasi_ke=0, peruntukan='RKAT Awal')
- [ ] Year-aware tb_sumberdana JOIN with `sd.tahun = derived_year`
- [ ] Year-aware tb_unit_api JOIN with `unit.tahun = derived_year`
- [ ] Correct table alias (`tb_sumberdana`, not `sumberdana`)
- [ ] No master JOIN multiplication (verify row counts)

### Aggregation Logic
- [ ] Correct GROUP BY (unit.idunit, sd)
- [ ] Explicit SUM() for financial fields
- [ ] Correct pagu source (tb_backup_alokasi for backup, tb_alokasi for current)
- [ ] Correct realization source (tb_backup_rkat_detail with SUM)

### Business Logic
- [ ] Daya serap = pagu - realisasi
- [ ] Persentase = (realisasi / pagu) × 100
- [ ] Rata-rata uses arithmetic average (not weighted aggregate)
- [ ] Over-budget detection (persentase > 100)

### UI Validation
- [ ] Browser validation performed
- [ ] Top KPI values match backend
- [ ] Progress bar uses percentage, not raw currency
- [ ] Unit rankings display correctly
- [ ] Over-budget units are flagged

---

## 14. Troubleshooting Guide

### "Total Realisasi suddenly doubles"

**Check**:
- tb_unit_api year JOIN (missing tahun filter?)
- tb_sumberdana year JOIN (missing tahun filter?)
- Detail JOIN cardinality

### "Wrong sumber dana data appears"

**Check**:
- Table alias (is it `tb_sumberdana` or `sumberdana`?)
- Year filter on tb_sumberdana JOIN

### "Percentage calculation looks wrong"

**Check**:
- Whether arithmetic average is being used (correct for dayaSerap)
- Whether weighted aggregate is being used (incorrect for dayaSerap)
- Formula should be: `Σ(persentase) / jumlah_sumber_dana`

### "Over-budget units not detected"

**Check**:
- Whether persentase > 100 condition is working
- Whether data is being refreshed correctly

### "Pagu allocation doesn't match expected"

**Check**:
- Whether correct table is used (tb_backup_alokasi vs tb_alokasi)
- Whether pagu_tambahan is included in calculation
- Formula should be: `COALESCE(pagu, 0) + COALESCE(pagu_tambahan, 0)`

---

## 15. Known Issues & Recommendations

### Current Issues

1. **Missing year filters on master table JOINs** (Lines 78-79, 87-88 in DayaSerapController.php)
   - Risk: Data corruption similar to rktUnit FIX-008
   - Recommendation: Add year filters to all master table JOINs

2. **Incorrect table alias** (Line 87 in DayaSerapController.php)
   - Risk: SQL errors or incorrect data
   - Recommendation: Change `sumberdana` to `tb_sumberdana`

### Recommendations

1. Apply year-aware JOIN fixes to prevent data multiplication
2. Fix table alias to prevent SQL errors
3. Consider adding data validation for over-budget scenarios
4. Consider adding logging for debugging data integrity issues

---

## 16. Change History

### No fixes have been applied to dayaSerap yet

The Daya Serap dashboard currently has the following known issues that should be addressed:

1. **Missing year filters** on tb_sumberdana and tb_unit_api JOINs
2. **Incorrect table alias** (`sumberdana` instead of `tb_sumberdana`)

These issues were identified during the documentation process based on lessons learned from rktUnit (FIX-008).

---

## 17. Documentation Quality Rules

This documentation must:

- ✓ **Distinguish FACT from ASSUMPTION** - Only include verified findings from code analysis
- ✓ **Preserve exact formulas** - Do not simplify or generalize
- ✓ **Distinguish different grains** - dayaSerap vs rktUnit
- ✓ **Identify critical JOIN cardinalities** - Year-aware JOINs are mandatory
- ✓ **Identify dangerous patterns** - Missing year filters, incorrect aliases
- ✓ **Avoid vague phrases** - Be specific about issues and recommendations
- ✓ **Include examples** - SQL patterns with recommended fixes

---

## Appendix: Code References

### Key Files

- **Controller**: `app/Http/Controllers/Laporan/DayaSerapController.php`
  - `index()` method (lines ~16-22)
  - `getAlokasiData()` method (lines ~24-50)
  - `getAlokasiBackup()` method (lines ~52-111)

- **View**: `resources/views/statistik/dayaserap.blade.php`
  - Top KPI section (lines ~40-109)
  - Progress bar section (lines ~111-137)
  - Unit rankings (lines ~142-224)
  - DataTable section (lines ~226-244)
  - Documentation section (lines ~247-410)

- **Helper**: `app/Helper/rekat.php`
  - `getBaseData()` function (lines ~1008-1100)
  - `getRekapRealisasi()` function (referenced but not shown)

---

**Document Version**: 1.0  
**Last Updated**: August 31, 2026  
**Status**: Documentation complete, known issues identified but not yet fixed
