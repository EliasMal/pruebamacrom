<?php

class CacheInvalidator
{
    private $catalogo;

    public function __construct()
    {
        $this->catalogo = new Catalogo();
    }

    /**
     * Producto editado (precio, stock, estado)
     */
    public function productoActualizado(int $idProducto, ?int $idMarca = null, ?int $idModelo = null): void
    {
        $this->catalogo->invalidateByTag("producto:$idProducto");

        if ($idMarca) {
            $this->catalogo->invalidateByTag("marca:$idMarca");
        }

        if ($idModelo) {
            $this->catalogo->invalidateByTag("modelo:$idModelo");
        }
    }

    /**
     * Oferta creada / eliminada / modificada
     */
    public function ofertasActualizadas(): void
    {
        $this->catalogo->invalidateByTag('ofertas');
    }

    /**
     * Producto nuevo
     */
    public function productoCreado(int $idMarca, int $idModelo): void
    {
        $this->catalogo->invalidateByTag('nuevos');
        $this->catalogo->invalidateByTag("marca:$idMarca");
        $this->catalogo->invalidateByTag("modelo:$idModelo");
    }
}
