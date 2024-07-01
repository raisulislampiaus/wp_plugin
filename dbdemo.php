<?php
/*
Plugin Name: sign up to a contact list
Plugin URI:
Description: sign up to a contact list
Version: 1.0
Author: Raisul Islam
Author URI: https://github.com/raisulislampiaus

*/

define( "DBDEMO_DB_VERSION", "1.4" );
require_once "class.dbdemousers.php";

function dbdemo_init() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'new';
    $sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            address text NOT NULL,
            phone varchar(15) NOT NULL,
            email varchar(100) NOT NULL,
            hobbies text NOT NULL,
            PRIMARY KEY (id)
    );";
    require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
    dbDelta( $sql );

    add_option( "dbdemo_db_version", DBDEMO_DB_VERSION );

    if ( get_option( "dbdemo_db_version" ) != DBDEMO_DB_VERSION ) {
        $sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            address text NOT NULL,
            phone varchar(15) NOT NULL,
            email varchar(100) NOT NULL,
            hobbies text NOT NULL,
            PRIMARY KEY (id)
        );";
        dbDelta( $sql );
        update_option( "dbdemo_db_version", DBDEMO_DB_VERSION );
    }
}

register_activation_hook( __FILE__, "dbdemo_init" );

function dbdemo_drop_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'new';
    // Here you can add any necessary column modifications or drops
    update_option( "dbdemo_db_version", DBDEMO_DB_VERSION );
}

add_action( "plugins_loaded", "dbdemo_drop_column" );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( "toplevel_page_dbdemo" == $hook ) {
        wp_enqueue_style( 'dbdemo-style', plugin_dir_url( __FILE__ ) . 'assets/css/form.css' );
    }
} );

function dbdemo_load_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'new';
    $wpdb->insert( $table_name, [
        'name'  => 'John Doe',
        'address' => '123 Street',
        'phone' => '1234567890',
        'email' => 'john@doe.com',
        'hobbies' => 'Reading, Swimming'
    ] );
    $wpdb->insert( $table_name, [
        'name'  => 'Jane Doe',
        'address' => '456 Avenue',
        'phone' => '0987654321',
        'email' => 'jane@doe.com',
        'hobbies' => 'Running, Biking'
    ] );
}

register_activation_hook( __FILE__, "dbdemo_load_data" );

function dbdemo_flush_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'new';
    $query      = "TRUNCATE TABLE {$table_name}";
    $wpdb->query( $query );
}

register_deactivation_hook( __FILE__, "dbdemo_flush_data" );

add_action( 'admin_menu', function () {
    add_menu_page( 'WordPress Plugin for Dotcamp', 'WordPress Plugin for Dotcamp', 'manage_options', 'dbdemo', 'dbdemo_admin_page' );
} );

function dbdemo_admin_page() {
    global $wpdb;
    if ( isset( $_GET['pid'] ) ) {
        if ( ! isset( $_GET['n'] ) || ! wp_verify_nonce( $_GET['n'], "dbdemo_edit" ) ) {
            wp_die( __( "Sorry you are not authorized to do this", "database-demo" ) );
        }

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {
            $wpdb->delete( "{$wpdb->prefix}new", [ 'id' => sanitize_key( $_GET['pid'] ) ] );
            $_GET['pid'] = null;
        }
    }

    echo '<h2>DB Demo</h2>';
    $id = $_GET['pid'] ?? 0;
    $id = sanitize_key( $id );
    if ( $id ) {
        $result = $wpdb->get_row( "select * from {$wpdb->prefix}new WHERE id='{$id}'" );
    }
    ?>
    <div class="form_box">
        <div class="form_box_header">
            <?php _e( 'SignUp', 'database-demo' ) ?>
        </div>
        <div class="form_box_content">
            <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
                <?php wp_nonce_field( 'dbdemo', 'nonce' ); ?>
                <input type="hidden" name="action" value="dbdemo_add_record">
                <label><strong>Name</strong></label><br/>
                <input type="text" name="name" class="form_text" value="<?php if ( $id ) { echo $result->name; } ?>"><br/>
                <label><strong>Address</strong></label><br/>
                <input type="text" name="address" class="form_text" value="<?php if ( $id ) { echo $result->address; } ?>"><br/>
                <label><strong>Phone</strong></label><br/>
                <input type="text" name="phone" class="form_text" value="<?php if ( $id ) { echo $result->phone; } ?>"><br/>
                <label><strong>Email</strong></label><br/>
                <input type="text" name="email" class="form_text" value="<?php if ( $id ) { echo $result->email; } ?>"><br/>
                <label><strong>Hobbies</strong></label><br/>
                <input type="text" name="hobbies" class="form_text" value="<?php if ( $id ) { echo $result->hobbies; } ?>"><br/>
                <?php
                if ( $id ) {
                    echo '<input type="hidden" name="id" value="' . $id . '">';
                    submit_button( "Update Record" );
                } else {
                    submit_button( "Add Record" );
                }
                ?>
            </form>
        </div>
    </div>
    <div class="form_box" style="margin-top: 30px;">
        <div class="form_box_header">
            <?php _e( 'Contact List', 'database-demo' ) ?>
        </div>
        <div class="form_box_content">
            <?php
            global $wpdb;
            $dbdemo_users = $wpdb->get_results( "SELECT id, name, address, phone, email, hobbies FROM {$wpdb->prefix}new ORDER BY id DESC", ARRAY_A );
            $dbtu         = new DBTableUsers( $dbdemo_users );
            $dbtu->prepare_items();
            $dbtu->display();
            ?>
        </div>
    </div>
    <?php
}

add_action( 'admin_post_dbdemo_add_record', function () {
    global $wpdb;
    $nonce = sanitize_text_field( $_POST['nonce'] );
    if ( wp_verify_nonce( $nonce, 'dbdemo' ) ) {
        $name  = sanitize_text_field( $_POST['name'] );
        $address = sanitize_text_field( $_POST['address'] );
        $phone = sanitize_text_field( $_POST['phone'] );
        $email = sanitize_text_field( $_POST['email'] );
        $hobbies = sanitize_text_field( $_POST['hobbies'] );
        $id    = sanitize_text_field( $_POST['id'] );

        if ( $id ) {
            $wpdb->update( "{$wpdb->prefix}new", [ 'name' => $name, 'address' => $address, 'phone' => $phone, 'email' => $email, 'hobbies' => $hobbies ], [ 'id' => $id ] );
            $nonce = wp_create_nonce( "dbdemo_edit" );
            wp_redirect( admin_url( 'admin.php?page=dbdemo&pid=' ) . $id . "&n={$nonce}" );
        } else {
            $wpdb->insert( "{$wpdb->prefix}new", [ 'name' => $name, 'address' => $address, 'phone' => $phone, 'email' => $email, 'hobbies' => $hobbies ] );
            wp_redirect( admin_url( 'admin.php?page=dbdemo' ) );
        }
    }
} );
?>
