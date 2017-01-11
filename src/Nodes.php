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

    Nodes.php - Defines all AST node classes
    Troy Daniels - 08/01/17
*/

//  Binary operators
class BinaryOperation {
  public $symbol;
  public $left;   
  public $right;
  function __construct( $symbol_, $left_, $right_ ) {
  	$this->symbol = $symbol_;
  	$this->left = $left_;
  	$this->right = $right_;	
  }
}

// Unary operators
class UnaryOperation {
  public $symbol;
  public $right;
  function __construct( $symbol_, $right_ ) {
    $this->symbol = $symbol_;
    $this->right = $right_;
  }  
}

// Variable, string or integer
class Element {
  public $symbol;
  public $value;
  function __construct( $symbol_, $value_ ) {
  	$this->symbol = $symbol_;
	  $this->value = $value_;
  }
}

// Print function
class PrintFunction {
  public $symbol;
  public $right;
  function __construct ( $symbol_, $right_ ) {
  	$this->symbol = $symbol_;
  	$this->right = $right_;
  }
}

// While loop
class WhileLoop {
  public $symbol;
  public $right;
  function __construct ( $symbol_, $right_ ) {
  	$this->symbol = $symbol_;
  	$this->right = $right_;
  }	
}



