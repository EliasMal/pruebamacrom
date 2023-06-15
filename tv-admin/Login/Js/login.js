
var url = "./Ajax/login.php";
tsuruVolks.controller('loginCtrl',['$scope','$http', loginCtrl]);

function loginCtrl($scope,$http){
    var obj = $scope;
    obj.login = {};
    
     
    obj.btnlogin = function(){
        $http({
            method: 'POST',
                url: url,
                data: {login: obj.login}
            }).then(function successCallback(res){
                console.log(res.data);
                if(res.data.Bandera == 1){
                    localStorage.setItem('session', JSON.stringify(res.data.session))
                    toastr.success(res.data.mensaje + obj.login.user);
                    window.location.href="../asset/";
                }else{
                    toastr.error(res.data.mensaje);
                }
                
                
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
        
    }
    
}


