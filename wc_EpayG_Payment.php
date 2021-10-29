<?php
//Creamos nuestra propia clase de pasarela heredando de WC_Payment_Gateway
class EpayG extends WC_Payment_Gateway
{
  function __construct() {
    //ID global para nuestro metodo de pago
    $this->id = "EpayG_payment";

    // Titulo que se mostrara en la parte superior de la pagina de pasarelas de pago
    $this->method_title = __( "EpayG", 'EpayG_payment' );

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

    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

      //chequeamos el SSL
    add_action( 'admin_notices', array( $this,  'do_ssl_check' ) );

    // Guardamos la configuracion
    if ( is_admin() ) {
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }
  } // Fin del constructor()

  //Campos de configuracion del plugin
  public function init_form_fields() {
    $this->form_fields = array(
      'enabled' => array(
        'title'   => __( 'Activar / Desactivar', 'EpayG-payment' ),
        'label'   => __( 'Activar el metodo de pago EpayG', 'EpayG-payment' ),
        'type'    => 'checkbox',
        'default' => 'no',
      ),
      'title' => array(
        'title'   => __( 'Título', 'EpayG-payment' ),
        'type'    => 'text',
        'desc_tip'  => __( 'Título de pago que el cliente verá durante el proceso de pago.', 'EpayG-payment' ),
        'default' => __( 'Tarjeta de crédito', 'bac-payment' ),
      ),
      'description' => array(
        'title'   => __( 'Descripción', 'EpayG-payment' ),
        'type'    => 'textarea',
        'desc_tip'  => __( 'Descripción de pago que el cliente verá durante el proceso de pago.', 'EpayG-payment' ),
        'default' => __( 'Pague con seguridad usando su tarjeta de crédito.', 'EpayG-payment' ),
        'css'   => 'max-width:350px;'
      ),
    );
  }
}
?>
