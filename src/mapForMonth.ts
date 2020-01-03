declare var L: any;

document.addEventListener("DOMContentLoaded", async () => {
	const url = new URL(document.location.href);
	url.searchParams.set('action', 'gps');
	const res = await fetch(url.toString(), {
	});
	const json = await res.json();
	console.log(json);
	if (json) {
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
			L.marker([info.lat, info.lon]).addTo(map);
		});
	}
});

