drop database if exists logigator;
create database logigator;
use logigator;

GRANT ALL ON logigator.* TO 'logigator'@'localhost' IDENTIFIED BY 'logigator';

create table users
(
  pk_id 			int auto_increment primary key,
  username 			varchar(100) not null unique,
  password 			varchar(100),
  social_media_key	varchar(100),
  email 			varchar(100) not null unique,
  login_type 		ENUM ('local', 'google', 'twitter') not null,
  profile_image		varchar(100),
  constraint constraint_check_login 
	check ( (login_type = 'local' and password is not null) or (login_type != 'local' and social_media_key is not null) )
);

create table projects
(
  pk_id 				int auto_increment primary key,
  location 				varchar(100) unique not null,
  name					varchar(100) not null,
  description			varchar(500) not null default '',
  symbol				varchar(10),
  fk_user				int not null,
  fk_originates_from	int,
  last_edited			timestamp default CURRENT_TIMESTAMP not null,
  created_on			timestamp default CURRENT_TIMESTAMP not null,
  is_component			tinyint(1) not null,
  
  constraint constraint_check_symbol
	check (is_component = 0 or symbol is not null),
  
  constraint constraint_projects_fk_user
    foreign key (fk_user) references users (pk_id)
      on update cascade on delete cascade,
  constraint constraint_projects_fk_originates_from
    foreign key (fk_originates_from) references projects (pk_id)
      on update cascade on delete set null
);

create table links
(
  pk_id			int auto_increment primary key,
  address		char(100) not null unique,
  is_public		tinyint(1) default 0 not null,
  fk_project	int not null,

  constraint constraint_links_fk_project
    foreign key (fk_project) references projects (pk_id)
      on update cascade on delete cascade
);

create table link_permits
(
  fk_user int,
  fk_link int,
  
  primary key (fk_user, fk_link),
  
  constraint constraint_link_permits_fk_link
    foreign key (fk_link) references links (pk_id)
      on update cascade on delete cascade,
  constraint constraint_link_permits_fk_user
    foreign key (fk_user) references users (pk_id)
      on update cascade on delete cascade
);

