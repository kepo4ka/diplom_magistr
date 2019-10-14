<?php


class Keyword
{
    static $table = 'keywords';
    static $primary = 'id';


    static function get($id, $full = false)
    {
        $keyword = getById(self::$table, $id);


        if (!empty($keyword['name'])) {
            $keyword['publications'] = array();

            if ($full) {
                $keyword['publications'] = self::getPublications($id);
            }
        } else {
            $keyword = ElibraryCurl::getKeywordInfo($id);
            self::save($keyword);
        }
        return $keyword;
    }

    static function parse($id)
    {
        $keyword = getById(self::$table, $id);
        if (empty($keyword)) {
            $keyword = ElibraryCurl::getKeywordInfo($id);
            self::save($keyword);
            return true;
        }
        return false;
    }


    static function getPublications($id)
    {
        $table = 'publications_to_keywords';
        $needed = 'publicationid';
        $column = 'keyword';
        return getOneToMany($table, $column, $id, $needed);
    }


    static function save($keyword)
    {
        @$id = $keyword[self::$primary];

        if (empty($id)) {
            return false;
        }

        $res = save($keyword, self::$table);

        if (empty($res)) {
            return false;
        }

        if (!empty($keyword['publications'])) {
            self::savePublications($id, $keyword['publications']);
        }

        return $keyword;
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
        if (empty($publication)) {
            return false;
        }

//        Publication::get($publication);

        $rel_table = 'publications_to_keywords';

        $data = [
            'publicationid' => $publication,
            'keywordid' => $id
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
//        self::savePublications($id, $publications);
//        self::checkPublications($publications);
        return $publications;
    }

}


?>