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

    Bytecode.php - generates Java bytcode class to print strings passed to constructor
    Troy Daniels - 21/01/17
*/

define("_STRING", "01");
define("_CLASSREF", "07");
define("_STRINGREF", "08");
define("_STRINGREF", "08");
define("_FIELD", "09");
define("_METHOD", "0a");
define("_NAMETYPE", "0c");

class Bytecode{
    private $tokenFilename = "file";
    private $tokenClassName = "a";
    // Magic number
    private $magicNumber = "cafebabe";
    // Minor version
    private $minVersion = "0000";
    // Major version - Java SE 8
    private $majVersion = "0034";
    
    function __construct($strings){
        // Constant pool Count (+1 by design) 
        $constPoolCount = self::paddedHex(27 + 2 * count($strings), 4);
        // 1 Method Reference java/lang/Object."<init>":()V
        $constPool[] = _METHOD;
        $constPool[] = self::paddedHex(5 + count($strings), 4);
        $constPool[] = self::paddedHex(14 + count($strings), 4);
        // 2 Field Reference java/lang/System.out:Ljava/io/PrintStream;
        $constPool[] = _FIELD;
        $constPool[] = self::paddedHex(15 + count($strings), 4);
        $constPool[] = self::paddedHex(16 + count($strings), 4);
        $n = 0;
        foreach ($strings as $string) {
            // 3  String Reference to $strings
            $constPool[] = _STRINGREF;
            $constPool[] = self::paddedHex(17 + count($strings) + $n++, 4);
        }

        // 3 + count($strings) Method java/io/PrintStream.print:(Ljava/lang/String;)V
        $constPool[] = _METHOD;
        $constPool[] = self::paddedHex(17 + 2 * count($strings), 4);
        $constPool[] = self::paddedHex(18 + 2 * count($strings), 4);
        // 4 + count($strings) Class Reference $tokenClassName
        $constPool[] = _CLASSREF;
        $constPool[] = self::paddedHex(19 + 2 * count($strings), 4);
        // 5 + count($strings) Class Reference java/lang/Object
        $constPool[] = _CLASSREF;
        $constPool[] = self::paddedHex(20 + 2 * count($strings), 4);
        // 6 + count($strings) String <init>
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("<init>"), 4);
        $constPool[] = self::strToHex("<init>");
        // 7 + count($strings) String ()V
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("()V"), 4);
        $constPool[] = self::strToHex("()V");
        // 8 + count($strings) String Code
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("Code"), 4);
        $constPool[] = self::strToHex("Code");
        // 9 + count($strings) String LineNumberTable
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("LineNumberTable"), 4);
        $constPool[] = self::strToHex("LineNumberTable");
        // 10 + count($strings) String main
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("main"), 4);
        $constPool[] = self::strToHex("main");
        // 11 + count($strings) String ([Ljava/lang/String;)V
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("([Ljava/lang/String;)V"), 4);
        $constPool[] = self::strToHex("([Ljava/lang/String;)V");
        // 12 + count($strings) String SourceFile
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("SourceFile"), 4);
        $constPool[] = self::strToHex("SourceFile");
        // 13 + count($strings) String $filename
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen($this->tokenFilename), 4);
        $constPool[] = self::strToHex($this->tokenFilename);
        // 14 + count($strings) Name & Type "<init>":()V  
        $constPool[] = _NAMETYPE;
        $constPool[] = self::paddedHex(6 + count($strings), 4);
        $constPool[] = self::paddedHex(7 + count($strings), 4);
        // 15 + count($strings) Class Reference java/lang/System
        $constPool[] = _CLASSREF;
        $constPool[] = self::paddedHex(21 + 2 * count($strings), 4);
        // 16 + count($strings) Name & Type out:Ljava/io/PrintStream;
        $constPool[] = _NAMETYPE;
        $constPool[] = self::paddedHex(22 + 2 * count($strings), 4);
        $constPool[] = self::paddedHex(23 + 2 * count($strings), 4);
        foreach ($strings as $string) {
            // String reference
            $constPool[] = _STRING;
            $constPool[] = self::paddedHex(strlen($string), 4);
            $constPool[] = self::strToHex($string);
        }
        // 18 + 2 * count($strings) Class java/io/PrintStream    
        $constPool[] = _CLASSREF;
        $constPool[] = self::paddedHex(24 + 2 * count($strings), 4);
        // 19 + 2 * count($strings) Name & Type
        $constPool[] = _NAMETYPE;
        $constPool[] = self::paddedHex(25 + 2 * count($strings), 4);
        $constPool[] = self::paddedHex(26 + 2 * count($strings), 4);
        // 20 + 2 * count($strings) String $tokenClassName
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen($this->tokenClassName), 4);
        $constPool[] = self::strToHex($this->tokenClassName);
        // 21 + 2 * count($strings) String java/lang/Object
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("java/lang/Object"), 4);
        $constPool[] = self::strToHex("java/lang/Object");
        // 22 + 2 * count($strings) String java/lang/System
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("java/lang/System"), 4);
        $constPool[] = self::strToHex("java/lang/System");
        // 23 + 2 * count($strings) String out
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("out"), 4);
        $constPool[] = self::strToHex("out");
        // 24 + 2 * count($strings) String Ljava/io/PrintStream;
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("Ljava/io/PrintStream;"), 4);
        $constPool[] = self::strToHex("Ljava/io/PrintStream;");
        // 25 + 2 * count($strings) String java/io/PrintStream
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("java/io/PrintStream"), 4);
        $constPool[] = self::strToHex("java/io/PrintStream");
        // 26 + 2 * count($string) String print
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("print"), 4);
        $constPool[] = self::strToHex("print");
        // 27 + 2 * count($string) String (Ljava/lang/String;)V
        $constPool[] = _STRING;
        $constPool[] = self::paddedHex(strlen("(Ljava/lang/String;)V"), 4);
        $constPool[] = self::strToHex("(Ljava/lang/String;)V");

        // Class access mask - Public + superclass
        $classInfo[] = self::paddedHex(33, 4);
        // Reference to class name $tokenClassName
        $classInfo[] = self::paddedHex(4 + count($strings), 4);
        // Reference to superclass java/lang/Object
        $classInfo[] = self::paddedHex(5 + count($strings), 4);
        // Number of interfaces
        $classInfo[] = self::paddedHex(0, 4);
        // Number of superinterfaces
        $classInfo[] = self::paddedHex(0, 4);
        // Method count
        $classInfo[] = self::paddedHex(2, 4);

        // Method 1 - Constructor
        // Access flags - public
        $constructorInfo[] = self::paddedHex(1, 4);
        // Name Index <Init>
        $constructorInfo[] = self::paddedHex(6 + count($strings), 4);
        // Descriptor index ()V
        $constructorInfo[] = self::paddedHex(7 + count($strings), 4);
        // Attributes count
        $constructorInfo[] = self::paddedHex(1, 4);
        // Attributes name index Code
        $constructorInfo[] = self::paddedHex(8 + count($strings), 4);
        // Attribute length
        $constructorInfo[] = self::paddedHex(17, 8);
        // Max stack
        $constructorInfo[] = self::paddedHex(1, 4);
        // Max locals
        $constructorInfo[] = self::paddedHex(1, 4);
        // Code length
        $constructorInfo[] = self::paddedHex(5, 8);
        // aload_0
        $constructorInfo[] = self::paddedHex(42, 2);
        // Invoke special
        $constructorInfo[] = self::paddedHex(183, 2);
        // Constant pool reference java/lang/Object."<init>":()V
        $constructorInfo[] = self::paddedHex(1, 4);;
        // Return
        $constructorInfo[] = self::paddedHex(177, 2);
        // Exception table length
        $constructorInfo[] = self::paddedHex(0, 4);
        // Attributes count
        $constructorInfo[] = self::paddedHex(0, 4);

        // Method 2 - main
        // Access flags
        $mainMethod[] = self::paddedHex(9, 4);
        // Attribute name index main
        $mainMethod[] = self::paddedHex(10 + count($strings), 4);
        // Descriptor index
        $mainMethod[] = self::paddedHex(11 + count($strings), 4);
        // Attribute count
        $mainMethod[] = self::paddedHex(1, 4);
        // Attribute name index Code
        $mainMethod[] = self::paddedHex(8 + count($strings), 4);
        // Attribute length
        $mainMethod[] = self::paddedHex(8 * count($strings) + 13, 8);
        // Max stack
        $mainMethod[] = self::paddedHex(2, 4);
        // Max locals
        $mainMethod[] = self::paddedHex(1, 4);
        // Code Length
        $mainMethod[] = self::paddedHex(8 * count($strings) + 1, 8);

        for($n = 0; $n < count($strings); $n++) {
            // Get static
            $mainMethod[] = self::paddedHex(178, 2);
            // Constant pool 2 java/lang/System.out:Ljava/io/PrintStream;
            $mainMethod[] = self::paddedHex(2, 4);
            // ldc
            $mainMethod[] = self::paddedHex(18, 2);
            // Reference to our strings
            $mainMethod[] = self::paddedHex(3 + $n, 2);
            // Invoke virtual
            $mainMethod[] = self::paddedHex(182, 2);
            // Constant pool java/io/PrintStream.print:(Ljava/lang/String;)V
            $mainMethod[] = self::paddedHex(3 + count($strings), 4);

        }
        // Return
        $mainMethod[] = self::paddedHex(177, 2);

        // Exception table length
        $mainMethod[] = self::paddedHex(0, 4);
        // Attribute count
        $mainMethod[] = self::paddedHex(0, 4);

        // Attributes count
        $sourceAttributes[] = self::paddedHex(1, 4);
        // Attribute name index SourceFile
        $sourceAttributes[] = self::paddedHex(12 + count($strings), 4);
        // Attribute length
        $sourceAttributes[] = self::paddedHex(2, 8);
        // Sourcefile index
        $sourceAttributes[] = self::paddedHex(13 + count($strings), 4);

        $output .= $this->magicNumber;
        $output .= $this->minVersion;
        $output .= $this->majVersion;
        $output .= $constPoolCount;

        foreach($constPool as $constant){
            $output .= $constant;
        }
        foreach($classInfo as $class){
            $output .= $class;
        }
        foreach ($constructorInfo as $constructor) {
            $output .= $constructor;
        }
        foreach ($mainMethod as $main) {
            $output .= $main;
        }
        foreach ($sourceAttributes as $source) {
            $output .= $source;
        }
        
        file_put_contents($this->tokenClassName . ".class", pack('H*', $output));

}
    // Returns hex representation of string as string
    function strToHex($string){
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return $hex;
    }

    // Returns correctly padded hex representation of int as string
    // whose length is a multiple of $mod
    function paddedHex($string, $mod){
        // Get a hex representation of int
        $tempString = dechex($string);
        while(strlen($tempString) % $mod !== 0){
            $tempString = "0" . $tempString;
        }
        return $tempString;
    }
}