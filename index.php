<?php

/**
 * @file plugins/generic/greetRegistration/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_greetRegistration
 * @brief Mails user after successful registration
 *
 */

require_once('GreetRegistrationPlugin.inc.php');

return new GreetRegistrationPlugin();
