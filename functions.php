<?php


function checkExist($table, $value)
{
    global $db;
    $query = "SELECT `id` FROM ?n WHERE `id`=?i LIMIT 1";
    $is_exist = $db->getOne($query, $table, $value);
    return $is_exist;
}

function getIpReg($str)
{
    $matches = array();
    preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/m', $str, $matches);

    if (!empty($matches[0])) {
        return $matches[0];
    }
    return false;
}

function save($p_data, $table, $primary = 'id')
{
    global $db;
    if (empty($p_data)) {
        return false;
    }

    $columns = getColumnNames($table);
    $data = $db->filterArray($p_data, $columns);

    if (!checkExist($table, $data[$primary])) {
        $query = 'INSERT INTO ?n SET ?u';
        return $db->query($query, $table, $data);
    } else if (!empty($p_data[$primary])) {
        $query = 'UPDATE ?n SET ?u WHERE ?n=?i';
        return $db->query($query, $table, $data, $primary, $data[$primary]);
    }
    return true;
}

function saveRelation($p_data, $table)
{
    global $db;
    if (empty($p_data)) {
        return false;
    }

    $columns = getColumnNames($table);
    $data = $db->filterArray($p_data, $columns);
    $query = 'INSERT INTO ?n SET ?u';
    return $db->query($query, $table, $data);
}


function fetch($url, $z = null)
{
    global $cookiePath, $def_proxy_info, $current_user_agent;

    $ch = curl_init();

    if (!empty($z['params'])) {
        $url .= '?' . http_build_query($z['params']);
    }

    $useragent = $current_user_agent;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 200); // http request timeout 20 seconds

    if (!empty($def_proxy_info)) {
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXY, $def_proxy_info['full']);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $def_proxy_info['auth']);
    }

    if (isset($z['refer'])) {
        curl_setopt($ch, CURLOPT_REFERER, $z['refer']);
    }

//    echoVarDumpPre($useragent);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (isset($z['timeout']) ? $z['timeout'] : 5));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiePath);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiePath);

    //https://stackoverflow.com/questions/8419747/php-curl-does-not-work-on-localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}


function fetchProxy($url, $z = null)
{
    global $query_count, $def_proxy_info, $delay_min, $delay_max;

    if ($query_count % 5 == 0 || $query_count % 9 == 0) {
        ProxyDB::update();
    }

    $result = array();

    $k = 1;
    $t = 1;


    while (empty($result)) {
        if ($k > 3) {
            return false;
        }


        $result = fetch($url, $z);
        echoBr($query_count . '. ' . json_encode($def_proxy_info['full']));
        $query_count++;
        $k++;


        if ($t > 2) {
            echoBr('BAD PROXY: ' . json_encode($def_proxy_info['full']));
            ProxyDB::update();
            $t = 1;
        }
        $t++;

//        usleep(rand($delay_min, $delay_max));
    }

    return $result;
}


function fetchNoProxy($url, $z = null)
{
    global $cookiePath;

    $result = '';
    try {
        $ch = curl_init();

        if (!empty($z['params'])) {
            $url .= '?' . http_build_query($z['params']);
        }

        $useragent = isset($z['useragent']) ? $z['useragent'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200); // http request timeout 20 seconds

        if (isset($z['refer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $z['refer']);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (isset($z['timeout']) ? $z['timeout'] : 5));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiePath);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiePath);

        //https://stackoverflow.com/questions/8419747/php-curl-does-not-work-on-localhost
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);

        curl_close($ch);
    } catch (Exception $ex) {

    }

    return $result;
}


/**
 * Получить столбцы таблицы
 * @param $table_name string Исходная таблица
 * @return array Список столбцов
 */
function getColumnNames($table_name)
{
    global $db;
    $columns = array();

    try {
        $sql = "SHOW COLUMNS FROM `$table_name`";
        $result = $db->query($sql);
        while ($row = $db->fetch($result)) {
            $columns[] = $row['Field'];
        }
    } catch (Exception $ex) {

    }
    return $columns;
}


function clearCookie()
{
    global $cookiePath;
    file_put_contents($cookiePath, '');
    return true;
}

function checkRegular($re, $str, $index = 1)
{
    $result = '';
    $matches = array();

    if (preg_match($re, $str, $matches)) {
        if (!empty($matches[$index])) {
            $result = $matches[$index];
        }
    }
    return $result;
}

function checkArrayFilled($array)
{
    foreach ($array as $key => $value) {
        if (empty($array[$key])) {
            return false;
        }
    }
    return true;
}

function jsRandom()
{
    return mt_rand() / (mt_getrandmax() + 1);
}


function delApostrof($string)
{
    $bad_symbol = '"';
    $count = substr_count($string, $bad_symbol);
    $last_symbol = substr($string, -1);


    if ($count % 2 == 1 && $last_symbol == $bad_symbol) {
        $string = substr($string, 0, -1);
    }
    return $string;
}

function echoVarDumpPre($var, $no_exit = false)
{
    global $log;
    echo '<pre>';
    var_dump($var);
    echo '</pre>';

    echo '<hr>';
    echo "log";
    echo '<hr>';
    echo '<pre>';
    var_dump($log);
    echo '</pre>';
    echo '<hr>';
    if (!$no_exit) {
        exit;
    }
}


function echoBr($var)
{
    echo json_encode($var, JSON_UNESCAPED_UNICODE);
    echo '<hr>';
}

