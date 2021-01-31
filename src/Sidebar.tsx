import React, {FormEvent} from "react";
import {AppContext, context} from "./context";

interface ISidebarProps {
}

interface ISidebarState {
	minWidth?: number;
}

export class Sidebar extends React.Component<ISidebarProps, ISidebarState> {

	static contextType = context;
	// @ts-ignore
	context: AppContext;

	state: ISidebarState = {};

	constructor(props: any, context: any) {
		super(props, context);
		setTimeout(this.postConstruct.bind(this), 1);
	}

	postConstruct() {
		if (this.context) {
			let minWidth = this.context.sidebar?.minWidth;
			console.log('setState(minWidth)', minWidth);
			this.setState({
				minWidth: minWidth,
			});
		}
	}

	render() {
		return (
			<form onSubmit={this.onSubmit.bind(this)}>
				<label htmlFor="minWidth">Min Width</label>
				<input className="u-full-width" type="number" placeholder="minWidth"
					   id="minWidth" name="minWidth" defaultValue={this.state.minWidth}
					   onChange={(e) => this.setState({minWidth: parseInt(e.target.value)})}/>
				<input className="button-primary" type="submit" value="Submit"/>
			</form>
		);
	}

	onSubmit(e: FormEvent) {
		e.preventDefault();
		this.context.setState({
			sidebar: {
				minWidth: this.state.minWidth,
			}
		})
	}

}
