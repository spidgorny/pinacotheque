const Redis = require("ioredis");
const mysql = require('mysql2');
const stream = require('stream');
const fs = require('fs');
const ProgressBar = require('progress');

export class Snapshot {

	redis;
	connection;
	bar;
	file;

	constructor() {
		const redis = new Redis({
			host: '192.168.1.120',
		}); // uses defaults unless given configuration object
		this.redis = redis;
		const connection = mysql.createPool({
			connectionLimit: 10,
			host: '192.168.1.120',
			user: 'slawa',
			database: 'pina',
			password: '123',
		});
		this.connection = connection;
	}

	async testRedis() {
		this.redis.set("foo", "bar");

		const result = await this.redis.get("foo");
		console.log(result);
		this.redis.quit();
	}

	async main() {
		const query = `SELECT files.id,
                              source.path as source,
                              source.thumbRoot,
                              files.path,
                              DateTime,
                              colors
                       FROM files
                                JOIN source on source.id = source
                       WHERE type = "file"
                         AND DateTime is not null`;
		const total = await this.getNumRows(query);
		this.bar = new ProgressBar(':current / :total :bar :percent :eta sec', {total});
		const results = this.connection.query(query);

		this.file = fs.openSync(__dirname + '/snapshot.json', 'w');
		fs.writeSync(this.file, '[');
		const transform = stream.Transform({
			objectMode: true,
			transform: (data, encoding, callback) => {
				this.processRow(data, callback);
			}
		});

		results.stream().pipe(transform)
			.on('finish', () => {
				console.log('done');
				fs.writeSync(this.file, '{}]');
				fs.closeSync(this.file);
				this.stopEverything();
			});
	}

	async getNumRows(query: string) {
		const countQuery = 'SELECT count(*) AS count FROM (' + query + ') AS asd';
		const count = await this.connection.promise().query(countQuery);
		// console.log(count, count[0][0]);
		return count[0][0].count;
	}

	async fetchMeta(id: number): Promise<any> {
		const query = `SELECT *
                       FROM meta
                       WHERE id_file = "?"`;
		// console.log(query);
		const metaDataRows = await this.connection.promise().query(query, [id]);
		// console.log(metaData[0]);
		// console.log(metaDataRows[0].length);
		const metaData = {};
		for (let row of metaDataRows[0]) {
			let value = row.value;
			if (value.length && (value[0] === '{' || value[0] === '[')) {
				try {
					const jsonMaybe = JSON.parse(value);
					value = jsonMaybe;
				} catch (e) {
				}	// do nothing
			}
			metaData[row.name] = value;
		}
		return metaData;
	}

	async processRow(data, callback: () => void) {
		this.bar.tick();
		const meta = await this.fetchMeta(data.id);
		data.width = this.getWidth(meta);
		data.height = this.getHeight(meta);
		fs.writeSync(this.file, JSON.stringify(data, null, 2) + ',');
		// console.log(data);
		callback();
	}

	getWidth(meta: any) {
		if ('COMPUTED' in meta) {
			return parseInt(meta.COMPUTED.Width);
		}
		if ('streams' in meta/* && this.meta.streams.length*/) {
			return parseInt(meta.streams[0].width, 10);
		}
	}

	getHeight(meta: any) {
		if ('COMPUTED' in meta) {
			return parseInt(meta.COMPUTED.Height, 10);
		}
		if ('streams' in meta/* && this.meta.streams.length*/) {
			return parseInt(meta.streams[0].width, 10);
		}
	}

	stopEverything() {
		this.connection.close();
	}
}

const sink = stream.Writable({
	start(controller) {
		console.log('start');
	},
	write(chunk, controller) {
		// console.log(chunk);
	},
	close() {
		console.log('close');
	},
	abort(reason) {

	}
});

const snapshot = new Snapshot();
snapshot.main();
