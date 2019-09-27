<?php


class Elibrary
{
    var $login;
    var $password;
    var $api_url;
    var $wrapAPIKey;
    var $stateToken;
    var $login_check_string;

    function __construct()
    {
        $this->base_url = 'https://elibrary.ru/';
        $this->api_url = 'https://wrapapi.com/use/kepo4ka/test/';
        $this->login = 'larisa2566';
        $this->password = 'larisa7502635';
        $this->wrapAPIKey = 'gkCzQ9pXQ5REWTv9KDPFDqdbAtcMZZ3K';
        $this->stateToken = '';
        $this->login_check_string = 'СЕССИЯ';
    }

    function buildApiUrl($part, $v = false)
    {
        $version = 'latest';
        if ($v) {
            $version = $v;
        }

        $url = $this->api_url . $part . '/' . $version;
        return $url;
    }

    function fetch($url, $data)
    {
        $result = fetch($url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    function getHome()
    {
        $url = $this->base_url;
        return fetch($url);
    }

    function login()
    {
        $url = $this->buildApiUrl('authing');

        $data['params'] = [
            'login' => $this->login,
            'password' => $this->password,
            'wrapAPIKey' => $this->wrapAPIKey
        ];

        $result = $this->fetch($url, $data);

        $this->stateToken = $result['stateToken'];

        return !empty($result['data']['output']) && $result['data']['output'] == $this->login_check_string;
    }

    function getPublication($id = 36873446)
    {
        if (empty($this->stateToken)) {
            $this->login();
        }

        $url = $this->buildApiUrl('publication');
        $data['params'] = [
            'id' => $id,
            'stateToken' => $this->stateToken,
            'wrapAPIKey' => $this->wrapAPIKey
        ];

        $result = $this->fetch($url, $data);
        return $result['data'];
    }


    function getPublications($orgsid, $pagenum = 1)
    {
        try {
            $url = $this->buildApiUrl('publications_in_organisations');

            $data['params'] = [
                'orgsid' => $orgsid,
                'pagenum' => $pagenum,
                'wrapAPIKey' => $this->wrapAPIKey
            ];

            $result = $this->fetch($url, $data);

            $result = $result['data']['output'];
        } catch (Exception $exception) {
            $result = null;
        }
        return $result;
    }

    function getOrganisationInfo($orgsid)
    {
        $url = $this->buildApiUrl('organisation');
        $data['params'] = [
            'orgsid' => $orgsid,
            'wrapAPIKey' => $this->wrapAPIKey
        ];
        $result = $this->fetch($url, $data);

        $organisation_info = array();
        $organisation_info['id'] = $orgsid;
        $organisation_info['name'] = '';
        $organisation_info['name_en'] = '';
        $organisation_info['city'] = '';
        $organisation_info['country'] = '';

        try {
            $result = $result['data']['output'];
            $result = trim(preg_replace('/\r\n/', ' ', $result));
            $result = trim(preg_replace('/[ ]+/', ' ', $result));


            preg_match('/Полное название (.+?)" Название/', $result, $matches);
            $organisation_info['name'] = $matches[1];

            preg_match('/Название на англ\. (.+?) Сокращение/', $result, $matches);
            $organisation_info['name_en'] = $matches[1];

            preg_match('/Страна (.+?) Регион/', $result, $matches);
            $organisation_info['country'] = $matches[1];

            preg_match('/Город (.+?) Город/', $result, $matches);
            $organisation_info['city'] = $matches[1];

        } catch (Exception $exception) {
            $result = null;
        }

        return $organisation_info;
    }


//    function getPublicationsInOrganication($orgsid, $pagenum = 1)
//    {
//        $url = $this->base_url . 'org_items.asp';
//        $data = ['orgsid' => $orgsid, 'pagenum' => $pagenum];
//
////        $url = $this->base_url . 'org_items.asp?' . http_build_query($data);
//
//        $res = fetch($url, $data, true);
//
//        return $res;
//    }

}


?>