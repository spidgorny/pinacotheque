alter table files
    add ext varchar(255) null;

create index files_ext_index
    on files (ext);

UPDATE files
SET ext = substr(path, -4)
WHERE type = 'file';

ALTER TABLE files ADD ym varchar(7) null;
CREATE INDEX files_ym_index ON files(ym);

UPDATE files
JOIN meta ON (meta.id_file = files.id AND meta.name = 'DateTime')
SET files.ym = date_format(replace(substr(meta.value, 1, 7), ':', '-'), '%Y-%m')
WHERE NOT files.ym;
