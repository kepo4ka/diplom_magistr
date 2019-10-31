<?php


class ElibraryDB
{
    var $db;
    var $organisations = 'organisations';
    var $publications = 'publications';
    var $authors = 'authors';
    var $authors_to_publications = 'authors_to_publications';


    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function getAllOrganisations()
    {
        $query = 'SELECT * FROM ?n';
        return $this->db->getAll($query, $this->organisations);
    }


    function getOrganisationRelOrganisations($id)
    {
        $query = 'SELECT `organisations`.`id` FROM `organisations`, `authors_to_organisations` WHERE `organisations`.`id`=`authors_to_organisations`.`orgsid`'
    }



}


?>