import React from 'react';
import SystemListItem from './SystemListItem';

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
			return <SystemListItem data={row} />
		});
		return (
			<table>
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
			});
	}

}

export default SystemList;
