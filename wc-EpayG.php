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
  if(class_exists( WC_Payment_Gateway ))
  {
    //Creamos nuestra propia clase de pasarela heredando de WC_Payment_Gateway
    class WC_EpayG_Payment_Gateway extends WC_Payment_Gateway
    {
      function __construct() {
        //ID global para nuestro metodo de pago
        $this->id = "EpayG_payment";

        // Titulo que se mostrara en la parte superior de la pagina de pasarelas de pago
        $this->method_title = __( "EPAYG PAYMENT GATEWAY", 'EpayG_payment' );

        // Descripcion de la pasarela de pago mostrada en la pagina de opciones de pago real
        $this->method_description = __( "EPAYG Payment Gateway Plug-in for WooCommerce", 'EpayG_payment' );

        // Titulo que se utilizara para las pestañas verticales que se pueden ordenar de arriba a abajo
        $this->title = __( "EPAYG Payment Gateway", 'EpayG_payment' );

        // Si se desea mostrar un icono o no de la pasarela de pago
        $this->icon = null;

        // integracion directa para los campos de pago se muestren en en proceso de pago.
        $this->has_fields = true;

        // Formulario de tarjeta de credito por defecto
        $this->supports = array( 'default_credit_card_form' );

        // Definicion de su configuracion y son cargados con init_settings()
        $this->init_form_fields();

        //obtenemos la configuracion y podemos cargarlas en variables
        $this->init_settings();

        //convertimos las variables para poder usarlas
        foreach ( $this->settings as $setting_key => $value ) {
           $this->$setting_key = $value;
        }

          //chequeamos el SSL
        add_action( 'admin_notices', array( $this,  'do_ssl_check' ) );

        // Guardamos la configuracion
        if ( is_admin() ) {
          add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
      } // Fin del constructor()
    }
  }
}

?>
