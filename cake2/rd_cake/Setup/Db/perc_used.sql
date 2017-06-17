alter table users add column `perc_time_used` int(6) DEFAULT NULL;
alter table users add column `perc_data_used` int(6) DEFAULT NULL;

alter table devices add column `perc_time_used` int(6) DEFAULT NULL;
alter table devices add column `perc_data_used` int(6) DEFAULT NULL;
