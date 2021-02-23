update files set DateTime = null where DateTime = '0000-00-00 00:00:00';
update files set DateTime = substring(DateTime, 0, 19) where length(DateTime) > 19;
update files set DateTime = null where DateTime = '';

alter table files modify DateTime timestamp null;

alter table files
    add width int null;

alter table files
    add height int null;

create fulltext index idx_file_path on files (path);
