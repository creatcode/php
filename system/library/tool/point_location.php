<?php
/**
 * Class Point_Location
 */
class Point_Location {
    private $point_on_vertex = true;

    function pointInPolygon($point, $polygon, $point_on_vertex = true) {
        $this->point_on_vertex = $point_on_vertex;

        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }
    }

    function pointOnVertex($point, $vertices) {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
        return false;
    }

    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
}