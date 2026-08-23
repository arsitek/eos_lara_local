# Plan — Audit & Revisi Coding Guide Statistik RKT Unit

Melakukan audit dan revisi terhadap Coding Guide yang sudah dibuat untuk memastikan semua task yang membutuhkan implementasi code menyediakan code konkret siap copy-paste, khususnya RKT-010 (View Blade).

## Context

User menemukan masalah pada Coding Guide: RKT-010 memberikan instruksi seperti "Implementasikan Card 2: Statistik Status" tetapi tidak menyediakan code Blade konkret yang dapat langsung copy-paste. Ini tidak sesuai dengan prinsip CODE-FIRST, EXPLANATION-ALONGSIDE.

## Current State

- RKT-007 (rktUnit method): SUDAH diimplementasikan oleh user di StatistikController
- RKT-008 (getRktUnitDataTable method): SUDAH diimplementasikan oleh user di StatistikController
- RKT-009 (statistik-rktunit.js): SUDAH dibuat oleh user (menggunakan window.dataRktUnit pattern)
- RKT-010 (rktUnit.blade.php): DIBUAT oleh user tapi masih kosong (hanya "tess")
- Pattern existing tersedia di dayaserap.blade.php

## Approach

### 1. Audit Coding Guide

Periksa RKT-001 sampai RKT-015 untuk mencari instruksi yang berbentuk "Implementasikan..." tanpa code konkret.

### 2. Revisi RKT-010 (Priority Utama)

Buat code Blade lengkap untuk semua komponen UI berdasarkan pattern existing dari dayaserap.blade.php:
- Card 1: KPI Utama (Total RKT)
- Card 2: Statistik Status (Sudah, Belum, Draft)
- Card 3: 5 Unit Tertinggi
- Card 4: 5 Unit Terendah
- Card 5: Distribusi Jenis RAB
- Card 6: Distribusi Sumber Dana
- Card 7: Unit > 100%
- Card 8: DataTables dengan filter
- Card 9: Dokumentasi

### 3. Mapping Controller → Blade

Buat tabel mapping variable dari Controller ke Blade:
- Variable yang dikirim dari compact()
- Variable yang digunakan di Blade
- Formatting yang dilakukan

### 4. Final Completeness Audit

Buat tabel audit untuk semua task:
- Memerlukan Code?
- Code Tersedia?
- Siap Copy-Paste?
- Status

### 5. Output

Berikan:
1. Audit Findings
2. Revised Coding Guide (khusus RKT-010)
3. Mapping Controller → Blade
4. Final Completeness Audit
5. Commit Sequence

## Files yang Diperiksa

- `C:\Users\X1 Carbon\.windsurf\plans\statistik-rktunit-coding-guide-c4c5ad.md` (Coding Guide yang akan diaudit)
- `d:\2027\eos_lara\resources\views\statistik\dayaserap.blade.php` (Pattern existing)
- `d:\2027\eos_lara\app\Http\Controllers\StatistikController.php` (Variable yang dikirim ke view)
- `d:\2027\eos_lara\resources\views\statistik\rktUnit.blade.php` (View yang akan diisi)

## Notes

- Jangan mengarang UI pattern - gunakan pattern existing dari dayaserap.blade.php
- Pastikan semua code Blade siap copy-paste
- Gunakan nama variable aktual dari Controller
- Tetap pertahankan prinsip CODE-FIRST, EXPLANATION-ALONGSIDE
