import React from 'react';
import StorageWrap from 'storage-wrap';
// import Defiant from 'defiant';
// <script src="node_modules/defiant/dist/defiant.min.js"></script>

/**
 *
 */
class SystemView extends React.Component {

	constructor() {
		super();
		this.state = {
			id: null,
			name: null,
		};
	}

	componentWillMount() {
		var id = this.props.params.systemID;
		console.log('componentWillMount', id);
		var json = StorageWrap.getItem('system.json');
		var row = JSON.search(json, '//*[id=' + id + ']');
		if (row.length) {
			console.log(row[0]);
			this.setState(row[0]);
		}
	}

	render() {
		return (
			<section>
				<h2>{this.props.params.systemID}</h2>
				Name: {this.state.name}
			</section>);
	}
}

export default SystemView;
