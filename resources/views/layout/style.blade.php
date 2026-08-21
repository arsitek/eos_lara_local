<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');
    :root {
        --color-primary: #3a7bd5;   /* calm blue */
        --bg-primary: #e8f0fe;  /* very light blue background */
    }
    .soft-primary {
        display: block;
        margin-bottom: 5px;
        border-radius: 0.375rem;
        background: var(--bg-primary);
        color: var(--color-primary);
    }
    .disabled {
        pointer-events: none;
        opacity: 0.5;
        cursor: default;
    }
    /* 1. Error Red Light */
    .error-red {
        padding: 0.5rem 0.75rem;
        display: inline-block;
        color: #dc3545;
        background: #fdecea;
    }
    .tabel-info-unit tr td{
        font-size: 15px;
        padding: 6px;
    }
    .tabel-info-unit {
        background-color: #fafafa;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.8rem;
    }

    .tabel-info-unit tr {
        transition: background-color 0.3s ease;
    }

    .tabel-info-unit tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .tabel-info-unit td {
        padding: 0.8rem 1.2rem;
        line-height: 1.6;
    }

    .tabel-info-unit td:first-child {
        font-weight: 600;
        color: #2c3e50;
        width: 10%;
    }

    .tabel-info-unit td:last-child {
        color: #505c6e;
    }

    /* Optional: Add responsive styles */
    @media (max-width: 768px) {
        .info-unit {
            margin: 1rem !important;
            padding: 1rem;
        }

        .tabel-info-unit td {
            padding: 0.6rem 1rem;
        }
    }
    .swal2-container {
        z-index: 20000 !important; /* Higher than Bootstrap modal and backdrop */
    }
    .chosen-container {
      background-color: white!important;
      border-radius: 5px;
      background-image: none;
    }
    .chosen-container-multi .chosen-choices {
        background:unset;
    }
    .chosen-search-input{
        color: black;
    }
    .loader {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        border: 3px solid;
        border-color: #FFF #FFF transparent;
        box-sizing: border-box;
        animation: rotation 1s linear infinite;
    }
    .loader::after {
        content: '';
        box-sizing: border-box;
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        margin: auto;
        border: 3px solid;
        border-color: transparent #FF3D00 #FF3D00;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        animation: rotationBack 0.5s linear infinite;
        transform-origin: center center;
    }

    @keyframes rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes rotationBack {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(-360deg);
        }
    }


    /* The switch - the box around the slider */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
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
    .btn-ptnbh {
        background-image: linear-gradient(to right, #52c234 0%, #061700  51%, #52c234  100%);
        margin: 10px;
        padding: 15px 45px;
        text-align: center;
        text-transform: uppercase;
        transition: 0.5s;
        background-size: 200% auto;
        color: white;
        border:none;
        border-radius: 10px;
        display: block;
    }
    .btn-ptnbh:hover {
        background-position: right center; /* change the direction of the change here */
        color: #fff;
        text-decoration: none;
    }
    .btn-boptnbh {
        background-image: linear-gradient(to right, #004FF9 0%, #FFF94C  51%, #004FF9  100%);
        margin: 10px;
        padding: 15px 45px;
        text-align: center;
        text-transform: uppercase;
        transition: 0.5s;
        background-size: 200% auto;
        color: white;
        border:none;
        border-radius: 10px;
        display: block;
    }
    .btn-boptnbh:hover {
        background-position: right center; /* change the direction of the change here */
        color: #fff;
        text-decoration: none;
    }

    .loader {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        border: 3px solid;
        border-color: #FFF #FFF transparent;
        box-sizing: border-box;
        animation: rotation 1s linear infinite;
    }
    .loader::after {
        content: '';
        box-sizing: border-box;
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        margin: auto;
        border: 3px solid;
        border-color: transparent #FF3D00 #FF3D00;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        animation: rotationBack 0.5s linear infinite;
        transform-origin: center center;
    }

    @keyframes rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes rotationBack {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(-360deg);
        }
    }

    .kotak {
        --angle: 0deg;
        border: 5px solid;
        border-image: conic-gradient(from var(--angle), red, yellow, lime, aqua, blue, magenta, red) 1;
        animation: 3s rotate linear infinite;
    }

    @keyframes rotate {
        to {
            --angle: 360deg;
        }
    }

    @property --angle {
        syntax: '<angle>';
        initial-value: 0deg;
        inherits: false;
    }

    .gradient-border {
        --border-width: 3px;
        position: relative;
        z-index: 1;
        color: white;
        border: 1px solid;
        background: #222;
        border-radius: var(--border-width);
    }
    .gradient-border::after {
        position: absolute;
        z-index: 1;
        content: "";
        top: calc(-1 * var(--border-width));
        left: calc(-1 * var(--border-width));
        z-index: -1;
        width: calc(100% + var(--border-width) * 2);
        height: calc(100% + var(--border-width) * 2);
        background: linear-gradient(60deg, #5f86f2, #a65ff2, #f25fd0, #f25f61, #f2cb5f, #abf25f, #5ff281, #5ff2f0);
        background-size: 300% 300%;
        background-position: 0 50%;
        border-radius: calc(2 * var(--border-width));
        animation: moveGradient 4s alternate infinite;
    }

    @keyframes moveGradient {
        50% {
            background-position: 100% 50%;
        }
    }
    .file-upload-2 {
        cursor: pointer;
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    .file-upload-2 .btn {
        border: 2px solid gray;
        color: gray;
        background-color: white;
        padding: 5px 8px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: bold;
    }
    .file-upload-2 input[type=file] {
        font-size: 40px;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
    }
    .custom-dropdown {
        display: inline-block;
    }
    .custom-dropdown .anchor {
        position: relative;
        cursor: pointer;
        display: inline-block;
        padding: 5px 50px 5px 10px;
        border: 1px solid #ccc;
    }
    .custom-dropdown .anchor:after {
        position: absolute;
        content: "";
        border-left: 2px solid black;
        border-top: 2px solid black;
        padding: 5px;
        right: 10px;
        top: 20%;
        -moz-transform: rotate(-135deg);
        -ms-transform: rotate(-135deg);
        -o-transform: rotate(-135deg);
        -webkit-transform: rotate(-135deg);
        transform: rotate(-135deg);
    }
    .custom-dropdown .anchor:active:after {
        right: 8px;
        top: 21%;
    }
    .custom-dropdown ul.items {
        padding: 2px;
        display: none;
        margin: 0;
        border: 1px solid #ccc;
        border-top: none;
    }
    .custom-dropdown ul.items li {
        list-style: none;
    }
    .custom-dropdown.visible .anchor {
        color: #0094ff;
    }
    .custom-dropdown.visible .items {
        display: block;
    }
    .hide{
        opacity: 0;
    }
    .chosen-container {
      background-color: white!important;
      border-radius: 5px;
      background-image: none;
    }
    .chosen-container-multi .chosen-choices {
        background:unset;
    }
    .chosen-search-input{
        color: black;
    }
    .btn-satu {
        background-image: linear-gradient(to right, #fc00ff 0%, #00dbde  51%, #fc00ff  100%);
        margin: 10px;
        padding: 15px 45px;
        text-align: center;
        text-transform: uppercase;
        transition: 0.5s;
        background-size: 200% auto;
        color: white;
        box-shadow: 0 0 20px #eee;
        border-radius: 10px;
        display: block;
    }

    .btn-satu:hover {
        background-position: right center; /* change the direction of the change here */
        color: #fff;
        text-decoration: none;
    }
    .btn-dua {
        background-image: linear-gradient(to right, #00c3ff 0%, #ffff1c  51%, #00c3ff  100%);
        margin: 10px;
        padding: 15px 45px;
        text-align: center;
        text-transform: uppercase;
        transition: 0.5s;
        background-size: 200% auto;
        color: white;
        box-shadow: 0 0 20px #eee;
        border-radius: 10px;
        display: block;
    }

    .btn-dua:hover {
        background-position: right center; /* change the direction of the change here */
        color: #fff;
        text-decoration: none;
    }

    .btn-tiga {
        background-image: linear-gradient(to right, #a80077 0%, #66ff00  51%, #a80077  100%);
        margin: 10px;
        padding: 15px 45px;
        text-align: center;
        text-transform: uppercase;
        transition: 0.5s;
        background-size: 200% auto;
        color: white;
        box-shadow: 0 0 20px #eee;
        border-radius: 10px;
        display: block;
    }

    .btn-tiga:hover {
        background-position: right center; /* change the direction of the change here */
        color: #fff;
        text-decoration: none;
    }
    .btn-empat {
        background-image: linear-gradient(to right, #ef32d9 0%, #89fffd  51%, #ef32d9  100%);
        margin: 10px;
        padding: 15px 45px;
        text-align: center;
        text-transform: uppercase;
        transition: 0.5s;
        background-size: 200% auto;
        color: white;
        box-shadow: 0 0 20px #eee;
        border-radius: 10px;
        display: block;
    }

    .btn-empat:hover {
        background-position: right center; /* change the direction of the change here */
        color: #fff;
        text-decoration: none;
    }

    .ios-font {
        font-family: 'Roboto';
        font-weight: 400;
        font-optical-sizing: auto;
        font-style: normal;
        font-variation-settings:
            "wdth" 100;
    }
    .ios-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px;
        background-color: #007AFF;
        color: white;
        border-radius: 8px;
        border: none;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        text-decoration: none;
    }

    .ios-button:hover {
        background-color: #0066d6
    }

    .ios-button:active {
        background-color: #004fb3
    }

    .ios-button.secondary {
        background-color: #e5e5ea;
        color: #007AFF
    }

    .ios-button.secondary:hover {
        background-color: #d1d1d6
    }

    .ios-button.secondary:active {
        background-color: #c7c7cc
    }

    .ios-button .icon {
        width: 20px;
        height: 20px
    }
    .import-notice {
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
        border-radius: 4px;
        padding: 1.5rem;
        margin: 1rem 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        font-family: system-ui, -apple-system, sans-serif;
        line-height: 1.6;
        max-width: 800px;
    }

    .import-notice p {
        color: #333;
        margin: 0;
        font-size: 1rem;
    }

    .import-notice b {
        color: #0d6efd;
        background: #e7f0ff;
        padding: 0.2rem 0.5rem;
        border-radius: 3px;
        font-weight: 600;
    }

    .import-notice:hover {
        background-color: #fff;
        transition: background-color 0.2s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .tableStickyContainer::-webkit-scrollbar {
        width: 10px;
        background: #888; /* scrollbar handle */
    }

    .tableStickyContainer::-webkit-scrollbar-track {
        background: #f1f1f1; /* track color */
        background: #888; /* scrollbar handle */
        border-radius: 5px;
    }

    .tableStickyContainer::-webkit-scrollbar-thumb {
        background: #888; /* scrollbar handle */
        border-radius: 5px;
    }
    .tableStickyContainer {
        max-height: 100vh; /* or dynamically via JS */
        overflow-y: auto;
        border: 2.5px solid black;
    }

    /* Primary header row (SUMBER DANA, TARGET, REALISASI, etc.) */
    .tabel-sticky thead tr:first-child th {
        position: sticky;
        top: 0;              /* stick at the very top */
        z-index: 3;          /* above everything else */
        background-color: #343a40;
        color: white;
    }
    .tabel-sticky thead tr:nth-child(2) th {
        position: sticky;
        top: 2.5rem;         /* height of the first row—adjust as needed */
        z-index: 2;          /* just under the first row */
        background-color: #343a40;
        color: white
    }
</style>

