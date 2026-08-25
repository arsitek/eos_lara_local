/**
 * Statistik RKT Unit DataTable (js)
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const dt_rktunit_table = document.querySelector('.dt-rktunit');
  if (dt_rktunit_table) {
    let dt_rktunit = new DataTable(dt_rktunit_table, {
      data: window.dataRktUnit || [],
      columns: [
        {
          data: 'unit_kerja',
          width: '20%',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        {
          data: 'sumberdana',
          width: '15%',
          className: 'text-wrap',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        {
          data: 'rab_type',
          width: '10%',
          className: 'text-wrap',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        {
          data: 'kode_keg',
          width: '10%',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        {
          data: 'rincian_kegiatan',
          width: '20%',
          className: 'text-wrap',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        {
          data: 'jumlah_biaya',
          width: '10%',
          className: 'text-end',
          render: function (data, type, row) {
            return type === 'display' ? new Intl.NumberFormat('id-ID').format(data) : data;
          }
        },
        {
          data: 'realisasi',
          width: '10%',
          className: 'text-end',
          render: function (data, type, row) {
            return type === 'display' ? new Intl.NumberFormat('id-ID').format(data) : data;
          }
        },
        {
          data: 'sisa',
          width: '10%',
          className: 'text-end',
          render: function (data, type, row) {
            return type === 'display' ? new Intl.NumberFormat('id-ID').format(data) : data;
          }
        },
        {
          data: 'persentase',
          width: '5%',
          className: 'text-end',
          render: function (data, type, row) {
            return type === 'display' ? data + '%' : data;
          }
        }
      ],
      columnDefs: [
        {
          targets: '_all',
          responsivePriority: 1
        }
      ],
      layout: {
        topStart: {
          rowClass: 'row mx-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: 'Show_MENU_entries'
              }
            }
          ]
        },
        topEnd: {
          search: {
            placeholder: 'Type search here'
          }
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      language: {
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
          first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
          last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
        },
        emptyTable: 'Tidak ada data untuk ditampilkan'
      },
      responsive: true,
      order: [[0, 'asc']]
    });
  }

  // Filter dropdown change event
  $('#filter-status').on('change', function () {
    const filterValue = $(this).val();

    // Update judul tabel berdasarkan filter
    const titleMap = {
      semua: 'Rincian RKT Unit - Semua Data',
      realisasi: 'Rincian RKT Unit - Sudah Realisasi',
      '!realisasi': 'Rincian RKT Unit - Belum Realisasi',
      draft: 'Rincian RKT Unit - Draft'
    };

    $('#table-title').text(titleMap[filterValue] || 'Rincian RKT Unit');

    // Filter data lokal berdasarkan filter
    if (dt_rktunit) {
      let filteredData = window.dataRktUnit || [];

      if (filterValue === 'realisasi') {
        filteredData = filteredData.filter(item => item.realisasi > 0);
      } else if (filterValue === '!realisasi') {
        filteredData = filteredData.filter(item => item.realisasi === 0 && item.is_draft !== 'true');
      } else if (filterValue === 'draft') {
        filteredData = filteredData.filter(item => item.is_draft === 'true');
      }

      dt_rktunit.clear().rows.add(filteredData).draw();
    }
  });

  // Filter form control to default size
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-4' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-end', classToAdd: 'mt-0' },
      { selector: '.dt-layout-end .dt-search', classToAdd: 'mt-md-6 mt-0' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);
});

// Financial formatter for executive-friendly display
function formatFinancial(value) {
  if (value >= 1000000000000) {
    return 'Rp' + (value / 1000000000000).toFixed(1).replace('.', ',') + ' T';
  } else if (value >= 1000000000) {
    return 'Rp' + (value / 1000000000).toFixed(1).replace('.', ',') + ' M';
  } else if (value >= 1000000) {
    return 'Rp' + (value / 1000000).toFixed(1).replace('.', ',') + ' Jt';
  } else if (value >= 1000) {
    return 'Rp' + (value / 1000).toFixed(1).replace('.', ',') + ' Rb';
  } else {
    return 'Rp' + value.toLocaleString('id-ID');
  }
}

// Financial Distribution Chart
if (document.querySelector('#financialDistributionChart')) {
  var financialOptions = {
    series: [
      {
        name: 'Sudah',
        data: [window.statusDistributionData.financial.sudah]
      },
      {
        name: 'Belum',
        data: [window.statusDistributionData.financial.belum]
      },
      {
        name: 'Draft',
        data: [window.statusDistributionData.financial.draft]
      }
    ],
    chart: {
      type: 'bar',
      stacked: true,
      height: 150,
      toolbar: { show: false }
    },
    plotOptions: {
      bar: {
        horizontal: true,
        borderRadius: 4,
        barHeight: '80%'
      }
    },
    colors: ['#28c76f', '#ea5455', '#ff9f43'],
    xaxis: {
      categories: ['Financial Distribution'],
      title: { text: 'Value' },
      labels: {
        formatter: function (val) {
          return formatFinancial(val);
        }
      }
    },
    yaxis: {
      title: { text: '' }
    },
    legend: {
      show: false
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return formatFinancial(val);
        }
      }
    }
  };

  var financialChart = new ApexCharts(document.querySelector('#financialDistributionChart'), financialOptions);
  financialChart.render();
}

// Item Count Distribution Chart
if (document.querySelector('#itemCountDistributionChart')) {
  var itemCountOptions = {
    series: [
      {
        name: 'Sudah',
        data: [window.statusDistributionData.item.sudah]
      },
      {
        name: 'Belum',
        data: [window.statusDistributionData.item.belum]
      },
      {
        name: 'Draft',
        data: [window.statusDistributionData.item.draft]
      }
    ],
    chart: {
      type: 'bar',
      stacked: true,
      height: 150,
      toolbar: { show: false }
    },
    plotOptions: {
      bar: {
        horizontal: true,
        borderRadius: 4,
        barHeight: '80%'
      }
    },
    colors: ['#28c76f', '#ea5455', '#ff9f43'],
    xaxis: {
      categories: ['Item Count Distribution'],
      title: { text: 'Count' },
      labels: {
        formatter: function (val) {
          return val.toLocaleString('id-ID');
        }
      }
    },
    yaxis: {
      title: { text: '' }
    },
    legend: {
      position: 'bottom'
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return val.toLocaleString('id-ID');
        }
      }
    }
  };

  var itemCountChart = new ApexCharts(document.querySelector('#itemCountDistributionChart'), itemCountOptions);
  itemCountChart.render();
}
