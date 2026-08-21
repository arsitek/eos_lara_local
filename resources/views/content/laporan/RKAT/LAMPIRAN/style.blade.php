<style>
    .trHover:hover {
        background-color: #f2f2f2;
        opacity: 0.5;
    }
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
    #tabel-rkat thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #343a40; /* dark background to match .bg-dark */
        color: white; /* ensure text stays visible */
    }
</style>
