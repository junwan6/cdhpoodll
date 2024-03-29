<?php

/**
 * Capabilities
 *
 * @package     ucla
 * @subpackage  uclasiteindicator
 */

$capabilities = array(

    'tool/uclasiteindicator:view' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    
    'tool/uclasiteindicator:edit' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    )
);
