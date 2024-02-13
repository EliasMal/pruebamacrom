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

function homeCtrl($scope, $http) {
    var obj = $scope;
    obj.Data = {};
    obj.productos = [];
    obj.databannerPrincipal;
    obj.promociones;

    const vistamasvendidos = document.querySelector("#vistamasvendidos");
    const vistaliquidacion = document.querySelector("#vistaliquidacion");

    document.querySelector(".toolbar_click").addEventListener("click", function () {
        vistaliquidacion.style.display = "none";
        vistamasvendidos.style.display = "block";
        this.classList.add("toolbar__activada");
        document.querySelector(".toolbar_click0").classList.remove("toolbar__activada");
    });
    document.querySelector(".toolbar_click0").addEventListener("click", function () {
        vistamasvendidos.style.display = "none";
        vistaliquidacion.style.display = "block";
        this.classList.add("toolbar__activada");
        document.querySelector(".toolbar_click").classList.remove("toolbar__activada");
    });


    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token
            })
        })
    }

    obj.getSeicom = async (clave) => {
        try {
            const result = await $http({
                method: 'GET',
                url: url_seicom,
                params: { articulo: clave },
                headers: { 'Content-Type': "application/x-www-form-urlencoded" },
                transformResponse: function (data) {
                    return $.parseXML(data);
                }

            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error(res);
            });
            if (result) {
                const xml = $(result.data).find("string");
                let json = JSON.parse(xml.text());
                return json.Table.map(e => e.existencia).reduce((a, b) => a + b, 0) == 0 ? true : false;
            }
        } catch (error) {
            toastr.error(error)
        }

    }

    obj.getCategorias = async () => {
        try {
            const result = await $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "buscar", tipo: "Categorias", home: true } },
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
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

    obj.getImagen = (e) => {
        return "images/Categorias/" + e._id + ".png";
    }

    obj.RefaccionDetalles = (_id) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + _id, "_self");
    }

    obj.getBanners = (data) => {
        $http({
            method: 'POST',
            url: "./tv-admin/asset/Modulo/Secciones/webprincipal/Ajax/webprincipal.php",
            data: { imagen: data },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.imagen) {
                    formData.append(m, data.imagen[m]);
                }
                //formData.append("file",data.file);

                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                switch (res.data.categoria) {
                    case 'Principal':
                        obj.databannerPrincipal = res.data.Data;

                        break;
                    case 'Promociones':
                        obj.promociones = res.data.Data;
                        break;
                }

            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    angular.element(document).ready(function () {
        obj.getCategorias();
        obj.getBanners({ opc: "get", Categoria: "Principal", Estatus: 1 });
        obj.getBanners({ opc: "get", Categoria: "Promociones", Estatus: 1 });
    });

}

