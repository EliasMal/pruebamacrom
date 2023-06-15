var url = "./Modulo/Configuracion/Categorias/Ajax/Categorias.php";
tsuruVolks
        .controller("CategoriasCrtl",["$scope","$http",CategoriasCrtl]);


function CategoriasCrtl($scope,$http){
    var obj = $scope;
    obj.categoria = {};
    obj.nuevo = true;
    obj.img = "Images/boxed-bg.jpg";
    
    obj.btnNuevaCategoria = ()=>{
        obj.nuevo = true;
        $("#mcategoria").modal("show");
        obj.categoria = {};
        obj.categoria.opc = "new";
        obj.img = "Images/boxed-bg.jpg";
        document.getElementById("txtfile").value = "";
    };
    
    obj.getCategorias = ()=>{
        $http({
            method: 'POST',
                url: url,
                data: {categoria:{opc:"buscar",historico: obj.historico? 0:1}},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.categoria){
                        formData.append(m, data.categoria[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.categorias = res.data.data;
                    console.log(obj.categorias);
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    };
    
    
    
    obj.btnGetxml = ()=>{
        $http({
            method: "POST",
            url: "./Xml/productos1.xml",
            data: {},
            headers:{
                    'Content-Type':  undefined
            },
            transformResponse: function(data){
                console.log(data);
                return $.parseXML(data);
            }
        }).then(
            function successCallback(res){
                var xml = $(res.data);
                var json = xml.find("string");
                var json = JSON.parse(json.text());
                console.log(json.Table);
            },
            function errorCallback(res){
                console.log(res);
            }
        );
    }
    
    obj.btnCrearCategoria = ()=>{
        $http({
            method: 'POST',
                url: url,
                data: {categoria:obj.categoria},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.categoria){
                        formData.append(m, data.categoria[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    $("#mcategoria").modal("hide");
                    obj.getCategorias();
                    toastr.success(res.data.mensaje);
                }else{
                    toastr.error("Error: No se creo la categoria");
                }
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
    obj.btnEditarCategoria = ()=>{
        if(confirm("Estas seguro de guardar los cambios?")){
            
            $http({
                method: 'POST',
                    url: url,
                    data: {categoria:obj.categoria},
                    headers:{
                        'Content-Type': undefined
                    },
                    transformRequest: function(data){
                        var formData = new FormData();
                        for(var m in data.categoria){
                            formData.append(m, data.categoria[m]);
                        }
                        //formData.append("file",data.file);
                        return formData;
                    }
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        $("#mcategoria").modal("hide");
                        obj.getCategorias();
                        toastr.success(res.data.mensaje);
                    }else{
                        toastr.error(res.data.mensaje);
                    }
                }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }
    
    obj.opcEditar = (categoria)=>{
        obj.nuevo = false;
        obj.categoria = categoria;
        obj.categoria.opc = "edit"
        obj.img = obj.categoria.foto? "../../images/Categorias/"+obj.categoria._id+".png":"Images/boxed-bg.jpg";
        console.log(obj.categoria);
        $("#mcategoria").modal("show");
    }
    obj.opcDesactivar = (_id) =>{
        if(confirm("Estas seguro de guardar los cambios?")){
            
            $http({
                method: 'POST',
                    url: url,
                    data: {categoria:{opc:"disabled",_id:_id}},
                    headers:{
                        'Content-Type': undefined
                    },
                    transformRequest: function(data){
                        var formData = new FormData();
                        for(var m in data.categoria){
                            formData.append(m, data.categoria[m]);
                        }
                        //formData.append("file",data.file);
                        return formData;
                    }
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        $("#mcategoria").modal("hide");
                        obj.getCategorias();
                        toastr.success(res.data.mensaje);
                    }else{
                        toastr.error(res.data.mensaje);
                    }
                }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }
    
    obj.opcActivar = (_id) =>{
        if(confirm("Estas seguro de activar la categoria?")){
            
            $http({
                method: 'POST',
                    url: url,
                    data: {categoria:{opc:"enabled",_id:_id}},
                    headers:{
                        'Content-Type': undefined
                    },
                    transformRequest: function(data){
                        var formData = new FormData();
                        for(var m in data.categoria){
                            formData.append(m, data.categoria[m]);
                        }
                        //formData.append("file",data.file);
                        return formData;
                    }
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        $("#mcategoria").modal("hide");
                        obj.getCategorias();
                        toastr.success(res.data.mensaje);
                    }else{
                        toastr.error(res.data.mensaje);
                    }
                }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }
    
    angular.element(document).ready(function(){
        var fileInput1 = document.getElementById('txtfile');
        fileInput1.addEventListener('change', function(e) {
            var file = fileInput1.files[0];
            var imageType = /image.*/;
            if (file) {
                if (file.size <= 512000) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        obj.img = reader.result;
                        obj.$apply();
                    }
                    reader.readAsDataURL(file);
                } else {
                    toastr.warning("Error la Imagen supera los 512 KB");
                    return;
                }
            } else {
                return
            }
        });
        obj.getCategorias();
    });
}

