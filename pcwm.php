<?php 

/*
Plugin Name: Per Category Widgets
Plugin URI: http://cfo.com/
Description: This plugin allows users to attach sidebars to a category in a way that respects their hierarchy.
Version: 0.0.1
Author: Aram Zucker-Scharff
Author URI: http://aramzs.me
License: GPL2
*/

/*  Developed for SES

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class PCW {

}

/**
 * Bootstrap
 *
 * You can also use this to get a value out of the global, eg
 *
 *    $foo = PCW()->bar;
 *
 * @since 1.7
 */
function pcw() {
	global $pcw;
	if ( ! is_a( $pcw, 'PCW' ) ) {
		$pcw = new PCW();
	}
	return $pcw;
}

// Start me up!
pcw();