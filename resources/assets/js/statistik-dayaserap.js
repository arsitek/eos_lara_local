/**
 * Statistik Daya Serap DataTable (js)
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  console.log('Data Daya Serap:', window.dataDayaSerap);

  const dt_dayaserap_table = document.querySelector('.dt-dayaserap');
  if (dt_dayaserap_table) {
    let dt_dayaserap = new DataTable(dt_dayaserap_table, {
      data: window.dataDayaSerap || [],
      columns: [
        {
          data: 'unit_kerja',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        {
          data: 'sumberdana',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        {
          data: 'pagu_alokasi',
          render: function (data, type, row) {
            return type === 'display' ? new Intl.NumberFormat('id-ID').format(data) : data;
          }
        },
        {
          data: 'realisasi',
          render: function (data, type, row) {
            return type === 'display' ? new Intl.NumberFormat('id-ID').format(data) : data;
          }
        },
        {
          data: 'daya_serap',
          render: function (data, type, row) {
            return type === 'display' ? new Intl.NumberFormat('id-ID').format(data) : data;
          }
        },
        {
          data: 'persentase',
          render: function (data, type, row) {
            return type === 'display' ? data + '%' : data;
          }
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
