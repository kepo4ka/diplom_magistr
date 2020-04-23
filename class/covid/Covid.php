<?php

require_once __DIR__ . '/../../init.php';

use Helper\DB;
use Helper\Helper;

function updateCountriesCovidInfo()
{
    $url = 'https://insysbio.github.io/covid-19-data/hopkins/json/_combined.json';

    $countries = json_decode(Helper::fetch($url), true);

    foreach ($countries as $key => $country) {
        $info['iso'] = $country['country_code'];
        $info['name'] = $country['Country.Region'];
//        DB::save($info, 'countries', 'iso');

        foreach ($country['timeseries'] as $timesery) {
            $timesery['iso'] = $info['iso'];
            DB::save($timesery, 'covid');
        }
    }
    return true;
}


function getCountriesInfo()
{
    $url = 'https://www.routitude.com/api/geo/v2/countries/iso2/';
    $countries = DB::getAll('countries');
    $k = 1;
    foreach ($countries as $country) {
        $info = fetch($url . $country['iso']);
        $info = json_decode($info, true);

        if (empty($info['iso_code'])) {
            continue;
        }

        $info['iso'] = $info['iso_code'];
        $info['safety'] = $info['indexes']['safety'];
        $info['cpi'] = $info['indexes']['cpi'];
        $info['area'] = $info['area_in_sq_km'];
        $info['language'] = @$info['languages'][0];
        $info['language1'] = @$info['languages'][1];
        $info['language2'] = @$info['languages'][2];
        $info['language3'] = @$info['languages'][3];

        $info['alternate_names'] = @$info['alternate_names'][0];
        DB::save($info, 'countries', 'iso');

        if ($k % 3 == 0) {
            ProxyDB::update();
        }
    }
    return true;
}


function getVisaInfo()
{
    $url = 'https://www.routitude.com/api/geo/v2/visa/from_country_to_country/';

    $countries = DB::getAll('countries');
    $k = 1;
    foreach ($countries as $country) {
        foreach ($countries as $country1) {
            if ($country['iso'] === $country1['iso']) {
                continue;
            }

            $info = fetch($url . $country['iso'] . '/' . $country1['iso']);
            $info = json_decode($info, true);

            if (empty($info['from_country_code'])) {
                continue;
            }
            $info['from_iso'] = $country['iso'];
            $info['to_iso'] = $country1['iso'];
            $info['status'] = $info['visa_option_0']['status'];
            $info['stay'] = $info['visa_option_0']['stay'];


            $primary = [
                [
                    'column' => 'from_iso',
                    'value' => $info['from_iso'],
                ],
                [
                    'column' => 'to_iso',
                    'value' => $info['to_iso']
                ]
            ];

            DB::save($info, 'visa', $primary);

            if ($k % 3 == 0) {
                ProxyDB::update();
            }
        }
    }
    return true;

}


function getResrictions()
{
    $url = 'https://www.routitude.com/api/health/v2/covid/restrictions/country/';
    $countries = DB::getAll('countries');


    $k = 1;
    foreach ($countries as $country) {
        $full_url = $url . $country['iso'] . '/combined';
        $info = fetch($full_url);
        $info = json_decode($info, true);

        if (empty($info)) {
            continue;
        }


        $country['restriction_type'] = $info['restriction_type'];
        $country['restriction_text'] = $info['restriction_text'];

        $date = new DateTime(str_replace('.', ':', $info['update_time']));
        $country['restriction_update_time'] = $date->format('Y-m-d H:i:s');

        DB::save($country, 'countries', 'iso');
        if ($k % 3 == 0) {
            ProxyDB::update();
        }
        $k++;
    }

    Helper::echoVarDumpPre('Complete!');
}


function getTopCities()
{
    $url = 'https://www.routitude.com/api/geo/v2/countries/iso2/';
    $countries = DB::getAll('countries');


    $k = 1;
    foreach ($countries as $country) {
        $full_url = $url . $country['iso'] . '/top-cities';
        $cities = fetch($full_url);
        $cities = json_decode($cities, true);

        if (empty($cities)) {
            continue;
        }

        foreach ($cities as $info) {
            $info['id'] = $info['geoname_id'];
            $info['iata'] = $info['iata_code'];
            $info['iata'] = $info['iata_code'];
            $info['cpi'] = @$info['indexes']['cpi'];
            $info['safety'] = @$info['indexes']['safety'];
            $info['country_iso'] = $info['country']['code'];
            $info['is_top'] = true;

            DB::save($info, 'cities', 'id');
        }

        if ($k % 3 == 0) {
            ProxyDB::update();
        }
        $k++;
    }

    Helper::echoVarDumpPre('Complete!');
}


function getCities()
{
    $cities = DB::getAll('cities');

    $k = 1;

    foreach ($cities as $city) {
        $info = getCity($city['id']);

        if (empty($info)) {
            continue;
        }

        DB::save($info, 'cities', 'id');
        if ($k % 3 == 0) {
            ProxyDB::update();
        }
        $k++;
    }

    Helper::echoVarDumpPre('Complete!');
}


function getCity($id)
{
    $url = 'https://www.routitude.com/api/geo/v2/cities/geonameid/';

    $full_url = $url . $id;
    $info = fetch($full_url);
    $info = json_decode($info, true);

    if (empty($info)) {
        return false;
    }


    $info['id'] = $info['geoname_id'];
    $info['iata'] = @$info['iata_code'];
    $info['cpi'] = @$info['indexes']['cpi'];
    $info['safety'] = @$info['indexes']['safety'];
    $info['country_iso'] = $info['country']['iso_code'];
    $info['is_top'] = false;
    return $info;
}


function getAirports()
{
    $url = 'https://www.routitude.com/api/routes/v2/from_city/';
    $cities = DB::getAll('cities');

    $k = 1;

    foreach ($cities as $city) {

        if (DB::checkExist('airports', 'city_id', $city['id'])) {
            continue;
        }

        $full_url = $url . $city['id'];
        $z['post']['rand'] = rand(1, 100000);

        $info = fetch($full_url, $z);
        $info = json_decode($info, true);


        if (empty($info['airports'])) {
            continue;
        }

        foreach ($info['airports'] as $airport) {
            $airport['city_id'] = $airport['cg_id'];
            $airport['city_name'] = @$airport['c_name'];

            if (empty($airport['city_id'])) {
                continue;
            }

            getCityAutoComplete($airport['city_name']);

            DB::save($airport, 'airports', 'id');
        }


        foreach ($info['destinations'] as $destination) {
            $destination['air_from'] = $destination[0];
            $destination['air_to'] = $destination[1];
            $destination['status'] = $destination[2];
            unset($destination[0]);
            unset($destination[1]);
            unset($destination[2]);

            if (empty($destination['air_from'])) {
                continue;
            }

            $primary = [
                [
                    'column' => 'air_from',
                    'value' => $destination['air_from']
                ],
                [
                    'column' => 'air_to',
                    'value' => $destination['air_to']
                ]
            ];
            DB::save($destination, 'destinations', $primary);
        }


        if ($k % 3 == 0) {
            ProxyDB::update();
        }
        $k++;
    }
}


function getCityAutoComplete($search)
{
    $url = 'https://www.routitude.com//api/geo/v2/cities/autocomplete/';

    $info = Helper::fetch($url . $search);

    $info = json_decode($info, true);

    if (empty($info)) {
        return false;
    }

    $k = 1;
    foreach ($info as $city) {
        $exist = DB::getById('cities', $city['geoname_id']);

        if ($exist) {
            continue;
        }

        $city['id'] = $city['geoname_id'];

        $city = getCity($city['id']);

        if (empty($city)) {
            continue;
        }

        DB::save($city, 'cities', 'id');
        if ($k % 3 == 0) {
            ProxyDB::update();
        }
        $k++;
    }
    return count($info);
}

function getCitiesFromAirportsDB()
{
    $airports = DB::getAll('airports');

    $k = 1;

    foreach ($airports as $airport) {
        $city_id = $airport['city_id'];

        if (!DB::checkExist('cities', 'id', $city_id)) {
            $info = getCity($city_id);

            if (empty($info)) {
                continue;
            }

            DB::save($info, 'cities');

            if ($k % 3 == 0) {
                ProxyDB::update();
                $k = 0;
            }
            $k++;
        }
    }

    return true;
}