-- drop table files;
create table files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source integer,
    type varchar(255),
    path varchar(255),
    timestamp numeric
)
