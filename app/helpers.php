<?php

use ProcessMaker\Laravel\Nayra\ServiceTask;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Implement ServiceTask to serve AI services
 */
function WebService(TokenInterface $token, ServiceTask $service)
{
    $dataInput = $service->getDataInputs($token);
    $drools = 'http://www.jboss.org/drools';
    $interface = $service->getBpmnElement()->getAttributeNS($drools, 'serviceinterface');
    if (class_exists($interface) && method_exists($interface, '__invoke')) {
        // AI interface
        $ai = new $interface();
        $ai(...$dataInput);
        $output = $service->getDataOutputs($token, (array) $ai);
        error_log(json_encode($output));
        return $output;
    }
    throw new Exception('Invalid service interface: ' . $interface);
}
