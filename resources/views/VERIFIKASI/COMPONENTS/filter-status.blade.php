<select name="filter_status" class="s" style="width:300px">
    <option value="">Filter status</option>
    <option value="BelumVerifikasi"
    @if( request()->get('status') === "BelumVerifikasi" )
        selected @endif>Belum di verifikasi</option>
    <option value="Setuju"
    @if( request()->get('status') === "Setuju" )
        selected @endif>Di setujui</option>
    <option value="!tor"
    @if( request()->get('status') === "!tor" )
        selected @endif>Tor tidak lengkap</option>
    <option value="lengkap"
    @if( request()->get('status') === "lengkap" )
        selected @endif>Data lengkap</option>
</select>
