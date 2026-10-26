import jQuery from 'jquery';

// Bootstrap 3, select2 3 and the inline scripts in Blade views expect jQuery
// as a global rather than importing it
window.jQuery = window.$ = jQuery;
