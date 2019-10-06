<?php


class ElibraryCurl
{
    function __construct()
    {
        global $elibrary_config;

        $this->base_url = $elibrary_config['base_url'];
        $this->login = $elibrary_config['login'];
        $this->password = $elibrary_config['password'];
    }


    function getHome()
    {
        $url = $this->base_url;
        return fetch($url);
    }

    function login()
    {
        $url = $this->base_url . '/' . 'start_session.asp';

        $data['params'] = [
            'login' => $this->login,
            'password' => $this->password
        ];
        $parsed_html = fetch($url, $data);
        return $parsed_html;
    }

    function checkLogin($html = false)
    {
        if (!$html) {
            $html = $this->getHome();
        }

        $reg = $this->login;
        return preg_match("/$reg/", $html);
    }

    function logOut()
    {
        clearCookie();
    }

    function getOrganisationInfo($id)
    {
        $ref_publications = array();

        if (!$this->checkLogin()) {
            $this->login();
        }

        $url = $this->base_url . '/' . 'get_item_refs.asp';
        $data['params'] = ['id' => $id,
            'rand' => jsRandom()];
        $parsed_html = fetch($url, $data);

        $data = str_get_html($parsed_html);

        $refs = $data->find('a[title=Перейти на описание цитируемой публикации]');

        foreach ($refs as $ref) {
            $ref_id = checkRegular('/item.asp\?id=(\d+)/m', $ref->href);

            $ref_publications[] = $ref_id;
        }

        return $ref_publications;
    }


    function getPublication($id = 35287282)
    {
        $publication = array();
        $publication['id'] = $id;
        $publication['title'] = '';
        $publication['type'] = '';
        $publication['year'] = '';
        $publication['language'] = '';
        $publication['authors'] = array();
        $publication['refs'] = array();

        if (!$this->checkLogin()) {
            $this->login();
        }

        $url = $this->base_url . '/' . 'item.asp';
        $data['params'] = ['id' => $id];
        $parsed_html = fetch($url, $data);

        $data = str_get_html($parsed_html);


        $title = $data->find('.bigtext', 0)->plaintext;
        $publication['title'] = @$title;


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

            $result = checkRegular('/Год:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d);
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

        $refs = $data->find('a[title=Перейти на описание цитируемой публикации]');
        foreach ($refs as $ref) {
            $ref_id = checkRegular('/item.asp\?id=(\d+)/m', $ref->href);
            $publication['refs'][] = $ref_id;
        }

        $load_more = $data->find('#show_reflist');
        if (!empty($load_more)) {
            $more_refs = $this->getMorePublicationRefs($id);
            $publication['refs'] = array_merge($publication['refs'], $more_refs);
        }
        $publication['refs'] = array_unique($publication['refs']);

        $data->clear();

        return $publication;
    }

    function getMorePublicationRefs($id)
    {
        $ref_publications = array();

        if (!$this->checkLogin()) {
            $this->login();
        }

        $url = $this->base_url . '/' . 'get_item_refs.asp';
        $data['params'] = ['id' => $id,
            'rand' => jsRandom()];
        $parsed_html = fetch($url, $data);

        $data = str_get_html($parsed_html);

        $refs = $data->find('a[title=Перейти на описание цитируемой публикации]');

        foreach ($refs as $ref) {
            $ref_id = checkRegular('/item.asp\?id=(\d+)/m', $ref->href);

            $ref_publications[] = $ref_id;
        }

        return $ref_publications;
    }



}


?>