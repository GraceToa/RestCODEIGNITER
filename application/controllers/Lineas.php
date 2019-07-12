<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/Libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;




class Lineas extends REST_Controller {

  public function __construct(){

    //CORS para filtrar las peticiones (movil - servidor)
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");

    parent:: __construct();
    //siempre que se consulte BD se llama al constructor
    $this->load->database();
    //
  }

//http://localhost/~gracetoa/rest/index.php/lineas si queremos ejecutar solo esto
//necesitamos crear un $index
  public function index_get(){
    //documentación de CodeIngter

    $query = $this->db->query('SELECT * FROM `lineas`');

    $res = array('error'=> FALSE, 'lineas'=> $query->result_array());
    $this->response($res);

  }





}//end class
