@extends('layouts/layoutMaster')
@section('title', 'Klasifikasi Rka | Sasaran')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="row">
            <div role="button" style="font-size:15px; font-weight:bold;color:black" class="col-md-12 mb-2 p-5 s1 btn-satu"> [S 1] Meningkatnya kualitas lulusan pendidikan tinggi</div>
            <div role="button" style="font-size:15px; font-weight:bold;color:black" class="col-md-12 mb-2 p-5 s2 btn-dua"> [S 2] Meningkatnya kualitas dosen pendidikan tinggi</div>
            <div role="button" style="font-size:15px; font-weight:bold;color:black" class="col-md-12 mb-2 p-5 s3 btn-tiga"> [S 3] Meningkatnya kualitas kurikulum dan pembelajaran</div>
            <div role="button" style="font-size:15px; font-weight:bold;color:black" class="col-md-12 mb-2 p-5 s4 btn-empat"> [SK 4] Meningkatnya tata kelola Satuan Kerja di lingkungan Ditjen Pendidikan Tinggi </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        $(document).ready( function () {
            $(".s1").on("click", () => {
                const s1_link = document.createElement("a")
                s1_link.href = "/laporanrka/sasaran/satu"
                s1_link.target = "_blank"
                s1_link.click()
            })
            $(".s2").on("click", () => {
                const s2_link = document.createElement("a")
                s2_link.href = "/laporanrka/sasaran/dua"
                s2_link.target = "_blank"
                s2_link.click()
            })
            $(".s3").on("click", () => {
                const s3_link = document.createElement("a")
                s3_link.href = "/laporanrka/sasaran/tiga"
                s3_link.target = "_blank"
                s3_link.click()
            })
            $(".s4").on("click", () => {
                const s4_link = document.createElement("a")
                s4_link.href = "/laporanrka/sasaran/empat"
                s4_link.target = "_blank"
                s4_link.click()
            })
        })
    </script>
@endpush
