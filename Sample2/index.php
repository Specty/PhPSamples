<?php
require_once "filter.php";

$filterData = new FilterData();
$filterData->getInputData($_POST);
$filterData->parseDataFromFile();
