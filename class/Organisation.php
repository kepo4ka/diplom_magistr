<?php


class Organisation
{
    static $table = 'organisations';
    static $primary = 'id';


    static function get($id, $full = false)
    {
        $organisation = getById(self::$table, $id);
        $organisation['publications'] = array();
        $organisation['authors'] = array();

        if (!empty($organisation)) {
            if ($full) {
                $organisation['publications'] = self::getPublications($id);
                $organisation['authors'] = self::getAuthors($id);
            }
        } else {
            $author = ElibraryCurl::getAuthorInfo($id);
            self::save($author);
        }

        return $author;
    }


    static function getPublications($id)
    {
        $table = 'publications_to_organisations';
        $needed = 'publicationid';
        $column = 'orgsid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function getAuthors($id)
    {
        $table = 'authors_to_organsations';
        $needed = 'authorid';
        $column = 'orgsid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function parse($id)
    {
        $organisation = getById(self::$table, $id);
        if (empty($organisation)) {
            $organisation = ElibraryCurl::getOrganisationInfo($id);
            self::save($organisation);
            return true;
        }
        return false;
    }

    static function save($organisation)
    {
        @$id = $organisation[self::$primary];

        if (empty($id)) {
            return false;
        }

        $res = save($organisation, self::$table);

        if (empty($res)) {
            return false;
        }

        return $organisation;
    }

    static function savePublications($id, $publications)
    {
        if (empty($publications)) {
            return false;
        }

        $rel_table = 'publications_to_organisations';

        foreach ($publications as $publication) {
            $data = [
                'orgsid' => $id,
                'publicationid' => $publication
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
        $publications = ElibraryCurl::getOrgPublications($id, $pagenum);
        self::savePublications($id, $publications);
        self::checkPublications($publications);
        return $publications;
    }
}


?>