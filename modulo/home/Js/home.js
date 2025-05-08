/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var url_getusercampras = "./modulo/Compras/Ajax/Compras.php";
var urlCostumer = "./modulo/ProcesoCompra/Ajax/ProcesoCompra.php";
var url_session = "./modulo/home/Ajax/session.php";
const urlSkydropx = "https://api.skydropx.com/v1/quotations";
const token = "Token token=fInE1ArT8CJfaR2wkznA5hXSNCMSXs7vitsCFeM98Pct";

var url = "./modulo/home/Ajax/home.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";

tsuruVolks.controller('homeCtrl', homeCtrl);
if ($_SESSION.iduser == null) {
    localStorage.clear();
}
function homeCtrl($scope, $http) {
    var obj = $scope;
    obj.Data = {};
    obj.productos = [];
    obj.promociones;

    const vistamasvendidos = document.querySelector("#vistamasvendidos");
    const vistaliquidacion = document.querySelector("#vistaliquidacion");
    const vistanuevos = document.querySelector("#vistanuevos");

    document.querySelector(".toolbar_click").addEventListener("click", function () {
        vistaliquidacion.style.display = "none";
        vistamasvendidos.style.display = "block";
        vistanuevos.style.display = "none";
        this.classList.add("toolbar__activada");
        document.querySelector(".toolbar_click0").classList.remove("toolbar__activada");
        document.querySelector(".toolbar_click1").classList.remove("toolbar__activada");
    });
    document.querySelector(".toolbar_click0").addEventListener("click", function () {
        vistamasvendidos.style.display = "none";
        vistaliquidacion.style.display = "block";
        vistanuevos.style.display = "none";
        this.classList.add("toolbar__activada");
        document.querySelector(".toolbar_click").classList.remove("toolbar__activada");
        document.querySelector(".toolbar_click1").classList.remove("toolbar__activada");
    });
    document.querySelector(".toolbar_click1").addEventListener("click", function () {
        vistamasvendidos.style.display = "none";
        vistaliquidacion.style.display = "none";
        vistanuevos.style.display = "block";
        this.classList.add("toolbar__activada");
        document.querySelector(".toolbar_click").classList.remove("toolbar__activada");
        document.querySelector(".toolbar_click0").classList.remove("toolbar__activada");
    });

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            e.NewUrlName = e["Producto"].replaceAll(" ","-");
            e.NewUrlName = e.NewUrlName.replaceAll(",","");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
            e.NewAltName = e["Producto"].replaceAll(",","");
            if(e.stock == 0){
                e.agotado = true;
            }
        })
    }

    obj.getCategorias = async () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Categorias", home: true } },
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.Data = res.data.Data;
                obj.eachRefacciones(obj.Data.masVendidos);
                obj.eachRefacciones(obj.Data.liquidacion);
            } else {
                toastr.error("Error: Condición Incumplida");
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });

    }

    obj.getImagen = (e) => {
        //return "https://macromautopartes.com/images/Categorias/" + e._id + ".png";
        return "images/Categorias/" + e._id + ".png";
    }

    obj.RefaccionDetalles = (_id) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + _id, "_self");
    }

    angular.element(document).ready(function () {
        obj.getCategorias();

        setTimeout(() => {
            $('.slick2').slick({
                arrows: true,
                dots:true,
                infinite: true,
                autoplay:true,
                autoplaySpeed: 5000,
                slidesToShow: 1,
                adaptiveHeight: true,
                responsive: [
                    {
                        breakpoint: 1500,
                        settings: {
                            arrows: false,
                            cssEase: 'linear'
                        }
                    }
                ]
            });
        }, 500);

        $('.slick2').on('wheel', (function(e){
            e.preventDefault();
            if(e.originalEvent.deltaY < 0){
                $(this).slick('slickPrev');
            } else {
                $(this).slick('slickNext');
            }
        }));
    });

}

