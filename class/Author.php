<?php


class Author
{
    static $table = 'authors';
    static $primary = 'id';


    static function get($id, $full = false)
    {
        $author = getById(self::$table, $id);


        if (!empty($author['fio'])) {
            $author['publications'] = array();
            $author['organisations'] = array();

            if ($full) {
                $author['publications'] = self::getPublications($id);
                $author['organisations'] = self::getOrganisations($id);
            }
        } else {
            $author = ElibraryCurl::getAuthorInfo($id);
            self::save($author);
        }

        return $author;
    }

    static function parse($id)
    {
        $author = getById(self::$table, $id);
        if (empty($author)) {
            $author = ElibraryCurl::getAuthorInfo($id);
            self::save($author);
            return true;
        }
        return false;
    }


    static function getPublications($id)
    {
        $table = 'publications_to_authors';
        $needed = 'publicationid';
        $column = 'authorid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function getOrganisations($id)
    {
        $table = 'authors_to_organisations';
        $needed = 'orgsid';
        $column = 'authorid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function getOtherAuthors($id)
    {
        $table = 'authors_to_organisations';
        $needed = 'authorid';
        $column = 'orgsid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function GetMainOrganisation($id)
    {
        $table = 'authors_to_organisations';
        $needed = 'orgsid';
        $column = 'authorid';
        $res = getOneToMany($table, $column, $id, $needed, 1);
        if (!empty($res)) {
            return $res[0];
        }
        return false;
    }


    static function save($author)
    {
        @$id = $author[self::$primary];

        if (empty($id)) {
            return false;
        }

        $res = save($author, self::$table);

        if (empty($res)) {
            return false;
        }

        if (!empty($author['organisations'])) {
            self::saveOrganisations($id, $author['organisations']);
        }
        if (!empty($author['publications'])) {
            self::savePublications($id, $author['publications']);
        }

        return $author;
    }


    static function saveOrganisations($id, $organisations)
    {
        $rel_table = 'authors_to_organisations';

        foreach ($organisations as $organisation) {
            $data = [
                'orgsid' => $organisation,
                'authorid' => $id
            ];
            saveRelation($data, $rel_table);
        }
    }


    static function checkOrganisations($organisations)
    {
        if (empty($organisations)) {
            return false;
        }

        foreach ($organisations as $organisation) {
            Organisation::parse($organisation);
        }

        return $organisations;
    }


    static function savePublications($id, $publications)
    {
        if (empty($publications)) {
            return false;
        }

        $rel_table = 'publications_to_authors';

        foreach ($publications as $publication) {
            $data = [
                'publicationid' => $publication,
                'authorid' => $id
            ];
            saveRelation($data, $rel_table);
        }
        return true;
    }

    static function checkPublications($publications)
    {
        if (empty($publications)) {
            return false;
        }

        foreach ($publications as $publication) {
            Publication::parse($publication);
        }

        return true;
    }


    static function parsePublicationsPart($id, $pagenum = 1)
    {
        $publications = ElibraryCurl::getAuthorPublications($id, $pagenum);
//        self::savePublications($id, $publications);
//        self::checkPublications($publications);
        return $publications;
    }

}


?>