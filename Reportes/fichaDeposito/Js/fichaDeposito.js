'use strict'

var urlfichadeposito = "Ajax/FichaDeposito.php";
var RptsuruVolks = angular.module('RptsuruVolks',[])

RptsuruVolks.controller('FichaDepositoCtrl', ["$scope","$http", FichaDepositoCtrl]);

function FichaDepositoCtrl($scope,$http){
    var obj = $scope;
    obj.id_pedido;
    obj.fichaData = {};
    obj.session = {};

    obj.sendData = (data) =>{
        $http({
            method: 'POST',
            url: urlfichadeposito,
            data: {ficha: data}
        }).then(function successCallback(res) {
            console.log(res.data);
            if(res.data.Bandera == 1){
                obj.fichaData = res.data.Data;
            }else{
                toastr.error(res.data.mensaje)
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    angular.element(document).ready(function () {
        obj.session = JSON.parse(localStorage.getItem('session'));
        
        if(obj.session == "null" && obj.session.autentificacion==undefined && obj.session.autentificacion!=1){
            localStorage.clear();
            location.href = "../../";
        }else{
            
            if(localStorage.getItem("id_pedido")){
                obj.id_pedido = localStorage.getItem("id_pedido");
                obj.sendData({id:obj.id_pedido});
            }else{
                localStorage.clear();
                location.href = "../../";
            }
        }
        
        
    });
}


