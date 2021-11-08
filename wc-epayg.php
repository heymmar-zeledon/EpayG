<?php
/*
Plugin name: EpayG
Plugin URI: https://github.com/heymmar-zeledon/EpayG
Description: Extension de WooCommerce añadiendo una pasarela de pago
Version: 1
Author: Heymmar_Zeledon
Author URI: https://github.com/heymmar-zeledon
*/

// Añadimos la funcion de iniciar el metodo de pago
  add_action( 'plugins_loaded', 'EpayG_payment_init', 0 );
  function EpayG_payment_init() {

    //Si la clase para pasarelas de pago de woocommerce no existe retornamos
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

    //Incluimos nuestra propia clase en otro archivo.php
    include_once( 'wc-epayg-payment.php' );

    //Añadimos nuestro metodo de pago en los ajustes de WooCommerce
    add_filter( 'woocommerce_payment_gateways', 'add_EpayG_payment_gateway' );
    function add_EpayG_payment_gateway( $methods ) {
      $methods[] = 'EpayG_Payment_Gateway';
      return $methods;
    }
  }


  // Añadimos funciones para ajustes perzonalizados de nuestro metodo de pago
  add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'EpayG_action_links' );
  function EpayG_action_links( $links ) {
    $plugin_links = array(
      '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Ajustes', 'epayg-payment' ) . '</a>',
    );

    // Añadimos el nuevo link a nuestros links del plugin
    return array_merge( $plugin_links, $links );
  }
?>
