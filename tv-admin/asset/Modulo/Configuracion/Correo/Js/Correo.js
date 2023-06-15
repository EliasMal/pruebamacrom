'use strict'
const url = "./Modulo/Configuracion/Correo/Ajax/Correo2.php";

tsuruVolks.controller('CorreoCtrl',["$scope","$http",CorreoCtrl]);

function CorreoCtrl($scope, $http){
    let obj = $scope;

    obj.btnEnviarCorreo = async()=>{
        try {
            const result = await $http({
                method: 'GET',
                url: url,
                params:  {id:1},
                headers:{'Content-Type':  "application/x-www-form-urlencoded"},
                }).then(function successCallback(res) {
                    return res
                }, function errorCallback(res) {
                    toastr.error(res);
                });
        } catch (error) {
            toastr.error(error);
        }
    }
}