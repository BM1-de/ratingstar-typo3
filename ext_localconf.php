<?php

declare(strict_types=1);

defined('TYPO3') or die();

// Make <rs:seal …/> available globally in Fluid templates without an
// explicit namespace import.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['rs'][] = 'RatingStar\\Seal\\ViewHelpers';
