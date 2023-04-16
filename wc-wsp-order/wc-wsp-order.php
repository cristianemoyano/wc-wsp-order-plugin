<?php
/*
Plugin Name: Whatsapp Orders
Version: 1.0.1
Description: Plugin personalizado para listar pedidos de WooCommerce y generar links de Whatsapp
Author: Cristian Moyano
*/

// Agrega una página de administración para mostrar la lista de pedidos
function mostrar_lista_pedidos_admin_page() {
    add_menu_page(
        'Links de Whatsapp', // Título de la página
        'Links de Whatsapp', // Título del menú
        'manage_options', // Capacidad de usuario requerida para ver la página
        'wc-wsp-order', // Slug del menú
        'mostrar_lista_pedidos_admin_page_callback' // Callback para renderizar el contenido de la página
    );
}

// Generar el formulario HTML de búsqueda
function generar_formulario_busqueda() {
    $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
    $formulario = '<form method="get" action="?page=wc-wsp-order">';
    $formulario .= '<input type="hidden" name="page" value="wc-wsp-order" />';
    $formulario .= '<input type="text" name="search" value="' . esc_attr( $search ) . '" placeholder="Buscar por Nro. de Pedido" />';
    $formulario .= '<input type="submit" value="Buscar" class="button" />';
    $formulario .= '</form>';
    return $formulario;
}

// Callback para renderizar el contenido de la página de administración
function mostrar_lista_pedidos_admin_page_callback() {
    // Obtén la lista de pedidos
    $lista_pedidos = obtener_lista_pedidos();

    // Obtener el formulario de búsqueda y almacenarlo en una variable
    $formulario_busqueda = generar_formulario_busqueda();


    // Muestra la lista de pedidos en el área de administración
    echo '<div class="wrap">';
    echo '<h1>Lista de Pedidos</h1>';
    // Imprimir el formulario de búsqueda
    echo $formulario_busqueda;
    echo $lista_pedidos;
    echo '</div>';
}

// Función para obtener la lista de pedidos
function obtener_lista_pedidos() {
    // Comprueba si WooCommerce está activo
    if (class_exists('WooCommerce')) {
        // Obtener el valor del número de pedido para buscar
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

        // Obtener los pedidos que coinciden con el número de pedido buscado
        $args = array(
            'numberposts' => -1,
            'meta_key'    => '_order_key', // Utilizar _order_key como meta_key para el número de pedido
            'meta_value'  => $search,
            'post_type'   => 'shop_order',
            'post_status' => 'any',
        );
        $pedidos = wc_get_orders( $args );


        // Configurar la paginación
        $pedidos_por_pagina = 10; // Número de pedidos por página
        $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1; // Obtener el número de página actual
        $total_pedidos = count($pedidos); // Obtener el total de pedidos
        $total_paginas = ceil($total_pedidos / $pedidos_por_pagina); // Calcular el total de páginas

        // Calcular el índice inicial y final de los pedidos para la página actual
        $indice_inicial = ($pagina_actual - 1) * $pedidos_por_pagina;
        $indice_final = $indice_inicial + $pedidos_por_pagina - 1;

        // Filtrar los pedidos para la página actual
        $pedidos_pagina = array_slice($pedidos, $indice_inicial, $pedidos_por_pagina);

        // Inicio de la tabla
        $tabla = '<table class="wp-list-table widefat fixed striped">';
        $tabla .= '<thead>';
        $tabla .= '<tr>';
        $tabla .= '<th class="manage-column">Nro. de Pedido</th>';
        $tabla .= '<th class="manage-column">Dirección</th>';
        $tabla .= '<th class="manage-column">Nombre del Cliente</th>';
        $tabla .= '<th class="manage-column">Estado del Pedido</th>'; // Nueva columna para el estado del pedido
        // ... más columnas si es necesario ...
        $tabla .= '</tr>';
        $tabla .= '</thead>';
        $tabla .= '<tbody>';

        foreach ($pedidos_pagina as $pedido) {
            // Obtiene el número de pedido, dirección y nombre del cliente
            $numero_pedido = $pedido->get_order_number();
            $direccion_pedido = $pedido->get_formatted_billing_address();
            $nombre_cliente = $pedido->get_billing_first_name() . ' ' . $pedido->get_billing_last_name();
            // Obtener el estado del pedido utilizando la función wc_get_order_status_name()
            $estado_pedido = wc_get_order_status_name( $pedido->get_status() );

            $tabla .= '<tr>';
            $tabla .= '<td class="column-columnname">' . $numero_pedido . '</td>';
            $tabla .= '<td class="column-columnname">' . $direccion_pedido . '</td>';
            $tabla .= '<td class="column-columnname">' . $nombre_cliente . '</td>';
            $tabla .= '<td class="column-columnname">' . $estado_pedido . '</td>'; // Valor del estado del pedido
            $tabla .= '</tr>';
        }

        // Cierre de la tabla
        $tabla .= '</tbody>';
        $tabla .= '</table>';

        // Mostrar la paginación debajo de la tabla
        $tabla .= '<div class="tablenav">';
        $tabla .= '<div class="tablenav-pages">';
        $tabla .= paginate_links(array(
            'base' => add_query_arg('pagina', '%#%'),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $total_paginas,
            'current' => $pagina_actual,
            'show_all' => false,
            'end_size' => 1,
            'mid_size' => 2,
        ));
        $tabla .= '</div>';
        $tabla .= '</div>';

        return $tabla;
    } else {
        return 'WooCommerce no está activo.';
    }
}

// Agrega la página de administración al hook 'admin_menu'
add_action('admin_menu', 'mostrar_lista_pedidos_admin_page');