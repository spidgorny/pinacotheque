import {ImagesController} from "./ImagesController";

const fastify = require('fastify');

const server = fastify();

server.register(require('fastify-cors'), {
	// put your options here
});

server.get('/', async (request, reply) => {
	console.log(request);
	return {
		['http://' + request.hostname + '/ping']: 'just ping',
		['http://' + request.hostname + '/images']: {
			'?minWidth=': '640',
			'?since=': '2020-01-01',
		},
	};
});

server.get('/ping', async (request, reply) => {
	return 'pong\n';
});

const images = new ImagesController();
server.get('/images', async (request, reply) => {
	return images.get(request, reply);
});

server.listen(8080, (err, address) => {
	if (err) {
		console.error(err);
		process.exit(1);
	}
	console.log(`Server listening at ${address}`);
});
