<?php


class Elibrary
{
    var $login;
    var $password;

    function __construct($login = null, $password = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->base_url = 'https://elibrary.ru/';
    }


    function getHome()
    {
        $url = $this->base_url;
        return fetch($url);
    }


    function getPublicationsInOrganication($orgsid, $pagenum = 1)
    {
        $url = $this->base_url . 'org_items.asp';
        $data = ['orgsid' => $orgsid, 'pagenum' => $pagenum];

//        $url = $this->base_url . 'org_items.asp?' . http_build_query($data);

        $res = fetch($url, $data);

        return $res;
    }

}


?>