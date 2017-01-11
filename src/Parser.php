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

    Parser.php - implementation of simple recursive descent parser in PHP
    Troy Daniels - 08/01/17
*/
require_once('Nodes.php');
require_once('Lexer.php');

class Parser {
  private $string;
  private $currentToken;
  private $line;
  private $lexer;

  function run($fh){
  	$this->lexer = new Lexer();
  	$this->line = 0;
	// Until we reach EOF, grab the next line from the file
	while( ($this->string = fgets($fh)) !== false ) {
	  // And break off tokens
	  $this->currentToken = $this->lexer->next($this->string, $this->line);
	  // Curse of the whitespace
	  if(   $this->currentToken['symbol'] == "WHITESPACE" ){
  	  	$this->currentToken = $this->lexer->next($this->string, $this->line);
  	  }
	  while( $this->currentToken != false ) {
	  	return @self::assignment();
	  }
	  $this->line++;
	}
  }

   function printError($expectedSymbol) {
   	  print "Parse error near '". $this->currentToken['value'] . "' - Expected " . $expectedSymbol . "\n";
   }

  // If we find a token we expect update $currentToken, else, throw an error
  function consume($expectedSymbol) {
  	if ( $this->currentToken['symbol'] === $expectedSymbol ) {
  	  $this->currentToken = $this->lexer->next($this->string, $this->line);
  	  // This could be a whitespace character
  	  if(   $this->currentToken['symbol'] === "WHITESPACE" ){
  	  	$this->currentToken = $this->lexer->next($this->string, $this->line);
  	  }
  	} else {
  		self::printError($expectedSymbol);
  		var_dump($this->currentToken);
  		exit (1);
  	}
  }

  // <assignment> => VARIABLE ASSIGN [ <exp_1> | QUOTED_STRING ] SEMICOLON
  function assignment() {
  	$variable = new Element( $this->currentToken['symbol'], $this->currentToken['value'] );
  	$this->consume("VARIABLE");
  	$assign = new BinaryOperation( $this->currentToken['symbol'], $variable, null );
  	$this->consume("ASSIGN");
  	if( $this->currentToken['symbol'] === "QUOTED_STRING" ) {
  		$string = new Element( $this->currentToken['symbol'], $this->currentToken['value'] );
  		$assign->right = $string;
  		$this->consume("QUOTED_STRING");
  	} else {
  		$assign->right = self::exp_1();
  	}
    $this->consume("SEMICOLON");
    return $assign;
  }

  // <exp_1> => <exp_2> ( [ LESS_THAN | GREATER_THAN | EQUALITY ] <exp_2> )*
  function exp_1() {
  	$exp_1 = self::exp_2();
  	while ( $this->currentToken['symbol'] === "LESS_THAN" ||
    	    $this->currentToken['symbol'] === "GREATER_THAN" ||
    	    $this->currentToken['symbol'] === "EQUALITY" ) {
  	    	$exp_1 = new BinaryOperation( $this->currentToken['symbol'], $exp_1, null );
  			$this->consume($this->currentToken['symbol']);
  			$exp_1->right = self::exp_2();
  	}
  	return $exp_1;
}

  // <exp_2> => <exp_3> ( [ PLUS | MINUS ] <exp_3> )*
  function exp_2() {
  	$exp_2 = self::exp_3();
  	while ( $this->currentToken['symbol'] === "PLUS" ||
    	    $this->currentToken['symbol'] === "MINUS" ) {
  	    	$exp_2 = new BinaryOperation( $this->currentToken['symbol'], $exp_2, null );
  			$this->consume($this->currentToken['symbol']);
  			$exp_2->right = self::exp_3();
  	}
  	return $exp_2;
  }

  // <exp_3> => <exp_4> ( [ MULTIPLY | DIVIDE ] <exp_4> )*
  function exp_3() {
  	$exp_3 = self::exp_4();
  	while ( $this->currentToken['symbol'] === "MULTIPLY" ||
    	    $this->currentToken['symbol'] === "DIVIDE" ) {
  	    	$exp_3 = new BinaryOperation( $this->currentToken['symbol'], $exp_3, null );
  			$this->consume($this->currentToken['symbol']);
  			$exp_3->right = self::exp_4();
  	}
  	return $exp_3;
  }

  // <exp_4> => VARIABLE | ( PLUS | MINUS )? INTEGER | L_BRA <exp_1> R_BRA
  function exp_4() {
    if ( $this->currentToken['symbol'] === "VARIABLE") {
      $exp_4 = new Element( $this->currentToken['symbol'], $this->currentToken['value'] );
      $this->consume("VARIABLE");
    // The following will be unary +/-
    } else if ( $this->currentToken['symbol'] === "PLUS" ||
    	        $this->currentToken['symbol'] === "MINUS" ) {
    	$exp_4 = new UnaryOperation( $this->currentToken['symbol'], null);
        // Small hack, but we know it's the correct token
    	self::consume($this->currentToken['symbol']);
    	$exp_4->right = $this->currentToken['value'];
    	self::consume('INTEGER');
    } else if ( $this->currentToken['symbol'] === "INTEGER" ) {
    	$exp_4->right = $this->currentToken['value'];
    	self::consume('INTEGER');
    } else {
//        var_dump($this->currentToken);
    	self::consume('L_BRA');
    	$exp_4 = self::exp_1();
    	self::consume('R_BRA');
    }
    return $exp_4;
  }
}
