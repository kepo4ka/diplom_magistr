<?php


class Author
{
    static $table = 'authors';
    static $primary = 'id';


    static function get($id, $parse = true)
    {
        $author = getById(self::$table, $id);

        if (empty($author)) {

            if ($parse) {
                $author = ElibraryCurl::getAuthorInfo($id);
                $res = ElibraryDB::saveAuthor($author);

                if (empty($res)) {
                    return false;
                }
            }
        }




    }

    static function parse($id)
    {
        $publication = ElibraryDB::getPublication($id);

        if (!empty($publication)) {
            return $publication;
        }
        $publication = ElibraryCurl::getPublication($id);

        if (!empty($publication)) {
            $res = ElibraryDB::savePublication($publication);
            if (empty($res)) {
                return false;
            }
        }
        return $publication;
    }


    static function checkRefs($publication)
    {
        if (empty($publication['refs'])) {
            return false;
        }

        $refs = $publication['refs'];

        foreach ($refs as $ref) {
            self::parse($ref);
        }

        return $refs;
    }


    static function checkAuthors($publication)
    {
        if (empty($publication['authors'])) {
            return false;
        }

        $refs = $publication['authors'];

        foreach ($refs as $ref) {
            self::parse($ref);
        }

        return true;
    }


}


?>