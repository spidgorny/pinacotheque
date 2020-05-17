const Redis = require("ioredis");
const mysql = require('mysql2');
const stream = require('stream');
const fs = require('fs');
const ProgressBar = require('progress');

class Snapshot {

	redis;
	connection;

	constructor() {
		const redis = new Redis({
			host: '192.168.1.120',
		}); // uses defaults unless given configuration object
		this.redis = redis;
		const connection = mysql.createConnection({
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
                       FROM files JOIN source on source.id = source
                       WHERE type = "file"
                         AND DateTime is not null`;
		const total = await this.getNumRows(query);
		const bar = new ProgressBar(':current / :total :bar :percent :eta sec', {total});
		const results = this.connection.query(query);

		const file = fs.openSync(__dirname + '/snapshot.json', 'w');
		fs.writeSync(file, '[');
		const transform = stream.Transform({
			objectMode: true,
			transform: async (data, encoding, callback) => {
				bar.tick();
				const meta = await this.fetchMeta(data.id);
				data.width = meta.COMPUTED?.width;
				fs.writeSync(file, JSON.stringify(data, null, 2) + ',');
				// console.log(data);
				callback();
			}
		});

		results.stream().pipe(transform)
			.on('finish', () => {
				console.log('done');
				fs.writeSync(file, '{}]');
				fs.closeSync(file);
				this.stopEverything();
			});

	}

	async getNumRows(query: string) {
		const countQuery = 'SELECT count(*) AS count FROM (' + query + ') AS asd';
		const count = await this.connection.promise().query(countQuery);
		// console.log(count, count[0][0]);
		return count[0][0].count;
	}

	async fetchMeta(id: number) {
		const query = `SELECT *
                       FROM meta
                       WHERE id_file = "?"`;
		const metaData = await this.connection.query(query, [id]);
		console.log(metaData);
		process.exit();
		return metaData;
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
