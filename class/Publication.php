<?php


class Publication
{
    static $primary = 'id';

    static function get($id, $parse = true)
    {
        $publication = ElibraryDB::getPublication($id);

        if (!empty($publication)) {
            $publication['refs'] = ElibraryDB::getPublicationRefs($id);
            $publication['authors'] = ElibraryDB::getPublicationAuthors($id);
            $publication['parsed'] = false;
            return $publication;
        }


        if ($parse) {
            $publication = ElibraryCurl::getPublication($id);
            echoVarDumpPre($publication);
            $publication['parsed'] = true;
            self::save($publication);
        }

        return $publication;
    }

    static function save($publication)
    {
        @$id = $publication[self::$primary];

        if (empty($id)) {
            return false;
        }

        $res = ElibraryDB::savePublication($publication);

        if (empty($res)) {
            return false;
        }

        foreach ($publication['authors'] as $author) {
            ElibraryDB::saveRelationAuthorPublication($publication['id'], $author);
        }

        foreach ($publication['refs'] as $ref_id) {
            ElibraryDB::saveRelationPublicationPublication($ref_id, $publication['id']);
        }
        return $publication;
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