var url = "./Includes/Cron/getRefacciones.php";

tsuruVolks
        .controller('PreciosCrtl',["$scope","$http",PreciosCrtl]);

function PreciosCrtl($scope,$http){
    var obj = $scope;
    obj.flag = false;
    obj.usr = "root"
    obj.logData = [];

    obj.btnActualizar = ()=>{
        if(confirm("Estas seguro de actualizar la lista de precios")){
            obj.flag = true;
            $http({
                method: 'POST',
                    url: "https://volks.dyndns.info:444/service.asmx/datos_art",
                    headers:{
                        'Content-Type':  "application/x-www-form-urlencoded"
                                        
                    },
                     transformResponse: function(data){
                         return $.parseXML(data);
                     }
                }).then(function successCallback(res){
                    var xml = $(res.data);
                    var json = xml.find("string");
                    
                    obj.enviar(json[0].innerHTML);
                    
                }, function errorCallback(res){
                    console.log("Error: no se realizo la conexion con el servidor");
                    
            });
        }
    }

    obj.enviar= (json)=>{
        $http({
            method: 'POST',
                url: url,
                data: {usr:obj.usr,opc:"set",json:json},
                
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.flag = false;
                    obj.logData = res.data.Data;
                    toastr.success(res.data.mensaje);
                    
                }else{
                    toastr.error(res.data.mensaje);
                }
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
    }

    obj.btnActualizar2 = ()=>{
        if(confirm("Estas seguro de actualizar la lista de precios")){
            obj.flag = true;
            $http({
                method: 'POST',
                    url: url,
                    data: {usr:obj.usr,opc:"set"},
                    
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        obj.flag = false;
                        obj.logData = res.data.Data;
                        toastr.success(res.data.mensaje);

                    }else{
                        toastr.error(res.data.mensaje);
                    }
        
                }, function errorCallback(res){
                        toastr.error("Error: no se realizo la conexion con el servidor");
                });
            
        }
    }

    obj.getLog = ()=>{
        $http({
            method: 'POST',
                url: url,
                data: {usr:obj.root,opc:"get"},
                
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.logData = res.data.Data;
                }else{
                    toastr.error(res.data.mensaje);
                }
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
    }

    angular.element(document).ready(function(){
        obj.getLog();
    });
}