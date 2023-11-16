<div ng-controller="catalogosDetallesCtrl" ng-init="Refaccion.id={id}" class="ctrlmain global__content">
	<!-- breadcrumb -->
	<div class="bread-crumb bgwhite flex-w categorias__link">
		<a href="?mod=catalogo" class="s-text16">
			{{Refaccion.datos.Categoria}}
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</a>

		<span class="s-text16">
			{{Refaccion.datos.Marca}}
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</span>

		<span class="s-text16">
			{{Refaccion.datos.Modelo}}
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</span>

		<span class="s-text17">
			{{Refaccion.datos.Anio}}
		</span>
	</div>
	<!-- Product Detail -->
	<div class="contenedor contenido-principal bgwhite" id="MainDark">
		<div class="detalles">
			<div class="detalles__visual">
				<div class="detalles__visual--contenido">
					<div class="detalles__visual--opciones">
						<div ng-repeat="gal in Refaccion.galeria" data-thumb="{{getGaleria(gal._id)}}">
							<div class="detalles__visual--miniatura">
								<img src="{{getGaleria(gal._id)+'.webp'}}" alt="miniatura" class="secundaria">
							</div>
						</div>
						
					</div>
					
					<div class="">
						<div class="" data-thumb="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}">
							<div class="detalles__visual--producto">
								<img src="{{getImagen(Refaccion.datos.imagen, Refaccion.datos._id)}}" alt="IMG-PRODUCT" class="hero">
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="">
				<h4 class="product-detail-name m-text16">
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
									ng-show="btnEnabled" onclick="location.href='?mod=login'">
									Iniciar sesion
								</button>
							</div>
						</div>

						<div class="botones__sesion--iniciada" ng-show="!btnEnabled">
							<div class="agregarmas no-overflow">
								
								<button class="agregarmas__botones"
									ng-click="btndisminuir()">
									<i class="fa fa-minus" aria-hidden="true"></i>
								</button>

								<div class="agregarmas__contador num-product center--text">
									<span >{{Refaccion.cantidad}}</span>
								</div>

								<button class="agregarmas__botones"
									ng-click="btnaumentar();">
									<i class="fa fa-plus" aria-hidden="true"></i>
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

				<div ng-show="Refaccion.datos.Enviogratis">
					<div class="enviogratis">
						<img src="/images/icons/Icono-camion.png" alt="" >
						<p class="text-danger"> <strong>Envío Gratis a todo el país.</strong> </p> 
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

		<!-- Relate Product -->
		<section class="contenedor productos__relacionados">
			<div class="contenedor-contenido">
				<div class="sec-title">
					<h3 class="m-text5 t-center">
						Productos Relacionados
					</h3>
				</div>

				<div class="wrap-slick2">
					<div class="slick2">
						<div class="enlace m-1-r" ng-repeat="producto in productos">
							<div class="block2 block2__contenedor">
								<div class="block2-img wrap-pic-w of-hidden pos-relative cursorpnt" ng-style="{'background-color': producto.color}" ng-class="{
									'ribboagotado': producto.agotado ,
									'ribbonnuevo': producto.RefaccionNueva==1,
									'ribbonoferta': producto.RefaccionOferta==1,
									'pos-relative': !producto.agotado || producto.RefaccionNueva==0

									}">
									<a href="?mod=catalogo&opc=detalles&_id={{producto._id}}">
										<img ng-src="{{producto.imagen? 'images/refacciones/motor.webp':'images/refacciones/'+producto._id+'.webp'}}" alt="IMG-PRODUCT">
									</a>

								</div>
								<div class="block2-txt">
									<section class="descripcion-producto">
										<img ng-src="{{producto.imagenproveedor? 'images/Marcasrefacciones/' + producto.id_proveedor + '.png':'images/Marcasrefacciones/boxed-bg.jpg'}}" 
										alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}">
										<p class="block2-name dis-block s-text3">
											{{producto.Producto}}
										</p>

									</section>


									<span class="block2-price m-text6 text-white text-center">
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
</div>