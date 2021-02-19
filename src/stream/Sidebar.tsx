import React, { FormEvent } from "react";
import { AppContext, context } from "../context";
import { Source } from "../App";

interface ISidebarProps {
	sources: Source[];
	sourceID?: number;
	setSource: (id?: number) => void;
}

interface ISidebarState {
	minWidth?: number;
}

export class Sidebar extends React.Component<ISidebarProps, ISidebarState> {
	static contextType = context;
	// @ts-ignore
	context: AppContext;

	state: ISidebarState = {};

	componentDidMount() {
		this.postConstruct();
	}

	postConstruct() {
		if (this.context) {
			let minWidth = this.context.sidebar?.minWidth;
			console.log("setState(minWidth)", minWidth);
			// this.setState({
			// 	minWidth: minWidth,
			// });
			// eslint-disable-next-line
			this.state.minWidth = minWidth;
		}
	}

	render() {
		return (
			<>
				<ul className="pb-2">
					{this.getSourceLi({ id: undefined, name: "All" })}
					{this.props.sources.map((source: Source) => this.getSourceLi(source))}
				</ul>
				<form onSubmit={this.onSubmit.bind(this)}>
					<label htmlFor="minWidth">Min Width</label>
					<input
						className="u-full-width"
						type="number"
						placeholder="minWidth"
						id="minWidth"
						name="minWidth"
						defaultValue={this.state.minWidth}
						onChange={(e) =>
							this.setState({ minWidth: parseInt(e.target.value) })
						}
					/>
					<input className="button-primary" type="submit" value="Submit" />
				</form>
			</>
		);
	}

	private getSourceLi(source: { id?: number; name: string }) {
		return (
			<li key={source.id}>
				<button
					onClick={(e: React.MouseEvent) => {
						e.preventDefault();
						return this.props.setSource(source.id);
					}}
					className={this.props.sourceID === source.id ? "active" : ""}
					style={{
						fontWeight: this.props.sourceID === source.id ? "bold" : undefined,
					}}
				>
					{source.name}
				</button>
			</li>
		);
	}

	onSubmit(e: FormEvent) {
		e.preventDefault();
		this.context.setState({
			sidebar: {
				minWidth: this.state.minWidth,
			},
		});
	}
}
