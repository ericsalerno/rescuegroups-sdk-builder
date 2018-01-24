<?php
/**
 * Class RequestGenerator
 *
 * This code is built to assist in the fleshing out of many of the request objects required by the API.
 *
 * You generally should not run this unless you are activily developing on the SDK.
 *
 * @package RescueGroupsBuilder
 * @subpackage bin
 * @author Eric
 */
require_once(__DIR__ . '/../../vendor/autoload.php');

$generator = new \RescueGroupsBuilder\RequestGenerator();

$time = microtime(true);
$generator->buildDefinableDataModel();
echo 'Generation took ' . (microtime(true) - $time) . ' seconds.' . PHP_EOL;
