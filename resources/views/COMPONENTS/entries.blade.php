<select name="jumlah_data" class="s" style="width: 60px">
    <option value="5" @if(request()->jumlah_data == "5") selected @endif>5</option>
    <option value="10" @if(request()->jumlah_data == "10") selected @endif>10</option>
    <option value="25" @if(request()->jumlah_data == "25") selected @endif>25</option>
    <option value="50" @if(request()->jumlah_data == "50") selected @endif>50</option>
</select>