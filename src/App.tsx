import React from "react";
import ImageStream from "./ImageStream";
import {AppContext, context} from "./context";
import axios from 'redaxios';

interface IAppProps {
}

interface IAppState {
}

export default class App extends React.Component<IAppProps, IAppState> {

	static contextType = context;
	context: AppContext;
	baseUrl;

	componentDidMount() {
		this.baseUrl = this.context.baseUrl;
		this.fetchRange();
	}

	async fetchRange() {
		const urlInfo = new URL('Info', this.baseUrl);
		const res = await axios.get(urlInfo.toString());
		// console.log(res.data);
		const resData = JSON.parse(res.data);
		if (resData.status !== 'ok') {
			throw new Error(resData.error);
		}
		console.log(urlInfo);
	}

	render() {
		return (
			<ImageStream/>
		);
	}

}
