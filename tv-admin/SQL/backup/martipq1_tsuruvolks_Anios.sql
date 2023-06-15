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
-- Table structure for table `Anios`
--

DROP TABLE IF EXISTS `Anios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Anios` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `Anio` text NOT NULL,
  `_idModelo` int(11) NOT NULL,
  `USRCreacion` text NOT NULL,
  `USREdicion` text NOT NULL,
  `FechaCreacion` date NOT NULL,
  `FechaModificacion` date NOT NULL,
  PRIMARY KEY (`_id`),
  KEY `fk_A_1_idx` (`_idModelo`),
  CONSTRAINT `fk_A_1` FOREIGN KEY (`_idModelo`) REFERENCES `Modelos` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COMMENT='Años de produccion del Vehiculo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Anios`
--

LOCK TABLES `Anios` WRITE;
/*!40000 ALTER TABLE `Anios` DISABLE KEYS */;
INSERT INTO `Anios` VALUES (1,'1982-1987',1,'root','root','2019-06-11','2019-06-11'),(2,'1988-1991',2,'root','root','2019-06-11','2019-06-11'),(3,'1992-1994 8val',3,'root','root','2019-06-11','2019-06-11'),(4,'1995-2006 16val',3,'root','root','2019-06-11','2019-06-11'),(5,'2002-2011',4,'root','root','2019-06-25','2019-06-25'),(6,'1994-2001',5,'root','root','2019-06-25','2019-06-25'),(9,'2011-2014',10,'Admin','Admin','2019-06-25','2019-06-25'),(10,'2005',11,'Admin','Admin','2019-06-25','2019-06-25'),(11,'2002-2005',12,'Admin','Admin','2019-06-25','2019-06-25'),(12,'2004-2009',13,'Admin','Admin','2019-06-25','2019-06-25'),(13,'2000-2006',14,'Admin','Admin','2019-06-25','2019-06-25'),(14,'1982-1985',9,'Admin','Admin','2019-06-25','2019-06-25'),(15,'1984-1991',53,'Admin','Admin','2019-06-25','2019-06-25'),(16,'1992-1999',54,'Admin','Admin','2019-06-26','2019-06-26'),(17,'1998-2005',55,'Admin','Admin','2019-06-26','2019-06-26'),(18,'2005-2011',56,'Admin','Admin','2019-06-26','2019-06-26'),(19,'2011-2019',57,'Admin','Admin','2019-06-26','2019-06-26'),(20,'2002-2006',66,'Admin','Admin','2019-06-26','2019-06-26'),(21,'2007-2010',67,'Admin','Admin','2019-06-26','2019-06-26'),(22,'Todos',47,'Admin','Admin','2019-06-29','2019-06-29'),(24,'1993-1997',6,'Admin','Admin','2019-06-29','2019-06-29'),(25,'1998-2001',6,'Admin','Admin','2019-06-29','2019-06-29'),(26,'Todos',7,'Admin','Admin','2019-06-29','2019-06-29'),(27,'1991-1998',8,'Admin','Admin','2019-06-29','2019-06-29'),(28,'1998-2004',8,'Admin','Admin','2019-06-29','2019-06-29'),(29,'2004-2010',8,'Admin','Admin','2019-06-29','2019-06-29'),(30,'1990-1995',25,'Admin','Admin','2019-06-29','2019-06-29'),(31,'1995-2000',25,'Admin','Admin','2019-06-29','2019-06-29'),(32,'2000-2006',25,'Admin','Admin','2019-06-29','2019-06-29'),(33,'Todos',26,'Admin','Admin','2019-06-29','2019-06-29'),(34,'Todos',27,'Admin','Admin','2019-06-29','2019-06-29'),(35,'Todos',46,'Admin','Admin','2019-06-29','2019-06-29'),(36,'1999-04',70,'Admin','Admin','2019-06-29','2019-06-29'),(37,'04-',70,'Admin','Admin','2019-06-29','2019-06-29'),(38,'2004-',15,'Admin','Admin','2019-06-29','2019-06-29'),(39,'Todos',45,'Admin','Admin','2019-06-29','2019-06-29'),(40,'1987-',30,'Admin','Admin','2019-06-29','2019-06-29'),(41,'2005-2018',28,'Admin','Admin','2019-06-29','2019-06-29'),(42,'1980-1983',29,'Admin','Admin','2019-06-29','2019-06-29'),(43,'1999-2004',32,'Admin','Admin','2019-06-29','2019-06-29'),(44,'1998-2006',33,'Admin','Admin','2019-06-29','2019-06-29'),(45,'1995-1999',34,'Admin','Admin','2019-06-29','2019-06-29'),(46,'2000-2006',34,'Admin','Admin','2019-06-29','2019-06-29'),(47,'2007-2012',34,'Admin','Admin','2019-06-29','2019-06-29'),(48,'2012-2019',34,'Admin','Admin','2019-06-29','2019-06-29'),(49,'2004-2019',35,'Admin','Admin','2019-06-29','2019-06-29'),(50,'1985-2017',36,'Admin','Admin','2019-06-29','2019-06-29'),(51,'2006-2019',37,'Admin','Admin','2019-06-29','2019-06-29'),(52,'2000-2019',38,'Admin','Admin','2019-06-29','2019-06-29'),(53,'Todos',48,'Admin','Admin','2019-06-29','2019-06-29'),(54,'2005-2019',49,'Admin','Admin','2019-06-29','2019-06-29'),(55,'1994-2008',50,'Admin','Admin','2019-06-29','2019-06-29'),(56,'2006-2014',51,'Admin','Admin','2019-06-29','2019-06-29'),(57,'1984-1991',52,'Admin','Admin','2019-06-29','2019-06-29'),(58,'1998-2005',58,'Admin','Admin','2019-06-29','2019-06-29'),(59,'1993-1997',59,'Admin','Admin','2019-06-29','2019-06-29'),(60,'1997-2005',59,'Admin','Admin','2019-06-29','2019-06-29'),(61,'2005-2010',59,'Admin','Admin','2019-06-29','2019-06-29'),(62,'1998-2008',61,'Admin','Admin','2019-06-29','2019-06-29'),(63,'2006-2019',62,'Admin','Admin','2019-06-29','2019-06-29'),(64,'2004-',63,'Admin','Admin','2019-06-29','2019-06-29'),(65,'1992-1999',68,'Admin','Admin','2019-06-29','2019-06-29'),(66,'1999-2005',69,'Admin','Admin','2019-06-29','2019-06-29'),(67,'-76',64,'Admin','Admin','2019-06-29','2019-06-29'),(68,'76-',64,'Admin','Admin','2019-06-29','2019-06-29'),(69,'1993-',65,'Admin','Admin','2019-06-29','2019-06-29'),(70,'1993-2000',16,'Admin','Admin','2019-06-29','2019-06-29'),(71,'2000-2011',16,'Admin','Admin','2019-06-29','2019-06-29'),(72,'Todos',17,'Admin','Admin','2019-06-29','2019-06-29'),(73,'1998-2005',18,'Admin','Admin','2019-06-29','2019-06-29'),(74,'2003-2012',19,'Admin','Admin','2019-06-29','2019-06-29'),(75,'2005-2018',20,'Admin','Admin','2019-06-29','2019-06-29'),(76,'2009-',21,'Admin','Admin','2019-06-29','2019-06-29'),(77,'1999-',22,'Admin','Admin','2019-06-29','2019-06-29'),(78,'Todos',73,'Admin','Admin','2019-06-29','2019-06-29'),(79,'Todos',40,'Admin','Admin','2019-06-29','2019-06-29'),(80,'1993-2002',41,'Admin','Admin','2019-06-29','2019-06-29'),(81,'2002-2008',41,'Admin','Admin','2019-06-29','2019-06-29'),(82,'2008-2017',41,'Admin','Admin','2019-06-29','2019-06-29'),(83,'2017-',41,'Admin','Admin','2019-06-29','2019-06-29'),(84,'Todos',42,'Admin','Admin','2019-06-29','2019-06-29'),(85,'Todos',43,'Admin','Admin','2019-06-29','2019-06-29'),(86,'2007-2014',71,'Admin','Admin','2019-06-29','2019-06-29'),(87,'2014-2019',71,'Admin','Admin','2019-06-29','2019-06-29'),(88,'todos',23,'Admin','Admin','2019-06-29','2019-06-29'),(89,'Todos',24,'Admin','Admin','2019-06-29','2019-06-29'),(90,'Todos',39,'Admin','Admin','2019-06-29','2019-06-29'),(91,'Todos',44,'Admin','Admin','2019-06-29','2019-06-29'),(93,'Todos',31,'','','2019-07-02','2019-07-02'),(94,'99-',75,'Admin','Admin','2019-07-05','2019-07-05');
/*!40000 ALTER TABLE `Anios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-07 19:04:24
