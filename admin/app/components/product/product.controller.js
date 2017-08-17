angular
	.module('angular_mongo')
	.controller('productController', ["$scope", 'ajaxService', 'CONFIG', '$location', '$timeout', '$cookies', '$state', "helper", "$rootScope",'$window','$stateParams', function($scope, ajaxService, CONFIG, $location, $timeout, $cookies, $state, helper, $rootScope,$window,$stateParams)
	{
		//alert($state.$current.name);

		$scope.productsData 		= {};
	    $scope.pageno 				= 1; // initialize page no to 1
	    $scope.itemsPerPage 		= CONFIG.itemsPerPage; 
	    $scope.order_by 			= 'id';
	    $scope.order 				= 'desc';
	    $scope.searchByName 		= '';
	    $scope.productsDetail 		= {};
		$scope.productsID 			= $stateParams.productsID;
		$scope.successMessage 		= '';
	    $scope.errorMessage 		= '';	
		
		// Perform to getAllDegree action
		$scope.getAllProducts = function(pageno, order_by, order)
		{ 
	        //alert($scope.searchByName);
	        //alert("hii");
	        $scope.pageno 	= pageno ? pageno : 1;
	       	$scope.order_by = order_by ? order_by : 'id';
	        $scope.order 	= order ? order : 'desc';

	        var getproductsParam = 
	        {
	        	
	            'pass_key' 			: $cookies.get('pass_key'),
	        	'admin_user_id'		: $cookies.get('admin_user_id'),
	            'searchByName'		: $scope.searchByName,
	            'order_by'			: $scope.order_by,
	            'order'				: $scope.order,
	            'page'				: $scope.pageno,
	            'page_size'			: $scope.itemsPerPage
	        };

	        //alert(getproductsParam.pass_key+',,,'+getproductsParam.admin_user_id);

			ajaxService.ApiCall(getproductsParam, CONFIG.ApiUrl+'products/getAllProducts', $scope.getAllProductsSuccess, $scope.getAllProductsError, 'post');
		}

		//getAllDegree success function
		$scope.getAllProductsSuccess = function(result,status) 
		{
			
		    if(status == 200) 
		    {
                
		    	//alert("hii");
                $scope.allProducts	= result.raws.data.dataset;
                //alert($scope.allProducts.count);
                //alert($scope.allProducts.product_name);
                $scope.total_count 	= result.raws.data.count;
               // $scope.imgsrc='/var/www/html/angular/assets/resources/product/thumb/'+$scope.allProducts[0].id+'.'+$scope.allProducts[0].product_image_extension;      
		    }		       
		}

		//getAllDegree error function
		$scope.getAllProductsError = function(result) 
		{
            if(status == 403)
            {
                helper.unAuthenticate();
            }
            else
            {
                $scope.errorMessage = result.raws.error_message;
                $scope.clearMessage();
            }
		}
		//$scope.getAllProducts();


		/****************Search START******************/
		$scope.$watch('searchByName', function(tmpStr) 
		{
		    if (angular.isUndefined(tmpStr))
		    {		    	
		        return 0;
		    }
		    else if(tmpStr=='')
		    {
				$scope.getAllProducts($scope.pageno, $scope.order_by, $scope.order, $scope.searchByName);
		    }
		    else
		    {
		    	$timeout(function() 
		    	{ 
			        if (tmpStr === $scope.searchByName) 
			        {
						
						$scope.getAllProducts($scope.pageno, $scope.order_by, $scope.order, $scope.searchByName);
			        }
			    }, 1000);	
		    }		    
		});
		/**************** Search END ******************/

		$scope.uploadImg = function (element) {
			$scope.product_img = element.files[0];
		}

		$scope.doaddproducts = function(productsData) 
		{ 
			var productsParam=productsData;
			productsData.pass_key=$cookies.get('pass_key');
			productsData.admin_user_id=$cookies.get('admin_user_id');
			ajaxService.ApiCall(productsParam, CONFIG.ApiUrl+'products/addProducts', $scope.addProductsSuccess, $scope.addProductsError, 'post');
		}

		//addDegree success function
		$scope.addProductsSuccess = function(result,status) 
		{

		    if(status == 200) 
		    {   $window.scrollTo(0, 100);
		    	$scope.successMessage = result.raws.success_message;
		    	$scope.clearMessage();
		    	$timeout(function() {
		        	$location.path('dashboard/product/list');
		        }, CONFIG.TimeOut);
		    }		       
		}

		//addDegree error function
		$scope.addProductsError = function(result) 
		{
			window.scrollTo(0, 100);
            $scope.errorMessage = result.raws.error_message;
            $scope.clearMessage();
		}
		$scope.deleteProduct = function(productsId,index)
		{
			
		    //alert(productsId);
		    $scope.productsIndex = index;
			var productsParam = {
				'pass_key' 			: $cookies.get('pass_key'),
	        	'admin_user_id'		: $cookies.get('admin_user_id'),
				'productsID' 	    : productsId
			    
			};
			ajaxService.ApiCall(productsParam, CONFIG.ApiUrl+'products/deleteProducts', $scope.deleteProductsSuccess, $scope.deleteProductsError, 'post');
		}

		$scope.deleteProductsSuccess = function(result, status)
		{
			if(status == 200)
			{   
				$scope.getAllProducts($scope.pageno, $scope.order_by, $scope.order, $scope.searchByName);
				//alert($scope.employeeIndex);
				$scope.successMessage = result.raws.success_message;
				//alert($scope.successMessage);
				$scope.clearMessage();
				$scope.getAllProducts.splice($scope.productsIndex,1);
				//window.location.reload();
				
			}
		}

		$scope.deleteProductsError = function(result, status)
		{
			if(status == 403)
            {
                helper.unAuthenticate();
            } 
            else 
            {
	            $scope.errorMessage = result.raws.error_message;
	            $scope.clearMessage(); 
	        }
		}

		$scope.clearMessage = function()
		{
			$timeout(function() {
        		$scope.successMessage = '';
                $scope.errorMessage = '';
            }, CONFIG.TimeOut);
		}

	
 // Perform to getDegreeDetail action
		$scope.getProductsDetail = function()
		{ 
			//alert("here");
			//alert($stateParams.productsID);
			//var productsID    = $stateParams.productsID ;
			var productsParam = {
				'pass_key' 			: $cookies.get('pass_key'),
	        	'admin_user_id'		: $cookies.get('admin_user_id'),
				'productsID'        : $scope.productsID};
			//	alert(productsParam.productsID);
			ajaxService.ApiCall(productsParam, CONFIG.ApiUrl+'products/getProductsDetail', $scope.getProductsDetailSuccess, $scope.getProductsDetailError, 'post');
		}

 //getDegreeDetail success function
		$scope.getProductsDetailSuccess = function(result,status) 
		{
		    
			//alert(result.raws.data.dataset.name);
			//alert("1"+result.raws.data.dataset.product_name);
		    if(status == 200) 
		    {
		    	//alert("1"+result.raws.data.dataset[0].name);
                //$scope.productsDetail = result.raws.data.dataset;
                //alert("hii");
                $scope.productsDetail.id=result.raws.data.dataset[0].productsId;
                $scope.productsDetail.name = result.raws.data.dataset[0].name;
                $scope.productsDetail.product_code = result.raws.data.dataset[0].product_code;
                $scope.productsDetail.price = result.raws.data.dataset[0].price;
                $scope.productsDetail.cat_id = result.raws.data.dataset[0].category_id;
                //$scope.productsDetail.product_img_url = result.raws.data.dataset.product_img_url;
                $scope.productsDetail.cat_name = result.raws.data.dataset[0].category_id.toString();
                
		    }
		}

		//getDegreeDetail error function
		$scope.getProductsDetailError = function(result) 
		{
            $scope.errorMessage = result.raws.error_message;
            $scope.clearMessage();
		}

		if($state.$current.name == 'product.update-product')
		{
			$scope.getProductsDetail();
		}

        $scope.updateProductsDetail= function(productsDetail){
        	//alert(productsDetail.id);
        productsDetail.pass_key=$cookies.get('pass_key');
		productsDetail.admin_user_id=$cookies.get('admin_user_id');
 		ajaxService.ApiCall(productsDetail, CONFIG.ApiUrl+'products/updateProductsDetail',
 		$scope.updateProductsDetailSuccess,$scope.updateProductsDetailError, 'post');
        }

//updateDegreeDetail success function
		$scope.updateProductsDetailSuccess = function(result,status) 
		{
		    if(status == 200) 
		    {
		    	$window.scrollTo(0, 100);
                $scope.successMessage = result.raws.success_message;
                $scope.clearMessage();
                $timeout(function() {
		        $location.path('dashboard/product/list');
		        }, CONFIG.TimeOut);
		    }
		}

		//updateDegreeDetail error function
		$scope.updateProductsDetailError = function(result) 
		{
			window.scrollTo(0, 100);
            $scope.errorMessage = result.raws.error_message;
            $scope.clearMessage();
		}
		
		$scope.clearMessage = function()
		{
			$timeout(function() 
			{
        		$scope.successMessage = '';
                $scope.errorMessage = '';
            }, CONFIG.TimeOut);
		}
	}])
