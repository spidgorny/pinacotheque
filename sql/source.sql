CREATE TABLE "source"
(
    "id"        INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT,
    "path"      TEXT,
    "thumbRoot" TEXT
);

alter table source
    add files int null;

alter table source
    add folders int null;

alter table source
    add md5 varchar(255) null;

alter table source
    add mtime timestamp default CURRENT_TIMESTAMP null;

