<?php

namespace PhpSpec\Extension;

use PhpSpec\ServiceContainer;
use PhpSpec\Extension\Listener\CodeCoverageListener;
use PHP_CodeCoverage_Report_Clover,
    PHP_CodeCoverage_Report_HTML;

/**
 * Injects a Event Subscriber into the EventDispatcher. The Subscriber
 * will before each example add CodeCoverage Information.
 */
class CodeCoverageExtension implements \PhpSpec\Extension\ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ServiceContainer $container)
    {
        $container->setShared('code_coverage.filter', function () {
            return new \PHP_CodeCoverage_Filter();
        });

        $container->setShared('code_coverage', function ($container) {
            return new \PHP_CodeCoverage(null, $container->get('code_coverage.filter'));
        });

        $reports = ['HTML', 'Clover'];
        foreach ($reports as $r) {
            $callable = 'PHP_CodeCoverage_Report_'.$r;
            $rl = strtolower($r);
            $container->setShared('code_coverage.report.'.$rl, new $callable());

            $container->setShared('event_dispatcher.listeners.code_coverage_'.$rl, function ($container) {
                $listener = new CodeCoverageListener($container->get('code_coverage'), $container->get('code_coverage.report.'.$rl));
                $listener->setIO($container->get('console.io'));
                $listener->setOptions($container->getParam('code_coverage', array()));

                return $listener;
            });
        }
    }
}
