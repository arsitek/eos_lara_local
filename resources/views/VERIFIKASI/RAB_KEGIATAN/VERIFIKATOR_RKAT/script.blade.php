<script>
$(document).ready(function() {
    // 🚀 Initialize variable
    let tempdata       = []
    const url          = new URL( window.location.href )
    const idunit       = $("select.unit_kerja").filter(function () {
        return $(this).parent().parent().css("display") !== "none";
    })
    const kodeSd       = $("select.sumberdana").filter(function () {
        return $(this).parent().parent().css("display") !== "none";
    })

    const editable_row = [ 7, 11]
    let is_edit        = false
    const select2      = $(".s").select2()
    const tbodyVerKeg  = $(".tabel-verkeg tbody")
    const userAgent    = navigator.userAgent
    const screenSize   = `${window.screen.availWidth} x ${window.screen.availHeight}`
    const platform     = navigator.platform
    const lang         = navigator.language
    const datatable    = $('.tabel-verkeg').DataTable({
        "pageLength": 10,
        "ordering": false,
        "rowsGroup": [1, 2, 3,4]
    });
    let anggaran_teralokasi = $(".alokasi-anggaran").text()
    let anggaran_terpetakan = $(".anggaran-terpetakan").text()
    if ( '-' !== anggaran_teralokasi ) {
        $(".alokasi-anggaran").text( rupiah(anggaran_teralokasi) )
    }
    if ( '-' !== anggaran_terpetakan ) {
        $(".anggaran-terpetakan").text( rupiah(anggaran_terpetakan) )
    }

    // ❓ check button click count
    let clickCount = 0;

    // ✅ onclick btn save
    $( document ).on("click", ".btn-save", function() {
        if ( is_edit === false ) {
            return tata.warn("⚠️ Perhatian", "Anda belum melakukan edit data.")
        }
        // get dropdown and id value
        const row                = $(this).closest("tr")
        const id_rab             = row.find('.id').attr('key')
        const id_rekat           = row.find('.id').attr('rekat')
        const rpd                = row.find('select.rpd').val()
        const kebutuhan_kegiatan = row.find('.kk').text()
        const biaya_satuan       = row.find('.biaya_satuan').text()
        const id_coa             = row.find('select.jenis_belanja').val().split("[")[0]
        const coa                = row.find('select.jenis_belanja option:selected').text().split("] ")[1].trim()
        const jenis              = "updateItemCoa"
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} })

        $.ajax({
            type: "POST", url: "{{ route("vRabKeg.store") }}",
            data: { "_token": "{{ csrf_token() }}",
                id_rab, kebutuhan_kegiatan, biaya_satuan, id_coa, coa, is_edit, rpd, jenis
            },
            success: ( res ) => {
                // console.log( res )
                const data = res.data
                const row = $(this).closest("tr")
                if ( false === res.success ) {
                    return tata.warn("⚠️ Perhatian", res.message)
                }
                if ( true == is_edit ) {
                    row.find('.coa').empty()
                    row.find('.coa').html(`[ ${res.data.id_jenis_belanja} ] ${res.data.jenis_belanja}`)
                    row.find(".biaya_satuan").html( rupiah(res.data.biaya_satuan) )
                    row.find(".jumlah_biaya").html( rupiah(res.data.jumlah_biaya) )
                    row.find("td.rpd").html( res.data.rpd )
                    row.find(".status").html("")
                    is_edit = false
                }
                editable_row.forEach( item => {
                    row.find(`td:nth-child(${item})`).attr('contenteditable', false)
                })
                tempdata = {}
                return tata.success("✅ Sukses", res.message)
            }, error: ( err ) => {
                const error = err.responseJSON.message
                return tata.error("⛔ Error", error)
            }
        })
    })

    // ✅ onclick btn edit
    $( document ).on("click", ".btn-edit", function() {
        const row            = $(this).closest('tr')
        const id             = row.find('td:nth-child(1)').attr("key")
        const td_coa         = row.find('td:nth-child(5)')
        const td_rpd         = row.find('td:nth-child(6)')
        const biaya_satuan   = row.find('.biaya_satuan')
        const edit_key       = $(this).attr('edit');

        // 🚀 Toggle the value of the 'edit' attribute
        const edit_status    = edit_key === 'true' ? 'false' : 'true'
        is_edit              = edit_key === 'true' ? false : true
        // Set the updated value
        $(this).attr('edit', edit_status);
        // ❓ Optionally, you can check the value and perform additional actions

        if (edit_status === 'true') {
            console.log('🚀 Editing enabled')

            // row.find(".status").html(`Anda sedang mengedit id rab ${id}`)
            tempdata = {
                id,
                id_coa      : td_coa.attr("key"),
                coa         : td_coa[0].innerHTML,
                rpd         : td_rpd[0].innerHTML,
                biaya_satuan: biaya_satuan.text()
            }
            const rpd = [...Array(12).keys()].map(i => i + 1).map( opt => `<option value="${opt}" ${opt == tempdata.rpd ? 'selected' : ''}>${opt}</option>`).join('')

            editable_row.forEach( item => {
                row.find(`td:nth-child(${item})`).attr('contenteditable', true)
            })
            biaya_satuan.text( rupiahToNumber( biaya_satuan.text() ) )
            td_coa.html(`<select name="jenis_belanja" style="width:300px" class="s jenis_belanja">
                        <option value="${tempdata.id_coa}" selected>[ ${tempdata.id_coa} ] ${tempdata.coa.split("]")[1]}</option>
                        @foreach($jenis_belanja as $item)
                            <option value="{{ $item['id_coa'] }}">{{ '['.$item['id_coa'].']'.' '.  $item['coa'] }}</option>
                        @endforeach
            </select>`)
            td_rpd.html(`<select name="rpd" style="width:50px" class="s rpd">${rpd}</select>`)
            $(".s").select2()
            $(".btn-edit").prop('disabled', true)
            $(this).prop('disabled', false)
        } else {
            console.log('🚀 Editing disabled')
            row.find(".status").html("")
            $(".btn-edit").prop('disabled', false)
            td_coa.html(tempdata.coa)
            td_rpd.html(tempdata.rpd)
            biaya_satuan.text(tempdata.biaya_satuan )
            editable_row.forEach( item => {
                row.find(`td:nth-child(${item})`).attr('contenteditable', false)
            })
            tempdata = {}
        }
    })

    // ✅ onchange file tor upload
    $( document ).on("change", ".tor-verifikator", function(e){
        const $tr      = $(this).closest("tr")
        const filename = $tr.find('input[type=file]').val().replace(/C:\\fakepath\\/i, '')
        const tor_name = $tr.find('.tor-name')
        tor_name.html(filename)
    })

    // ✅ onclick button serach by unitkerja
    $( document ).on("click", ".btn-filter-unitkerja", function(){

        if ( idunit.val() === "") {
            return tata.warn("Perhatian", "Anda belum memilih unitkerja.")
        }
        if ( kodeSd.val() === "" || kodeSd.val() === "-" ) {
            return tata.warn("Perhatian", "Anda belum memilih sumberdana")
        }
        window.open(`/verifikasi/kegiatan/${idunit.val()}?kd_sumberdana=${kodeSd.val()}`, '_self')
    })

    // ✅ oncheck input verifikasi pimpinan unit
    $( document ).on("click", ".switchVerifikasi", (e) => {
        const $target   = $(e.target)
        const $tr       = $target.closest("tr")
        const isChecked = $target.is(":checked")
        const id        = $tr.find(".id").attr("key")
        const jenis     = $target.data("jenis")
        const tanggapan = $tr.find(".tanggapan").text()
        const status    = isChecked ? "Setuju" : "Tolak"

        $.ajax({
            type: "POST",
            url: "{{ route('vRabKeg.storeVerif') }}",
            data: { _token: "{{ csrf_token() }}", id, isChecked, tanggapan, jenis, status, userAgent, screenSize, platform, lang, },
            success: ( res ) => {
                // console.log( res )
                const { message } = res
                return tata.success("✅ Sukses", message)
            },
            error: ( err ) => {
                const message = res.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                return tata.error("⛔ Error", message)
            }
        })
    })

    const muatDataFilter = ( data ) => {
        return data.reduce( ( html, item, index ) => {
            let torHTML = ''
            if (item.tor) {
                torHTML += `
                    <a href="/uploads/tor/${item.tor}"
                    target="_blank"
                    class="btn btn-primary" {{$isCrud == 0 ? 'style="pointer-events: none;"' : ''}}>
                    Download TOR
                    </a>`
            } else {
                torHTML += 'Tor tidak tersedia'
            }
            return html + `
                <tr>
                    <td class="id" key="${item.id}">${index + 1}</td>
                    <td class="idRekat">${item.id_rekat}</td>
                    <td class="sk"> ${ ' [ ' + item.kd_rk + ' ] ' + item.rincian_kegiatan } </td>
                    <td class="dk"> ${ item.sub_judul }</td>
                    <td class="tor">${torHTML}</td>
                    <td class="coa">${ ' [ ' + item.id_jenis_belanja + ' ] ' + item.jenis_belanja }}</td>
                    <td class="rpd">${item.rpd}</td>
                    <td class="kuantitas">${item.kuantitas}</td>
                    <td class="satuan_kuantitas"> ${ item.satuan_kuantitas }</td>
                    <td class="durasi"> ${ item.durasi }</td>
                    <td class="satuan_durasi"> ${ item.satuan_durasi }</td>
                    <td class="kegiatan"> ${ item.kegiatan }</td>
                    <td class="satuan_kegiatan"> ${ item.satuan_kegiatan }</td>
                    <td class="kk"> ${item.kebutuhan_kegiatan}</td>
                    <td class="biaya_satuan"> ${ item.formatted_biaya_satuan }</td>
                    <td class="jumlah_biaya"> ${ item.formatted_jumlah_biaya }</td>
                    <td>
                        <label class="switch">
                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiTim" ${item.verifikasi_tim == 'Setuju' ? 'checked' : ''}>
                        <span class="slider round"></span>
                        </label>
                    </td>
                    <td>
                        <label class="switch">
                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiAset" ${item.verifikasi_aset == 'Setuju' ? 'checked' : ''}>
                        <span class="slider round"></span>
                        </label>
                    </td>
                    <td>
                        <label class="switch">
                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiKeu" ${item.verifikasi_keu == 'Setuju' ? 'checked' : ''}>
                        <span class="slider round"></span>
                        </label>
                    </td>
                    <td>
                        <label class="switch">
                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiPimpinanUnit" ${item.verifikasi_pimpinan_unit == 'Setuju' ? 'checked' : ''}>
                        <span class="slider round"></span>
                        </label>
                    </td>
                    <td>
                        <label class="switch">
                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiPimpinanUniv" ${item.verifikasi_pimpinan_univ == 'Setuju' ? 'checked' : ''}>
                        <span class="slider round"></span>
                        </label>
                    </td>
                    <td class="tanggapan" contenteditable="true">${item.tanggapan ?? ''}</td>
                    <td>
                        <div class="btn-group">
                            <a class="btn-edit-modal text-white" href="/verifikasi/kegiatan/edit/${item.id}?kd_sumberdana=${item.sumberdana}" target="_blank">
                                <i role="button" class="bg-success px-2 mx-1 py-2 fa-solid fe fe-edit"></i>
                            </a>
                        </div>
                        <span class="status mt-1"></span>
                    </td>
                </tr>
            `
        }, "")
    }
    const syncParam = () => {
        url.searchParams.set("idunit", idunit.val())
        window.history.replaceState({}, '', url)

        if ( url.searchParams.get("idunit") !== null ) {
            idunit.val( url.searchParams.get("idunit") ).trigger("change")
        }
        if ( url.searchParams.get("kd_sumberdana") !== null ) {
            kodeSd.val( url.searchParams.get("kd_sumberdana") ).trigger("change")
        }
    }
    syncParam()
})
</script>

