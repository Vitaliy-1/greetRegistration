<?php

/**
 * @file plugins/generic/greetRegistration/listeners/RegistrationSubscriber.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RegistrationSubscriber
 *
 * @brief Register listener to the registration events and implements email sending logic
 */

import('plugins.generic.greetRegistration.mailables.RegistrationCompletedContext');

use PKP\config\Config;
use PKP\mail\Mailable;
use PKP\observers\events\UserRegisteredContext;
use PKP\observers\events\UserRegisteredSite;
use Illuminate\Support\Facades\Mail;

class RegistrationSubscriber
{
    /**
     * Handle user registered from context events
     * @param \PKP\observers\events\UserRegisteredContext
     */
    public function handleRegistrationContext(UserRegisteredContext $event)
    {
        if ($this->emailValidationRequired()) {
            return;
        }

        $mailable = new RegistrationCompletedContext($event->context); // enables usage of context template variables
        $mailable->setRecipients([$event->recipient]); // sets recipients and enables usage of recipient-related variables
        $mailable->from($event->context->getContactEmail(), $event->context->getContactName());

        // Assign additional template variable
        $mailable->addVariables([
            'aboutSubmissionsUrl' => \PKP\core\PKPApplication::get()->getRequest()->url($event->context, 'about', 'submissions'),
        ]);

        // Set the subject and body
        $mailable->subject('Registration successful');
        $mailable->body('<p>Congratulations, you are now registered on the {$journalName}. </p>' .
            '<p>To make a submission, please visit: <a href="{$aboutSubmissionsUrl}">Submissions page</a></p>');

        Mail::send($mailable);
    }

    /**
     * Handle user registered from site events
     * @param \PKP\observers\events\UserRegisteredSite $event
     */
    public function handleRegistrationSite(UserRegisteredSite $event)
    {
        if ($this->emailValidationRequired()) {
            return;
        }

        // Create mailable; passing site object to the constructor to use Site-related template variables
       $mailable = new Mailable([$event->site]);
       $mailable->subject('Registration successful');

       // Using template variable as an example
       $mailable->body('Your registration on the {$siteName} is complete. Please visit your user account for additional settings');

       $mailable->from($event->site->getLocalizedContactEmail(), $event->site->getLocalizedContactName());
       $mailable->to($event->recipient->getEmail(), $event->recipient->getFullName());

       Mail::send($mailable);
    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            UserRegisteredContext::class,
            self::class . '@handleRegistrationContext'
        );

        $events->listen(
            UserRegisteredSite::class,
            self::class . '@handleRegistrationSite'
        );
    }

    protected function emailValidationRequired() : bool
    {
        return (bool) Config::getVar('email', 'require_validation');
    }
}
