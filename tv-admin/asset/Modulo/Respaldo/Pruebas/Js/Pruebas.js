const url = "https://api-demo.skydropx.com/v1/quotations"
const url2 = "https://api-demo.skydropx.com/v1/shipments";
const token = "Token token=SuOZQz5IrqceQbJmBqQfAo4PMQvNKMCh2PtXOKMfKM0t";

tsuruVolks
        .controller('PruebasCrtl',["$scope","$http",PruebasCrtl]);

function PruebasCrtl($scope, $http){
    let obj = $scope;

    obj.btnPruebas = async()=>{
        try {
            const result = await $http({
                method: 'GET',
                    url: url2,
                    headers:{
                        'Authorization': token,
                        'Content-Type':  "application/json"                    
                    }
                }).then(function successCallback(res){
                    return res
                    
                }, function errorCallback(res){
                    console.error(res)
                    console.log("Error: no se realizo la conexion con el servidor");
                    
            });
            console.log(result.data);    
        } catch (error) {
            console.log(error)
        }
        
    }

    obj.btnCotizacion = async()=>{
        try {
            let data = { 
                "zip_from": "28000", 
                "zip_to": "60174", 
                "parcel": { 
                    "weight": "1", //peso
                    "height": "10", //altura
                    "width": "10", //ancho
                    "length": "10" } //largo
                }
            const result = await $http({
                method: 'POST',
                    url: url,
                    data: data,
                    headers:{
                        'Authorization': token,
                        'Content-Type':  "application/json"                    
                    }
                }).then(function successCallback(res){
                    return res
                    
                }, function errorCallback(res){
                    console.error(res)
                    console.log("Error: no se realizo la conexion con el servidor");
                    
            }); 
            console.log(result);
        } catch (error) {
            
        }
    }
}
