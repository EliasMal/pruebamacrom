<div ng-controller="catalogosDetallesCtrl" ng-init="Refaccion.id={id}" class="ctrlmain global__content contenedor">
	<!-- breadcrumb -->
	<div class="bread-crumb bgwhite flex-w categorias__link">
		<a href="?mod=catalogo&pag=1&prod=&cate={{Refaccion.datos._idCategoria}}&armadora=&mdl=&[a]=" class="s-text16">
			{{Refaccion.datos.Categoria}}
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</a>

		<a href="?mod=catalogo&pag=1&prod=&cate={{Refaccion.datos._idCategoria}}&armadora={{Refaccion.datos._idMarca}}&mdl=&[a]=" class="s-text16">
			{{Refaccion.datos.Marca}}
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</a>

		<a href="?mod=catalogo&pag=1&prod=&cate={{Refaccion.datos._idCategoria}}&armadora={{Refaccion.datos._idMarca}}&mdl={{Refaccion.datos._idModelo}}&[a]=" class="s-text16">
			{{Refaccion.datos.Modelo}}
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</a>

		<a href="?mod=catalogo&pag=1&prod=&cate={{Refaccion.datos._idCategoria}}&armadora={{Refaccion.datos._idMarca}}&mdl={{Refaccion.datos._idModelo}}&[a]={{Refaccion.datos.Anios}}" class="s-text17">
			{{Refaccion.datos.Anio}}
		</a>
	</div>
	<!-- Product Detail -->
	<div class="contenido-principal bgwhite" id="MainDark">
		<div class="detalles">
			<div class="detalles__visual">
				<div class="detalles__visual--contenido">
					<div class="detalles__visual--opciones">
						<div ng-repeat="gal in Refaccion.galeria" data-thumb="{{getGaleria(gal._id)}}">
							<div class="detalles__visual--miniatura">
								<!-- <img ng-src="{{getGaleria(gal._id)+'.webp'}}" alt="miniatura" class="secundaria"> -->
								<img ng-src="{{getGaleria(gal._id)+'.webp'}}" alt="miniatura" class="secundaria">
							</div>
						</div>
					</div>
					
					<div class="detalles__visual--imagen">
						<div class="" data-thumb="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}">
							<div class="detalles__visual--producto">
								<!-- <img ng-src="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}" alt="IMG-PRODUCT" class="hero"> -->
								<img ng-src="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}" alt="IMG-PRODUCT" class="hero">
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="detalles__informacion justify--text">
				<div class="avisos__productos">
					<span class="promocion__aviso" ng-show="Refaccion.datos.RefaccionOferta">promoción</span>
					<span class="enviogratis__aviso" ng-show="Refaccion.datos.Enviogratis">envío gratis</span>
				</div>

				<div class="detalles__informacion--titulares">
					<h4 class="product-detail-name">{{Refaccion.datos.Producto}}</h4>
					<h5 class="product-detail-SKU">SKU: {{Refaccion.datos.No_parte}}</h5>
				</div>
				

				<!-- Descripciones mobile y desktop -->
				<div class="descripcion">
					<!-- {{Refaccion}} -->
					<p>Agencia: <strong>{{Refaccion.datos.Marca}}</strong></p>
					<p>Vehiculo: <strong>{{Refaccion.datos.Modelo}}</strong></p>
					<p>Modelo: <strong>{{Refaccion.datos.Anio}}</strong></p>
					<p>Articulos en Existencia: <strong class="{{Refaccion.Existencias==0? 'text-danger':''}}">{{Refaccion.Existencias!=0?Refaccion.Existencias:"Agotado"}}</strong></p>
					<p>Categoria: <strong>{{Refaccion.datos.Categoria}}</strong></p>
					<p>Marca: <strong>{{Refaccion.datos.Proveedor}}</strong></p>
					<p>Clave: <strong>{{Refaccion.datos.Clave}}</strong></p>
				</div>
				<div class="descripcion__mobile">
					<p>Agencia: </p>
					<p><strong>{{Refaccion.datos.Marca}}</strong></p>
					<p>Vehiculo: </p>
					<p><strong>{{Refaccion.datos.Modelo}}</strong></p>
					<p>Modelo: </p>
					<p><strong>{{Refaccion.datos.Anio}}</strong></p>
					<p>Articulos en Existencia: </p>
					<p><strong class="{{Refaccion.Existencias==0? 'text-danger':''}}">{{Refaccion.Existencias!=0?Refaccion.Existencias:"Agotado"}}</strong></p>
					<p>Categoria: </p>
					<p><strong>{{Refaccion.datos.Categoria}}</strong></p>
					<p>Marca: </p>
					<p><strong>{{Refaccion.datos.Proveedor}}</strong></p>
					<p>Clave: </p>
					<p><strong>{{Refaccion.datos.Clave}}</strong></p>
				</div>
				<div class="contenedor__botonesesion">

					<div class="botones__sesion" ng-show="Activa">
						<div class="botones__sesion--noiniciada">
							<div class="btn-addcart-product-detail iniciarsesion__contenedor">
								<!-- Button -->
								<button class="btn btn-danger iniciarsesion__contenedor--boton form-control"
									ng-show="btnEnabled" onclick="location.href='?mod=login'">
									Iniciar sesion
								</button>
							</div>
						</div>

						<div class="botones__sesion--iniciada" ng-show="!btnEnabled">
							<span class="m-text17" ng-hide="Refaccion.datos.RefaccionOferta">
								{{Refaccion.precio | currency}} <small class="m-text15"> IVA incluido</small>
							</span>
							<span class="m-text17" ng-show="Refaccion.datos.RefaccionOferta">
								<small class="line_through-red">{{Refaccion.precio | currency}}</small>
								{{Refaccion.datos.Precio2 | currency}} <small class="m-text15"> IVA incluido</small>
							</span>
							<div class="agregarmas no-overflow">
								<button class="agregarmas__botones b-radius-left"
									ng-click="btndisminuir()">
									<i class="fa fa-minus" aria-hidden="true"></i>
								</button>

								<div class="agregarmas__contador num-product center--text">
									<span >{{Refaccion.cantidad}}</span>
								</div>

								<button class="agregarmas__botones b-radius-right"
									ng-click="btnaumentar();">
									<i class="fa fa-plus" aria-hidden="true"></i>
								</button>
							</div>
						</div>
						<div class="btn-addcart-product-detail agregarcarrito__contenedor" ng-show="!btnEnabled">
								<!-- Button -->
								<button class="btn btn-danger form-control agregarcarrito__contenedor--boton"
									ng-click="Agregarcarrito()">
									Agregar al carrito
								</button>
						</div>
					</div>

					<div class="agotado__contenedor" ng-show="!Activa">
						<div class="agotado__texto">
							<h1 class="text-danger"><strong>Agotado</strong></h1>
						</div> 
					</div>

				</div>

				<!--  --> 
				<div class="wrap-dropdown-content active-dropdown-content descripcion__texto">
					<h5 class="js-toggle-dropdown-content flex-sb-m cs-pointer m-text19 color0-hov trans-0-4">
						Descripci&oacute;n
						<i class="down-mark color1 fa fa-minus dis-none" aria-hidden="true"></i>
						<i class="up-mark color1 fa fa-plus" aria-hidden="true"></i>
					</h5>

					<div class="dropdown-content dis-none">
						<p class="s-text8">
							{{Refaccion.datos.Descripcion}}
						</p>
					</div>
				</div>

				
			</div>
		</div>
		<!-- Compatibilidad -->
		<section ng-show="Refaccion.compatibilidad.length != 0">
			<div class="contenedor productos__compatibilidad">
				<div class="contenedor-contenido">
					<div class="compatibilidad__titulo">
						<h3 class="m-text5 t-center">
							Compatibilidad con otros Vehiculos
						</h3>
					</div>
				<div class="table-responsive">
					<table class="table table-striped">
                        <thead>
                            <tr class="datos__compatibilidad center--text">
                                <th class="text-center">Marca</th>
                                <th class="text-center">Modelo</th>
                                <th class="text-center">Desde</th>
                                <th class="text-center">Hasta</th>
								<th class="text-center">Motor</th>
                                <th class="text-center">Transmision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="com in Refaccion.compatibilidad" class="info__compatibilidad center--text">
                                <td>{{com.Marca}}</td>
                                <td>{{com.Modelo}}</td>
                                <td>{{com.ainicial}}</td>
                                <td class="text-center">{{com.afinal}}</td>
								<td class="text-center">{{com.motor}}</td>
                                <td class="text-center">{{com.transmision}}</td>
                            </tr>
        
                        </tbody>
                    </table>
				</div>

				</div>
			</div>
		</section>

		<!-- Related Product -->
		<section class="contenedor productos__relacionados">
			<div class="contenedor-contenido">
				<div class="sec-title">
					<h3 class="m-text5">
						Productos Relacionados
					</h3>
				</div>

				<div class="wrap-slick2">
					<div class="slick2">
						<div class="enlace m-1-r" ng-repeat="producto in productos">
							<div class="block2 block2__contenedor">
								<div class="block2-img wrap-pic-w of-hidden pos-relative cursorpnt" ng-class="{
									'ribboagotado': producto.agotado ,
									'ribbonnuevo': producto.RefaccionNueva==1,
									'pos-relative': !producto.agotado || producto.RefaccionNueva==0

									}">
									<a href="?mod=catalogo&opc=detalles&_id={{producto._id}}">
										<img ng-src="{{producto.imagen? 'https://macromautopartes.com/images/refacciones/motor.webp':'https://macromautopartes.com/images/refacciones/'+producto._id+'.webp'}}" alt="IMG-PRODUCT">
										<!-- <img ng-src="{{producto.imagen? 'images/refacciones/motor.webp':'images/refacciones/'+producto._id+'.webp'}}" alt="IMG-PRODUCT"> -->
									</a>
								</div>
								<div class="block2-txt">
									<div class="avisos__productos">
										<span class="promocion__aviso" ng-show="producto.RefaccionOferta =='1'">promoción</span>
										<span class="enviogratis__aviso" ng-show="producto.Enviogratis">envío gratis</span>
									</div>
									<a href="?mod=catalogo&opc=detalles&_id={{producto._id}}" class="block2-name dis-block s-text3">{{producto.Producto}}</a>
									<section class="descripcion-producto-refa">
										<p class="precio__producto" ng-hide="producto.RefaccionOferta=='1'">{{producto.Precio1 | currency}}</p>	
										<p class="precio__producto" ng-show="producto.RefaccionOferta=='1'"><small class="line_through-red">{{producto.Precio1 | currency}}</small>
										{{producto.Precio2 | currency}}</p>
										<img ng-src="{{producto.imagenproveedor? 'https://macromautopartes.com/images/Marcasrefacciones/boxed-bg.jpg':'https://macromautopartes.com/images/Marcasrefacciones/' + producto.id_proveedor + '.png'}}" 
										alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}">
										<!-- <img ng-src="{{producto.imagenproveedor? 'images/Marcasrefacciones/' + producto.id_proveedor + '.png':'images/Marcasrefacciones/boxed-bg.jpg'}}" 
										alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}"> -->

									</section>
									
								</div>
								
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

	</div>
</div>