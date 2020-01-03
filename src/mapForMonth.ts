declare var L: any;

document.addEventListener("DOMContentLoaded", async () => {
	new MapManager();
});

interface ImageGPS {
	id: number;
	id_file: number;
	name: "DateTime";
	value: string;
	source: number;
	type: "file";
	path: string;
	timestamp: number;
	YM: string;
	lat: number;
	lon: number;
}

class MapManager {

	protected url: URL;
	protected source: number;
	protected year: number;
	protected month: number;
	protected map: any;

	constructor() {
		this.fetchGPSdata();
	}

	public async fetchGPSdata() {
		this.url = new URL(document.location.href);
		this.source = parseInt(this.url.searchParams.get('source'), 10);
		this.year = parseInt(this.url.searchParams.get('year'), 10);
		this.month = parseInt(this.url.searchParams.get('month'), 10);

		const url = new URL(this.url.toString());
		url.searchParams.set('action', 'gps');
		url.searchParams.set('bounds', this.getBoundsFromURL);
		const res = await fetch(url.toString(), {});
		const json = await res.json();
		console.log(json);
		if (json) {
			this.makeMap(json);
		}
	}

	public get getBoundsFromURL() {
		return this.url.searchParams.get('bounds');
	}

	public makeMap(json: ImageGPS[]) {
		const arrayOfLatLngs = json.map((info: any) => {
			return [info.lat, info.lon];
		});
		const bounds = new L.LatLngBounds(arrayOfLatLngs);

		this.map = L.map('mapid');
		//mymap.setView([51.505, -0.09], 13);
		this.map.fitBounds(bounds);

		const osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		L.tileLayer(osmUrl, {
			maxZoom: 18,
		}).addTo(this.map);

		json.map((info: any) => {
			const clickURL = `Preview?source=${this.source}&year=${this.year}&month=${this.month}&file=${info.id}`;
			const imageURL = `ShowThumb?file=${info.id}`;
			L.marker([info.lat, info.lon], {
				title: info.path,
				riseOnHover: true,
			}).addTo(this.map)
				.bindPopup(`
					<p>
					<a href="${clickURL}">
					<img src="${imageURL}" />
					</a>
					</p>
					<p>${info.path}</p>
				`);
		});

		this.map.on('zoomend', (e) => {
			// console.log('zoom', e);
			this.updateImageRows();
			this.updateURL();
		});
		this.map.on('moveend', (e) => {
			// console.log('move', e);
			this.updateImageRows();
			this.updateURL();
		});
	}

	public get getBoundsJSON() {
		const bounds = this.map.getBounds();
		// console.log(bounds);
		return JSON.stringify({
			west: bounds.getWest(),
			east: bounds.getEast(),
			north: bounds.getNorth(),
			south: bounds.getSouth(),
		});
	}

	public async updateImageRows() {
		const ajaxURL = new URL(this.url.toString());
		ajaxURL.pathname = 'MonthBrowserDB';
		ajaxURL.searchParams.set('action', 'filterByGPS');
		ajaxURL.searchParams.set('source', this.source.toString());
		ajaxURL.searchParams.set('year', this.year.toString());
		ajaxURL.searchParams.set('month', this.month.toString());
		ajaxURL.searchParams.set('bounds', this.getBoundsJSON);
		// console.log(ajaxURL);
		const html = await (await fetch(ajaxURL.toString())).text();
		document.querySelector('#imageRows').innerHTML = html;
	}

	public updateURL()
	{
		const newURL = new URL(document.location.href);
		newURL.searchParams.set('bounds', this.getBoundsJSON);
		window.history.replaceState({}, document.title, newURL.toString());
	}

}

