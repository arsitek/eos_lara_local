@extends('layout.layout')
@section('title', 'laporan rka')
@section('content')
    <div class="row mt-5">
         <div class="col-lg-12">
      <div class="row mb-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">RKA</h3>
            </div>
        <div class="card-body">
        <div class="table-responsive py-4">
        <table class="tabel-rka table table-bordered border mb-0" id="new-edit">
        <thead>
            <th>No</th>
            <th>Keg</th>
            <th>Kro</th>
            <th>Ro</th>
            <th>KP</th>
            <th>SK</th>
            <th>AK</th>
            <th>Uraian</th>
            <th>Detil</th>
            <th>Volume</th>
            <th>Satuan Biaya</th>
            <th>Satuan Jumlah</th>
        </thead>
        <label style="color:black;font-size:20px" id="status"></label>
        <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>   
        </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
