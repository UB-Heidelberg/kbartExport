<?php

/**
 * @file plugins/generic/kbartExport/index.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Copyright (c) 2022 Heidelberg University
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_kbartexport
 * @brief Wrapper for KBART export plugin.
 *
 */

require_once('KBARTExportPlugin.inc.php');
return new KBARTExportPlugin();
