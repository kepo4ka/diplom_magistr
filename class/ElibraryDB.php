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

    private function save($p_data, $table, $primary = 'id')
    {
        global $db;
        if (empty($p_data)) {
            return false;
        }

        $columns = getColumnNames($table);
        $data = $db->filterArray($p_data, $columns);


        if (@!$this->checkExist($table, $data[$primary])) {
            $query = 'INSERT INTO ?n SET ?u';

            return $db->query($query, $table, $data);
        } else if (!empty($p_data[$primary])) {
            $query = 'UPDATE ?n SET ?u WHERE ?n=?i';
            return $db->query($query, $table, $data, $primary, $data[$primary]);
        }
        return true;
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


    function relationOrganisationPublication($publication_id, $organisation_id)
    {
        $table = 'publications_to_organisations';

        $data = [
            'publicationid' => $publication_id,
            'orgsid' => $organisation_id
        ];


        return $this->save($data, $table);
    }

    function relationAuthorPublication($publication_id, $author_id)
    {
        $table = 'publications_to_authors';

        $data = [
            'publicationid' => $publication_id,
            'authorid' => $author_id
        ];

        return $this->save($data, $table);
    }

    function relationOrganisationAuthor($author_id, $organisation_id)
    {
        $table = 'authors_to_organisations';

        $data = [
            'orgsid' => $organisation_id,
            'authorid' => $author_id
        ];
        return $this->save($data, $table);
    }

    function relationPublicationPublication($publication_id, $publication_id_origin)
    {
        $table = 'publications_to_publications';

        $data = [
            'origin_publ_id' => $publication_id_origin,
            'end_publ_id' => $publication_id
        ];
        return $this->save($data, $table);
    }

}


?>