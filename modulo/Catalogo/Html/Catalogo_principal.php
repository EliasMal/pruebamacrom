<div ng-controller="catalogosCtrl" ng-init="refaccion.categoria='{categoria}'" class="ctrlmain global__content">
	<!-- Title Page -->
	<header class="header">
		<section class="header__contenido" ng-repeat="cat in catalogos.Escritorio">
		<picture class="header__picture">
						<source srcset="images/Banners/CATALOOG.webp" type="image/webp" class="header__picture--img">
						<img loading="lazy" src="images/Banners/{{banner.imagen}}" alt="banner">
				</picture>
		</section>
	</header>
	<!-- Content page -->
	<main class="bgblack main__catalogo--principal">
		<div class="container-fluid">
			<div class="contenedor__catalogo">
				<div class="catalogo">
					
					<div class="catalogo__filtrado">

						<div class="catalogo__filtrado--opciones">

							<label for="txtMarca" class="text-white">Armadora:</label>
							<select name="txtMarca" id="txtMarca" class="form-control" ng-model="refaccion.marca"
								ng-change="getVehiculos(true)">
								<optgroup label="Selecciona la Marca">
									<option value="T">-- Todas --</option>
									<option ng-repeat="marca in Marcas" value="{{marca._id}}">{{marca.Marca}}</option>
								</optgroup>
							</select>

							<label for="txtVehiculo" class="text-white">Vehiculo</label>
							<select name="txtVehiculo" id="txtVehiculo" class="form-control"
								ng-model="refaccion.vehiculo" ng-change="getModelos()">
								<optgroup label="Selecciona el Vehiculo">
									<option value="">-- Todas --</option>
									<option ng-repeat="vehiculo in Vehiculos" value="{{vehiculo._id}}">{{vehiculo.Modelo}}</option>
								</optgroup>
							</select>

							<label for="txtModelo" class="text-white">Modelo</label>
							<select name="txtModelo" id="txtModelo" class="form-control" ng-model="refaccion.anio"
								ng-change="getAnios()">
								<optgroup label="Selecciona el Modelo">
									<option value="">-- Todas --</option>
									<option ng-repeat="modelo in Modelos" value="{{modelo._id}}">{{modelo.Anio}}
									</option>
								</optgroup>
							</select>

							<label for="txtCategoria" class="text-white">Categorias:</label>
							<select name="txtCategoria" id="txtCategoria" class="form-control"
								ng-model="refaccion.categoria" ng-change="getCategorias(true)">
								<optgroup label="Selecciona una categoria">
									<option value="T">--Todas--</option>
									<option ng-repeat="cat in categorias" value="{{cat._id}}">{{cat.Categoria}}</option>
								</optgroup>
							</select>

						</div>

						<div class="search-product pos-relative catalogo__filtrado--buscador form-control">
							<input type="text" name="search-product"
								placeholder="Buscar Producto..." ng-model="refaccion.producto"
								ng-model-options="{debounce:500}" ng-change="getRefaccion()">

							<button class="ab-r-m">
								<i class="fa fa-search" aria-hidden="true"></i>
							</button>
						</div>

					</div>

				</div>

				<div class="">
					<!--  -->
					<div class="flex-sb-m flex-w Trefacciones__cont">
						<div class="flex-w">

						</div>

						<span class="s-text8 text-white">
							Refacciones Encontradas: {{Trefacciones}} resultados
						</span>
					</div>
					
					<!-- Product -->
					<div class="row refacciones__productos">

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 half-w producto__contenedor" ng-repeat="producto in Refacciones">
							<!-- Block2 -->
							<div class="block2 ">

								<div ng-click="RefaccionDetalles(producto._id)" class="block2-img enlace wrap-pic-w of-hidden
									{{producto.RefaccionOferta==1? 'block2-labelsale':''}}"
									ng-class="{
											'ribboagotado': producto.agotado ,
											'ribbonnuevo': producto.RefaccionNueva==1,
											'ribbonoferta': producto.RefaccionOferta,
											'pos-relative': !producto.agotado || producto.RefaccionNueva==0

										}"
									ng-style="{'background-color': producto.color}">
									<img ng-src="{{producto.imagen? 'images/refacciones/'+producto._id+'.png':'images/refacciones/'+producto._id+'.webp'}}"
										alt="{{producto.tag_alt}}" title = "{{producto.tag_title}}">

								</div>
								
								<div class="block2-txt">

									<section class="descripcion-producto">
										<img ng-src="{{producto.imagenproveedor? 'images/Marcasrefacciones/' + producto.id_proveedor + '.png':'images/Marcasrefacciones/boxed-bg.jpg'}}" 
										alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}">
										<p class="block2-name dis-block s-text3 text-white">
											{{producto.Producto}}
										</p>
									</section>
									
									<span class="block2-price m-text6 text-white text-center">
										<h3 ng-click="RefaccionDetalles(producto._id)" class="precio enlace">{{producto.RefaccionOferta? producto.Precio2:producto.Precio1 | currency}}</h3>
									</span>

									<div class="enviogratis" ng-show="producto.Enviogratis">
										<img src="/images/icons/Icono-camion.png" alt="" >
										<p class="text-white"> <strong>Envío Gratis </strong> </p> 
									</div>

								</div>
							</div>
						</div>

					</div>

					<script type="text/javascript">function toTop() {window.scrollTo(0, 0)} </script>
					<!-- Pagination -->
					<!-- <div style="display:grid;justify-items: center;grid-template-columns:repeat(2,1fr);">
						<span class="bg9 center--text" style="width:15rem;cursor:pointer;color:var(--blanco);" ng-click="mostrarmas()">Mostrar 20 más</span>
						<span class="bg9 center--text" style="width:15rem;cursor:pointer;color:var(--blanco);" ng-click="mostrarmenos()">Mostrar 20 menos</span>
					</div> -->

					<div class="pagination">
						<a onClick="toTop()" class="item-pagination flex-c-m trans-0-4 {{currentPage == (pag.no - 1)? 'active-pagination':''}}"
							ng-repeat='pag in pages' ng-click='setPage(pag.no)'>{{pag.no}}</a>
					</div>
					
				</div>
			</div>
		</div>
	</main>

</div>