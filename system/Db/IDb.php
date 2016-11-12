<?php
namespace Sys\Db;

interface IDb {

    public function __construct($host, $user, $pass, $name, $port = NULL);

    public function connect();

    public function query($sql);
}