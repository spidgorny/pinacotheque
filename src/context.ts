import {createContext} from "react";

export class AppContext {
	public baseUrl = new URL('http://192.168.1.120/');
}

export var context = createContext(new AppContext());
