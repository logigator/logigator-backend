drop database if exists logigator;
create database logigator;
use logigator;

GRANT ALL ON logigator.* TO 'logigator'@'localhost' IDENTIFIED BY 'logigator';

create table users
(
    pk_id            int auto_increment primary key,
    username         varchar(100) not null unique,
    password         varchar(100),
    social_media_key varchar(100),
    email            varchar(100) not null unique,
    login_type       enum ('local', 'local_not_verified', 'google', 'twitter') not null,
    profile_image    varchar(100),

    constraint constraint_check_login
        check ( ((login_type = 'local' or login_type = 'local_not_verified') and password is not null) or (login_type != 'local' and login_type != 'local_not_verified' and social_media_key is not null) )
) AUTO_INCREMENT=1000;

create table shortcuts
(
    pk_id int auto_increment primary key,
    fk_user int not null,
    name enum ('copy', 'paste', 'cut', 'delete', 'undo', 'redo', 'zoom100', 'zoomIn', 'zoomOut',
               'fullscreen', 'connWireMode', 'wireMode', 'selectMode', 'newComp', 'textMode', 'eraserMode', 'save',
               'openProj', 'newProj', 'cutSelectMode', 'enterSim', 'leaveSim') not null,
    key_code varchar(10) not null,
    shift bit not null default 0,
    ctrl bit not null default 0,
    alt bit not null default 0,

    constraint constraint_shortcuts_fk_user
        foreign key (fk_user) references users(pk_id)
            on update cascade on delete cascade,
    constraint constraint_shortcuts_unique unique (fk_user, name)
) AUTO_INCREMENT=1000;

create table projects
(
    pk_id              int auto_increment primary key,
    location           varchar(100) unique not null,
    name               varchar(100) not null,
    description        varchar(1000) not null default '',
    fk_user            int not null,
    fk_originates_from int,
    last_edited        timestamp default CURRENT_TIMESTAMP not null,
    created_on         timestamp default CURRENT_TIMESTAMP not null,
    is_component       bit not null,
    symbol             varchar(10),
    num_inputs         int,
    num_outputs        int,
    labels             varchar(3072),
    version            int default 0 not null,

    constraint constraint_projects_check_symbol
        check (is_component = 0 or symbol is not null),
    constraint constraint_projects_check_num_inputs
        check (is_component = 0 or num_inputs is not null),
    constraint constraint_projects_check_num_outputs
        check (is_component = 0 or num_outputs is not null),
    constraint constraint_projects_check_labels
        check (is_component = 0 or labels is not null),

    constraint constraint_projects_fk_user
        foreign key (fk_user) references users (pk_id)
            on update cascade on delete cascade,
    constraint constraint_projects_fk_originates_from
        foreign key (fk_originates_from) references projects (pk_id)
            on update cascade on delete set null
) AUTO_INCREMENT=1000;

create table links
(
    pk_id      int auto_increment primary key,
    address    varchar(100) not null unique,
    is_public  bit default 0 not null,
    fk_project int not null unique,

    constraint constraint_links_fk_project
        foreign key (fk_project) references projects (pk_id)
            on update cascade on delete cascade
) AUTO_INCREMENT=1000;

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
) AUTO_INCREMENT=1000;
