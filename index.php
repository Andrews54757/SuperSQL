<?php
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

/*
Author: Andrews54757
License: MIT
Source: https://github.com/ThreeLetters/SuperSQL
*/

include "lib/parser/Advanced.php";
include "lib/parser/Simple.php";
include "lib/connector/index.php";
include "lib/helper/index.php";

use SuperSQL\AdvParser as AdvParser;
use SuperSQL\SimParser as SimParser;
use SuperSQL\Connector as Connector;


// BUILD BETWEEN
class SuperSQL
{
    public $con;
    
    /**
     * Creates a connection
     * @param {String} dsn - DSN of the connection
     * @param {String} user - Username
     * @param {String} pass - Password
     */
    function __construct($dsn, $user, $pass)
    {
        $this->con = new Connector($dsn, $user, $pass);
    }
    // BUILD ADVANCED BETWEEN
    /**
     * Queries a SQL table (SELECT)
     *
     * @param {String} table - SQL Table
     * @param {Array} columns - Columns to return
     * @param {Object|Array} where - Where clause
     * @param {Object|null} join - Join clause 
     * @param {String} limit - Limit clause 
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function SELECT($table, $columns = array(), $where = array(), $join = null, $limit = false)
    {
        if ((gettype($join) == "integer" || gettype($join) == "string") && !$limit) {
            $limit = $join;
            $join  = null;
        }
        $d = AdvParser::SELECT($table, $columns, $where, $join, $limit);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3], $d[4]);
    }
    
    /**
     * Inserts data into a SQL table
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to insert
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function INSERT($table, $data)
    {
        $d = AdvParser::INSERT($table, $data);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3]);
    }
    
    /**
     * Updates a SQL table
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to update
     * @param {Object|Array} where - Where clause
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function UPDATE($table, $data, $where = array())
    {
        $d = AdvParser::UPDATE($table, $data, $where);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3]);
    }
    
    /**
     * Deletes from a SQL table
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} where - Where clause
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function DELETE($table, $where = array())
    {
        $d = AdvParser::DELETE($table, $where);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3]);
    }
    // BUILD ADVANCED BETWEEN
    
    // BUILD SIMPLE BETWEEN
    /**
     * Queries a SQL table (SELECT) (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Array} columns - Columns to return
     * @param {Object} where - Where clause
     * @param {String} append - SQL append
     *
     * @returns {SQLResponse}
     */
    function sSELECT($table, $columns = array(), $where = array(), $append = "")
    {
        $d = SimParser::SELECT($table, $columns, $where, $append);
        return $this->con->query($d[0], $d[1]);
    }
    
    /**
     * Inserts data into a SQL table (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Object} data - Data to insert
     *
     * @returns {SQLResponse}
     */
    function sINSERT($table, $data)
    {
        $d = SimParser::INSERT($table, $data);
        return $this->con->query($d[0], $d[1]);
    }
    
    /**
     * Updates a SQL table (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Object} data - Data to update
     * @param {Object} where - Where clause
     *
     * @returns {SQLResponse}
     */
    function sUPDATE($table, $data, $where = array())
    {
        $d = SimParser::UPDATE($table, $data, $where);
        return $this->con->query($d[0], $d[1]);
    }
    
    /**
     * Deletes from a SQL table (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Object} where - Where clause
     *
     * @returns {SQLResponse}
     */
    function sDELETE($table, $where = array())
    {
        $d = SimParser::DELETE($table, $where);
        return $this->con->query($d[0], $d[1]);
    }
    
    // BUILD SIMPLE BETWEEN
    
    /**
     * Query
     */
    function query($query, $obj = null)
    {
        return $this->con->query($query, $obj);
    }
    /**
     * Closes the connection
     */
    function close()
    {
        $this->con->close();
    }
    
    /**
     * Turns on dev mode
     */
    function dev()
    {
        $this->con->dev = true;
    }
    
    /**
     * Get log
     */
    function getLog()
    {
        return $this->con->log;
    }
    /**
     * Clear cache
     */
    function clearCache() 
    {
        $this->con->clearCache();
    }
    
    /**
     * Transaction
     */
    function transact($func) {
        $this->con->db->beginTransaction();
        $r = $func($this);
        if ($r === false)
            $this->con->db->rollBack();
         else 
            $this->con->db->commit();
        
        return $r;
    }
    
}
// BUILD BETWEEN

?>
