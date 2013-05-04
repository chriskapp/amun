/**
 * testee
 *
 * This file is part of testee. A simple testing framework to run js tests 
 * within phantomjs.
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 */

var AssertException = function(message){
	this.message = message;
};

var Assert = {
	SUCCESS: 0x1,
	FAILURE: 0x2,
	NEXT: 0x3,

	addResult: function(result){
		window.callPhantom(result);
	},

	triggerNext: function(){
		this.addResult({
			code: this.NEXT
		});
	},

	addSuccess: function(){
		this.addResult({
			code: this.SUCCESS,
		});
	},

	addFailure: function(message){
		this.addResult({
			code: this.FAILURE,
			message: message
		});
	}
};

Assert.contains = function(expectedValue, array){
	var found = false;
	for (var i = 0; i < array.length; i++) {
		if (array[i] == expectedValue) {
			found = true;
			break;
		}
	}
	if (!found) {
		throw ('Array does not contain "' + expectedValue + '"');
	} else {
		this.addSuccess();
	}
};

Assert.count = function(expectedCount, array){
	if (array.length != expectedCount) {
		throw ('Expect count "' + expectedCount + '" actual "' + array.length + '"');
	} else {
		this.addSuccess();
	}
};

Assert.empty = function(actual){
	if (actual != '') {
		throw ('Expect "' + actual + '" to be empty');
	} else {
		this.addSuccess();
	}
};

Assert.notEmpty = function(actual){
	if (actual == '') {
		throw ('Expect "' + actual + '" to be not empty');
	} else {
		this.addSuccess();
	}
};

Assert.equals = function(expected, actual){
	if (expected != actual) {
		throw ('Expected "' + expected + '" is not equal "' + actual + '"');
	} else {
		this.addSuccess();
	}
};

Assert.equalsJsonStructure = function(expected, actual){
	// @todo
};

Assert.equalsXmlStructure = function(expected, actual){
	// @todo
};

Assert.exists = function(cssSelector){
	if (document.body.querySelector(cssSelector) === null) {
		throw ('Selector "' + cssSelector + '" does not match');
	} else {
		this.addSuccess();
	}
};

Assert.func = function(actual){
	if (typeof actual !== 'function') {
		throw ('Is not a function');
	} else {
		this.addSuccess();
	}
};

Assert.instanceOf = function(classType, actual){
	if (actual instanceof classType) {
		throw ('Object is not of type "' + classType + '"');
	} else {
		this.addSuccess();
	}
};

Assert.object = function(actual){
	if (typeof actual !== 'object') {
		throw ('Is not an object');
	} else {
		this.addSuccess();
	}
};

Assert.objectHasProperty = function(object, property){
	if (typeof object[property] === 'undefined') {
		throw ('Object has no property "' + property + '"');
	} else {
		this.addSuccess();
	}
};
