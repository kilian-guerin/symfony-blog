<?php
use Symfony\Config\TwigConfig;

return static function(TwigConfig $twig) {
    $twig->formThemes(['bootstrap_5_layout.html.twig']);
};
?>