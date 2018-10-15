CREATE TABLE photo
(
	filename varchar(255),
	hash     varchar(255),
	lat      real,
	lon      real,
	date_taken varchar,
	date_uploaded varchar,
	flickr_secret varchar,
	flickr_server int,
	flickr_farm int,
	title varchar
);

ALTER TABLE photo ADD COLUMN date_taken varchar;
ALTER TABLE photo ADD COLUMN date_uploaded varchar;
ALTER TABLE photo ADD COLUMN flickr_secret varchar;
ALTER TABLE photo ADD COLUMN flickr_server int;
ALTER TABLE photo ADD COLUMN flickr_farm int;
ALTER TABLE photo ADD COLUMN title varchar;

DELETE FROM photo WHERE filename IS NULL;
