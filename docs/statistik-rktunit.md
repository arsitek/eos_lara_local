# RKT Unit — Data, Formula & Business Logic Documentation

## 1. Purpose

The `/statistik/rktunit` dashboard is an RKT (Rencana Kerja dan Anggaran Tahunnan) activity-based analytical dashboard that provides:

- **Activity-level performance tracking**: Granular monitoring of individual activities/programs (kegiatan) within each unit
- **Internal operational insights**: Decision-making support for unit-level budget execution
- **Status-based financial distribution**: Understanding of budget allocation across execution stages (Sudah/Belum/Draft)
- **Realization rate analysis**: Overall and per-unit realization percentages

**Important distinction**: This dashboard is NOT automatically identical in grain or meaning to `/statistik/dayaserap`. The two dashboards serve different purposes:

- **rktUnit**: Activity-based performance tracking (per unit + sumber dana + jenis RAB + kegiatan)
- **dayaSerap**: Budget execution monitoring (per unit + sumber dana)

Differences in reported numbers are acceptable only if their data source, grain, and business definitions justify them.

---

## 2. Data Scope

### Backup Selection Logic

The dashboard operates on a single backup snapshot selected by the following criteria:

```sql
SELECT *
FROM tb_duplikasi_rkat
WHERE is_deleted = false
  AND duplikasi_ke = 0
  AND peruntukan = 'RKAT Awal'
ORDER BY created_at DESC
LIMIT 1
```

### Current Verified Example

- **idBackup**: 73
- **backupTahun**: "Definitif_2026"

### Year Derivation

The year used by master tables (`tb_sumberdana`, `tb_unit_api`) must be dynamically derived from the backup year:

```sql
RIGHT(backupRkat.tahun, 4)  -- Produces "2026" from "Definitif_2026"
```

This is critical because master tables contain data across multiple years, and joining without year filtering causes financial multiplication.

---

## 3. Source Tables

### tb_backup_rkat
- **Role**: Header table for RKT backup snapshots
- **Type**: Fact table (backup metadata)
- **Important keys**: `id`, `id_duplikasi`, `tahun`, `sd` (sumber dana)
- **Relevant filters**: `id_duplikasi`, `tahun`, `is_deleted`

### tb_backup_rkat_detail
- **Role**: Detail table containing individual activities/programs with financial data
- **Type**: Fact table (financial facts)
- **Important keys**: `id_rekat`, `id_duplikasi`, `id_mak`, `jenis`, `jumlah_biaya`, `jumlah_amprahan`, `jumlah_realisasi`, `is_draft`
- **Relevant filters**: `id_duplikasi`, `is_deleted`, `is_draft`
- **Cardinality concerns**: Multiple detail records can belong to one analytical group (see Section 4)

### tb_sumberdana
- **Role**: Master table for funding sources (sumber dana)
- **Type**: Dimension table
- **Important keys**: `kd_sumberdana`, `sumberdana`, `tahun`
- **Relevant filters**: `tahun`, `is_show`, `is_deleted`
- **Cardinality concerns**: Contains data across multiple years - year-aware JOIN mandatory

### tb_unit_api
- **Role**: Master table for organizational units
- **Type**: Dimension table
- **Important keys**: `idunit`, `nama`, `tahun`
- **Relevant filters**: `tahun`
- **Cardinality concerns**: Each `idunit` has one record per year - year-aware JOIN mandatory

### tb_duplikasi_rkat
- **Role**: Backup metadata and selection
- **Type**: Metadata/config table
- **Important keys**: `id`, `duplikasi_ke`, `peruntukan`, `created_at`
- **Relevant filters**: `is_deleted`, `duplikasi_ke`, `peruntukan`

---

## 4. Relational Grain

### Data Levels

The RKT Unit dashboard operates at multiple distinct data levels. Understanding these levels is critical for correct data interpretation.

| Level | Description | Current Verified Count |
|-------|-------------|------------------------|
| Raw joined rows | Detail-level JOIN result before GROUP BY | 12,640 |
| Distinct detail IDs | Unique detail records | 12,638 |
| Analytical groups | Result of GROUP BY (dashboard grain) | 12,637 |
| Aggregated units | Final unit-level aggregation | 50 |

### Critical Distinction

**12,637 analytical groups is NOT the same thing as 12,637 raw detail records.**

The GROUP BY operation legitimately reduces the count from raw joined rows to analytical groups. This is expected behavior, not data loss.

### GROUP BY Grain of rktUnit()

The main SQL query in `rktUnit()` groups by:

```sql
GROUP BY unit.idunit, backupRkat.sd, backupRkatDet.jenis, backupRkatDet.id_mak
```

This means:
- Multiple detail records can legitimately belong to one analytical group
- The reduction from 12,640 → 12,637 is a grouping effect, not data loss
- Do NOT describe this as "3 records lost"

### Example Discovered During Audit

One analytical grain can contain multiple detail rows with the same `idunit`, `sd`, `jenis`, and `id_mak` combination. This is legitimate and expected.

---

## 5. Financial Formulas

### Actual Realization

```php
realisasi = COALESCE(jumlah_amprahan, 0) + COALESCE(jumlah_realisasi, 0)
```

This formula is used at both:
- Detail level (in SQL SELECT)
- Aggregated level (in PHP foreach loops)

### Total Pagu

```php
SUM(jumlah_biaya)  // At the relevant analytical grain
```

### Sisa (Remaining Budget)

```php
sisa = jumlah_biaya - realisasi
```

### Overall Realization Rate

```php
realization_rate = (total_realisasi / total_jumlah_biaya) × 100
```

**Important**: This is a weighted/aggregate rate, NOT the arithmetic average of unit percentages.

### Current Verified Results

| Metric | Value |
|--------|-------|
| Total Pagu | Rp523.352.407.280 |
| Total Realisasi | Rp121.001.672.609 |
| Sisa | Rp402.350.734.671 |
| Realization Rate | 23,12% |

### Historical Field-Name Issue

The field `avg_persentase` is currently used to hold the aggregate realization rate. This name is potentially misleading because the final calculation is:

```php
total_realisasi / total_jumlah_biaya × 100
```

This is NOT an arithmetic average. Do NOT rename the code without careful consideration of downstream impacts.

---

## 6. Status Business Rules

### Verified Status Definitions

| Status | Condition | Description |
|--------|-----------|-------------|
| SUDAH | `realisasi > 0` | Activities with actual realization |
| BELUM | `realisasi = 0 AND is_draft != 'true'` | Activities with no realization, not in draft |
| DRAFT | `is_draft = 'true'` | Activities marked as draft |

### Current Verified Dataset (Analytical Groups)

| Status | Count |
|--------|-------|
| Sudah | 2,663 |
| Belum | 9,060 |
| Draft | 914 |
| **Total** | **12,637** |

### Raw-Detail Diagnostic Counts

| Status | Count |
|--------|-------|
| Sudah | 2,663 |
| Belum | 9,063 |
| Draft | 914 |
| **Total** | **12,640** |

**Note**: These counts differ because the dashboard and raw diagnostic operate at different grains (analytical groups vs raw joined rows).

### Data Quality Verification

The current verified dataset contains:
- ✓ No negative realization
- ✓ No NULL is_draft values
- ✓ No unexpected is_draft values
- ✓ No mixed draft/non-draft analytical groups

---

## 7. Status Financial Semantics

### Critical Distinction

**"Pagu Kegiatan dengan Realisasi"** means:

```php
SUM(jumlah_biaya) for groups where realisasi > 0
```

It does **NOT** mean:

```php
actual realization amount
```

### Current Verified Values

| Metric | Value |
|--------|-------|
| Pagu Kegiatan dengan Realisasi | Rp174.098.668.649 |
| Actual Realization of those groups | Rp121.001.672.609 |

This distinction MUST remain explicit in future documentation and UI labeling. The displayed financial value is the **budget allocation** of activities that have realization, not the **actual realization amount** itself.

---

## 8. Master JOIN Rules

### Mandatory Year-Aware JOIN Patterns

#### tb_sumberdana

```sql
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = backupRkat.sd
    AND sd.tahun = RIGHT(backupRkat.tahun, 4)
    AND sd.is_show = 'true'
    AND sd.is_deleted = 'false'
```

#### tb_unit_api

```sql
INNER JOIN tb_unit_api unit 
    ON unit.idunit = backupRkat.idunit
    AND unit.tahun = RIGHT(backupRkat.tahun, 4)
```

### Why Year-Aware JOIN is Mandatory

**Verified fact**: `tb_unit_api` contained:
- 100 rows total
- 50 distinct idunit values
- Each idunit had one 2025 record and one 2026 record

Joining only by `idunit` (without tahun filter) caused approximately **2× financial multiplication** because both the 2025 and 2026 records would join to the same backup data.

This is a critical architectural lesson: **Master tables with temporal data must always be joined with year filtering**.

---

## 9. Detail JOIN Rules

### Contextual Relationship

```sql
INNER JOIN tb_backup_rkat_detail backupRkatDet 
    ON backupRkatDet.id_rekat = backupRkat.id_rekat
    AND backupRkatDet.id_duplikasi = backupRkat.id_duplikasi
```

### Why id_duplikasi Participates

The `id_duplikasi` field ensures that detail records are correctly associated with the specific backup snapshot. Without this, detail records from different backups could incorrectly join, causing data contamination.

---

## 10. Critical Anti-Patterns

### DO NOT: JOIN tb_unit_api only by idunit

```php
// WRONG
INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
```

**Why**: This can multiply financial facts when the master table contains multiple records per idunit (e.g., one per year).

**Correct**: Always include year filtering:
```php
INNER JOIN tb_unit_api unit 
    ON unit.idunit = backupRkat.idunit
    AND unit.tahun = RIGHT(backupRkat.tahun, 4)
```

### DO NOT: JOIN tb_sumberdana only by kd_sumberdana

```php
// WRONG
INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
```

**Why**: The code exists across multiple years. Joining without year filtering can cause incorrect data association.

**Correct**: Always include year filtering:
```php
INNER JOIN tb_sumberdana sd 
    ON sd.kd_sumberdana = backupRkat.sd
    AND sd.tahun = RIGHT(backupRkat.tahun, 4)
```

### DO NOT: Use SUM(DISTINCT jumlah_biaya) as a generic duplication fix

```php
// WRONG
SUM(DISTINCT jumlah_biaya)
```

**Why**: Two legitimate financial records can have the same nominal value. Using DISTINCT would incorrectly eliminate legitimate financial data.

**Correct**: Fix the JOIN cardinality instead (see Section 8).

### DO NOT: Use SELECT DISTINCT as a substitute for correcting JOIN cardinality

```php
// WRONG
SELECT DISTINCT financial_rows
```

**Why**: This masks the root cause of duplication and can eliminate legitimate duplicate values.

**Correct**: Fix the JOIN conditions to ensure correct cardinality.

### DO NOT: Use NULL/non-NULL fields as a proxy for business status

```php
// WRONG
WHERE jumlah_amprahan IS NOT NULL OR jumlah_realisasi IS NOT NULL
```

**Why**: "Column populated" is not equivalent to "actual realization > 0". A column can be populated with zero values.

**Correct**: Use business-value-based conditions:
```php
WHERE (COALESCE(jumlah_amprahan, 0) + COALESCE(jumlah_realisasi, 0)) > 0
```

### DO NOT: Reuse a PHP foreach reference variable without unsetting it

```php
// WRONG
foreach ($dataPerUnit as &$unitData) {
    // ...
}
foreach ($dataPerUnit as $unitData) {
    // This will be corrupted!
}
```

**Why**: The reference persists after the first loop, causing the last array element to be overwritten.

**Correct**: Always unset the reference:
```php
foreach ($dataPerUnit as &$unitData) {
    // ...
}
unset($unitData);
foreach ($dataPerUnit as $unitData) {
    // Safe now
}
```

---

## 11. PHP Reference Leakage Incident (FIX-007)

### Problem

Using a foreach-by-reference loop followed by another foreach loop without unsetting the reference caused the last array element to be overwritten.

### Verified Example

The last unit in `$dataPerUnit` was corrupted:
- **Original value**: Departemen Pendidikan Profesi Guru
- **Overwritten with**: Divisi World Class University

This produced incorrect financial totals:
- **Pagu error**: +Rp14.304.648.804
- **Realisasi error**: +Rp8.171.125.990

### Root Cause

PHP references persist after the loop completes. When the same variable name is used in a subsequent loop, the reference is still active, causing data corruption.

### Fix

```php
foreach ($dataPerUnit as &$unitData) {
    // Reference loop
}
unset($unitData);  // CRITICAL: Break the reference
```

### Technical Explanation

In PHP, the `&` operator creates a reference to the array element, not a copy. After the loop, `$unitData` still holds a reference to the last element. When `$unitData` is reused in a subsequent loop, it modifies the last element of the original array instead of creating a new variable.

---

## 12. Data Integrity Incidents & Fix History

| ID | Problem | Root Cause | Symptom | Fix | Verification |
|----|---------|------------|---------|-----|--------------|
| FIX-008 | 2× financial multiplication | tb_unit_api JOIN without year filter | Total pagu doubled from ~Rp523M to ~Rp1.047M | Added `AND unit.tahun = RIGHT(backupRkat.tahun, 4)` to JOIN | Financial totals returned to expected baseline |
| FIX-007 | Last unit data corruption | PHP reference leakage without unset | Departemen Pendidikan Profesi Guru overwritten with WCU values | Added `unset($unitData)` after reference foreach | Unit data and financial totals corrected |
| FIX-010 | DataTable filter semantics | NULL/non-NULL used as proxy for business status | DataTable "!realisasi" included draft rows (9,977 vs 9,063) | Changed to business-value-based filters: `COALESCE(...) > 0` and `COALESCE(...) = 0 AND is_draft != 'true'` | DataTable filters now match dashboard semantics |
| RKT-UI-LABEL-001 | Misleading status label | "Sudah realisasi" implied actual realization amount | UI showed pagu value with "realisasi" label | Changed to "Pagu Kegiatan dengan Realisasi" | Label now accurately reflects displayed value |

### Progress Bar Presentation Rule

The realization progress bar width is calculated as:

```php
width = (total_realisasi / total_pagu) × 100
```

This value is clamped visually to 0–100% in the UI. Do not pass raw currency values as the width parameter.

---

## 13. Reconciliation Rules

### Financial Invariant

```php
SUM(data source) = SUM(dataPerUnit)
```

### Final Aggregation Invariant

```php
SUM(dataPerUnit) = totalSemua
```

### Current Verified Results

Both invariants passed exactly after FIX-007 and FIX-008:

| Metric | Source Sum | dataPerUnit Sum | totalSemua | Status |
|--------|------------|-----------------|------------|--------|
| Pagu | Rp523.352.407.280 | Rp523.352.407.280 | Rp523.352.407.280 | ✓ PASS |
| Realization | Rp121.001.672.609 | Rp121.001.672.609 | Rp121.001.672.609 | ✓ PASS |

### Importance

These invariants are more important than merely matching a screenshot. They prove that:
- No data is lost during aggregation
- No data is duplicated during aggregation
- The aggregation logic is mathematically sound

---

## 14. RKT Unit vs Daya Serap

### Fundamental Difference

The two dashboards must NOT automatically be expected to produce identical numbers because they serve different purposes:

| Aspect | rktUnit | dayaSerap |
|--------|---------|-----------|
| **Purpose** | Activity-based performance tracking | Budget execution monitoring |
| **Stakeholder** | Internal operational management | External (Kementerian/Dewan) |
| **Grain** | unit + sumber dana + jenis RAB + kegiatan (id_mak) | unit + sumber dana |
| **Reporting Standard** | Internal monitoring | Standar Akuntansi Pemerintahan (SAP) |

### Acceptable Differences

Differences in reported numbers are acceptable ONLY if their:
- Data source differs
- Grain differs
- Business definition differs

Do NOT force the two dashboards to match without understanding why they differ.

---

## 15. DataTable Semantics

### Intended Filters

#### Semua (All)
No status restriction. Shows all records.

#### Realisasi (With Realization)
```sql
WHERE (COALESCE(jumlah_amprahan, 0) + COALESCE(jumlah_realisasi, 0)) > 0
```

#### Belum (Without Realization)
```sql
WHERE (COALESCE(jumlah_amprahan, 0) + COALESCE(jumlah_realisasi, 0)) = 0
  AND is_draft != 'true'
```

#### Draft
```sql
WHERE is_draft = 'true'
```

### Historical Issue

The old NULL/non-NULL filter was wrong because "column populated" is not equivalent to "actual realization":

```php
// WRONG
WHERE jumlah_amprahan IS NOT NULL OR jumlah_realisasi IS NOT NULL
```

This would match records with zero values, which is not the intended business logic.

---

## 16. UI Semantics

### Top KPI

| Component | Meaning |
|-----------|---------|
| Total Pagu | Total budget allocation across all activities |
| Realisasi Aktual Kegiatan | Total actual expenditure across all activities |
| Sisa Pagu | Remaining budget (pagu - realisasi) |
| Tingkat Realisasi | Overall realization rate (realisasi / pagu × 100) |

### Financial Distribution Chart

**Based on PAGU VALUES** (budget allocation).

Current verified percentages:
- Sudah ≈ 33%
- Belum ≈ 64%
- Draft ≈ 3%

### Item Count Distribution Chart

**Based on NUMBER OF ANALYTICAL GROUPS** (activity count).

Current verified percentages:
- Sudah ≈ 21%
- Belum ≈ 72%
- Draft ≈ 7%

### Critical Distinction

The two distributions are intentionally different:
- **33/64/3** represents budget allocation
- **21/72/7** represents activity count

This is expected because activities with high budget allocation may be fewer in number than activities with low budget allocation.

---

## 17. Current Verified Snapshot

### Backup Information
- **id**: 73
- **tahun**: "Definitif_2026"

### Financial Totals
- **Total Pagu**: Rp523.352.407.280
- **Total Realisasi**: Rp121.001.672.609
- **Sisa**: Rp402.350.734.671
- **Realization Rate**: 23,12%

### Status Breakdown
- **Pagu Kegiatan dengan Realisasi**: Rp174.098.668.649
- **Sudah**: 2,663 analytical groups
- **Belum**: 9,060 analytical groups
- **Draft**: 914 analytical groups

### Data Levels
- **Raw joined rows**: 12,640
- **Analytical groups**: 12,637
- **Aggregated units**: 50

**Last Verified**: August 31, 2026

---

## 18. Test / Validation Checklist

Use this checklist when modifying the RKT Unit dashboard:

### Data Source & JOINs
- [ ] Correct backup selected (is_deleted=false, duplikasi_ke=0, peruntukan='RKAT Awal')
- [ ] Year-aware tb_sumberdana JOIN with `sd.tahun = RIGHT(backupRkat.tahun, 4)`
- [ ] Year-aware tb_unit_api JOIN with `unit.tahun = RIGHT(backupRkat.tahun, 4)`
- [ ] id_duplikasi included in detail JOIN
- [ ] No master JOIN multiplication (verify row counts)

### Aggregation Logic
- [ ] Correct analytical GROUP BY (unit.idunit, sd, jenis, id_mak)
- [ ] Explicit SUM() for financial fields
- [ ] No non-aggregated financial field hidden inside GROUP BY
- [ ] Reference variables unset after foreach-by-reference

### Financial Invariants
- [ ] Source → dataPerUnit financial invariant passes
- [ ] dataPerUnit → totalSemua invariant passes
- [ ] Status totals reconcile (sudah + belum + draft = total)

### Business Logic
- [ ] DataTable filters use business semantics (COALESCE > 0, not NULL checks)
- [ ] KPI rate uses aggregate realization / aggregate budget
- [ ] Financial percentages use financial totals
- [ ] Item percentages use analytical-group counts
- [ ] Progress bar uses percentage, not raw currency value

### UI Validation
- [ ] Browser validation performed
- [ ] Top KPI values match backend
- [ ] Status cards match backend
- [ ] Chart percentages match calculations
- [ ] DataTable filters work correctly

---

## 19. Troubleshooting Guide

### "Total Realisasi suddenly doubles"

**Check**:
- tb_unit_api year JOIN (missing tahun filter?)
- tb_sumberdana year JOIN (missing tahun filter?)
- detail JOIN cardinality (is id_duplikasi included?)

### "Last unit has another unit's value"

**Check**:
- foreach reference leakage
- `unset($unitData)` after reference loop

### "DataTable Belum includes Draft"

**Check**:
- !realisasi filter (is it using NULL checks instead of business logic?)
- Should be: `COALESCE(...) = 0 AND is_draft != 'true'`

### "Progress bar is 100%"

**Check**:
- Whether raw currency is being passed as width
- Should be: percentage (0-100), not currency value

### "Dashboard percentage looks wrong"

**Check**:
- Whether arithmetic average is being used instead of aggregate rate
- Should be: `total_realisasi / total_pagu × 100`
- NOT: `SUM(unit_percentages) / unit_count`

### "Financial totals don't match baseline"

**Check**:
- Source → dataPerUnit invariant
- dataPerUnit → totalSemua invariant
- JOIN cardinality
- Year filtering on master tables

---

## 20. Change History

### FIX-007 (PHP Reference Leakage)
- **Date**: August 31, 2026
- **Problem**: Last unit data overwritten due to PHP reference leakage
- **Fix**: Added `unset($unitData)` after foreach-by-reference loop
- **File**: `app/Http/Controllers/StatistikController.php` (line 374)
- **Verification**: Unit data and financial totals corrected

### FIX-008 (Year-Aware Master JOINs)
- **Date**: August 31, 2026
- **Problem**: 2× financial multiplication from tb_unit_api JOIN without year filter
- **Fix**: Added `AND unit.tahun = RIGHT(backupRkat.tahun, 4)` to rktUnit() and getRktUnitDataTable()
- **Files**: 
  - `app/Http/Controllers/StatistikController.php` (lines 258, 746)
- **Verification**: Financial totals returned to expected baseline (Rp523.352.407.280)

### FIX-010 (DataTable Filter Semantics)
- **Date**: August 31, 2026
- **Problem**: DataTable filters used NULL/non-NULL instead of business logic
- **Fix**: Changed to business-value-based filters using COALESCE
- **File**: `app/Http/Controllers/StatistikController.php` (lines 714-717)
- **Verification**: DataTable filters now match dashboard semantics

### RKT-UI-LABEL-001 (Label Clarification)
- **Date**: August 31, 2026
- **Problem**: "Sudah realisasi" label implied actual realization amount
- **Fix**: Changed to "Pagu Kegiatan dengan Realisasi"
- **Files**:
  - `resources/views/statistik/rktunit.blade.php` (line 153)
  - Updated chart labels to Indonesian
- **Verification**: Labels now accurately reflect displayed values

---

## 21. Documentation Quality Rules

This documentation must:

- ✓ **Distinguish FACT from ASSUMPTION** - Only include verified findings
- ✓ **Preserve exact formulas** - Do not simplify or generalize
- ✓ **Preserve exact verified numbers** - Use current snapshot values
- ✓ **Distinguish raw rows from analytical groups** - Critical for understanding
- ✓ **Distinguish pagu from actual realization** - Important semantic distinction
- ✓ **Distinguish database bugs from UI semantic issues** - Different categories
- ✓ **Identify critical JOIN cardinalities** - Year-aware JOINs are mandatory
- ✓ **Identify dangerous PHP reference behavior** - unset() is mandatory
- ✓ **Avoid vague phrases** - e.g., "data doubled somewhere" → "2× multiplication from tb_unit_api JOIN"
- ✓ **Avoid unsupported claims** - Only document what was verified
- ✓ **Include examples** - Where they help future debugging

---

## Appendix: Code References

### Key Files

- **Controller**: `app/Http/Controllers/StatistikController.php`
  - `rktUnit()` method (lines ~200-500)
  - `getRktUnitDataTable()` method (lines ~700-770)

- **View**: `resources/views/statistik/rktunit.blade.php`
  - Top KPI section (lines ~65-110)
  - Status cards section (lines ~145-180)
  - Charts section (lines ~195-235)
  - DataTable section (lines ~470-510)

### Key SQL Patterns

#### Main rktUnit Query
```sql
SELECT
    unit.nama AS unit_kerja,
    unit.idunit AS unit_kerja_rkt,
    sd.kd_sumberdana,
    sd.sumberdana,
    backupRkatDet.jenis AS rab_type,
    backupRkatDet.id_mak,
    SUM(backupRkatDet.jumlah_biaya) AS jumlah_biaya,
    SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)) AS realisasi,
    SUM(CASE
        WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL)
             AND backupRkatDet.terpakai_sisa IS NOT NULL
        THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)
             + backupRkatDet.terpakai_sisa
        ELSE backupRkatDet.jumlah_biaya
    END) AS jumlah_biaya_revisi,
    MAX(backupRkatDet.is_draft) AS is_draft
FROM tb_backup_rkat backupRkat
INNER JOIN tb_backup_rkat_detail backupRkatDet
    ON backupRkatDet.id_rekat = backupRkat.id_rekat
    AND backupRkatDet.id_duplikasi = backupRkat.id_duplikasi
INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
    AND sd.tahun = RIGHT(backupRkat.tahun, 4)
    AND sd.is_show = 'true'
    AND sd.is_deleted = 'false'
INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit AND unit.tahun = RIGHT(backupRkat.tahun, 4)
WHERE backupRkat.id_duplikasi = ?
  AND backupRkat.tahun = ?
  AND backupRkatDet.is_deleted = 'false'
GROUP BY unit.idunit, backupRkat.sd, backupRkatDet.jenis, backupRkatDet.id_mak
```

---

**Document Version**: 1.0  
**Last Updated**: August 31, 2026  
**Verified Against**: Backup ID 73 (Definitif_2026)
