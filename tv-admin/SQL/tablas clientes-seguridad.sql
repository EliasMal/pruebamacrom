-- MySQL Workbench Synchronization
-- Generated: 2019-07-30 23:06
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Francisco

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `tsuruVolks`.`clientes` (
  `_id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombres` TEXT NOT NULL,
  `Apellidos` TEXT NOT NULL,
  `correo` TEXT NOT NULL,
  `Domicilio` TEXT NOT NULL,
  `Codigo_postal` TEXT NOT NULL,
  `ciudad` TEXT NOT NULL,
  `estado` TEXT NOT NULL,
  `telefono` TEXT NOT NULL,
  `FechaCreacion` DATE NOT NULL,
  `FechaModificacion` DATE NOT NULL,
  `ultimoacceso` DATE NOT NULL,
  `inicioacceso` DATE NOT NULL,
  `Estatus` TINYINT(1) NOT NULL,
  PRIMARY KEY (`_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `tsuruVolks`.`Cseguridad` (
  `_id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` TEXT NOT NULL,
  `password` TEXT NOT NULL,
  `FechaCreacion` DATE NOT NULL,
  `fechaModificacion` DATE NOT NULL,
  `Estatus` TINYINT(1) NOT NULL,
  `_id_cliente` INT(11) NOT NULL,
  PRIMARY KEY (`_id`),
  INDEX `fk_Cseguridad_1_idx` (`_id_cliente` ASC),
  CONSTRAINT `fk_Cseguridad_1`
    FOREIGN KEY (`_id_cliente`)
    REFERENCES `tsuruVolks`.`clientes` (`_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
