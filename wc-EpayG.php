<?php
/*
Plugin name: EpayG
Plugin URI: https://github.com/heymmar-zeledon/EpayG
Description: Extension de WooCommerce añadiendo una pasarela de pago
Version: 1.0
Author: Heymmar_Zeledon
Author URI: https://github.com/heymmar-zeledon
*/

//Verificacion de que WooCommerce este instalado
if(! in_array('woocommerce/woocommerce.php', apply_filters
('active plugins', get-options('active_plugins'))))return;
//añadimos un hooks de accion para iniciar el pago
add_action('plugins_loaded','EpayG_payment_init', 11)
//creamos el cuerpo de la funcion EpayG_payment_init
function EpayG_payment_init()
{
  //Verificamos si la clase WC_Payment_Gateway existe
  if(!class_exists( WC_Payment_Gateway ))return;

  //incluimos el archivo principal de la pasarela
  include_once("wc-EpayG-Payment_Gateway");

  //añadimos nuestro metodo de pago en WooCommerce
  function add_bac_payment_gateway( $methods ){
    $methods[]= 'EpayG_Payment_Gateway';
    return $methods;
  }
}
?>
