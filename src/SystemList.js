import React from 'react';
import SystemListItem from './SystemListItem';
import StorageWrap from 'storage-wrap';
//import depot from 'depot';

/**
 *
 */
class SystemList extends React.Component {

	constructor() {
		super();
		this.state = {
			systems: [
				{
					id: 0,
					name: 'test',
				}
			],
		};
	}

	render() {
		console.log(this.state.systems.length);
		var systemListItems = this.state.systems.map(row => {
			return <SystemListItem key={row.id} data={row} />
		});
		return (
			<table className="table">
				<thead>
					<tr>
						<td>ID</td>
						<td>Name</td>
						<td>Consoles</td>
						<td>Active? (ORS)</td>
						<td>Hidden? (DCI)</td>
						<td>Universal? (SD Card?)</td>
						<td>Created</td>
					</tr>
				</thead>
				<tbody>
					{systemListItems}
				</tbody>
			</table>
		);
	}

	componentDidMount() {
		console.log('SystemList did mount');

		// TODO: make it a nice cache component
		if (false) {
			var systems = StorageWrap.getItem('system.json');

			// save into depot if already cached
			if (systems) {
				var systemDepot = depot('system');
				systems.map(item => {
					systemDepot.save(item);
				});
			}
		}

		var systemDepot = depot('system', {
			idAttribute: 'id',
		});
		var systems = systemDepot.all();
		console.log('systems.length', systems.length);
		// systems.filter((value, index, self) => {
		// 	return self.indexOf(value) === index;
		// });

		// let _ = require('underscore');
		// systems = _.uniq(systems);
		// console.log(systems.length);

		if (!systems.length) {
			fetch('../data/system.json')
				.then(response => {
					console.log(response);
					return response.json();
				})
				.then(systems => {
					console.log(systems.length);
					this.setState({
						systems: systems
					});
					// old way
					//StorageWrap.setItem('system.json', systems);

					systems.map(item => {
						systemDepot.save(item);
					});
				});
		} else {
			console.log(systems.length);
			this.setState({
				systems: systems
			});
		}
	}

}

export default SystemList;
