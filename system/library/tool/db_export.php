<?php
namespace Tool;


class Db_Export {
    private $link;
    public function __construct($registry) {
        $config = $registry->get('config');
        $this->link = @mysql_connect($config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'));
        mysql_select_db($config->get('db_database'));
    }

    /**
     * @param $sql
     * @param array $fields 设置需要输出的字段
     * @return array
     */
    public function getUnBufferedResult($sql, $fields = array()) {
        $result = mysql_unbuffered_query($sql, $this->link);
        $fields_count = mysql_num_fields($result);

        if (!empty($fields) && is_array($fields)) {
            for ($i = 0; $i < $fields_count; $i++) {
//                if ($)
            }
        }
        $data = array();
        while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
            $data[] = $row;
        }
        return $data;
    }

    public function selectDB($dbName) {
        mysql_select_db($dbName, $this->link);
        return $this;
    }
}
