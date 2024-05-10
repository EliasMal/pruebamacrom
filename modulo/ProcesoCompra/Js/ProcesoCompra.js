
var urlModel = "./modulo/ProcesoCompra/Model.php";

tsuruVolks.controller('ProcesoCompraCtrl', ProcesoCompraCtrl);
(function () {
    if($_SESSION["iduser"] == null){
        window.location.href = '?mod=home';
    }
})()
function ProcesoCompraCtrl($scope, $http) {
    var obj = $scope;

    obj.setAcreditado = async () => {
        try {
            const result = await $http({
                method: 'POST',
                url: urlModel,
                data: { modelo: { opc: "tarjeta" } },
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
                if (result.data.Bandera == 1) {
                    console.log("Compra Registrada");
                    location.reload();
                }
            }
            $scope.$apply();
        } catch (error) {
            toastr.error(error)
        }

    }

    angular.element(document).ready(function () {
            
        if (window.location.href.includes("?mod=ProcesoCompra&opc=paso3")) {
            if($_SESSION["padlock"] != "lock"){
                console.log("Pago Efectivo");
            }
        }
        if (window.location.href.includes("?mod=ProcesoCompra&opc=cc?")) {
            if($_SESSION["padlock"] != "lock"){
                obj.setAcreditado();
                console.log("Pago con tarjeta");
            }
        }

    });
}