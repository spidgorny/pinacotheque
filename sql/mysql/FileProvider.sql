SELECT min(coalesce(meta.value, date_format(from_unixtime(timestamp), '%Y:%m:%d %H:%i:%s'))) as min,
       max(coalesce(meta.value, date_format(from_unixtime(timestamp), '%Y:%m:%d %H:%i:%s'))) as max
FROM files
         LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")
WHERE source = '2'
  AND type = 'file'
  AND substr(path, -4) IN ('jpeg', '.jpg', '.png', '.gif', '.mp4', '.mov', '.mkv', 'tiff', '.tif')
  AND (meta.value IS NULL or meta.value != '0000:00:00 00:00:00');

SELECT
    min(files.id) as id,
    CASE WHEN meta.value THEN
             replace(substr(meta.value, 0, 8), ':', '-')
         ELSE date_format(from_unixtime(timestamp), '%Y-%m')
        END as YM,
    count(*) as count
FROM files
         LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")
WHERE
        source = '2'
  AND type = 'file'
  AND substr(path, -4) IN ('jpeg', '.jpg', '.png', '.gif', '.mp4', '.mov', '.mkv', 'tiff', '.tif')
GROUP BY  CASE WHEN meta.value THEN
                   replace(substr(meta.value, 0, 8), ':', '-')
               ELSE date_format(from_unixtime(timestamp), '%Y-%m')
              END
ORDER BY CASE WHEN meta.value THEN replace(substr(meta.value, 0, 8), ':', '-') ELSE date_format(from_unixtime(timestamp), '%Y-%m') END;
