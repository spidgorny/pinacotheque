const Redis = require("ioredis");
const redis = new Redis({
	host: '192.168.1.120',
}); // uses defaults unless given configuration object
const mysql = require('mysql2');
const stream = require('stream');

const connection = mysql.createConnection({
	host: '192.168.1.120',
	user: 'slawa',
	database: 'pina',
	password: '123',
});


async function main() {
	redis.set("foo", "bar");

	const result = await redis.get("foo");
	console.log(result);
	redis.quit();


	const results = connection.query(
		'SELECT * FROM `files` WHERE `type` = "file" AND `DateTime` is not null');
	results.stream().pipe(transform)
		.on('finish', () => {
			console.log('done');
			stopEverything();
		});

}

function stopEverything() {
	connection.close();
}

const transform = stream.Transform({
	objectMode: true,
	transform: (data, encoding, callback) => {
		console.log(data);
		callback();
	}
});

const sink = stream.Writable({
	start(controller) {
		console.log('start');
	},
	write(chunk, controller) {
		console.log(chunk);
	},
	close() {
		console.log('close');
	},
	abort(reason) {

	}
});

main();
