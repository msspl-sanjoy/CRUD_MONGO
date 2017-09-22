<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
* --------------------------------------------------------------------------
* @ Controller Name          : Ed
* @ Added Date               : 20-09-2017
* @ Added By                 : Sanjoy
* -----------------------------------------------------------------
* @ Description              : All the Ed related api call from ed controller
* -----------------------------------------------------------------
* @ return                   : array
* -----------------------------------------------------------------
* @ Modified Date            : 
* @ Modified By              : 
* 
*/

//All the required library file for API has been included here 
/*require APPPATH . 'libraries/api/AppExtrasAPI.php';
require APPPATH . 'libraries/api/AppAndroidGCMPushAPI.php';
require APPPATH . 'libraries/api/AppApplePushAPI.php';*/

require_once('src/OAuth2/Autoloader.php');
require APPPATH . 'libraries/api/REST_Controller.php';


class Ed extends REST_Controller{
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
        $this->user    = $this->mongodb->user;
        $this->ed      = $this->mongodb->ed;


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

    function getAllEd_post(){
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
           $details_arr                 = $this->ed->find($like);

           

            if(!empty($details_arr) && count($details_arr)>0)
               {
                  foreach($details_arr as $row)
                   {
                       $temp['edId']       = $row['_id']->{'$id'};
                       $temp['ed_name']       = $row['name'];
                       $temp['ed_address']    = $row['address'];
                       $where=array("ed_id"=>new MongoId($temp['edId']));
                       $user_detail = $this->user->find($where);
                       //pre($user_detail,1);
                       foreach ($user_detail as $user) {

                           $temp['username']            = $user['username'];
                           $temp['user_email_id']       = $user['email_id'];
                           $temp['user_contact_no']     = $user['contact_no'];
                           array_push($output_array, $temp);
                       }

                   }
                    $result_arr['dataset']= $output_array;
                    $http_response      = 'http_response_ok';
                    $success_message    = 'All Ed';  
               } 
                 
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

    function addEd_post(){
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
              $error_message = "Ed Name is required";

            }else{
                $req_arr['ed_name'] = $this->post('name',true);
            }
            
            if(empty($this->post('username',true))){
              $flag = false;
              $error_message = "Username is required";

            }else{
                $req_arr['username'] = $this->post('username',true);
            }
            
            if(empty($this->post('password',true))){
              $flag = false;
              $error_message = "Password is required";

            }else{
                $req_arr['password'] = base64_encode($this->post('password',true));
            }
            
            if(empty($this->post('email',true))){
              $flag = false;
              $error_message = "Email Id is required";

            }else{
                $req_arr['email'] = $this->post('email',true);
            }

            if(empty($this->post('number',true))){
              $flag = false;
              $error_message = "Phone Number is required";

            }else{
                $req_arr['number'] = $this->post('number',true);
            }

            if(empty($this->post('address',true))){
              $flag = false;
              $error_message = "Ed Address is required";

            }else{
                $req_arr['address'] = $this->post('address',true);
            }
            //pre($req_arr,1);
            $ed_id= $this->post('id',true);
            if($flag)
            {
                if(!empty($ed_id))
                {
                   //echo "update";die();
                   $where=array("_id"=>new MongoId($ed_id));
                   $set=array(
                        '$set'=>array("name"=>$req_arr['ed_name'],
                              "address"=>$req_arr['address']));

                   $result=$this->ed->update($where,$set);
                     if($result['err'])
                     {
                        $error_message  = 'Ed is not updated, please try again';
                        $http_response = 'http_response_bad_request';
                     }
                     else
                     {
                        
                        $where_user=array("ed_id"=>new MongoId($ed_id));
                        $set=array(
                        '$set'=>array("email_id"=>$req_arr['email'],
                              "contact_no"=>$req_arr['number']));
                        //pre($set,1);
                        $result_user=$this->user->update($where_user,$set);
                        if($result_user['err'])
                        {
                            $error_message  = 'User is not updated, please try again';
                            $http_response = 'http_response_bad_request';
                        }
                        else
                        {
                          $http_response = 'http_response_ok';
                          $success_message = 'Updated Ed successfully';  
                        }
                        
                     }
                }

                else
                {
                  $ed_insert_array=array('_id'=>new MongoId(),
                                      "name"=>$req_arr['ed_name'],
                                      "address"=>$req_arr['address'],
                                      'created_at'=>new MongoDate()); 

                  $result=$this->ed->insert($ed_insert_array); 
                  $last_id= $ed_insert_array['_id'];
                   if($result['err'])
                   {
                      $error_message  = 'Ed is not added, please try again';
                      $http_response = 'http_response_bad_request';
                   }
                   else
                   {
                      $user_insert_array= array('_id'=>new MongoId(),
                                      "ed_id"=>$last_id,
                                      "username"=>$req_arr['username'],
                                      "password"=>$req_arr['password'],
                                      "email_id"=>$req_arr['email'],
                                      "contact_no"=>$req_arr['number'],
                                      'created_at'=>new MongoDate()); 

                      $result_user=$this->user->insert($user_insert_array);
                      if($result_user['err'])
                       {
                          $error_message  = 'User is not added, please try again';
                          $http_response = 'http_response_bad_request';
                       }
                      else
                      {
                            $http_response = 'http_response_ok';
                            $success_message = 'Ed Added successfully';
                      }
                      
                   }
                }

            }
           
            else
            {
                $http_response = 'http_response_bad_request';
            }

        }

        json_response($result_arr, $http_response, $error_message, $success_message);
    }

    function getEdDetail_post(){

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

            if(empty($this->post('edID')))
            {
                      $flag = false;
                      $error_message = "Ed Id is required";
            }else{
              $req_arr['edID'] = $this->post('edID');
            }

            //pre($req_arr,1);

           if($flag){

              $where=array("_id"=>new MongoId($req_arr['edID']));
              //print_r($where);die();
              $ed_detail = $this->ed->find($where);
              //pre($ed_detail);die();

              if(!empty($ed_detail) && count($ed_detail)>0)
               {
                  foreach($ed_detail as $row)
                   {
                       $temp['edId']       = $row['_id']->{'$id'};
                       $temp['ed_name']       = $row['name'];
                       $temp['ed_address']    = $row['address'];
                       $where=array("ed_id"=>new MongoId($temp['edId']));
                       $user_detail = $this->user->find($where);
                       //pre($user_detail,1);
                       foreach ($user_detail as $user) {

                           $temp['username']            = $user['username'];
                           $temp['password']            = $user['password'];
                           $temp['user_email_id']       = $user['email_id'];
                           $temp['user_contact_no']     = $user['contact_no'];
                           array_push($output_array, $temp);
                       }

                   }
                   //print_r($output_array);die();
                    $result_arr['dataset']= $output_array;
                    //print_r($result_arr);die();
                    $http_response      = 'http_response_ok';
                    $success_message    = 'Single Product';  
     

                } 
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
        json_response($result_arr,$http_response,$error_message,$success_message);
    }

    function deleteEd_post(){

       $error_message = $success_message = $http_response = "";
       $req_arr = array();
       //echo "hii";die();
       if(!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals()))
       {
            $error_message = 'Invalid Token';
            $http_response = 'http_response_unauthorized';
       }
       else
       {
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

            if(empty($this->post('edID',true))){
                $flag = false;
                $error_message = 'product id is required';
            }else{
               $req_arr['edID'] =  $this->post('edID'); 
            }
    

            if($flag)
            {

             
              $where=array("_id"=>new MongoId($req_arr['edID']));

              $edId = $this->ed->find($where);
              //echo "1".$proId->count();die();
              if(!empty($edId))
              {
                  $this->ed->remove($where);
                  $where_user=array("ed_id"=>new MongoId($req_arr['edID']));
                  $this->user->remove($where_user);
                  $result_arr = $req_arr;
                  $http_response = 'http_response_ok';
                  $success_message = 'Ed deleted successfully';     
               
              }
              else
              {
                  $http_response = 'http_response_invalid_login';
                  $error_message = 'Invalid login';
              }

            }
            else
            {
                $error_message = 'http_response_unauthorized';
            }

        }
        json_response($result_arr, $http_response, $error_message, $success_message);
    }
   
    function duplicateChecking_post()
    {
       $error_message = $success_message = $http_response = "";
       $req_arr = array();
       //echo "hii";die();
       //echo $this->post('username');die();
       if(!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals()))
       {
            $error_message = 'Invalid Token';
            $http_response = 'http_response_unauthorized';
       }
       else
       {
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
            if(empty($this->post('username',true))){
                $flag = false;
                $error_message = "Username is required";
            }else{
                $req_arr['username'] = $this->post('username',true);
            }

            if($flag)
            {

             
              $where=array("username"=>$req_arr['username']);

              $username = $this->user->find($where);

              $count=$username->count();
              
              if($count>0)
              {
                  $result_arr = $req_arr;
                  $http_response = 'http_response_bad_request';
                  $error_message = 'Username already exits';   
               
              }
              else
              {
                  $result_arr = $req_arr; 
                  $http_response = 'http_response_ok';
              }

            }
            else
            {
                $error_message = 'http_response_unauthorized';
            }
            //pre($req_arr);
        }
        json_response($result_arr, $http_response, $error_message, $success_message);
    }
}