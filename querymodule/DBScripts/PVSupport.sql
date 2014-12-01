-- --------------------------------------------------------
-- Host:                         
-- Server version:               5.6.21-log - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table PVSupport.QueryDef
CREATE TABLE IF NOT EXISTS `QueryDef` (
  `QueryDefId` bigint(20) unsigned NOT NULL,
  `QueryString` text NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`QueryDefId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PVSupport.QueryResults
CREATE TABLE IF NOT EXISTS `QueryResults` (
  `QueryDefId` bigint(20) unsigned NOT NULL,
  `Sequence` int(11) unsigned NOT NULL,
  `EntityId` varchar(50) NOT NULL,
  KEY `QueryDefId_Sequence` (`QueryDefId`,`Sequence`),
  KEY `EntityId` (`EntityId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
