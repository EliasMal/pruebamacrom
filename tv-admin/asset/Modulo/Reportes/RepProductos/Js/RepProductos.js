const url = "./Modulo/Mantenimiento/repRefacciones/Ajax/repRefacciones.php";

tsuruVolks
    .controller('RepProductosCtrl', ["$scope", "$http", RepProductosCtrl]);

function RepProductosCtrl($scope, $http) {
    const obj = $scope;
    obj.reportes = {};
    obj.mantenimiento = {};
    obj.daysCount = {};
    let dinamicV = {};
    obj.semanas = { smn1: "", smn2: "", smn3: "", smn4: "", smn5: "" };
    obj.semanasF = { smn1: "", smn2: "", smn3: "", smn4: "", smn5: "" };
    const d = new Date();
    const dia = d.getDate();
    const mes = d.getMonth() + 1;
    if (localStorage.getItem("dateNew")) {
        obj.mantenimiento.dateNew = localStorage.getItem("dateNew");
        let modDate = new Date(localStorage.getItem("dateOld"));
        obj.mantenimiento.dateOld = modDate.getFullYear() + '/' + (modDate.getMonth() + 1) + '/' + (modDate.getDate() + 1);
    } else {
        obj.mantenimiento.dateNew = d.getFullYear() + '/' + mes + '/' + dia;
        obj.mantenimiento.dateOld = d.getFullYear() + '/' + (mes - 1) + '/' + (dia + 1);
    }

    obj.CalRestablecer = () => {
        if (localStorage.getItem("dateNew")) {
            localStorage.removeItem("dateNew");
            localStorage.removeItem("dateOld");
            location.reload();
        }
    }

    obj.getNewCreated = () => {
        if (document.querySelector("#CalendardateNew").value != "" && document.querySelector("#CalendardateOld").value != "") {
            localStorage.setItem("dateNew", document.querySelector("#CalendardateNew").value);
            localStorage.setItem("dateOld", document.querySelector("#CalendardateOld").value);
            location.reload();
        }
        obj.mantenimiento.opc = "newCreated";
        $http({
            method: 'POST',
            url: url,
            data: { mantenimiento: obj.mantenimiento }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.reportes = res.data.data;
                toastr.success(res.data.Mensaje);
                let dold = { doldI: "", doldFM: "", doldIM: "", doldF: "" };
                dold.doldI = new Date(obj.mantenimiento.dateOld);
                let verificacion = "";
                switch (dold.doldI.getDay()) {
                    case 0:
                        for (var property in obj.semanas) {
                            dold.doldIM = dold.doldI.getMonth() + 1;
                            obj.semanas[property] = dold.doldI.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldI.getDate());
                            dold.doldI.setDate(dold.doldI.getDate() + 7);

                            dold.doldF = new Date(obj.semanas[property]);
                            dold.doldF.setDate(dold.doldF.getDate() + 6);
                            obj.semanasF[property] = dold.doldF.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldF.getDate());
                            verificacion = new Date(obj.semanasF[property]);
                            if(dold.doldF > verificacion){
                              obj.semanasF[property] = verificacion.getFullYear() + '/' + (verificacion.getMonth()+2) +'/' + verificacion.getDate();
                            }
                        }
                    break;
                    case 1:
                        for (var property in obj.semanas) {
                            dold.doldIM = dold.doldI.getMonth() + 1;
                            dold.doldI.setDate(dold.doldI.getDate() - 1);
                            obj.semanas[property] = dold.doldI.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldI.getDate());
                            dold.doldI.setDate(dold.doldI.getDate() + 8);

                            dold.doldF = new Date(obj.semanas[property]);
                            dold.doldF.setDate(dold.doldF.getDate() + 6);
                            obj.semanasF[property] = dold.doldF.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldF.getDate());
                            verificacion = new Date(obj.semanasF[property]);
                            if(dold.doldF > verificacion){
                              obj.semanasF[property] = verificacion.getFullYear() + '/' + (verificacion.getMonth()+2) +'/' + verificacion.getDate();
                            }
                        }
                    break;
                    case 2:
                        for (var property in obj.semanas) {
                            dold.doldIM = dold.doldI.getMonth() + 1;
                            dold.doldI.setDate(dold.doldI.getDate() - 2);
                            obj.semanas[property] = dold.doldI.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldI.getDate());
                            dold.doldI.setDate(dold.doldI.getDate() + 9);

                            dold.doldF = new Date(obj.semanas[property]);
                            dold.doldF.setDate(dold.doldF.getDate() + 6);
                            obj.semanasF[property] = dold.doldF.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldF.getDate());
                            verificacion = new Date(obj.semanasF[property]);
                            if(dold.doldF > verificacion){
                              obj.semanasF[property] = verificacion.getFullYear() + '/' + (verificacion.getMonth()+2) +'/' + verificacion.getDate();
                            }
                        }
                    break;
                    case 3:
                        for (var property in obj.semanas) {
                            dold.doldIM = dold.doldI.getMonth() + 1;
                            dold.doldI.setDate(dold.doldI.getDate() - 3);
                            obj.semanas[property] = dold.doldI.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldI.getDate());
                            dold.doldI.setDate(dold.doldI.getDate() + 10);

                            dold.doldF = new Date(obj.semanas[property]);
                            dold.doldF.setDate(dold.doldF.getDate() + 6);
                            obj.semanasF[property] = dold.doldF.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldF.getDate());
                            verificacion = new Date(obj.semanasF[property]);
                            if(dold.doldF > verificacion){
                              obj.semanasF[property] = verificacion.getFullYear() + '/' + (verificacion.getMonth()+2) +'/' + verificacion.getDate();
                            }
                        }
                    break;
                    case 4:
                        for (var property in obj.semanas) {
                            dold.doldIM = dold.doldI.getMonth() + 1;
                            dold.doldI.setDate(dold.doldI.getDate() - 4);
                            obj.semanas[property] = dold.doldI.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldI.getDate());
                            dold.doldI.setDate(dold.doldI.getDate() + 11);

                            dold.doldF = new Date(obj.semanas[property]);
                            dold.doldF.setDate(dold.doldF.getDate() + 6);
                            obj.semanasF[property] = dold.doldF.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldF.getDate());
                            verificacion = new Date(obj.semanasF[property]);
                            if(dold.doldF > verificacion){
                              obj.semanasF[property] = verificacion.getFullYear() + '/' + (verificacion.getMonth()+2) +'/' + verificacion.getDate();
                            }
                        }
                    break;
                    case 5:
                        for (var property in obj.semanas) {
                            dold.doldIM = dold.doldI.getMonth() + 1;
                            dold.doldI.setDate(dold.doldI.getDate() - 5);
                            obj.semanas[property] = dold.doldI.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldI.getDate());
                            dold.doldI.setDate(dold.doldI.getDate() + 12);

                            dold.doldF = new Date(obj.semanas[property]);
                            dold.doldF.setDate(dold.doldF.getDate() + 6);
                            obj.semanasF[property] = dold.doldF.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldF.getDate());
                            verificacion = new Date(obj.semanasF[property]);
                            if(dold.doldF > verificacion){
                              obj.semanasF[property] = verificacion.getFullYear() + '/' + (verificacion.getMonth()+2) +'/' + verificacion.getDate();
                            }
                        }
                    break;
                    case 6:
                        for (var property in obj.semanas) {
                            dold.doldIM = dold.doldI.getMonth() + 1;
                            dold.doldI.setDate(dold.doldI.getDate() - 6);
                            obj.semanas[property] = dold.doldI.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldI.getDate());
                            dold.doldI.setDate(dold.doldI.getDate() + 13);

                            dold.doldF = new Date(obj.semanas[property]);
                            dold.doldF.setDate(dold.doldF.getDate() + 6);
                            obj.semanasF[property] = dold.doldF.getFullYear() + '/' + dold.doldIM + '/' + (dold.doldF.getDate());
                            verificacion = new Date(obj.semanasF[property]);
                            if(dold.doldF > verificacion){
                              obj.semanasF[property] = verificacion.getFullYear() + '/' + (verificacion.getMonth()+2) +'/' + verificacion.getDate();
                            }
                        }
                    break;
                }
                
                dinamicV.Semana1 = { Lunes: 0, Martes: 0, Miercoles: 0, Jueves: 0, Viernes: 0, Sabado: 0 }
                dinamicV.Semana2 = { Lunes: 0, Martes: 0, Miercoles: 0, Jueves: 0, Viernes: 0, Sabado: 0 }
                dinamicV.Semana3 = { Lunes: 0, Martes: 0, Miercoles: 0, Jueves: 0, Viernes: 0, Sabado: 0 }
                dinamicV.Semana4 = { Lunes: 0, Martes: 0, Miercoles: 0, Jueves: 0, Viernes: 0, Sabado: 0 }
                dinamicV.Semana5 = { Lunes: 0, Martes: 0, Miercoles: 0, Jueves: 0, Viernes: 0, Sabado: 0 }

                obj.reportes.forEach(element => {
                    var day = new Date(element.dateCreated);
                    element.Semana1 = {smnI:new Date(obj.semanas.smn1),smnF:new Date(obj.semanasF.smn1 + " 23:59:59")};
                    element.Semana2 = {smnI:new Date(obj.semanas.smn2),smnF:new Date(obj.semanasF.smn2 + " 23:59:59")};
                    element.Semana3 = {smnI:new Date(obj.semanas.smn3),smnF:new Date(obj.semanasF.smn3 + " 23:59:59")};
                    element.Semana4 = {smnI:new Date(obj.semanas.smn4),smnF:new Date(obj.semanasF.smn4 + " 23:59:59")};
                    element.Semana5 = {smnI:new Date(obj.semanas.smn5),smnF:new Date(obj.semanasF.smn5 + " 23:59:59")};
                    switch (day.getDay()) {
                        case 1:
                            element.day = 'Lunes';
                            for (let property in element) {
                                if(day > element[property].smnI && day <= element[property].smnF){
                                  dinamicV[property].Lunes = dinamicV[property].Lunes +1;
                                }
                            }
                        break;

                        case 2:
                            element.day = 'Martes';
                            for (let property in element) {
                                if(day > element[property].smnI && day <= element[property].smnF){
                                  dinamicV[property].Martes = dinamicV[property].Martes +1;
                                }
                            }
                        break;

                        case 3:
                            element.day = "Miercoles";
                            for (let property in element) {
                                if(day > element[property].smnI && day <= element[property].smnF){
                                  dinamicV[property].Miercoles = dinamicV[property].Miercoles +1;
                                }
                            }
                        break;

                        case 4:
                            element.day = "Jueves";
                            for (let property in element) {
                                if(day > element[property].smnI && day <= element[property].smnF){
                                  dinamicV[property].Jueves = dinamicV[property].Jueves +1;
                                }
                            }
                        break;

                        case 5:
                            element.day = "Viernes";
                            for (let property in element) {
                                if(day > element[property].smnI && day <= element[property].smnF){
                                  dinamicV[property].Viernes = dinamicV[property].Viernes +1;
                                }
                            }
                        break;

                        case 6:
                            element.day = "Sabado";
                            for (let property in element) {
                                if(day > element[property].smnI && day <= element[property].smnF){
                                  dinamicV[property].Sabado = dinamicV[property].Sabado +1;
                                }
                            }
                        break;
                    }
                });
                console.log(obj.semanas," Hasta ",obj.semanasF);
                obj.showGrafica(obj.reportes, dinamicV);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.showGrafica = () => {
        let mesOld = new Date(obj.mantenimiento.dateOld);
        Highcharts.chart('grafica', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Nuevas Refacciones'
            },
            subtitle: {
                text: mesOld.toLocaleString('default', { month: 'long' }).toUpperCase() + ' (' + obj.mantenimiento.dateOld + ') - ' + d.toLocaleString('default', { month: 'long' }).toUpperCase() + ' (' + obj.mantenimiento.dateNew + ')'
            },
            xAxis: {
                categories: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'],
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: obj.reportes.length + ' Piezas (pz)'
                }
            },
            tooltip: {
                valueSuffix: ' (pz)'
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                name: 'Lunes',
                data: [dinamicV.Semana1.Lunes, dinamicV.Semana2.Lunes, dinamicV.Semana3.Lunes, dinamicV.Semana4.Lunes, dinamicV.Semana5.Lunes]

            }, {
                name: 'Martes',
                data: [dinamicV.Semana1.Martes, dinamicV.Semana2.Martes, dinamicV.Semana3.Martes, dinamicV.Semana4.Martes, dinamicV.Semana5.Martes]

            }, {
                name: 'Miercoles',
                data: [dinamicV.Semana1.Miercoles, dinamicV.Semana2.Miercoles, dinamicV.Semana3.Miercoles, dinamicV.Semana4.Miercoles, dinamicV.Semana5.Miercoles]

            }, {
                name: 'Jueves',
                data: [dinamicV.Semana1.Jueves, dinamicV.Semana2.Jueves, dinamicV.Semana3.Jueves, dinamicV.Semana4.Jueves, dinamicV.Semana5.Jueves]

            }, {
                name: 'Viernes',
                data: [dinamicV.Semana1.Viernes, dinamicV.Semana2.Viernes, dinamicV.Semana3.Viernes, dinamicV.Semana4.Viernes, dinamicV.Semana5.Viernes]

            }, {
                name: 'Sabado',
                data: [dinamicV.Semana1.Sabado, dinamicV.Semana2.Sabado, dinamicV.Semana3.Sabado, dinamicV.Semana4.Sabado, dinamicV.Semana5.Sabado]

            }]
        });
    }

    angular.element(document).ready(function () {
        obj.getNewCreated();
        $('.calendario').datepicker({
            format: 'yyyy-mm-dd'
        });
    });
}