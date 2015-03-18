CREATE TABLE IF NOT EXISTS `patent` (
  `patent_id` varchar(20) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `number` varchar(64) NOT NULL,
  `country` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `year` smallint(5) DEFAULT NULL,
  `abstract` text,
  `title` text,
  `kind` varchar(10) DEFAULT NULL,
  `num_claims` smallint(5) DEFAULT NULL,
  `firstnamed_assignee_id` int(10) DEFAULT NULL,
  `firstnamed_assignee_persistent_id` varchar(36) DEFAULT NULL,
  `firstnamed_assignee_location_id` int(10) DEFAULT NULL,
  `firstnamed_assignee_persistent_location_id` varchar(128) DEFAULT NULL,
  `firstnamed_assignee_city` varchar(128) DEFAULT NULL,
  `firstnamed_assignee_state` varchar(20) DEFAULT NULL,
  `firstnamed_assignee_country` varchar(10) DEFAULT NULL,
  `firstnamed_assignee_latitude` float DEFAULT NULL,
  `firstnamed_assignee_longitude` float DEFAULT NULL,
  `firstnamed_inventor_id` int(10) DEFAULT NULL,
  `firstnamed_inventor_persistent_id` varchar(36) DEFAULT NULL,
  `firstnamed_inventor_location_id` int(10) DEFAULT NULL,
  `firstnamed_inventor_persistent_location_id` varchar(128) DEFAULT NULL,
  `firstnamed_inventor_city` varchar(128) DEFAULT NULL,
  `firstnamed_inventor_state` varchar(20) DEFAULT NULL,
  `firstnamed_inventor_country` varchar(10) DEFAULT NULL,
  `firstnamed_inventor_latitude` float DEFAULT NULL,
  `firstnamed_inventor_longitude` float DEFAULT NULL,
  `num_foreign_documents_cited` int(10) NOT NULL,
  `num_us_applications_cited` int(10) NOT NULL,
  `num_us_patents_cited` int(10) NOT NULL,
  `num_total_documents_cited` int(10) NOT NULL,
  `num_times_cited_by_us_patents` int(10) NOT NULL,
  `earliest_application_date` date DEFAULT NULL,
  `patent_processing_days` int(10) DEFAULT NULL,
  `uspc_current_mainclass_average_patent_processing_days` int(10) DEFAULT NULL,
  PRIMARY KEY (`patent_id`)
);


CREATE TABLE IF NOT EXISTS `application` (
  `application_id` varchar(36) NOT NULL,
  `patent_id` varchar(20) NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `number` varchar(64) DEFAULT NULL,
  `country` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`application_id`,`patent_id`),
  foreign key (patent_id) references patent (patent_id)
);



CREATE TABLE IF NOT EXISTS `assignee` (
  `assignee_id` int(10) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  `name_first` varchar(64) DEFAULT NULL,
  `name_last` varchar(64) DEFAULT NULL,
  `organization` varchar(256) DEFAULT NULL,
  `num_patents` int(10) NOT NULL,
  `num_inventors` int(10) NOT NULL,
  `lastknown_location_id` int(10) DEFAULT NULL,
  `lastknown_persistent_location_id` varchar(128) DEFAULT NULL,
  `lastknown_city` varchar(128) DEFAULT NULL,
  `lastknown_state` varchar(20) DEFAULT NULL,
  `lastknown_country` varchar(10) DEFAULT NULL,
  `lastknown_latitude` float DEFAULT NULL,
  `lastknown_longitude` float DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) NOT NULL,
  `persistent_assignee_id` varchar(36) NOT NULL,
  PRIMARY KEY (`assignee_id`)
);

-- CREATE TABLE IF NOT EXISTS `cpc_current` (
--   `patent_id` varchar(20) NOT NULL,
--   `sequence` int(10) NOT NULL,
--   `section_id` varchar(10) DEFAULT NULL,
--   `subsection_id` varchar(20) DEFAULT NULL,
--   `subsection_title` varchar(512) DEFAULT NULL,
--   `group_id` varchar(20) DEFAULT NULL,
--   `group_title` varchar(256) DEFAULT NULL,
--   `subgroup_id` varchar(20) DEFAULT NULL,
--   `subgroup_title` varchar(512) DEFAULT NULL,
--   `category` varchar(36) DEFAULT NULL,
--   `num_assignees` int(10) DEFAULT NULL,
--   `num_inventors` int(10) DEFAULT NULL,
--   `num_patents` int(10) DEFAULT NULL,
--   `first_seen_date` date DEFAULT NULL,
--   `last_seen_date` date DEFAULT NULL,
--   `years_active` smallint(5) DEFAULT NULL,
--   PRIMARY KEY (`patent_id`,`sequence`),
-- );


CREATE TABLE IF NOT EXISTS `cpc_current_subsection` (
  `patent_id` varchar(20) NOT NULL,
  `section_id` varchar(10) DEFAULT NULL,
  `subsection_id` varchar(20) NOT NULL DEFAULT '',
  `subsection_title` varchar(512) DEFAULT NULL,
  `num_assignees` int(10) DEFAULT NULL,
  `num_inventors` int(10) DEFAULT NULL,
  `num_patents` int(10) DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`subsection_id`),
  foreign key (patent_id) references patent (patent_id)
);


CREATE TABLE IF NOT EXISTS `inventor` (
  `inventor_id` int(10) NOT NULL,
  `name_first` varchar(64) DEFAULT NULL,
  `name_last` varchar(64) DEFAULT NULL,
  `num_patents` int(10) NOT NULL,
  `num_assignees` int(10) NOT NULL,
  `lastknown_location_id` int(10) DEFAULT NULL,
  `lastknown_persistent_location_id` varchar(128) DEFAULT NULL,
  `lastknown_city` varchar(128) DEFAULT NULL,
  `lastknown_state` varchar(20) DEFAULT NULL,
  `lastknown_country` varchar(10) DEFAULT NULL,
  `lastknown_latitude` float DEFAULT NULL,
  `lastknown_longitude` float DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) NOT NULL,
  `persistent_inventor_id` varchar(36) NOT NULL,
  PRIMARY KEY (`inventor_id`)
);

CREATE TABLE IF NOT EXISTS `location` (
  `location_id` int(10) NOT NULL,
  `city` varchar(128) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `country` varchar(10) DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `num_assignees` int(10) NOT NULL,
  `num_inventors` int(10) NOT NULL,
  `num_patents` int(10) NOT NULL,
  `persistent_location_id` varchar(128) NOT NULL,
  PRIMARY KEY (`location_id`)
);

CREATE TABLE IF NOT EXISTS `nber` (
  `patent_id` varchar(20) NOT NULL,
  `category_id` varchar(20) DEFAULT NULL,
  `category_title` varchar(512) DEFAULT NULL,
  `subcategory_id` varchar(20) DEFAULT NULL,
  `subcategory_title` varchar(512) DEFAULT NULL,
  `num_assignees` int(10) DEFAULT NULL,
  `num_inventors` int(10) DEFAULT NULL,
  `num_patents` int(10) DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`patent_id`),
  foreign key (patent_id) references patent (patent_id)
);


CREATE TABLE IF NOT EXISTS `usapplicationcitation` (
  `citing_patent_id` varchar(20) NOT NULL,
  `sequence` int(11) NOT NULL,
  `cited_application_id` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `kind` varchar(10) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`citing_patent_id`,`sequence`),
  foreign key (citing_patent_id) references patent (patent_id),
  foreign key (cited_application_id) references application (application_id)
);



CREATE TABLE IF NOT EXISTS `uspatentcitation` (
  `citing_patent_id` varchar(20) NOT NULL,
  `sequence` int(11) NOT NULL,
  `cited_patent_id` varchar(20) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`citing_patent_id`,`sequence`),
  foreign key (citing_patent_id) references patent (patent_id),
  foreign key (cited_patent_id) references patent (patent_id)
);



-- CREATE TABLE IF NOT EXISTS `uspc_current` (
--   `patent_id` varchar(20) NOT NULL,
--   `sequence` int(10) NOT NULL,
--   `mainclass_id` varchar(20) DEFAULT NULL,
--   `mainclass_title` varchar(256) DEFAULT NULL,
--   `subclass_id` varchar(20) DEFAULT NULL,
--   `subclass_title` varchar(512) DEFAULT NULL,
--   `num_assignees` int(10) DEFAULT NULL,
--   `num_inventors` int(10) DEFAULT NULL,
--   `num_patents` int(10) DEFAULT NULL,
--   `first_seen_date` date DEFAULT NULL,
--   `last_seen_date` date DEFAULT NULL,
--   `years_active` smallint(5) DEFAULT NULL,
--   PRIMARY KEY (`patent_id`,`sequence`),
-- );


CREATE TABLE IF NOT EXISTS `uspc_current_mainclass` (
  `patent_id` varchar(20) NOT NULL,
  `mainclass_id` varchar(20) NOT NULL DEFAULT '',
  `mainclass_title` varchar(256) DEFAULT NULL,
  `num_assignees` int(10) DEFAULT NULL,
  `num_inventors` int(10) DEFAULT NULL,
  `num_patents` int(10) DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`mainclass_id`),
  foreign key (patent_id) references patent (patent_id)
);

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
  `num_assignees` int(10) DEFAULT NULL,
  `num_inventors` int(10) DEFAULT NULL,
  `first_seen_date` date DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `years_active` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`patent_id`,`sequence`),
  foreign key (patent_id) references patent (patent_id)
);


CREATE TABLE IF NOT EXISTS `assignee_cpc_subsection` (
  `assignee_id` int(10) NOT NULL,
  `subsection_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (assignee_id) references assignee (assignee_id),
  foreign key (subsection_id) references cpc_current_subsection (subsection_id)
);



CREATE TABLE IF NOT EXISTS `assignee_inventor` (
  `assignee_id` int(10) NOT NULL,
  `inventor_id` int(10) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (assignee_id) references assignee (assignee_id),
  foreign key (inventor_id) references inventor (inventor_id)
);



CREATE TABLE IF NOT EXISTS `assignee_nber_subcategory` (
  `assignee_id` int(10) NOT NULL,
  `subcategory_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (assignee_id) references assignee (assignee_id),
  foreign key (`subcategory_id`) references nber (subcategory_id)
);



CREATE TABLE IF NOT EXISTS `assignee_uspc_mainclass` (
  `assignee_id` int(10) NOT NULL,
  `mainclass_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (assignee_id) references assignee (assignee_id),
  foreign key (mainclass_id) references uspc_current_mainclass (mainclass_id)
);



CREATE TABLE IF NOT EXISTS `assignee_year` (
  `assignee_id` int(10) NOT NULL,
  `patent_year` smallint(6) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (assignee_id) references assignee (assignee_id)
);





CREATE TABLE IF NOT EXISTS `cpc_current_subsection_patent_year` (
  `subsection_id` varchar(20) NOT NULL,
  `patent_year` smallint(5) NOT NULL,
  `num_patents` int(10) NOT NULL,
  PRIMARY KEY (`subsection_id`,`patent_year`),
  foreign key (subsection_id) references cpc_current_subsection (subsection_id)
);


CREATE TABLE IF NOT EXISTS `inventor_coinventor` (
  `inventor_id` int(10) NOT NULL,
  `coinventor_id` int(10) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (inventor_id) references inventor (inventor_id),
  foreign key (coinventor_id) references inventor (inventor_id)
);



CREATE TABLE IF NOT EXISTS `inventor_cpc_subsection` (
  `inventor_id` int(10) NOT NULL,
  `subsection_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (inventor_id) references inventor (inventor_id),
  foreign key (subsection_id) references cpc_current_subsection (subsection_id)
);



CREATE TABLE IF NOT EXISTS `inventor_nber_subcategory` (
  `inventor_id` int(10) NOT NULL,
  `subcategory_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (inventor_id) references inventor (inventor_id),
  foreign key (subcategory_id) references nber (subcategory_id)
);



CREATE TABLE IF NOT EXISTS `inventor_uspc_mainclass` (
  `inventor_id` int(10) NOT NULL,
  `mainclass_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (inventor_id) references inventor (inventor_id),
  foreign key (mainclass_id) references uspc_current_mainclass (mainclass_id)
);



CREATE TABLE IF NOT EXISTS `inventor_year` (
  `inventor_id` int(10) NOT NULL,
  `patent_year` smallint(6) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (inventor_id) references inventor (inventor_id)
);



CREATE TABLE IF NOT EXISTS `location_assignee` (
  `location_id` int(10) NOT NULL,
  `assignee_id` int(10) NOT NULL,
  `num_patents` int(10) DEFAULT NULL,
  PRIMARY KEY (`location_id`,`assignee_id`),
  foreign key (location_id) references location (location_id),
  foreign key (assignee_id) references assignee (assignee_id)
);



CREATE TABLE IF NOT EXISTS `location_cpc_subsection` (
  `location_id` int(10) NOT NULL,
  `subsection_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (location_id) references location (location_id),
  foreign key (subsection_id) references cpc_current_subsection (subsection_id)
);



CREATE TABLE IF NOT EXISTS `location_inventor` (
  `location_id` int(10) NOT NULL,
  `inventor_id` int(10) NOT NULL,
  `num_patents` int(10) DEFAULT NULL,
  PRIMARY KEY (`location_id`,`inventor_id`),
  foreign key (location_id) references location (location_id),
  foreign key (inventor_id) references inventor (inventor_id)
);



CREATE TABLE IF NOT EXISTS `location_nber_subcategory` (
  `location_id` int(10) NOT NULL,
  `subcategory_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (location_id) references location (location_id),
  foreign key (subcategory_id) references nber (subcategory_id)
);



CREATE TABLE IF NOT EXISTS `location_uspc_mainclass` (
  `location_id` int(10) NOT NULL,
  `mainclass_id` varchar(20) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (location_id) references location (location_id),
  foreign key (mainclass_id) references uspc_current_mainclass (mainclass_id)
);



CREATE TABLE IF NOT EXISTS `location_year` (
  `location_id` int(10) NOT NULL,
  `year` smallint(6) NOT NULL,
  `num_patents` int(10) NOT NULL,
  foreign key (location_id) references location (location_id)
);



CREATE TABLE IF NOT EXISTS `nber_subcategory_patent_year` (
  `subcategory_id` varchar(20) NOT NULL,
  `patent_year` smallint(5) NOT NULL,
  `num_patents` int(10) NOT NULL,
  PRIMARY KEY (`subcategory_id`,`patent_year`),
  foreign key (subcategory_id) references nber (subcategory_id)
);



CREATE TABLE IF NOT EXISTS `patent_assignee` (
  `patent_id` varchar(20) NOT NULL,
  `assignee_id` int(10) NOT NULL,
  `location_id` int(10) DEFAULT NULL,
  `sequence` smallint(5) NOT NULL,
  PRIMARY KEY (`patent_id`,`assignee_id`),
  foreign key (patent_id) references patent (patent_id),
  foreign key (assignee_id) references assignee (assignee_id),
  foreign key (location_id) references location (location_id)
);



CREATE TABLE IF NOT EXISTS `patent_inventor` (
  `patent_id` varchar(20) NOT NULL,
  `inventor_id` int(10) NOT NULL,
  `location_id` int(10) DEFAULT NULL,
  `sequence` smallint(5) NOT NULL,
  PRIMARY KEY (`patent_id`,`inventor_id`),
  foreign key (patent_id) references patent (patent_id),
  foreign key (inventor_id) references inventor (inventor_id),
  foreign key (location_id) references location (location_id)
);



-- CREATE TABLE IF NOT EXISTS `temp_assignee_num_inventors` (
--   `assignee_id` varchar(36) NOT NULL,
--   `num_inventors` int(10) NOT NULL,
--   PRIMARY KEY (`assignee_id`)
-- );



-- CREATE TABLE IF NOT EXISTS `temp_inventor_num_assignees` (
--   `inventor_id` varchar(36) NOT NULL,
--   `num_assignees` int(10) NOT NULL,
--   PRIMARY KEY (`inventor_id`)
-- );



CREATE TABLE IF NOT EXISTS `uspc_current_mainclass_application_year` (
  `mainclass_id` varchar(20) NOT NULL,
  `application_year` smallint(5) NOT NULL,
  `sample_size` int(10) NOT NULL,
  `average_patent_processing_days` int(10) DEFAULT NULL,
  PRIMARY KEY (`mainclass_id`,`application_year`),
  foreign key (mainclass_id) references uspc_current_mainclass (mainclass_id)
);



CREATE TABLE IF NOT EXISTS `uspc_current_mainclass_patent_year` (
  `mainclass_id` varchar(20) NOT NULL,
  `patent_year` smallint(5) NOT NULL,
  `num_patents` int(10) NOT NULL,
  PRIMARY KEY (`mainclass_id`,`patent_year`),
  foreign key (mainclass_id) references uspc_current_mainclass (mainclass_id)
);

