<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2017/3/1
 * Time: 11:08
 */
namespace Tool;
class Polygon {
    public function LatLng($lng, $lat) {
        $result = array();
        $result['lat'] = $lat;
        $result['lng'] = $lng;
        return $result;
    }

    public function pointIsInRegion($x, $y, $point_list) {
        $crossings = 0;
        $point = $this->LatLng($x, $y);
        $count = count($point_list);
        for ($i = 0; $i < $count; $i++) {
            $a = $point_list[$i];
            $j = $i + 1;
            if ($j >= $count) {
                $j = 0;
            }
            $b = $point_list[$j];
            if ($this->rayCrossesSegment($point, $a, $b)) {
                $crossings++;
            }
        }
        return ($crossings % 2 == 1);
    }

    /**
     * @param array $point
     * @param array $a 坐标数组0 lng , 1 lat
     * @param array $b 坐标数组0 lng , 1 lat
     * @return bool
     */
    public function rayCrossesSegment($point, $a, $b) {
        $px = $point['lng'];
        $py = $point['lat'];

        $ax = $a['lng'];
        $ay = $a['lat'];

        $bx = $b['lng'];
        $by = $b['lat'];

        if ($ay > $by) {
            $ax = $b['lng'];
            $ay = $b['lat'];
            $bx = $a['lng'];
            $by = $a['lat'];
        }

        if ($px < 0) { $px += 360; }
        if ($ax < 0) { $ax += 360; }
        if ($bx < 0) { $bx += 360; }

        if ($py == $ay || $py == $by) $py += 0.00000001;
        if (($py > $by || $py < $ay) || ($px > max($ax, $bx))) return false;
        if ($px < min($ax, $bx)) return true;

        $red = ($ax != $bx) ? (($by - $ay) / ($bx - $ax)) : 3.4028235E38;
        $blue = ($ax != $px) ? (($py - $ay) / ($px - $ax)) : 3.4028235E38;
        return ($blue >= $red);
    }
}