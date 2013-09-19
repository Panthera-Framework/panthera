<?php
/**
  * Google Page Rank checking class
  *
  * @package Panthera\modules\googlepr
  * @license GNU Affero General Public License 3, see license.txt
  * @see http://www.tutorialconnect.com/google-pagerank-checker-php-script/
  * @author Damian Kęska
  * @author tutorialconnect.com
  */

/**
  * Google Page Rank checking class
  *
  * @package Panthera\modules\googlepr
  * @see http://www.tutorialconnect.com/google-pagerank-checker-php-script/
  * @author Damian Kęska
  * @author tutorialconnect.com
  */

class GooglePR
{
	// Convert string to a number
	public static function stringToNumber($string, $check, $magic)
	{
		$int32 = 4294967296;  // 2^32
		$length = strlen($string);
		for ($i = 0; $i < $length; $i++) {
			$check *= $magic;
			// If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31),
			// the result of converting to integer is undefined
			// refer to http://www.php.net/manual/en/language.types.integer.php
			if($check >= $int32) {
				$check = ($check - $int32 * (int) ($check / $int32));
				// if the check less than -2^31
				$check = ($check < -($int32 / 2)) ? ($check + $int32) : $check;
			}
			$check += ord($string{$i});
		}
		return $check;
	}

	// Create a url hash
	function createHash($string)
	{
		$check1 = self::stringToNumber($string, 0x1505, 0x21);
		$check2 = self::stringToNumber($string, 0, 0x1003F);

		$factor = 4;
		$halfFactor = $factor/2;

		$check1 >>= $halfFactor;
		$check1 = (($check1 >> $factor) & 0x3FFFFC0 ) | ($check1 & 0x3F);
		$check1 = (($check1 >> $factor) & 0x3FFC00 ) | ($check1 & 0x3FF);
		$check1 = (($check1 >> $factor) & 0x3C000 ) | ($check1 & 0x3FFF);

		$calc1 = (((($check1 & 0x3C0) << $factor) | ($check1 & 0x3C)) << $halfFactor ) | ($check2 & 0xF0F );
		$calc2 = (((($check1 & 0xFFFFC000) << $factor) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000);

		return ($calc1 | $calc2);
	}

	// Create checksum for hash
	public static function checkHash($hashNumber)
	{
		$check = 0;
		$flag = 0;

		$hashString = sprintf('%u', $hashNumber);
		$length = strlen($hashString);

		for ($i = $length - 1;  $i >= 0;  $i --) {
			$r = $hashString{$i};
			if(1 === ($flag % 2)) {
				$r += $r;
				$r = (int)($r / 10) + ($r % 10);
			}
			$check += $r;
			$flag ++;
		}

		$check %= 10;
		if(0 !== $check) {
			$check = 10 - $check;
			if(1 === ($flag % 2) ) {
				if(1 === ($check % 2)) {
					$check += 9;
				}
				$check >>= 1;
			}
		}
		return '7'.$check.$hashString;
	}

	public static function getRank($page)
	{
		// Open a socket to the toolbarqueries address, used by Google Toolbar
		$socket = fsockopen("toolbarqueries.google.com", 80, $errno, $errstr, 10);

		// If a connection can be established
		if($socket) {
			// Prep socket headers
			$out = "GET /tbr?client=navclient-auto&ch=".self::checkHash(self::createHash($page)).
              "&features=Rank&q=info:".$page."&num=100&filter=0 HTTP/1.1\r\n";
			$out .= "Host: toolbarqueries.google.com\r\n";
			$out .= "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008072403 Mandriva/3.0.1-1mdv2008.1 (2008.1) Firefox/3.0.1\r\n";
			$out .= "Connection: Close\r\n\r\n";

			// Write settings to the socket
			fwrite($socket, $out);

			// When a response is received...
			$result = "";
			while(!feof($socket)) {
				$data = fgets($socket, 128);
				$pos = strpos($data, "Rank_");
				if($pos !== false){
					$pagerank = substr($data, $pos + 9);
					$result += $pagerank;
				}
			}
			// Close the connection
			fclose($socket);
			
			// Return the rank!
			return intval($result);
		}
		
		return -1;
	}
}
