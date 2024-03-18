<?php
/**
 * Batch Tasks Cron public file.
 *
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @copyright DOT Systems sp. z o.o
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
chdir(__DIR__ . '/../');
define('IS_PUBLIC_DIR', true);
require 'batchTasksCron.php';
