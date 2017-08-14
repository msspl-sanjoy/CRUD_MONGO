angular
	.module('angular_mongo')
	.controller('dashboardController',["$scope", 'ajaxService', 'CONFIG', '$location', '$timeout', '$cookies', '$state', "helper", "$rootScope",'$stateParams','$window',function($scope,ajaxService,CONFIG,$location,$timeout,$cookies, $state, helper, $rootScope, $stateParams,$window){
		$scope.ChangePassword = {};
		
   $scope.doChangePassword =function(ChangePassword){
			
        var param = {};
        var admin_user_id   	= $cookies.get('admin_user_id');
		param.old_password 		= ChangePassword.old_password;
		param.new_password 		= ChangePassword.new_password;
		param.confirm_password 	= ChangePassword.confirm_password;
		param.admin_user_id 	= admin_user_id;
		param.pass_key 	= $cookies.get('pass_key');

		ajaxService.ApiCall(param,CONFIG.ApiUrl+'admin/ChangePassword',
        $scope.ChangePasswordSuccess, $scope.ChangePasswordError,'post');	
   }

		//updateDegreeDetail success function
		$scope.ChangePasswordSuccess = function(result,status) 
		{
		    if(status == 200) 
		    {
		    	$window.scrollTo(0, 100);
                $scope.successMessage = result.raws.success_message;
                $scope.clearMessage();
                $timeout(function() {
		        	$location.path('dashboard/employee/list');
		        }, CONFIG.TimeOut);
		    }
		}

		//updateDegreeDetail error function
		$scope.ChangePasswordError = function(result) 
		{
            $scope.errorMessage = result.raws.error_message;
            $scope.clearMessage();
		}

		$scope.logout=function()
		{
			//alert("logout");
			var admin_user_id   = $cookies.get('admin_user_id');
            var pass_key        = $cookies.get('pass_key');
            var param = {};
            param.admin_user_id = admin_user_id;
            param.pass_key      = pass_key;
            ajaxService.ApiCall(param,CONFIG.ApiUrl+'admin/logout',
           $scope.logoutSuccess, $scope.logoutError,'post');
		}

		$scope.logoutSuccess=function(result,status)
		{
			//alert("hello");
			if(status == 200)
			{

                // Removing a cookie
                //alert("hello");
                $cookies.remove('admin_user_id');
                $cookies.remove('pass_key');

                //console.log($cookies.getAll());

                $scope.successMessage = result.raws.success_message;
                //$scope.errorMessage = result.raws.error_message;
                //alert($scope.successMessage);
                $location.path('/home/login');
            }
		}

		$scope.logoutError=function()
		{
			$scope.errorMessage = result.raws.error_message;
            $timeout(function() {
                $scope.errorMessage = '';
                $scope.successMessage = '';
            }, CONFIG.TimeOut);
		}
		
		$scope.clearMessage = function()
		{
			$timeout(function() 
			{
        		$scope.successMessage = '';
                $scope.errorMessage = '';
            }, CONFIG.TimeOut);
		}

		
	}]);