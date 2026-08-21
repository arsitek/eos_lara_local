<style>
    .tableContainer::-webkit-scrollbar {
        width: 10px;
        background: #888; /* scrollbar handle */
    }

    .tableContainer::-webkit-scrollbar-track {
        background: #f1f1f1; /* track color */
        background: #888; /* scrollbar handle */
        border-radius: 5px;
    }

    .tableContainer::-webkit-scrollbar-thumb {
        background: #888; /* scrollbar handle */
        border-radius: 5px;
    }
    .tableContainer {
        max-height: 100vh; /* or dynamically via JS */
        overflow-y: auto;
        border: 2.5px solid black;
    }

    /* Make the thead sticky */
    #tabel-rekat-unit thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #343a40; /* dark background to match .bg-dark */
        color: white; /* ensure text stays visible */
    }
    @media only screen and ( max-width: 768px ) {
        .cari {
            margin-top: 15px;
            width: 100% !important;
        }
        .selectRiwayat {
            width: 100% !important;
        }
        .cari-larger-screen {
            display: none !important;
        }
        .cari-smaller-screen {
            display: block !important;
            position: relative;
            width: 100% !important;
            left: 0;
            right: 0;
            margin-inline: auto;
        }

        /* Make dropdowns responsive */
        .rkaUnit .ios-select-multiple {
            width: 100% !important; /* Full width on mobile */
            margin-bottom: 15px; /* Add spacing between dropdowns */
        }

        .filterBulan .ios-select-multiple {
            width: 100% !important;
        }

        /* Options container responsive */
        .selectRiwayat .options-container {
            width: 100% !important;
            display: none;
            position: absolute;
            top: 100%; /* Position below the trigger */
            max-height: 400px;
            overflow-y: auto;
            background: #f2f2f7;
            border-radius: 10px;
            left: 0;
            right: 0;
            margin-inline: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 8px;
            z-index: 1000;
            box-sizing: border-box; /* Prevent padding from increasing dimensions */
        }

        /* All options containers should be full width */
        .ios-select-multiple .options-container {
            width: 100% !important;
            left: 0;
            right: 0;
            margin-inline: auto;
        }

        .rkaUnit button.cari {
            width: 100%; /* Button spans full width */
            margin-top: 10px;
        }

        .rkaUnit {
            display: flex;
            gap: 5px;
            align-items: stretch;
            flex-direction: column; /* Stack items vertically */
            flex-wrap: nowrap;
            width: 100%;
        }

        /* Make header container responsive */
        .rkaUnitHeader {
            flex-direction: column !important;
            gap: 15px;
        }

        .filterBulan {
            width: 100%;
            align-self: stretch;
        }
    }
    .ppkNull {
        text-align: center;
        transition: 0.3s;
    }
    .listDataPpkBppNull:hover {
        opacity: 0.8;
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
        width: 100%; /* Full width on smaller devices */
        max-width: 100%; /* Ensure it doesn’t exceed a set size on larger screens */
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
    .filterBulan {
        align-self: center; /* Center button alignment */
        width: 100%;
        max-width: 100%;
    }

    .filterBulan .ios-select-multiple {
        width: 100%;
    }

    /* Responsive layout for larger screens */
    @media only screen and (min-width: 769px) {
        .rkaUnitHeader {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 15px;
        }

        .rkaUnit {
            flex-direction: row;
            align-items: center;
            flex: 1;
        }

        .filterBulan {
            flex-shrink: 0;
        }
    }

    /* Ultra-wide screen optimization */
    @media only screen and (min-width: 1200px) {
        .rkaUnit .ios-select-multiple {
            max-width: 100%px;
        }

        .rkaUnit {
            justify-content: flex-start;
        }
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: rgb(255, 255, 255); /* For debugging */
        color: black !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
        color: black !important;
    }
</style>
