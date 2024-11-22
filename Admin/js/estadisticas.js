document.addEventListener("DOMContentLoaded", () => {
    console.log(datosEstadisticas); // Verifica los datos en la consola del navegador

    // Extraer datos de los objetos
    const labelsMensuales = datosEstadisticas.datosMensuales.map(item => `Mes ${item.mes}`);
    const ingresosMensuales = datosEstadisticas.datosMensuales.map(item => item.total_ingresos);
    const egresosMensuales = datosEstadisticas.datosMensuales.map(item => item.total_egresos);
    const utilidadMensual = datosEstadisticas.datosMensuales.map(item => item.total_utilidad);

    const labelsAnuales = datosEstadisticas.datosAnuales.map(item => item.anio);
    const ingresosAnuales = datosEstadisticas.datosAnuales.map(item => item.total_ingresos);
    const egresosAnuales = datosEstadisticas.datosAnuales.map(item => item.total_egresos);
    const utilidadAnual = datosEstadisticas.datosAnuales.map(item => item.total_utilidad);

    // Gráfico Mensual
    const ctxMensual = document.getElementById('graficoMensual').getContext('2d');
    new Chart(ctxMensual, {
        type: 'bar',
        data: {
            labels: labelsMensuales,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresosMensuales,
                    backgroundColor: 'rgba(56, 142, 60, 0.7)',
                },
                {
                    label: 'Egresos',
                    data: egresosMensuales,
                    backgroundColor: 'rgba(198, 40, 40, 0.7)',
                },
                {
                    label: 'Utilidad',
                    data: utilidadMensual,
                    backgroundColor: 'rgba(21, 101, 192, 0.7)',
                }
            ]
        },
        options: {
            scales: {
                x: { title: { display: true, text: 'Mes' } },
                y: { title: { display: true, text: 'Monto ($)' } }
            }
        }
    });

    // Gráfico Anual
    const ctxAnual = document.getElementById('graficoAnual').getContext('2d');
    new Chart(ctxAnual, {
        type: 'bar',
        data: {
            labels: labelsAnuales,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresosAnuales,
                    backgroundColor: 'rgba(56, 142, 60, 0.7)',
                },
                {
                    label: 'Egresos',
                    data: egresosAnuales,
                    backgroundColor: 'rgba(198, 40, 40, 0.7)',
                },
                {
                    label: 'Utilidad',
                    data: utilidadAnual,
                    backgroundColor: 'rgba(21, 101, 192, 0.7)',
                }
            ]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: { title: { display: true, text: 'Monto ($)' } },
                y: { title: { display: true, text: 'Año' } }
            }
        }
    });
});
