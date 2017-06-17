drop procedure if exists social_logins_charset_fix;

delimiter //
create procedure social_logins_charset_fix()
begin

ALTER TABLE social_login_users CONVERT TO CHARACTER SET utf8;
ALTER TABLE social_login_user_realms CONVERT TO CHARACTER SET utf8;

end//

delimiter ;
call social_logins_charset_fix;

