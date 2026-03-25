'use strict';

const url = "./Modulo/Mantenimiento/repRefacciones/Ajax/repRefacciones.php";

tsuruVolks.controller('RepProductosCtrl', ["$scope", "$http", "$timeout", RepProductosCtrl]);

function RepProductosCtrl($scope, $http, $timeout) {
    const obj = $scope;
    obj.reportes = [];
    obj.datosTicket = [];
    obj.resumenTicket = {};
    obj.mantenimiento = {};
    obj.topProductos = [];

    const initFechas = () => {
        if (localStorage.getItem("dateNew")) {
            obj.mantenimiento.dateNew = localStorage.getItem("dateNew");
            obj.mantenimiento.dateOld = localStorage.getItem("dateOld");
        } else {
            let hoy = new Date();
            let hace30Dias = new Date();
            hace30Dias.setDate(hoy.getDate() - 30);
            
            obj.mantenimiento.dateNew = hoy.toLocaleDateString('en-CA'); 
            obj.mantenimiento.dateOld = hace30Dias.toLocaleDateString('en-CA');
        }
    };
    initFechas();

    obj.CalRestablecer = () => {
        localStorage.removeItem("dateNew");
        localStorage.removeItem("dateOld");
        location.reload();
    };

    obj.generarReporte = () => {
        let inputOld = document.querySelector("#CalendardateOld").value;
        let inputNew = document.querySelector("#CalendardateNew").value;

        if (inputOld && inputNew) {
            localStorage.setItem("dateOld", inputOld);
            localStorage.setItem("dateNew", inputNew);
            obj.mantenimiento.dateOld = inputOld;
            obj.mantenimiento.dateNew = inputNew;
            
            obj.getNewCreated();
            obj.getTicketPromedio();
            obj.getTopProductos();
        } else {
            toastr.warning("Por favor selecciona ambas fechas.");
        }
    };

    // ==========================================
    // GRÁFICA: NUEVAS REFACCIONES
    // ==========================================
    obj.getNewCreated = () => {
        let payload = angular.copy(obj.mantenimiento);
        payload.opc = "newCreated";
        
        $http.post(url, { mantenimiento: payload }).then(function(res) {
            if (res.data.Bandera == 1) {
                obj.reportes = res.data.data || [];
                toastr.success("Datos de refacciones cargados");
                obj.procesarDatosGrafica();
            } else {
                toastr.error("Error al cargar los datos.");
            }
        }, function(res) {
            toastr.error("Error de conexión con el servidor.");
        });
    };

    obj.procesarDatosGrafica = () => {
        if (!obj.reportes || obj.reportes.length === 0) {
            $('#grafica').html('<div class="text-center py-5 text-muted"><i class="fa fa-chart-area fa-3x mb-3 text-light"></i><br><h5>No hay capturas registradas en este periodo.</h5></div>');
            return;
        }

        let conteoPorFecha = {};

        obj.reportes.forEach(item => {
            if (item && item.dateCreated) {
                let fechaCorta = item.dateCreated.split(" ")[0]; 
                if (!conteoPorFecha[fechaCorta]) {
                    conteoPorFecha[fechaCorta] = 0;
                }
                conteoPorFecha[fechaCorta]++;
            }
        });

        let fechasOrdenadas = Object.keys(conteoPorFecha).sort();
        let datosSerie = fechasOrdenadas.map(fecha => conteoPorFecha[fecha]);
        let categoriasAmigables = fechasOrdenadas.map(fecha => {
            let parts = fecha.split('-');
            let d = new Date(parts[0], parts[1] - 1, parts[2]); 
            return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' });
        });

        $('#grafica').empty(); 
        obj.showGrafica(categoriasAmigables, datosSerie);
    };

    obj.showGrafica = (categorias, datos) => {
        Highcharts.chart('grafica', {
            chart: { type: 'areaspline' },
            title: { text: 'Actividad de Captura de Refacciones', style: { fontWeight: 'bold' } },
            subtitle: { text: 'Del ' + obj.mantenimiento.dateOld + ' al ' + obj.mantenimiento.dateNew },
            xAxis: { categories: categorias, crosshair: true, title: { text: 'Fecha de Captura' } },
            yAxis: { min: 0, title: { text: 'Piezas Capturadas' }, allowDecimals: false },
            colors: ['#de0007'],
            tooltip: { shared: true, valueSuffix: ' piezas' },
            plotOptions: { areaspline: { fillOpacity: 0.2, marker: { enabled: true, radius: 4, fillColor: '#ffffff', lineColor: '#de0007', lineWidth: 2 } } },
            series: [{ name: 'Refacciones nuevas', data: datos }]
        });
    };

    // ==========================================
    // GRÁFICA: TICKET PROMEDIO
    // ==========================================
    obj.getTicketPromedio = () => {
        let payload = angular.copy(obj.mantenimiento);
        payload.opc = "ticketPromedio";

        $http.post(url, { mantenimiento: payload }).then(function(res) {
            if (res.data.Bandera == 1) {
                obj.datosTicket = res.data.data.grafica || [];
                obj.resumenTicket = res.data.data.resumen || {};
                obj.detallesTicket = res.data.data.detalles || []; 
                
                obj.procesarGraficaTicket();
            }
        });
    };

    obj.procesarGraficaTicket = () => {
        if(!obj.datosTicket || obj.datosTicket.length === 0) {
            $('#graficaTicket').html('<div class="text-center py-5 text-muted"><i class="fa fa-chart-bar fa-3x mb-3 text-light"></i><br><h5>No hay ventas en este periodo.</h5></div>');
            return;
        }

        let categorias = [];
        let seriePromedio = [];
        let serieVentas = [];

        obj.datosTicket.forEach(item => {
            if (item && item.fecha) {
                let parts = item.fecha.split('-');
                let label = new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('es-MX', { day: 'numeric', month: 'short' });
                
                categorias.push(label);
                serieVentas.push({
                    y: parseFloat(item.total_vendido),
                    cantidad: parseInt(item.pedidos_dia)
                });

                seriePromedio.push(parseFloat(item.ticket_diario));
            }
        });

        $('#graficaTicket').empty();
        Highcharts.chart('graficaTicket', {
            chart: { zoomType: 'xy', backgroundColor: 'transparent' },
            title: { text: 'Evolución de Ventas vs Ticket Promedio', style: { fontWeight: 'bold' } },
            xAxis: [{ categories: categorias, crosshair: true }],
            yAxis: [
                { title: { text: 'Total Vendido ($)', style: { color: '#007bff' } }, labels: { format: '${value}', style: { color: '#007bff' } } }, 
                { title: { text: 'Ticket Promedio ($)', style: { color: '#28a745' } }, labels: { format: '${value}', style: { color: '#28a745' } }, opposite: true }
            ],
            
            tooltip: { 
                shared: true, 
                useHTML: true,
                formatter: function () {
                    let s = `<b style="font-size: 12px;">${this.points[0].key}</b><br/>`;
                    
                    this.points.forEach(point => {
                        let color = point.color;
                        let valor = Highcharts.numberFormat(point.y, 2);
                        
                        if (point.series.name === 'Ingreso Total') {
                            s += `<span style="color:${color}">\u25CF</span> ${point.series.name}: <b>${point.point.cantidad} pedidos</b> ($${valor})<br/>`;
                        } else {
                            s += `<span style="color:${color}">\u25CF</span> ${point.series.name}: <b>$${valor}</b>`;
                        }
                    });
                    return s;
                }
            },
            
            series: [
                {name: 'Ingreso Total', type: 'column', yAxis: 0, data: serieVentas, color: '#007bff', opacity: 0.7},
                {name: 'Ticket Promedio', type: 'spline', yAxis: 1, data: seriePromedio, color: '#28a745', lineWidth: 3, marker: { radius: 4, fillColor: '#fff', lineColor: '#28a745', lineWidth: 2 } }
            ],
            credits: { enabled: false }
        });
    };

    // ==========================================
    // GRÁFICA: TOP PRODUCTOS
    // ==========================================
    obj.getTopProductos = () => {
        let payload = angular.copy(obj.mantenimiento);
        payload.opc = "topProductos";

        $http.post(url, { mantenimiento: payload }).then(function(res) {
            if (res.data.Bandera == 1) {
                obj.topProductos = res.data.data || [];
                obj.procesarGraficaTopProductos(); 
            }
        });
    };
    
    obj.procesarGraficaTopProductos = () => {
        if (!obj.topProductos || obj.topProductos.length === 0) {
            $('#graficaTopProductos').empty();
            return;
        }

        let categorias = [];
        let datosVentas = [];
        let top5 = obj.topProductos.slice(0, 5);

        top5.reverse().forEach(item => {
            categorias.push(item.NombreProducto);
            datosVentas.push(parseInt(item.UnidadesVendidas));
        });
        $('#graficaTopProductos').empty();

        Highcharts.chart('graficaTopProductos', {
            chart: { type: 'bar', backgroundColor: 'transparent', height: 350 },
            title: { text: 'Distribución de Ventas por Volumen (Top 5)', style: { fontWeight: 'bold', fontSize: '14px' } },
            xAxis: { 
                categories: categorias, 
                title: { text: null },
                labels: { style: { fontWeight: 'bold', color: '#333' } }
            },
            yAxis: { 
                min: 0, 
                title: { text: 'Unidades Vendidas', align: 'high' }, 
                labels: { overflow: 'justify' } 
            },
            tooltip: { valueSuffix: ' unidades vendidas' },
            plotOptions: { 
                bar: { 
                    dataLabels: { enabled: true, color: '#ffffff', inside: true, style: { textOutline: 'none' } }, 
                    color: '#2b6cb0',
                    borderRadius: 4
                } 
            },
            legend: { enabled: false },
            credits: { enabled: false },
            series: [{ name: 'Ventas', data: datosVentas }]
        });
    };

    // ==========================================
    // UTILIDADES
    // ==========================================
    obj.verDetalles = (producto) => {
        obj.productoActual = producto; 
        $('#modalDetalleProducto').modal('show');
    };
    
    obj.irAlPedido = (folio) => {
        window.location.href = "?mod=Pedidos&opc=detalles&id=" + folio; 
    };

    angular.element(document).ready(function () {
        obj.generarReporte();

        $('.calendario').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        const urlParams = new URLSearchParams(window.location.search);
        const vista = urlParams.get('view');

        if (vista === 'ticket') {
            $timeout(() => {
                const tabLink = $('a[href="#tab-ticket"]');
                
                if (tabLink.length > 0) {
                    tabLink.trigger('click'); 
                    $timeout(() => { 
                        window.dispatchEvent(new Event('resize')); 
                    }, 400);
                } else {
                    console.error("No encontré el enlace #tab-ticket en el HTML");
                }
            }, 300); 
        }

        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            window.dispatchEvent(new Event('resize'));
        });
    });
}