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

    function getPublication($id = 35287282)
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

    function getPublicationAjaxRefs($id = 35287282)
    {
        if (empty($this->stateToken)) {
            $this->login();
        }

        $url = $this->buildApiUrl('publication_refs_ajax');
        $data['params'] = [
            'id' => $id,
            'stateToken' => $this->stateToken,
            'wrapAPIKey' => $this->wrapAPIKey
        ];

        $result = $this->fetch($url, $data);
        return $result['data'];
    }


    function getPublications($orgsid = 5051, $pagenum = 1)
    {
        try {
            $url = $this->buildApiUrl('publications_in_organisation');

            $data['params'] = [
                'orgsid' => $orgsid,
                'pagenum' => $pagenum,
                'wrapAPIKey' => $this->wrapAPIKey
            ];

            $result = $this->fetch($url, $data);
            if (!empty($result['data']['publications'])) {
                return $result['data']['publications'];
            }

        } catch (Exception $exception) {
            $result = null;
        }
        return $result;
    }

    function getOrganisationInfo($orgsid=5051)
    {
        $url = $this->buildApiUrl('organisation');
        $data['params'] = [
            'orgsid' => $orgsid,
            'wrapAPIKey' => $this->wrapAPIKey
        ];
        $result = $this->fetch($url, $data);

        return $result;
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