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

	protected source;
	protected year;
	protected month;

	constructor() {
		this.fetchGPSdata();
	}

	public async fetchGPSdata() {
		const url = new URL(document.location.href);
		this.source = url.searchParams.get('source');
		this.year = url.searchParams.get('year');
		this.month = url.searchParams.get('month');

		url.searchParams.set('action', 'gps');
		const res = await fetch(url.toString(), {});
		const json = await res.json();
		console.log(json);
		if (json) {
			this.makeMap(json);
		}
	}

	public makeMap(json: ImageGPS[]) {
		const arrayOfLatLngs = json.map((info: any) => {
			return [info.lat, info.lon];
		});
		const bounds = new L.LatLngBounds(arrayOfLatLngs);

		const map = L.map('mapid');
		//mymap.setView([51.505, -0.09], 13);
		map.fitBounds(bounds);

		const osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		L.tileLayer(osmUrl, {
			maxZoom: 18,
		}).addTo(map);

		json.map((info: any) => {
			const clickURL = `Preview?source=${this.source}&year=${this.year}&month=${this.month}&file=${info.id}`;
			const imageURL = `ShowThumb?file=${info.id}`;
			L.marker([info.lat, info.lon], {
				title: info.path,
				riseOnHover: true,
			}).addTo(map)
				.bindPopup(`
					<p>
					<a href="${clickURL}">
					<img src="${imageURL}" />
					</a>
					</p>
					<p>${info.path}</p>
				`);
		});

		map.on('zoomend', (e) => {
			console.log('zoom', e);
		});
		map.on('moveend', (e) => {
			console.log('move', e);
		});
	}
}

