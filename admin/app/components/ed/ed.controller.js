angular
	.module('angular_mongo')
	.controller('edController', ["$scope", 'ajaxService', 'CONFIG', '$location', '$timeout', '$cookies', '$state', "helper", "$rootScope",'$window','$stateParams', function($scope, ajaxService, CONFIG, $location, $timeout, $cookies, $state, helper, $rootScope,$window,$stateParams)
	{
		//alert($state.$current.name);

		$scope.edData 		        = {};
	    $scope.pageno 				= 1; // initialize page no to 1
	    $scope.itemsPerPage 		= CONFIG.itemsPerPage; 
	    $scope.order_by 			= 'id';
	    $scope.order 				= 'desc';
	    $scope.searchByName 		= '';
	    $scope.edDetail 		    = {};
		$scope.edID 			    = $stateParams.edID;
		$scope.successMessage 		= '';
	    $scope.errorMessage 		= '';	
		
		// Perform to getAllDegree action
		$scope.getAllEd = function(pageno, order_by, order)
		{ 
	        //alert($scope.searchByName);
	        //alert("hii");
	        $scope.pageno 	= pageno ? pageno : 1;
	       	$scope.order_by = order_by ? order_by : 'id';
	        $scope.order 	= order ? order : 'desc';

	        var getedParam = 
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

			ajaxService.ApiCall(getedParam, CONFIG.ApiUrl+'ed/getAllEd', $scope.getAllEdSuccess, $scope.getAllEdError, 'post');
		}

		//getAllDegree success function
		$scope.getAllEdSuccess = function(result,status) 
		{
			
		    if(status == 200) 
		    {
                
		    	//alert("hii");
                $scope.allEd	= result.raws.data.dataset;
                //alert($scope.allProducts.count);
                //alert($scope.allProducts.product_name);
                $scope.total_count 	= result.raws.data.count;
               // $scope.imgsrc='/var/www/html/angular/assets/resources/product/thumb/'+$scope.allProducts[0].id+'.'+$scope.allProducts[0].product_image_extension;      
		    }		       
		}

		//getAllDegree error function
		$scope.getAllEdError = function(result) 
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
				$scope.getAllEd($scope.pageno, $scope.order_by, $scope.order, $scope.searchByName);
		    }
		    else
		    {
		    	$timeout(function() 
		    	{ 
			        if (tmpStr === $scope.searchByName) 
			        {
						
						$scope.getAllEd($scope.pageno, $scope.order_by, $scope.order, $scope.searchByName);
			        }
			    }, 1000);	
		    }		    
		});
		/**************** Search END ******************/

		$scope.uploadImg = function (element) {
			$scope.ed_img = element.files[0];
		}

		$scope.doadded = function(edData) 
		{ 
			console.log(edData);
			var edParam=edData;
			edData.pass_key=$cookies.get('pass_key');
			edData.admin_user_id=$cookies.get('admin_user_id');
			ajaxService.ApiCall(edParam, CONFIG.ApiUrl+'ed/addEd', $scope.addEdSuccess, $scope.addEdError, 'post');
		}

		//addDegree success function
		$scope.addEdSuccess = function(result,status) 
		{

		    if(status == 200) 
		    {   $window.scrollTo(0, 100);
		    	$scope.successMessage = result.raws.success_message;
		    	$scope.clearMessage();
		    	$timeout(function() {
		        	$location.path('dashboard/ed/list');
		        }, CONFIG.TimeOut);
		    }		       
		}

		//addDegree error function
		$scope.addEdError = function(result) 
		{
			window.scrollTo(0, 100);
            $scope.errorMessage = result.raws.error_message;
            $scope.clearMessage();
		}
		$scope.deleteEd = function(edId,index)
		{
			
		    //alert(productsId);
		    $scope.edIndex = index;
			var edParam = {
				'pass_key' 			: $cookies.get('pass_key'),
	        	'admin_user_id'		: $cookies.get('admin_user_id'),
				'edID' 	            : edId
			    
			};
			ajaxService.ApiCall(edParam, CONFIG.ApiUrl+'ed/deleteEd', $scope.deleteEdSuccess, $scope.deleteEdError, 'post');
		}

		$scope.deleteEdSuccess = function(result, status)
		{
			if(status == 200)
			{   
				$scope.getAllEd($scope.pageno, $scope.order_by, $scope.order, $scope.searchByName);
				//alert($scope.employeeIndex);
				$scope.successMessage = result.raws.success_message;
				//alert($scope.successMessage);
				$scope.clearMessage();
				$scope.getAllEd.splice($scope.edIndex,1);
				//window.location.reload();
				
			}
		}

		$scope.deleteEdError = function(result, status)
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
		$scope.getEdDetail = function()
		{ 
			//alert("here");
			//alert($stateParams.edID);
			//var productsID    = $stateParams.productsID ;
			var edParam = {
				'pass_key' 			: $cookies.get('pass_key'),
	        	'admin_user_id'		: $cookies.get('admin_user_id'),
				'edID'              : $scope.edID};
			//	alert(productsParam.productsID);
			ajaxService.ApiCall(edParam, CONFIG.ApiUrl+'ed/getEdDetail', $scope.getEdDetailSuccess, $scope.getPEdDetailError, 'post');
		}

 //getDegreeDetail success function
		$scope.getEdDetailSuccess = function(result,status) 
		{
		    
			//alert(result.raws.data.dataset.name);
			//alert("1"+result.raws.data.dataset.product_name);
		    if(status == 200) 
		    {
		    	//alert("1"+result.raws.data.dataset[0].name);
                //$scope.productsDetail = result.raws.data.dataset;
                //alert("hii");
                $scope.edDetail.id=result.raws.data.dataset[0].edId;
                $scope.edDetail.name = result.raws.data.dataset[0].ed_name;
                $scope.edDetail.address = result.raws.data.dataset[0].ed_address;
                $scope.edDetail.number = result.raws.data.dataset[0].user_contact_no;
                $scope.edDetail.email = result.raws.data.dataset[0].user_email_id;
                //$scope.productsDetail.product_img_url = result.raws.data.dataset.product_img_url;
                $scope.edDetail.username = result.raws.data.dataset[0].username;
                $scope.edDetail.password = result.raws.data.dataset[0].password;
		    }
		}

		//getDegreeDetail error function
		$scope.getEdDetailError = function(result) 
		{
            $scope.errorMessage = result.raws.error_message;
            $scope.clearMessage();
		}

		if($state.$current.name == 'ed.update-ed')
		{
			$scope.getEdDetail();
		}

        $scope.updateEdDetail= function(edDetail){
        	//alert(productsDetail.id);
        edDetail.pass_key=$cookies.get('pass_key');
		edDetail.admin_user_id=$cookies.get('admin_user_id');
 		ajaxService.ApiCall(edDetail, CONFIG.ApiUrl+'ed/updateEdDetail',
 		$scope.updateEdDetailSuccess,$scope.updateEdDetailError, 'post');
        }

//updateDegreeDetail success function
		$scope.updateEdDetailSuccess = function(result,status) 
		{
		    if(status == 200) 
		    {
		    	$window.scrollTo(0, 100);
                $scope.successMessage = result.raws.success_message;
                $scope.clearMessage();
                $timeout(function() {
		        $location.path('dashboard/ed/list');
		        }, CONFIG.TimeOut);
		    }
		}

		//updateDegreeDetail error function
		$scope.updateEdDetailError = function(result) 
		{
			window.scrollTo(0, 100);
            $scope.errorMessage = result.raws.error_message;
            $scope.clearMessage();
		}
		
		$scope.duplicateChecking = function(username) {

			var Param = {
				'pass_key' 			: $cookies.get('pass_key'),
	        	'admin_user_id'		: $cookies.get('admin_user_id'),
				'username'          : 	username};
			//console.log(edParam);
			ajaxService.ApiCall(Param, CONFIG.ApiUrl+'ed/duplicateChecking',
 		    $scope.duplicateCheckingSuccess,$scope.duplicateCheckingError, 'post');
        	
    	}

    	$scope.duplicateCheckingSuccess = function(result,status) 
		{
		    /*alert("hello");*/
		    if(status == 200) 
		    {
		    	$window.scrollTo(0, 100);
                $scope.clearMessage();
                /*$timeout(function() {
		        $location.path('dashboard/ed/list');
		        }, CONFIG.TimeOut);*/
		    }
		}

		$scope.duplicateCheckingError = function(result) 
		{
			//alert(result.raws.error_message);
			window.scrollTo(0, 100);
            $scope.errorMessage = result.raws.error_message;
            //$scope.clearMessage();
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
