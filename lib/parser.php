<?php
namespace SuperSQL;
/*
MIT License

Copyright (c) 2017 Andrew S (Andrews54757_at_gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
// BUILD BETWEEN
class Parser
{
    /**
     * Reads argument data from a string
     *
     * @param {String} str - String to read from
     *
     * @returns {String|Boolean}
     */
    static function getArg(&$str)
    {
        preg_match('/^(?:\[(?<a>.{2})\])(?<out>.*)/', $str, $m);
        if (isset($m["a"])) {
            $str = $m["out"];
            return $m["a"];
        } else {
            return false;
        }
    }
    /**
     * Appends value(s) to arguments
     *
     * @param {&Array} args - Arguments to append to
     * @param {String|Int|Boolean|Array} val - Value(s) to append
     */
    static function append(&$args, $val, $index, $values)
    {
        if (is_array($val) && $values[$index][2] < 5) {
            $len = count($val);
            for ($k = 1; $k < $len; $k++) {
                if (!isset($args[$k - 1]))
                    $args[$k - 1] = array();
                $args[$k - 1][$index] = $val[$k];
            }
        }
    }
    static function escape($val)
    {
        if (is_int($val)) {
            return (int) $val;
        } else {
            return self::quote($val);
        }
    }
    /**
     * Appends values to arguments
     *
     * @param {&Array} insert - Arguments to append to
     * @param {Object} indexes - Indexes of keys
     * @param {Array} dt - Values to append
     */
    static function append2(&$insert, $indexes, $dt, $values)
    {
        function stripArgs(&$key)
        {
            preg_match('/(?:\[.{2}\]){0,2}([^\[]*)/', $key, $matches); // 13 steps
            return $matches[1];
        }
        function escape($val, $dt)
        {
            if (!isset($dt[2]))
                return $val;
            switch ($dt[2]) {
                case 0: //Bool
                    return $val ? '1' : '0';
                    break;
                case 1: // int
                    return (int) $val;
                    break;
                case 2: // string
                    return (string) $val;
                    break;
                case 3: // lob
                    return $val;
                    break;
                case 4: // null
                    return null;
                    break;
                case 5: // json
                    return json_encode($val);
                    break;
                case 6: // seriealise
                    return serialize($val);
                    break;
            }
        }
        function recurse(&$holder, $val, $indexes, $par, $values)
        {
            foreach ($val as $k => &$v) {
                if ($k[0] === "#")
                    continue;
                stripArgs($k);
                $k1 = $k . '#' . $par;
                if (isset($indexes[$k1]))
                    $d = $indexes[$k1];
                else
                    $d = $indexes[$k];
                $isArr = is_array($v) && (!isset($values[$d][2]) || $values[$d][2] < 5);
                if ($isArr) {
                    if (isset($v[0])) {
                        foreach ($v as $i => &$j) {
                            $a = $d + $i;
                            if (isset($holder[$a]))
                                trigger_error('Key collision: ' . $k, E_USER_WARNING);
                            $holder[$a] = escape($j, $values[$a]);
                        }
                    } else {
                        recurse($holder, $v, $indexes, $par . '/' . $k, $values);
                    }
                } else {
                    if (isset($holder[$d]))
                        trigger_error('Key collision: ' . $k, E_USER_WARNING);
                    $holder[$d] = escape($v, $values[$d]);
                }
            }
        }
        $len = count($dt);
        for ($key = 1; $key < $len; $key++) {
            $val = $dt[$key];
            if (!isset($insert[$key - 1]))
                $insert[$key - 1] = array();
            recurse($insert[$key - 1], $val, $indexes, '', $values);
        }
    }
    /**
     * Puts quotes around a string
     *
     * @param {String} str - String to quote
     *
     * @returns {String}
     */
    static function quote($str)
    {
        preg_match('/([^.]*)\.?(.*)?/', $str, $matches); // 8 steps
        if ($matches[2] !== '') {
            return '`' . $matches[1] . '.' . $matches[2] . '`';
        } else {
            return '`' . $matches[1] . '`';
        }
    }
    /**
     * Forms the table
     *
     * @param {String|Array} table - tables(s)
     *
     * @returns {String}
     */
    static function table($table)
    {
        if (is_array($table)) {
            $sql = '';
            foreach ($table as $i => &$val) {
                $t = self::getType($val);
                if ($i !== 0)
                    $sql .= ', ';
                $sql .= '`' . $val . '`';
                if ($t)
                    $sql .= ' AS `' . $t . '`';
            }
            return $sql;
        } else {
            return '`' . $table . '`';
        }
    }
    static function value($type, $value)
    {
        $var   = $type ? $type : gettype($value);
        $type  = \PDO::PARAM_STR;
        $dtype = 2;
        if ($var === 'integer' || $var === 'int' || $var === 'double' || $var === 'doub') {
            $type  = \PDO::PARAM_INT;
            $dtype = 1;
            $value = (int) $value;
        } else if ($var === 'string' || $var === 'str') {
            $value = (string) $value;
            $dtype = 2;
        } else if ($var === 'boolean' || $var === 'bool') {
            $type  = \PDO::PARAM_BOOL;
            $value = $value ? '1' : '0';
            $dtype = 0;
        } else if ($var === 'null' || $var === 'NULL') {
            $dtype = 4;
            $type  = \PDO::PARAM_NULL;
            $value = null;
        } else if ($var === 'resource' || $var === 'lob') {
            $type  = \PDO::PARAM_LOB;
            $dtype = 3;
        } else if ($var === 'json') {
            $dtype = 5;
            $value = json_encode($value);
        } else if ($var === 'obj') {
            $dtype = 6;
            $value = serialize($value);
        } else {
            $value = (string) $value;
            trigger_error('Invalid type ' . $var . ' Assumed STRING', E_USER_WARNING);
        }
        return array(
            $value,
            $type,
            $dtype
        );
    }
    static function getType(&$str)
    {
        if (isset($str[1]) && $str[strlen($str) - 1] === ']') {
            $start = strrpos($str, '[');
            if ($start === false) {
                return '';
            }
            $out = substr($str, $start + 1, -1);
            $str = substr($str, 0, $start);
            return $out;
        } else
            return '';
    }
    static function rmComments($str)
    {
        preg_match('/([^#]*)/', $str, $matches);
        return $matches[1];
    }
    /**
     * Constructs logical conditional statements
     *
     * @param {Object|Array} arr - Conditions
     * @param {Array} args
     *
     * @returns {String}
     */
    static function conditions($dt, &$values = false, &$map = false, &$index = 0)
    {
        $build = function(&$build, $dt, &$map, &$index, &$values, $join = ' AND ', $operator = ' = ', $parent = '')
        {
            $num = 0;
            $sql = '';
            foreach ($dt as $key => &$val) {
                if ($key[0] === '#') {
                    $raw = true;
                    $key = substr($key, 1);
                } else {
                    $raw = false;
                }
                preg_match('/^(?:\[(?<a>.{2})\])(?:\[(?<b>.{2})\])?(?<out>.*)/', $key, $matches); // 14 steps
                if (isset($matches["a"])) {
                    $arg  = $matches["a"];
                    $key  = $matches["out"];
                    $arg2 = isset($matches["b"]) ? $matches["b"] : false;
                } else {
                    $arg = false;
                }
                $useBind     = !isset($val[0]);
                $newJoin     = $join;
                $newOperator = $operator;
                $type        = $raw ? false : self::getType($key);
                $arr         = is_array($val) && $type !== 'json' && $type !== 'obj';
                if ($arg && ($arg === '||' || $arg === '&&')) {
                    $newJoin = ($arg === '||') ? ' OR ' : ' AND ';
                    $arg     = $arg2;
                    if ($arr && $arg && ($arg === '||' || $arg === '&&')) {
                        $join    = $newJoin;
                        $newJoin = ($arg === '||') ? ' OR ' : ' AND ';
                        $arg     = self::getArg($key);
                    }
                }
                $between = false;
                if ($arg && $arg !== "==") {
                    switch ($arg) { // different conditionals
                        case '!=':
                            $newOperator = ' != ';
                            break;
                        case '>>':
                            $newOperator = ' > ';
                            break;
                        case '<<':
                            $newOperator = ' < ';
                            break;
                        case '>=':
                            $newOperator = ' >= ';
                            break;
                        case '<=':
                            $newOperator = ' <= ';
                            break;
                        case '~~':
                            $newOperator = ' LIKE ';
                            break;
                        case '!~':
                            $newOperator = ' NOT LIKE ';
                            break;
                        default:
                            if ($arg !== '><' && $arg !== '<>')
                                throw new \Exception("Invalid operator " . $arg . " Available: ==,!=,>>,<<,>=,<=,~~,!~,<>,><");
                            else
                                $between = true;
                            break;
                    }
                } else {
                    if (!$useBind || $arg === '==')
                        $newOperator = ' = '; // reset
                }
                if (!$arr)
                    $join = $newJoin;
                if ($num !== 0)
                    $sql .= $join;
                $column = self::rmComments($key);
                if (!$raw)
                    $column = self::quote($column);
                if ($arr) {
                    $sql .= '(';
                    if ($useBind) {
                        $sql .= $build($build, $val, $map, $index, $values, $newJoin, $newOperator, $parent . '/' . $key);
                    } else {
                        if ($map !== false && !$raw) {
                            $map[$key]                 = $index;
                            $map[$key . '#' . $parent] = $index++;
                        }
                        if ($between) {
                            $index += 2;
                            $sql .= $column . ($arg === '<>' ? 'NOT' : '') . ' BETWEEN ';
                            
                            if ($raw) {
                                $sql .= $val[0] . ' AND ' . $val[1];
                            } else if ($values !== false) {
                                $sql .= '? AND ?';
                                array_push($values, self::value($type, $val[0]));
                                array_push($values, self::value($type, $val[1]));
                            } else {
                                $sql .= self::escape($val[0]) . ' AND ' . self::escape($val[1]);
                            }
                        } else {
                            foreach ($val as $k => &$v) {
                                if ($k !== 0)
                                    $sql .= $newJoin;
                                $index++;
                                $sql .= $column . $newOperator;
                                if ($raw) {
                                    $sql .= $v;
                                } else if ($values !== false) {
                                    $sql .= '?';
                                    array_push($values, self::value($type, $v));
                                } else {
                                    $sql .= self::escape($v);
                                }
                            }
                        }
                        $sql .= ')';
                    }
                } else {
                    $sql .= $column . $newOperator;
                    if ($raw) {
                        $sql .= $val;
                    } else {
                        if ($values !== false) {
                            $sql .= '?';
                            array_push($values, self::value($type, $val));
                        } else {
                            $sql .= self::escape($val);
                        }
                        if ($map !== false) {
                            $map[$key]                 = $index;
                            $map[$key . '#' . $parent] = $index++;
                        }
                    }
                }
                $num++;
            }
            return $sql;
        };
        return $build($build, $dt, $map, $index, $values);
    }
    static function JOIN($join, &$sql)
    {
        foreach ($join as $key => &$val) {
            if ($key[0] === '#') {
                $raw = true;
                $key = substr($key, 1);
            } else {
                $raw = false;
            }
            $arg = self::getArg($key);
            switch ($arg) {
                case '<<':
                    $sql .= ' RIGHT JOIN ';
                    break;
                case '>>':
                    $sql .= ' LEFT JOIN ';
                    break;
                case '<>':
                    $sql .= ' FULL JOIN ';
                    break;
                default: // inner join
                    $sql .= ' JOIN ';
                    break;
            }
            $sql .= '`' . $key . '` ON ';
            if ($raw) {
                $sql .= $val;
            } else {
                $sql .= self::conditions($val);
            }
        }
    }
    static function columns($columns, &$sql, &$outTypes)
    {
        $into = '';
        $f    = $columns[0][0];
        if ($f === 'D' || $f === 'I') {
            if ($columns[0] === 'DISTINCT') {
                $req = 1;
                $sql .= 'DISTINCT ';
                array_splice($columns, 0, 1);
            } else if (substr($columns[0], 0, 11) === 'INSERT INTO') {
                $req = 1;
                $sql = $columns[0] . ' ' . $sql;
                array_splice($columns, 0, 1);
            } else if (substr($columns[0], 0, 4) === 'INTO') {
                $req  = 1;
                $into = ' ' . $columns[0] . ' ';
                array_splice($columns, 0, 1);
            }
        }
        if (isset($columns[0])) { // has var
            foreach ($columns as $i => &$val) {
                preg_match('/(?<column>[a-zA-Z0-9_\.]*)(?:\[(?<alias>[^\]]*)\])?(?:\[(?<type>.*)\])?/', $val, $match); // 8 steps
                //     echo json_encode($match);
                $val   = $match["column"];
                $alias = false;
                if (isset($match["alias"])) { // name[alias][type]
                    $alias = $match["alias"];
                    if (isset($match["type"])) {
                        $type = $match["type"];
                    } else {
                        if ($alias === "json" || $alias === "obj" || $alias === "int" || $alias === "string" || $alias === "bool") {
                            $type  = $alias;
                            $alias = false;
                        } else
                            $type = false;
                    }
                    if ($type) {
                        if (!$outTypes)
                            $outTypes = array();
                        $outTypes[$alias ? $alias : $val] = $type;
                    }
                }
                if ($i != 0) {
                    $sql .= ', ';
                }
                $sql .= self::quote($val);
                if ($alias)
                    $sql .= ' AS `' . $alias . '`';
            }
        } else
            $sql .= '*';
        $sql .= $into;
    }
    /**
     * Constructs SQL commands (SELECT)
     *
     * @param {String} table - SQL Table
     * @param {Array} columns - Columns to return
     * @param {Object|Array} where - Where clause
     * @param {Object|null} join - Join clause 
     * @param {Int} limit - Limit clause
     * 
     * @returns {Array}
     */
    static function SELECT($table, $columns, $where, $join, $limit)
    {
        $sql      = 'SELECT ';
        $values   = array();
        $insert   = array();
        $outTypes = null;
        if (!isset($columns[0])) { // none
            $sql .= '*';
        } else { // some
            self::columns($columns, $sql, $outTypes);
        }
        $sql .= ' FROM ' . self::table($table);
        if ($join) {
            self::JOIN($join, $sql);
        }
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index);
                self::append2($insert, $index, $where, $values);
            } else {
                $sql .= self::conditions($where, $values, $index);
            }
        }
        if ($limit) {
            if (is_int($limit)) {
                $sql .= ' LIMIT ' . $limit;
            } else if (is_string($limit)) {
                $sql .= ' ' . $limit;
            }
        }
        return array(
            $sql,
            $values,
            $insert,
            $outTypes
        );
    }
    /**
     * Constructs SQL commands (INSERT INTO)
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to insert
     *
     * @returns {Array}
     */
    static function INSERT($table, $data)
    {
        $sql     = 'INSERT INTO ' . self::table($table) . ' (';
        $values  = array();
        $insert  = array();
        $append  = '';
        $i       = 0;
        $b       = 0;
        $indexes = array();
        $multi   = isset($data[0]);
        $dt      = $multi ? $data[0] : $data;
        foreach ($dt as $key => &$val) {
            if ($key[0] === '#') {
                $raw = true;
                $key = substr($key, 1);
            } else {
                $raw = false;
            }
            if ($b !== 0) {
                $sql .= ', ';
                $append .= ', ';
            }
            preg_match('/(?<out>[^\#\[]*)(?:#[^[]*)?(?:\[(?<type>[^]]*)\])?/', $key, $matches);
            $key  = $matches["out"];
            $type = isset($matches["type"]) ? $matches["type"] : false;
            $sql .= '`' . $key . '`';
            if ($raw) {
                $append .= $val;
            } else {
                $append .= '?';
                array_push($values, self::value($type, $val));
                if ($multi) {
                    $indexes[$key] = $i++;
                } else {
                    self::append($insert, $val, $i++, $values);
                }
            }
            $b++;
        }
        if ($multi)
            self::append2($insert, $indexes, $data, $values);
        $sql .= ') VALUES (' . $append . ')';
        return array(
            $sql,
            $values,
            $insert
        );
    }
    /**
     * Constructs SQL commands (UPDATE)
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to update
     * @param {Object|Array} where - Where clause
     *
     * @returns {Array}
     */
    static function UPDATE($table, $data, $where)
    {
        $sql     = 'UPDATE ' . self::table($table) . ' SET ';
        $values  = array();
        $insert  = array();
        $i       = 0;
        $b       = 0;
        $indexes = array();
        $multi   = isset($data[0]);
        $dt      = $multi ? $data[0] : $data;
        foreach ($dt as $key => &$val) {
            if ($key[0] === '#') {
                $raw = true;
                $key = substr($key, 1);
            } else {
                $raw = false;
            }
            if ($b !== 0) {
                $sql .= ', ';
            }
            if ($raw) {
                $sql .= '`' . $key . '` = ' . $val;
            } else {
                preg_match('/(?:\[(?<arg>.{2})\])?(?<out>[^\[]*)(?:\[(?<type>[^\]]*)\])?/', $key, $matches);
                $key = $matches["out"];
                $sql .= '`' . $key . '` = ';
                if (isset($matches["arg"])) {
                    switch ($matches["arg"]) {
                        case '+=':
                            $sql .= '`' . $key . '` + ?';
                            break;
                        case '-=':
                            $sql .= '`' . $key . '` - ?';
                            break;
                        case '/=':
                            $sql .= '`' . $key . '` / ?';
                            break;
                        case '*=':
                            $sql .= '`' . $key . '` * ?';
                            break;
                        default:
                            $sql .= '?';
                            break;
                    }
                }
                $type = isset($matches["type"]) ? $matches["type"] : false;
                array_push($values, self::value($type, $val));
                if ($multi) {
                    $indexes[$key] = $i++;
                } else {
                    self::append($insert, $val, $i++, $values);
                }
            }
            $b++;
        }
        if ($multi)
            self::append2($insert, $indexes, $data, $values);
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index, $i);
                self::append2($insert, $index, $where, $values);
            } else {
                $sql .= self::conditions($where, $values, $index, $i);
            }
        }
        return array(
            $sql,
            $values,
            $insert
        );
    }
    /**
     * Constructs SQL commands (DELETE)
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} where - Where clause
     *
     * @returns {Array}
     */
    static function DELETE($table, $where)
    {
        $sql    = 'DELETE FROM ' . self::table($table);
        $values = array();
        $insert = array();
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index);
                self::append2($insert, $index, $where, $values);
            } else {
                $sql .= self::conditions($where, $values, $index);
            }
        }
        return array(
            $sql,
            $values,
            $insert
        );
    }
}
// BUILD BETWEEN
?>
