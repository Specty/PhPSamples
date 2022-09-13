<?php

require_once "DBConnect.php";
require "vendor\autoload.php";

use Shuchkin\SimpleXLS;

class FilterData
{
    private $dbCon;
    private $artFrom;
    private $artTo;
    private $name;
    private $priceFrom;
    private $priceTo;
    private $number;
    private $foundSymbol = "";

    public function __construct()
    {
        $this->dbCon = new DBConnect();
    }

    public function getInputData(array $params)
    {
        $this->artFrom = $params['artFrom'];
        $this->artTo = $params['artTo'];
        $this->name = $params['name'];
        $this->priceFrom = $params['priceFrom'];
        $this->priceTo = $params['priceTo'];
        $this->number = $params['number'];
        return $this;
    }

    public function echoInputData()
    {
        echo $this->this->artFrom . ", " . $this->artTo . "<br>";
        echo $this->name . "<br>";
        echo $this->priceFrom . ", " . $this->priceTo . "<br>";
        echo $this->number . "<br>";
        echo $this->foundSymbol . "<br>";
    }

    private function artInputAnalyze(): FilterData
    {
        //check if one of articles is empty (not both)
        if (
            (trim($this->artFrom) && !trim($this->artTo))
            || (!trim($this->artFrom) && trim($this->artTo))
        ) {
            exit("One of articles is empty!");
        }

        //if input articles not empty (both)
        if (trim($this->artFrom) && trim($this->artTo)) {
            //if digits only or not
            if (
                ctype_digit($this->artFrom)
                && ctype_digit($this->artTo)
            ) {
                if ($this->artFrom > $this->artTo) {
                    exit("Article `from` value must be lower than `to` value!<br>");
                }
            } else {
                $symbolArr = array("200E", "NXK", "PXR", "LYR", "TS", "TT", "TW", "T", "F", "R", "E");

                foreach ($symbolArr as $symbol) {
                    if (str_contains($this->artFrom, $symbol)) {
                        $this->foundSymbol = $symbol;
                        break;
                    }
                }

                if (!$this->foundSymbol) {
                    exit("Cant recognize article!");
                }

                if (!str_starts_with($this->artTo, $this->foundSymbol)) {
                    exit("Articles dont match!<br>");
                }

                $this->artFrom = str_replace($this->foundSymbol, "", $this->artFrom);
                $this->artTo = str_replace($this->foundSymbol, "", $this->artTo);
                if ($this->artFrom > $this->artTo) {
                    exit("Article `from` value must be lower than `to` value!<br>");
                }
            }
        }
        return $this;
    }

    private function priceInputAnalyze(): FilterData
    {
        if (
            ($this->priceFrom&&!$this->priceTo&&$this->priceFrom<0)
            ||(!$this->priceFrom&&$this->priceTo&&$this->priceTo<0)
        ) {
            exit("Something wrong with prices!<br>");
        }
        if($this->priceFrom&&$this->priceTo)
        {
            if ($this->priceFrom < 0 || $this->priceTo < 0 || $this->priceFrom > $this->priceTo) {
                exit("Something wrong with prices!<br>");
            }
        }
        return $this;
    }

    private function numberAnalyze(): FilterData
    {
        if (
            $this->number < 0
            || $this->number == ""
        ) {
            $this->number = 0;
        }
        return $this;
    }

    public function parseDataFromFile():void
    {
        $titles = [];
        $xlsRows = [];
        if ($xls = SimpleXLS::parseFile('parser (1) (2) (1) (1).xls')) {
            $xlsRows = $xls->rows();
            //array_shift($this->xlsRows);
            foreach (array_shift($xlsRows) as $title) {
                if ($title != null) {
                    $titles[] = str_replace(" ", "_", $title);
                } else {
                    exit("Error: title is empty!<br>");
                }
            }
            $this->filterData($titles, $xlsRows);
        } else {
            exit("Cant parse file!<br>");
        }
    }

    private function filterData($titles, $xlsRows)
    {
        $this->artInputAnalyze()->priceInputAnalyze()->numberAnalyze();
        $this->createDBStructure($titles);
        $counter = 0;
        foreach ($xlsRows as $row) {
            if (trim($row[0]) == "") {
                continue;
            }

            if (
                $this->filterByArt($row[0])
                && $this->filterByName($row[1])
                && $this->filterByPrice($row[2])
            ) {
                $this->dbCon->insertData($titles, $row); //insertData
                $counter++;
            }

            if ($this->number != 0 && $counter == $this->number) {
                break;
            }
        }
        $this->getDBData($titles);
    }

    private function getDBData($titles)
    {
        $result = $this->dbCon->getData($titles);
        if(!$result)
        {
            echo "Ничего не найдено!";
        } else {
            echo "<div class='row justify-content-center border'><div class='d-flex justify-content-center col-sm'><b>";
            echo $titles[0];
            echo "</b></div><div class='d-flex justify-content-center col-md'><b>";
            echo $titles[1];
            echo "</b></div><div class='d-flex justify-content-center col-sm'><b>";
            echo $titles[2];
            echo "</b></div></div>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='row justify-content-center border'><div class='d-flex justify-content-center col-sm'>";
                echo $row[$titles[0]];
                echo "</div><div class='d-flex justify-content-center col-md'>";
                echo $row[$titles[1]];
                echo "</div><div class='d-flex justify-content-center col-sm'>";
                echo $row[$titles[2]];
                echo "</div></div>";
            }
        }
        $this->dbCon->closeConn();
    }

    private function filterByArt($art)
    {
        if (!$this->artFrom) {
            return true;
        }

        if (!$this->foundSymbol) {
            if (
                $this->artFrom > $art
                || $this->artTo < $art
            ) {
                return false;
            }
        } else {
            if (!str_contains($art, $this->foundSymbol)) {
                return false;
            } else {
                $art = str_replace($this->foundSymbol, "", $art);
                if (
                    $this->artFrom > $art
                    || $this->artTo < $art
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    private function filterByName($rowName)
    {
        if ($this->name) {
            if (!str_contains($rowName, $this->name)) {
                return false;
            }
        }
        return true;
    }

    private function filterByPrice($price)
    {
        if (
            ($this->priceFrom && !$this->priceTo && $this->priceFrom > $price)
            || (!$this->priceFrom && $this->priceTo && $this->priceTo < $price)
        ) {
            return false;
        }

        if ($this->priceFrom && $this->priceTo) {
            if ($this->priceFrom > $price || $this->priceTo < $price) {
                return false;
            }
        }

        return true;
    }

    private function createDBStructure(array $titles)
    {
        $this->dbCon->createDB();
        $this->dbCon->dropTable();
        $this->dbCon->createTable($titles);
    }
}