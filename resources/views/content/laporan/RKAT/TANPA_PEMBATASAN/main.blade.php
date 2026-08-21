@extends('layout.layout')
@section('title', 'RKAT | Tanpa Pembatasan')
@section('content')
@push('yss')
    <style>
.loader {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: inline-block;
  position: relative;
  border: 3px solid;
  border-color: #FFF #FFF transparent;
  box-sizing: border-box;
  animation: rotation 1s linear infinite;
}
.loader::after {
  content: '';  
  box-sizing: border-box;
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  margin: auto;
  border: 3px solid;
  border-color: transparent #FF3D00 #FF3D00;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  animation: rotationBack 0.5s linear infinite;
  transform-origin: center center;
}

@keyframes rotation {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
} 
    
@keyframes rotationBack {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(-360deg);
  }
}
    
  
    </style>
@endpush
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">LAPORAN RKAT TANPA PEMBATASAN</h3></div>
            <div class="card-body">
                <div>
                    <span class="loader" id="loading-spin">></span>
                    <h4 style="font-weight:bold; display: inline; margin-left:10px;" class="loading-msg">MEMUAT DATA RKAT... MOHON MENUNGGU</h4>
                </div>
                <div class="table-responsive mt-2">
                    <table class="tabel-rkat table table-bordered border mb-0" id="new-edit">
                        <thead>
                            <tr>
                                <th>KODEFIKASI</th>
                                <th>URAIAN</th>
                                <th>VOLUME</th>
                                <th>SATUAN</th>
                                <th>ANGGARAN BIAYA</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    @include('content.laporan.RKAT.TANPA_PEMBATASAN.script')
@endpush
