<?php


class Organisation
{
    static $table = 'organisations';
    static $primary = 'id';



    static function get($id, $full = false)
    {
        $organisation = getById(self::$table, $id);


        if (!empty($organisation)) {
            $organisation['publications'] = array();
            $organisation['authors'] = array();

            if ($full) {
                $organisation['publications'] = self::getPublications($id);
                $organisation['authors'] = self::getAuthors($id);
            }
        } else {
            $organisation = ElibraryCurl::getOrganisationInfo($id);
            self::save($organisation);
        }

        return $organisation;
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
        $table = 'authors_to_organisations';
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

        $rel_table = 'publications_to_organisations';

        $data = [
            'orgsid' => $id,
            'publicationid' => $publication
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
        $publications = ElibraryCurl::getOrgPublications($id, $pagenum);
        self::savePublications($id, $publications);
//        self::checkPublications($publications);
        return $publications;
    }



    static function parseOrganisationPublications()
    {
        global $org_id, $pagenum, $query_count, $start;
        $organisation = Organisation::get($org_id, true);
        while (true) {
            $org_publications = Organisation::parsePublicationsPart($org_id, $pagenum);

            arrayLog('', 'Полученные статьи организации на странице ' . $pagenum);

            $pagenum++;

            if (empty($org_publications)) {
                break;
            }

            if (!empty($pagenum_end) && $pagenum > $pagenum_end) {
                break;
            }

            foreach ($org_publications as $org_publication) {
                $publication = Publication::get($org_publication, true);

                if (empty($publication['title'])) {
                    continue;
                }

                arrayLog($publication['title'], 'Работа со статьей ' . $publication['id']);

                if (!empty($publication['authors'])) {
                    foreach ($publication['authors'] as $pub_author) {
                        $author_pagenum = 1;
                        while (!empty(Author::parsePublicationsPart($pub_author, $author_pagenum))) {
                            $author_pagenum++;
                        }
                    }
                }

                if (!empty($publication['keywords_full'])) {
                    foreach ($publication['keywords_full'] as $keyword) {
                        Keyword::save($keyword);
                    }
                }

                if (!empty($publication['refs'])) {
                    foreach ($publication['refs'] as $pub_ref) {
                        $ref = Publication::get($pub_ref, true);
                    }
                }
            }

        }


        arrayLog($query_count, 'Количество запросов');
        arrayLog('Информация об организация <strong>' . $organisation['name'] . '</strong> Добавлена', 'Информация об организации');
        arrayLog('Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.', 'Время выполнения скрипта');

        exit;

    }



}


?>