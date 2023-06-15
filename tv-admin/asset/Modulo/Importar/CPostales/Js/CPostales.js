var url = "./Modulo/Importar/CPostales/Ajax/CPostales.php";

tsuruVolks
        .controller('CPostalesCtrl', ["$scope","$http",CPostalesCtrl]);

function CPostalesCtrl($scope,$http){
    var obj  = $scope;
    obj.archivo = {
        nombre: "Selecciona un archivo con formato CSV",
        opc: "importar"
    };
    obj.disabled = false;


    obj.btnUpload = ()=>{
        if(obj.archivo.file != undefined){
            obj.setSend(obj.archivo);
        }else{
            toastr.warning("Error: no has seleccionado un archivo CSV");
        }
    }

    obj.setSend = (archivo)=>{
        if(confirm("¿Estas seguro de importar el archivo?")){
            obj.disabled = true;
            $http({
                method: 'POST',
                    url: url,
                    data: {importar:archivo},
                    headers:{
                        'Content-Type': undefined
                    },
                    transformRequest: function(data){
                        var formData = new FormData();
                        for(var m in data.importar){
                            formData.append(m, data.importar[m]);
                        }
                        return formData;
                    }
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

    angular.element(document).ready(function () {
        $(".archivos").on("change",function(e){
            var file = this.files[0];
            if(file){
                if(file.size <= 5120000){
                    obj.archivo.nombre = file.name;
                    
                    obj.$apply();
                    
                }else {
                    toastr.warning("Error la Imagen supera los 1 MB");
                    return;
                }
            }else{
                return;
            }
        })
       
    });
}