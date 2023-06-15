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
-- Table structure for table `Modelos`
--

DROP TABLE IF EXISTS `Modelos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Modelos` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `Modelo` text NOT NULL,
  `Estatus` tinyint(1) NOT NULL,
  `_idMarca` int(11) NOT NULL,
  `USRCreacion` text NOT NULL,
  `USRModificacion` text NOT NULL,
  `FechaCreacion` date NOT NULL,
  `FechaModificacion` date NOT NULL,
  PRIMARY KEY (`_id`),
  KEY `fk_Modelos_1_idx` (`_idMarca`),
  CONSTRAINT `fk_Modelos_1` FOREIGN KEY (`_idMarca`) REFERENCES `Marcas` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COMMENT='Tabla para autos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Modelos`
--

LOCK TABLES `Modelos` WRITE;
/*!40000 ALTER TABLE `Modelos` DISABLE KEYS */;
INSERT INTO `Modelos` VALUES (1,'Tsuru I',1,1,'root','root','2019-06-11','2019-06-11'),(2,'Tsuru II',1,1,'root','root','2019-06-11','2019-06-11'),(3,'Tsuru III',1,1,'root','root','2019-06-11','2019-06-11'),(4,'Bora',1,2,'root','root','2019-06-25','2019-06-25'),(5,'Chevy',1,3,'root','root','2019-06-25','2019-06-25'),(6,'Altima',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(7,'Aprio',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(8,'Astra',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(9,'Atlantic',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(10,'A1',1,4,'Admin','Admin','2019-06-25','2019-06-25'),(11,'A3',1,4,'Admin','Admin','2019-06-25','2019-06-25'),(12,'A4',1,4,'Admin','Admin','2019-06-25','2019-06-25'),(13,'A8',1,4,'Admin','Admin','2019-06-25','2019-06-25'),(14,'TT',1,4,'Admin','Admin','2019-06-25','2019-06-25'),(15,'Aveo',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(16,'Corsa',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(17,'Corsar',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(18,'Matiz',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(19,'Meriva',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(20,'Spark',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(21,'Tornado',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(22,'Zafira',1,3,'Admin','Admin','2019-06-25','2019-06-25'),(23,'306',1,7,'Admin','Admin','2019-06-25','2019-06-25'),(24,'Partner',1,7,'Admin','Admin','2019-06-25','2019-06-25'),(25,'Almera',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(26,'Lucino',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(27,'March',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(28,'Pick-up NP300',1,1,'Admin','Admin','2019-06-25','2019-06-26'),(29,'Pick-up 720',1,1,'Admin','Admin','2019-06-25','2019-06-26'),(30,'Pick-up D21',1,1,'Admin','Admin','2019-06-25','2019-06-26'),(31,'Pick-up D22',1,1,'Admin','Admin','2019-06-25','2019-06-26'),(32,'Pathfinder',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(33,'Platina',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(34,'Sentra',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(35,'Tiida',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(36,'Urvan',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(37,'Versa',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(38,'X-Trail',1,1,'Admin','Admin','2019-06-25','2019-06-25'),(39,'Clio',1,8,'Admin','Admin','2019-06-25','2019-06-25'),(40,'Cordoba',1,5,'Admin','Admin','2019-06-25','2019-06-25'),(41,'Ibiza',1,5,'Admin','Admin','2019-06-25','2019-06-25'),(42,'León',1,5,'Admin','Admin','2019-06-25','2019-06-25'),(43,'Toledo',1,5,'Admin','Admin','2019-06-25','2019-06-25'),(44,'Swift',1,9,'Admin','Admin','2019-06-25','2019-06-25'),(45,'Beetle',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(46,'Brasilia',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(47,'Caribe',1,2,'Admin','Admin','2019-06-25','2019-06-29'),(48,'Combi',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(49,'Crossfox',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(50,'Derby',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(51,'Gol',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(52,'Golf A2',1,2,'Admin','Admin','2019-06-25','2019-06-26'),(53,'Jetta A2',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(54,'Jetta A3',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(55,'Jetta A4',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(56,'Jetta A5',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(57,'Jetta A6',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(58,'Lupo',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(59,'Passat',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(60,'Pointer',0,2,'Admin','Admin','2019-06-25','2019-06-29'),(61,'Pointer',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(62,'Saveiro',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(63,'Touareg',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(64,'Vocho',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(65,'Vento',1,2,'Admin','Admin','2019-06-25','2019-06-25'),(66,'Chevy C2',1,3,'Admin','Admin','2019-06-26','2019-06-26'),(67,'Chevy C3',1,3,'Admin','Admin','2019-06-26','2019-06-26'),(68,'Golf A3',1,2,'Admin','Admin','2019-06-26','2019-06-26'),(69,'Golf A4',1,2,'Admin','Admin','2019-06-26','2019-06-26'),(70,'Atos',1,6,'Admin','Admin','2019-06-26','2019-06-26'),(71,'I10',1,6,'Admin','Admin','2019-06-26','2019-06-26'),(72,'Altima',0,1,'Admin','Admin','2019-06-26','2019-06-26'),(73,'Chevy Pick-up',1,3,'Admin','Admin','2019-06-26','2019-06-26'),(74,'Chevy',0,3,'Admin','Admin','2019-06-26','2019-06-29'),(75,'Polo',1,2,'Admin','Admin','2019-07-05','2019-07-05');
/*!40000 ALTER TABLE `Modelos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-07 19:04:26
