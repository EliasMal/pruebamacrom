show databases;
use mysql;
show full tables;
select * from user;

select * from Cdirecciones where _id_cliente = 1;

select * from Producto where _id = 5362;
select * from Producto;

select * from Pedidos;
select * from DetallesPedidos;

INSERT INTO Producto (Clave, Producto, No_parte, _idCategoria,_idMarca, Modelo, Anios, Precio1, Precio2, Descripcion, RefaccionNueva, RefaccionOferta, Color, Estatus, Alto,Largo, Ancho, Peso, id_proveedor, tag_title, tag_alt,Enviogratis ) value ('16427','Reley de pruebas','123456','5','2','64','68','62','0','-','0','0','#aa0009',1,0,0,0,0,26,'',''
                    ,'0');
             
SELECT * FROM Cenvios;
SELECT 
    CE.precio
FROM
    Cenvios AS CE
        INNER JOIN
    CPmex AS CP ON (CP.D_mnpio = CE.Municipio)
WHERE
    CP.d_codigo = 77519 and CE.Estatus = 1
GROUP BY CE.precio;

select * from Cdirecciones where _id_cliente = 1;

SELECT 
   P._id, P.Clave, P.Producto, C.Categoria, M.Marca, V.Modelo,   
	A.Anio, P.Precio1, P.Precio2, P.No_parte, P.Estatus
FROM
    Producto AS P
        INNER JOIN
    Categorias AS C ON (C._id = P._idCategoria)
        INNER JOIN
    Marcas AS M ON (M._id = P._idMarca)
        INNER JOIN
    Modelos AS V ON (V._id = P.Modelo)
        INNER JOIN
    Anios AS A ON (A._id = P.Anios)
WHERE
    ((P.Producto LIKE '%mot%' AND P.Producto LIKE '%ju%' ) 
    OR 
    (P.Clave LIKE '%mot%' AND P.Clave LIKE '%ju%'  ))
        AND P.Estatus = 1
ORDER BY P.Producto