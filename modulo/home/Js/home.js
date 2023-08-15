/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var url_getusercampras = "./modulo/Compras/Ajax/Compras.php";
var urlCostumer = "./modulo/ProcesoCompra/Ajax/ProcesoCompra.php";
var url_session ="./modulo/home/Ajax/session.php";
const url_monedero = "./tv-admin/asset/Modulo/Control/Monedero/Ajax/Monedero.php";
//const urlSkydropx = "https://api-demo.skydropx.com/v1/quotations"
const urlSkydropx = "https://api.skydropx.com/v1/quotations"
//const token = "Token token=SuOZQz5IrqceQbJmBqQfAo4PMQvNKMCh2PtXOKMfKM0t";
const token = "Token token=fInE1ArT8CJfaR2wkznA5hXSNCMSXs7vitsCFeM98Pct";


var url = "./modulo/home/Ajax/home.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";

tsuruVolks.controller('homeCtrl', homeCtrl);

function homeCtrl ($scope,$http){
    var obj = $scope;
    obj.Data = {};
    obj.productos = [];
    obj.databannerPrincipal;
    obj.promociones;

    obj.eachRefacciones = (array)=>{
        array.forEach(e=>{
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token
            })
        })
    }

    const masvendidos = document.getElementById("masvendidos");
    const liquidacion = document.getElementById("liquidacion");
    const idopcion1 = document.getElementById("idmasvendidos");
    const idopcion2 = document.getElementById("idliquidacion");
    obj.mostrarvendido = ()=>{
        liquidacion.style.display = "none";
        masvendidos.style.display = "block";
        idopcion1.classList.add("opcion__activada");
        idopcion2.classList.remove("opcion__activada");
    }
    obj.mostrarliquida = () =>{
        masvendidos.style.display = "none";
        liquidacion.style.display = "block";
        idopcion2.classList.add("opcion__activada");
        idopcion1.classList.remove("opcion__activada");
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
        try {
            const result = await $http({
                method: 'POST',
                url: url,
                data: {modelo: {opc: "buscar", tipo: "Categorias", home:true}},
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            console.log(result.data)
            if(result){
                if (result.data.Bandera == 1) {
                    obj.Data = result.data.Data;
                    obj.eachRefacciones(obj.Data.masVendidos);
                    obj.eachRefacciones(obj.Data.liquidacion);
                }
            }  
            $scope.$apply();
        } catch (error) {
            toastr.error(error)
        }
        
    }
    
    obj.getImagen = (e)=>{
        return "images/Categorias/"+e._id+".webp";
    }
    
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
                        case 'Principal':
                            obj.databannerPrincipal = res.data.Data;
                            
                        break;
                        case 'Promociones':
                            obj.promociones = res.data.Data;
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
        obj.getBanners({opc:"get", Categoria: "Principal", Estatus:1});
        obj.getBanners({opc:"get", Categoria: "Promociones", Estatus:1});
        setTimeout(()=>{
            var itemSlick1 = $('.slick1').find('.item-slick1');
            var action1 = [];
            var action2 = [];
            var action3 = [];
            var cap1Slide1 = [];
            var cap2Slide1 = [];
            var btnSlide1 = [];

            for(var i=0; i<itemSlick1.length; i++) {
            cap1Slide1[i] = $(itemSlick1[i]).find('.caption1-slide1');
            cap2Slide1[i] = $(itemSlick1[i]).find('.caption2-slide1');
            btnSlide1[i] = $(itemSlick1[i]).find('.wrap-btn-slide1');
            }


            $('.slick1').on('init', function(){

                action1[0] = setTimeout(function(){
                    $(cap1Slide1[0]).addClass($(cap1Slide1[0]).data('appear') + ' visible-true');
                },200);

                action2[0] = setTimeout(function(){
                    $(cap2Slide1[0]).addClass($(cap2Slide1[0]).data('appear') + ' visible-true');
                },1000);

                action3[0] = setTimeout(function(){
                    $(btnSlide1[0]).addClass($(btnSlide1)[0].data('appear') + ' visible-true');
                },1800);              
            });


            $('.slick1').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                dots: false,
                appendDots: $('.wrap-slick1-dots'),
                dotsClass:'slick1-dots',
                infinite: true,
                autoplay: true,
                autoplaySpeed: 6000,
                arrows: true,
                appendArrows: $('.wrap-slick1'),
                prevArrow:'<button class="arrow-slick1 prev-slick1"><i class="fa  fa-angle-left" aria-hidden="true"></i></button>',
                nextArrow:'<button class="arrow-slick1 next-slick1"><i class="fa  fa-angle-right" aria-hidden="true"></i></button>',  
            });

            $('.slick1').on('afterChange', function(event, slick, currentSlide){ 
                for(var i=0; i<itemSlick1.length; i++) {

                clearTimeout(action1[i]);
                clearTimeout(action2[i]);
                clearTimeout(action3[i]);


                $(cap1Slide1[i]).removeClass($(cap1Slide1[i]).data('appear') + ' visible-true');
                $(cap2Slide1[i]).removeClass($(cap2Slide1[i]).data('appear') + ' visible-true');
                $(btnSlide1[i]).removeClass($(btnSlide1[i]).data('appear') + ' visible-true');

                }

                action1[currentSlide] = setTimeout(function(){
                    $(cap1Slide1[currentSlide]).addClass($(cap1Slide1[currentSlide]).data('appear') + ' visible-true');
                },200);

                action2[currentSlide] = setTimeout(function(){
                    $(cap2Slide1[currentSlide]).addClass($(cap2Slide1[currentSlide]).data('appear') + ' visible-true');
                },1000);

                action3[currentSlide] = setTimeout(function(){
                    $(btnSlide1[currentSlide]).addClass($(btnSlide1)[currentSlide].data('appear') + ' visible-true');
                },1800);            
            });
        },1500)
    });
    
}

