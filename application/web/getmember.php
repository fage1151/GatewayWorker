<?php
use Workerman\Protocols\Http;
Http::header('content-type:application/json');
echo file_get_contents('getmember.json');