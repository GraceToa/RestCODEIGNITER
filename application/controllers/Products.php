<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/Libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;




class Products extends REST_Controller {

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

//http://localhost/~gracetoa/rest/index.php/products/allProducts/2
//en el postman nos traera la 2da pagina con 10 items
  public function allProducts_get($page = 0){
    //para traer productos de 10 en 10
    $page = $page * 10;

    $query = $this->db->query('SELECT * FROM `productos` limit '. $page .',10');

    $res = array('error'=> FALSE, 'productos'=> $query->result_array());
    $this->response($res);
  }

  public function allProductsiOS_get(){

    $query = $this->db->query('SELECT * FROM productos ');

    $res = array('error'=> FALSE, 'productos'=> $query->result_array());
    $this->response($res);
  }

//http://localhost/~gracetoa/rest/index.php/products/by_typeProduct/1
//esto traeria los productos con id de linea tipo
//http://localhost/~gracetoa/rest/index.php/products/by_typeProduct/1/2
//trae productos de tipo 1 la 2da pagina
public function by_typeProduct_get($type = 0, $page = 0){
  //controlamos tipo= 0 no existe en bd
  if($type == 0){
    $res = array('error'=> TRUE,'mesage'=> 'parameter type not exist');
    $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
    return;//para que no haga nada mas
  }

  $page = $page * 10;
  $query = $this->db->query('SELECT * FROM `productos` WHERE linea_id = '. $type .'  limit '. $page .',10');

  $res = array('error'=> FALSE, 'productos'=> $query->result_array());
  $this->response($res);
}



//busca en bd por palabra,  busca antes%word%despues
//http://localhost/~gracetoa/rest/index.php/products/searchProduct/ford
//nos trae los productos que tienen en producto ford (no cansensitive)
public function searchProduct_get($word = "not especific"){

  $query = $this->db->query("SELECT * FROM `productos` where producto like '%". $word ."%' ");

  $res = array('error'=> FALSE,
              'word'=> $word,
              'productos'=> $query->result_array());
  $this->response($res);
}






}//end class
