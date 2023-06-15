const url = "./Modulo/Mantenimiento/repRefacciones/Ajax/repRefacciones.php";

tsuruVolks
        .controller('repRefaccionesCtrl', ["$scope","$http",repRefaccionesCtrl]);

function repRefaccionesCtrl($scope, $http){
    const obj = $scope;

    obj.btnrepRefacciones = ()=>{
        if(confirm("¿Estas seguro de actuliar la tabla de refacciones?")){
            $http({
                method: 'POST',
                    url: url,
                    data: {},
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        toastr.success(res.data.Mensaje);
                        
                    }else{
                        toastr.error(res.data.Mensaje);
                    }
                    obj.disabled = false;
                }, function errorCallback(res){
                        toastr.error("Error: no se realizo la conexion con el servidor");
                        obj.disabled = false;
                });
        }
    }

}