angular
	.module('angular_mongo')
	.controller('homeController', ["$scope", "$http", "$window", "$q", 'ajaxService', 'CONFIG', '$location', '$timeout', '$cookies', 'helper', function($scope, $http, $window, $q, ajaxService, CONFIG, $location, $timeout, $cookies, helper){
     //alert(CONFIG.ApiUrl);
    	//$scope.admin_user_id 	= 0;
    	//$scope.loginData 		= {};

		// Perform the login action when the user submits the login form
		$scope.doLogin = function(loginData) { 
			//alert(CONFIG.ApiUrl);
			//alert(loginData) ; 
			ajaxService.ApiCall(loginData, CONFIG.ApiUrl+'admin/logIn', $scope.loginUserSuccess, $scope.loginUserError, 'post');
		}
		$scope.loginUserSuccess = function(result,status) {
			//alert(result+status);
		    if(status == 200) {
		    	// Setting a cookie
		    	//alert("hii");
		    	/*$cookies.put('admin_user_id', result.raws.data.admin_user_id,{'path': '/'});
		    	$cookies.put('pass_key', result.raws.data.pass_key,{'path': '/'});*/
		        $location.path('dashboard/welcome');
		    }		       
		}
		//login error function
		$scope.loginUserError = function(result) {
            $scope.error_message = result.raws.error_message;
           // alert($scope.errorMessage);
            $timeout(function() {
        		$scope.successMessage = '';
                $scope.errorMessage = '';
            }, CONFIG.TimeOut);
		}
	}]);