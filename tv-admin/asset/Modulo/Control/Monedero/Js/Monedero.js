const urlMonedero = "./Modulo/Control/Monedero/Ajax/Monedero.php";
tsuruVolks
.controller('MonederoCtrl', ["$scope","$http",MonederoCtrl])
.controller('MonederoDetallesCtrl',["$scope", "$http", MonederoDetallesCtrl]);


function MonederoCtrl($scope, $http){
    let obj = $scope;
    obj.res;

    obj.btnCrearMonedero = ()=>{
        window.location.href="?mod=Monedero&opc=crear"
    }

    obj.sendMonedero = async function (metodo,params=null){
        try {
            const resultado  = await $http({
                method: metodo,
                url: urlMonedero,
                params: params
            }).then(function successCallback(res){
                return res       
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
            console.log(resultado.data);
            if(resultado.data.Bandera==1){
                obj.res = resultado.data.Data;

                $scope.$apply();
                console.log(obj.res);
            }else{
                toastr.error(resultado.data.mensaje);  
            } 
        } catch (error) {
            toastr.error(error)
        }
        
    }

    obj.btnDetallesMonedero = (_idCliente)=>{
        window.location.href="?mod=Monedero&opc=detalles&id="+_idCliente;
    }

    angular.element(document).ready(function(){
        obj.sendMonedero('GET');
    });

}

function MonederoDetallesCtrl($scope, $http){
    const obj = $scope;
    obj.id;
    obj.result;
    obj.records;
    obj.totalrecords=0;
    obj.spin = false;
    obj.paginador = {page:0, limit:15}
    
    obj.getDetallesMonedero = async (params)=>{
        try {
            const result = await $http({
                method: "GET",
                url: urlMonedero,
                params: params
            }).then(function successCallback(res){
                return res       
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
            console.log(result.data);
            if(result.data.Bandera == 1){
                obj.result = result.data.Data;
                obj.records = result.data.Data.History;
                obj.totalrecords = result.data.Data.NoMonedero;
                $scope.$apply();
            }else{
                toastr.error(result.data.mensaje);
            }
        } catch (error) {
            toastr.error(error);
        }
    }

    obj.getHistoryMonedero = async (params)=>{
        console.log(params)
        try {
            const result = await $http({
                method: "GET",
                url: urlMonedero,
                params: params
            }).then(function successCallback(res){
                return res       
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
            
            if(result.data.Bandera == 1){
                obj.records = result.data.Data.History;
                obj.totalrecords = result.data.Data.NoMonedero;
                
            }else{
                toastr.error(result.data.mensaje);
            }
            obj.spin = false
            $scope.$apply();
        } catch (error) {
            toastr.error(error);
        }
    }

    obj.btnNext = ()=>{
        obj.paginador.page += obj.paginador.limit
        
        let params = {opc:"history", idCliente: obj.id, page:obj.paginador.page, limit: obj.paginador.limit }
        obj.getHistoryMonedero(params)
       
    }

    obj.bntPrevios = ()=>{
        obj.paginador.page -= obj.paginador.limit
        if(obj.paginador.page<0){
            obj.paginador.page = 0
        }
        let params = {opc:"history", idCliente: obj.id, page: obj.paginador.page, limit: obj.paginador.limit}
        obj.getHistoryMonedero(params)
        
    }

    obj.btnRefresh = ()=>{
        obj.spin = true
        let params = {opc:"history", idCliente: obj.id, page: obj.paginador.page, limit: obj.paginador.limit}
        obj.getHistoryMonedero(params)
    }

    angular.element(document).ready(function(){
        $params = {opc:"Detalles", idCliente: obj.id}
        obj.getDetallesMonedero($params);
        
    });
}

