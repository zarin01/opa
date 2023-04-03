<div class="opa-show-dashboard__payments">
    <h3><?php _e( 'Show Payment Processing', OPA_DOMAIN ) ?></h3>
    <?php
    $payment_info = OPA_Model_Show::get_revenue_info( $show_id );
    $payment_info_formatted = [];
    foreach ( $payment_info as $payment ) {
        $payment_info_formatted[] = array(
            'Revenue' => esc_html( $payment_info[0]['payments'] ),
            'Refunds' => esc_html( $payment_info[0]['refunds'] ),
            'Profit' => esc_html( $payment_info[0]['payments'] - $payment_info[0]['refunds'] ),
        );
    }

    echo OPA_Functions::build_table( $payment_info_formatted, 'hide' );
    ?>
</div>
