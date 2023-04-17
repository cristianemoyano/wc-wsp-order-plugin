<?php
/*
Plugin Name: Whatsapp Orders
Version: 1.0.1
Description: Plugin personalizado para listar pedidos de WooCommerce y generar links de Whatsapp
Author: Cristian Moyano
*/


function wc_wsp_order_enqueue_admin_script() {
    wp_enqueue_script( 'wp-wsp-order-script', plugin_dir_url( __FILE__ ) . '/assets/js/main.js', array(), '1.0' );
}
// Cargar script
add_action('admin_enqueue_scripts', 'wc_wsp_order_enqueue_admin_script');

function wc_wsp_order_enqueue_styles() {
    // Registrar el estilo
    wp_register_style('wc_wsp_orde-style', plugins_url('/assets/css/main.css', __FILE__));
    // Encolar el estilo
    wp_enqueue_style('wc_wsp_orde-style');
}
add_action('admin_enqueue_scripts', 'wc_wsp_order_enqueue_styles');


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
    $formulario .= '<input type="number" name="search" value="' . esc_attr( $search ) . '" placeholder="Buscar por Nro. de Pedido" />';
    $formulario .= '<input type="submit" value="Buscar" class="button" />';
    $formulario .= '</form>';
    return $formulario;
}

function generar_filter_input() {
    $input = '<div class="search"><input type="text" id="search" onkeyup="filterTable()"  placeholder="Buscar por Nro. de Pedido" /></div>';
    return $input;
}

// Callback para renderizar el contenido de la página de administración
function mostrar_lista_pedidos_admin_page_callback() {
    // Obtén la lista de pedidos
    $lista_pedidos = obtener_lista_pedidos();
    $search = generar_filter_input();

    // Muestra la lista de pedidos en el área de administración
    echo '<div class="wrap">';
    echo '<h1>Lista de Pedidos</h1>';

    echo $search;
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
            'limit' => -1,
        );
        $pedidos = wc_get_orders( $args );


        // Configurar la paginación
        $pedidos_por_pagina = 50; // Número de pedidos por página
        $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1; // Obtener el número de página actual
        $total_pedidos = count($pedidos); // Obtener el total de pedidos
        $total_paginas = ceil($total_pedidos / $pedidos_por_pagina); // Calcular el total de páginas

        // Calcular el índice inicial y final de los pedidos para la página actual
        $indice_inicial = ($pagina_actual - 1) * $pedidos_por_pagina;
        $indice_final = $indice_inicial + $pedidos_por_pagina - 1;

        // Filtrar los pedidos para la página actual
        $pedidos_pagina = array_slice($pedidos, $indice_inicial, $pedidos_por_pagina);

        // Inicio de la tabla
        $tabla = '<div class="tablenav">';
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

        $tabla = '<div class="table-container wrap">';
        $tabla .= '<table id="pedidos-table" class="tabla pedidos">';
        $tabla .= '<thead>';
        $tabla .= '<tr>';
        $tabla .= '<th class="manage-pedido">Nro. de Pedido</th>';
        $tabla .= '<th class="manage-direccion">Dirección</th>';
        $tabla .= '<th class="manage-cliente">Nombre del Cliente</th>';
        $tabla .= '<th class="manage-empresa">Empresa</th>';
        $tabla .= '<th class="manage-estado">Estado del Pedido</th>';
        $tabla .= '<th class="manage-total">Total</th>';
        $tabla .= '<th class="manage-link">Link</th>';
        $tabla .= '<th class="manage-msg">Mensaje</th>';
        $tabla .= '</tr>';
        $tabla .= '</thead>';
        $tabla .= '<tbody>';

        foreach ($pedidos_pagina as $pedido) {
            // Obtiene el número de pedido, dirección y nombre del cliente
            $numero_pedido = $pedido->get_order_number();
            $direccion_pedido = $pedido->get_formatted_billing_address();

            $nombre_cliente = $pedido->get_billing_first_name() . ' ' . $pedido->get_billing_last_name();

            $nombre_empresa = $pedido->get_billing_company();

            $total = $pedido->get_total();
            
            // Remover el nombre del cliente
            $formatted_billing_address = str_replace( $nombre_cliente,"", $direccion_pedido );
            // Remover el nombre de la empresa
            $formatted_billing_address = str_replace( $nombre_empresa,"", $formatted_billing_address );

            // Obtener el estado del pedido utilizando la función wc_get_order_status_name()
            $raw_status = $pedido->get_status();
            $estado_pedido = wc_get_order_status_name( $raw_status );

            $tabla .= '<tr id="'.$numero_pedido.'">';
            $tabla .= '<td class="column-nro-pedido">' . $numero_pedido . '</td>';
            $tabla .= '<td class="column-direccion">' . $formatted_billing_address . '</td>';
            $tabla .= '<td class="column-cliente">' . $nombre_cliente . '</td>';
            $tabla .= '<td class="column-empresa">' . $nombre_empresa . '</td>';
            $tabla .= '<td class="column-estado"><mark class="order-status status-'.$raw_status.'"><span>' . $estado_pedido . '</span></mark></td>';
            $tabla .= '<td class="column-total">'.wc_price($total).'</td>';
            $tabla .= '<td class="column-link"></td>';
            $tabla .= '<td class="column-msg"></td>';
            $tabla .= '</tr>';
        }

        // Cierre de la tabla
        $tabla .= '</tbody>';
        $tabla .= '</table>';
        $tabla .= '</div>';

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
