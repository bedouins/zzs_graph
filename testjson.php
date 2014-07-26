<?php 
$fruits = array (
    "fruits"  => array("a" => "orange", "b" => "banana", "c" => "apple"),
    "numbers" => array(1, 2, 3, 4, 5, 6),
    "holes"   => array("first", 5 => "second", "third")
); 
$fruits1= array (
    "fruits2"  => array("d" => "orange", "e" => "banana", "f" => "apple"),
    "numbers" => array(1, 2, 3, 4, 5, 6),
    "holes"   => array("first", 5 => "second", "third")
); 
array_merge_recursive($fruits,$fruits1);
echo json_encode($fruits);
?>