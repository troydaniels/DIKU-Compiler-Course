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

    Interpereter.php - implementation of simple interpreter in PHP
    Troy Daniels - 14/01/17
*/

require_once('Parser.php');
require_once('SymbolTable.php');

if( $argc != 2 ) {
   print "Usage: php " . $argv[0] . " filename\n";
   exit (1);
} else {
   $filename = $argv[1]; 
}

if ( file_exists($filename) ) {
  $fh = fopen($filename, "r") or die("Unable to open file");
  $interpreter = new Interpreter($fh);
  $interpreter->run();
  fclose($fh);
} else {
   print "File '" . $filename . "' does not exist\n";
   exit (1);
}

/***************************************************************************/

class Interpreter{
    private $parser;
    private $fh;
    private $symbolTable;

   function __construct( $fh_ ){
       $this->parser = new Parser();
       $this->fh = $fh_;
       $this->symbolTable = new SymbolTable();
   }

   function run(){
       $array = $this->parser->run($this->fh);
       self::postorderTraversal($array[0]);    
   }

    // Implements AST postorder traversal algorithm 
    function postorderTraversal( $node ) {
        $left = $node->left;
        $right = $node->right;
        $symbol = $node->symbol;

        // Recurse to bottom, and traverse child nodes left to right
        if( $left instanceof Node ) {
            self::postorderTraversal( $left );
        } else if ( $right instanceof Node ) {
            self::postorderTraversal( $right );
        } 

        // Now, call relevant method for this node
        $this->{"interpret_" . $symbol}();
    }

    // The following defines functions to interpret a given node
    function interpret_MINUS(){}
    function interpret_ASSIGN(){}
}
