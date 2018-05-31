<?php

$layout = new \stdClass();
$layout->width = 1181;
$layout->height = 1417;


$margin_x =  0;
$margin_y =  0;
$padding_x = 0;
$padding_y = 0;


$layout->margin[] = array($margin_x,$margin_y);
$layout->margin[] = array($layout->width - $margin_x,$margin_y);
$layout->margin[] = array($margin_x, $layout->height - $margin_y);
$layout->margin[] = array($layout->width - $margin_x, $layout->height - $margin_y);



$posX = $margin_x;
$posY = $margin_y;
$cellHeight = 1181;
$cellWidth  = 1181;

$layout->box[1] = array(
    array('x'=> $margin_x , 'y' => $margin_y ),
    array('x'=> $posX + $cellWidth, 'y' => $posY ),
    array('x'=> $posX + $cellWidth, 'y' => $posY  + $cellHeight),
    array('x'=> $posX , 'y' => $posY + $cellHeight),
);

return $layout;