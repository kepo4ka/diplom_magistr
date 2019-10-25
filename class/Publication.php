<?php


class Publication
{
    static $table = 'publications';
    static $primary = 'id';

    static function get($id, $full = false)
    {
        $publication = getById(self::$table, $id);

        if (!empty($publication['title'])) {
            $publication['refs'] = array();
            $publication['authors'] = array();
            $publication['organisations'] = array();
            $publication['keywords'] = array();
            $publication['keywords_full'] = array();

            if ($full) {
                $publication['refs'] = self::getRefs($id);
                $publication['authors'] = self::getAuthors($id);
                $publication['organisations'] = self::getOrganisations($id);
                $publication['keywords'] = self::getKeywords($id);
            }
        } else {
            $publication = ElibraryCurl::getPublication($id);
            self::save($publication);
        }

        if (!empty($publication['authors'])) {
            foreach ($publication['authors'] as $author_id) {
                $author = Author::get($author_id, true);

                if (!empty($author['organisations'])) {
                    foreach ($author['organisations'] as $organisation_id) {
                        Organisation::savePublication($organisation_id, $id);
                    }
                }
            }
        }

        if (!empty($publication['keywords_full'])) {
            foreach ($publication['keywords_full'] as $keyword) {
                Keyword::save($keyword);
            }
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

    static function getKeywords($id)
    {
        $table = 'publications_to_keywords';
        $needed = 'keywordid';
        $column = 'publicationid';
        return getOneToMany($table, $column, $id, $needed);
    }

    static function parse($id)
    {
        $publication = getById(self::$table, $id);
        if (empty($publication['title'])) {
            $publication = ElibraryCurl::getPublication($id);
            $res = self::save($publication);
            if (empty($res)) {
                return false;
            }
        } else {
            if (!empty($publication['authors'])) {
                foreach ($publication['authors'] as $author_id) {
                    $author = Author::get($author_id, true);

                    if (!empty($author['organisations'])) {
                        foreach ($author['organisations'] as $organisation_id) {
                            Organisation::savePublication($organisation_id, $id);
                        }
                    }

                    if (!empty($publication['keywords_full'])) {
                        foreach ($publication['keywords_full'] as $keyword) {
                            Keyword::save($keyword);
                        }
                    }
                }
            }
        }
        return true;
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

        if (!empty($publication['keywords'])) {
            self::saveKeywords($id, $publication['keywords']);
        }

        return $publication;
    }


    static function saveAuthors($id, $authors)
    {
        if (empty($authors)) {
            return false;
        }

        foreach ($authors as $author) {
            self::saveAuthor($id, $author);
        }
        return true;
    }

    static function saveAuthor($id, $author)
    {
        $rel_table = 'publications_to_authors';

        if (empty($author)) {
            return false;
        }

//        Author::get($author);

        $data = [
            'publicationid' => $id,
            'authorid' => $author
        ];


        return saveRelation($data, $rel_table);
    }


    static function saveRefs($id, $refs)
    {
        if (empty($refs)) {
            return false;
        }

        foreach ($refs as $ref) {
            self::saveRef($id, $ref);
        }
        return true;
    }

    static function saveRef($id, $ref)
    {
        $rel_table = 'publications_to_publications';

        if (empty($ref)) {
            return false;
        }

//        Publication::get($ref);

        $data = [
            'origin_publ_id' => $id,
            'end_publ_id' => $ref
        ];

        return saveRelation($data, $rel_table);
    }

    static function saveKeywords($id, $keywords)
    {
        if (empty($keywords)) {
            return false;
        }

        foreach ($keywords as $keyword) {
            self::saveKeyword($id, $keyword);
        }
        return true;
    }

    static function saveKeyword($id, $keyword)
    {
        $rel_table = 'publications_to_keywords';

        if (empty($keyword)) {
            return false;
        }

        $data = [
            'publicationid' => $id,
            'keywordid' => $keyword
        ];
        return saveRelation($data, $rel_table);
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