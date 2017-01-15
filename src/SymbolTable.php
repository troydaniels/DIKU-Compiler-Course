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

    SymbolTable.php - Defines a simple symbol table implementation
    Troy Daniels - 14/01/17
*/

class SymbolTable {
	private $symbolTable;

	// Add a new symbol/data pair to our symbol table
	function bind( $symbol, $data ) {
		$this->symbolTable[$symbol] = $data;
	}

	// Return data associated with $symbol, or print warning if not defined
	function lookup( $symbol ) {
		// Is this a number or a QUOTED_STRING?
		if( is_numeric($symbol) == false && $symbol[0] != '"'){
			$data = $this->symbolTable[$symbol];
			// Strictly equal, as assigning a variable to an empty string is valid
			if( $data === null ){ 
				print "Warning: symbol '" . $symbol . "' is not defined.\n";
				// We don't have to exit - we could though if we wanted to, here
			}
			return $data;
		}
		return $symbol;
	}
}