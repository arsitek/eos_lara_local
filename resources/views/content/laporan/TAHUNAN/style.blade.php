<style>
    .nav-link:hover {
        color: blue !important
    }
    .rkaUnit {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        flex-wrap: wrap; /* Ensures wrapping for smaller screens */
    }

    .rkaUnit .ios-select-multiple {
        margin: 5px auto;
        position: relative;
        /* Fix width so short labels don't shrink the container, but keep it responsive */
        flex: 0 0 clamp(260px, 28vw, 320px);
        width: clamp(260px, 28vw, 320px);
        min-width: 300px;
        max-width: 320px;
    }
    .rkaUnit .level-1 .level-2{
        padding-right: 10px
    }
    .rkaUnit .group-header {
        padding-right: 10px;
    }
    .rkaUnit button.cari {
        align-self: center; /* Center button alignment */
        width: 100%; /* Full width on smaller devices */
    }
    .col-width-200 {
        width: 200px !important;
        max-width: 200px !important;
        min-width: 200px !important;
        word-wrap: break-word !important;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    table.dataTable .col-width-200 {
        width: 200px !important;
        max-width: 200px !important;
    }
    .total-row td {
        background: #f5f7fba6 !important;
        font-weight: 700;
        font-size: 15px;
        text-transform: uppercase;
        color: #1f2d3d;
        border-top: 2px solid #d6d9e0;
    }
    .sd-group-header td {
        background: #f5f7fb !important;
        font-weight: 700;
        text-transform: uppercase;
        color: #1f2d3d;
        border-top: 2px solid #d6d9e0;
    }
    .ss-group-header td {
        background: #eef3ff !important;
        font-weight: 800;
        color: #1f2d3d;
        text-transform: uppercase;
        border-top: 2px solid #cdd6f3;
    }
    .ss-subgroup-header td{
        background: #eff1f7 !important;
        font-weight: 700;
        color: #1f2d3d;
        text-transform: uppercase;
        border-top: 2px solid #cdd6f3;
    }
    .ss-sd-header td {
        background: #f9fbff !important;
        font-weight: 700;
        color: #2c3e50;
    }
    .ro-group-header td {
        background: #eef3ff !important;
        font-weight: 800;
        color: #1f2d3d;
        text-transform: uppercase;
        border-top: 2px solid #cdd6f3;
    }
    .ro-sd-header td {
        background: #f9fbff !important;
        font-weight: 700;
        color: #2c3e50;
    }
    .ikv-group-header td {
        background: #eef3ff !important;
        font-weight: 800;
        color: #1f2d3d;
        text-transform: uppercase;
        border-top: 2px solid #cdd6f3;
    }
    .ikv-sd-header td {
        background: #f9fbff !important;
        font-weight: 700;
        color: #2c3e50;
    }
    .keg-group-header td {
        background: #eef3ff !important;
        font-weight: 800;
        color: #1f2d3d;
        text-transform: uppercase;
        border-top: 2px solid #cdd6f3;
    }
    .keg-sd-header td {
        background: #f9fbff !important;
        font-weight: 700;
        color: #2c3e50;
    }
</style>
