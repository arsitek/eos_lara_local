# Mapping Controller → Blade — Statistik RKT Unit

## Variable yang Dikirim dari Controller (rktUnit method)

| Variable Controller | Type | Digunakan Untuk |
| ------------------ | ---- | -------------- |
| `$dataPerUnitArray` | Array (indexed) | DataTables, window.dataRktUnit |
| `$backupKeterangan` | String/null | Judul card, dokumentasi |
| `$backupTahun` | String/null | Dokumentasi |
| `$totalSemua` | Array | Card 1 (KPI Utama) |
| `$statusStatistik` | Array (associative) | Card 2 (Statistik Status) |
| `$unitTertinggi5` | Array (indexed) | Card 3 (5 Unit Tertinggi) |
| `$unitTerendah5` | Array (indexed) | Card 4 (5 Unit Terendah) |
| `$distribusiJenisRab` | Array (indexed) | Card 5 (Distribusi Jenis RAB) |
| `$distribusiSumberDana` | Array (indexed) | Card 6 (Distribusi Sumber Dana) |
| `$unitDiatas100` | Array (indexed) | Card 7 (Unit > 100%) |

## Detail Variable Structure

### $totalSemua
```php
[
    'total_jumlah_biaya' => int,
    'total_realisasi' => int,
    'total_sisa' => int,
    'avg_persentase' => float,
    'count' => int
]
```

### $statusStatistik
```php
[
    'sudah' => [
        'total_jumlah_biaya' => int,
        'total_realisasi' => int,
        'total_sisa' => int,
        'persentase' => float,
        'count' => int
    ],
    'belum' => [
        'total_jumlah_biaya' => int,
        'total_realisasi' => int,
        'total_sisa' => int,
        'persentase' => float,
        'count' => int
    ],
    'draft' => [
        'total_jumlah_biaya' => int,
        'total_realisasi' => int,
        'total_sisa' => int,
        'persentase' => float,
        'count' => int
    ]
]
```

### $unitTertinggi5 dan $unitTerendah5
```php
[
    [
        'unit_kerja' => string,
        'unit_kerja_rkt' => string,
        'total_jumlah_biaya' => int,
        'total_realisasi' => int,
        'total_sisa' => int,
        'avg_persentase' => float,
        'count' => int
    ],
    // ... 5 items
]
```

### $distribusiJenisRab
```php
[
    [
        'jenis' => string,
        'total_jumlah_biaya' => int,
        'count' => int
    ],
    // ... items
]
```

### $distribusiSumberDana
```php
[
    [
        'kd_sumberdana' => string,
        'sumberdana' => string,
        'total_jumlah_biaya' => int,
        'count' => int
    ],
    // ... items
]
```

### $unitDiatas100
```php
[
    [
        'unit_kerja' => string,
        'unit_kerja_rkt' => string,
        'total_jumlah_biaya' => int,
        'total_realisasi' => int,
        'total_sisa' => int,
        'avg_persentase' => float,
        'count' => int
    ],
    // ... items (bisa kosong)
]
```

### $dataPerUnitArray
```php
[
    [
        'unit_kerja' => string,
        'unit_kerja_rkt' => string,
        'total_jumlah_biaya' => int,
        'total_realisasi' => int,
        'total_sisa' => int,
        'avg_persentase' => float,
        'count' => int
    ],
    // ... items
]
```

## Mapping ke Blade Components

### Card 1: Total RKT
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$totalSemua['total_jumlah_biaya']` | `$totalSemua['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$totalSemua['total_realisasi']` | `$totalSemua['total_realisasi']` | `number_format(..., 0, ',', '.')` |
| `$totalSemua['total_sisa']` | `$totalSemua['total_sisa']` | `number_format(..., 0, ',', '.')` |
| `$totalSemua['avg_persentase']` | `$totalSemua['avg_persentase']` | `number_format(..., 2, ',', '.')` + `%` |
| `$backupKeterangan` | `$backupKeterangan` | String (default: 'Data Terbaru') |

### Card 2: Statistik Status
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$statusStatistik['sudah']['total_jumlah_biaya']` | `$statusStatistik['sudah']['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$statusStatistik['sudah']['total_realisasi']` | `$statusStatistik['sudah']['total_realisasi']` | `number_format(..., 0, ',', '.')` |
| `$statusStatistik['sudah']['total_sisa']` | `$statusStatistik['sudah']['total_sisa']` | `number_format(..., 0, ',', '.')` |
| `$statusStatistik['sudah']['persentase']` | `$statusStatistik['sudah']['persentase']` | `number_format(..., 2, ',', '.')` + `%` |
| `$statusStatistik['sudah']['count']` | `$statusStatistik['sudah']['count']` | Integer |
| `$statusStatistik['belum']['total_jumlah_biaya']` | `$statusStatistik['belum']['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$statusStatistik['belum']['total_sisa']` | `$statusStatistik['belum']['total_sisa']` | `number_format(..., 0, ',', '.')` |
| `$statusStatistik['belum']['count']` | `$statusStatistik['belum']['count']` | Integer |
| `$statusStatistik['draft']['total_jumlah_biaya']` | `$statusStatistik['draft']['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$statusStatistik['draft']['total_sisa']` | `$statusStatistik['draft']['total_sisa']` | `number_format(..., 0, ',', '.')` |
| `$statusStatistik['draft']['count']` | `$statusStatistik['draft']['count']` | Integer |

### Card 3: 5 Unit Tertinggi
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$unit['unit_kerja']` | `$unitTertinggi5[$i]['unit_kerja']` | String |
| `$unit['total_jumlah_biaya']` | `$unitTertinggi5[$i]['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$unit['total_realisasi']` | `$unitTertinggi5[$i]['total_realisasi']` | `number_format(..., 0, ',', '.')` |
| `$unit['total_sisa']` | `$unitTertinggi5[$i]['total_sisa']` | `number_format(..., 0, ',', '.')` |
| `$unit['avg_persentase']` | `$unitTertinggi5[$i]['avg_persentase']` | `number_format(..., 2, ',', '.')` + `%` |

### Card 4: 5 Unit Terendah
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$unit['unit_kerja']` | `$unitTerendah5[$i]['unit_kerja']` | String |
| `$unit['total_jumlah_biaya']` | `$unitTerendah5[$i]['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$unit['total_realisasi']` | `$unitTerendah5[$i]['total_realisasi']` | `number_format(..., 0, ',', '.')` |
| `$unit['total_sisa']` | `$unitTerendah5[$i]['total_sisa']` | `number_format(..., 0, ',', '.')` |
| `$unit['avg_persentase']` | `$unitTerendah5[$i]['avg_persentase']` | `number_format(..., 2, ',', '.')` + `%` |

### Card 5: Distribusi Jenis RAB
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$jenis['jenis']` | `$distribusiJenisRab[$i]['jenis']` | String |
| `$jenis['total_jumlah_biaya']` | `$distribusiJenisRab[$i]['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$jenis['count']` | `$distribusiJenisRab[$i]['count']` | Integer |
| Progress bar width | `($jenis['total_jumlah_biaya'] / $totalSemua['total_jumlah_biaya']) * 100` | Percentage |

### Card 6: Distribusi Sumber Dana
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$sd['sumberdana']` | `$distribusiSumberDana[$i]['sumberdana']` | String |
| `$sd['total_jumlah_biaya']` | `$distribusiSumberDana[$i]['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$sd['count']` | `$distribusiSumberDana[$i]['count']` | Integer |
| Progress bar width | `($sd['total_jumlah_biaya'] / $totalSemua['total_jumlah_biaya']) * 100` | Percentage |

### Card 7: Unit > 100%
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$unit['unit_kerja']` | `$unitDiatas100[$i]['unit_kerja']` | String |
| `$unit['total_jumlah_biaya']` | `$unitDiatas100[$i]['total_jumlah_biaya']` | `number_format(..., 0, ',', '.')` |
| `$unit['total_realisasi']` | `$unitDiatas100[$i]['total_realisasi']` | `number_format(..., 0, ',', '.')` |
| `$unit['total_sisa']` | `$unitDiatas100[$i]['total_sisa']` | `number_format(..., 0, ',', '.')` |
| `$unit['avg_persentase']` | `$unitDiatas100[$i]['avg_persentase']` | `number_format(..., 2, ',', '.')` + `%` |

### Card 8: DataTables
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `window.dataRktUnit` | `$dataPerUnitArray` | JSON encoded via `@json()` |

### Card 9: Dokumentasi
| Blade Variable | Controller Source | Formatting |
| -------------- | ----------------- | ---------- |
| `$backupKeterangan` | `$backupKeterangan` | String (default: 'Data Terbaru') |
| `$backupTahun` | `$backupTahun` | String |

## Empty State Handling

Semua variable menggunakan null coalescing operator `??` untuk handle empty state:
- `$totalSemua['total_jumlah_biaya'] ?? 0`
- `$backupKeterangan ?? 'Data Terbaru'`
- `@forelse($unitTertinggi5 as $unit)` untuk table dengan empty state
