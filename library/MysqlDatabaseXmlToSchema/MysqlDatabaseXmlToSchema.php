<?php

class MysqlDatabaseXmlToSchema {

    private $db_handle;
    private $db_name;
    private $databases;
    private $tables;
    private $xml_file;
    private $queries;
    public $error_msg;

    public function __construct($xml_file, $db_name, $db_handle = null, $db_host = null, $db_user = null, $db_pass = null)
    {
        $this->db_name = $db_name;
        $this->tables = array();
        $this->queries = array();
        $this->xml_file = $xml_file;
        if($db_handle) {
            $this->db_handle = $db_handle;
        } else {
            $this->db_handle = mysql_connect($db_host, $db_user, $db_pass) or die("Could not connect to database");
        }
    }

    private function _mysql_db_xml_2_schema()
    {
        mysql_close($this->db_handle);
        unset($this->db_handle, $this->db_name, $this->tables, $this->queries);
    }

    private function getDatabases()
    {
        $res = mysql_query("SHOW DATABASES ", $this->db_handle);
        if($res && (mysql_num_rows($res) > 0)) {
            while($tbl_row = mysql_fetch_row($res)) {
                $this->databases[$tbl_row[0]] = $tbl_row[0];
            }
        }
    }

    private function getTables()
    {
        $res = mysql_query("SHOW TABLES FROM ".$this->db_name, $this->db_handle);
        if($res && (mysql_num_rows($res) > 0)) {
            while($tbl_row = mysql_fetch_row($res)) {
                $this->tables[] = $tbl_row[0];
                $this->getTableFields($tbl_row[0]);
            }
        }
    }

    private function getTableFields($table)
    {
        $res = mysql_query("SHOW FULL COLUMNS FROM `$table` ", $this->db_handle);
        if($res && (mysql_num_rows($res) > 0)) {
            while($fld_row = mysql_fetch_assoc($res)) {
                $this->tables[$table][$fld_row['Field']] = $fld_row;
            }
        }
    }

    public function XML2Schema()
    {
        $log = "";
        $this->getDatabases();
        $doc = new DOMDocument();
        $doc->load($this->xml_file);
        /* Database */
        $database = $doc->firstChild;
        /* Database name */
        $database_name = $this->db_name = $database->getAttribute('name');
        /* Database character set */
        $database_character_set = $database->getAttribute('character_set');
        /* Database collate */
        $database_collate = $database->getAttribute('collate');
        /* Check if database exists */
        if(!array_key_exists($database_name, $this->databases)) {
            $query = 'CREATE DATABASE IF NOT EXISTS';
            $query .= ' `'.$database_name.'`';
            /* Character set */
            if(!empty($database_character_set)) {
                $query .= ' CHARACTER SET = "'.$database_character_set.'"';
            }
            /* Collate */
            if(!empty($database_collate)) {
                $query .= ' COLLATE = "'.$database_collate.'"';
            }
            $mysqlQueryResult = mysql_query($query);
        }
        /* Select database */
        mysql_select_db($this->db_name, $this->db_handle) or die("Could not select the database or database doesn't exist");
        $this->getTables();
        $xml_tables = $database->getElementsByTagName('table');
        /* Setup tables */
        foreach($xml_tables as $xml_tbl) {
            $xml_tbl_comments = '';
            /* Table name */
            $xml_tbl_name = $xml_tbl->getAttribute('name');
            /* Table primary key */
            $xml_tbl_primary = $xml_tbl->getAttribute('primary');
            /* Table fields */
            $xml_flds = $xml_tbl->getElementsByTagName('fields')->item(0)->getElementsByTagName('field');
            /* Table structure string */
            $xml_tbl_struct = '';
            /**/
            if(in_array($xml_tbl_name, $this->tables)) {
                $tbl_fields = $this->tables["$xml_tbl_name"];
                foreach($xml_flds as $xml_fld) {
                    /* Field column attribute */
                    $xml_fld_name = trim($xml_fld->getAttribute('column'));
                    /* Field type attribute */
                    $xml_fld_type = trim($xml_fld->getAttribute('type'));
                    /* Field collation attribute */
                    $xml_fld_collation = trim($xml_fld->getAttribute('collation'));
                    /* Field length attribute */
                    $xml_fld_length = trim($xml_fld->getAttribute('length'));
                    /* Field default attribute */
                    $xml_fld_default = trim($xml_fld->getAttribute('default'));
                    /* Field null attribute */
                    $xml_fld_null = trim($xml_fld->getAttribute('null'));
                    /* Field extra attribute */
                    $xml_fld_extra = trim($xml_fld->getAttribute('extra'));
                    /* Field comments attribute */
                    $xml_fld_comments = trim($xml_fld->getAttribute('comments'));
                    if(array_key_exists($xml_fld_name, $tbl_fields)) {
                        /* Current table field definition */
                        $tbl_field = $tbl_fields[$xml_fld_name];
                        /**/
                        $tbl_field['Type'] = strtoupper($tbl_field['Type']);
                        $realFieldType = preg_replace('/\(.*\)/', '', $tbl_field['Type']);
                        /* Field type has changed */
                        $fieldTypeHasChanged = $realFieldType != $xml_fld_type;
                        /* Field length has changed */
                        $fieldLengthHasChanged = false;
                        if(preg_match('/^.*\([0-9]+\)$/', $tbl_field['Type'])) {
                            $fieldLength = preg_replace('/.*\(([0-9]+)\)/', '$1', $tbl_field['Type']);
                            $fieldLengthHasChanged = $xml_fld_length != $fieldLength;
                        }
                        /* Field collation has changed */
                        $fieldCollationHasChanged = !empty($xml_fld_collation) && $tbl_field['Collation'] != $xml_fld_collation;
                        /* Field null has changed */
                        $fieldNullHasChanged = !empty($xml_fld_null) && $tbl_field['Null'] != $xml_fld_null;
                        /* Field default has changed */
                        $fieldDefaultHasChanged = !empty($xml_fld_default) && $tbl_field['Default'] != $xml_fld_default;
                        /* Field extra has changed */
                        $fieldExtraHasChanged = !empty($xml_fld_extra) && $tbl_field['Extra'] != $xml_fld_extra;
                        /* Field comments has changed */
                        $fieldCommentsHasChanged = !empty($xml_fld_comments) && $tbl_field['Comment'] != $xml_fld_comments;
                        if(($fieldTypeHasChanged) ||
                                ($fieldLengthHasChanged) ||
                                ($fieldCollationHasChanged) ||
                                ($fieldNullHasChanged) ||
                                ($fieldDefaultHasChanged) ||
                                ($fieldExtraHasChanged) ||
                                ($fieldCommentsHasChanged)
                        ) {
                            $query = "ALTER TABLE `$xml_tbl_name` CHANGE `$xml_fld_name` `$xml_fld_name` ";
                            /* Type(LENGTH) */
                            if($fieldTypeHasChanged || $fieldLengthHasChanged) {
                                $query .= ' '.($xml_fld_type.($xml_fld_length ? "($xml_fld_length)" : "")).' ';
                            }
                            /* Collation */
                            if($fieldCollationHasChanged) {
                                $query .= ($xml_fld_collation ? " CHARACTER SET ".preg_replace('/_(.*)/', '', $xml_fld_collation)." COLLATE $xml_fld_collation " : "");
                            }
                            /* Null / Default */
                            if($fieldNullHasChanged) {
                                $query .= (!$xml_fld_null ? (!empty($xml_fld_default) || $xml_fld_default == '0' ? " DEFAULT $xml_fld_default" : 'NOT NULL') : ($xml_fld_default ? " DEFAULT $xml_fld_default" : 'NULL'));
                            }
                            /* Extra */
                            if($fieldExtraHasChanged) {
                                $query .= ($xml_fld_extra ? " $xml_fld_extra " : '');
                            }
                            /* Comments */
                            if($fieldCommentsHasChanged) {
                                $query .= ($xml_fld_comments ? " COMMENT '".stripslashes($xml_fld_comments)."'" : '');
                            }
                            $log .= "\n changing type of field $xml_fld_name of table $xml_tbl_name,  \n\n";
                            $this->queries[] = $query;
                        }
                    } else {
                        $query = "ALTER TABLE `$xml_tbl_name` ADD `$xml_fld_name` ";
                        /* Type(LENGTH) */
                        $query .= ' '.($xml_fld_type.($xml_fld_length ? "($xml_fld_length)" : "")).' ';
                        /* Collation */
                        $query .= ($xml_fld_collation ? " CHARACTER SET ".preg_replace('/_(.*)/', '', $xml_fld_collation)." COLLATE $xml_fld_collation " : "");
                        /* Null / Default */
                        $query .= (!$xml_fld_null ? (!empty($xml_fld_default) || $xml_fld_default == '0' ? " DEFAULT $xml_fld_default" : 'NOT NULL') : ($xml_fld_default ? " DEFAULT $xml_fld_default" : 'NULL'));
                        /* Extra */
                        $query .= ($xml_fld_extra ? " $xml_fld_extra " : '');
                        /* Comments */
                        $query .= ($xml_fld_comments ? " COMMENT '".stripslashes($xml_fld_comments)."'" : '');
                        echo $query;
                        $log .= "\n changing type of field $xml_fld_name of table $xml_tbl_name,  \n\n";
                        $this->queries[] = $query;
                    }
                }
            } else {
                $query = "CREATE TABLE IF NOT EXISTS `$xml_tbl_name` (";
                foreach($xml_flds as $xml_fld) {
                    $xml_fld_name = trim($xml_fld->getAttribute('column'));
                    $xml_fld_type = trim($xml_fld->getAttribute('type'));
                    $xml_fld_collation = trim($xml_fld->getAttribute('collation'));
                    $xml_fld_length = trim($xml_fld->getAttribute('length'));
                    $xml_fld_default = trim($xml_fld->getAttribute('default'));
                    $xml_fld_null = trim($xml_fld->getAttribute('null'));
                    $xml_fld_extra = trim($xml_fld->getAttribute('extra'));
                    $xml_fld_comments = trim($xml_fld->getAttribute('comments'));
                    /* Name */
                    $query .= PHP_EOL."`$xml_fld_name`";
                    /* Type(LENGTH) */
                    $query .= ' '.($xml_fld_type.($xml_fld_length ? "($xml_fld_length)" : "")).' ';
                    /* Collation */
                    if(!empty($xml_fld_collation)) {
                        $query .= ($xml_fld_collation ? " CHARACTER SET ".preg_replace('/_(.*)/', '', $xml_fld_collation)." COLLATE $xml_fld_collation " : "");
                    }
                    /* Null / Default */
                    $query .= (!$xml_fld_null ? (!empty($xml_fld_default) || $xml_fld_default == '0' ? " DEFAULT $xml_fld_default" : 'NOT NULL') : ($xml_fld_default ? " DEFAULT $xml_fld_default" : 'NULL'));
                    /* Extra */
                    if(!empty($xml_fld_extra)) {
                        $query .= ($xml_fld_extra ? " $xml_fld_extra " : '');
                    }
                    /* Comments */
                    if(!empty($xml_fld_comments)) {
                        $query .= ($xml_fld_comments ? " COMMENT '".stripslashes($xml_fld_comments)."'" : '');
                    }
                    /**/
                    $query .= ',';
                }
                $query .= "PRIMARY KEY(`$xml_tbl_primary`)";
                $query = rtrim($query, ',').PHP_EOL;
                $query .= ")";
                $log .= $query;
                $log .= " \n Creating table \n ".$xml_tbl_name." \n \n";
                $this->queries[] = $query;
            }
        }
        if(count($this->queries) > 0) {
            foreach($this->queries as $query) {
                mysql_query($query, $this->db_handle);
                echo PHP_EOL.mysql_error().PHP_EOL;
            }
        }
        $this->_mysql_db_xml_2_schema();
        return $log;
    }

}
