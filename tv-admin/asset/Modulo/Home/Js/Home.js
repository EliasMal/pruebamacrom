var url = "./Modulo/Home/Ajax/Home.php";

tsuruVolks
        .controller('HomeCtrl', ["$scope","$http",HomeCtrl]);

function HomeCtrl($scope, $http){
     var obj = $scope;
    
     obj.datos;
     obj.data= {
          opc:""
     }

     obj.getData = ()=>{
          obj.data.opc= "get";
          obj.sendData();
     }

     obj.sendData = ()=>{
          $http({
               method: 'POST',
                   url: url,
                   data: {home:obj.data}
               }).then(function successCallback(res){
                   if(res.data.Bandera == 1){
                       obj.datos = res.data;
                   }else{
                       toastr.error(res.data.mensaje);
                   }
               
               }, function errorCallback(res){
                   toastr.error("Error: no se realizo la conexion con el servidor");
           });
     }

     angular.element(document).ready(function(){
          obj.getData();         
     });
}