<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
* --------------------------------------------------------------------------
* @ Controller Name          : All the Employee related api call from employee controller
* @ Added Date               : 28-05-2017
* @ Added By                 : Sanjoy
* -----------------------------------------------------------------
* @ Description              : This is the Employee index page
* -----------------------------------------------------------------
* @ return                   : array
* -----------------------------------------------------------------
* @ Modified Date            : 28-05-2017
* @ Modified By              : Sanjoy
* 
*/

//All the required library file for API has been included here 
/*require APPPATH . 'libraries/api/AppExtrasAPI.php';
require APPPATH . 'libraries/api/AppAndroidGCMPushAPI.php';
require APPPATH . 'libraries/api/AppApplePushAPI.php';*/

require_once('src/OAuth2/Autoloader.php');
require APPPATH . 'libraries/api/REST_Controller.php';


class Products extends REST_Controller{
    function __construct(){

        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: authorization, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }

        
        $this->load->config('rest');
        
        /*$this->load->config('serverconfig');
        $developer = 'www.massoftind.com';
        $this->app_path = "api_" . $this->config->item('test_api_ver');
        //publish app version
        $version = str_replace('_', '.', $this->config->item('test_api_ver'));

        $this->publish = array(
            'version' => $version,
            'developer' => $developer
        );*/
        
        //echo $_SERVER['SERVER_ADDR']; exit;
        $dsn = 'mysql:dbname='.$this->config->item('oauth_db_database').';host='.$this->config->item('oauth_db_host');
        $dbusername = $this->config->item('oauth_db_username');
        $dbpassword = $this->config->item('oauth_db_password');

        /*$sitemode= $this->config->item('site_mode');
        $this->path_detail=$this->config->item($sitemode);*/      
        $this->tables = $this->config->item('tables'); 
        $this->load->model('api_' . $this->config->item('test_api_ver') . '/admin/products_model', 'products');
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->library('encrypt');

        //$this->load->library('calculation');

       $this->encryption->initialize(array(
            'cipher' => 'aes-256',
            'mode'   => 'ctr',
            'key'    => 'SAGLcHZ6nxEBnE4XlJ1nmcPTZaOXOGIX',
        ));

        $mongo = new MongoClient();
        $this->mongodb = $mongo->selectDB('test');
        $this->user = $this->mongodb->user;
        $this->products = $this->mongodb->products;


        $this->push_type = 'P';
        //$this->load->library('mpdf');

         OAuth2\Autoloader::register();

        // $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
        $storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $dbusername, 'password' => $dbpassword));

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $this->oauth_server = new OAuth2\Server($storage);

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $this->oauth_server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->oauth_server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
    }


function getAllProducts_post(){
  //pre($this->post(),1);
 
 $error_message = $success_message = $http_response = '';
 $result_arr = array();
 $output_array=array();
 if (!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) 
    {
        $error_message = 'Invalid Token';
        $http_response = 'http_response_unauthorized';
    }
    else
    {

        $req_arr = $details_arr = array();
        //pre($this->input->post(),1);
        $flag = true;
        //echo $this->post('pass_key',true); exit();
        

        if(empty($this->post('pass_key',true))){
        $flag = false;
        $error_message = "pass key is required";
        }else{
          $req_arr['pass_key'] = $this->post('pass_key',true);

         }
        if(empty($this->post('admin_user_id',true))){
            $flag = false;
            $error_message = "admin user id is required";
        }else{
            $req_arr['admin_user_id'] = $this->post('admin_user_id',true);
        }

         if(empty($this->post('page',true))){
            $flag = false;
            $error_message = "page is required";
         }else{
             $req_arr['page'] = $this->post('page',true);

         }

        if($flag && empty($this->post('page_size', true)))
        {
            $flag           = false;
            $error_message  = "Page Size is required";
        }
        else
        {
            $req_arr['page_size']  = $this->post('page_size', true);
        }


        $req_arr['order']           = $this->input->post('order', true);
        $req_arr['order_by']        = $this->input->post('order_by', true);
        $search                     = $this->input->post('searchByName', true);

      if($flag)
      {

       $nmregx                      =new MongoRegex("/$search/i");
       $like                        =array('name'=>$nmregx);

       //print_r($like);die();
       $details_arr                 = $this->products->find($like);

       

        if(!empty($details_arr) && count($details_arr)>0)
           {
              foreach($details_arr as $row)
               {
                   $temp['productsId'] = $row['_id']->{'$id'};
                   $temp['name']       = $row['name'];
                   $temp['product_code']= $row['product_code'];
                   $temp['price']       = $row['price'];
                   $temp['category_id'] = $row['category']['id'];
                   $temp['category_name'] = $row['category']['name'];
                   array_push($output_array, $temp);

               }
                $result_arr['dataset']= $output_array;
                //print_r($result_arr);die();
                $http_response      = 'http_response_ok';
                $success_message    = 'All Products';  
 

           } 
             //print_r($output_array); die;
                   
            
        else 
        {
            $http_response      = 'http_response_bad_request';
            $error_message      = 'Something went wrong in API';  
        }
      }
      else
      {
        $http_response      = 'http_response_bad_request';
      }
    }
 json_response($result_arr, $http_response, $error_message, $success_message);
}
    

function addProducts_post(){
$error_message = $success_message = $http_response = '';
 $result_arr = array();
if (!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) 
        {
            $error_message = 'Invalid Token';
            $http_response = 'http_response_unauthorized';
        }
    else{


        $req_arr = $details_arr = array();
        //pre($this->input->post(),1);
        $flag = true;
        //echo $this->post('id',true); exit();

        if(empty($this->post('pass_key',true))){
        $flag = false;
        $error_message = "pass key is required";
        }else{
          $req_arr['pass_key'] = $this->post('pass_key',true);

         }
        if(empty($this->post('admin_user_id',true))){
            $flag = false;
            $error_message = "admin user id is required";
        }else{
            $req_arr['admin_user_id'] = $this->post('admin_user_id',true);
        }
       
        if(empty($this->post('name',true))){
          $flag = false;
          $error_message = "Products Name is required";

        }else{
            $req_arr['product_name'] = $this->post('name',true);
        }
        if(empty($this->post('cat_name',true))){

            $flag = false;
            $error_message = "Category name is required";

        }else{
            $req_arr['cat_id'] = $this->post('cat_name',true);
            if($req_arr['cat_id']=='1')
            {
                $req_arr['cat_name']="Laptop";
            }
            else if($req_arr['cat_id']=='2')
            {
                $req_arr['cat_name']="Mobile";
            }
            else if($req_arr['cat_id']=='3')
            {
                $req_arr['cat_name']="Cameras";
            }
            $req_arr['category']=array('id'=>$req_arr['cat_id'],'name'=>$req_arr['cat_name']);

            //print_r($req_arr['category']);die();

        }
        if(empty($this->post('price',true))){
            $flag = false;
            $error_message = "Product Price is required";
       }else{
        $req_arr['product_price'] = new MongoInt32($this->post('price',true));
       }
       
       $req_arr['product_code']='PR'.rand(000,999);

       $result_arr['id'] = '1';
       $product_id= $this->post('id',true);
  
    //pre($req_arr,1);

       //print_r($_FILES['file']); exit();
            //print_r($_FILES['profile_image']['name']);
    
    
        if($flag)
        {
            if(!empty($product_id))
            {
               //echo "update";die();
               $where=array("_id"=>new MongoId($product_id));
               $set=array(
                    '$set'=>array("name"=>$req_arr['product_name'],
                          "category"=>$req_arr["category"],
                        "price"=> $req_arr['product_price']));

               $result=$this->products->update($where,$set);
                 if($result['err'])
                 {
                    $error_message  = 'There is some problem, please try again';
                    $http_response = 'http_response_bad_request';
                 }
                 else
                 {
                    $http_response = 'http_response_ok';
                    $success_message = 'Update Products successfully';
                 }
            }

            else
            {
              $insert_array=array('_id'=>new MongoId(),
                                  "name"=>$req_arr['product_name'],
                                  "category"=>$req_arr["category"],
                                  "product_code"=>$req_arr['product_code'],
                                  "price"=> $req_arr['product_price'],); 

              $result=$this->products->insert($insert_array); 
               if($result['err'])
               {
                  $error_message  = 'There is some problem, please try again';
                  $http_response = 'http_response_bad_request';
               }
               else
               {
                  $http_response = 'http_response_ok';
                  $success_message = 'Add Products successfully';
               }
            }

        }
       
        else
        {
            $http_response = 'http_response_bad_request';
        }

    }
 //pre($result_arr,1);

    json_response($result_arr, $http_response, $error_message, $success_message);
}


function getProductsDetail_post(){

//echo "hiiii";die();
$error_message = $success_message = $http_response = '';
$result_arr = array();
$output_array=array();
if (!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) 
        {
            $error_message = 'Invalid Token';
            $http_response = 'http_response_unauthorized';
        }
else{

        $req_arr = $details_arr = array();
        $flag = true;

        if(empty($this->post('pass_key',true))){
        $flag = false;
        $error_message = "pass key is required";
        }else{
          $req_arr['pass_key'] = $this->post('pass_key',true);

         }
        if(empty($this->post('admin_user_id',true))){
            $flag = false;
            $error_message = "admin user id is required";
        }else{
            $req_arr['admin_user_id'] = $this->post('admin_user_id',true);
        }

        if(empty($this->post('productsID')))
        {
                  $flag = false;
                  $error_message = "Products Id is required";
        }else{
          $req_arr['productsID'] = $this->post('productsID');
        }

       if($flag){

          $where=array("_id"=>new MongoId($req_arr['productsID']));
          //print_r($where);die();
          $products_detail = $this->products->find($where);
          //pre($products_detail);die();

          if(!empty($products_detail) && count($products_detail)>0)
           {
              foreach($products_detail as $row)
               {
                    
                   $temp['productsId'] = $row['_id']->{'$id'};
                   $temp['name']       = $row['name'];
                   $temp['product_code']= $row['product_code'];
                   $temp['price']       = $row['price'];
                   $temp['category_id'] = $row['category']['id'];
                   $temp['category_name'] = $row['category']['name'];
                   array_push($output_array, $temp);

               }
               //print_r($output_array);die();
                $result_arr['dataset']= $output_array;
                //print_r($result_arr);die();
                $http_response      = 'http_response_ok';
                $success_message    = 'Single Product';  
 

            } 
           //print_r($output_array); die;
                 
          
        else 
        {
            $http_response      = 'http_response_bad_request';
            $error_message      = 'Something went wrong in API';  
        }
           //print_r($req_arr);die();
          //$http_response    = 'http_response_ok';

        }
        else
        {
                    $http_response      = 'http_response_bad_request';
        }
    }
  json_response($result_arr,$http_response,$error_message,$success_message);
}

/*public function updateProductsDetail_post(){

 $error_message = $success_message = $http_response ='';
 $result_arr = array();

if(!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals()))
  {
    $error_message = 'Invalid Token';
    $http_response = 'http_response_unauthorized';
 }else{

    $req_arr = $details_arr = array();
    $flag = true;

    if(empty($this->post('id',true))){

        $flag = false;
        $error_message = 'Product Id is required';
    }else{
        $req_arr['id'] = $this->post('id');
    }
    //pre($req_arr['id']);die();
    if(empty($this->post('name',true))){
      $flag = false;
      $error_message = "Product Name is required";

    }else{
        $req_arr['name'] = $this->post('name',true);
    }
    if(empty($this->post('cat_name',true))){

        $flag = false;
        $error_message = "Category name is required";

    }else{
        $req_arr['category_id'] = $this->post('cat_name',true);
        if($req_arr['category_id']=="1")
        {
            $req_arr['category_name']="Laptop";
        }
        else if($req_arr['category_id']=="2")
        {
            $req_arr['category_name']="Mobile";
        }
        else if($req_arr['category_id']=="3")
        {
            $req_arr['category_name']="Camera";
        }
        $req_arr["category"]=array('id'=>$req_arr['category_id'],'name'=> $req_arr['category_name']);
    }
    if(empty($this->post('price',true))){
        $flag = false;
        $error_message = "Product Price is required";
   }else{
    $req_arr['price'] = $this->post('price',true);
   }
   
   //print_r($req_arr);die();
      
    //$req_arr['address']= $this->post('address',true);
    $result_arr['id']=$req_arr['id'];

  
  
  if($flag)
  {
       $where=array("_id"=>new MongoId($req_arr['id']));
       $set=array(
            '$set'=>array("name"=>$req_arr['name'],
                  "category"=>$req_arr["category"],
                "price"=> $req_arr['price']));

       $result=$this->products->update($where,$set);
         if($result['err'])
         {
            $error_message  = 'There is some problem, please try again';
            $http_response = 'http_response_bad_request';
         }
         else
         {
            $http_response = 'http_response_ok';
            $success_message = 'Update Products successfully';
         }


  }else{
    $http_response = 'http_response_bad_request';
  }

 
 }

 json_response($result_arr, $http_response, $error_message, $success_message);

}*/


function deleteProducts_post(){

   $error_message = $success_message = $http_response = "";
   $req_arr = array();
   //echo "hii";die();
   if(!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals()))
   {
    $error_message = 'Invalid Token';
    $http_response = 'http_response_unauthorized';
   }else{
    $req_arr = $details_arr = array();
    $flag = true;

    if(empty($this->post('pass_key',true))){
        $flag = false;
        $error_message = "pass key is required";
        }
    else{
      $req_arr['pass_key'] = $this->post('pass_key',true);

     }
    if(empty($this->post('admin_user_id',true))){
        $flag = false;
        $error_message = "admin user id is required";
    }else{
        $req_arr['admin_user_id'] = $this->post('admin_user_id',true);
    }

    if(empty($this->post('productsID',true))){
        $flag = false;
        $error_message = 'product id is required';
    }else{
       $req_arr['productsID'] =  $this->post('productsID'); 
    }
    

    if($flag){

     
      $where=array("_id"=>new MongoId($req_arr['productsID']));

      $proId = $this->products->find($where);
      //echo "1".$proId->count();die();
      if(!empty($proId)){
          $this->products->remove($where);
          
           //$result_arr['dataset'] = $this->employee->getAllEmployee($req_arr);
           $result_arr = $req_arr;
           $http_response = 'http_response_ok';
           $success_message = 'Product delete successfully';
              
       
     }else{

      $http_response = 'http_response_invalid_login';
      $error_message = 'Invalid login';
     }

    }else{
        $error_message = 'http_response_unauthorized';

    }


 }

json_response($result_arr, $http_response, $error_message, $success_message);
}

/****************************end of products controlller**********************/

}
