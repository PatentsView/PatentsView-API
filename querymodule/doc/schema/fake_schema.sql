-- --------------------------------------------------------
-- Host:                         pv3-ingestmysql.cckzcdkkfzqo.us-east-1.rds.amazonaws.com
-- Server version:               5.6.21-log - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table PatentsView_20141215_v5.application
CREATE TABLE IF NOT EXISTS `application` (
  `application_id` varchar(36) NOT NULL,
  `patent_id` varchar(20) NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `number` varchar(64) DEFAULT NULL,
  `country` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`application_id`,`patent_id`),
  KEY `ix_application_number` (`number`),
  KEY `ix_application_patent_id` (`patent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.assignee
CREATE TABLE IF NOT EXISTS `assignee` (
  `assignee_id` int(10) unsigned NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  `name_first` varchar(64) DEFAULT NULL,
  `name_last` varchar(64) DEFAULT NULL,
  `organization` varchar(256) DEFAULT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  `num_inventors` int(10) unsigned NOT NULL,
  `lastknown_location_id` int(10) unsigned DEFAULT NULL,
  `lastknown_persistent_location_id` varchar(128) DEFAULT NULL,
  `lastknown_city` varchar(128) DEFAULT NULL,
  `lastknown_state` varchar(20) DEFAULT NULL,
  `lastknown_country` varchar(10) DEFAULT NULL,
  `lastknown_latitude` float DEFAULT NULL,
  `lastknown_longitude` float DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned NOT NULL,
  `persistent_assignee_id` varchar(36) NOT NULL,
  PRIMARY KEY (`assignee_id`),
  KEY `ix_assignee_name_first` (`name_first`),
  KEY `ix_assignee_name_last` (`name_last`),
  KEY `ix_assignee_organization` (`organization`(255)),
  KEY `ix_assignee_persistent_assignee_id` (`persistent_assignee_id`),
  KEY `ix_assignee_num_patents` (`num_patents`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.assignee_cpc_subsection
CREATE TABLE IF NOT EXISTS `assignee_cpc_subsection` (
  `assignee_id` int(10) unsigned NOT NULL,
  `subsection_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_assignee_cpc_subsection_assignee_id` (`assignee_id`),
  KEY `ix_assignee_cpc_subsection_subsection_id` (`subsection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.assignee_inventor
CREATE TABLE IF NOT EXISTS `assignee_inventor` (
  `assignee_id` int(10) unsigned NOT NULL,
  `inventor_id` int(10) unsigned NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_assignee_inventor_assignee_id` (`assignee_id`),
  KEY `ix_assignee_inventor_inventor_id` (`inventor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.assignee_nber_subcategory
CREATE TABLE IF NOT EXISTS `assignee_nber_subcategory` (
  `assignee_id` int(10) unsigned NOT NULL,
  `subcategory_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_assignee_nber_subcategory_assignee_id` (`assignee_id`),
  KEY `ix_assignee_nber_subcategory_subcategory_id` (`subcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.assignee_uspc_mainclass
CREATE TABLE IF NOT EXISTS `assignee_uspc_mainclass` (
  `assignee_id` int(10) unsigned NOT NULL,
  `mainclass_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_assignee_uspc_mainclass_assignee_id` (`assignee_id`),
  KEY `ix_assignee_uspc_mainclass_mainclass_id` (`mainclass_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.assignee_year
CREATE TABLE IF NOT EXISTS `assignee_year` (
  `assignee_id` int(10) unsigned NOT NULL,
  `patent_year` smallint(6) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_assignee_year_assignee_id` (`assignee_id`),
  KEY `ix_assignee_year_year` (`patent_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.cpc_current
CREATE TABLE IF NOT EXISTS `cpc_current` (
  `patent_id` varchar(20) NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `section_id` varchar(10) DEFAULT NULL,
  `subsection_id` varchar(20) DEFAULT NULL,
  `subsection_title` varchar(512) DEFAULT NULL,
  `group_id` varchar(20) DEFAULT NULL,
  `group_title` varchar(256) DEFAULT NULL,
  `subgroup_id` varchar(20) DEFAULT NULL,
  `subgroup_title` varchar(512) DEFAULT NULL,
  `category` varchar(36) DEFAULT NULL,
  `num_assignees` int(10) unsigned DEFAULT NULL,
  `num_inventors` int(10) unsigned DEFAULT NULL,
  `num_patents` int(10) unsigned DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`sequence`),
  KEY `ix_cpc_current_group_id` (`group_id`),
  KEY `ix_cpc_current_subgroup_id` (`subgroup_id`),
  KEY `ix_cpc_current_subsection_id` (`subsection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.cpc_current_subsection
CREATE TABLE IF NOT EXISTS `cpc_current_subsection` (
  `patent_id` varchar(20) NOT NULL,
  `section_id` varchar(10) DEFAULT NULL,
  `subsection_id` varchar(20) NOT NULL DEFAULT '',
  `subsection_title` varchar(512) DEFAULT NULL,
  `num_assignees` int(10) unsigned DEFAULT NULL,
  `num_inventors` int(10) unsigned DEFAULT NULL,
  `num_patents` int(10) unsigned DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`subsection_id`),
  KEY `ix_cpc_current_subsection_subsection_id` (`subsection_id`),
  KEY `ix_cpc_current_subsection_title` (`subsection_title`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.cpc_current_subsection_patent_year
CREATE TABLE IF NOT EXISTS `cpc_current_subsection_patent_year` (
  `subsection_id` varchar(20) NOT NULL,
  `patent_year` smallint(5) unsigned NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  PRIMARY KEY (`subsection_id`,`patent_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.inventor
CREATE TABLE IF NOT EXISTS `inventor` (
  `inventor_id` int(10) unsigned NOT NULL,
  `name_first` varchar(64) DEFAULT NULL,
  `name_last` varchar(64) DEFAULT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  `num_assignees` int(10) unsigned NOT NULL,
  `lastknown_location_id` int(10) unsigned DEFAULT NULL,
  `lastknown_persistent_location_id` varchar(128) DEFAULT NULL,
  `lastknown_city` varchar(128) DEFAULT NULL,
  `lastknown_state` varchar(20) DEFAULT NULL,
  `lastknown_country` varchar(10) DEFAULT NULL,
  `lastknown_latitude` float DEFAULT NULL,
  `lastknown_longitude` float DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned NOT NULL,
  `persistent_inventor_id` varchar(36) NOT NULL,
  PRIMARY KEY (`inventor_id`),
  KEY `ix_inventor_name_first` (`name_first`),
  KEY `ix_inventor_name_last` (`name_last`),
  KEY `ix_inventor_persistent_inventor_id` (`persistent_inventor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.inventor_coinventor
CREATE TABLE IF NOT EXISTS `inventor_coinventor` (
  `inventor_id` int(10) unsigned NOT NULL,
  `coinventor_id` int(10) unsigned NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_inventor_coinventor_inventor_id` (`inventor_id`),
  KEY `ix_inventor_coinventor_coinventor_id` (`coinventor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.inventor_cpc_subsection
CREATE TABLE IF NOT EXISTS `inventor_cpc_subsection` (
  `inventor_id` int(10) unsigned NOT NULL,
  `subsection_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_inventor_cpc_subsection_inventor_id` (`inventor_id`),
  KEY `ix_inventor_cpc_subsection_subsection_id` (`subsection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.inventor_nber_subcategory
CREATE TABLE IF NOT EXISTS `inventor_nber_subcategory` (
  `inventor_id` int(10) unsigned NOT NULL,
  `subcategory_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_inventor_nber_subcategory_inventor_id` (`inventor_id`),
  KEY `ix_inventor_nber_subcategory_subcategory_id` (`subcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.inventor_uspc_mainclass
CREATE TABLE IF NOT EXISTS `inventor_uspc_mainclass` (
  `inventor_id` int(10) unsigned NOT NULL,
  `mainclass_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_inventor_uspc_mainclass_inventor_id` (`inventor_id`),
  KEY `ix_inventor_uspc_mainclass_mainclass_id` (`mainclass_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.inventor_year
CREATE TABLE IF NOT EXISTS `inventor_year` (
  `inventor_id` int(10) unsigned NOT NULL,
  `patent_year` smallint(6) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_inventor_year_inventor_id` (`inventor_id`),
  KEY `ix_inventor_year_year` (`patent_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.ipcr
CREATE TABLE IF NOT EXISTS `ipcr` (
  `patent_id` varchar(20) NOT NULL,
  `sequence` int(11) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `ipc_class` varchar(20) DEFAULT NULL,
  `subclass` varchar(20) DEFAULT NULL,
  `main_group` varchar(20) DEFAULT NULL,
  `subgroup` varchar(20) DEFAULT NULL,
  `symbol_position` varchar(20) DEFAULT NULL,
  `classification_value` varchar(20) DEFAULT NULL,
  `classification_data_source` varchar(20) DEFAULT NULL,
  `action_date` date DEFAULT NULL,
  `ipc_version_indicator` date DEFAULT NULL,
  `num_assignees` int(10) unsigned DEFAULT NULL,
  `num_inventors` int(10) unsigned DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`sequence`),
  KEY `ix_ipcr_ipc_class` (`ipc_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.location
CREATE TABLE IF NOT EXISTS `location` (
  `location_id` int(10) unsigned NOT NULL,
  `city` varchar(128) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `country` varchar(10) DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `num_assignees` int(10) unsigned NOT NULL,
  `num_inventors` int(10) unsigned NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  `persistent_location_id` varchar(128) NOT NULL,
  PRIMARY KEY (`location_id`),
  KEY `ix_location_city` (`city`),
  KEY `ix_location_country` (`country`),
  KEY `ix_location_persistent_location_id` (`persistent_location_id`),
  KEY `ix_location_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.location_assignee
CREATE TABLE IF NOT EXISTS `location_assignee` (
  `location_id` int(10) unsigned NOT NULL,
  `assignee_id` int(10) unsigned NOT NULL,
  `num_patents` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`location_id`,`assignee_id`),
  KEY `ix_location_assignee_assignee_id` (`assignee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.location_cpc_subsection
CREATE TABLE IF NOT EXISTS `location_cpc_subsection` (
  `location_id` int(10) unsigned NOT NULL,
  `subsection_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_location_cpc_subsection_location_id` (`location_id`),
  KEY `ix_location_cpc_subsection_subsection_id` (`subsection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.location_inventor
CREATE TABLE IF NOT EXISTS `location_inventor` (
  `location_id` int(10) unsigned NOT NULL,
  `inventor_id` int(10) unsigned NOT NULL,
  `num_patents` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`location_id`,`inventor_id`),
  KEY `ix_location_inventor_inventor_id` (`inventor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.location_nber_subcategory
CREATE TABLE IF NOT EXISTS `location_nber_subcategory` (
  `location_id` int(10) unsigned NOT NULL,
  `subcategory_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_location_nber_subcategory_location_id` (`location_id`),
  KEY `ix_location_nber_subcategory_mainclass_id` (`subcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.location_uspc_mainclass
CREATE TABLE IF NOT EXISTS `location_uspc_mainclass` (
  `location_id` int(10) unsigned NOT NULL,
  `mainclass_id` varchar(20) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_location_uspc_mainclass_location_id` (`location_id`),
  KEY `ix_location_uspc_mainclass_mainclass_id` (`mainclass_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.location_year
CREATE TABLE IF NOT EXISTS `location_year` (
  `location_id` int(10) unsigned NOT NULL,
  `year` smallint(6) NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  KEY `ix_location_year_location_id` (`location_id`),
  KEY `ix_location_year_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.nber
CREATE TABLE IF NOT EXISTS `nber` (
  `patent_id` varchar(20) NOT NULL,
  `category_id` varchar(20) DEFAULT NULL,
  `category_title` varchar(512) DEFAULT NULL,
  `subcategory_id` varchar(20) DEFAULT NULL,
  `subcategory_title` varchar(512) DEFAULT NULL,
  `num_assignees` int(10) unsigned DEFAULT NULL,
  `num_inventors` int(10) unsigned DEFAULT NULL,
  `num_patents` int(10) unsigned DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`patent_id`),
  KEY `ix_nber_subcategory_id` (`subcategory_id`),
  KEY `ix_nber_subcategory_title` (`subcategory_title`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.nber_subcategory_patent_year
CREATE TABLE IF NOT EXISTS `nber_subcategory_patent_year` (
  `subcategory_id` varchar(20) NOT NULL,
  `patent_year` smallint(5) unsigned NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  PRIMARY KEY (`subcategory_id`,`patent_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.patent
CREATE TABLE IF NOT EXISTS `patent` (
  `patent_id` varchar(20) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `number` varchar(64) NOT NULL,
  `country` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `year` smallint(5) unsigned DEFAULT NULL,
  `abstract` text,
  `title` text,
  `kind` varchar(10) DEFAULT NULL,
  `num_claims` smallint(5) unsigned DEFAULT NULL,
  `firstnamed_assignee_id` int(10) unsigned DEFAULT NULL,
  `firstnamed_assignee_persistent_id` varchar(36) DEFAULT NULL,
  `firstnamed_assignee_location_id` int(10) unsigned DEFAULT NULL,
  `firstnamed_assignee_persistent_location_id` varchar(128) DEFAULT NULL,
  `firstnamed_assignee_city` varchar(128) DEFAULT NULL,
  `firstnamed_assignee_state` varchar(20) DEFAULT NULL,
  `firstnamed_assignee_country` varchar(10) DEFAULT NULL,
  `firstnamed_assignee_latitude` float DEFAULT NULL,
  `firstnamed_assignee_longitude` float DEFAULT NULL,
  `firstnamed_inventor_id` int(10) unsigned DEFAULT NULL,
  `firstnamed_inventor_persistent_id` varchar(36) DEFAULT NULL,
  `firstnamed_inventor_location_id` int(10) unsigned DEFAULT NULL,
  `firstnamed_inventor_persistent_location_id` varchar(128) DEFAULT NULL,
  `firstnamed_inventor_city` varchar(128) DEFAULT NULL,
  `firstnamed_inventor_state` varchar(20) DEFAULT NULL,
  `firstnamed_inventor_country` varchar(10) DEFAULT NULL,
  `firstnamed_inventor_latitude` float DEFAULT NULL,
  `firstnamed_inventor_longitude` float DEFAULT NULL,
  `num_foreign_documents_cited` int(10) unsigned NOT NULL,
  `num_us_applications_cited` int(10) unsigned NOT NULL,
  `num_us_patents_cited` int(10) unsigned NOT NULL,
  `num_total_documents_cited` int(10) unsigned NOT NULL,
  `num_times_cited_by_us_patents` int(10) unsigned NOT NULL,
  `earliest_application_date` date DEFAULT NULL,
  `patent_processing_days` int(10) unsigned DEFAULT NULL,
  `uspc_current_mainclass_average_patent_processing_days` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`patent_id`),
  KEY `ix_patent_date` (`date`),
  KEY `ix_patent_number` (`number`),
  KEY `ix_patent_title` (`title`(128)),
  KEY `ix_patent_type` (`type`),
  KEY `ix_patent_year` (`year`),
  FULLTEXT KEY `fti_patent_abstract` (`abstract`),
  FULLTEXT KEY `fti_patent_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.patent_assignee
CREATE TABLE IF NOT EXISTS `patent_assignee` (
  `patent_id` varchar(20) NOT NULL,
  `assignee_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `sequence` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`patent_id`,`assignee_id`),
  UNIQUE KEY `ak_patent_assignee` (`assignee_id`,`patent_id`),
  KEY `ix_patent_assignee_location_id` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.patent_inventor
CREATE TABLE IF NOT EXISTS `patent_inventor` (
  `patent_id` varchar(20) NOT NULL,
  `inventor_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `sequence` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`patent_id`,`inventor_id`),
  UNIQUE KEY `ak_patent_inventor` (`inventor_id`,`patent_id`),
  KEY `ix_patent_inventor_location_id` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.temp_assignee_num_inventors
CREATE TABLE IF NOT EXISTS `temp_assignee_num_inventors` (
  `assignee_id` varchar(36) NOT NULL,
  `num_inventors` int(10) unsigned NOT NULL,
  PRIMARY KEY (`assignee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.temp_inventor_num_assignees
CREATE TABLE IF NOT EXISTS `temp_inventor_num_assignees` (
  `inventor_id` varchar(36) NOT NULL,
  `num_assignees` int(10) unsigned NOT NULL,
  PRIMARY KEY (`inventor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.usapplicationcitation
CREATE TABLE IF NOT EXISTS `usapplicationcitation` (
  `citing_patent_id` varchar(20) NOT NULL,
  `sequence` int(11) NOT NULL,
  `cited_application_id` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `kind` varchar(10) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`citing_patent_id`,`sequence`),
  KEY `ix_usapplicationcitation_cited_application_id` (`cited_application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.uspatentcitation
CREATE TABLE IF NOT EXISTS `uspatentcitation` (
  `citing_patent_id` varchar(20) NOT NULL,
  `sequence` int(11) NOT NULL,
  `cited_patent_id` varchar(20) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`citing_patent_id`,`sequence`),
  KEY `ix_uspatentcitation_cited_patent_id` (`cited_patent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.uspc_current
CREATE TABLE IF NOT EXISTS `uspc_current` (
  `patent_id` varchar(20) NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `mainclass_id` varchar(20) DEFAULT NULL,
  `mainclass_title` varchar(256) DEFAULT NULL,
  `subclass_id` varchar(20) DEFAULT NULL,
  `subclass_title` varchar(512) DEFAULT NULL,
  `num_assignees` int(10) unsigned DEFAULT NULL,
  `num_inventors` int(10) unsigned DEFAULT NULL,
  `num_patents` int(10) unsigned DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`sequence`),
  KEY `ix_uspc_current_mainclass_id` (`mainclass_id`),
  KEY `ix_uspc_current_subclass_id` (`subclass_id`),
  KEY `ix_uspc_current_mainclass_title` (`mainclass_title`(255)),
  KEY `ix_uspc_current_subclass_title` (`subclass_title`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.uspc_current_mainclass
CREATE TABLE IF NOT EXISTS `uspc_current_mainclass` (
  `patent_id` varchar(20) NOT NULL,
  `mainclass_id` varchar(20) NOT NULL DEFAULT '',
  `mainclass_title` varchar(256) DEFAULT NULL,
  `num_assignees` int(10) unsigned DEFAULT NULL,
  `num_inventors` int(10) unsigned DEFAULT NULL,
  `num_patents` int(10) unsigned DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`mainclass_id`),
  KEY `ix_uspc_current_mainclass_mainclass_id` (`mainclass_id`),
  KEY `ix_uspc_current_mainclass_mainclass_title` (`mainclass_title`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.uspc_current_mainclass_application_year
CREATE TABLE IF NOT EXISTS `uspc_current_mainclass_application_year` (
  `mainclass_id` varchar(20) NOT NULL,
  `application_year` smallint(5) unsigned NOT NULL,
  `sample_size` int(10) unsigned NOT NULL,
  `average_patent_processing_days` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`mainclass_id`,`application_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table PatentsView_20141215_v5.uspc_current_mainclass_patent_year
CREATE TABLE IF NOT EXISTS `uspc_current_mainclass_patent_year` (
  `mainclass_id` varchar(20) NOT NULL,
  `patent_year` smallint(5) unsigned NOT NULL,
  `num_patents` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mainclass_id`,`patent_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
