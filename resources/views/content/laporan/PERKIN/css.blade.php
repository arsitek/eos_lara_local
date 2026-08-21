<style type="text/css">
    * {
        box-sizing: border-box;
    }
    html, body {
        width: 100%;
        margin: 0;
        padding: 0;
        font-family: "Times New Roman", Times, serif;
        background-color: #ffffff;
    }
    .container-fluid {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: 20px;
        box-sizing: border-box;
    }
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        word-break: break-word;
        font-family: "Times New Roman", Times, serif;
        font-size: 14px;
        margin: 0;
        max-width: 100%;
    }
    table {
        width: 100% !important;
        max-width: 100% !important;
        border-collapse: collapse;
        font-size: 13px;
        margin-top: 15px;
        margin-bottom: 20px;
        table-layout: auto;
    }
    table th {
        background-color: rgba(208, 206, 206, 1);
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
        font-weight: bold;
        color: #000000;
        padding: 8px 5px;
        border: 1px solid #000000;
        word-wrap: break-word;
        word-break: break-word;
    }
    table thead {
        background-color: rgba(208, 206, 206, 1);
    }
    table thead tr th {
        vertical-align: middle;
        color: #000000;
    }
    table tbody tr td {
        font-size: 12px;
        padding: 6px 5px;
        vertical-align: top;
        color: #000000;
        border: 1px solid #000000;
        word-wrap: break-word;
        word-break: break-word;
    }
    .column {
        font-weight: bold;
        float: left;
        padding: 10px 0;
        min-height: 150px;
        box-sizing: border-box;
    }
    .left {
        width: 60%;
    }
    .right {
        width: 40%;
    }
    /* Clear floats after the columns */
    .row {
        width: 100%;
        margin-top: 30px;
        page-break-inside: avoid;
    }
    .row:after {
        content: "";
        display: table;
        clear: both;
    }
    @page {
        margin: 10mm;
    }
    @media print {
        html, body {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .container-fluid {
            margin: 0;
            padding: 0;
            width: 100% !important;
        }
        table {
            width: 100% !important;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
</style>
