<?php
namespace Lugh\WebAppBundle\Lib\External;

class NifCifNiePredictor
{
        static public function predict($value)
        {
           $cif=$value;
           $ok=0;
           $cif = strtoupper($cif);
           for ($i = 0; $i < 9; $i ++)
              $num[$i] = substr($cif, $i, 1);
           //si no tiene un formato valido devuelve otros
           if (preg_match('((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)', $cif)!=1)
               return 'otros';
           //comprobacion de NIFs estandar
           if (preg_match('(^[0-9]{8}[A-Z]{1}$)', $cif)==1){
                  //echo $num[8]."---->".substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($cif, 0, 8) % 23, 1);
                  if ($num[8] != substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($cif, 0, 8) % 23, 1)){
                         return 'otros';
                  }
                  else{
                         return 'nif';
                  }
           }
        //algoritmo para comprobacion de codigos tipo CIF
           $suma = $num[2] + $num[4] + $num[6];
           for ($i = 1; $i < 8; $i += 2)
              $suma += substr((2 * $num[$i]),0,1) + substr((2 * $num[$i]), 1,1);
           $n = 10 - substr($suma, strlen($suma) - 1, 1);
        //comprobacion de NIFs especiales (se calculan como CIFs)
           if (preg_match('/^[KLM]{1}/', $cif)==1){
                  if ($num[8] != chr(64 + $n))
                        return 'otros';
                  else
                         return 'cif';
           }
        //comprobacion de CIFs
           if (preg_match('/^[ABCDEFGHJNPQRSUVW]{1}/', $cif)==1){
                  if ($num[8] == chr(64 + $n) || $num[8] == substr($n, strlen($n) - 1, 1))
                         return 'cif';
                  else
                         return 'otros';
           }
          //comprobacion de NIEs
           //T
           if (preg_match('/^[T]{1}/', $cif)==1){
                  if ($num[8] != ereg('^[T]{1}[A-Z0-9]{8}$', $cif))
                         return 'otros';
                  else
                         return 'nie';
           }
           //XYZ
           if (preg_match('/^[XYZ]{1}/', $cif)==1){
                   if ($num[8] != substr('TRWAGMYFPDXBNJZSQVHLCKE',
                           substr(str_replace(array('X','Y','Z'),
                                   array('0','1','2'), $cif), 0, 8) % 23, 1))
                        return 'otros';
                   else
                         return 'nie';
           }
           return 'otros';
        }
}