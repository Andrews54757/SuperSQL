<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.2
 Built on: 18/08/2017
*/

class SQLHelper{public$s;public$connections;function __construct($a,$b=null,$c=null,$d=null,$e=array()){$this->connections=array();if(is_array($a)){if(is_array($a[0])){foreach($a as$f=>$g){$h=isset($g['host'])?$g['host']: '';$b=isset($g['db'])?$g['db']: '';$c=isset($g['user'])?$g['user']: '';$d=isset($g['password'])?$g['password']: '';$i=isset($g['options'])?$g['options']: array();$j=self::connect($h,$b,$c,$d,$i);array_push($this->connections,$j);}}else{foreach($a as$f=>$g){array_push($this->connections,$g);}}$this->s=$this->connections[0];}else if(is_string($a)){$this->s=self::connect($a,$b,$c,$d,$e);array_push($this->connections,$this->s);}else{array_push($this->connections,$a);$this->s=$a;}}static function connect($h,$b,$c,$d,$e=array()){$k='mysql';$l=false;if(is_string($e)){if(strpos($e,':')!==false){$l=$e;}else{$k=strtolower($e);}}else if(isset($e['dbtype']))$k=strtolower($e['dbtype']);if(!$l){$m='';switch($k){case 'pgsql':$m='pgsql';$n=array('dbname'=>$b,'host'=>$h);if(isset($e['port']))$n['port']=$e['port'];break;case 'sybase':$m='dblib';$n=array('dbname'=>$b,'host'=>$h);if(isset($e['port']))$n['port']=$e['port'];break;case 'oracle':$m='oci';$n=array('dbname'=>isset($h)? '//'.$h.':'.(isset($e['port'])?$e['port']: '1521').'/'.$b :$b);break;default:$m='mysql';$n=array('dbname'=>$b);if(isset($e['socket']))$n['unix_socket']=$e['socket'];else{$n['host']=$h;if(isset($e['port']))$n['port']=$e['port'];}break;}$l=$m.':';if(isset($e['charset'])){$n['charset']=$e['charset'];}$l=$m.':';$o=0;foreach($n as$f=>$p){if($o!=0){$l.=';';}$l.=$f.'='.$p;$o++;}}return new SuperSQL($l,$c,$d);}private static function rmComments($q){$r=strpos($q,'#');if($r!==false){$q=substr($q,0,$r);}return trim($q);}private static function escape($s){$t=strtolower(gettype($s));if($t=='boolean'){$s=$s ? '1' : '0';}else if($t=='string'){$s='\''.$s.'\'';}else if($t=='double'||$t=='integer'){$s=(int)$s;}else if($t=='null'){$s='0';}return$s;}private static function escape2($s){if(is_numeric($s)){return(int)$s;}else{return '\''.$s.'\'';}}private static function includes($p,$u){foreach($u as$g){if(strpos($p,$g)!==false)return true;}return false;}private static function containsAdv($u,$v=false){if($v){foreach($u as$f=>&$p){if(is_array($p))return true;if(self::includes($p,array('[')))return true;if(self::includes($p,array('DISTINCT','INSERT INTO','INTO')))return true;}}else{foreach($u as$f=>&$p){if(is_array($p))return true;if(self::includes($f,array('[','#')))return true;}}return false;}function change($w){$this->s=$this->connections[$w];return$this->s;}function getCon($x=false){if($x){return$this->connections;}else{return$this->s;}}function get($y,$z=array(),$aa=array(),$ba=null){$ca=$this->s->SELECT($y,$z,$aa,$ba,1)->getData();return($ca&&$ca[0])?$ca[0]: false;}function create($y,$n){$da='CREATE TABLE `'.$y.'` (';$r=0;foreach($n as$f=>$p){if($r!=0){$da.=', ';}$da.='`'.$f.'` '.$p;$r++;}$da.=')';return$this->s->query($da);}function drop($y){return$this->s->query('DROP TABLE `'.$y.'`');}function replace($y,$n,$aa=array()){$ea=array();foreach($n as$f=>$p){$q='`'.self::rmComments($f).'`';foreach($p as$fa=>$g){$q='REPLACE('.$q.', '.self::escape2($fa).', '.self::escape($g).')';}$ea['#'.$f]=$q;}return$this->s->UPDATE($y,$ea,$aa);}function select($y,$z=array(),$aa=array(),$ba=null,$ga=false){if(is_array($y)||self::containsAdv($z,true)||self::containsAdv($aa)||$ba){return$this->s->SELECT($y,$z,$aa,$ba,$ga);}else{if(is_int($ga))$ga='LIMIT '.(int)$ga;return$this->s->sSELECT($y,$z,$aa,$ga);}}function insert($y,$n){if(is_array($y)||self::containsAdv($n)){return$this->s->INSERT($y,$n);}else{return$this->s->sINSERT($y,$n);}}function update($y,$n,$aa=array()){if(is_array($y)||self::containsAdv($n)||self::containsAdv($aa)){return$this->s->UPDATE($y,$n,$aa);}else{return$this->s->sUPDATE($y,$n,$aa);}}function delete($y,$aa=array()){if(is_array($y)||self::containsAdv($aa)){return$this->s->DELETE($y,$aa);}else{return$this->s->sDELETE($y,$aa);}}function sqBase($da,$aa,$ba){$ha=array();if($ba){AdvParser::JOIN($ba,$da);}if(count($aa)!=0){$da.=' WHERE ';$da.=AdvParser::conditions($aa,$ha);}$ia=$this->_query($da,$ha);return$ia[0]->fetchColumn();}function count($y,$aa=array(),$ba=array()){return$this->sqBase('SELECT COUNT(*) FROM `'.$y.'`',$aa,$ba);}function avg(){return$this->sqBase('SELECT AVG(`'.$column.'`) FROM `'.$y.'`',$aa,$ba);}function max($y,$ja,$aa=array(),$ba=array()){return$this->sqBase('SELECT MAX(`'.$ja.'`) FROM `'.$y.'`',$aa,$ba);}function min($y,$ja,$aa=array(),$ba=array()){return$this->sqBase('SELECT MIN(`'.$ja.'`) FROM `'.$y.'`',$aa,$ba);}function sum($y,$ja,$aa=array(),$ba=array()){return$this->sqBase('SELECT SUM(`'.$ja.'`) FROM `'.$y.'`',$aa,$ba);}function _query($da,$ka){$la=$this->s->con->db->prepare($da);foreach($ka as$f=>&$ma){$la->bindParam($f+1,$ma[0],$ma[1]);}$na=$la->execute();return array($la,$na);}function query($oa,$o=null){return$this->s->con->query($oa,$o);}function transact($pa){return$this->s->transact($pa);}function selectMap($y,$qa,$aa=array(),$ba=null,$ga=false){$z=array();$ra=array();function recurse($n,&$sa,&$z,&$ra){foreach($n as$f=>$p){if(is_int($f)){array_push($z,$p);$ta=AdvParser::getType($p);if($ta){$j=AdvParser::getType($p);if($j){$ta=$j;}else if($ta==="int"||$ta==="bool"||$ta==="string"||$ta==="json"||$ta==="obj"){$ta=false;}}if($ta){array_push($sa,$ta);}else{preg_match('/(?:[^\.]*\.)?(.*)/',$p,$ua);array_push($sa,$ua[1]);}}else{$sa[$f]=array();recurse($p,$sa[$f],$z,$ra);}}}recurse($qa,$ra,$z,$ra);$va=$this->s->select($y,$z,$aa,$ba,$ga);$ca=$va->getData();function recurse2($n,$wa,&$xa){$xa=array();foreach($n as$f=>$p){if(is_int($f)){$xa[$p]=$wa[$p];}else{recurse2($p,$wa,$xa[$f]);}}}$va->result=array();foreach($ca as$r=>$wa){recurse2($ra,$wa,$va->result[$r]);}return$va;}}
?>