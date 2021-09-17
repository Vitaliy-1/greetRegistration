<?php

/**
 * @file plugins/generic/greetRegistration/mailables/RegistrationCompletedContext.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RegistrationCompletedContext
 *
 * @brief Sends email on successful registration from a context
 */

use PKP\context\Context;

class RegistrationCompletedContext extends \PKP\mail\Mailable
{
    use \PKP\mail\mailables\Recipient;

    public function __construct(Context $context)
    {
        parent::__construct(func_get_args());
    }
}
