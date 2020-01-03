CREATE TABLE meta
(
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    id_file INTEGER,
    name    varchar(255),
    value   TEXT,
    UNIQUE(id_file, name)
);
