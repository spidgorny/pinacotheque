import {createContext} from "react";

export class AppContext {
	public baseUrl = new URL(process.env.REACT_APP_API);
	public sidebar = {
		minWidth: 640,
	};
	public static readonly VIEWPORT_TIMESTAMP: string = 'viewportTimestamp';
	lastTopTimestamp: Date;

	constructor() {
		const lastTopTimestamp = this.getStorage(AppContext.VIEWPORT_TIMESTAMP);
		if (lastTopTimestamp) {
			this.lastTopTimestamp = new Date(lastTopTimestamp);
		}
		console.log('lastTopTimestamp', this.lastTopTimestamp);
		this.sidebar = this.getStorage('sidebar');
		console.log('sidebar', this.sidebar);
	}

	setStorage(name: string, val: any) {
		window.localStorage.setItem(name, JSON.stringify(val));
		console.log('setStorage', name, val);
	}

	getStorage(name: string) {
		return JSON.parse(window.localStorage.getItem(name));
	}

	setState(props: object) {
		for (let key of Object.keys(props)) {
			this.setStorage(key, props[key]);
		}
	}

}

export var context = createContext(new AppContext());
