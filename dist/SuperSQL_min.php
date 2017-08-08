<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v2.5.0
 Built on: 08/08/2017
*/

// lib/connector/index.php
class Response{public$result;public$affected;public$ind;public$error;public$errorData;function __construct($a,$b){$this->error=!$b;if(!$b){$this->errorData=$a->errorInfo();}else{$this->result=$a->fetchAll();$this->affected=$a->rowCount();}$this->ind=0;$a->closeCursor();}function error(){return$this->error ?$this->errorData : false;}function getData(){return$this->result;}function getAffected(){return$this->affected;}function next(){return$this->result[$this->ind++];}function reset(){$this->ind=0;}}class Connector{public$queries=array();public$db;public$log=array();public$dev=false;function __construct($c,$d,$e){$this->db=new \PDO($c,$d,$e);$this->log=array();}function query($f,$g=null){if(isset($this->queries[$f])){$h=$this->queries[$f];}else{$h=$this->db->prepare($f);$this->queries[$f]=$h;}if($g)$i=$h->execute($g);else$i=$h->execute();if($this->dev)array_push($this->log,array($f,$g));return new Response($h,$i);}function _query($j,$k,$l,$m){if(isset($this->queries[$j."|".$m])){$n=$this->queries[$j."|".$m];$h=$n[1];$o=&$n[0];foreach($k as$p=>$q){$o[$p][0]=$q[0];}if($this->dev)array_push($this->log,array("fromcache",$j,$m,$k,$l));}else{$h=$this->db->prepare($j);$o=$k;foreach($o as$p=>&$r){$h->bindParam($p+1,$r[0],$r[1]);}$this->queries[$j."|".$m]=array(&$o,$h);if($this->dev)array_push($this->log,array($j,$m,$k,$l));}if(count($l)==0){$i=$h->execute();return new Response($h,$i);}else{$s=array();$i=$h->execute();array_push($s,new Response($h,$i));foreach($l as$p=>$t){foreach($t as$u=>$v){$o[$u][0]=$v;}$i=$h->execute();array_push($s,new Response($h,$i));}return$s;}}function close(){$this->db=null;$this->queries=null;}function clearCache(){$this->queries=array();}}
// lib/parser/Simple.php
class SimParser{public static function WHERE($a,&$b,&$c){if(count($a)!=0){$b.=" WHERE ";$d=0;foreach($a as$e=>$f){if($d!=0){$b.=" AND ";}$b.="`".$e."` = ?";array_push($c,$f);$d++;}}}public static function SELECT($g,$h,$a,$i){$b="SELECT ";$c=array();$j=count($h);if($j==0){$b.="*";}else{for($d=0;$d<$j;$d++){if($d!=0){$b.=", ";}$b.="`".$h[$d]."`";}}$b.=" FROM `".$g."`";self::WHERE($a,$b,$c);if($i)$b.=" ".$i;return array($b,$c);}public static function INSERT($g,$k){$b="INSERT INTO `".$g."` (";$l=") VALUES (";$c=array();$d=0;foreach($k as$e=>$f){if($d!=0){$b.=", ";$l.=", ";}$b.="`".$e."`";$l.="?";array_push($c,$f);$d++;}$b.=$l;return array($b,$c);}public static function UPDATE($g,$k,$a){$b="UPDATE `".$g."` SET ";$c=array();$d=0;foreach($k as$e=>$f){if($d!=0){$b.=", ";}$b.="`".$e."` = ?";array_push($c,$f);$d++;}self::WHERE($a,$b,$c);return array($b,$c);}public static function DELETE($g,$a){$b="DELETE FROM `".$g."`";$c=array();self::WHERE($a,$b,$c);return array($b,$c);}}
// lib/parser/Advanced.php
class AdvParser{private static function getArg(&$a){if(substr($a,0,1)=="["&&substr($a,3,1)=="]"){$b=substr($a,1,2);$a=substr($a,4);return$b;}else{return false;}}private static function append(&$c,$d,$e){if(gettype($d)=="array"){$f=count($d);for($g=1;$g<$f;$g++){if(!isset($c[$g]))$c[$g]=array();$c[$g][$e]=$d[$g];}}}private static function append2(&$h,$i,$j){function stripArgs(&$k){if(substr($k,-1)=="]"){$l=strrpos($k,"[",-1);$k=substr($k,0,$l);}$l=strrpos($k,"]",-1);if($l!==false)$k=substr($k,$l+1);}function recurse(&$m,$d,$i,$n){foreach($d as$g=>$o){if(gettype($o)=="array"){stripArgs($g);if(isset($o[0])){if(isset($i[$g."#".$n."*"]))$p=$i[$g."#".$n."*"];else$p=$i[$g."*"];foreach($o as$q=>$r){$m[$p+$q]=$r;}}else{recurse($m,$o,$i,$n."/".$g);}}else{stripArgs($g);if(isset($i[$g."#".$n]))$p=$i[$g."#".$n];else$p=$i[$g];$m[$p]=$o;}}}$f=count($j);for($k=1;$k<$f;$k++){$d=$j[$k];if(!isset($h[$k]))$h[$k]=array();recurse($h[$k],$d,$i,"");}}private static function quote($a){$a=explode(".",$a);$b="";for($q=0;$q<count($a);$q++){if($q!=0)$b.=".";$b.="`".$a[$q]."`";}return$b;}private static function table($s){if(gettype($s)=="array"){$t="";for($q=0;$q<count($s);$q++){$u=self::getType($s[i]);if($q!=0)$t.=", ";$t.=self::quote($s[$q]);if($u)$t.=" AS ".self::quote($u);}return$t;}else{return self::quote($s);}}private static function value($v,$w,&$x){$y=strtolower($v);if(!$y)$y=strtolower(gettype($w));$v=\PDO::PARAM_INT;if($y=="boolean"||$y=="bool"){$v=\PDO::PARAM_BOOL;$w=$w ? "1" : "0";$x.="b";}else if($y=="integer"||$y=="int"){$x.="i";}else if($y=="string"||$y=="str"){$v=\PDO::PARAM_STR;$x.="s";}else if($y=="double"||$y=="doub"){$w=(int)$w;$x.="i";}else if($y=="resource"||$y=="lob"){$v=\PDO::PARAM_LOB;$x.="l";}else if($y=="null"){$v=\PDO::PARAM_NULL;$w=null;$x.="n";}return array($w,$v);}private static function getType(&$a){if(substr($a,-1)=="]"){$z=strpos($a,"[");if($z===false){return "";}$b=substr($a,$z+1,-1);$a=substr($a,0,$z);return$b;}else return "";}private static function rmComments($a){$q=strpos($a,"#");if($q!==false){$a=substr($a,0,$q);}return trim($a);}private static function conditions($j,&$aa=false,&$ba=false,&$x="",&$e=0){$ca=function(&$ca,$j,&$ba,&$e,&$aa,&$x,$da=" AND ",$ea=" = ",$fa=""){$ga=0;$t="";foreach($j as$k=>$d){if(substr($k,0,1)==="#"){$ha=true;$k=substr($k,1);}else{$ha=false;}$ia=self::getArg($k);$ja=$ia ? self::getArg($k): false;$ka=gettype($d);$la=!isset($d[0]);$ma=$da;$na=$ea;switch($ia){case "||":$ia=$ja;$ma=" OR ";break;case "&&":$ia=$ja;$ma=" AND ";break;}switch($ia){case ">>":$na=" > ";break;case "<<":$na=" < ";break;case ">=":$na=" >= ";break;case "<=":$na=" <= ";break;case "!=":$na=" != ";break;case "~~":$na=" LIKE ";break;case "!~":$na=" NOT LIKE ";break;default: if(!$la||$ia=="==")$na=" = ";break;}if($ga!=0)$t.=$da;if($ka=="array"){if($la){$t.="(".$ca($ca,$d,$ba,$e,$aa,$ma,$na,$fa."/".$k).")";}else{$v=self::getType($k);$oa=self::rmComments($k);if($ba!==false&&!$ha){$ba[$k."*"]=$e;$ba[$k."#".$fa."*"]=$e++;}foreach($w as$g=>$o){if($g!=0)$t.=$ma;$e++;if($ha){$t.=self::quote($oa).$na.$o;}else if($aa!==false){$t.="`".$oa."`".$na."?";array_push($aa,self::value($v,$o,$x));}else{$t.=self::quote($oa).$na;if(gettype($o)=="integer"){$t.=$o;}else{$t.=self::quote($o);}}}}}else{if($ha){$t.=self::quote(self::rmComments($k)).$na.$d;}else{if($aa!==false){$u=self::getType($k);$t.="`".self::rmComments($k)."`".$na."?";array_push($aa,self::value($u,$d,$x));}else{$t.=self::quote(self::rmComments($k)).$na;if(gettype($d)=="integer"){$t.=$d;}else{$t.=self::quote($d);}}if($ba!==false){$ba[$k]=$e;$ba[$k."#".$fa]=$e++;}}}return$t;}$ga++;};return$ca($ca,$j,$ba,$e,$aa,$x);}static function SELECT($s,$pa,$qa,$da,$ra){$t="SELECT ";$f=count($pa);$aa=array();$h=array();if($f==0){$t.="*";}else{$q=0;$sa=0;$ta="";if($pa[0]=="DISTINCT"){$q=1;$sa=1;$t.="DISTINCT ";}else if(substr($pa[0],0,11)=="INSERT INTO"){$q=1;$sa=1;$t=$pa[0]." ".$t;}else if(substr($pa[0],0,4)=="INTO"){$q=1;$sa=1;$ta=" ".$pa[0]." ";}if($f>$sa){for(;$q<$f;$q++){$u=self::getType($pa[$q]);if($q>$sa){$t.=", ";}$t.=self::quote($pa[$q]);if($u)$t.=" AS `".$u."`";}}else$t.="*";$t.=$ta;}$t.=" FROM ".self::table($s);if($da){foreach($da as$k=>$d){if(substr($k,0,1)==="#"){$ha=true;$k=substr($k,1);}else{$ha=false;}$ia=self::getArg($k);switch($ia){case "<<":$t.=" RIGHT JOIN ";break;case ">>":$t.=" LEFT JOIN ";break;case "<>":$t.=" FULL JOIN ";break;default:$t.=" JOIN ";break;}$t.=self::quote($k)." ON ";if($ha){$t.="val";}else{$t.=self::conditions($d);}}}$x="";if(count($qa)!=0){$t.=" WHERE ";$e=array();if(isset($qa[0])){$t.=self::conditions($qa[0],$aa,$e,$x);self::append2($h,$e,$qa);}else{$t.=self::conditions($qa,$aa,$e,$x);}}if($ra)$t.=" LIMIT ".$ra;return array($t,$aa,$h,$x);}static function INSERT($s,$ua){$t="INSERT INTO ".self::table($s)." (";$aa=array();$h=array();$x="";$va="";$q=0;$l=0;$i=array();$wa=isset($ua[0]);$j=$wa ?$ua[0]:$ua;foreach($j as$k=>$d){if(substr($k,0,1)==="#"){$ha=true;$k=substr($k,1);}else{$ha=false;}if($l!=0){$t.=", ";$va.=", ";}$v=self::getType($k);$t.="`".self::rmComments($k)."`";if($ha){$va.=$d;}else{$va.="?";array_push($aa,self::value($v,$d,$x));if($wa){$i[$k]=$q++;}else{self::append($h,$d,$q++);}}$l++;}if($wa)self::append2($h,$i,$ua);$t.=") VALUES (".$va.")";return array($t,$aa,$h,$x);}static function UPDATE($s,$ua,$qa){$t="UPDATE ".self::table($s)." SET ";$aa=array();$h=array();$x="";$q=0;$l=0;$i=array();$wa=isset($ua[0]);$j=$wa ?$ua[0]:$ua;foreach($j as$k=>$d){if(substr($k,0,1)==="#"){$ha=true;$k=substr($k,1);}else{$ha=false;}if($l!=0){$t.=", ";}if($ha){$t.="`".$k."` = ".$d;}else{$ia=self::getArg($k);$t.="`".$k."` = ";switch($ia){case "+=":$t.="`".$k."` + ?";break;case "-=":$t.="`".$k."` - ?";break;case "/=":$t.="`".$k."` / ?";break;case "*=":$t.="`".$k."` * ?";break;default:$t.="?";break;}$v=self::getType($k);array_push($aa,self::value($v,$d,$x));if($wa){$i[$k]=$q++;}else{self::append($h,$d,$q++);}}$l++;}if($wa)self::append2($h,$i,$ua);if(count($qa)!=0){$t.=" WHERE ";$e=array();if(isset($qa[0])){$t.=self::conditions($qa[0],$aa,$e,$x,$q);self::append2($h,$e,$qa);}else{$t.=self::conditions($qa,$aa,$e,$x,$q);}}return array($t,$aa,$h,$x);}static function DELETE($s,$qa){$t="DELETE FROM ".self::table($s);$aa=array();$h=array();$x="";if(count($qa)!=0){$t.=" WHERE ";$e=array();if(isset($qa[0])){$t.=self::conditions($qa[0],$aa,$e,$x);self::append2($h,$e,$qa);}else{$t.=self::conditions($qa,$aa,$e,$x);}}return array($t,$aa,$h,$x);}}
// index.php
class SuperSQL{public$con;function __construct($a,$b,$c){$this->con=new Connector($a,$b,$c);}function SELECT($d,$e=array(),$f=array(),$g=null,$h=false){if(gettype($g)=="integer"){$h=$g;$g=null;}$i=AdvParser::SELECT($d,$e,$f,$g,$h);return$this->con->_query($i[0],$i[1],$i[2],$i[3]);}function INSERT($d,$j){$i=AdvParser::INSERT($d,$j);return$this->con->_query($i[0],$i[1],$i[2],$i[3]);}function UPDATE($d,$j,$f=array()){$i=AdvParser::UPDATE($d,$j,$f);return$this->con->_query($i[0],$i[1],$i[2],$i[3]);}function DELETE($d,$f=array()){$i=AdvParser::DELETE($d,$f);return$this->con->_query($i[0],$i[1],$i[2],$i[3]);}function sSELECT($d,$e=array(),$f=array(),$k=""){$i=SimParser::SELECT($d,$e,$f,$k);return$this->con->query($i[0],$i[1]);}function sINSERT($d,$j){$i=SimParser::INSERT($d,$j);return$this->con->query($i[0],$i[1]);}function sUPDATE($d,$j,$f=array()){$i=SimParser::UPDATE($d,$j,$f);return$this->con->query($i[0],$i[1]);}function sDELETE($d,$f=array()){$i=SimParser::DELETE($d,$f);return$this->con->query($i[0],$i[1]);}function query($l,$m=null){return$this->con->query($l,$m);}function close(){$this->con->close();}function dev(){$this->con->dev=true;}function getLog(){return$this->con->log;}function clearCache(){$this->con->clearCache();}function transact($n){$this->con->db->beginTransaction();$o=$n($p);if($o===false)$p->con->db->rollBack();else$p->con->db->commit();return$o;}}
?>