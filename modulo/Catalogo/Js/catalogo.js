const url_catalogo = "./modulo/Catalogo/Ajax/Catalogo.php";
var url_session ="./modulo/home/Ajax/session.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";

tsuruVolks
.controller('catalogosCtrl',["$scope","$http",catalogosCtrl])
.controller("catalogosDetallesCtrl",["$scope","$http",catalogosDetallesCtrl])
.filter('startFromGrid', function() {
            return function(input, start) {
                start = +start;
                return input.slice(start);
            }
        });

function catalogosCtrl ($scope,$http){
    var obj = $scope;
    obj.refaccion = {
        opc: "Buscar",
        tipo: "",
        categoria:"",
        marca:"",
        vehiculo:"",
        anio:"",
        producto:"",
        x:0,
        y:0
    }
    obj.categorias = [];
    obj.Marcas = [];
    obj.Vehiculos = [];
    obj.Modelos = [];
    obj.Refacciones = [];
    obj.catalogos = [];
    /*variables del paginador*/
    obj.currentPage = 0;
    obj.pages = [];
    obj.pageSize = 20;
    obj.Trefacciones = 0;
    
    
    obj.eachRefacciones = (array)=>{
        array.forEach(e=>{
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token
            })
        })
    }
    obj.getSeicom = async (clave)=>{
        try {
            const result = await $http({
                method: 'GET',
                url: url_seicom,
                params:  {articulo:clave},
                headers:{'Content-Type':  "application/x-www-form-urlencoded"},
                transformResponse: function(data){
                     return $.parseXML(data);
                 }
    
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error(res);
            }); 
            if(result){
                const xml = $(result.data).find("string");
                let json = JSON.parse(xml.text());
                return json.Table.map(e=>e.existencia).reduce((a,b)=>a+b,0)==0? true: false;
            }   
        } catch (error) {
            toastr.error(error)
        }
        
    }

    obj.getCategorias = async()=>{
        obj.refaccion.tipo = "Categorias"
        obj.refaccion.x = 0
        obj.refaccion.y = obj.pageSize
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params:  obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
    
            if(result){
                if (result.data.Bandera == 1) {
                    obj.categorias = result.data.Data.Categorias;
                    obj.Marcas = result.data.Data.Marcas;
                    
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    
                    obj.currentPage = 0;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones);
                }
                $scope.$apply();
            }
        } catch (error) {
            toastr.error(error);
        }
        
            
    }
    
    obj.getVehiculos = async () => {
        //obj.refaccion.categoria = "";
        obj.refaccion.tipo="Vehiculos";
        obj.refaccion.vehiculo="";
        obj.refaccion.anio="";
        obj.refaccion.producto="";
        obj.refaccion.x = 0
        obj.refaccion.y = obj.pageSize
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
                
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if(result){
                if (result.data.Bandera == 1) {
                    obj.Vehiculos = result.data.Data.Vehiculos;
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.currentPage = 0;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones);
                }else{
                    toastr.error(result.data.Mensaje);        
                }
                $scope.$apply();
            }
        } catch (error) {
            toastr.error(error);
        }
        
    }
    
     obj.getModelos = async() => {
        obj.refaccion.tipo = "Modelos";
        obj.refaccion.anio="";
        obj.refaccion.producto="";
        obj.refaccion.x = 0
        obj.refaccion.y = obj.pageSize
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if(result){
                if (result.data.Bandera == 1) {
                    obj.Modelos = result.data.Data.Modelos;
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.currentPage = 0;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones)
                }else{
                    toastr.error(result.data.Mensaje);
                }
            }
            $scope.$apply();    
        } catch (error) {
             
        }
    }
    
    obj.getAnios = async ()=>{
        obj.refaccion.tipo = "Anios";
        obj.refaccion.producto="";
        obj.refaccion.x = 0
        obj.refaccion.y = obj.pageSize
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if(result){
                if (result.data.Bandera == 1) {
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.currentPage = 0;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones)
                }else{
                    toastr.error(result.data.Mensaje);
                }
            }
            $scope.$apply();
        } catch (error) {
            toastr.error(error);
        }
        
    }
    
        
    obj.getRefaccion = async (x=0, y=obj.pageSize)=>{
        
        obj.refaccion.tipo = "Refaccion";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if(result){
                if (result.data.Bandera == 1) {
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.currentPage = 0;

                    obj.configPages(); 
                    obj.eachRefacciones(obj.Refacciones)               
                }else{
                    toastr.error(result.data.Mensaje);
                }
            }
            
            $scope.$apply();
        } catch (error) {
            toastr.error(error);
        }
        
    }

    obj.getPaginador = async(x=0, y=obj.pageSize)=>{
        obj.refaccion.tipo="Paginacion";
        obj.refaccion.x = x;
        obj.refaccion.y = y;
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params:  obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if(result){
                if (result.data.Bandera) {
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones)
                }else{
                    toastr.error(result.data.Mensaje);
                }
            }
            $scope.$apply();
        } catch (error) {
            toastr.error(error);
        }
        
    };
    
    obj.configPages = function() {
            obj.pages.length = 0;
            var ini = obj.currentPage - 4;
            var fin = obj.currentPage + 5;
            
            if (ini < 1) {
                ini = 1;
                if (Math.ceil(obj.Trefacciones / obj.pageSize) > 10)
                    fin = 10;
                else
                    fin = Math.ceil(obj.Trefacciones / obj.pageSize);
            } else {
                
                if (ini >= Math.ceil(obj.Trefacciones / obj.pageSize) - 10) {
                    ini = Math.ceil(obj.Trefacciones / obj.pageSize) - 10;
                    fin = Math.ceil(obj.Trefacciones / obj.pageSize);
                }
            }
            if (ini < 1) ini = 1;
            for (var i = ini; i <= fin; i++) {
                obj.pages.push({
                    no: i
                });
            }
        };
    
    obj.setPage = function(index) {
        obj.currentPage = index - 1;
        obj.configPages();
        obj.getPaginador(obj.currentPage*obj.pageSize, obj.pageSize)
    };
    
    obj.RefaccionDetalles = (_id)=>{
        window.open("?mod=catalogo&opc=detalles&_id="+_id,"_self");
    }
    
    obj.getBanners = (data)=>{
        $http({
            method: 'POST',
                url: "./tv-admin/asset/Modulo/Secciones/webprincipal/Ajax/webprincipal.php",
                data: {imagen:data},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.imagen){
                        formData.append(m, data.imagen[m]);
                    }
                    //formData.append("file",data.file);
                    
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    switch(res.data.categoria){
                        case 'Catalogos':
                            obj.catalogos = res.data.Data;
                        break;
                       
                    }
                    
                }else{
                    toastr.error(res.data.mensaje);
                }
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
    }


    angular.element(document).ready(function () {
        obj.getCategorias();
        //obj.getMarcas();
        obj.getBanners({opc:"get", Categoria: "Catalogos", Estatus:1});    
    });
    


}

function catalogosDetallesCtrl($scope, $http){

   var obj = $scope;
   obj.session = $_SESSION;
   
   obj.btnEnabled = obj.session.autentificacion == undefined? true:false;
  
   obj.Refaccion={
       id:0,
       opc: "OneRefaccion",
       datos: {},
       galeria: [],
       Existencias: 0,
       cantidad : 1,
       precio:0
       
   };
       obj.RefaccionDetalles = (_id)=>{
        window.open("?mod=catalogo&opc=detalles&_id="+_id,"_self");
    }
   
   obj.Activa = false;
   
   obj.trunc = (x, posiciones = 0)=>{
        var s = x.toString()
        var l = s.length
        var decimalLength = s.indexOf('.') + 1
        var numStr = decimalLength > 0? s.substr(0, decimalLength + posiciones): s
        return Number(numStr)
   }

      
   obj.getSeicom = async (clave)=>{
        try {
            const result = await $http({
                method: 'GET',
                url: url_seicom,
                params:  {articulo:clave},
                headers:{'Content-Type':  "application/x-www-form-urlencoded"},
                transformResponse: function(data){
                    return $.parseXML(data);
                }

            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error(res);
            }); 
            if(result){
                const xml = $(result.data).find("string");
                let json = JSON.parse(xml.text());
                return json.Table.map(e=>e.existencia).reduce((a,b)=>a+b,0)==0? true: false;
            }   
        } catch (error) {
            toastr.error(error)
        }
        
    }

    obj.getArticulovolks = ()=>{
        $http({
            method: 'POST',
                url: "https://volks.dyndns.info:444/service.asmx/consulta_art",
                data: "articulo="+obj.Refaccion.datos.Clave,
                headers:{
                    'Content-Type':  "application/x-www-form-urlencoded"
                                    
                },
                 transformResponse: function(data){
                     return $.parseXML(data);
                 }
            }).then(function successCallback(res){
                var xml = $(res.data);
                var json = xml.find("string");
                obj.existencias = JSON.parse(json.text());
                
                obj.existencias.Table.forEach(function(e){
                     obj.Refaccion.Existencias+= parseInt(e.existencia);
                     obj.Refaccion.precio = obj.trunc((e.precio_5 * 1.16),2);
                })
                obj.Activa = obj.Refaccion.Existencias != 0? true:false;
                
            }, function errorCallback(res){
                console.log("Error: no se realizo la conexion con el servidor");
                
        });
    }
    obj.btndisminuir = ()=>{
        obj.Refaccion.cantidad = obj.Refaccion.cantidad!=1? obj.Refaccion.cantidad-1:1  
    }

    obj.btnaumentar = ()=>{
        if(obj.Refaccion.cantidad<obj.Refaccion.Existencias){
            obj.Refaccion.cantidad++
        }
    }
   
   obj.getRefaccion = ()=>{
    
        $http({
            method: 'GET',
            url: url_catalogo,
            params: {opc:"OneRefaccion",id: obj.Refaccion.id},
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.Refaccion.datos = res.data.Data.Refaccion;
                obj.Refaccion.galeria = res.data.Data.Galeria;
                obj.productos = res.data.Data.Productos;
                obj.eachRefacciones(obj.productos);
                obj.getArticulovolks();
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
    obj.Agregarcarrito = ()=>{
            $http({
                method: 'POST',
                url: url_session,
                data: {modelo: obj.Refaccion}

            }).then(function successCallback(res) {

                location.reload();

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        
    }
    
    obj.getImagen = (status, id)=>{
        var url = "images/refacciones/";
        return status? url+id+".png":url+id+".webp";
    }
    
    obj.getGaleria = (id)=>{
        if(id != undefined){
            url = "images/galeria/"+id;
            return  url;
        }
        
    }
    
    obj.btnInciarSession = ()=>{
        location.href="?mod=login";
    }
    
    obj.btnDetallesRelacionados = (id)=>{
        window.open("?mod=catalogo&opc=detalles&_id="+id,"_self");
    }

    obj.eachRefacciones = (array)=>{
        array.forEach(e=>{
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token
            })
        })
        
    }
    
   angular.element(document).ready(function () {
        obj.getRefaccion();
        setTimeout(()=>{
            $('.slick2').slick({
                
                slidesToShow: 4,
                slidesToScroll: 4,
                infinite: true,
                dots: true,
                autoplay: true,
                autoplaySpeed: 5000,
                arrows: true,
                responsive: [
                    {
                      breakpoint: 1200,
                      settings: {
                        slidesToShow: 4,
                        slidesToScroll: 4
                      }
                    },
                    {
                      breakpoint: 992,
                      settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                      }
                    },
                    {
                      breakpoint: 768,
                      settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                      }
                    },
                    {
                      breakpoint: 576,
                      settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: false,
                        fade: true,
                        cssEase: 'linear'
                      }
                    }
                ]    
            });
        },2000);
    });
    
}
