<select name="subkomponen" class="subkomponen select2" style="width: 300px">
    <option value="">Pilih Subkomponen</option>
    @foreach ($subkomponen as $item)
        <option value="{{ $item->kode_keg }}">{{ $item->kode_keg }} | {{ $item->rincian_kegiatan }}</option>
    @endforeach
</select>
