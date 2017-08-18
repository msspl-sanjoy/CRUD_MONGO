<?php
defined('BASEPATH') OR exit('No direct script access allowed');


require_once('src/OAuth2/Autoloader.php');
require APPPATH . 'libraries/api/REST_Controller.php';


class Products_api extends REST_Controller{
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
        //$this->tables = $this->config->item('tables'); 
        //$this->load->model('api_' . $this->config->item('test_api_ver') . '/admin/products_model', 'products');
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


        //$this->push_type = 'P';
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

    /****************************API for MongoDb Testing**********************/

	public function viewdetails_post()
	{
	  
	  $error_message = $success_message = $http_response = "";
	  $output_array=array();
	  if (!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) 
	  {
	      $error_message = 'Invalid Token';
	      $http_response = 'http_response_unauthorized';
	  }
	  else
	  {
	      $result=$this->products->find();
	      if(!empty($result) && count($result)>0)
	      {
	        foreach ($result as  $row)
	        {
	            $temp['product_id']                   =$row['_id']->{'$id'};
	            $temp['products_name']                =$row['name'];
	            $temp['products_category_id']         =$row['category']['id'];
	            $temp['products_category_name']       =$row['category']['name'];
	            $temp['products_code']                =$row['product_code'];
	            $temp['products_price']               =$row['price'];
	            array_push($output_array,$temp);
	        }
	        if(!empty($result_arr) && count($result_arr)>0) 
	        {
		        $result_arr = $output_array;
		        $http_response = 'http_response_ok';
		        $success_message = 'All Products fetch successfully';
		    }
		    else
		    {
		    	$http_response = 'http_response_ok';
		        $error_message = 'No record found';
		    }

	      }
	      else
	      {
	        $http_response      = 'http_response_bad_request';
	        $error_message      = 'Something went wrong in API';  
	      }
	   }

	  json_response($result_arr,$http_response,$error_message,$http_response);

	}

	public function viewdetails_by_key_post()
	{
	  
	  $error_message = $success_message = $http_response = "";
	  $output_array=array();
	  if (!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) 
	  {
	      $error_message = 'Invalid Token';
	      $http_response = 'http_response_unauthorized';
	  }
	  else
	  {
	      if(empty($this->post('category_name',true)))
	      {
	        $flag = false;
	        $error_message = "Category name is required";
          }
          else
          {
          	$products_category_name=$this->post('category_name',true);
          }

          if($flag)
      	  {
	      
		      $where=array('category.name'=>$products_category_name);
		      //print_r($where);die();
		      $result=$this->products->find($where);
		     
		      if(!empty($result) && count($result)>0)
		      {
		        foreach ($result as  $row)
		        {
		            //print_r($row);die();
		            $temp['product_id']                   =$row['_id']->{'$id'};
		            $temp['products_name']                =$row['name'];
		            $temp['products_category_id']         =$row['category']['id'];
		            $temp['products_category_name']       =$row['category']['name'];
		            $temp['products_code']                =$row['product_code'];
		            $temp['products_price']               =$row['price'];
		            array_push($output_array,$temp);
		            	
		        }

		        $result_arr = $output_array;
		        if(!empty($result_arr) && count($result_arr)>0) 
		        {
			        $http_response = 'http_response_ok';
			        $success_message = 'All Related Products fetch successfully';
			    }
			    else
			    {
			    	$http_response = 'http_response_ok';
			        $error_message = 'No record found';
			    }
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
		        //$error_message      = 'Something went wrong in API';
		   }
	   }

	  json_response($result_arr,$http_response,$error_message,$http_response);
	

	}

	public function group_by_post()
	{
	  
	  $error_message = $success_message = $http_response = "";
	  $output_array=array();
	  $flag=true;
	  if (!$this->oauth_server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) 
	  {
	      $error_message = 'Invalid Token';
	      $http_response = 'http_response_unauthorized';
	  }
	  else
	  {
	      /*if(empty($this->post('category_name',true)))
	      {
	        $flag = false;
	        $error_message = "Category name is required";
          }
          else
          {
          	$products_category_name=$this->post('category_name',true);
          }*/
          if(empty($this->post('category_name',true)))
	      {
	        $flag = false;
	        $error_message = "Category name is  required";
          }
          else
          {
          	$products_category_name=$this->post('category_name',true);
          }
	      if($flag)
	      {
		      $aggregate_ops=array(   
		      	                   
		      	                   array(       
                                       '$match' => array(            
                                              'category.name' => $products_category_name                          
                                                        )   
                                      ),
	                           	                                   
                                     array(
                                     '$group'=>array(
                                              '_id'=>'$category.name',
                                              'number'=>array(
                                                           '$sum'=>'$price'
                                                              )
                                                    )
                                     )
	                                );
		      //print_r($aggregate_ops);die();
		      $result=$this->products->aggregate($aggregate_ops);
			     
		      if(!empty($result) && count($result)>0)
		      {
		        foreach ($result as  $row)
		        {
		            //print_r($row);die();
/*		            $temp['product_id']                   =$row['_id']->{'$id'};
		            $temp['products_name']                =$row['name'];
		            $temp['products_category_id']         =$row['category']['id'];
		            $temp['products_category_name']       =$row['category']['name'];
		            $temp['products_code']                =$row['product_code'];
		            $temp['products_price']               =$row['price'];*/
		            array_push($output_array,$row);
		            	
		        }

		        $result_arr = $output_array;
		        if(!empty($result_arr) && count($result_arr)>0) 
		        {
			        $http_response = 'http_response_ok';
			        $success_message = 'All Related Products fetch successfully';
			    }
			    else
			    {
			    	$http_response = 'http_response_ok';
			        $error_message = 'No record found';
			    }
		      }
		      else
		      {
		        $http_response      = 'http_response_bad_request';
		        $error_message      = 'Something went wrong in API';  
		      }
		  }
		  else
		  {
		  	//echo "hii";die();
		  	$http_response      = 'http_response_bad_request';
		  }
	   }

	  json_response($result_arr,$http_response,$error_message,$success_message);
	

	}
}