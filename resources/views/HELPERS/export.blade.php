<script>
	const exportToPdf = ( fileName ) => {
        html2canvas(document.body, {
            scale: 1.1,
            scrollY: -window.scrollY,
            windowHeight: document.documentElement.offsetHeight
        }).then(canvas => {
            const contentWidth = canvas.width;
            const contentHeight = canvas.height;
            const pageHeight = contentWidth / 595.28 * 841.89; // A4 size ratio
            let leftHeight = contentHeight;
            let position = 0;
            const imgWidth = 595.28;
            const imgHeight = 595.28 / contentWidth * contentHeight;
            const pageData = canvas.toDataURL('image/jpeg')

            const pdf = new jspdf.jsPDF('', 'pt', 'a4')
            if (leftHeight < pageHeight) {
                pdf.addImage(pageData, 'JPEG', 0, position, imgWidth, imgHeight)
            } else {
                while (leftHeight > 0) {
                    pdf.addImage(pageData, 'JPEG', 0, position, imgWidth, imgHeight)
                    leftHeight -= pageHeight
                    position -= 841.89
                    if (leftHeight > 0) {
                        pdf.addPage()
                    }
                }
            }
            pdf.save(fileName)
        })
    }
    const exportExcel = ( tableName, fileName ) => {
        const table = document.getElementById(tableName)
        const ws    = XLSX.utils.table_to_sheet(table)
        const wb    = XLSX.utils.book_new()
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1")
        XLSX.writeFile(wb, `${fileName}.xlsx`)
    }
</script>
