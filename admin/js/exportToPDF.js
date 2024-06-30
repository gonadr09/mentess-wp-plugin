/* async function generatePDF() {
    const { jsPDF } = window.jspdf;
    const element = document.getElementById("pdf");

    // Ajustar el tamaño del elemento temporalmente para la impresión
    element.classList.add("pdf-width");

    // Capturar el elemento como imagen con html2canvas
    const canvas = await html2canvas(element, { scale: 2 });
    const imgData = canvas.toDataURL("image/png");

    // Crear una instancia de jsPDF
    const pdf = new jsPDF("p", "mm", "a4");

    // Calcular las dimensiones de la imagen y del PDF
    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pdf.internal.pageSize.getWidth();
    let pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    // Añadir la imagen al PDF
    let yPos = 0;
    while (yPos < imgProps.height) {
        pdf.addImage(imgData, "PNG", 0, yPos, pdfWidth, pdfHeight);
        yPos += pdfHeight;
        if (yPos < imgProps.height) {
            pdf.addPage();
        }
    }

    // Guardar el PDF
    pdf.save("1er-jsPDF.pdf");

    // Restaurar el tamaño del elemento como estaba antes
    element.classList.remove("pdf-width");
} */

    // Generar pdf sacando un print de pantalla completo como imagen 
    async function generatePDF() {
        const { jsPDF } = window.jspdf;
        const element = document.getElementById('pdf');

        if (!element) {
            console.error("El elemento con ID 'content' no se encontró.");
            return;
        }

        try {
            const canvas = await html2canvas(element, { scale: 2 });
            const imgData = canvas.toDataURL('image/png');

            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'pt',
                format: 'a4'
            });

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();

            const imgProps = pdf.getImageProperties(imgData);
            const imgWidth = imgProps.width;
            const imgHeight = imgProps.height;

            const pageHeight = pdfHeight;
            const scale = pdfWidth / imgWidth;
            const scaledHeight = imgHeight * scale;

            let position = 0;
            let heightLeft = scaledHeight;

            while (heightLeft > 0) {
                pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, scaledHeight);
                heightLeft -= pageHeight;
                position = heightLeft > 0 ? position - pageHeight : position;
                if (heightLeft > 0) {
                    pdf.addPage();
                }
            }

            pdf.save('documento.pdf');
        } catch (error) {
            console.error("Error al generar el PDF: ", error);
        }
    }







    async function generatePDFv2() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'pt',
            format: 'a4'
        });
        
        const element = document.querySelector('#pdf')

        // Estilos
        const h1 = {
            align: 'center',
            size: 40,
            fontStyle: 'bold',
            color: '#3879F1'
        };

        const h2 = {
            align: 'center',
            size: 26,
            fontStyle: 'bold',
            color: '#212529'
        };

        const h3 = {
            align: 'left',
            size: 22,
            fontStyle: 'bold',
            color: '#3879F1'
        };

        const h4 = {
            align: 'left',
            size: 20,
            fontStyle: 'bold',
            color: '#212529'
        };

        const h5 = {
            align: 'center',
            size: 20,
            fontStyle: 'bold',
            color: '#3879F1'
        };

        const h6 = {
            align: 'center',
            size: 14,
            fontStyle: 'bold',
            color: '#212529'
        };

        const p = {
            align: 'left',
            size: 13,
            fontStyle: 'normal',
            color: '#212529'
        };

        const pageWidth = pdf.internal.pageSize.getWidth()
        const middleWidth = pageWidth / 2

        const lineXStart = pageWidth / 4
        const lineXEnd = pageWidth * 3 / 4
        
        const marginTop = 80
        const marginStart = 40

        let currentY = marginTop
       

        // Función para añadir texto con estilo
        function addStyledText(pdf, text, x, y, style) {
            pdf.setFont('times', style.fontStyle);
            pdf.setFontSize(style.size);
            pdf.setTextColor(style.color);
        
            const textWidth = pageWidth - 80; // Ancho disponible para el texto
            const textLines = pdf.splitTextToSize(text, textWidth);
            let lineHeight = style.size * 1.2; // Altura de línea estimada
            let cursorY = y; // Cursor de posición Y actual
        
            textLines.forEach(line => {
                const remainingPageSpace = pdf.internal.pageSize.height - 50 - cursorY;
                
                if (lineHeight > remainingPageSpace) {
                    pdf.addPage(); // Agregar nueva página si no hay suficiente espacio
                    cursorY = marginTop; // Reiniciar cursor Y para la nueva página
                }
        
                pdf.text(line, x, cursorY, { align: style.align });
                cursorY += lineHeight; // Mover cursor a la siguiente línea
            });
        
            currentY = cursorY; // Actualizar posición Y actual
        }


        // Función para añadir una imagen con comprobación de espacio
        function addImage(pdf, img, x, y, width, height) {
            if (currentY + height > pdf.internal.pageSize.height) {
                pdf.addPage(); // Agregar una nueva página si no hay suficiente espacio
                currentY = marginTop; // Reiniciar Y para la nueva página
            }

            try {
                pdf.addImage(img, 'PNG', x, currentY, width, height); // Ajusta el tamaño de la imagen según sea necesario
                currentY += height + 20; // Incrementar la posición vertical actual
            } catch (error) {
                console.error('Error al agregar la imagen:', error);
            }
        }

        // Función para añadir un canvas con comprobación de espacio
        function addCanvas(pdf, canvas, x, y, width, height) {
            if (currentY + height > pdf.internal.pageSize.height) {
                pdf.addPage(); // Agregar una nueva página si no hay suficiente espacio
                currentY = marginTop; // Reiniciar Y para la nueva página
            }

            pdf.addImage(canvas, 'PNG', x, currentY, width, height); // Ajusta el tamaño del canvas según sea necesario
            currentY += height + 20; // Incrementar la posición vertical actual
        }


        // Sección del título del cuestionario
        const titleSection = element.querySelector('[data-pdf="quiz-title-section"]');
        const quizTitle = titleSection.querySelector('h1').innerText;
        const quizSubtitle = titleSection.querySelector('h5').innerText;
        addStyledText(pdf, quizTitle, middleWidth, currentY, h1);
        currentY -= 15,
        addStyledText(pdf, quizSubtitle, middleWidth, currentY, h5);

        // Separador
        pdf.setDrawColor(33, 37, 41); // Cambia el color a un azul específico (valores RGB)
        pdf.line(lineXStart, currentY, lineXEnd, currentY);
        currentY += 35

        // Sección de respuestas generales
        const generalSections = element.querySelectorAll('[data-pdf="general-answers-section"]');
        generalSections.forEach((section) => {
            const sectionTitle = section.querySelector('h2').innerText;
            const sectionContent = section.querySelector('h6').innerText;
            addStyledText(pdf, sectionTitle, middleWidth, currentY, h2);
            currentY -= 5
            addStyledText(pdf, sectionContent, middleWidth, currentY, h6);
            
            const table = section.querySelector('tbody')
            const rows = table.querySelectorAll('tr')
            const dataTable = [];
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                const rowData = [];
                cols.forEach(col => rowData.push(col.innerText));
                dataTable.push(rowData);
            });
            
            pdf.autoTable({
                startY: currentY,
                head: [], // Si no necesitas encabezado, puedes poner un array vacío
                body: dataTable.map(row => row.map((cell, index) => {
                    return index === 0 ? { content: cell, styles: { fillColor: [56, 121, 241], textColor: [255, 255, 255] } } : cell;
                })),
                styles: {
                    fontSize: 12,
                    lineColor: [222, 226, 230], // Color del borde (valores RGB)
                    lineWidth: 0.5, // Ancho del borde
                },
                didParseCell: function (data) {
                    if (data.column.index === 0) {
                        data.cell.styles.fillColor = [56, 121, 241]; // Fondo azul
                        data.cell.styles.textColor = [255, 255, 255]; // Texto blanco
                    }
                }
            });
            currentY += 170;
        });

        // Separador
        currentY += 20
        pdf.setDrawColor(33, 37, 41); // Cambia el color a un azul específico (valores RGB)
        pdf.line(lineXStart, currentY, lineXEnd, currentY);
        currentY += 35

        // Scored answers section
        const scoredSections = element.querySelectorAll('[data-pdf="scored-answers-section"]')
        scoredSections.forEach((section) => {
            const sectionTitle = section.querySelector('h2').innerText;
            const sectionContent = section.querySelector('h6').innerText;
            const canvasChart = section.querySelector('canvas')

            addStyledText(pdf, sectionTitle, middleWidth, currentY, h2);
            addStyledText(pdf, sectionContent, middleWidth, currentY, h6);

            // Textos de categorias ganadoras
            const winnersCategories = section.querySelectorAll('[data-pdf="category-winner-text-section"]');
            winnersCategories.forEach((winnerCategory) => {
                const img = winnerCategory.querySelector('img');
                console.log(img)
                const title = winnerCategory.querySelector('h3').innerText;
                const subtitle = winnerCategory.querySelector('h4').innerText;
                const paragraph = winnerCategory.querySelector('p').innerText;
                console.log(canvasChart)

                // Renderiza la imagen
                if (img) {
                    try {
                        addImage(pdf, img, marginStart, currentY, img.width, img.height); // Ajusta el tamaño de la imagen según sea necesario
                    } catch (error) {
                        console.error('Error al agregar la imagen:', error);
                    }
                }
                // Agrega textos
                addStyledText(pdf, title, marginStart, currentY, h3);
                addStyledText(pdf, subtitle, marginStart, currentY, h4);
                addStyledText(pdf, paragraph, marginStart, currentY, p);
                currentY += 20
            })

            // Renderiza el canvas
            if (canvasChart) {
                const canvasData = canvasChart.toDataURL('image/png');

                // Calcula la altura proporcional manteniendo la relación de aspecto
                const originalWidth = canvasChart.width;
                const originalHeight = canvasChart.height;
                const scaledWidth = pageWidth - 80;
                const scaledHeight = (scaledWidth * originalHeight) / originalWidth;

                addCanvas(pdf, canvasData, marginStart, currentY, pageWidth - 80, scaledHeight);
            }
            // pdf 47

            currentY += 20
            pdf.setDrawColor(33, 37, 41); // Cambia el color a un azul específico (valores RGB)
            pdf.line(lineXStart, currentY, lineXEnd, currentY);
            currentY += 35
            
        })
               
        pdf.save('newPDF.pdf');
    }

document.addEventListener('DOMContentLoaded', function() {
    // Agregar un evento al botón para generar el PDF
    const pdfButton = document.getElementById('generate-pdf');
    if (pdfButton) {
        pdfButton.addEventListener('click', generatePDFv2);
    }
});
