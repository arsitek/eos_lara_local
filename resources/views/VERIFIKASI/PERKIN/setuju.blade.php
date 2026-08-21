
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Perjanjian Kinerja di Setujui</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="tabel-perkinSetuju table table-bordered border mb-0" id="new-edit">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Unit<span style="visibility:hidden;">_</span>kerja</th>
                                    <th>Kode IK</th>
                                    <th>Indikator<span style="visibility: hidden;">_</span>kinerja<span
                                            style="visibility:hidden;">_</span>kegiatan</th>
                                    <th>KK MENDIKBUD</th>
                                    <th>TW 1</th>
                                    <th>TW 2</th>
                                    <th>TW 3</th>
                                    <th>TW 4</th>
                                    <th>Bobot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($verPerkin as $data)
                                @if($data->verifikasi_tim == "Setuju" && $data->verifikasi_pimpinan == "Setuju")
                                <tr>
                                    <td key="{{$data->id}}">{{ $loop->iteration }}</td>
                                    <td>{{$data->unitApi->nama ?? '-'}}</td>
                                    <td>{{ $data->kode_ikk }}</td>
                                    <td> {{ $data->ro->indikator_kinerja_kegiatan }}</td>
                                    <td> {{ $data->kk_mendikbud }}</td>
                                    <td id="tw_1"> {{ $data->tw_1 }}</td>
                                    <td id="tw_2"> {{ $data->tw_2 }}</td>
                                    <td id="tw_3"> {{ $data->tw_3 }}</td>
                                    <td id="tw_4"> {{ $data->tw_4 }}</td>
                                    <td class="bobot"> {{ $data->bobot }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
