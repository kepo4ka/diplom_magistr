<?php


class ElibraryDB
{
    function __construct()
    {
    }

    function saveOrganisation($data)
    {
        global $db;
        $table = 'organisations';

        $query = 'SELECT `orgsid` FROM ?n WHERE `orgsid`=?i LIMIT 1';

        $is_exist = $db->getOne($query, $table, $data['orgsid']);

        if (!$is_exist) {
            $query = 'INSERT INTO ?n SET ?u';

            return $db->query($query, $table, $data);
        }
        return true;
    }


    function savePublication($data)
    {
        global $db;
        $table = 'publications';


        $query = 'SELECT `publicationid` FROM ?n WHERE `publicationid`=?i LIMIT 1';

        $is_exist = $db->getOne($query, $table, $data['id']);

        if (!$is_exist) {
            $insert = array();
            $insert['publicationid'] = $data['id'];
            $insert['title'] = $data['title'];
            $insert['type'] = $data['type'];
            $insert['year'] = $data['year'];
            $insert['language'] = $data['language'];

            $query = 'INSERT INTO ?n SET ?u';

            return $db->query($query, $table, $insert);
        }
        return true;
    }
}


?>