select source, type, path, count(meta.id) as count_meta
from files
         left outer join meta on meta.id_file = files.id
where source is null
group by source, type, path
having count_meta > 0;

select files.id, files.source, files.type, files.path, f2.id, f2.source
from files
         left outer join files as f2 on f2.path = files.path
where files.source is null
  and f2.source is not null;

select count(id) from files where source = 3;
select count(id) from files where source is null;

delete from files where source is null;

select * from files
where meta_error > '';

select source.id, source.name, count(files.id), count(files.meta_error > '')
from files left outer join meta on meta.id_file = files.id
           join source on source.id = files.source
where meta.id is null
group by source.id, source.name;

select files.id, meta.id, meta.id is not null
from files
         left outer join meta on meta.id_file = files.id;

select files.id, sum(meta.id is not null) as count_meta
from files
         left outer join meta on meta.id_file = files.id
where type = 'file'
group by files.id;

select count(id), sum(count_meta > 0) as has_meta, sum(count_meta > 0) / count(id) * 100 as percent_has_meta
from (
         select files.id, sum(meta.id is not null) as count_meta
         from files
                  left outer join meta on meta.id_file = files.id
         where type = 'file'
         group by files.id
     ) as sub1;
