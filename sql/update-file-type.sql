alter table files
    add ext varchar(255) null;

create index files_ext_index
    on files (ext);

UPDATE files
SET ext = substr(path, -4)
WHERE type = 'file' AND ext is null;

ALTER TABLE files ADD ym varchar(7) null;
CREATE INDEX files_ym_index ON files(ym);

UPDATE files
JOIN meta ON (meta.id_file = files.id AND meta.name = 'DateTime')
SET files.ym = date_format(replace(substr(meta.value, 1, 7), ':', '-'), '%Y-%m')
WHERE NOT files.ym;

# this is hopeless because the format can be very different
UPDATE files
    JOIN meta ON (meta.id_file = files.id AND meta.name = 'DateTime')
SET files.DateTime = date_format(substr(replace(meta.value, ':', '-'), 1, 16), '%Y-%m-%d %H:%i:%s');
# WHERE NOT files.DateTime;

select count(id) FROM files where DateTime is not null;
select count(distinct files.id) FROM files inner join meta on meta.id_file = files.id where DateTime is not null and meta.id is not null;
select count(id) FROM files;
select count(distinct id_file) FROM meta;
select count(id) from meta where name='DateTime';   # 437616
select count(id) from files where DateTime > '';    # 165719
select substr(meta.value, 1, 5), count(id) from meta where name='DateTime' group by substr(meta.value, 1, 5);

select substr(meta.value, 1, 5), count(id) from meta
join meta m2 on (meta.id_file = m2.id_file and meta.name = '')
where meta.name='DateTime'
group by substr(meta.value, 1, 5);

select distinct name, count(id) from meta group by name;

select substr(DateTime, 1, 5), count(id) from files
where DateTime > ''
group by substr(DateTime, 1, 5);

select * from files where DateTime like '14691%';

select * from files
where DateTime < now()
order by DateTime desc;
