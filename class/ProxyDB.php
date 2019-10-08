<?php


class ProxyDB
{
    function __construct()
    {
    }

    static function getList()
    {
        global $proxy_list;

        $proxy_list = array();
        $url = 'http://localhost/proxy_list.txt/';

        $data = fetchNoProxy($url);

        $lines = preg_split('/\n/m', $data);

        $proxy_info = array();

        foreach ($lines as $line) {
            $split = explode('@', $line);
            $proxy = $split[1];
            $auth = $split[0];
            $proxy_info['full'] = $proxy;
            $proxy_info['auth'] = $auth;
            $proxy_list[] = $proxy_info;
        }

        return $proxy_list;
    }

    static function ban($proxy)
    {
        global $db;
        $table = 'bad_proxy';
        $data['proxy'] = $proxy;

        $query = 'INSERT INTO ?n SET ?u';
        return $db->query($query, $table, $data);
    }

    static function isBanned($proxy)
    {
        global $db;
        $table = 'bad_proxy';
        $column = 'proxy';
        $query = 'SELECT ?n FROM ?n WHERE ?n=?s';
        return $db->getOne($query, $column, $table, $column, $proxy);
    }

    static function clearBanList()
    {
        global $db;
        $table = 'bad_proxy';
        $query = 'DELETE FROM ?n WHERE 1';

        return $db->query($query, $table);
    }


    static function getGoogle()
    {
        $url = 'https://google.ru/';
        return fetch($url);
    }

    static function get2ip()
    {
        $url = 'https://2ip.ru/';
        return fetch($url);
    }

    static function getElib()
    {
        $url = 'https://elibrary.ru/org_about.asp?orgsid=4831';
        $data = fetch($url);
        if (empty($data)) {
            return false;
        }

        $is_ban = preg_match('/нарушения/m', $data);

        if ($is_ban) {
            return false;
        } else {
            return $data;
        }

    }


    static function updateAgent()
    {
        global $cookiePath1, $proxy_list;
        usleep(12000000, 35000000);
        $proxy_list = self::getList();
        self::update();
        @unlink($cookiePath1);
        return true;
    }

    static function update()
    {
        global $proxy_list, $def_proxy_info;
        if (empty($proxy_list)) {
            $proxy_list = self::getList();
        }
        $index = rand(0, count($proxy_list));
        $def_proxy_info = $proxy_list[$index];
        return $def_proxy_info;
    }

}


?>