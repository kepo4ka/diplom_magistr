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
        global $elibrary_config, $query_count;
        $query_count = 0;


        $url = $elibrary_config['base_url'] . '/' . 'start_session.asp';

        $data['params'] = [
            'login' => $elibrary_config['login'],
            'password' => $elibrary_config['password']
        ];

        $parsed_html = fetchProxy($url, $data);

//        echoVarDumpPre($parsed_html);

        return $parsed_html;
    }

    static function checkLogin($html = false)
    {
        global $elibrary_config;

        if (!$html) {
            $html = self::getHome();
        }
        $res = preg_match("/project_user_office/m", $html);

        if (!$res) {
            $elibrary_config['authed'] = false;
            return false;
        }
        return true;
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
                if (!checkRegular('/Телефон/', $finded, 0)) {
                    $organisation['type'] = $finded;
                }
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
                if (!checkRegular('/Город/', $finded, 0)) {
                    $organisation['region'] = $finded;
                }
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

        if (empty($res[0])) {
            $data->clear();
            return $publications;
        }

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

        return preg_match('/Доступ к сайту eLIBRARY\.RU для IP/m', $html) || preg_match('/Из-за нарушения правил пользования сайтом eLIBRARY/m', $html);
    }

    static function getPublication($id = 35287282, $auth = true)
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
        $publication['publisher'] = '';
        $publication['rubric'] = '';
        $publication['in_rinc'] = '';
        $publication['in_rinc_ker'] = '';
        $publication['cit_in_rinc'] = 0;
        $publication['cit_in_rinc_ker'] = 0;
        $publication['impact_factor'] = 0;
        $publication['norm_cit'] = 0;

        if (!$elibrary_config['authed'] && $auth) {
            $login = self::login();

            if (!self::checkLogin($login)) {
                $find_login = preg_match('/' . $elibrary_config['login'] . '/m', $login);
                arrayLog(array($elibrary_config, $find_login), 'Не удалось авторизоваться', 'error');
            } else {
                $elibrary_config['authed'] = true;
            }
        }


        $url = $elibrary_config['base_url'] . '/' . 'item.asp';
        $data['params'] = ['id' => $id];
        $parsed_html = fetchProxy($url, $data);


        $data = str_get_html($parsed_html);

        if (empty($parsed_html) || empty($data)) {
            return false;
        }
        $text = preg_replace('/&nbsp;/m', ' ', $data->plaintext);


        $title_selector = $data->find('.bigtext', 0);
        $title = '';

        if (!empty($title_selector)) {
            $title = $title_selector->plaintext;
        }
        $publication['title'] = $title;


        $res = $data->find('table td[width=574][align=center]');
        foreach ($res as $d) {
            $k = false;
            $result = checkRegular('/Тип:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d);
            if (!empty($result) && empty($publication['type'])) {
                $publication['type'] = $result;
            }

            $result = checkRegular('/Язык:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d);
            if (!empty($result) && empty($publication['language'])) {
                $publication['language'] = $result;
            }

            $result = checkRegular('/Год(&nbsp;| )?(издания)?:&nbsp;<font color=#00008f>(\d+)<\/font>/m', $d, 3);
            if (!empty($result) && empty($publication['year'])) {
                $publication['year'] = $result;
            }
        }

        $res = checkRegular('/Входит в РИНЦ(.+?):(.+?)(да|нет)/m', $text, 3);
        $publication['in_rinc'] = trim($res);

        $res = checkRegular('/Входит в ядро РИНЦ(.+?):(.+?)(да|нет)/m', $text, 3);
        $publication['in_rinc_ker'] = trim($res);


        $res = checkRegular('/Цитирований в РИНЦ(.+?):(.+?)([\d,]+)/m', $text, 3);
        $publication['cit_in_rinc'] = (float)str_replace(',', '.', $res);


        $res = checkRegular('/Цитирований из ядра РИНЦ(.+?):(.+?)([\d,]+)/m', $text, 3);
        $publication['cit_in_rinc_ker'] = (float)str_replace(',', '.', $res);


        $res = checkRegular('/Норм\. цитируемость по направлению:(.+?)([\d,]+)/m', $text, 2);
        $publication['norm_cit'] = (float)str_replace(',', '.', $res);


        $authors = $data->find('a[title=Список публикаций этого автора]');
        foreach ($authors as $author) {
            $author_id = checkRegular('/author_items.asp\?authorid=(\d+)/m', $author->href);
            $publication['authors'][] = $author_id;
        }
        $publication['authors'] = array_unique($publication['authors']);

        $selector = $data->find('a[title=Список публикаций этого издательства]', 0);
        if (!empty($selector)) {
            $publication['publisher'] = $selector->plaintext;
        }
        if (empty($publication['publisher'])) {
            $selector = $data->find('a[title=Информация об издательстве]', 0);
            if (!empty($selector)) {
                $publication['publisher'] = $selector->plaintext;
            }
        }
        if (empty($publication['publisher'])) {
            $selector = $data->find('a[title=Список журналов этого издательства]', 0);
            if (!empty($selector)) {
                $publication['publisher'] = $selector->plaintext;
            }
        }

        $selector = $data->find('span#rubric_grnti', 0);
        if (!empty($selector)) {
            $rubric = $selector->plaintext;
            $publication['rubric'] = checkRegular('/нет/m', $rubric, 0) ? '' : $rubric;
        }
        if (checkRegular('/\//m', $publication['rubric'], 0)) {
            $publication['rubric'] = preg_replace('/ \/  /m', '.', $publication['rubric']);
            $publication['rubric'] = preg_replace('/ /m', '_', $publication['rubric']);
        }


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


        if ($auth) {
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
                arrayLog($elibrary_config, 'Не удалось авторизоваться 1', 'error');
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


    static function getAllOrganisations()
    {
        global $elibrary_config;
        $url = $elibrary_config['base_url'] . '/' . 'orgs.asp';

        $pagenum = 1;
        $parsed_count = 0;

        while (true) {

            if ($pagenum % 10 === 0) {
                $pause = rand(250, 320);
                arrayLog('', 'Парсинг Организаций, Пауза - ' . $pause . 'сек');
                sleep($pause);
            }

            arrayLog('', 'Парсинг Организаций, Страница - ' . $pagenum);

            $z['params'] = [
                'pagenum' => $pagenum,
                'orgname' => '',
                'town' => '',
                'regionid' => 0,
                'countryid' => '',
                'sortorder' => 0,
                'order' => 0,
            ];

            $parsed_html = fetchProxy($url, $z);

            $data = str_get_html($parsed_html);

            if (empty($data)) {
                return false;
            }

            $res = $data->find('form[name=results]');

            $matches = array();
            preg_match_all('/org_about\.asp\?orgsid=(\d+)/m', @$res[0], $matches);

            if (!empty($matches[1])) {
                $items = $matches[1];
            }

            if (empty($items)) {
                break;
            }
            $parsed_count += count($items);

            $data->clear();

            foreach ($items as $key => $item) {
                Organisation::get($item);
            }
        }
        return $parsed_count;
    }
}


?>