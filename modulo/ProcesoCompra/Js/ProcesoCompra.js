
var urlModel = "./modulo/ProcesoCompra/Model.php";

tsuruVolks.controller('ProcesoCompraCtrl', ProcesoCompraCtrl);
(function () {
    if($_SESSION["iduser"] == null){
        window.location.href = '?mod=home';
    }
})()
function ProcesoCompraCtrl($scope, $http) {
    var obj = $scope;
    obj.compras;

    obj.setAcreditado = async () => {
        try {
            const result = await $http({
                method: 'POST',
                url: urlModel,
                data: { modelo: { opc: "tarjeta" } },
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
                if (result.data.Bandera == 1) {
                    console.log("Compra Registrada");
                    if($_SESSION["datacc"] == ""){
                        location.reload();
                    }
                }
            }
            $scope.$apply();
        } catch (error) {
            toastr.error(error)
        }

    }
    
    const button = document.getElementById('button');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    let cx = ctx.canvas.width / 2;
    let cy = ctx.canvas.height / 2;

    let confetti = [];
    const confettiCount = 300;
    const gravityConfetti = 0.3;
    const dragConfetti = 0.075;
    const terminalVelocity = 3;

    const colors = [
        { front: '#7b5cff', back: '#6245e0' },
        { front: '#b3c7ff', back: '#8fa5e5' },
        { front: '#5c86ff', back: '#345dd1' },
        { front: '#FFFF33', back: '#FFCC00' },
        { front: '#FF33FF', back: '#CC33FF' }
    ];
    randomRange = (min, max) => Math.random() * (max - min) + min;

    initConfettoVelocity = (xRange, yRange) => {
        const x = randomRange(xRange[0], xRange[1]);
        const range = yRange[1] - yRange[0] + 1;
        let y = yRange[1] - Math.abs(randomRange(0, range) + randomRange(0, range) - range);
        if (y >= yRange[1] - 1) {
            y += (Math.random() < .25) ? randomRange(1, 3) : 0;
        }
        return { x: x, y: -y };
    }

    function Confetto() {
        this.randomModifier = randomRange(0, 99);
        this.color = colors[Math.floor(randomRange(0, colors.length))];
        this.dimensions = {
            x: randomRange(5, 9),
            y: randomRange(8, 15),
        };
        this.position = {
            x: randomRange(canvas.width - button.offsetWidth / 2, canvas.width / 12),
            y: randomRange(canvas.height + button.offsetHeight / 2, canvas.height / 8),
        };
        this.rotation = randomRange(0, 2 * Math.PI);
        this.scale = {
            x: 1,
            y: 1,
        };
        this.velocity = initConfettoVelocity([-9, 9], [6, 11]);
    }

    Confetto.prototype.update = function () {
        this.velocity.x -= this.velocity.x * dragConfetti;
        this.velocity.y = Math.min(this.velocity.y + gravityConfetti, terminalVelocity);
        this.velocity.x += Math.random() > 0.5 ? Math.random() : -Math.random();
        this.position.x += this.velocity.x;
        this.position.y += this.velocity.y;

        this.scale.y = Math.cos((this.position.y + this.randomModifier) * 0.09);
    }

    initBurst = () => {
        for (let i = 0; i < confettiCount; i++) {
            confetti.push(new Confetto());
        }
    }

    render = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        confetti.forEach((confetto, index) => {
            let width = (confetto.dimensions.x * confetto.scale.x);
            let height = (confetto.dimensions.y * confetto.scale.y);
            ctx.translate(confetto.position.x, confetto.position.y);
            ctx.rotate(confetto.rotation);

            confetto.update();

            ctx.fillStyle = confetto.scale.y > 0 ? confetto.color.front : confetto.color.back;
            ctx.fillRect(-width / 2, -height / 2, width, height);
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            if (confetto.velocity.y < 0) {
                ctx.clearRect(canvas.width / 2 - button.offsetWidth / 2, canvas.height / 2 + button.offsetHeight / 2, button.offsetWidth, button.offsetHeight);
            }
        })

        confetti.forEach((confetto, index) => {
            if (confetto.position.y >= canvas.height) confetti.splice(index, 1);
        });

        window.requestAnimationFrame(render);
    }

    resizeCanvas = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        cx = ctx.canvas.width / 2;
        cy = ctx.canvas.height / 2;
    }

    window.addEventListener('resize', () => {
        resizeCanvas();
    });
    render();

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
                    case 'Compras':
                        obj.compras = res.data.Data;
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
        obj.getBanners({ opc: "get", Categoria: "Compras", Estatus: 1 });
        if (window.location.href.includes("?mod=ProcesoCompra&opc=paso3")) {
            if($_SESSION["padlock"] != "lock"){
                console.log("Pago Efectivo");
                setTimeout(() => {
                    window.initBurst();
                }, 1500);
            }
        }
        if (window.location.href.includes("?mod=ProcesoCompra&opc=cc?")) {
            if($_SESSION["padlock"] != "lock"){
                obj.setAcreditado();
                console.log("Pago con tarjeta");
                setTimeout(() => {
                    window.initBurst();
                }, 1500);
            }
        }

    });
}