document.addEventListener('DOMContentLoaded', function () {
  // Verifica si es la página de la tabla deseada
  if (document.querySelector('.pedidos')) {
    // Ejecuta la función para eliminar los <br> en la columna
    clean_address();
    process_cell();
  }
});

function clean_address() {
  // Obtén todas las celdas de la columna
  let cells = document.querySelectorAll('td.column-direccion');

  // Itera a través de todas las celdas
  cells.forEach(function (cell) {
    // Reemplaza las dos primeras ocurrencias de <br> con una cadena vacía
    cell.innerHTML = cell.innerHTML.replace(/<br>/, '').replace(/<br>/, '');
  });
}

function getMsg(saltoLinea, nroPedido, nombreCliente, empresa, googleMaps) {
  return `Pedido #${nroPedido} ${saltoLinea} Cliente: ${nombreCliente} ${saltoLinea} Empresa: ${empresa} ${saltoLinea} Dirección: ${googleMaps}`
}

function process_cell() {
  // Obtén la celda deseada
  let cellDireccion = document.querySelectorAll('td.column-direccion');

  // Itera sobre todas las celdas de contenido
  cellDireccion.forEach(function (cellContenido) {
    // Obtén la celda donde se generará el enlace de Google Maps
    let cellEnlace = cellContenido.closest('tr').querySelector('td.column-link');

    // Copia el contenido de la celda
    let contenido = cellContenido.innerHTML;

    // Reemplaza <br> por ","
    contenido = contenido.replace(/<br>/g, ',');

    // Reemplaza espacios por "+" y sacar los ultimos 5 caracteres
    contenido = contenido.replace(/\s+/g, '+').slice(0, -5);;

    // Genera el enlace de Google Maps
    let enlace = 'https://www.google.com/maps/place/' + contenido + '/';

    // Crea un elemento de enlace (<a>) con el enlace generado como atributo href
    let link = document.createElement('a');
    link.href = enlace;
    link.target = '_blank';
    link.textContent = 'Link de Google Maps';
    // Agrega el enlace como contenido de la celda de enlace
    cellEnlace.innerHTML = link.outerHTML;

    // Mensaje HTML
    let saltoLinea = "<br>";
    let nroPedido = cellContenido.closest('tr').querySelector('td.column-nro-pedido').innerText;
    let nombreCliente = cellContenido.closest('tr').querySelector('td.column-cliente').innerText;
    let empresa = cellContenido.closest('tr').querySelector('td.column-empresa').innerText;

    let msg = getMsg(saltoLinea, nroPedido, nombreCliente, empresa, enlace);

    let cellMsg = cellContenido.closest('tr').querySelector('td.column-msg');
    cellMsg.innerHTML = `<div class="wsp-msg"><p id="copy-my-contents-${nroPedido}">${msg}</p><button class="button button-primary button-small copy-btn" onclick="copyClipboard(${nroPedido})">Copiar Mensaje</button></div>`;

    // Generar el enlace de WhatsApp
    let numeroTelefono = "+5492612091093";
    let mensajeWsp = document.getElementById("copy-my-contents-"+nroPedido).innerText;
    let enlaceWhatsApp = "https://wa.me/" + numeroTelefono + "?text=" + encodeURIComponent(`${mensajeWsp}`);
    cellMsg.innerHTML += `<p><a href="${enlaceWhatsApp}" target="_blank" class="button button-small whatsapp">Enviar a Whatsapp</a></p>`

  });

}


function copiarContenido(cellId) {
  // Identifica la fila específica que contiene la celda que deseas copiar
  let fila = document.querySelector(cellId);

  // Identifica la celda específica dentro de la fila
  let celda = fila.querySelector('.column-msg');

  // Obtén el contenido de la celda a copiar
  let contenido = celda.innerText;

  console.log(cellId, contenido);

  navigator.clipboard.writeText(contenido)
}

function copyClipboard(contentId) {

  var copyText = document.getElementById("copy-my-contents-"+contentId);
  var range = document.createRange();
  var selection = window.getSelection();
  range.selectNodeContents(copyText);  
  selection.removeAllRanges();
  selection.addRange(range);
  document.execCommand("copy");
}


function filterTable() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("search");
  filter = input.value.toUpperCase();
  table = document.getElementById("pedidos-table");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    // filter by first column
    td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}