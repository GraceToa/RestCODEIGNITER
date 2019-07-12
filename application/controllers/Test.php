<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/Libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;




class Test extends REST_Controller {

  public function __construct(){

    //CORS para filtrar las peticiones (movil - servidor)
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");

    parent:: __construct();
    //siempre que se consulte BD se llama al constructor
    $this->load->database();
    //
  }


  public function index(){
    echo "Hello World";
  }

  //_get al final puede ser _post _update _delete
  public function get_array_get($index = 0){
    //asi controlamos los request y el envio de status
    if($index > 2){
      $res = array('error' => TRUE, 'mesage'=> 'Not exist item with position');
      $this->response( $res, REST_Controller:: HTTP_BAD_REQUEST);
    }else{
        $arrayF = array("Uva","Melon","Naranja");
        $res = array('error' => FALSE, 'fruit'=> $arrayF[$index]);
        $this->response( $res);
    }
  }

//en el postman se pone http://localhost/~gracetoa/rest/index.php/test/get_product/S10_1949
  public function get_product_get($codigo){
    $query = $this->db->query("SELECT * FROM `productos` WHERE codigo = '". $codigo ."'");
    // echo json_encode($query->result() );
    $this->response($query->result());

  }



}//end class
