'use strict';

var url = "./Modulo/Home/Ajax/Home.php"; 
var url2 = "./Modulo/Configuracion/Perfil/Ajax/Perfil.php";

tsuruVolks.controller('HomeCtrl', ["$scope", "$http", "$timeout", HomeCtrl]);

function HomeCtrl($scope, $http, $timeout) {
    var obj = $scope;

    obj.perfil = {};
    obj.permisos = {};
    obj.kpis = {};
    obj.ultimosPedidos = [];
    obj.datosAntiguos = {};
    obj.toastCerrado = false;

    obj.cerrarToast = function() {
        obj.toastCerrado = true;
    };
    
    obj.getUsuario = () => {
        $http.post(url2, { usuarios: { opc: "get" } }).then(function (res) {
            if (res.data.Bandera == 1) {
                obj.perfil = res.data.Data;
                obj.cargarPermisosYDashboard();
            }
        });
    };

    obj.cargarPermisosYDashboard = () => {
        $http.post(url, { opc: "getPermisos" }).then(function(res) {
            if (res.data.Bandera == 1) {
                
                let rol = String(obj.perfil.Tipo_usuario).toLowerCase();
                
                if(rol == 'admin' || rol == 'root') {
                    obj.permisos = { Pedidos: true, Reportes: true, Clientes: true, Contacto: true, Actualizarpre: true, Refacciones: true, Principal: true, Blog: true };
                } else {
                    obj.permisos = res.data.Data;
                }

                if(obj.permisos.Pedidos || obj.permisos.Reportes || obj.permisos.Clientes || obj.permisos.Contacto) {
                    $http.post(url, { opc: "getKPIs" }).then(r => { if(r.data.Bandera == 1) obj.kpis = r.data.Data; });
                }

                if(obj.permisos.Pedidos) {
                    $http.post(url, { opc: "getUltimosPedidos" }).then(r => { if(r.data.Bandera == 1) obj.ultimosPedidos = r.data.Data; });
                    $http.post(url, { opc: "getGraficaMensual" }).then(r => { if(r.data.Bandera == 1) obj.renderHighchart(r.data.Data); });
                }

                if(obj.permisos.Actualizarpre || obj.permisos.Refacciones) {
                    $http.post(url, { home: { opc: "get" } }).then(r => { if(r.data.Bandera == 1) obj.datosAntiguos = r.data; });
                }
            }
        });
    };

    obj.renderHighchart = (datosVentas) => {
        let categorias = datosVentas.map(item => item.dia);
        let serieDeDatos = datosVentas.map(item => {
            return {
                y: item.total,
                cantidad: item.cantidad || 0 
            };
        });

        Highcharts.chart('graficaVentas', {
            chart: { type: 'areaspline', backgroundColor: 'transparent' },
            accessibility: { enabled: false },
            title: { text: null },
            xAxis: { 
                categories: categorias, 
                crosshair: true, 
                gridLineWidth: 0 
            },
            yAxis: { 
                title: { text: 'Ingresos ($)' },
                labels: { formatter: function () { return '$' + this.value.toLocaleString(); } }
            },
            tooltip: { 
                useHTML: true,
                formatter: function () {
                    return `
                        <div style="padding: 5px;">
                            <b>${this.key}</b><br/>
                            <span style="color:${this.color}">\u25CF</span> 
                            Ventas Concretadas: <b>${this.point.cantidad}</b><br/>
                            <span style="color:#28a745; margin-left: 12px;">
                                Total: <b>$${Highcharts.numberFormat(this.y, 2)}</b>
                            </span>
                        </div>
                    `;
                }
            },
            plotOptions: { 
                areaspline: { fillOpacity: 0.3, lineWidth: 3, marker: { radius: 4 } } 
            },
            series: [{ 
                name: 'Ventas Concretadas', 
                data: serieDeDatos, 
                color: '#de0007' 
            }],
            credits: { enabled: false },
            legend: { enabled: false }
        });
    };

    angular.element(document).ready(function () {
        obj.getUsuario();
    });
}