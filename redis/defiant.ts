const defiant = require('defiant.js');
const data = require('./snapshot.json');

export async function search() {
	const search = await defiant.search(data, '//.[id=3]');
	console.log(search);
}

search();

// requires puppeteer to work in Node
