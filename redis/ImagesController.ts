import * as path from "path";

const fs = require('fs');
import {Image} from "../src/model/Image";
const humanizeDuration = require('humanize-duration');

interface ImageSnapshot {
	id: number;
	source: string;
	thumbRoot: string;
	path: string;
	DateTime: string;
	colors: null|string;
	width: number;
	height: number;
}

export class ImagesController {

	data: ImageSnapshot[];
	started: Date;

	constructor() {
		fs.readFile(path.resolve(__dirname, 'snapshot.json'), (err: NodeJS.ErrnoException, data: Buffer) => {
			if (err) {
				throw err;
			}
			this.data = JSON.parse(data.toString()+'{}]');
			console.log('ready', this.data.length);
			this.data.sort((a, b) => {
				return (a.DateTime ?? '').localeCompare(b.DateTime);
			});
			console.log('sorted', this.data.length);
		});
	}

	start() {
		this.started = new Date();
	}

	async get(request, reply) {
		this.start();
		if (!this.data) {
			return {
				status: 'error',
				message: 'loading',
				duration: humanizeDuration(new Date().getTime() - this.started.getTime()),
			};
		}

		const results = this.data.filter((row: ImageSnapshot) => {
			if (request.query.minWidth) {
				if (!row.width) {
					return false;
				}
				const minWidth = parseInt(request.query.minWidth);
				if (row.width < minWidth) {
					return false;
				}
			}
			if (request.query.maxWidth) {
				if (!row.width) {
					return false;
				}
				const maxWidth = parseInt(request.query.maxWidth);
				if (row.width > maxWidth) {
					return false;
				}
			}
			if (request.query.since) {
				const since = new Date(request.query.since);
				if (new Date(row.DateTime).getTime() < since.getTime()) {
					return false;
				}
			}
			return true;
		});

		const just100 = results.slice(0, 100);

		console.log(request.query);
		return {
			status: 'ok',
			source: this.data.length,
			query: request.query,
			length: results.length,
			duration: humanizeDuration(new Date().getTime() - this.started.getTime()),
			results: just100,
		};
	}

}
