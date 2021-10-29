<?php
/*
Plugin name: EpayG
Plugin URI: https://github.com/heymmar-zeledon/EpayG
Description: Extension de WooCommerce añadiendo una pasarela de pago
Version: 1.0
Author: Heymmar_Zeledon
Author URI: https://github.com/heymmar-zeledon
*/

add_filter( 'woocommerce_payment_gateways', 'epayg_add_gateway_class' );
function epayg_add_gateway_class( $gateways ) {
  $gateways[] = 'EpayG';
  return $gateway;
}

//Añadimos el hooks de filtro para mostrar nuestra pasarela de pago en los metodos de pagina
add_filter( 'woocommerce_payment_gateways_setting_columns', 'rudr_add_payment_method_column' );

function rudr_add_payment_method_column( $default_columns ) {

$default_columns = array_slice( $default_columns, 0, 2 ) + array( 'id' => 'ID' ) + array_slice( $default_columns, 2, 3 );
return $default_columns;
}

// woocommerce_payment_gateways_setting_column_{COLUMN ID}
add_action( 'woocommerce_payment_gateways_setting_column_id', 'rudr_populate_gateway_column' );

function rudr_populate_gateway_column( $gateway ) {

  echo '<td style="width:10%">' . $gateway->id . '</td>';
}

//añadimos un link hacia las configuraciones desde la pagina de los plugins
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'epayg_payment_action_links' );
 function epayg_payment_action_links( $links ) {
   $plugin_links = array(
     '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Ajustes', 'EpayG_payment' ) . '</a>',
   );
   return array_merge( $plugin_links, $links );
 }

//añadimos un hooks de accion para iniciar el pago
add_action('plugins_loaded','EpayG_payment_init', 0);
//creamos el cuerpo de la funcion EpayG_payment_init
function EpayG_payment_init(){
  //Verificamos si la clase WC_Payment_Gateway existe
  if(! class_exists( WC_Payment_Gateway ) )return;

  //incluimos el archivo principal de la pasarela
  include_once('wc-EpayG_Payment.php');

}

?>
