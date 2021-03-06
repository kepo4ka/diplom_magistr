<?php
$curr_try = 0;

class ProxyDB
{

    function __construct()
    {
    }

    static function getList()
    {
        global $proxy_list, $project_url;

        $proxy_list = array();
        $url = $project_url . 'proxy_list.txt';



        $data = fetchNoProxy($url);

        if (preg_match('/[^\w:\s\n.@]+/', $data, $na)) {
            \Helper\Helper::echoVarDumpPre('Ошибка загрузка Списка Прокси');
            arrayLog('', 'Ошибка загрузка Списка Прокси. Сон 10 сек...', 'error');
            sleep(10);
            return self::getList();
        }

        $lines = preg_split('/\n/m', trim($data));

        $proxy_info = array();

        foreach ($lines as $line) {
            if (preg_match('/@/m', $line)) {
                $split = explode('@', $line);
                $proxy = trim($split[1]);
                $auth = trim($split[0]);
            } else {
                $proxy = trim($line);
                $auth = '';
            }
            $proxy_info['full'] = $proxy;
            $proxy_info['auth'] = $auth;
            $proxy_info['type'] = CURLPROXY_HTTP;
            $proxy_list[] = $proxy_info;
        }

        return $proxy_list;
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
        global $proxy_list, $def_proxy_info, $current_user_agent, $user_agents, $elibrary_config;


        if (empty($proxy_list)) {
            $proxy_list = self::getList();
            if (empty($proxy_list)) {
                arrayLog('STOP WORK', 'Proxy List Empty', 'error');
                exit;
            }
        }



        $index = rand(0, count($proxy_list) - 1);
        $def_proxy_info = $proxy_list[$index];

        if ($index > count($proxy_list) * 0.98 - 1) {
            $proxy_list = self::getList();
        }

        $index = rand(0, count($user_agents) - 1);
        $current_user_agent = $user_agents[$index];
        @unlink(getCookiePath(1));

        $elibrary_config = updateAuthAccount();


        arrayLog(array('New Proxy: ' . $def_proxy_info['full']), 'Change Proxy');

        return true;
    }

    static function myproxySet($proxy)
    {
        global $def_proxy_info;
        $def_proxy_info['full'] = $proxy;
    }


}


?>