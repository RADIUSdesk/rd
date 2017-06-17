alter table dynamic_details add column `t_c_check` tinyint(1) NOT NULL DEFAULT '0';
alter table dynamic_details add column `t_c_url` char(50) NOT NULL DEFAULT '';

alter table users add column `perc_time_used` int(6) DEFAULT NULL;
alter table users add column `perc_data_used` int(6) DEFAULT NULL;
alter table devices add column `perc_time_used` int(6) DEFAULT NULL;
alter table devices add column `perc_data_used` int(6) DEFAULT NULL;

