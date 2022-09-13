<?php

require_once "player.php";

class Tournament
{
    private $name, $date, $playerInfo = [];

    public function __construct($name, $date = null)
    {
        $this->name = $name;

        $this->date = ($date == null) ? date('Y-m-d', strtotime('+1 day'))
            : str_replace(".", "-", $date);//иначе парсер даты не пашет
    }

    public function addPlayer($playerInfo)
    {
        $this->playerInfo[] = $playerInfo;
        return $this;
    }

    public function createPairs()
    {
        $inputArr = $this->playerInfo;
        $i = count($inputArr);
        if ($i % 2 == 1) {
            $inputArr[] = new Player("Zaglushka"); //now its invisible!
            $i++;
        }
        //split arr in 2 pieces for better management
        $chunks = array_chunk($inputArr, $i / 2);
        $arr1 = $chunks[0]; //1st column
        $arr2 = array_reverse($chunks[1]); //2nd column + reverse it
        for ($k = 1; $k < $i; $k++) //number of tournaments n-1
        {
            echo $this->name . ", " . date('Y.m.d', strtotime($this->date));
            $this->echoBr();
            
            for ($j=0; $j<count($arr1); $j++)
            {
                if (
                    $arr1[$j]->getName()!="Zaglushka" 
                    && $arr2[$j]->getName()!="Zaglushka"
                ) {
                    echo $this->echoPlayer($arr1[$j]) . " - ". $this->echoPlayer($arr2[$j]);
                    $this->echoBr();
                }
            }
            
            $this->date = date('Y-m-d', strtotime($this->date . ' +1 day')); //change date for next turik
            
            $locked = array_shift($arr1); //lock 1 player for algorithm
            //array shifting
            $temp = array_shift($arr1);
            array_unshift($arr2, $temp);
            $arr1[] = array_pop($arr2);
            //add locked player back
            array_unshift($arr1, $locked);
        }
        $this->echoBr(); //extra space between turiks
    }

    public function echoPlayer($obj) //to prevent ctrl+c ctrl+v
    {
        $name = $obj->getName();
        $city=null;
        if ($obj->getCity() != null) {
            $city = " (" . $obj->getCity() . ")";
        }
        return $name.$city;
    }

    public function echoBr() //jk
    {
        echo "<br>";
    }
}

//It just works! (c) Todd
