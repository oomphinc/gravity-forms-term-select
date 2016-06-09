<?php
/*
Plugin Name: Gravity Forms Term Select
Description: Adds a field type that allows for selecting terms when creating posts.
Author: Stephen Beemsterboer @ Oomph, Inc.
Version: 0.9.1
Author URI: http://www.oomphinc.com/
License: MIT
	The MIT License (MIT)
	Copyright (c) 2016 Oomph, Inc
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

namespace OomphInc\GFormsTermSelect;

if ( !class_exists( __NAMESPACE__ . '\\Plugin' ) ) {
	require_once __DIR__ . '/inc/Plugin.php';
	define( __NAMESPACE__ . '\\PLUGINS_URL', plugins_url( '/', __FILE__ ) );
	Plugin::init();
}
