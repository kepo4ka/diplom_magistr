<?php
$curr_try = 0;

class ProxyDB
{

    function __construct()
    {
    }

    static function getList()
    {
        global $proxy_list;

        $proxy_list = array();
        $url = 'http://localhost/proxy_list.txt';

        $data = fetchNoProxy($url);

        $lines = preg_split('/\n/m', trim($data));

        $proxy_info = array();

        foreach ($lines as $line) {
            $split = explode('@', $line);
            $proxy = trim($split[1]);
            $auth = trim($split[0]);
            $proxy_info['full'] = $proxy;
            $proxy_info['auth'] = $auth;
            $proxy_info['type'] = CURLPROXY_HTTP;
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
        $url = 'https://elibrary.ru';
        $data = fetchProxy($url);
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


    static function deleteProxy($proxy)
    {
        global $proxy_list;

        $del_proxy = $proxy;

        if (!empty($proxy['full'])) {
            $del_proxy = $proxy['full'];
        }

        for ($i = 0; $i < count($proxy_list); $i++) {
            if ($proxy_list[$i]['full'] == $del_proxy) {
                array_splice($proxy_list, $i, 1);
                return true;
            }
        }
        return false;
    }

    static function update()
    {
        global $proxy_list, $def_proxy_info, $cookiePath1, $current_user_agent, $user_agents;


        if (empty($proxy_list)) {
            arrayLog('STOP WORK', 'Proxy List Empty', 'error');
            exit;
        }

        $index = rand(0, count($proxy_list) - 1);
        $def_proxy_info = $proxy_list[$index];
        $index = rand(0, count($user_agents) - 1);
        $current_user_agent = $user_agents[$index];
        @unlink($cookiePath1);

        arrayLog('New Proxy: ' . $def_proxy_info['full'], 'Change Proxy');

        return true;
    }

}


?>