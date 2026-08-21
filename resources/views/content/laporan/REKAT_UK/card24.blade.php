<div class="card-header d-flex">
    <table class="table">
        <tr>
            <td></td>
            <td class="fw-bold">RINCIAN KERTAS KERJA SATKER T.A. {{ $tahunAngka }}</td>
        </tr>
        <tr>
            <td>KEMENTRIAN LEMBAGA</td>
            <td>(023) KEMENTRIAN PENDIDIKAN TINGGI SAINS DAN TEKNOLOGI</td>
        </tr>
        <tr>
            <td>UNIT ORGANISASI</td>
            <td>(17) DITJEN PENDIDIKAN TINGGI</td>
        </tr>
        <tr>
            <td>PTN/KOPERTIS</td>
            <td>(690662) UNIVERSITAS SYIAH KUALA</td>
        </tr>
        <tr>
            <td>UNIT KERJA</td>
            @if( @session()->get('role') == "superadmin" )
            <td id="unitkerja">{{ $unitkerja->nama }}</td>
            @else
            <td> {{ session()->get('unitkerja_nama') }}</td>
            @endif
        </tr>
        @foreach ($sumberdana as $sd)
        <tr>
            <td class="sumberdanaHeader" key="{{$sd->kd_sumberdana}}" style="width: 400px">{{$sd->sumberdana}}</td>
            <td class="total-{{$sd->kd_sumberdana}}">0</td>
        </tr>
        @endforeach
        <tr>
            <td>Total</td>
            <td class="total">0</td>
        </tr>
    </table>
</div>
