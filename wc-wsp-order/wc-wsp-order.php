<?php
/*
Plugin Name: Whatsapp Orders
Version: 1.0.0
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

// Callback para renderizar el contenido de la página de administración
function mostrar_lista_pedidos_admin_page_callback() {
    // Obtén la lista de pedidos
    $lista_pedidos = obtener_lista_pedidos();

    // Muestra la lista de pedidos en el área de administración
    echo '<div class="wrap">';
    echo '<h1>Lista de Pedidos</h1>';
    echo $lista_pedidos;
    echo '</div>';
}

// Función para obtener la lista de pedidos
function obtener_lista_pedidos() {
    // Comprueba si WooCommerce está activo
    if (class_exists('WooCommerce')) {
        // Obtiene los pedidos de WooCommerce
        $pedidos = wc_get_orders(array('limit' => -1));

        // Inicio de la tabla
        $tabla = '<table class="wp-list-table widefat fixed striped">';
        $tabla .= '<thead>';
        $tabla .= '<tr>';
        $tabla .= '<th class="manage-column">Nro. de Pedido</th>';
        $tabla .= '<th class="manage-column">Dirección</th>';
        $tabla .= '<th class="manage-column">Nombre del Cliente</th>';
        // ... más columnas si es necesario ...
        $tabla .= '</tr>';
        $tabla .= '</thead>';
        $tabla .= '<tbody>';

        foreach ($pedidos as $pedido) {
            // Obtiene el número de pedido, dirección y nombre del cliente
            $numero_pedido = $pedido->get_order_number();
            $direccion_pedido = $pedido->get_formatted_billing_address();
            $nombre_cliente = $pedido->get_billing_first_name() . ' ' . $pedido->get_billing_last_name();

            $tabla .= '<tr>';
            $tabla .= '<td class="column-columnname">' . $numero_pedido . '</td>';
            $tabla .= '<td class="column-columnname">' . $direccion_pedido . '</td>';
            $tabla .= '<td class="column-columnname">' . $nombre_cliente . '</td>';
            $tabla .= '</tr>';
        }

        // Cierre de la tabla
        $tabla .= '</tbody>';
        $tabla .= '</table>';

        return $tabla;
    } else {
        return 'WooCommerce no está activo.';
    }
}

// Agrega la página de administración al hook 'admin_menu'
add_action('admin_menu', 'mostrar_lista_pedidos_admin_page');