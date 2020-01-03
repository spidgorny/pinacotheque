-- min, max

SELECT
    coalesce(meta.value, strftime("%Y:%m:%d %H:%M:%S", datetime(timestamp, 'unixepoch'))) as min,
    coalesce(meta.value, strftime("%Y:%m:%d %H:%M:%S", datetime(timestamp, 'unixepoch'))) as max,
       files.*, meta.*
FROM `files`
         LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")
WHERE
        `source` = '3'
  AND `type` = 'file'
  AND substr(path, -4) IN ('jpeg', '.jpg', '.png', '.gif', '.mp4', '.mov', '.mkv', 'tiff', '.tif');

-- one per month
SELECT
    *, CASE WHEN meta.value THEN strftime('%Y-%m', replace(substr(meta.value, 0, 11), ':', '-') || substr(meta.value, 11))
            ELSE strftime('%Y-%m', datetime(timestamp, 'unixepoch'))
    END as YM, count(*) as count
FROM `files`
         LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")
WHERE
        `source` = '3'
  AND `type` = 'file'
  AND substr(path, -4) IN ('jpeg', '.jpg', '.png', '.gif', '.mp4', '.mov', '.mkv', 'tiff', '.tif')
GROUP BY CASE WHEN meta.value THEN strftime('%Y-%m', replace(substr(meta.value, 0, 11), ':', '-') || substr(meta.value, 11))
              ELSE strftime('%Y-%m', datetime(timestamp, 'unixepoch'))
             END
ORDER BY CASE WHEN meta.value THEN strftime('%Y-%m', replace(substr(meta.value, 0, 11), ':', '-') || substr(meta.value, 11))
              ELSE strftime('%Y-%m', datetime(timestamp, 'unixepoch'))
             END;
