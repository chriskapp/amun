/**
 * testee
 *
 * Simple testing framework to run js tests within phantomjs. It uses a simple
 * format to declare a test case:
 * <code>
 * testCase('http://127.0.0.1/foo.htm', {
 *
 * 	testBar: function(){
 *  	Assert.equals('foo', document.getElementById('identity').value;
 *
 * 		document.getElementById('identity').value = 'test@test.com';
 * 		document.getElementById('pw').value = 'test123';
 * 		document.getElementsByTagName('form')[0].submit();
 *  },
 *
 * 	testFoo: function(){
 * 		Assert.exists(".login-success");
 * 		Assert.triggerNext();
 * 	}
 *
 * });
 * </code>
 *
 * For each test case a webpage is created for the given url. Then each function
 * is evaluated. The scope of the function is the js enviroment of the website
 * so you can access i.e. window or dom element. The assert.js is injected into
 * every webpage wich offers assertion methods and handels the reporting of the
 * results. The next test method is trigger either through an page load i.e.
 * an form submit or an call to the method Assert.triggerNext()
 *
 * If you declare for every url an test case you can be sure that there are at 
 * least no js errors since the test fails if the js on the page is not valid.
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 */

var fs = require('fs');
var system = require('system');
var webpage = require('webpage');

// settings
var baseDir = '.';
if (system.args.length >= 2) {
	baseDir = system.args[1];
}

var baseUrl = '';
if (system.args.length >= 3) {
	baseUrl = system.args[2];
}

// message constants
var SUCCESS = 0x1;
var FAILURE = 0x2;
var NEXT = 0x3;
var SKIP = 0x4;

var cases = [];
var result = [];
var out;
var i = 0;
var j = 0;
var currentTestCase;
var currentTest;
var loading = false;
var goNext = true;
var inTest = false;
var debug = false;
var interval;

/**
 * Class wich is responsible for writing to stdout. If we are on an windows 
 * machine we have to write to an buffer and then output the result through 
 * console.log since we cant to write to /dev/stdout
 */
var Writer = function(){
	var out = '';
	var isWindows = system.os.name == 'windows';

	this.print = function(str){
		if (isWindows) {
			out+= str;
		} else {
			fs.write('/dev/stdout', str, 'w');
		}
	}

	this.flush = function(){
		if (isWindows) {
			console.log(out);
		} else {
		}		
	}
}

/**
 * Scans and includes all js files in an given folder. The file should add an
 * test case through test testCase() method
 *
 * @param string path
 */
function scanTestDir(path){
	var count = 0;
	var files = fs.list(path);
	// search for _ini.js wich will gets executed first
	for (var i = 0; i < files.length; i++) {
		if (files[i] == '_ini.js') {
			var item = path + '/' + files[i];
			if (fs.isFile(item)) {
				if (debug) {
					console.log('[INJECT] ' + item);
				}
				phantom.injectJs(item);
				count++;
			}
		}
	}
	// add all files
	for (var i = 0; i < files.length; i++) {
		if (files[i].charAt(0) != '.' && files[i] != '_ini.js') {
			var item = path + '/' + files[i];
			if (fs.isDirectory(item)) {
				count+= scanTestDir(item);
			}
			if (fs.isFile(item) && files[i].indexOf('.js') != -1) {
				if (debug) {
					console.log('[INJECT] ' + item);
				}
				phantom.injectJs(item);
				count++;
			}
		}
	}
	return count;
}

/**
 * Adds an result set from an test case
 *
 * @param array<result>
 */
function addResultSet(resultSet){
	for (var i = 0; i < resultSet.length; i++) {
		addResult(resultSet[i]);
	}
}

/**
 * Adds an result object to the list
 *
 * @param object
 */
function addResult(res){
	result.push(res);
}

function runAllTests(){
	// run all tests
	nextTestCase();
}

/**
 * Runs the next test case in the queue
 */
function nextTestCase(){
	// run first test suite
	if (i < cases.length) {
		// set current test case
		currentTestCase = cases[i];

		// increase
		i++;

		if (debug) {
			console.log('[RUN_TEST] ' + currentTestCase.url);
		}

		// execute next test
		runNextTest();
	} else {
		// print result
		completeTest();
	}
}

/**
 * Output the test result
 */
function completeTest(){
	// output result
	var successCount = 0;
	var failureCount = 0;
	var out = new Writer();

	out.print("\n");
	out.print('TesTee 0.0.1 by Christoph Kappestein' + "\n");
	out.print("\n");

	for (var i = 0; i < result.length; i++) {
		if (result[i].code == SUCCESS) {
			successCount++;
			out.print('.');
		} else if(result[i].code == FAILURE) {
			failureCount++;
			out.print('F');
		} else if(result[i].code == SKIP) {
			out.print('S');
		}
		if (i > 0 && i % 40 == 0) {
			out.print("\n");
		}
	}

	out.print("\n");
	out.print("\n");

	for (var i = 0; i < result.length; i++) {
		if (result[i].code == FAILURE) {
			out.print((i + 1) + ') ' + (result[i].message ? result[i].message : '-') + "\n");
			out.print(result[i].trace ? result[i].trace : '-');
			out.print("\n");
			out.print("\n");
		}
	}

	if (failureCount > 0) {
		out.print('FAILURES!' + "\n");
	}

	out.print('Tests: ' + result.length + ', Failures: ' + failureCount);
	out.flush();

	if (failureCount > 0) {
		phantom.exit(1);
	} else {
		phantom.exit(0);
	}
}

function triggerNextTestMethod()
{
	j++;
	goNext = true;

	// check whether we have the last test case
	var k = 0;
	for (var method in currentTestCase.testCase) {
		k++;
	}
	if (k == j) {
		clearInterval(interval);
		nextTestCase();
	}
}

function runNextTest(){
	// create page
	var page = webpage.create();
	page.onError = function(msg, trace){
		if (debug) {
			console.log('[ERROR] ' + msg);
		}
		var traceAsString = '';
		for (var i = 0; i < trace.length; i++) {
			traceAsString+= trace[i].file + ':' + trace[i].line + ' ' + trace[i].function + "\n";
		}
		var message = msg;
		if(typeof currentTest === 'object') {
			message = currentTest.url + ': ' + currentTest.method + "\n" + msg;
		}
		addResult({
			code: FAILURE,
			message: message,
			trace: traceAsString
		});
		if (inTest) {
			inTest = false;
			triggerNextTestMethod();
		}
	};
	page.onCallback = function(result){
		if (debug) {
			console.log('[CALLBACK] ' + JSON.stringify(result));
		}
		if (result.code && result.code == NEXT) {
			triggerNextTestMethod();
		} else {
			addResult(result);
		}
	};
	page.onLoadStarted = function(){
		if (debug) {
			console.log('[LOAD_STARTED]');
		}
		loading = true;
	};
	page.onLoadFinished = function(){
		if (debug) {
			console.log('[LOAD_FINISHED]');
		}
		loading = false;
		if (inTest) {
			inTest = false;
			triggerNextTestMethod();
		}
	};
	page.onInitialized = function(){
		if (debug) {
			console.log('[INITIALIZED]');
		}
		page.injectJs('./assert.js');
	};
	page.open(encodeURI(baseUrl + currentTestCase.url), function(status){
		if (debug) {
			console.log('[OPEN] ' + status);
		}
		if (status !== 'success') {
			addResult({
				code: SKIP,
				message: 'Unable to access network'
			});
			nextTestCase();
		} else {
			j = 0;
			interval = setInterval(function(){
				if (!loading) {
					var k = 0;
					for (var method in currentTestCase.testCase) {
						if (typeof currentTestCase.testCase[method] === 'function'){
							if (k == j && goNext) {
								currentTest = {
									url: baseUrl + currentTestCase.url,
									method: method
								};
								if (debug) {
									console.log('[RUN_TEST_METHOD] ' + method);
								}
								inTest = true;
								goNext = false;
								page.evaluate(currentTestCase.testCase[method], baseUrl);
							}
							k++;
						}
					}
				}
			}, 100);
		}
	});
}

/**
 * Function to add a test case to the list
 *
 * @param string url
 * @param function testCase
 */
function testCase(url, testCase){
	cases.push({
		url: url,
		testCase: testCase
	});
}

scanTestDir(baseDir);
runAllTests();
