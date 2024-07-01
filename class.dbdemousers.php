<?php

if ( ! class_exists( "WP_List_Table" ) ) {
    require_once( ABSPATH . "wp-admin/includes/class-wp-list-table.php" );
}

class DBTableUsers extends WP_List_Table {

    function __construct( $data ) {
        parent::__construct();
        $this->items = $data;
    }

    function get_columns() {
        return [
            'cb'      => '<input type="checkbox">',
            'name'    => __( 'Name', 'database-demo' ),
            'address' => __( 'Address', 'database-demo' ),
            'phone'   => __( 'Phone', 'database-demo' ),
            'email'   => __( 'Email', 'database-demo' ),
            'hobbies' => __( 'Hobbies', 'database-demo' ),
            'action'  => __( 'Action', 'database-demo' ),
        ];
    }

    function column_cb( $item ) {
        return "<input type='checkbox' value='{$item['id']}'>";
    }

    function column_name( $item ) {
        $nonce   = wp_create_nonce( "dbdemo_edit" );
        $actions = [
            'edit'   => sprintf( '<a href="?page=dbdemo&pid=%s&n=%s">%s</a>', $item['id'], $nonce, __( 'Edit', 'database-demo' ) ),
            'delete' => sprintf( '<a href="?page=dbdemo&pid=%s&n=%s&action=%s">%s</a>', $item['id'], $nonce, 'delete', __( 'Delete', 'database-demo' ) ),
        ];

        return sprintf( '%s %s', $item['name'], $this->row_actions( $actions ) );
    }

    function column_action( $item ) {
        $link = wp_nonce_url( admin_url( 'admin.php?page=dbdemo&pid=' ) . $item['id'], "dbdemo_edit", 'n' );

        return "<a href='" . esc_url( $link ) . "'>Edit</a>";
    }

    function column_default( $item, $column_name ) {
        return $item[ $column_name ];
    }

    function prepare_items() {
        $this->_column_headers = array( $this->get_columns(), [], [] );
    }
}
