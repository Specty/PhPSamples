<?php

class Player
{
    private $name, $city;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        //for chaining
        return $this->name;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }
}

