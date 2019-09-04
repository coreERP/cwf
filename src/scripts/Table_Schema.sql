CREATE EXTENSION "uuid-ossp";

create schema sys;

CREATE TABLE sys.user
(   user_id bigint NOT NULL,
    user_name varchar(20) NOT NULL,
    user_pass varchar(60) NOT NULL,
    full_user_name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    is_active boolean NOT NULL DEFAULT false,
    is_owner boolean NOT NULL default false,
    is_admin boolean default false,
    auth_client Varchar(50) NOT NULL DEFAULT '',
    auth_person_id Varchar(50) NOT NULL DEFAULT '',
    auth_account Varchar(250) NOT NULL DEFAULT '',
    mobile varchar(50) not null DEFAULT '',
    phone varchar(50) not null DEFAULT '',
    clfy_access boolean not null DEFAULT (FALSE),
    user_attr jsonb Not Null Default '{}',
    last_updated timestamp Not Null default current_timestamp(0),
    CONSTRAINT pk_sys_user PRIMARY KEY (user_id),
    CONSTRAINT uk_sys_user_user_name UNIQUE (user_name)
);

?==?
CREATE TABLE sys.user_session
(   user_session_id varchar(32) NOT NULL,
    user_id bigint NOT NULL,
    auth_id varchar(32) NOT NULL,
    login_time timestamp without time zone NOT NULL,
    last_refresh_time timestamp without time zone NOT NULL,
    session_info text NOT NULL,
    CONSTRAINT pk_sys_user_session PRIMARY KEY (user_session_id)
);

?==?
Create Table sys.menu_seq
(   menu_level Varchar(1) Not Null,
    max_id BigInt Not Null,
    Constraint pk_sys_menu_seq Primary Key (menu_level)
);

?==?
CREATE TABLE sys.menu
(   menu_id bigint NOT NULL,
    parent_menu_id BigInt NOT NULL,
    menu_key Varchar(10),
    menu_name varchar(100) NOT NULL,
    menu_text varchar(250) NOT NULL,
    menu_type smallint NOT NULL,
    bo_id uuid NULL,
    is_hidden boolean NOT NULL,
    last_updated timestamp without time zone NOT NULL,
    link_path varchar(250),
    menu_code varchar(4) not null default '',
    is_staged boolean not null default(false),
    count_class character varying not null default '',
    vch_type varchar[] default '{}',
    CONSTRAINT pk_sys_menu PRIMARY KEY (menu_id)
);

?==?
CREATE UNIQUE INDEX uk_sys_menu on sys.menu (Lower(menu_name));

?==?