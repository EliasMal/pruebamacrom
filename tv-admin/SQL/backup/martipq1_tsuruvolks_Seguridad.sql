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
-- Table structure for table `Seguridad`
--

DROP TABLE IF EXISTS `Seguridad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Seguridad` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `Tipo_usuario` text NOT NULL,
  `FechaCreacion` datetime NOT NULL,
  `FechaModificacion` datetime NOT NULL,
  `USRCreacion` text NOT NULL,
  `USRModificacion` text NOT NULL,
  `_idUsuarios` int(11) NOT NULL,
  `Estatus` tinyint(1) NOT NULL,
  PRIMARY KEY (`_id`),
  KEY `fk_Seguridad_1_idx` (`_idUsuarios`),
  CONSTRAINT `fk_Seguridad_1` FOREIGN KEY (`_idUsuarios`) REFERENCES `Usuarios` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Seguridad`
--

LOCK TABLES `Seguridad` WRITE;
/*!40000 ALTER TABLE `Seguridad` DISABLE KEYS */;
INSERT INTO `Seguridad` VALUES (1,'root','4f4f92245c170bd04249570993365d415a08577f','root','2019-05-23 18:09:05','2019-05-23 18:09:05','SYS','SYS',1,1),(2,'Admin','8cb2237d0679ca88db6464eac60da96345513964','Admin','2019-05-23 18:09:05','2019-05-23 18:09:05','SYS','SYS',2,1);
/*!40000 ALTER TABLE `Seguridad` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-07 19:04:22
