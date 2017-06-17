drop procedure if exists photo_more_enhancements;

delimiter //
create procedure photo_more_enhancements()
begin

if not exists (select * from information_schema.columns
    where column_name = 'fit' and table_name = 'dynamic_photos' and table_schema = 'rd') then
    alter table dynamic_photos add column `fit` enum('stretch_to_fit','horizontal','vertical','original') DEFAULT 'stretch_to_fit';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'background_color' and table_name = 'dynamic_photos' and table_schema = 'rd') then
    alter table dynamic_photos add column `background_color` varchar(7) NOT NULL DEFAULT 'ffffff';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'slide_duration' and table_name = 'dynamic_photos' and table_schema = 'rd') then
    alter table dynamic_photos add column `slide_duration` int(4) NOT NULL DEFAULT 10;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'include_title' and table_name = 'dynamic_photos' and table_schema = 'rd') then
    alter table dynamic_photos add column `include_title` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'include_description' and table_name = 'dynamic_photos' and table_schema = 'rd') then
    alter table dynamic_photos add column `include_description` tinyint(1) NOT NULL DEFAULT '1';
end if;


if not exists (select * from information_schema.columns
    where column_name = 'slideshow_enforce_watching' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `slideshow_enforce_watching` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'slideshow_enforce_seconds' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `slideshow_enforce_seconds` int(4) NOT NULL DEFAULT 10;
end if;

alter table dynamic_photos MODIFY column `fit` enum('stretch_to_fit','horizontal','vertical','original','dynamic') DEFAULT 'stretch_to_fit';

end//

delimiter ;
call photo_more_enhancements

