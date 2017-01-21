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

    Compiler.php - Compiles down to Java bytecode, saving a.class to current
    directory
    Troy Daniels - 14/01/17
*/

require_once('Parser.php');
require_once('SymbolTable.php');
require_once('Bytecode.php');

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
    // Stores return value (possibly null) of the previous interpret_* function call
    private $previousCalc;
    private $strings;

   function __construct( $fh_ ){
       $this->parser = new Parser();
       $this->fh = $fh_;
       $this->symbolTable = new SymbolTable();
   }

   function run(){
        $ASTarray = @$this->parser->run($this->fh);
        foreach( $ASTarray as $AST ){
            self::postorderTraversal($AST);
        }

        // Now we can generate our Java bytecode file
        new Bytecode($this->strings);
   }

    // Implements AST postorder traversal algorithm 
    function postorderTraversal( $node ) {
        $left = $node->left;
        $right = $node->right;
        $symbol = $node->symbol;
        $nextInstruction = $node->nextInstruction;

        // Recurse to bottom, and traverse child nodes left to right
        if( $left instanceof Node ) {
            self::postorderTraversal( $left );
        } else if ( $right instanceof Node ) {
            self::postorderTraversal( $right );
        }

        // Now, call relevant method for this node
        $this->previousCalc = $this->{"interpret_" . $symbol}( $node );
        
        // And then move onto any next instruction
        if ( $nextInstruction != null ) {
            self::postorderTraversal( $nextInstruction );
        } 
    }

    // The following defines methods to interpret a given node

    function interpret_MINUS( $node ) {
        $left = $this->symbolTable->lookup($node->left);
        $right = $this->symbolTable->lookup($node->right);
        return ($left - $right);
    }
    
    function interpret_PLUS( $node ) {
        $left = $this->symbolTable->lookup($node->left);
        $right = $this->symbolTable->lookup($node->right);
        return ($left + $right);
    }
    function interpret_MULTIPLY( $node ) {
        $left = $this->symbolTable->lookup($node->left);
        $right = $this->symbolTable->lookup($node->right);
        return ($left * $right);
    }

    function interpret_DIVIDE( $node ) {
        $left = $this->symbolTable->lookup($node->left);
        $right = $this->symbolTable->lookup($node->right);
        return ($left / $right);
    }

    function interpret_ASSIGN( $node ) {
        $left = $node->left;
        $right = $this->previousCalc;
        // If we're not assigning to a previous calculation
        if( is_null($right) ){
            $right = $this->symbolTable->lookup($node->right);
        }
        $this->symbolTable->bind( $left, $right );
    }
    
    function interpret_EQUALITY( $node ) {
        $left = $this->symbolTable->lookup($node->left);
        $right = $this->symbolTable->lookup($node->right);
        return ($left == $right);
    }

    function interpret_LESS_THAN( $node ) {
        $left = $this->symbolTable->lookup($node->left);
        $right = $this->symbolTable->lookup($node->right);
        return ($left < $right);       
    }

    function interpret_GREATER_THAN( $node ) {
        $left = $this->symbolTable->lookup($node->left);
        $right = $this->symbolTable->lookup($node->right);
        return ($left > $right);       
    }

    function interpret_WHILE( $node ) {
        // We need to recurse again
        self::postorderTraversal($node->left);
        while($this->previousCalc) {
            self::postorderTraversal( $node->right );
            self::postorderTraversal($node->left);
        }
    }

    function interpret_IF( $node ) {
        // We need to recurse again
        self::postorderTraversal($node->left);
        if($this->previousCalc) {
            self::postorderTraversal( $node->right );
            self::postorderTraversal($node->left);
        }
    }


    function interpret_PRINT( $node ) {
        // Regex to match valid escape chars
        $escapeCharRegex = array(
            "/\\\\a/" => "\a",
            "/\\\\b/" => "\b",
            "/\\\\f/" => "\f",
            "/\\\\n/" => "\n",
            "/\\\\r/" => "\r",
            "/\\\\t/" => "\t",
            "/\\\\v/" => "\v",
            );

        $right = $this->symbolTable->lookup($node->right);
        // If quoted string, we need to strip off '"' characters
        if( $right[0] === '"' ){
            $right = substr( $right, 1, -1 );

        }
        // Any escape characters arent interpreted properly
        // They're a multi character string - so lets replace them
        foreach($escapeCharRegex as $pattern => $replacement ){
            $right = preg_replace($pattern, $replacement, $right);
        }
        // Add to output strings, to be passed to Bytecode instance
        $this->strings[] = $right;
    }

}
