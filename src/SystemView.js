import React from 'react';
import StorageWrap from 'storage-wrap';
// import Defiant from 'defiant';
// <script src="node_modules/defiant/dist/defiant.min.js"></script>
import Formsy from 'formsy-react';
import FRC from 'formsy-react-components';
const { Checkbox, CheckboxGroup, Input, RadioGroup, Row, Select, File, Textarea } = FRC;

/**
 *
 */
class SystemView extends React.Component {

	constructor() {
		super();
		this.state = {
			canSubmit: false,
			id: null,
			name: null,
		};
	}

	componentWillMount() {
		var id = this.props.params.systemID;
		console.log('componentWillMount', id);

		// old way
		// var json = StorageWrap.getItem('system.json');
		// var rows = JSON.search(json, '//*[id=' + id + ']');
		// var row = rows && rows.length ? rows[0] : NULL;

		var systemDepot = depot('system', {
			idAttribute: 'id',
		});
		var row = systemDepot.get(id);
		console.log(row);
		// console.log(systemDepot.all());
		this.setState(row);
		this.enableButton = this.enableButton.bind(this);
		this.disableButton = this.disableButton.bind(this);
		this.onSubmit = this.onSubmit.bind(this);
	}

	enableButton() {
		this.setState({
			canSubmit: true
		});
	}

	disableButton() {
		this.setState({
			canSubmit: false
		});
	}

	onSubmit(data) {
		console.log(data);
		this.refs.btnSubmit.value = 'Saving...';
		fetch('../data/system.json', {
			method: 'POST',
			body: JSON.stringify(data)
		}).then(res => {
			console.log(res);

			// old way
			//var json = StorageWrap.getItem('system.json');

			var systemDepot = depot('system', {
				idAttribute: 'id',
			});
			systemDepot.update(this.state.id, data);

			this.context.router.push('/');
		});
	}

	render() {
		return (
			<section className="container">
				<h2>{this.props.params.systemID}</h2>
				Name: {this.state.name}
				<Formsy.Form onValidSubmit={this.onSubmit}
							 onValid={this.enableButton}
							 onInvalid={this.disableButton}>
					<fieldset>
						<legend>System</legend>
						<Input
							label="ID"
							name="id"
							value={this.state.id}
							type="number"
							readOnly
						/>
						<Input
							label="Name"
							name="name"
							value={this.state.name}
							required
						/>
						<Input
							label="Consoles"
							name="consoles"
							value={this.state.consoles}
						/>
						<Checkbox
							label="Active?"
							name="active"
							value={this.state.active}
						/>
						<Checkbox
							label="hidden?"
							name="hidden"
							value={this.state.hidden}
						/>
						<Checkbox
							label="Universal?"
							name="universal"
							value={this.state.universal}
						/>
						<Input
							label="Created"
							name="ctime"
							value={this.state.ctime}
							readOnly
						/>
					</fieldset>
					<button type="submit"
							ref="btnSubmit"
							disabled={!this.state.canSubmit}>Submit</button>
				</Formsy.Form>
			</section>);
	}
}

SystemView.contextTypes = {
	router: React.PropTypes.object
};

export default SystemView;
