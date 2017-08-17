/**
 * Load states for application
 * more info on UI-Router states can be found at
 * https://github.com/angular-ui/ui-router/wiki
 */

angular
    .module('angular_mongo')
    .run(function($rootScope, CONFIG, $state, helper, $confirmModalDefaults, $location){
      
        $rootScope.CONFIG = CONFIG;
        $rootScope.bodyClass = 'admin-body';
        //$rootScope.bodyClass      = ''; 
        $rootScope.carousel         = '';
        $rootScope.parentBreadcrumb = '';
        $rootScope.breadcrumb       = '';
        $rootScope.leftMenu         = '';

        
        //state change event called here           
        $rootScope.$on('$stateChangeStart', 
        function(event, toState, toParams, fromState, fromParams){ 

            //console.log(fromState);                           
            //console.log(toState);    

            if(fromState.parent == 'home' && toState.parent == 'home'){
                //$rootScope.bodyClass = 'admin-body';
            } else {
                if(toState.parent == 'home' && toState.url == '/login'){
                    $rootScope.bodyClass = 'admin-body';
                    helper.checkUserAuthentication('home');
                }else{
                    $rootScope.bodyClass = 'agent-body';
                    helper.checkUserAuthentication();
                }
            }
        })

        //get current timestamp
        function getTimeStamp(){
            var d = new Date();
            var currentTime = d.getTime();
            $rootScope.currentTime = currentTime;
        }
        getTimeStamp();

        $confirmModalDefaults.defaultLabels.title = 'angular practice';
    })
    .config(['$stateProvider', '$urlRouterProvider', '$locationProvider', function($stateProvider, $urlRouterProvider, $locationProvider){
    // any unknown URLS go to 404
    $urlRouterProvider.otherwise('/404');
    // no route goes to index
    $urlRouterProvider.when('', '/home/login');
    //$urlRouterProvider.when('#', '/home/login');
    //$urlRouterProvider.when('#/home', '/home/login');
    // use a state provider for routing
    //console.log($stateParams);
    $stateProvider
        
        //404 page not found section
        .state('404', {
            url: '/404',
            views: {
                "main": {
                  templateUrl: 'app/shared/404.html'
                },
            }
        })


        //Home module section
        .state('home', {
            url: '/home',
            views: {
                "main": {
                  controller: 'homeController as ctrl',
                  templateUrl: 'app/components/home/views/home.view.html'
                },
            }
        })

        .state('login', {
            parent: 'home',
            //url: '/home/login',
            url: '/login',
            templateUrl: 'app/components/home/views/login.view.html'
        })    

        //Dashboard module section
        .state('dashboard', {
            // we'll add another state soon
            url: '/dashboard',
            views: {
                "main": {
                  controller: 'dashboardController as ctrl',
                  templateUrl: 'app/components/dashboard/views/dashboard.index.view.html'
                },
            }

        })

        .state('welcome', {
            parent: 'dashboard',
            //url: '/dashboard/welcome',
            url: '/welcome',
            templateUrl: 'app/components/dashboard/views/dashboard.welcome.view.html',
            onEnter: function($state,$rootScope){ // define value and load the default variable in our page
              $rootScope.parentBreadcrumb = 'Dashboard';
              $rootScope.breadcrumb       = 'Welcome';
              $rootScope.carousel         = '';
              $rootScope.leftMenu         = '';
            }
        })

        .state("product",{
          parent:'dashboard',
          url:'/product',
          templateUrl:'app/components/product/views/product.index.view.html'

         })

        .state("product.list",{
              url: '/list',
              templateUrl:'app/components/product/views/product.list.html',
              controller:'productController',
              onEnter: function($state,$rootScope){ // define value and load the default variable in our page
              $rootScope.parentBreadcrumb = 'Dashboard';
              $rootScope.breadcrumb       = 'Product list';
              $rootScope.carousel         = '';
              //$rootScope.leftMenu         = 'list'; 
            }
  

          })
        .state("product.add-product",{
              url:'/add-product',
              templateUrl:'app/components/product/views/product.add.html',
              controller: 'productController',
              onEnter: function($state,$rootScope){ // define value and load the default variable in our page
                $rootScope.parentBreadcrumb = 'Dashboard';
                $rootScope.breadcrumb       = 'Product';
                $rootScope.carousel         = 'Add Product';
                //$rootScope.leftMenu         = 'add'; 
              }
        })

        .state("product.update-product",{
          url:'/update-product/:productsID',
          templateUrl:'app/components/product/views/update.product.html',
          controller: 'productController',
          onEnter: function($state,$rootScope){ // define value and load the default variable in our page
            $rootScope.parentBreadcrumb = 'Dashboard';
            $rootScope.breadcrumb       = 'Product';
            $rootScope.carousel         = 'Update product';
            
            //$rootScope.leftMenu         = 'edit'; 
            }
          
        })
        
}])

