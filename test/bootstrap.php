<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
$loader = include __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerLoader([$loader, 'loadClass']);
