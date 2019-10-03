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

        $res = $data->find('table td[width=574][align=center]');

        foreach ($res as $d) {
            $k = false;
            $result = checkRegular('/Тип:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d);
            if (!empty($result)) {
                $publication['type'] = $result;
                $k = true;
            }

            $result = checkRegular('/Язык:&nbsp;<font color=#00008f>(.+?)<\/font>/m', $d);
            if (!empty($result)) {
                $publication['language'] = $result;
                $k = true;
            }
            if ($k) {
                break;
            }
        }


        $title = $data->find('.bigtext', 0)->plaintext;

        $publication['title'] = @$title;

        $data->clear();
        return $publication;

    }

}


?>