import mysql from 'mysql2/promise'
import dotenv from 'dotenv'
import {Builder} from "json-sql";
import * as path from "path";
import * as fs from "fs";
import fileExists from 'file-exists';

dotenv.config({path: '../.env'})

let sources = {}
const db = await mysql.createConnection({
	host: process.env.mysql_host,
	user: process.env.mysql_user,
	password: process.env.mysql_password,
	database: process.env.mysql_db,
});

const SQL = new Builder({dialect: 'mysql'})

await processYears();

await db.end();

async function processYears() {
	const years = [2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022]

	for (let year of years) {
		const query = `SELECT *
				   FROM files
							JOIN meta ON (meta.id_file = files.id AND name = 'DateTime')
				   WHERE value like '${year}:11:22%'`;

		const [rows] = await db.query(query);

		console.log(year, rows.length);

		await copyFiles(year, rows);
	}
}

async function copyFiles(year, rows) {
	await Promise.all(rows.map(async row => {
		const sourceRow = await getSource(row.source);
		// console.log(sourceRow)
		let filePath = path.join(sourceRow.path, row.path);
		filePath = filePath.replace('\\media\\slawa\\Elements\\PhotosNSA', 'P:');
		filePath = filePath.replace('\\media\\slawa\\My Book\\marina', 'P:')
		filePath = filePath.replace('\\media\\slawa\\Elements\\Slawa-dv7\\Pictures', 'P:')
		filePath = filePath.replace('\\media\\nas\\photo', 'P:')
		filePath = filePath.replace('\\media\\slawa\\My Book\\slawa\\Photo\\Pictures', 'P:')
		let exists = await fileExists(filePath);
		if (exists) {
			await copyToDestination(year, filePath)
			process.stdout.write('.');
		} else {
			console.log(filePath, {exists})
		}
	}));
	console.log();
}

async function copyToDestination(year, filePath) {
	const destination = 'tmp';
	const destinationYear = path.join(destination, year+'');
	fs.mkdirSync(destinationYear, {recursive: true});
	const destinationFile = path.join(destinationYear, path.basename(filePath));
	let exists = await fileExists(destinationFile);
	if (!exists) {
		fs.copyFileSync(filePath, destinationFile);
	}
}

async function getSource(sourceId) {
	if (sources[sourceId]) {
		return sources[sourceId];
	}
	const sql = SQL.build({
		type: 'select',
		table: 'source',
		condition: {id: sourceId}
	});

	const [rows] = await db.query(sql.query, sql.values);
	sources[sourceId] = rows[0]
	return rows[0]
}
