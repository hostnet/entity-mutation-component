<?php
declare(strict_types=1);

/**
 * @copyright 2016-present Hostnet B.V.
 */
$loader = include __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerLoader([$loader, 'loadClass']);
