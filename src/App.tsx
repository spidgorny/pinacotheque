import React from "react";
import ImageStream from "./ImageStream";
import {AppContext, context} from "./context";
import axios from 'redaxios';

import ScaleLoader from "react-spinners/ScaleLoader";
import {Sidebar} from "./Sidebar";

interface IAppProps {
}

export interface Source {
	id: number;
	name: string;
	path: string;
	thumbRoot: string;
	_missingProperties: [];
}

interface IAppState {
	status: 'ok';
	min: string;
	max: string;
	sources: any[];
	query: string;
	duration: number;
}

export default class App extends React.Component<IAppProps, IAppState> {

	static contextType = context;
	context: AppContext;
	baseUrl;

	state = null;

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
		// console.log(resData);
		this.setState(resData);
	}

	render() {
		return (
			<div className="container" style={{width: '100%', maxWidth: '100%'}}>
				<div className="row">
					<div className="two columns">
						<Sidebar/>
					</div>
					<div className="ten columns">
						{this.state === null ? <ScaleLoader loading={true} color='#4DAF7C'/> :
							<ImageStream/>
						}
					</div>
				</div>
			</div>
		);
	}

}
