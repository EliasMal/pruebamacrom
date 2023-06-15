var url = "./Modulo/Configuracion/Cenvios/Ajax/Cenvios.php";

tsuruVolks
        .controller('CenviosCtrl', ["$scope","$http",CenviosCtrl]);
        /* .directive("select2", function($timeout, $parse) {
            return {
              restrict: 'AC',
              require: 'ngModel',
              link: function(scope, element, attrs) {
                console.log(attrs);
                $timeout(function() {
                  element.select2();
                  element.select2Initialized = true;
                });
          
                var refreshSelect = function() {
                  if (!element.select2Initialized) return;
                  $timeout(function() {
                    element.trigger('change');
                  });
                };
                
                var recreateSelect = function () {
                  if (!element.select2Initialized) return;
                  $timeout(function() {
                    element.select2('destroy');
                    element.select2();
                  });
                };
          
                scope.$watch(attrs.ngModel, refreshSelect);
          
                if (attrs.ngOptions) {
                  var list = attrs.ngOptions.match(/ in ([^ ]*)/)[1];
                  // watch for option list change
                  scope.$watch(list, recreateSelect);
                }
          
                if (attrs.ngDisabled) {
                  scope.$watch(attrs.ngDisabled, refreshSelect);
                }
              }
            };
          }); */

    function CenviosCtrl($scope, $http){
        var obj = $scope
        obj.estados = [];
        obj.municipios = [];
        obj.envios = [];
        obj.editar = false;

        obj.send =  {
            opc:""
        }
       
        obj.btnNuevoEnvio = ()=>{
            obj.send = {};
            obj.editar = false;
            $("#Cenvios").modal('show');
        }

        obj.btnCrearEnvio = ()=>{
            mensaje = obj.editar? "Estas seguro de editar los costos de envio":"Estas seguro de crear el nuevo costo";
            if(confirm(mensaje)){
                obj.send.opc = obj.editar? "edit":"set";
                obj.sendData();
                $("#Cenvios").modal('hide');
            }
            
        }

        obj.btnDesactivar = (id)=>{
          if(confirm("Estas seguro de desactivar el costo")){
              obj.send.opc = "off";
              obj.send.id = id;
              obj.sendData();
          }
        }

        obj.getEstados = ()=>{
            obj.send.opc = "getEstados";
            obj.sendData();
        }

        obj.getEnvios = ()=>{
            obj.send.opc= "getEnvios";
            obj.sendData();
        }

        obj.btnEditar = (data)=>{
          obj.editar = true;
          obj.send = data;
          $("#Cenvios").modal('show'); 

        }

        obj.getMunicipios = ()=>{
            obj.send.opc = "getMunicipios";
            obj.sendData();
        }

        obj.sendData =  ()=>{
            $http({
                method: 'POST',
                    url: url,
                    data: obj.send,
                    
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        switch(obj.send.opc){
                            case 'getEstados':
                                    obj.estados = res.data.Estados;
                                    obj.envios = res.data.Envios;
                                    
                                       /*  $("#estados").select2({
                                            dropdownParent: $('#Cenvios')
                                        }); */
                                   
                            break;
                            case 'getMunicipios':
                                    obj.municipios = res.data.Data;
                            break;
                            case 'getEnvios':
                                obj.envios = res.data.Envios;
                            break;
                            case 'set':
                            case 'off':
                                obj.getEnvios();
                                toastr.success(res.data.mensaje);
                            break;
                            case 'edit':
                                toastr.success(res.data.mensaje);
                            break;
                        }
                    }else{
                        toastr.error(res.data.mensaje);
                    }
                }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }

        angular.element(document).ready(function(){
            obj.getEstados();
        });
    }
