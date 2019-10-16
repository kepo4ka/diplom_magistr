<?php


class ElibraryCurl
{
    static function getHome()
    {
        global $elibrary_config;

        $url = $elibrary_config['base_url'];

        $parsed_html = fetchProxy($url);

        if (empty($parsed_html)) {
            return false;
        }
        return $parsed_html;

//        return preg_match('/eLIBRARY.RU - НАУЧНАЯ ЭЛЕКТРОННАЯ БИБЛИОТЕКА/m', $parsed_html);
    }


    static function login()
    {
        global $elibrary_config;

        $url = $elibrary_config['base_url'] . '/' . 'start_session.asp';

        $data['params'] = [
            'login' => $elibrary_config['login'],
            'password' => $elibrary_config['password']
        ];


        $parsed_html = fetchProxy($url, $data);

        if (!empty($parsed_html)) {
            $elibrary_config['authed'] = true;
        }

//        echoVarDumpPre($parsed_html);

        return $parsed_html;
    }

    static function checkLogin($html = false)
    {
        global $elibrary_config;
        if (!$html) {
            $html = self::getHome();
        }

        $reg = $elibrary_config['login'];
        return preg_match("/$reg/", $html);
    }

    static function logOut()
    {
        clearCookie();
    }

    static function getOrganisationInfo($id = 4851)
    {
        global $elibrary_config;
        $organisation = array();
        $organisation['id'] = $id;
        $organisation['name'] = '';
        $organisation['name_en'] = '';
        $organisation['type'] = '';
        $organisation['city'] = '';
        $organisation['country'] = '';
        $organisation['region'] = '';

        $url = $elibrary_config['base_url'] . '/' . 'org_about.asp';
        $data['params'] = ['orgsid' => $id];
        $parsed_html = fetchProxy($url, $data);

        $data = str_get_html($parsed_html);

        if (empty($data)) {
            return false;
        }

        $res_html = $data->find('table[width=580]');

        foreach ($res_html as $table) {
            $str = $table->plaintext;
            if (empty($organisation['name'])) {
                $finded = checkRegular('/Полное название\s{2,}(.+?)\s{2,}/m', $str);
                $organisation['name'] = $finded;
            }

            if (empty($organisation['name_en'])) {
                $finded = checkRegular('/Название на англ\.\s{2,}(.+?)\s{2,}/m', $str);
                $organisation['name_en'] = $finded;
            }

            if (empty($organisation['type'])) {
                $finded = checkRegular('/Тип\s{2,}(.+?)\s{2,}/m', $str);
                $organisation['type'] = $finded;
            }

            if (empty($organisation['country'])) {
                $finded = checkRegular('/Страна\s{2,}(.+?)\s{2,}/m', $str);
                $organisation['country'] = $finded;
            }

            if (empty($organisation['city'])) {
                $finded = checkRegular('/Город\s{2,}(.+?)\s{2,}/m', $str);
                $organisation['city'] = $finded;
            }

            if (empty($organisation['region'])) {
                $finded = checkRegular('/Регион\s{2,}(.+?)\s{2,}/m', $str);
                $organisation['region'] = $finded;
            }

            if (checkArrayFilled($organisation)) {
                break;
            }
        }

        $data->clear();

        if (empty($organisation['name'])) {
            return false;
        }

        return $organisation;
    }


    static function getOrgPublications($id, $page = 1)
    {
        global $elibrary_config;
        $publications = array();

        $url = $elibrary_config['base_url'] . '/' . 'org_items.asp';
        $data['params'] = ['orgsid' => $id,
            'show_option' => '0',
            'pagenum' => $page];
        $parsed_html = fetchProxy($url, $data);

        $data = str_get_html($parsed_html);

        if (empty($data)) {
            return $publications;
        }

        $res = $data->find('form[name=results]');
        $matches = array();
        preg_match_all('/id="arw(\d+)"/m', $res[0], $matches);

        if (!empty($matches[1])) {
            $publications = $matches[1];
        }

        $data->clear();
        return $publications;
    }


    static function getKeywordInfo($id = 2324764)
    {
        global $elibrary_config;
        $keyword = array();
        $keyword['id'] = $id;
        $keyword['name'] = '';


        $url = $elibrary_config['base_url'] . '/' . 'keyword_items.asp';
        $data['params'] = ['id' => $id];
        $parsed_html = fetchProxy($url, $data);

        $data = str_get_html($parsed_html);

        if (empty($data)) {
            return false;
        }

        $keyword['name'] = checkRegular('/eLIBRARY.RU - Публикации с ключевым словом "(.+?)"/m', $parsed_html);


        $data->clear();

        if (empty($keyword['name'])) {
            return false;
        }

        return $keyword;
    }


    static function checkIpBan($html)
    {
        if (empty($html)) {
            return false;
        }

        return preg_match('/нарушения/m', $html);

    }

    static function getPublication($id = 35287282)
    {
        global $elibrary_config;
        $publication = array();
        $publication['id'] = $id;
        $publication['title'] = '';
        $publication['type'] = '';
        $publication['year'] = '';
        $publication['language'] = '';
        $publication['authors'] = array();
        $publication['refs'] = array();
        $publication['keywords'] = array();
        $publication['keywords_full'] = array();

        if (!$elibrary_config['authed']) {
            if (!self::checkLogin(self::login())) {
                $elibrary_config['authed'] = false;
                arrayLog($elibrary_config, 'Не удалось авторизоваться', 'error');
            }
        }


        $url = $elibrary_config['base_url'] . '/' . 'item.asp';
        $data['params'] = ['id' => $id];
        $parsed_html = fetchProxy($url, $data);


        $data = str_get_html($parsed_html);

        if (empty($parsed_html) || empty($data)) {
            return false;
        }

        $title = $data->find('.bigtext', 0)->plaintext;
        $publication['title'] = $title;


        $res = $data->find('table td[width=574][align=center]');
        foreach ($res as $d) {
            $k = false;
            $result = checkRegular('/Тип:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d);
            if (!empty($result)) {
                $publication['type'] = $result;
            }

            $result = checkRegular('/Язык:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d);
            if (!empty($result)) {
                $publication['language'] = $result;
            }

            $result = checkRegular('/Год(.+?)?:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d, 2);
            if (!empty($result)) {
                $publication['year'] = $result;
            }
        }

        $authors = $data->find('a[title=Список публикаций этого автора]');
        foreach ($authors as $author) {
            $author_id = checkRegular('/author_items.asp\?authorid=(\d+)/m', $author->href);
            $publication['authors'][] = $author_id;
        }
        $publication['authors'] = array_unique($publication['authors']);

        $matches = array();
        preg_match_all('/<a href=\"keyword_items\.asp\?id=(\d+)\">(.+?)<\/a>/m', $parsed_html, $matches);

        if (!empty($matches[1])) {
            $publication['keywords'] = $matches[1];

            foreach ($matches[1] as $key => $value) {
                if (!empty($matches[1][$key]) && !empty($matches[2][$key])) {
                    $publication['keywords_full'][] = ['id' => $matches[1][$key], 'name' => $matches[2][$key]];
                }
            }
        }


        $refs = $data->find('a[title=Перейти на описание цитируемой публикации]');
        foreach ($refs as $ref) {
            $ref_id = checkRegular('/item.asp\?id=(\d+)/m', $ref->href);
            $publication['refs'][] = $ref_id;
        }

        $load_more = $data->find('#show_reflist');
        if (!empty($load_more)) {
            $more_refs = self::getMorePublicationRefs($id);
            $publication['refs'] = array_unique(array_merge($publication['refs'], $more_refs));
        }


        $data->clear();

        if (empty($publication['title'])) {
            return false;
        }

        return $publication;
    }

    static function getMorePublicationRefs($id)
    {
        global $elibrary_config;
        $ref_publications = array();

        if (!$elibrary_config['authed']) {
            if (!self::checkLogin(self::login())) {
                $elibrary_config['authed'] = false;
                arrayLog($elibrary_config, 'Не удалось авторизоваться', 'error');
            }
        }

        $url = $elibrary_config['base_url'] . '/' . 'get_item_refs.asp';
        $data['params'] = ['id' => $id,
            'rand' => jsRandom()];
        $parsed_html = fetchProxy($url, $data);

        $data = str_get_html($parsed_html);

        if (empty($parsed_html) || empty($data)) {
            return array();
        }


        $refs = $data->find('a[title=Перейти на описание цитируемой публикации]');

        foreach ($refs as $ref) {
            $ref_id = checkRegular('/item.asp\?id=(\d+)/m', $ref->href);
            $ref_publications[] = $ref_id;
        }

        $data->clear();
        return $ref_publications;
    }

    static function getAuthorInfo($id = 781679)
    {
        global $elibrary_config;
        $author = array();
        $author['id'] = $id;
        $author['fio'] = '';
        $author['post'] = '';
        $author['articles_count'] = 0;
        $author['citation_count'] = 0;
        $author['hirsch_index'] = 0;
        $author['organisations'] = 0;


        $url = $elibrary_config['base_url'] . '/' . 'author_profile.asp';
        $data['params'] = ['authorid' => $id];
        $parsed_html = fetchProxy($url, $data);

        $data = str_get_html($parsed_html);

        if (empty($data)) {
            return false;
        }

        $body = $data->find('body', 0)->plaintext;

        $res = $data->find('title', 0)->innertext;

        $author['fio'] = checkRegular('/eLIBRARY.RU - (.+?) - Анализ публикационной активности/m', $res);

        $regex = '/<br><a href=\"org_about\.asp\?orgsid=\d+\">(.+?)<\/a>, (.+) \((.+?)\)\s+<br>/m';
        $author['post'] = checkRegular($regex, $parsed_html, 2);

        $res = $data->find('a[title=Полный список публикаций автора на портале elibrary.ru]', 0);
        if ($res) {
            $author['articles_count'] = $res->plaintext;
        }

        $res = $data->find('a[title=Список цитирований публикаций автора на elibrary.ru]', 0);
        if ($res) {
            $author['citation_count'] = $res->plaintext;
        }

        $author['hirsch_index'] = checkRegular('/Индекс Хирша по всем публикациям на elibrary.ru(\d+)/m', $body);


        $matches = array();
        preg_match_all('/org_about\.asp\?orgsid=(\d+)/m', $parsed_html, $matches);

        if (!empty($matches[1])) {
            $author['organisations'] = array_unique($matches[1]);
        }

        $data->clear();

        if (empty($author['fio'])) {
            return false;
        }

        return $author;
    }

    static function getAuthorPublications($id = 781679, $pagenum = 1)
    {
        global $elibrary_config;

        $publications = array();

        $url = $elibrary_config['base_url'] . '/' . 'author_items.asp';
        $data['params'] = ['authorid' => $id,
            'pagenum' => $pagenum];
        $parsed_html = fetchProxy($url, $data);

        $data = str_get_html($parsed_html);

        if (empty($data)) {
            return false;
        }

        $res = $data->find('form[name=results]');
        $matches = array();
        preg_match_all('/id="arw(\d+)"/m', $res[0], $matches);

        if (!empty($matches[1])) {
            $publications = $matches[1];
        }

        $data->clear();
        return $publications;
    }

    static function getKeywordPublications($id = 2324764, $pagenum = 1)
    {
        global $elibrary_config;

        $publications = array();

        $url = $elibrary_config['base_url'] . '/' . 'keyword_items.asp';
        $data['params'] = ['id' => $id,
            'pagenum' => $pagenum];
        $parsed_html = fetchProxy($url, $data);

        $data = str_get_html($parsed_html);

        if (empty($data)) {
            return false;
        }

        $res = $data->find('form[name=results]');
        $matches = array();
        preg_match_all('/id="arw(\d+)"/m', $res[0], $matches);

        if (!empty($matches[1])) {
            $publications = $matches[1];
        }

        $data->clear();
        return $publications;
    }

}


?>