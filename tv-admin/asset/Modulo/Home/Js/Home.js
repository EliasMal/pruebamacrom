var url = "./Modulo/Home/Ajax/Home.php";
var url2 = "./Modulo/Configuracion/Perfil/Ajax/Perfil.php";

tsuruVolks
    .controller('HomeCtrl', ["$scope", "$http", HomeCtrl]);

function HomeCtrl($scope, $http) {
    var obj = $scope;

    obj.datos;
    obj.perfil;
    obj.data = {
        opc: ""
    }

    obj.getData = () => {
        obj.data.opc = "get";
        obj.sendData();
    }

    obj.getUsuario = () => {
        $http({
            method: 'POST',
            url: url2,
            data: { usuarios: { opc: "get" } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.perfil = res.data.Data;
                //obj..opc = "save";
                obj.img = res.data.img ? "Images/usuarios/" + res.data.Data.Username + ".png" : "Images/usuarios/nouser.png";
                //console.log(obj.perfil);
            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.sendData = () => {
        $http({
            method: 'POST',
            url: url,
            data: { home: obj.data }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.datos = res.data;
            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    angular.element(document).ready(function () {
        obj.getData();
        obj.getUsuario();
    });
}