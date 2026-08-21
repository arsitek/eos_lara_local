{{--
    Dynamic table headers for Pendapatan report (reusable partial)
    - If $tahunAngka = 2025 -> show columns 2024 and 2025
    - If $tahunAngka >= 2026 -> show columns (tahunAngka - 1) and tahunAngka
    - This is generic: it always shows the previous year and the current year
--}}
@php
    // Defensive: make sure we have an integer year. If not provided, try to derive it from session or fallback to current year.
    $currentYear = isset($tahunAngka) ? (int) $tahunAngka : null;
    if (! $currentYear) {
        $sess = session('tahun');
        if ($sess && is_string($sess) && strpos($sess, '_') !== false) {
            $parts = explode('_', $sess);
            $currentYear = (int) ($parts[1] ?? now()->year);
        } else {
            $currentYear = (int) now()->year;
        }
    }
    // The spec asks to show previous and current year columns (2024 & 2025 for 2025, 2025 & 2026 for 2026, etc.)
    $prevYear = $currentYear - 1;
@endphp

<thead class="header">
    <tr>
        <th rowspan="2" class="text-center" style="vertical-align: middle">SUMBER DANA</th>
        <th colspan="2" class="text-center" style="border-left: 2px solid white; border-bottom: 2px solid white ">TARGET</th>
        <th colspan="2" class="text-center" style="border-right: 2px solid white;border-left: 2px solid white; border-bottom: 2px solid white">REALISASI</th>
        <th colspan="2" class="text-center" style="border-right: 2px solid white; border-bottom: 2px solid white">PERSENTASE</th>
        <th colspan="2" class="text-center" style="border-bottom: 2px solid white;">SELISIH</th>
    </tr>
    <tr style="border-bottom: 2px solid white; text-align: center">
        {{-- First column is previous year, second is current year. Keep the same styling as original file. --}}
        <th style="border-left: 2px solid white" data-tahun="{{ $prevYear }}">{{ $prevYear }}</th>
        <th style="border-right: 2px solid white" data-tahun="{{ $currentYear }}">{{ $currentYear }}</th>
        <th data-tahun="{{ $prevYear }}">{{ $prevYear }}</th>
        <th style="border-right: 2px solid white" data-tahun="{{ $currentYear }}">{{ $currentYear }}</th>
        <th data-tahun="{{ $prevYear }}">{{ $prevYear }}</th>
        <th style="border-right: 2px solid white" data-tahun="{{ $currentYear }}">{{ $currentYear }}</th>
        <th data-tahun="{{ $prevYear }}">{{ $prevYear }}</th>
        <th data-tahun="{{ $currentYear }}">{{ $currentYear }}</th>
    </tr>
</thead>
