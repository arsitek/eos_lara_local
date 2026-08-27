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

// Status Distribution - Progress Bars and Donut Charts
if (document.querySelector('#sudahTotal')) {
  // Calculate percentages
  const sudahFinPersentase =
    window.statusDistributionData.totalSemua > 0
      ? (window.statusDistributionData.sudah.total / window.statusDistributionData.totalSemua) * 100
      : 0;
  const belumFinPersentase =
    window.statusDistributionData.totalSemua > 0
      ? (window.statusDistributionData.belum.total / window.statusDistributionData.totalSemua) * 100
      : 0;
  const draftFinPersentase =
    window.statusDistributionData.totalSemua > 0
      ? (window.statusDistributionData.draft.total / window.statusDistributionData.totalSemua) * 100
      : 0;

  const sudahCountPersentase =
    window.statusDistributionData.totalItemCount > 0
      ? (window.statusDistributionData.sudah.count / window.statusDistributionData.totalItemCount) * 100
      : 0;
  const belumCountPersentase =
    window.statusDistributionData.totalItemCount > 0
      ? (window.statusDistributionData.belum.count / window.statusDistributionData.totalItemCount) * 100
      : 0;
  const draftCountPersentase =
    window.statusDistributionData.totalItemCount > 0
      ? (window.statusDistributionData.draft.count / window.statusDistributionData.totalItemCount) * 100
      : 0;

  // Update progress bars
  document.getElementById('sudahProgress').style.width = sudahFinPersentase + '%';
  document.getElementById('belumProgress').style.width = belumFinPersentase + '%';
  document.getElementById('draftProgress').style.width = draftFinPersentase + '%';

  // Update card values
  document.getElementById('sudahTotal').textContent = formatFinancial(window.statusDistributionData.sudah.total);
  document.getElementById('sudahCount').textContent =
    window.statusDistributionData.sudah.count.toLocaleString('id-ID') + ' item';

  document.getElementById('belumTotal').textContent = formatFinancial(window.statusDistributionData.belum.total);
  document.getElementById('belumCount').textContent =
    window.statusDistributionData.belum.count.toLocaleString('id-ID') + ' item';

  document.getElementById('draftTotal').textContent = formatFinancial(window.statusDistributionData.draft.total);
  document.getElementById('draftCount').textContent =
    window.statusDistributionData.draft.count.toLocaleString('id-ID') + ' item';

  // Update legend percentages
  document.getElementById('sudahFinPersentase').textContent = sudahFinPersentase.toFixed(0) + '%';
  document.getElementById('belumFinPersentase').textContent = belumFinPersentase.toFixed(0) + '%';
  document.getElementById('draftFinPersentase').textContent = draftFinPersentase.toFixed(0) + '%';

  document.getElementById('sudahCountPersentase').textContent = sudahCountPersentase.toFixed(0) + '%';
  document.getElementById('belumCountPersentase').textContent = belumCountPersentase.toFixed(0) + '%';
  document.getElementById('draftCountPersentase').textContent = draftCountPersentase.toFixed(0) + '%';

  // Financial Distribution Donut Chart
  const financialDonutOptions = {
    series: [sudahFinPersentase, belumFinPersentase, draftFinPersentase],
    labels: ['Sudah Realisasi', 'Belum Realisasi', 'Draft'],
    chart: {
      type: 'donut',
      height: 220,
      fontFamily: 'Public Sans, sans-serif'
    },
    colors: ['#00bad1', '#ea5455', '#ff9f43'],
    plotOptions: {
      pie: {
        donut: {
          size: '65%'
        }
      }
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: false
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return val.toFixed(1) + '%';
        }
      }
    }
  };

  const financialDonutChart = new ApexCharts(document.querySelector('#financialDonutChart'), financialDonutOptions);
  financialDonutChart.render();

  // Item Count Distribution Donut Chart
  const itemCountDonutOptions = {
    series: [sudahCountPersentase, belumCountPersentase, draftCountPersentase],
    labels: ['Sudah Realisasi', 'Belum Realisasi', 'Draft'],
    chart: {
      type: 'donut',
      height: 220,
      fontFamily: 'Public Sans, sans-serif'
    },
    colors: ['#00bad1', '#ea5455', '#ff9f43'],
    plotOptions: {
      pie: {
        donut: {
          size: '65%'
        }
      }
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: false
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return val.toFixed(1) + '%';
        }
      }
    }
  };

  const itemCountDonutChart = new ApexCharts(document.querySelector('#itemCountDonutChart'), itemCountDonutOptions);
  itemCountDonutChart.render();
}

// Initialize all status radial charts
if (document.querySelector('#sudahRadialChart')) {
  // Calculate percentage for each status
  const sudahPersentase =
    window.statusDistributionData.totalSemua > 0
      ? (window.statusDistributionData.sudah.total / window.statusDistributionData.totalSemua) * 100
      : 0;
  const belumPersentase =
    window.statusDistributionData.totalSemua > 0
      ? (window.statusDistributionData.belum.total / window.statusDistributionData.totalSemua) * 100
      : 0;
  const draftPersentase =
    window.statusDistributionData.totalSemua > 0
      ? (window.statusDistributionData.draft.total / window.statusDistributionData.totalSemua) * 100
      : 0;

  // Initialize charts
  initStatusRadialChart('sudahRadialChart', sudahPersentase, '#28c76f');
  initStatusRadialChart('belumRadialChart', belumPersentase, '#ea5455');
  initStatusRadialChart('draftRadialChart', draftPersentase, '#ff9f43');

  // Update display values
  document.getElementById('sudahTotal').textContent = formatFinancial(window.statusDistributionData.sudah.total);
  document.getElementById('sudahPersentase').textContent = sudahPersentase.toFixed(1) + '%';

  document.getElementById('belumTotal').textContent = formatFinancial(window.statusDistributionData.belum.total);
  document.getElementById('belumPersentase').textContent = belumPersentase.toFixed(1) + '%';

  document.getElementById('draftTotal').textContent = formatFinancial(window.statusDistributionData.draft.total);
  document.getElementById('draftPersentase').textContent = draftPersentase.toFixed(1) + '%';
}

// Realization Rate Radial Bar Chart
if (document.querySelector('#realizationRadialChart')) {
  const realizationRadialChartEl = document.querySelector('#realizationRadialChart');
  const realizationRadialChartConfig = {
    chart: {
      height: 450,
      sparkline: {
        enabled: true
      },
      parentHeightOffset: 0,
      type: 'radialBar'
    },
    colors: ['#FF9F43'], // Warning color (orange)
    series: [window.realizationRate],
    plotOptions: {
      radialBar: {
        offsetY: 0,
        startAngle: -90,
        endAngle: 90,
        hollow: {
          size: '65%'
        },
        track: {
          strokeWidth: '55%',
          background: '#E9ECEF'
        },
        dataLabels: {
          name: {
            show: false
          },
          value: {
            fontSize: '54px',
            color: '#ff8717',
            fontWeight: 600,
            offsetY: -5,
            formatter: function (val) {
              return val.toFixed(1) + '%';
            }
          }
        }
      }
    },
    grid: {
      show: false,
      padding: {
        bottom: 5
      }
    },
    stroke: {
      lineCap: 'round'
    },
    labels: ['Realisasi']
  };

  const realizationRadialChart = new ApexCharts(realizationRadialChartEl, realizationRadialChartConfig);
  realizationRadialChart.render();
}
