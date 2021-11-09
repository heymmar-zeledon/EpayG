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
    $this->icon = apply_filters( 'woocommerce_gateway_icon', plugins_url('\images\icon.png', __FILE__) );

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
  } // Fin del constructor


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
        'default' => __( '', 'epayg_payment' ),
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

  //Empezamos el proceso de pago
  public function process_payment( $order_id ) {
    global $woocommerce;

    //obtenemos la informacion de esta orden para saber a quien y cuanto se va a cobrar
    $customer_order = new WC_Order( $order_id );

    $url = 'https://EpayG/payment_Gateway.com/';

    $time = time();

    $key_id = $this->key_id;


    $orderid = str_replace( "#", "", $customer_order->get_order_number() );

    $hash = md5($orderid."|".$customer_order->order_total."|".$time."|".$this->api_key);

    // Preparamos la informacion a enviar
    $payload = array(
      "key_id"  => $key_id,
      "hash" => $hash,
      "time" => $time,
      "amount" => $customer_order->order_total,
      "ccnumber" => str_replace( array(' ', '-' ), '', $_POST['bac_payment-card-number'] ),
      "ccexp" => str_replace( array( '/', ' '), '', $_POST['bac_payment-card-expiry'] ),
      "orderid" => $orderid,
      "cvv" => ( isset( $_POST['bac_payment-card-cvc'] ) ) ? $_POST['bac_payment-card-cvc'] : '',
      "type" => "auth",
     );

    // Enviamos esta autorizacion para el procesamiento
    $response = wp_remote_post( $url, array(
      'method'    => 'POST',
      'body'      => http_build_query( $payload ),
      'timeout'   => 90,
      'sslverify' => false,
    ) );

    if ( is_wp_error( $response ) )
      throw new Exception( __( 'Ups! Tenemos un pequeño inconveniente con este pago, sentimos las molestias.', 'epayg_payment' ) );

    if ( empty( $response['body'] ) )
      throw new Exception( __( 'La respuesta esta vacia.', 'bac-payment' ) );

    // Si no se encontro ningun error recuperamos la respuesta
    $response_body = wp_remote_retrieve_body( $response );

    // Analizamos la respuesta para poder leerla
    $resp_e = explode( "&", $response_body ); //Convertimos el cuerpo de la respuesta en strings quitando el delimitador &
    $resp = array();
    foreach($resp_e as $r) {
      $v = explode('=', $r);//separamos los string de cada iteracion del delimitador =
      $resp[$v[0]] = $v[1];//Almacenamos los datos separados en el arreglo resp
    }

    //Evaluamos la respuesta del codigo enviado para verificar si fue exitoso o no
    if ( ($resp['response'] == 1 ) || ( $resp['response_code'] == 200 ) ) {
      // El pago se completo con exito
      $customer_order->add_order_note( __( 'Pago completado con exito.', 'epayg_payment' ) );

      // Guardando la informacion
      $order_id = method_exists( $customer_order, 'get_id' ) ? $customer_order->get_id() : $customer_order->ID;
      update_post_meta($order_id , '_wc_order_authcode', $resp['authcode'] );
			update_post_meta($order_id , '_wc_order_transactionid', $resp['transactionid'] );

      // Marcamos el pedido como pagado
      $customer_order->payment_complete();

      // Vaciamos el carrito
      $woocommerce->cart->empty_cart();

      // Redirigimos a la pagina de agradecimiento
      return array(
        'result'   => 'success',
        'redirect' => $this->get_return_url( $customer_order ),
      );
    } else {
      // Si la transaccion no fue exitosa agregamos una notificacion al carrito
      wc_add_notice( $resp['responsetext'], 'error' );
      // agregamos una nota al pedido referenciado
      $customer_order->add_order_note( 'Error: '. $resp['responsetext'] );
    }

  }//fin del proceso de pago

  //funcion para validar los campos
  public function validate_fields() {
    return true; //retornamos verdadero para activar la validacion
  }

}

//Mostramos el valor del campo en la página de edición del pedido
add_action( 'woocommerce_admin_order_data_after_billing_address', 'show_info', 10, 1 );
function show_info( $order ){
    $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
    echo '<p><strong>'.__('Auth Code').':</strong> ' . get_post_meta( $order_id, '_wc_order_authcode', true ) . '</p>';
    echo '<p><strong>'.__('Transaction Id').':</strong> ' . get_post_meta( $order_id, '_wc_order_transactionid', true ) . '</p>';
}

?>
