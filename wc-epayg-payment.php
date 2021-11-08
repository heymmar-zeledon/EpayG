<?php
//Añadimos nuestra propia clase extendiendo de WC_Payment_Gateway
class EpayG_Payment_Gateway extends WC_Payment_Gateway {
  
  // Configuracion del id, descripcion y otros valores de la pasarela de pago
  function __construct() {
    //ID global de nuestra pasarela de pago
    $this->id = "epayg_payment";

    //Titulo de la pasarela que aparecera con las demas pasarelas de pago activas
    $this->method_title = __( "EpayG", 'epayg_payment' );

    //Breve descripcion de nuestra pasarela de pago
    $this->method_description = __( "Metodo de pago rapido atravez de tarjeta de credito o debito", 'epayg_payment' );

    //Titulo que usaremos dentro de las configuraciones de nuestra pasarela
    $this->title = __( "EpayG", 'epayg_payment' );

    //Icono de nuestra pasarela
    $this->icon = apply_filters( 'woocommerce_gateway_icon', $plugin_dir.'\images\pasarela-de-pago.png' );

    //integramos los campos de pago
    $this->has_fields = true;

    // Añadimos un soporte de woocommerce para añadir el forms de la tarjeta de pago
    $this->supports = array( 'default_credit_card_form' );

    // Definimos nuestra configuracion y la cargamos en un metodo llamado init_form_fields()
    $this->init_form_fields();

    //llamamos a init settings para cargarlas en variables
    $this->init_settings();

   //Convertimos estas  configuraciones en variables para poder usarlas
    foreach ( $this->settings as $setting_key => $value ) {
      $this->$setting_key = $value;
    }

    //añadimos una accion para verificar el certificado SSL
    add_action( 'admin_notices', array( $this,  'do_ssl_check' ) );

    // Guardamos las configuraciones verificamos si es el administrador de la pagina y actualizamos la configurcion
    if ( is_admin() ) {
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }
  } // End __construct()


  // Construimos los campos de administracion del plugin para el administrador
  public function init_form_fields() {
    $this->form_fields = array(
      'enabled' => array(
        //funcion de activar o desactivar el plugin
        'title'   => __( 'Activar / Desactivar', 'epayg_payment' ),
        'label'   => __( 'Activar este metodo de pago', 'epayg_payment' ),
        'type'    => 'checkbox',
        'default' => 'no',
      ),
      'title' => array(
        //titulo para el proceso de pago
        'title'   => __( 'Título', 'epayg_payment' ),
        'type'    => 'text',
        'desc_tip'  => __( 'Título de pago que el cliente verá durante el proceso de pago.', 'epayg_payment' ),
        'default' => __( 'Tarjeta de credito', 'epayg_payment' ),
      ),
      'description' => array(
        //breve descripcion
        'title'   => __( 'Descripción', 'epayg_payment' ),
        'type'    => 'textarea',
        'desc_tip'  => __( 'Descripción de pago que el cliente verá durante el proceso de pago.', 'epayg_payment' ),
        'default' => __( 'Pague con seguridad usando su tarjeta de crédito.', 'epayg_payment' ),
        'css'   => 'max-width:350px;'
      ),
      //key id para el hash de pago
      'key_id' => array(
        'title'   => __( 'Key id', 'epayg_payment' ),
        'type'    => 'text',
        'desc_tip'  => __( 'ID de clave de seguridad del panel de control del comerciante.', 'epayg_payment' ),
        'default' => '',
      ),
      //api key para el hash de pago
      'api_key' => array(
        'title'   => __( 'Api key', 'epayg_payment' ),
        'type'    => 'text',
        'desc_tip'  => __( 'ID de clave de api del panel de control del comerciante.', 'epayg_payment' ),
        'default' => '',
      ),
    );
  }
  
  
}
?>
