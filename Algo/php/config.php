<?php
    try
    {
        $bdd = new PDO('mysql:host=localhost;dbname=facebook2.0;charset=utf8','root','');

    }catch(Exception $e)
    {
        die('Erreur'.$e->getMessage());
    }
