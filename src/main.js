/// <reference path="../typings/index.d.ts" />

console.log('Hello World! 3');

import React from 'react';
import ReactDOM from 'react-dom';
import Counter from './Counter';

document.addEventListener('DOMContentLoaded', function() {
	ReactDOM.render(
		<Counter />,
		document.getElementById('mount')
	);
});
