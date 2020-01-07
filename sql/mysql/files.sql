create table files (
                       id INTEGER PRIMARY KEY AUTO_INCREMENT,
                       source integer,
                       type varchar(255),
                       path varchar(255),
                       timestamp numeric,
                       UNIQUE(source, path)
);