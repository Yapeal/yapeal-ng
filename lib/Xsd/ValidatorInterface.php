<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Dragonaire
 * Date: 11/11/2016
 * Time: 5:53 PM
 */
namespace Yapeal\Xsd;

use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;

/**
 * Class Validator
 */
interface ValidatorInterface
{
    /**
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function validateEveApi(
        EveApiEventInterface $event,
        string $eventName,
        MediatorInterface $yem
    ): EveApiEventInterface;
}
