<?php

use Helper\DB;
use Helper\Helper;

require_once __DIR__ . '/../../init.php';


class Nosu
{
    public $fakultet_table = 'nosu_fakultets';
    public $kafedros_table = 'nosu_kafedros';

    public function parseFakultets()
    {

        $url = 'http://www.nosu.ru/facultet/';

        $parsed_html = Helper::fetch($url);


        $data = str_get_html($parsed_html);

        if (empty($data)) {
            return false;
        }

        $links = $data->find('.facultet-content .title>a');

        foreach ($links as $key => $link) {
            if ($key == 0) {
                // пропускаем Кафедру физического воспитания
                continue;
            }

            $href = $link->href;
            $name = Helper::checkRegular('/http:\/\/www.nosu.ru\/facultet\/(\w+)/', $href);

            $name_ru = $link->plaintext;

            $fakultet = [
                'name' => $name,
                'name_ru' => $name_ru
            ];

            DB::save($fakultet, $this->fakultet_table);
        }
        return true;
    }


    public function parseKafedros()
    {
        $base_url = 'http://www.nosu.ru/facultet/';

        $fakultets = DB::getAll($this->fakultet_table);

        foreach ($fakultets as $fakultet) {

            $url = $base_url . $fakultet['name'] . '/kafedry/';
            $parsed_html = Helper::fetch($url);

            $data = str_get_html($parsed_html);
            if (empty($data)) {
                return false;
            }

            $links = $data->find('.main-content .content-block.content-text ul>li a');

            foreach ($links as $key => $link) {
                $href = $link->href;
                $name = Helper::checkRegular('/kafedry\/([-\w]+)\//', $href);

                if (empty($name)) {
                    continue;
                }

                $name_ru = trim($link->plaintext);

                $name_ru = Helper::inputFilter($name_ru);

                $kafedra = [
                    'name' => $name,
                    'name_ru' => $name_ru,
                    'fakultet_id' => $fakultet['id']
                ];

                DB::save($kafedra, $this->kafedros_table, 'name');
            }
        }
        echo 'complete';
    }

    public function parseAuthors()
    {
        $base_url = 'http://www.nosu.ru/facultet/';
        $fakultets = DB::getAll($this->fakultet_table);

        $authors = [];

        foreach ($fakultets as $fakultet) {
            $kafedros = DB::getByColAll($this->kafedros_table, 'fakultet_id', $fakultet['id']);

            foreach ($kafedros as $kafedra) {
                $url = $base_url . $fakultet['name'] . '/kafedry/' . $kafedra['name'];

//                if ($url != 'http://www.nosu.ru/facultet/it/kafedry/kafedra-algebry-i-geometrii') {
//                    continue;
//                }

                $parsed_html = Helper::fetch($url);

                $data = str_get_html($parsed_html);
                if (empty($data)) {
                    return false;
                }

                $p = @$data->find('#middle .main-content .content-block.content-text p')[0];

                if (empty($p->innertext)) {
                    continue;
                }

                $nauthors = explode('<br /> ', $p);

                foreach ($nauthors as $nauthor) {
                    $nauthor = Helper::checkRegular('/[А-Я]{1}\w+ [А-Я]{1}\w+ [А-Я]{1}\w+/u', $nauthor, 0);
                    if (!$nauthor) {
                        continue;
                    }
                    $nauthor = [
                        'name_ru' => Helper::inputFilter($nauthor),
                        'fakultet' => $fakultet['name'],
                        'kafedra' => $kafedra['name']
                    ];
                    $authors[] = $nauthor;
                }
            }
        }

        $info [] = ['Количесвто' => count($authors)];

        $saved = 0;
        $bad = [];
        foreach ($authors as $author) {
            $dbauthor = DB::getByColumn('authors', 'fio', $author['name_ru']);

            if (empty($dbauthor)) {
                $bad [] = $author;
                continue;
            }

            $dbauthor['fakultet'] = $author['fakultet'];
            $dbauthor['kafedra'] = $author['kafedra'];

            DB::save($dbauthor, 'nosu_authors');
            $saved++;
        }

        $info[] = ['Сохранено' => $saved];

        echo Helper::json_encode($bad);

        return true;
    }

}