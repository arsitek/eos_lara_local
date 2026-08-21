<style>
    .tanggapan {
        max-width: 250px;
    }
    .btn-revision-filter:hover:not(.active) {
        background: rgba(0, 0, 0, 0.05) !important;
    }
    
    .btn-revision-filter.active {
        background: white !important;
        color: #007AFF !important;
        font-weight: 500 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    }
    
    .btn-revision-filter:not(.active) {
        background: transparent !important;
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 24px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .document-link-container {
        margin: 15px 0;
        max-width: 300px;
    }

    .document-link {
        height: 50px;
        display: flex;
        align-items: center;
        padding: 12px 20px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        text-decoration: none;
        color: #495057;
        transition: all 0.3s ease;
    }

    .document-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        background-color: #4a90e2;
        border-radius: 6px;
        margin-right: 15px;
    }

    .icon-wrapper i {
        color: white;
        font-size: 1.5em;
    }

    .filename {
        font-size: 0.95em;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100px;
    }

    /* Optional hover animation for icon */
    .document-link:hover .icon-wrapper {
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .document-link {
            padding: 10px 15px;
        }

        .icon-wrapper {
            width: 35px;
            height: 35px;
        }

        .filename {
            max-width: 100px;
            font-size: 0.9em;
        }
    }

    @media only screen and (max-width: 768px) {
        .table-header-desktop {
            display: none;
        }
        .table-header-phone {
            display: block !important;
         }
        .btn-filter-unitkerja {
            margin-top: 5px;
            width: 300px !important;
        }
        .card-header .select2-container {
            margin-top: 5px;
        }
    }
</style>
