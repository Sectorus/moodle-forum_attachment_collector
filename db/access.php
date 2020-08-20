<?php
/**
 * Forum Attachment collector capabilities for manager role
 *
 * @package   forum_attachment_collector
 * @copyright 2020 Stephan Lorbek
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = [
        'local/forum_attachment_collector:viewexporter'   => [
                'riskbitmask'  => RISK_CONFIG,
                'captype'      => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes'   => [
                        'manager' => CAP_ALLOW,
                ],
        ]
];

?>
