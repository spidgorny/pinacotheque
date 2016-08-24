/// <reference path="../typings/index.d.ts" />

//console.log('Hello World! 3');

import React from 'react';
import ReactDOM from 'react-dom';
import SystemsController from './SystemsController';
import SystemView from './SystemView';
import { Router, Route, hashHistory, browserHistory } from 'react-router'

document.addEventListener('DOMContentLoaded', function() {
	//console.log('DOMContentLoaded');
	ReactDOM.render((
			<Router history={hashHistory}>
				<Route path="/" component={SystemsController}/>
				<Route path="/system/:systemID/:systemName" component={SystemView}/>
			</Router>
		),
		document.getElementById('mount')
	);
});
