-- drop table files;
create table files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source integer,
    type varchar(255),
    path varchar(255),
    timestamp numeric,
    UNIQUE(source, path)
);

create unique index files_source_path_uindex
    on files (source, path);
