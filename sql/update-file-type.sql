alter table files
    add ext varchar(255) null;

create index files_ext_index
    on files (ext);

UPDATE files
SET ext = substr(path, -4)
WHERE type = 'file';
