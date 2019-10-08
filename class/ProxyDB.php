<?php


class ProxyDB
{
    function __construct()
    {
    }

    static function getList()
    {
        global $proxy_list;
        $url = 'http://127.0.0.1:1000/';

        $data = fetchNoProxy($url);

        $proxy_list = preg_split('/\n/m', $data);

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

    static function getElibOrg($proxy = '')
    {
        global $def_proxy_info;
        if ($proxy) {
            $def_proxy_info = $proxy;
        }

        $org = rand(1, 7000);
        $url = 'https://elibrary.ru/org_about.asp?orgsid=' . $org;
        $data = fetch($url);
        return $data;

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


    function check()
    {
        if (empty(self::getGoogle()) || empty(self::get2ip())) {
            return false;
        } else {
            return true;
        }
    }

    static function updateAgent()
    {
        global $cookiePath1, $def_proxy_info, $proxy_list;

        $proxy_list = self::getList();
        self::clearBanList();
        $elib_res = self::update();

        if (empty($elib_res)) {
            echoVarDumpPre('errror');
            return false;
        }

        @unlink($cookiePath1);
        return true;
    }

    static function update()
    {
        global $proxy_list, $def_proxy_info;
        if (empty($proxy_list)) {
            $proxy_list = self::getList();

            if (empty($proxy_list)) {
                return false;
            }
        }


        $try_count = 0;

        while (true) {
            if ($try_count > 100) {
//                $proxy_list = ProxyDB::getList();
                break;
            }

            $index = rand(0, count($proxy_list) - 1);

            $def_proxy_info = trim($proxy_list[$index]);

            if (preg_match('/[^\d\.:]+/m', $def_proxy_info)) {
                echoVarDumpPre($proxy_list);
            }

            if (self::isBanned($def_proxy_info)) {
                $try_count++;
                continue;
            }

            $google = self::getGoogle();

            if (empty($google)) {
                self::ban($def_proxy_info);
                $try_count++;
                continue;
            }
            return true;
        }

        echoVarDumpPre('eroor');
    }

}


?>