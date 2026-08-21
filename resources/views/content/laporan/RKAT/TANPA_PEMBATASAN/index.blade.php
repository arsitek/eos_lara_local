@extends('layouts/layoutMaster')
@section('title', 'RKAT | Tanpa Pembatasan')
@section('content')
@push('yss')
    <style>
              
              .btn-51 {
            background-image: linear-gradient(to right, #1e130c 0%, #9a8478  51%, #1e130c  100%);
            margin: 10px;
            padding: 15px 45px;
            text-align: center;
            text-transform: uppercase;
            transition: 0.5s;
            background-size: 200% auto;
            color: white;            
            box-shadow: 0 0 20px #eee;
            border-radius: 10px;
            display: block;
          }

          .btn-51:hover {
            background-position: right center; /* change the direction of the change here */
            color: #fff;
            text-decoration: none;
          }
                 
          .btn-51-01 {
            background-image: linear-gradient(to right, #8e9eab 0%, #eef2f3  51%, #8e9eab  100%);
            margin: 10px;
            padding: 15px 45px;
            text-align: center;
            text-transform: uppercase;
            transition: 0.5s;
            background-size: 200% auto;
            color: white;            
            box-shadow: 0 0 20px #eee;
            border-radius: 10px;
            display: block;
          }

          .btn-51-01:hover {
            background-position: right center; /* change the direction of the change here */
            color: #fff;
            text-decoration: none;
          }
                  
          .btn-51-01-01 {
            background-image: linear-gradient(to right, #abbaab 0%, #ffffff  51%, #abbaab  100%);
            margin: 10px;
            padding: 15px 45px;
            text-align: center;
            text-transform: uppercase;
            transition: 0.5s;
            background-size: 200% auto;
            color: white;            
            box-shadow: 0 0 20px #eee;
            border-radius: 10px;
            display: block;
          }

          .btn-51-01-01:hover {
            background-position: right center; /* change the direction of the change here */
            color: #fff;
            text-decoration: none;
          }
         
         
/*-------------vertical-tree-view------------*/
.vertical-tree{
    padding-top: 40px;
    padding-bottom: 40px;
}
.vertical-tree ul{
    padding-left: 30px;
}
.vertical-tree li {
    margin: 0px 0;
    list-style-type: none;
    position: relative;
    padding: 20px 5px 0px 5px;
}
.vertical-tree li::before{
    content: '';
    position: absolute; 
    top: 0;
    width: 1px; 
    height: 100%;
    right: auto; 
    left: -20px;
    border-left: 2px solid #ccc;
    bottom: 50px;
}
.vertical-tree li::after{
    content: '';
    position: absolute; 
    top: 34px; 
    width: 25px; 
    height: 20px;
    right: auto; 
    left: -20px;
    border-top: 2px solid #ccc;
}
.vertical-tree li a{
    display: inline-block;
    padding: 8px 30px;
    text-decoration: none;
    background-color: #e1eafc;
    color: black;
    font-weight:bold;
    border: 1px solid #e1eafc;
    font-size: 13px;
    border-radius: 4px;
}
.vertical-tree > ul > li::before, 
.vertical-tree > ul > li::after{
    border: 0;
}
.vertical-tree li:last-child::before{ 
        height: 34px;
}
.vertical-tree li a:hover, 
.vertical-tree li a:hover+ul li a {
    background-color: #5a8dee;
    color: black;
    border: 1px solid #5a8dee;
}
.vertical-tree li a:hover+ul li::after, 
.vertical-tree li a:hover+ul li::before, 
.vertical-tree li a:hover+ul::before, 
.vertical-tree li a:hover+ul ul::before{
    border-color:  #fbba00;
}
    </style>
@endpush
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="row">
            <!-- <div role="button" style="font-size:15px; font-weight:bold;color:black" class="btn-01 col-md-12 mb-2 p-2 s1 btn-satu"></div> -->
            <!-- <div role="button" style="font-size:15px; font-weight:bold;color:black" class="btn-02 col-md-12 mb-2 p-2 s2 btn-dua"> </div> -->
            <!-- <div role="button" style="font-size:15px; font-weight:bold;color:black" class="btn-03 col-md-12 mb-2 p-2 s2 btn-tiga"> [</div> -->
        </div>
    <div class="col-lg-12">
    <div class="vertical-tree">
    <ul>
        <li>
            <a href="javascript:void(0);" class="btn-51" style="color:white"> Sumber Dana Non APBN</a>
            <ul>
                <li>
                    <a href="javascript:void(0);" class="btn-51-01">[01] Layanan Tridarma dan Proses Belajar Mengajar</a>
                    <ul>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,1])}}" class="btn-51-01-01">Akselerasi Kesiapan kerja lulusan</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,2])}}" class="btn-51-01-01">Akselerasi Mahasiswa berkegiatan/meraih prestasi di luar program studi</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,3])}}" class="btn-51-01-01">Dosen di luar kampus</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,4])}}" class="btn-51-01-01">Kualifikasi dosen/pengajar</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,5])}}" class="btn-51-01-01">Penerapan karya dosen</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,6])}}" class="btn-51-01-01"> Kemitraan program studi</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,7])}}" class="btn-51-01-01"> Pembelajaran dalam kelas</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [1,8])}}" class="btn-51-01-01"> Akreditasi Internasional</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="btn-51-01">[02] Layanan Manajemen Perkantoran</a>
                    <ul>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [2,1])}}" class="btn-51-01-01">Rata-rata predikat SAKIP Satker minimal BB</a></li>
                        <li><a href="{{ route('rkat.tanpaPembatasan.filter', [2,2])}}" class="btn-51-01-01">Rata-rata nilai Kinerja Anggaran atas Pelaksanaan RKA-K/L Satker minimal 80</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);" class="btn-51-01">[03] Operasional Penunjang Layanan Manajemen Perkantoran</a>
                    <ul>
                        <li>
                            <a href="{{ route('rkat.tanpaPembatasan.filter', [3,2])}}" class="btn-51-01-01">Gaji dan Tunjangan</a>
                        </li>
                        <li>
                            <a href="{{ route('rkat.tanpaPembatasan.filter', [3,3])}}" class="btn-51-01-01">Investasi Sarana dan Prasarana</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</div>
    </div>
</div>
@endsection
@push("scripts")
<script>
    $( document ).on( "click", ".btn-01" , function() { window.open("{{ route('rkat.index.tanpaPembatasan.01') }}","_blank") } )
    $( document ).on( "click", ".btn-02" , function() { window.open("{{ route('rkat.index.tanpaPembatasan.02') }}","_blank") } )
    $( document ).on( "click", ".btn-03" , function() { window.open("{{ route('rkat.index.tanpaPembatasan.03') }}","_blank") } )
</script>
<script>
        // ------Vertical-tree-view
$(function () {
    $('.vertical-tree ul').hide();
    $('.vertical-tree>ul').show();
    $('.vertical-tree ul.active').show();
    $('.vertical-tree li').on('click', function (e) {
        var children = $(this).find('> ul');
        if (children.is(":visible")) children.hide('fast').removeClass('active');
        else children.show('fast').addClass('active');
        e.stopPropagation();
    });
});


    </script>
@endpush
