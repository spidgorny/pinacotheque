create table files
(
    id        INTEGER PRIMARY KEY AUTO_INCREMENT,
    source    integer not null,
    type      varchar(255) not null,
    path      varchar(255) not null,
    timestamp numeric not null,
    UNIQUE (source, path)
);
alter table files
    add DateTime varchar(255) null;

alter table files
    add meta_timestamp timestamp null;

alter table files
    add meta_error text null;

alter table files add mtime timestamp null;
