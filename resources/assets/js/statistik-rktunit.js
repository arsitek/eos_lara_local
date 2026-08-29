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

// Distribusi per Jenis RAB - Donut Chart and Detail Cards
if (document.querySelector('#jenisRabDonutChart')) {
  // Calculate percentages and averages
  const jenisRabData = window.distribusiJenisRab.map(item => {
    const persentase = window.totalSemua > 0 ? (item.total_jumlah_biaya / window.totalSemua) * 100 : 0;
    const avgPerItem = item.count > 0 ? item.total_jumlah_biaya / item.count : 0;
    return {
      ...item,
      persentase: persentase,
      avgPerItem: avgPerItem
    };
  });

  // Sort by percentage descending
  jenisRabData.sort((a, b) => b.persentase - a.persentase);

  // Colors for each type
  const colors = ['#4a3aa7', '#7a6cd6', '#c5bcec', '#9e8ed9', '#8b7bcf'];

  // Update total display
  document.getElementById('jenisRabTotal').textContent = formatFinancial(window.totalSemua);

  // Generate detail cards
  const detailsContainer = document.getElementById('jenisRabDetails');
  detailsContainer.innerHTML = jenisRabData
    .map(
      (item, index) => `
    <div style="display: flex; align-items: center; justify-content: space-between; background: #fff; border-radius: 8px; padding: 0.85rem 1rem; border-left: 3px solid${colors[index % colors.length]};">
      <div>
        <p style="font-size: 13px; font-weight: 500; margin: 0; color: #5D596C;">${item.jenis}</p>
        <p style="font-size: 11px; color: #6c757d; margin: 2px 0 0;">${item.count.toLocaleString('id-ID')} item · rata-rata${formatFinancial(item.avgPerItem)}/item</p>
      </div>
      <div style="text-align: right;">
        <p style="font-size: 15px; font-weight: 500; margin: 0; color: #5D596C;">${formatFinancial(item.total_jumlah_biaya)}</p>
        <p style="font-size: 11px; color: #6c757d; margin: 2px 0 0;">${item.persentase.toFixed(1)}%</p>
      </div>
    </div>
  `
    )
    .join('');

  // Donut Chart
  const jenisRabDonutOptions = {
    series: jenisRabData.map(item => item.persentase),
    labels: jenisRabData.map(item => item.jenis),
    chart: {
      type: 'donut',
      height: 220,
      fontFamily: 'Public Sans, sans-serif'
    },
    colors: colors.slice(0, jenisRabData.length),
    plotOptions: {
      pie: {
        donut: {
          size: '68%'
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

  const jenisRabDonutChart = new ApexCharts(document.querySelector('#jenisRabDonutChart'), jenisRabDonutOptions);
  jenisRabDonutChart.render();
}

// Distribusi per Sumber Dana - Summary Widget
if (document.querySelector('#sumberDanaChart')) {
  // Calculate percentages and sort
  const sumberDanaData = window.distribusiSumberDana.map(item => {
    const persentase = window.totalSemua > 0 ? (item.total_jumlah_biaya / window.totalSemua) * 100 : 0;
    return {
      ...item,
      persentase: persentase
    };
  });

  // Sort by percentage descending
  sumberDanaData.sort((a, b) => b.persentase - a.persentase);

  // Separate top 6 and others
  const top6 = sumberDanaData.slice(0, 6);
  const others = sumberDanaData.slice(6);

  const othersTotal = others.reduce((sum, item) => sum + item.total_jumlah_biaya, 0);
  const othersPersentase = window.totalSemua > 0 ? (othersTotal / window.totalSemua) * 100 : 0;

  // Prepare chart data
  const chartLabels = top6.map(item => item.sumberdana);
  const chartValues = top6.map(item => item.total_jumlah_biaya);
  const chartPercentages = top6.map(item => item.persentase);

  // Add "others" if there are more than 6 sources
  if (others.length > 0) {
    chartLabels.push(`${others.length} sumber lainnya`);
    chartValues.push(othersTotal);
    chartPercentages.push(othersPersentase);
  }

  // Colors - blue gradient
  const colors = ['#042C53', '#0C447C', '#185FA5', '#378ADD', '#85B7EB', '#B5D4F4', '#B5D4F4'];

  // Update summary cards
  document.getElementById('sumberDanaTotal').textContent = formatFinancial(window.totalSemua);
  document.getElementById('sumberDanaCount').textContent = sumberDanaData.length + ' sumber';

  if (top6.length > 0) {
    const terbesar = top6[0];
    document.getElementById('sumberDanaTerbesar').textContent =
      `${terbesar.sumberdana} ${terbesar.persentase.toFixed(1)}%`;
  }

  // Update summary text
  const top6Total = top6.reduce((sum, item) => sum + item.total_jumlah_biaya, 0);
  const top6Persentase = window.totalSemua > 0 ? (top6Total / window.totalSemua) * 100 : 0;
  document.getElementById('sumberDanaSummaryText').innerHTML =
    `${top6.length} dari${sumberDanaData.length} sumber dana menyumbang <span style="color: #5D596C; font-weight: 500;">${top6Persentase.toFixed(1)}%</span> dari total dana.`;

  // Horizontal Bar Chart
  const sumberDanaChartOptions = {
    series: [
      {
        data: chartValues
      }
    ],
    chart: {
      type: 'bar',
      height: 340,
      toolbar: { show: false }
    },
    plotOptions: {
      bar: {
        horizontal: true,
        borderRadius: 4,
        barHeight: '60%',
        distributed: true
      }
    },
    colors: colors,
    xaxis: {
      categories: chartLabels,
      labels: {
        show: false
      },
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: {
      labels: {
        style: {
          fontSize: '12px',
          colors: '#6c757d'
        }
      }
    },
    grid: {
      show: false
    },
    legend: {
      show: false
    },
    tooltip: {
      enabled: false
    },
    dataLabels: {
      enabled: true,
      formatter: function (val, opts) {
        const index = opts.dataPointIndex;
        const valueInM = val / 1000000000;
        const percentage = chartPercentages[index];
        return `Rp${valueInM.toFixed(1).replace('.', ',')}M ·${percentage.toFixed(1)}%`;
      },
      style: {
        fontSize: '12px',
        fontWeight: 500,
        colors: ['#52514e']
      },
      offsetX: 8
    }
  };

  const sumberDanaChart = new ApexCharts(document.querySelector('#sumberDanaChart'), sumberDanaChartOptions);
  sumberDanaChart.render();
}
