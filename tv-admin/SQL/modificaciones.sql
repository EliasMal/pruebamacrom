#modificaciones a la tabla productos
ALTER TABLE `tsuruvo1_database`.`Producto` 
ADD COLUMN `Alto` FLOAT NOT NULL AFTER `Estatus`,
ADD COLUMN `Largo` FLOAT NOT NULL AFTER `Alto`,
ADD COLUMN `Ancho` FLOAT NOT NULL AFTER `Largo`,
ADD COLUMN `Peso` FLOAT NOT NULL AFTER `Ancho`;