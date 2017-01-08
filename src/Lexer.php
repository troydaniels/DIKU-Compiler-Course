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
    "/^([+-]?(([0-9]+(.[0-9]∗)?|.[0-9]+)([eE][+-]?[0-9]+)?))/" => "NUMBER",
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

  function run($fh){
    // Until we reach EOF
    while( ($line = fgets($fh)) !== false ) {
      $number++;
      // Reset character offset
      $offset = 0;
      // Attempt to match regex patterns from offest to end of line 
      while( $offset < strlen($line) ) {
        $substring = substr($line, $offset);
        foreach( static::$tokenRegex as $regex => $type ) {
          $match = false;
          if( preg_match($regex, $substring, $matches) ) {
            // We have at least one match to our regex
            $tokens[] = array(
              // $matches[1] holds first propper substring
              'string' => $matches[1],
              'token' => $type,
            );
            $offset += strlen($matches[1]);
            $match = true;
            break;
          }
        }
        // We didn't find a match - print error and exit
        if( !$match ) {
              print "Parse error near line " . $number . ", character " . ++$offset . "\n";
              exit(1);
        }
      }
    }
    return $tokens;   
  }
}