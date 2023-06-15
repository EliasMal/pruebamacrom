CREATE DATABASE  IF NOT EXISTS `martipq1_puntoventarz` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `martipq1_puntoventarz`;
-- MySQL dump 10.13  Distrib 5.7.20, for Linux (x86_64)
--
-- Host: localhost    Database: martipq1_puntoventarz
-- ------------------------------------------------------
-- Server version	5.7.33-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `f4_c_usocfdi`
--

DROP TABLE IF EXISTS `f4_c_usocfdi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f4_c_usocfdi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_UsoCFDI` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Descripción` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Fisica` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Moral` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Fecha inicio de vigencia` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Fecha fin de vigencia` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f4_c_usocfdi`
--

LOCK TABLES `f4_c_usocfdi` WRITE;
/*!40000 ALTER TABLE `f4_c_usocfdi` DISABLE KEYS */;
INSERT INTO `f4_c_usocfdi` VALUES (1,'G01','Adquisición de mercancias','Sí','Sí','01/01/2017',''),(2,'G02','Devoluciones, descuentos o bonificaciones','Sí','Sí','01/01/2017',''),(3,'G03','Gastos en general','Sí','Sí','01/01/2017',''),(4,'I01','Construcciones','Sí','Sí','01/01/2017',''),(5,'I02','Mobilario y equipo de oficina por inversiones','Sí','Sí','01/01/2017',''),(6,'I03','Equipo de transporte','Sí','Sí','01/01/2017',''),(7,'I04','Equipo de computo y accesorios','Sí','Sí','01/01/2017',''),(8,'I05','Dados, troqueles, moldes, matrices y herramental','Sí','Sí','01/01/2017',''),(9,'I06','Comunicaciones telefónicas','Sí','Sí','01/01/2017',''),(10,'I07','Comunicaciones satelitales','Sí','Sí','01/01/2017',''),(11,'I08','Otra maquinaria y equipo','Sí','Sí','01/01/2017',''),(12,'D01','Honorarios médicos, dentales y gastos hospitalarios.','Sí','No','01/01/2017',''),(13,'D02','Gastos médicos por incapacidad o discapacidad','Sí','No','01/01/2017',''),(14,'D03','Gastos funerales.','Sí','No','01/01/2017',''),(15,'D04','Donativos.','Sí','No','01/01/2017',''),(16,'D05','Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).','Sí','No','01/01/2017',''),(17,'D06','Aportaciones voluntarias al SAR.','Sí','No','01/01/2017',''),(18,'D07','Primas por seguros de gastos médicos.','Sí','No','01/01/2017',''),(19,'D08','Gastos de transportación escolar obligatoria.','Sí','No','01/01/2017',''),(20,'D09','Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.','Sí','No','01/01/2017',''),(21,'D10','Pagos por servicios educativos (colegiaturas)','Sí','No','01/01/2017',''),(22,'P01','Por definir','Sí','Sí','31/03/2017','');
/*!40000 ALTER TABLE `f4_c_usocfdi` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-02-18 22:35:43
