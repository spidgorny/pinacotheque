/// <reference path="../typings/index.d.ts" />

console.log('Hello World! 3');

import React from 'react';
import ReactDOM from 'react-dom';
import SystemsController from './SystemsController';

document.addEventListener('DOMContentLoaded', function() {
	console.log('DOMContentLoaded');
	ReactDOM.render(
		<SystemsController />,
		document.getElementById('mount')
	);
});
