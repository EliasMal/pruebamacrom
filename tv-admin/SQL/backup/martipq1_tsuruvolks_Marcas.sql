-- MySQL dump 10.13  Distrib 5.6.19, for linux-glibc2.5 (x86_64)
--
-- Host: martinezpapeleria.com    Database: martipq1_tsuruvolks
-- ------------------------------------------------------
-- Server version	5.6.41-84.1

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
-- Table structure for table `Marcas`
--

DROP TABLE IF EXISTS `Marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Marcas` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `Marca` text NOT NULL,
  `Estatus` tinyint(1) NOT NULL,
  `USRCreacion` text NOT NULL,
  `USRModificacion` text NOT NULL,
  `FechaCreacion` date NOT NULL,
  `FechaModificacion` date NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Marcas`
--

LOCK TABLES `Marcas` WRITE;
/*!40000 ALTER TABLE `Marcas` DISABLE KEYS */;
INSERT INTO `Marcas` VALUES (1,'Nissan',1,'root','root','2019-06-11','2019-06-11'),(2,'Volkswagen',1,'root','root','2019-06-11','2019-06-11'),(3,'Chevrolet',1,'root','root','2019-06-11','2019-06-11'),(4,'Audi',1,'Admin','Admin','2019-06-25','2019-06-25'),(5,'Seat',1,'Admin','Admin','2019-06-25','2019-06-25'),(6,'Hyundai',1,'Admin','Admin','2019-06-25','2019-06-25'),(7,'Peugeot',1,'Admin','Admin','2019-06-25','2019-06-25'),(8,'Renault',1,'Admin','Admin','2019-06-25','2019-06-25'),(9,'Suzuki',1,'Admin','Admin','2019-06-25','2019-06-25'),(10,'Accesorios',1,'Admin','Admin','2019-07-01','2019-07-01');
/*!40000 ALTER TABLE `Marcas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-07 19:04:27
