<?php
/**
 *
 * @package   forum_attachment_collector
 * @copyright 2020 Stephan Lorbek
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('modsettings', new admin_externalpage('forum_attachment_collector_exporter',
                                                   get_string('forum_attachment_collector_exporter', 'local_forum_attachment_collector'),
                                                  new moodle_url('/local/forum_attachment_collector/views/exporter.php'),
                                                  'local/forum_attachment_collector:viewexporter'));
?>
