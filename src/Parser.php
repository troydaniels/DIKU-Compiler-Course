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
    Troy Daniels - 12/01/17
*/
require_once('Node.php');
require_once('Lexer.php');

class Parser {
  private $string;
  private $currentToken;
  private $line;
  private $lexer;
  private $fh;
  private $AST;

  function run($fh_){
  	$this->fh = $fh_;
  	$this->lexer = new Lexer();
  	$this->line = 0;

	while( $this->string !== false ) {
	  // Until we reach EOF, grab the next line from the file
	  self::getNextLine();
	  // And break off tokens
	  $this->currentToken = $this->lexer->next($this->string, $this->line);
	  // Curse of the whitespace
	  if(   $this->currentToken['symbol'] === "WHITESPACE" ){
  	  	$this->currentToken = $this->lexer->next($this->string, $this->line);
  	  }

	  switch ( $this->currentToken['symbol'] ) {
	  	case "VARIABLE":
        $this->AST[] = @self::assignment();
        break;
	    case "WHILE":
        $this->AST[] = @self::statement();
	      break;
	    case "PRINT":
	      $this->AST[] = @self::printFunction();
	    default:
	      break;
 	  }
	}
	return $this->AST;
  }

  function getNextLine(){
	$this->line++;
  	$this->string = fgets($this->fh);
  }

   function printError( $expectedSymbol ) {
    var_dump($this->currentToken);  
   	  print "Parse error near '". $this->currentToken['value'] . "' - Expected " . $expectedSymbol . "\n";
   	  exit(1);
   }

  // If we find a token we expect update $currentToken, else, throw an error
  function consume( $expectedSymbol ) {
  	if ( $this->currentToken['symbol'] === $expectedSymbol ) {
  	  $this->currentToken = $this->lexer->next($this->string, $this->line);
  	  // This could be a whitespace character
  	  if ( $this->currentToken['symbol'] === "WHITESPACE" ) {
  	  	$this->currentToken = $this->lexer->next($this->string, $this->line);
  	  }
  	} else {
  	  self::printError($expectedSymbol);
  	}  
  }

  // <assignment> => VARIABLE ASSIGN [ <exp_1> | QUOTED_STRING ] SEMICOLON
  function assignment() {
//  	$variable = new Element( $this->currentToken['symbol'], $this->currentToken['value'], null, null );
    $variable = $this->currentToken['value'];
  	self::consume("VARIABLE");
  	$assign = new BinaryOperation( $this->currentToken['symbol'], null, $variable, null );
  	self::consume("ASSIGN");
  	if( $this->currentToken['symbol'] === "QUOTED_STRING" ) {
//  	  $string = new Element( $this->currentToken['symbol'], $this->currentToken['value'], null, null );
      $string = $this->currentToken['value'];
  	  $assign->right = $string;
  	  $this->consume("QUOTED_STRING");
  	} else {
  	  $assign->right = self::exp_1();
  	}
    self::consume("SEMICOLON");
    return $assign;
  }

  // <exp_1> => <exp_2> ( [ LESS_THAN | GREATER_THAN | EQUALITY ] <exp_2> )*
  function exp_1() {
  	$exp_1 = self::exp_2();
  	while ( $this->currentToken['symbol'] === "LESS_THAN" ||
      $this->currentToken['symbol'] === "GREATER_THAN" ||
      $this->currentToken['symbol'] === "EQUALITY" ) {
  	  $exp_1 = new BinaryOperation( $this->currentToken['symbol'], null, $exp_1, null );
  	  self::consume($this->currentToken['symbol']);
  	  $exp_1->right = self::exp_2();
  	}
  	return $exp_1;
}

  // <exp_2> => <exp_3> ( [ PLUS | MINUS ] <exp_3> )*
  function exp_2() {
  	$exp_2 = self::exp_3();
  	while ( $this->currentToken['symbol'] === "PLUS" ||
      $this->currentToken['symbol'] === "MINUS" ) {
  	  $exp_2 = new BinaryOperation( $this->currentToken['symbol'], null, $exp_2, null );
  	  self::consume($this->currentToken['symbol']);
  	  $exp_2->right = self::exp_3();
  	}
  	return $exp_2;
  }

  // <exp_3> => <exp_4> ( [ MULTIPLY | DIVIDE ] <exp_4> )*
  function exp_3() {
  	$exp_3 = self::exp_4();
  	while ( $this->currentToken['symbol'] === "MULTIPLY" ||
      $this->currentToken['symbol'] === "DIVIDE" ) {
  	  $exp_3 = new BinaryOperation( $this->currentToken['symbol'], null, $exp_3, null );
  	  self::consume($this->currentToken['symbol']);
  	  $exp_3->right = self::exp_4();
  	}
  	return $exp_3;
  }

  // <exp_4> => VARIABLE | ( PLUS | MINUS )? INTEGER | L_BRA <exp_1> R_BRA
  function exp_4() {
    if ( $this->currentToken['symbol'] === "VARIABLE") {
      $exp_4 = $this->currentToken['value'];
      self::consume("VARIABLE");
    // The following will be unary +/-
    } else if ( $this->currentToken['symbol'] === "PLUS" ){
      // Small hack, but we know it's the correct token
      self::consume($this->currentToken['symbol']);
      $exp_4 = (int)$this->currentToken['value'];
      self::consume('INTEGER');
    } else if ( $this->currentToken['symbol'] === "MINUS" ){
      self::consume($this->currentToken['symbol']);
      $exp_4 = -1 * $this->currentToken['value'];
      self::consume('INTEGER');
    } else if ( $this->currentToken['symbol'] === "INTEGER" ) {
      $exp_4 = $this->currentToken['value'];
      self::consume('INTEGER');
    } else {
      self::consume('L_BRA');
      $exp_4 = self::exp_1();
      self::consume('R_BRA');
    }
    return $exp_4;
  }

  // <statement> => WHILE L_BRA ( <exp_1> ) R_BRA L_PAR ( <block> )* R_PAR
  function statement() {
  	$statement = new WhileLoop( $this->currentToken['symbol'], null, null, null );
  	self::consume('WHILE');
  	self::consume('L_BRA');
  	$statement->left = self::exp_1();
  	self::consume('R_BRA');
  	self::consume('L_PAR');
    $statement->right = self::block();
    $tempNode = &$statement->right;
  	while( $this->currentToken['symbol'] != "R_PAR" ){
      // We need to traverse to the bottom of the AST
      while( $tempNode->nextInstruction instanceof Node ) {
        $tempNode = &$tempNode->nextInstruction;
      }
      // And now we can store the next while-block instruction
  	  $tempNode->nextInstruction = self::block();
  	}
  	self::consume('R_PAR');

    return $statement;
  }

  // <block> => ( <print> )* | ( <assignment> ) *
  function block() {
    // In this case, as a WHILE loop can span several lines, our token could be null or WHITESPACE  
    while( $this->currentToken == null || $this->currentToken['symbol'] === "WHITESPACE" ) {
      if( $this->currentToken == null ) {
  	    self::getNextLine();
  	  }
  	  $this->currentToken = $this->lexer->next($this->string, $this->line);
  	}
  	if ( $this->currentToken['symbol'] === "PRINT" ){
  		$block = self::printFunction();
  	} else if ( $this->currentToken['symbol'] != "R_PAR" ) {
  	  // This should catch all our errors in the block
  		$block = self::assignment();
  	}
  	return $block;
  }

  //<print> => PRINT [ VARIABLE | INTEGER | QUOTED_STRING ] SEMICOLON
  function printFunction() {
  	$printFunction = new PrintFunction( $this->currentToken['symbol'], null, null, null );
  	self::consume('PRINT');
  	if ( $this->currentToken['symbol'] === "VARIABLE" ||
  		 $this->currentToken['symbol'] === "INTEGER" ||
  		 $this->currentToken['symbol'] === "QUOTED_STRING" ) {
  	  $printFunction->right = $this->currentToken['value'];
  	  self::consume($this->currentToken['symbol']);
  	}
  	self::consume('SEMICOLON');
  	return $printFunction;
  }
}
