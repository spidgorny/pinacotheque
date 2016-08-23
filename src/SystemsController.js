import * as React from "react";
import Counter from './Counter';
import SystemList from './SystemList';

class SystemsController extends React.Component {

	onStoreChange() {
		console.log('onStoreChange');
	}

	render() {
		console.log('SystemsController render()');
		return <div>
			<Counter />
			<SystemList ref="systemList"/>
		</div>;
	}

	componentDidMount() {
		console.log('SystemsController did mount');
	}

}

export default SystemsController;
