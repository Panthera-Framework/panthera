<?php
/**
  * Set of parsers for Polish official numbers; supports identity card, pesel, iban, nrb etc.
  *
  * @package Panthera\modules
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

class polishNumbers
{
      /**
        * Get information (birth_date, sex) from PESEL number and check correctness
        *
        * @return array
        * @author Mateusz Warzyński
        */

      public static function PESEL($pesel)
      {
            $data = array();

            // check correctness (http://pl.wikipedia.org/wiki/PESEL)
            if (($pesel[0]+3*$pesel[1]+7*$pesel[2]+9*$pesel[3]+$pesel[4]+3*$pesel[5]+7*$pesel[6]+9*$pesel[7]+$pesel[8]+3*$pesel[9]+$pesel[10])%10 != 0)
                  return False;

            // get birth_date from 1-6 numbers
            if ($pesel[2] == 0 or $pesel[2] == 1)       // 1900 - 1999
            {
                  $year = '19' . $pesel[0] . $pesel[1];
                  $month = $pesel[2] . $pesel[3];
            } elseif ($pesel[2] == 2 or $pesel[2] == 3) // 2000 - 2099
            {
                  $year = '20' . $pesel[0] . $pesel[1];
                  $month = $pesel[2]-2 . $pesel[3];
            } elseif ($pesel[2] == 4 or $pesel[2] == 5) // 2100 - 2199 (for future reference)
            {
                  $year = '21' . $pesel[0] . $pesel[1];
                  $month = $pesel[2]-4 . $pesel[3];
            } elseif ($pesel[2] == 6 or $pesel[2] == 7) // 2200 - 2299 (for future reference)
            {
                  $year = '22' . $pesel[0] . $pesel[1];
                  $month = $pesel[2]-6 . $pesel[3];
            } elseif ($pesel[2] == 8 or $pesel[2] == 9) // 1800 - 1899 (really old people)
            {
                  $year = '18' . $pesel[0] . $pesel[1];
                  $month = $pesel[2]-8 . $pesel[3];
            }

            $data['birth_date'] = $pesel[4] . $pesel[5] . "." . $month . "." . $year;

            if ($pesel[9]%2)
                  $data['sex'] = 'male';
            else
                  $data['sex'] = 'female';

            return $data;
      }


      /**
        * Check correctness of REGON number
        *
        * @return bool
        * @author Mateusz Warzyński
        */

      public static function REGON($r)
      {
            if (strlen($r) == 9)
            {
                  if ((8*$r[0]+9*$r[1]+2*$r[3]+3*$r[4]+4*$r[5]+5*$r[6]+6*$r[6]+7*$r[7])%11 == $r[8])
                        return True;
            } elseif (strlen($r) == 14)
            {
                  if ((2*$r[0]+4*$r[1]+8*$r[2]+5*$r[3]+9*$r[5]+7*$r[6]+3*$r[7]+6*$r[8]+$r[9]+2*$r[10]+4*$r[11]+8*$r[12])%11 == $r[13])
                        return True;
            } else {
                  return False;
            }

      }

      /**
        * Check correctness of NIP number
        *
        * @return bool
        * @author Mateusz Warzyński
        */

      public static function NIP($n)
      {
            if (strlen($n) == 9)
            {
                  if ((6*$n[0]+5*$n[1]+7*$n[2]+2*$n[3]+3*$n[4]+4*$n[5]+5*$n[6]+6*$n[7]+7*$n[8])%11 != 10)
                        return True;
            }

            return False;
      }

      /**
        * Check correctness of branch code of bank (iban[4:12])
        *
        * @return bool
        * @author Mateusz Warzyński
        */

      public static function branchCodeBank($i)
      {
            if (strlen($i) != 8)
                  return False;

            if ((7*$i[0]+$i[1]+3*$i[2]+9*$i[3]+7*$i[4]+$i[5]+3*$i[6])%10 == $i[7])
                  return True;

            return False;
      }

      /**
        * Check correctness of IBAN code (only from PL)
        *
        * @return bool
        * @author Mateusz Warzyński
        */

      public static function IBAN($iban)
      {
            $iban = str_replace(' ', '', $iban);
            $iban = substr($iban, 4).substr($iban, 0, 4);
            $iban = str_replace('PL', '2521', $iban);

            if (!self::branchCodeBank(substr($iban, 0, 8)))
                  return False;

            $W = array(1,10,3,30,9,90,27,76,81,34,49,5,50,15,53,45,62,38,89,17,
                               73,51,25,56,75,71,31,19,93,57);

            $Z = 0;
            for ($i=0;$i<30;$i++)
                $Z += $iban[29-$i] * $W[$i];

            if ($Z % 97 == 1)
                return True;
            else
                return False;
      }

      /**
        * Check correctness of NRB code
        *
        * @return bool
        * @author Mateusz Warzyński
        */

      public static function NRB($i)
      {
            $i = "PL".$i;
            return self::IBAN($i);
      }

      /**
        * Check correctness of Polish Identity Card
        *
        * @return bool
        * @author Mateusz Warzyński
        */

      public static function identityCard($c)
      {
            $chars = array( '0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4',
                              '5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9',
                              'A'=>'10','B'=>'11','C'=>'12','D'=>'13','E'=>'14',
					'G'=>'16','H'=>'17','I'=>'18','J'=>'19','K'=>'20',
					'L'=>'21','M'=>'22','N'=>'23','O'=>'24','P'=>'25',
					'Q'=>'26','R'=>'27','S'=>'28','T'=>'29','U'=>'30',
					'V'=>'31','W'=>'32','X'=>'33','Y'=>'34','Z'=>'35','F'=>'15');
		$weights = array(7, 3, 1, 9, 7, 3, 1, 7, 3);

            $number = 0;
            for ($i=0;$i<10;$i++)
                  $number += $chars[substr($c, $i, 1)]*$weights[$i];

            if ($number%10 == 0)
                  return True;
            return False;
      }
}