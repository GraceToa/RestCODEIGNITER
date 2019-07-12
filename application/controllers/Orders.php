<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/Libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;




class Orders extends REST_Controller {

  public function __construct(){

    //CORS para filtrar las peticiones (movil - servidor)
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");

    parent:: __construct();
    //siempre que se consulte BD se llama al constructor
    $this->load->database();

  }

  //realizar pedido
  public function get_order_post($token = '0', $id_usuario = '0'){
    $data = $this->post();
    //http://localhost/~gracetoa/rest/index.php/orders/get_order
    if ($token == '0' || $id_usuario == '0') {
      $res = array('error' => TRUE ,'message'=> 'Token y/o user invalid' );
      $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }

    //evaluamos si vienen items
    // http://localhost/~gracetoa/rest/index.php/orders/get_order/123/2
    if (!isset($data['items']) || strlen($data['items']) == 0 ) {
      $res = array('error' => TRUE ,'message'=> 'Items empty in post' );
      $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
    }

    //todo bien, items, user, token
    $condicions  = array('id' => $id_usuario,'token'=>$token );
    $this->db->where($condicions);
    $query = $this->db->get('login');

    $exist = $query->row();

    if(!$exist){
      $res = array('error' => TRUE ,'message'=> 'Token and User invalid' );
      $this->response($res);
      return;
    }

    //User and Token OK
    $this->db->reset_query();

    $insert = array('usuario_id' => $id_usuario );
    //lo grabamos en bd tabla ordenes
    $this->db->insert('ordenes', $insert);
    //regresa el id de la ultima insersion en bd
    $orde_id = $this->db->insert_id();
    //http://localhost/~gracetoa/rest/index.php/orders/get_order/01b2c2f2a74af446fee45061b3ca3a22eb322352/1?items=S10_1678,S10_1949
    //genera en tabla Ordenes una fila
    // $this->response($orde_id);

    //crear detalle de la orden, separar los items del Json
    //nos devuelve solo los items
    $items = explode(',', $data['items']);
    // $this->response($items);

    //separamos los items que seran producto_id (ordenes_detalle)
    //abria que validar producto_id
    foreach ($items as &$producto_id) {
      //'producto_id' y 'orden_id' corresponden a la tabla ordenes_detalle
     $data_insert = array('producto_id' => $producto_id ,'orden_id' => $orde_id );
     $this->db->insert('ordenes_detalle', $data_insert);
    }
    $res = array('error' => FALSE ,'orden_id'=> $orde_id );
    $this->response($res);
  }

  //OBTENER PEDIDOS
  public function get_orders_get($token = "0", $id_usuario = "0"){

    if ($token == '0' || $id_usuario == '0') {
      $res = array('error' => TRUE ,'message'=> 'Token y/o user invalid' );
      $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }

    //todo bien, items, user, token
    $condicions  = array('id' => $id_usuario,'token'=>$token );
    $this->db->where($condicions);
    $query = $this->db->get('login');

    $exist = $query->row();

    if(!$exist){
      $res = array('error' => TRUE ,'message'=> 'Token and User invalid' );
      $this->response($res);
      return;
    }

    //token existe y es valido
    //query obtener ordenes de un User
    $query = $this->db->query('SELECT * FROM `ordenes` where usuario_id = ' . $id_usuario );

    $ordes = array();

    foreach ($query->result() as $row) {
      $query_detail = $this->db->query('SELECT a.orden_id, b.* FROM `ordenes_detalle` a INNER JOIN productos b on a.producto_id = b.codigo WHERE orden_id = '. $row->id);
      $order = array('id' =>$row->id , 'creado_en' => $row->creado_en, 'detalle' => $query_detail->result() );
      //add al array items
      array_push($ordes,$order);
    }

    $res = array('error' => FALSE ,'orders'=> $ordes );
    $this->response($res);

  }

  public function delete_order_delete($token = "0", $id_usuario = "0", $order_id ="0"){

    if ($token == '0' || $id_usuario == '0' || $order_id == '0') {
      $res = array('error' => TRUE ,'message'=> 'Token y/o user , order invalid' );
      $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }

    //todo bien, items, user, token
    $condicions  = array('id' => $id_usuario,'token'=>$token );
    $this->db->where($condicions);
    $query = $this->db->get('login');

    $exist = $query->row();

    if(!$exist){
      $res = array('error' => TRUE ,'message'=> 'Token and User invalid' );
      $this->response($res);
      return;
    }

    //verificamos si la orden es de ese User
    $this->db->reset_query();
    $condicions = array('id' => $order_id, 'usuario_id'=> $id_usuario );
    $this->db->where($condicions);
    $query = $this->db->get('ordenes');

    $exist = $query->row();
    if(!$exist){
      $res = array('error' => TRUE ,'message'=> 'This order not exist BD' );
      $this->response($res);
      return;
    }

    //todo ok para realizar delete
    $condicions = array('id' => $order_id );
    $this->db->delete('ordenes', $condicions);
    $condicions = array('orden_id' => $order_id );
    $this->db->delete('ordenes_detalle', $condicions);

    $res = array('error' => FALSE, 'message'=> 'Order delete' );
    $this->response($res);


  }


//Functions for iOS "StoreAlmofire"

    public function create_order_post(){
      $data = $this->post();

       //evaluamos si vienen items
    if (!isset($data['items']) || strlen($data['items']) == 0 ) {
      $res = array('error' => TRUE ,'message'=> 'Items empty in post' );
      $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
    }

    //todo bien, items, user, token
    $condicions  = array('id' => $id_usuario,'token'=>$token );
    $this->db->where($condicions);
    $query = $this->db->get('login');

    $exist = $query->row();

    if(!$exist){
      $res = array('error' => TRUE ,'message'=> 'Token and User invalid' );
      $this->response($res);
      return;
    }

    //User and Token OK
    $this->db->reset_query();

    $insert = array('usuario_id' => $id_usuario );
    //lo grabamos en bd tabla ordenes
    $this->db->insert('ordenes', $insert);
    //regresa el id de la ultima insersion en bd
    $orde_id = $this->db->insert_id();
    //http://localhost/~gracetoa/rest/index.php/orders/get_order/01b2c2f2a74af446fee45061b3ca3a22eb322352/1?items=S10_1678,S10_1949
    //genera en tabla Ordenes una fila
    // $this->response($orde_id);

    //crear detalle de la orden, separar los items del Json
    //nos devuelve solo los items
    $items = explode(',', $data['items']);
    // $this->response($items);

    //separamos los items que seran producto_id (ordenes_detalle)
    //abria que validar producto_id
    foreach ($items as &$producto_id) {
      //'producto_id' y 'orden_id' corresponden a la tabla ordenes_detalle
     $data_insert = array('producto_id' => $producto_id ,'orden_id' => $orde_id );
     $this->db->insert('ordenes_detalle', $data_insert);
    }
    $res = array('error' => FALSE ,'orden_id'=> $orde_id );
    $this->response($res);



    }












}//end class
