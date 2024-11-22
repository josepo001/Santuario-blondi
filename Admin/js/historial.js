// Función para actualizar la tabla con los datos (filtros o sin filtros)
function actualizarTabla() {
    // Obtener valores de los filtros
    const tarjetaId = document.getElementById('tarjeta_id').value;
    const desde = document.getElementById('desde').value;
    const hasta = document.getElementById('hasta').value;
    const montoMin = document.getElementById('monto_min').value;
    const montoMax = document.getElementById('monto_max').value;

    // Construir la URL con los filtros
    const url = `actualizar_tabla.php?tarjeta_id=${encodeURIComponent(tarjetaId)}&desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}&monto_min=${encodeURIComponent(montoMin)}&monto_max=${encodeURIComponent(montoMax)}`;

    // Realizar la solicitud a actualizar_tabla.php
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error en la solicitud: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            // Obtener el tbody de la tabla
            const tbody = document.querySelector("table tbody");
            tbody.innerHTML = ""; // Limpiar la tabla

            if (data.length > 0) {
                // Iterar sobre los resultados y agregar filas a la tabla
                data.forEach(row => {
                    const tr = document.createElement("tr");

                    tr.innerHTML = `
                        <td>${row.fecha}</td>
                        <td>${row.descripcion}</td>
                        <td>$${parseFloat(row.egresos).toFixed(2)}</td>
                        <td>${row.tarjeta_id}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                // Mostrar mensaje si no hay resultados
                const tr = document.createElement("tr");
                tr.innerHTML = `<td colspan="4">No se encontraron transacciones.</td>`;
                tbody.appendChild(tr);
            }
        })
        .catch(error => console.error("Error al actualizar la tabla:", error));
}

// Llamar a la función al cargar la página
document.addEventListener("DOMContentLoaded", actualizarTabla);

// Event Listener para el botón de filtros
const botonFiltros = document.querySelector("button[onclick='actualizarTabla()']");
if (botonFiltros) {
    botonFiltros.addEventListener("click", actualizarTabla);
}
