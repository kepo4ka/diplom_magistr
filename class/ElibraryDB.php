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

    function saveOrganisation($data)
    {
        global $db;
        $table = 'organisations';

        if (!$this->checkExist($table, $data['orgsid'])) {
            $query = 'INSERT INTO ?n SET ?u';

            return $db->query($query, $table, $data);
        }
        return true;
    }

    function savePublication($data)
    {
        global $db;
        $table = 'publications';


        if (!$this->checkExist($table, $data['id'])) {
            $insert = array();
            $insert['id'] = $data['id'];
            $insert['title'] = $data['title'];
            $insert['type'] = $data['type'];
            $insert['year'] = $data['year'];
            $insert['language'] = $data['language'];

            $query = 'INSERT INTO ?n SET ?u';

            return $db->query($query, $table, $insert);
        }
        return true;
    }

    function saveAuthor($data)
    {
        global $db;
        $table = 'authors';

        if (!$this->checkExist($table, $data['id'])) {
            $insert = array();
            $insert['id'] = $data['id'];
            $insert['fio'] = $data['fio'];
            $insert['articles_count'] = 0;
            $insert['citation_count'] = 0;
            $insert['hirsch_index'] = 0;

            $query = 'INSERT INTO ?n SET ?u';

            $db->query($query, $table, $insert);
        }

        return true;
    }
}


?>