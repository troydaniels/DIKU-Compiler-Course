<?
/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    implementation.php - Simple working example of lexer.php
    Troy Daniels - 08/01/17
*/

require_once('lexer.php');

if( $argc != 2 ) {
   print "Usage: php " . $argv[0] . " filename\n";
   exit (1);
} else {
   $filename = $argv[1]; 
}

if ( file_exists($filename) ) {
   $fh = fopen($filename, "r") or die("Unable to open file");

   $lexer = new Lexer();
   var_dump($lexer::run($fh));
   fclose($fh);
} else {
   print "File '" . $filename . "' does not exist\n";
   exit (1);
}


