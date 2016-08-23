import React from 'react';

/**
 *
 */
class SystemListItem extends React.Component {

	constructor() {
		super();
	}

	render() {
		return (
			<tr>
				<td>{this.props.data.id}</td>
				<td>{this.props.data.name}</td>
				<td>{this.props.data.consoles}</td>
				<td>{this.props.data.active ? 'Yes' : 'No'}</td>
				<td>{this.props.data.hidden ? 'Yes' : 'No'}</td>
				<td>{this.props.data.universal ? 'Yes' : 'No'}</td>
				<td>{this.props.data.ctime}</td>
			</tr>);
	}
}

export default SystemListItem;
