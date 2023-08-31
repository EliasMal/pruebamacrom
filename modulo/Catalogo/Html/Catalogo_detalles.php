<div ng-controller="catalogosDetallesCtrl" ng-init="Refaccion.id={id}" class="ctrlmain global__content">
	<!-- breadcrumb -->
	<div class="bread-crumb bgwhite flex-w p-l-52 p-r-15 p-t-30 p-l-15-sm">
		<a href="?mod=catalogo" class="s-text16">
			{{Refaccion.datos.Categoria}}
			<i class="fa fa-angle-right m-l-8 m-r-9" aria-hidden="true"></i>
		</a>

		<span class="s-text16">
			{{Refaccion.datos.Marca}}
			<i class="fa fa-angle-right m-l-8 m-r-9" aria-hidden="true"></i>
		</span>

		<span class="s-text16">
			{{Refaccion.datos.Modelo}}
			<i class="fa fa-angle-right m-l-8 m-r-9" aria-hidden="true"></i>
		</span>

		<span class="s-text17">
			{{Refaccion.datos.Anio}}
		</span>
	</div>
	<!-- Product Detail -->
	<div class="contenedor contenido-principal">
		<div class="detalles">
			<div class="detalles__visual">
				<div class="detalles__visual--contenido">
					<div class="detalles__visual--opciones">
						<div ng-repeat="gal in Refaccion.galeria" data-thumb="{{getGaleria(gal._id)}}">
							<div class="detalles__visual--miniatura">
								<img src="{{getGaleria(gal._id)+'.webp'}}" alt="miniatura" class="secundaria">
		
							</div>
						</div>
						<!-- <img src="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}" alt="" class="secundaria"> -->
						
					</div>
					<div class="">
						<div class="" data-thumb="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}">
							<div class="detalles__visual--producto">
								<img src="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}" alt="IMG-PRODUCT" id="principal">
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="p-t-30">
				<h4 class="product-detail-name m-text16 p-b-13">
					{{Refaccion.datos.Producto}}
				</h4>

				<span class="m-text17" ng-hide="Refaccion.datos.RefaccionOferta">
					{{Refaccion.precio | currency}} <small class="m-text15"> IVA incluido</small>
				</span>
				<section ng-show="Refaccion.datos.RefaccionOferta">
					<h3 class="text-danger text-center">¡Articulo en oferta!</h3>
					<span style="text-decoration:line-through;">
						De {{Refaccion.precio | currency}}
					</span><br>
					<span class="m-text17 text-danger">
						a solo {{Refaccion.datos.Precio2 | currency}} <small class="m-text15"> IVA incluido</small>
					</span>
				</section>

				<!--  -->
				<div class="contenedor__botonesesion">

					<div class="botones__sesion" ng-show="Activa">
						<div class="botones__sesion--noiniciada">
							<div class="btn-addcart-product-detail iniciarsesion__contenedor">
								<!-- Button -->
								<button class="btn btn-danger iniciarsesion__contenedor--boton form-control"
									ng-show="btnEnabled" ng-click="btnInciarSession()">
									Iniciar sesion
								</button>
							</div>
						</div>

						<div class="botones__sesion--iniciada" ng-show="!btnEnabled">
							<div class="agregarmas no-overflow">
								
								<button class="agregarmas__botones"
									ng-click="btndisminuir()">
									<i class="fs-12 fa fa-minus" aria-hidden="true"></i>
								</button>

								<div class="agregarmas__contador num-product center--text">
									<span >{{Refaccion.cantidad}}</span>
								</div>

								<button class="agregarmas__botones"
									ng-click="btnaumentar();">
									<i class="fs-12 fa fa-plus" aria-hidden="true"></i>
								</button>
							</div>

							<div class="btn-addcart-product-detail agregarcarrito__contenedor">
								<!-- Button -->
								<button class="btn btn-danger form-control agregarcarrito__contenedor--boton"
									ng-click="Agregarcarrito()">
									Agregar al carrito
								</button>
							</div>
						</div>
					</div>

					<div class="agotado__contenedor" ng-show="!Activa">
						<div class="agotado__texto">
							<h1 class="text-danger"><strong>Agotado</strong></h1>
						</div> 
					</div>

				</div>

				<div class="descripcion">
					<p>No. Parte: </p>
					<p><strong>{{Refaccion.datos.No_parte}}</strong></p>
					<p>Articulos en Existencia: </p>
					<p><strong class="{{Refaccion.Existencias==0? 'text-danger':''}}">{{Refaccion.Existencias!=0?Refaccion.Existencias:"Agotado"}}</strong></p>
					<p>Categoria: </p>
					<p><strong>{{Refaccion.datos.Categoria}}</strong></p>
					<p>Marca del Vehiculo: </p>
					<p><strong>{{Refaccion.datos.Marca}}</strong></p>
					<p>Vehiculo: </p>
					<p><strong>{{Refaccion.datos.Modelo}}</strong></p>
					<p>Modelo: </p>
					<p><strong>{{Refaccion.datos.Anio}}</strong></p>
				</div>

				<div class="p-b-45" ng-show="Refaccion.datos.Enviogratis">
					<div class="enviogratis">
						<img src="/images/icons/Icono-camion.png" alt="" >
						<p class="text-danger"> <strong>Envío Gratis a todo el país.</strong> </p> 
					</div>
				</div>
				<!--  --> 
				<div class="wrap-dropdown-content active-dropdown-content descripcion__texto">
					<h5 class="js-toggle-dropdown-content flex-sb-m cs-pointer m-text19 color0-hov trans-0-4">
						Descripci&oacute;n
						<i class="down-mark fs-12 color1 fa fa-minus dis-none" aria-hidden="true"></i>
						<i class="up-mark fs-12 color1 fa fa-plus" aria-hidden="true"></i>
					</h5>

					<div class="dropdown-content dis-none p-t-15 p-b-23">
						<p class="s-text8">
							{{Refaccion.datos.Descripcion}}
						</p>
					</div>
				</div>

				
			</div>
		</div>
	</div>

	<!-- Relate Product -->
	<section class="contenedor bgwhite p-t-45 p-b-138">
		<div class="contenedor-contenido">
			<div class="sec-title p-b-60">
				<h3 class="m-text5 t-center">
					Productos Relacionados
				</h3>
			</div>

			<div class="wrap-slick2">
				<div class="slick2">
					<div class="enlace" ng-repeat="producto in productos">
						<div class="block2 block2__contenedor" ng-click="RefaccionDetalles(producto._id)">
							<div class="block2-img wrap-pic-w of-hidden pos-relative cursorpnt" ng-style="{'background-color': producto.color}" ng-class="{
								'ribboagotado': producto.agotado ,
								'ribbonnuevo': producto.RefaccionNueva==1,
								'ribbonoferta': producto.RefaccionOferta==1,
								'pos-relative': !producto.agotado || producto.RefaccionNueva==0

								}">
								<img ng-src="{{producto.imagen? 'images/refacciones/motor.webp':'images/refacciones/'+producto._id+'.webp'}}" alt="IMG-PRODUCT">

							</div>
							<div class="block2-txt p-t-20">
								<section class="descripcion-producto">
									<img ng-src="{{producto.imagenproveedor? 'images/Marcasrefacciones/' + producto.id_proveedor + '.png':'images/Marcasrefacciones/boxed-bg.jpg'}}" 
									alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}">
									<p class="block2-name dis-block s-text3 p-b-5">
										{{producto.Producto}}
									</p>
									
								</section>
								

								<span class="block2-price m-text6 p-r-5 p-t-5 text-white text-center">
									<h3 class="precio">{{producto.Precio1 | currency}}</h3>
								</span>
								<div class="enviogratis"  ng-show="producto.Enviogratis">
									<img src="/images/icons/Icono-camion.png" alt="">
									<p > <strong>Envío Gratis </strong> </p> 
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</section>
</div>