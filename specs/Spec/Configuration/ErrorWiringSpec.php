<?php
namespace Spec\Yapeal\Configuration;

use PhpSpec\ObjectBehavior;

/**
 * Class ErrorWiringSpec
 *
 * @mixin \Yapeal\Configuration\ErrorWiring
 */
class ErrorWiringSpec extends ObjectBehavior
{
    /**
     *
     */
    public function itIsInitializable()
    {
        $this->shouldHaveType('Yapeal\Configuration\ErrorWiring');
    }
    /**
     * @param \Yapeal\Container\ContainerInterface $dic
     */
    public function itProvidesFluentInterfaceFromWire($dic)
    {
        $this->wire($dic)
            ->shouldReturn($this);
    }
}
