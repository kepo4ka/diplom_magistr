<?php


class Publication
{
    static $table = 'publications';
    static $primary = 'id';

    static function get($id, $full = false)
    {
        $publication = getById(self::$table, $id);

        if (!empty($publication)) {
            if ($full) {
                $publication['refs'] = self::getRefs($id);
                $publication['authors'] = self::getAuthors($id);
                $publication['organisations'] = self::getOrganisations($id);
            }
        } else {
            $publication = ElibraryCurl::getPublication($id);
            echoVarDumpPre($publication);
            self::save($publication);
        }
        return $publication;
    }

    static function getRefs($id)
    {
        $table = 'publications_to_publications';
        $needed = 'end_publ_id';
        $column = 'origin_publ_id';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function getAuthors($id)
    {
        $table = 'publications_to_authors';
        $needed = 'authorid';
        $column = 'publicationid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function getOrganisations($id)
    {
        $table = 'publications_to_organisations';
        $needed = 'orgsid';
        $column = 'publicationid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function parse($id)
    {
        $publication = getById(self::$table, $id);
        if (empty($publication)) {
            $publication = ElibraryCurl::getPublication($id);
            self::save($publication);
            return true;
        }
        return false;
    }


    static function save($publication)
    {
        @$id = $publication[self::$primary];

        if (empty($id)) {
            return false;
        }

        $res = save($publication, self::$table);

        if (empty($res)) {
            return false;
        }

        if (!empty($publication['authors'])) {
            self::saveAuthors($id, $publication['authors']);
        }

        if (!empty($publication['refs'])) {
            self::saveRefs($id, $publication['refs']);
        }

        return $publication;
    }


    static function saveAuthors($id, $authors)
    {
        $rel_table = 'publications_to_authors';

        foreach ($authors as $author) {
            $data = [
                'publicationid' => $id,
                'authorid' => $author
            ];
            saveRelation($data, $rel_table);
        }
    }

    static function saveRefs($id, $refs)
    {
        $rel_table = 'publications_to_publications';

        foreach ($refs as $ref) {
            $data = [
                'origin_publ_id' => $id,
                'end_publ_id' => $ref
            ];
            saveRelation($data, $rel_table);
        }
    }


    static function checkAuthors($authors)
    {
        if (empty($authors)) {
            return false;
        }

        foreach ($authors as $author) {
            Author::parse($author);
        }

        return $author;
    }

    static function checkRefs($refs)
    {
        if (empty($refs)) {
            return false;
        }

        foreach ($refs as $item) {
            self::parse($item);
        }
        return $refs;
    }

}


?>