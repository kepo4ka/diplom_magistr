<?php


class ElibraryDB
{
    function __construct()
    {
    }

    function checkExist($table, $value)
    {
        global $db;
        $query = "SELECT `id` FROM ?n WHERE `id`=?i LIMIT 1";
        $is_exist = $db->getOne($query, $table, $value);
        return $is_exist;
    }

    private function save($data, $table, $primary = 'id')
    {
        global $db;
        $columns = getColumnNames($table);
        $data = $db->filterArray($data, $columns);

        if (!$this->checkExist($table, $data[$primary])) {
            $query = 'INSERT INTO ?n SET ?u';

            return $db->query($query, $table, $data);
        } else {
            $query = 'UPDATE ?n SET ?u WHERE ?n=?i';
            return $db->query($query, $table, $data, $primary, $data[$primary]);
        }
    }


    function saveOrganisation($data)
    {
        $table = 'organisations';
        return $this->save($data, $table);
    }

    function savePublication($data)
    {
        $table = 'publications';
        return $this->save($data, $table);
    }

    function saveAuthor($data)
    {
        $table = 'authors';
        return $this->save($data, $table);
    }
}


?>