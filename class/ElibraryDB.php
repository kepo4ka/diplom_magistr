<?php


class ElibraryDB
{
    function __construct()
    {
    }

    static function saveOrganisation($data)
    {
        $table = 'organisations';
        return save($data, $table);
    }

    static function savePublication($data)
    {
        $table = 'publications';

        @$id = $data['id'];

        if (empty($id)) {
            return false;
        }

        return save($data, $table);
    }

    static function getPublication($id)
    {
        $table = 'publications';
        $publication = getById($table, $id);
        return $publication;
    }




    static function getPublicationAuthors($id)
    {
        global $db;
        $table = 'publications_to_authors';
        $filter = 'publicationid';
        $column = 'authorid';

        $query = 'SELECT ?n FROM ?n WHERE ?n=?i';
        return $db->getCol($query, $column, $table, $filter, $id);
    }

    static function getPublicationRefs($id)
    {
        global $db;
        $table = 'publications_to_publications';
        $filter = 'origin_publ_id';
        $column = 'end_publ_id';

        $query = 'SELECT ?n FROM ?n WHERE ?n=?i';
        return $db->getCol($query, $column, $table, $filter, $id);
    }

    static function getRefOriginPublications($id)
    {
        global $db;
        $table = 'publications_to_publications';
        $filter = 'end_publ_id';
        $column = 'origin_publ_id';

        $query = 'SELECT ?n FROM ?n WHERE ?n=?i';
        return $db->getCol($query, $column, $table, $filter, $id);
    }


    static function saveAuthor($data)
    {
        $table = 'authors';
        return save($data, $table);
    }


    static function saveRelationOrganisationPublication($publication_id, $organisation_id)
    {
        $table = 'publications_to_organisations';

        $data = [
            'publicationid' => $publication_id,
            'orgsid' => $organisation_id
        ];

        return saveRelation($data, $table);
    }

    static function saveRelationAuthorPublication($publication_id, $author_id)
    {
        $table = 'publications_to_authors';

        $data = [
            'publicationid' => $publication_id,
            'authorid' => $author_id
        ];

        return saveRelation($data, $table);
    }

    static function saveRelationOrganisationAuthor($author_id, $organisation_id)
    {
        $table = 'authors_to_organisations';

        $data = [
            'orgsid' => $organisation_id,
            'authorid' => $author_id
        ];

        return saveRelation($data, $table);
    }

    static function saveRelationPublicationPublication($publication_id, $publication_id_origin)
    {
        $table = 'publications_to_publications';

        $data = [
            'origin_publ_id' => $publication_id_origin,
            'end_publ_id' => $publication_id
        ];

        return saveRelation($data, $table);
    }

}


?>