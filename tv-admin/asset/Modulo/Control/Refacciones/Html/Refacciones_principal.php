<div ng-controller="RefaccionesCtrl">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fa fa-truck"></i> Refacciones</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?mod=home">Inicio</a></li>
                        <li class="breadcrumb-item active">Refacciones</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col lg 12">

                    <button type="button" class="btn btn-danger float-left" ng-click="btnAgregarRefaccion()"><i class="fa fa-plus-square"></i> Agregar Refaccion</button>
                    <span class="switch switch-sm float-left" style="margin-left:10px; margin-top:5px;">
                        <input type="checkbox" class="switch" id="swpublicados" ng-model="publicados" ng-click="getRefacciones()">
                        <label for="swpublicados">Publicados</label>
                        <input type="checkbox" class="switch" id="swhistorico" ng-model="historico" ng-click="getRefacciones()">
                        <label for="swhistorico">Historico</label>
                    </span>

                </div>

            </div>
            <hr />
            <div class="row">
                <div class="col-lg-12">
                    <input type="text" name="txtbuscar" id="txtbuscar" placeholder="Ingresa el codigo o nombre de la refaccion a buscar ...." 
                    class="form-control" ng-model="buscar" ng-model-options="{debounce:500}" ng-change="getRefacciones()"/>
                </div>
            </div>
            <button ng-click="clickRefaccionUnica()" id="buttonClaveUnica" ng-show="OneRefaccion.Clave" class="form-control buttonClave">Ir a Refacción con Clave: <b>{{OneRefaccion.Clave}}</b></button>
            <span ng-show="SinRefaccion" class="form-control spanClave">No hay refaccion con la clave: <b>{{SinRefaccion}}</b></span>
            <hr />
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>Refacciones encontradas: <b>{{Numreg}}</b></p>
                </div>
            </div>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{currentPage==0? 'disabled':''}}"  >
                        <a href="#" class="page-link" ng-click='lastPage()'> &laquo;</a>
                    </li>
                    <li class="page-item {{currentPage == (page.no - 1)? 'disabled':''}}" ng-repeat="page in pages">
                        <button type="button" class="page-link" ng-click='setPage(page.no)'>{{page.no}}</button>
                    </li>
                    <li class="page-item {{currentPage >= (Numreg/pageSize - 1)? 'disabled':''}}">
                        <button type='button' class='page-link' ng-click='nextPage()'>&raquo;</button>  
                    </li>
                </ul>
            </nav>
            <div class="filtros__contenedor">
                <button class="productoAZ" ng-click="DefaultRefacciones()">Producto A-Z</button>
                <button class="dateCreated" ng-click="sortby('dateCreated')">Ultimo Creado</button>
                <button class="dateModify" ng-click="sortby('dateModify')">Ultimo Modificado</button>
            </div>
            <div class="row ng-cloak" >
                <div class="col-md-3" ng-repeat="refaccion in refacciones" >
                    <div class="card card-danger card-outline enlace" ng-click="btnEditarRefaccion(refaccion._id)">
                        <div class="ribbon-wrapper ribbon-lg" ng-show="refaccion.RefaccionNueva || refaccion.RefaccionOferta 
                        || refaccion.RefaccionLiquidacion || refaccion.agotado">
                            <div class="ribbon bg-danger text-lg">{{
                                refaccion.RefaccionNueva? 'Nuevo':
                                refaccion.RefaccionOferta? 'Oferta':
                                refaccion.RefaccionLiquidacion? 'Liquidación':
                                refaccion.agotado? 'Agotado':''
                            }}</div>
                        </div>
                        <img ng-src="{{refaccion.imagen? dominio+'/images/refacciones/'+refaccion._id+'.png': dominio+'/images/refacciones/'+refaccion._id+'.webp'}}" alt="Imagen" class="card-img-top"/>
                        <div class="card-body ">
                            <span class="text-center card-title">{{refaccion.Producto}}</span>
                            <hr />
                            <span class="card-text">Clave: </span><b>{{refaccion.Clave}}</b><br />
                            <span class="card-text">Categoria: </span><b>{{refaccion.Categoria}}</b><br />
                            <span class="card-text">Vehiculo: </span><b>{{refaccion.Modelo}}</b><br />
                            <span class="card-text">Modelo :</span><b>{{refaccion.Anio}}</b><br />
                            <span class="card-text">No. Parte: </span> <b>{{refaccion.No_parte}}</b><br />
                            <section ng-hide="refaccion.RefaccionOferta">
                                <p class="card-text">Precio: <b class="precio"> {{refaccion.Precio1 | currency}} </b></p>
                            </section>
                            <section ng-show="refaccion.RefaccionOferta">
                                <p class="card-text">Precio: <b class="tachado"> {{refaccion.Precio1 | currency}} </b> a solo: <b class="precio text-danger">{{refaccion.Precio2 | currency}}</b></p>
                            </section>
                            <div class="enviogratis" ng-show="refaccion.Enviogratis" >
                                <img src="./Images/Icono-camion.png" alt="" >
                                <p class="text-danger"> <strong>Envío Gratis </strong> </p> 
                            </div>
                        </div>
                        <!-- <div class="card-body text-center">
                            <table class="table">
                                <thead>
                                    <th colspan="2"><span class="card-title">{{refaccion.Producto}}</span></th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td width="20%" class="card-text text-left">Clave:</td>
                                        <td width="80%" class="card-text text-left"><b>{{refaccion.Clave}}</b></td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="text-left">Categoria:</td>
                                        <td width="80%" class="text-left">{{refaccion.Categoria}}</td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="text-left">Vehiculo:</td>
                                        <td width="80%" class="text-left">{{refaccion.Modelo}}</td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="text-left">Modelo:</td>
                                        <td width="80%" class="text-left">{{refaccion.Anio}}</td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="text-left">No. parte:</td>
                                        <td width="80%" class="text-left">{{refaccion.Modelo}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> -->
                    </div>
                </div>
            </div>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{currentPage==0? 'disabled':''}}"  >
                        <a href="#" class="page-link" ng-click='lastPage()'> &laquo;</a>
                    </li>
                    <li class="page-item {{currentPage == (page.no - 1)? 'disabled':''}}" ng-repeat="page in pages">
                        <button type="button" class="page-link" ng-click='setPage(page.no)'>{{page.no}}</button>
                    </li>
                    <li class="page-item {{currentPage >= (refacciones.length/pageSize - 1)? 'disabled':''}}">
                        <button type='button' class='page-link' ng-click='nextPage()'>&raquo;</button>  
                    </li>
                </ul>
            </nav>
        </div>
    </section>
</div>