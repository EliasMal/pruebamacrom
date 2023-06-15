
const urlBlog = './Modulo/Secciones/Blog/Ajax/Blog.php';

tsuruVolks
    .controller('BlogCtrl', ["$scope","$http",BlogCtrl])
    .controller('BlogeditCtrl', ["$scope","$http",BlogeditCtrl]);
    
function BlogCtrl($scope, $http){
    var obj = $scope
    obj.hoy = moment().format("YYYY-MM-DD")
    obj.paginador = {page:0, limit:10}
    
    obj.Blog = {
        Noentradas:0,
        entradas:[]
    };

    obj.entrada = {
        Titulo:null,
        Contenido:null,
        Fecha:null,
        imagen:{
            placeholder:"Ingresa una imagen del cintillo"
        },
        imagendestacada:{
            placeholder:"Ingresa una imagen destacada miniatura"
        },
        Publicar:false,
        Estatus:false,
        opc: "new"
        
    }

    obj.btnAumentar = ()=>{
        obj.paginador.page += obj.paginador.limit
        obj.getEntradas( obj.paginador.page,  obj.paginador.limit);
    }
    obj.btnDisminuir = ()=>{
        obj.paginador.page -= obj.paginador.limit
        if(obj.paginador.page<0){
            obj.paginador.page = 0
        }
        obj.getEntradas( obj.paginador.page,  obj.paginador.limit);
    }


    obj.btnNuevaEntrada = ()=>{
        window.location.href="?mod=Blog&opc=newEntrada";
    }

    obj.btnEditarEntrada = (id)=>{
        window.location.href="?mod=Blog&opc=editEntrada&id="+id;
    }

    obj.btnEliminarEntrada = (id)=>{
        if(confirm("¿Estas seguro de eliminar la Entrada?")){
            obj.getEntradas(0,10,"delete",id);
        }
    }

    obj.getEntradas = (skip=0, limit=10, opc="get",id=null)=>{
        $http({
            method: 'POST',
                url: urlBlog,
                data: {Blog:{opc:opc, skip:skip, limit:limit,id:id}},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.Blog){
                        formData.append(m, data.Blog[m]);
                    }                    
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera){
                    
                   obj.Blog.Noentradas = res.data.Data.NoRegistrados;
                   obj.Blog.entradas = res.data.Data.Registros
                }else{
                    toastr.error(res.data.mensaje);
                }
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnCrearEntrada = ()=>{
        obj.entrada.Estatus = true;
        $http({
            method: 'POST',
                url: urlBlog,
                data: {Blog:obj.entrada},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.Blog){
                        formData.append(m, data.Blog[m]);
                    }
                   /*  formData.append('file1',data.Blog.imagen.file1)
                    formData.append('file2',data.Blog.imagendestacada.file2) */
                    //console.log(data)

                    for(m in data.Blog.imagen){
                        formData.append(m+"1", data.Blog.imagen[m]);
                    }
                    for(m in data.Blog.imagendestacada){
                        formData.append(m+"2", data.Blog.imagendestacada[m]);
                    }
                    //formData.append("file",data.Blog.imagen.file);
                    
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera){
                    toastr.success(res.data.mensaje)
                    setTimeout(function(){ window.location.href='?mod=Blog' }, 3000);
                    
                }else{
                    toastr.error(res.data.mensaje);
                }
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
        });

    }

    angular.element(document).ready(function(){
        $(".archivos").on("change",function(e){
            let reader = new FileReader();
            let file = this.files[0];
            reader.readAsDataURL(file)
            if(file){
                if(file.size <= 1024000){
                    if(this.id == "imgcintillo")
                        obj.entrada.imagen.filename = file.name;
                    else
                        obj.entrada.imagendestacada.filename = file.name

                    obj.$apply();
                    reader.onload = ()=>{
                        if(this.id == "imgcintillo")
                            $("#previewimgcintillo").attr('src',reader.result);
                        else
                            $("#previewimgminiatura").attr('src',reader.result);
                        
                    }
                }else {
                    toastr.warning("Error la Imagen supera los 1 MB");
                    return;
                }
            }else{
                return;
            }
        })
        obj.getEntradas( );
    });
}

function BlogeditCtrl($scope, $http){
    var obj = $scope

    obj.id;
    obj.entrada;
    obj.Imagen={
        placeholder:"Ingresa una imagen destacada para el area del cintillo"
    };

    obj.imagenDestacada={
        placeholder:"Ingresa una imagen destacada para el area de miniaturas"
    };

    obj.dominio = "";

    obj.SendData = ($opc=null,$id=null, $data=null, file= null, file2=null)=>{
        $http({
            method: 'POST',
                url: urlBlog,
                data: {opc: $opc, id:$id, data: $data, file:file, file2: file2},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    console.log(data.file)
                    if(data.opc=="save"){
                        for(var m in data.data){
                            formData.append(m, data.data[m]);
                        }
                        formData.append('opc',data.opc)
                        formData.append('id',data.id)

                        formData.append('file1',data.file)
                        formData.append('file2',data.file2)
                    }else{
                        
                        for(var m in data){
                            formData.append(m, data[m]);
                        }
                    }
                    
                    
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera){
                    if($opc == "getOne"){
                        obj.entrada = res.data.Data;
                        obj.dominio = res.data.dominio;
                    }else{
                        toastr.success(res.data.mensaje)
                        setTimeout(function(){ window.location.href='?mod=Blog' }, 3000);
                    }
                    
                    
                }else{
                    toastr.error(res.data.mensaje);
                }
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnEditarEntrada = ()=>{
        if(confirm("¿Estas seguro de guardar los cambios?")){
            
            if(obj.Imagen.file ){
                obj.entrada.Imagen = obj.Imagen.filename;
            }
            if(obj.imagenDestacada.file){
                obj.entrada.imagendestacada = obj.imagenDestacada.filename
            }
            obj.SendData("save",obj.id,obj.entrada, obj.Imagen.file, obj.imagenDestacada.file);
        }
        
    }


    angular.element(document).ready(function(){
        $(".archivos").on("change",function(e){
            let reader = new FileReader();
            let file = this.files[0];
            reader.readAsDataURL(file)
            if(file){
                if(file.size <= 1024000){
                    if(this.id == "imgcintillo")
                        obj.Imagen.filename = file.name;
                    else
                        obj.imagenDestacada.filename= file.name
                    obj.$apply();
                    reader.onload = ()=>{
                        if(this.id == "imgcintillo")
                            $("#previewimgcintillo").attr('src',reader.result);
                        else
                            $("#previewimgminiatura").attr('src',reader.result);
                    }   
                }else {
                    toastr.warning("Error la Imagen supera los 1 MB");
                    return;
                }
            }else{
                return;
            }
        })
        obj.SendData("getOne",obj.id);
    });
}