<?php

/**
 * @file plugins/generic/greetRegistration/GreetRegistrationPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class GreetRegistrationPlugin
 * @ingroup plugins_generic_greetRegistration
 *
 * @brief Class for GreetRegistration plugin
 */
import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.greetRegistration.listeners.RegistrationSubscriber');

use Illuminate\Support\Facades\Event;
use PKP\plugins\GenericPlugin;

class GreetRegistrationPlugin extends GenericPlugin
{

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName() : string
    {
        return __('plugins.generic.greetRegistration.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription() : string
    {
        return __('plugins.generic.greetRegistration.description');
    }

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                $this->registerSubscriber();
            }
            return true;
        }
        return false;
    }


    /**
     * Adds a listener to the published event
     * @return void
     */
    protected function registerSubscriber() {
        Event::subscribe(RegistrationSubscriber::class);
    }
}
