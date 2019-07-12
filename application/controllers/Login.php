<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/Libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;




class Login extends REST_Controller {

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

  //para comprobar email and password
  //http://localhost/~gracetoa/rest/index.php/login
  
  public function register_post(){
    //get all post
    $data = $this->post();

    if(!isset($data['correo']) OR !isset ($data['contrasena'])){
      $res = array(
                    'error' => TRUE,
                    'mesage'=> 'The data is incorrect¡'
                  );
      $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }

      // se ha ingresado correo y contraseña verificamos
      $condicions = array('correo'=> $data['correo'], 'contrasena'=>$data['contrasena']);
      $query = $this->db->get_where('login',$condicions);
      $user =  $query->row();

      if(!isset($user)){
        $res = array(
                      'error' => TRUE,
                      'mesage'=> 'User y/o password incorrect'
                    );

        $this->response($res);
        return;

      }//else{
      //   $this->response($data['correo']);
      //  }

      //User and password correct
      // generamos TOKEN 2 formas
    //  $token = bin2hex(openssl_random_pseudo_bytes(20));
      $token = hash('ripemd160',$data['correo']);//a base del correo
      // $this->response($token);

    //clear $query
    $this->db->reset_query();
    //save in bd token
    $update_token = array('token'=> $token);
    $this->db->where('id',$user->id);

    $done = $this->db->update('login',$update_token);

    //regresamos el token y id-user para grabarlo en local store del dispositivo
    //para futuras peticiones
    $res = array('error'=> FALSE, 'token' => $token,'id_usuario'=> $user->id);

    $this->response($res);

  }



  public function nameUser_get($id="0"){
    $query = $this->db->query('SELECT nombre FROM `login` WHERE id = '.$id);
    $res = array('error' => FALSE ,'nombre'=>  $query->result());
    $this->response($res);
  }


//http://localhost/~gracetoa/rest/index.php/login/user/1
  public function user_get($id="0"){
    $query = $this->db->query('SELECT * FROM `users` WHERE id = '.$id);
    $res = array('error' => FALSE ,'user'=>  $query->result());
    $this->response($res);
  }


  /* 
  Función utilizada en iOS "StoreAlmofire", para registrar un user y
  grabarlo en la tabla users
  */
  public function createUser_post() {

    $path = "/Users/gracetoa/Sites/rest/public/img/imgUsers";

    $email = $_POST['email'];
    $passw = $_POST['passw'];
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $address = $_POST['address'];
    $country = $_POST['country'];

    //mueve un archivo subido (temporal) a una nueva ubicación ($path)
    move_uploaded_file($_FILES['image']['tmp_name'], $path."/".$_FILES['image']['name']);

    $image = "http://127.0.0.1/~gracetoa/rest/public/img/imgUsers/".$_FILES['image']['name'];


    $query = $this->db->query("INSERT INTO users VALUES(DEFAULT,'$email','$passw','$name','$lastname','$address','$country','$image')");

    if($query){
             $res = array(
              'error' => TRUE,
              'message'=> 'User OK Save '
            );
    }else{
            $res = array(
              'error' => FALSE,
              'message'=> 'Error save User '
            );
           }


       $this->response($res);
    
  }

/*
función para comprobar email y password (Login), iniciar sesión
se utiliza en iOS "StoreAlmofire"
*/
public function login_iOS_post(){
    //get all post
    $data = $this->post();

    if(!isset($data['email']) OR !isset ($data['passw'])){
      $res = array(
                    'error' => TRUE,
                    'mesage'=> 'The data is incorrect¡'
                  );
      $this->response($res, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }

      // se ha ingresado correo y contraseña verificamos
      $condicions = array('email'=> $data['email'], 'passw'=>$data['passw']);
      $query = $this->db->get_where('users',$condicions);
      $user =  $query->row();

      if(!isset($user)){
        $res = array(
                      'error' => TRUE,
                      'mesage'=> 'User y/o password incorrect'
                    );

        $this->response($res);
        return;

      }
      else{
        $res = array(
              'error' => FALSE,
              'mesage'=> 'User OK',
              'id_user'=> $user->id
            );

        $this->response($res);
       }
  }

//for iOS "StoreAlmofire" devuelve un user para "Profile"

//http://localhost/~gracetoa/rest/index.php/login/user/1
  public function user_ios_post(){
    $id = $_POST['id'];
    $query = $this->db->query('SELECT * FROM `users` WHERE id = '.$id);
    $res = array('error' => FALSE ,'user'=>  $query->result());
    $this->response($res);
  }

/*
  función para iOS "StoreAlmofire"
  para editar un usuario
*/
public function edit_user_post(){
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $address = $_POST['address'];
    $country = $_POST['country'];
    $email = $_POST['email'];
    $id = $_POST['id'];

    $query = $this->db->query("UPDATE users SET email = '$email',name = '$name',lastname = '$lastname',address = '$address',country = '$country' WHERE id = '$id' ");
       
     $res = array('message' => "OK UPDATE");
      $this->response($res);
}




}//end class
