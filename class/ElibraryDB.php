<?php


class ElibraryDB
{
    function __construct()
    {
    }


    function saveOrganisation($data)
    {
        $table = 'organisations';
        return save($data, $table);
    }

    function savePublication($data)
    {
        $table = 'publications';

        return save($data, $table);
    }

    function getPublication($id)
    {
        $table = 'publications';
        $publication = getById($table, $id);

        $publication['refs'] = $this->getPublicationRefs($id);
        $publication['authors'] = $this->getPublicationAuthors($id);

        return $publication;
    }

    function getPublicationAuthors($id)
    {
        global $db;
        $table = 'publications_to_authors';
        $filter = 'publicationid';
        $column = 'authorid';

        $query = 'SELECT ?n FROM ?n WHERE ?n=?i';
        return $db->getCol($query, $column, $table, $filter, $id);
    }

    function getPublicationRefs($id)
    {
        global $db;
        $table = 'publications_to_publications';
        $filter = 'origin_publ_id';
        $column = 'end_publ_id';

        $query = 'SELECT ?n FROM ?n WHERE ?n=?i';
        return $db->getCol($query, $column, $table, $filter, $id);
    }

    function getRefOriginPublications($id)
    {
        global $db;
        $table = 'publications_to_publications';
        $filter = 'end_publ_id';
        $column = 'origin_publ_id';

        $query = 'SELECT ?n FROM ?n WHERE ?n=?i';
        return $db->getCol($query, $column, $table, $filter, $id);
    }


    function saveAuthor($data)
    {
        $table = 'authors';
        return save($data, $table);
    }


    function relationOrganisationPublication($publication_id, $organisation_id)
    {
        $table = 'publications_to_organisations';

        $data = [
            'publicationid' => $publication_id,
            'orgsid' => $organisation_id
        ];


        return saveRelation($data, $table);
    }

    function relationAuthorPublication($publication_id, $author_id)
    {
        $table = 'publications_to_authors';

        $data = [
            'publicationid' => $publication_id,
            'authorid' => $author_id
        ];

        return saveRelation($data, $table);
    }

    function relationOrganisationAuthor($author_id, $organisation_id)
    {
        $table = 'authors_to_organisations';

        $data = [
            'orgsid' => $organisation_id,
            'authorid' => $author_id
        ];
        return saveRelation($data, $table);
    }

    function relationPublicationPublication($publication_id, $publication_id_origin)
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