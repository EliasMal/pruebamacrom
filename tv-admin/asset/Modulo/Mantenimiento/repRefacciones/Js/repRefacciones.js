const url = "./Modulo/Mantenimiento/repRefacciones/Ajax/repRefacciones.php";

tsuruVolks
        .controller('repRefaccionesCtrl', ["$scope","$http",repRefaccionesCtrl]);

function repRefaccionesCtrl($scope, $http){
    const obj = $scope;
    obj.matenimiento= {};

    obj.btnBloqUS = ()=>{
        obj.matenimiento.opc = "desactivarUS";
        if(confirm("Esta acción desactivara a todo los usuarios, menos root, para poder modificar el codigo de la pagina")){
            $http({
                method: 'POST',
                url: url,
                data: { matenimiento: obj.matenimiento }
    
            }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        toastr.success(res.data.Mensaje);
                        localStorage.setItem('mantenimiento',1);
                        
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

    obj.btnActUS = ()=>{
        obj.matenimiento.opc = "activarUS";
        if(confirm("Esta acción activara a todo los usuarios, para trabajar de manera normal")){
            $http({
                method: 'POST',
                url: url,
                data: { matenimiento: obj.matenimiento }
    
            }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        toastr.success(res.data.Mensaje);
                        localStorage.setItem('mantenimiento',0);
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


    // obj.btnrepRefacciones = ()=>{
    //     if(confirm("¿Estas seguro de actuliar la tabla de refacciones?")){
    //         $http({
    //             method: 'POST',
    //                 url: url,
    //                 data: {},
    //             }).then(function successCallback(res){
    //                 if(res.data.Bandera == 1){
    //                     toastr.success(res.data.Mensaje);
                        
    //                 }else{
    //                     toastr.error(res.data.Mensaje);
    //                 }
    //                 obj.disabled = false;
    //             }, function errorCallback(res){
    //                     toastr.error("Error: no se realizo la conexion con el servidor");
    //                     obj.disabled = false;
    //             });
    //     }
    // }

}