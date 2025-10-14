<?php

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

function apiUrl()
{
    return 'https://tests.saloon.dev/api';
}

/**
 * @return TestConnector
 */
function connector()
{
    return new TestConnector();
}

/**
 * @param resource $output
 *
 * @return Closure
 */
function getCustomVarDump($output)
{
    return static function ($var, $label = null) use ($output) {
        $dumper = new CliDumper;
        $cloner = new VarCloner;

        $var = $cloner->cloneVar($var);

        if (null !== $label) {
            $var = $var->withContext(['label' => $label]);
        }

        $dumper->dump($var, $output);
    };
}
