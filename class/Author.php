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

                if (empty($author['organisations'])) {
                    $author = ElibraryCurl::getAuthorInfo($id);
                    self::save($author);
                }
            }
        } else {
            $author = ElibraryCurl::getAuthorInfo($id);
            self::save($author);
        }

        if (!empty($author['organisations'])) {
            foreach ($author['organisations'] as $organisation_id) {
                Organisation::get($organisation_id, true);
            }
        }

        return $author;
    }

    static function parse($id)
    {
        $author = getById(self::$table, $id);

        if (empty($author)) {
            $author = ElibraryCurl::getAuthorInfo($id);
            $res = self::save($author);
            if (empty($res)) {
                return false;
            }
        } else {
            if (!empty($author['organisations'])) {
                foreach ($author['organisations'] as $organisation_id) {
                    Organisation::get($organisation_id, true);
                }
            }
        }
        return true;
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
        if (empty($organisations)) {
            return false;
        }

        foreach ($organisations as $organisation) {
            self::saveOrganisation($id, $organisation);
        }
        return true;
    }

    static function saveOrganisation($id, $organisation)
    {
        $rel_table = 'authors_to_organisations';

        if (empty($organisation)) {
            return false;
        }

//        Organisation::get($organisation);

        $data = [
            'orgsid' => $organisation,
            'authorid' => $id
        ];
        return saveRelation($data, $rel_table);
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

        foreach ($publications as $publication) {
            self::savePublication($id, $publication);
        }
        return true;
    }

    static function savePublication($id, $publication)
    {
        if (empty($publications)) {
            return false;
        }

        $rel_table = 'publications_to_authors';

//        Publication::get($publication);

        $data = [
            'publicationid' => $publication,
            'authorid' => $id
        ];
        return saveRelation($data, $rel_table);
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
        self::savePublications($id, $publications);
        self::checkPublications($publications);
        return $publications;
    }

}


?>