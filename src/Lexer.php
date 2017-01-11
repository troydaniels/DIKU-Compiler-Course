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

    Lexer.php - implementation of a simple lexer in PHP
    Troy Daniels - 08/01/17
*/

class Lexer {
  // Order is important for tokens which appear at beginning
  // of other tokens, ie '=' and '=='
  // Longer string should appear first   
  static $tokenRegex = array(
    "/^(while)/" => "WHILE",
    "/^(print)/" => "PRINT",
    "/^(\s+)/" => "WHITESPACE",
    "/^([a-zA-Z]+)/" => "VARIABLE",
    "/^([0-9]+)/" => "INTEGER",
    "/^(\*)/" => "MULTIPLY",
    "/^(\/)/" => "DIVIDE",
    "/^(\+)/" => "PLUS",
    "/^(\-)/" => "MINUS",
    "/^(\=\=)/" => "EQUALITY",
    "/^(\=)/" => "ASSIGN",
    "/^(\{)/" => "L_PAR",
    "/^(\})/" => "R_PAR",
    "/^(\()/" => "L_BRA",
    "/^(\))/" => "R_BRA",
    "/^(\;)/" => "SEMICOLON",
    "/^(\<)/" => "LESS_THAN",
    "/^(\>)/" => "GREATER_THAN",
    '/^(\"[^\"]*\")/' => "QUOTED_STRING",
  );

  // Keep track of the current line and character position
  private $line = 0;
  private $offset = 0;

  function next($string, $line_){
    //If we've never seen this line number before, reset our offset
    if( $this->line != $line_ ){
      $this->line = $line_;
      $this->offset = 0;
    }  
    // Attempt to match regex patterns from offest to end of line 
    if( $this->offset < strlen($string) ) {
      $substring = substr($string, $this->offset);
      foreach( static::$tokenRegex as $regex => $type ) {
        $match = false;
        if( preg_match($regex, $substring, $matches) ) {
          // We have at least one match to our regex
          $token = array(
            // $matches[1] holds first propper substring
            'value' => $matches[1],
            'symbol' => $type,
          );
          $this->offset += strlen($matches[1]);
          $match = true;
          break;
        }
      }
      // We didn't find a match - print error and exit
      if( !$match ) {
        print "Lexical error near line " . ++$this->line . ", character " . ++$this->offset . "\n";
        exit(1);
      }
    }
    return $token;   
  }
}