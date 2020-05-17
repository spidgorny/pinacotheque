async function search() {
	const data = require('./snapshot.json');
	console.log('source', data.length);
	const results = data.filter((row) => {
		return row.DateTime > '2019-01-01';
	});
	console.log(results.length);
	console.table(results.slice(0, 10));
}

search();
