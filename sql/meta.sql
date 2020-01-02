CREATE TABLE "meta"
(
    "id"      INTEGER PRIMARY KEY AUTOINCREMENT,
    "id_file" INTEGER,
    "name"    TEXT,
    "value"   TEXT,
    UNIQUE(id_file, name)
);

create unique index meta_file_name_uindex
    on meta ("id_file", "name");
