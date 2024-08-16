<?php

//define custom class

class Fruit{
    public $name;
    public $color;

    //Set name method
    public function set_name($name){
        $this-> name = $name;
    }

    //Get name method
    public function get_name(){
        return $this-> name;
    }

    //Set color method
    public function set_color($color){
        $this -> color = $color;
    }

    //Get color method
    public function get_color(){
        return $this -> color;
    }
}

// Define objects for name
$banana = new Fruit();
$banana -> set_name("Banana");
$banana -> set_color("The color of my banana is green");

$apple = new Fruit();
$apple -> set_name("Apple");
$apple -> set_color("The color of my apple is red");

//Define objects for color
// $yellow = new Fruit();
// $yellow -> set_color("The color of my banana is yellow");

// $green = new Fruit();
// $green -> set_color("The color of my apple is green");



echo $banana -> get_name();
echo "<br />";
echo $banana -> get_color();

echo "<br>";
echo "<br>";
echo $apple -> get_name();
echo "<br>";
echo $apple -> get_color();

echo "<br />";
echo"<br>";

// echo $yellow -> get_color();
// echo "<br />";

// echo $green -> get_color();