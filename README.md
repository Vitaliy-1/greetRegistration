# Greet Registration Plugin
A sample plugin for OJS 3.4+ that listens to 2 registration-related events and sends emails on successful registration 
to the site or context. Plugin demonstrates examples of managing emails with a new mail service. The latter is based on 
Illuminate's Mail Service Provider adapted to work together with OJS's mail templates and variables.
## Mail Service Guidelines
### General information
#### Mailable
`PKP\mail\Mailable` class represents email data and includes interface to set header field values, specifically but not limited
to `From`, `To`, and `Subject`. The class supports method chaining and has interface similar to Illuminate's `Mailable` 
with some differences. 

`Mailable` initialization can be as simple as
```php
$mailable = new \PKP\mail\Mailable();
```
To compose and send a simple email, it's required to set a sender, recipients, subject and body. Then, use `Mail`
facade for actual sending:
```php
$mailable->from('example-sender@email.address')
    ->to('example-recipient@email.address')
    ->subject('Example email')
    ->body('This is an example of email\'s body.');
    
Illuminate\Support\Facades\Mail::send($mailable);
```
#### Mailer
`PKP\mail\Mailer` class creates and configures Swift Mailer message, renders mail templates, and sends email, directly or through 
a facade, with firing associated events. All of these done automatically under the hood and doesn't require any additional 
user involvement. If necessary, current `Mailer` instance can be retrieved with a helper:
```php
$mailer = app('mailer');
```
### Mail templates and variables
`Mailable::body()` accepts also mail template string as the first argument. This method uses `Mailer` to compile it by replacing
variable names with their actual values. Variables are stored in the `viewData` property of the `Mailable` and can be assigned as in following example: 
```php
$mailable->addVariables([
    'userFullName' => 'John Doe',
    'contextName' => 'Journal Title Example'
]);  

$templateString = 'Dear {$userFullName},<br/>
                    <br/>
                    You have been listed as an author on a manuscript submission to {$contextName}.'
$mailable->body($templateString); 
```
`Mailable`  constructor also accepts additional arguments; instances of the following classes may be passed to the
constructor in any order:
* Site
* Context
* ReviewAssignment
* StageAssignment
* Submission
* QueuedPayment
Each of these assigns associated template variables to the `Mailable`. E.g.:
```php
$context = PKPApplication::get()->getRequest()->getSite();
$mailable = new \PKP\mail\Mailable($site);
```
makes `siteTitle` and `siteContactName` variable available to the template and they will be substituted with their real values
before email is sent. Classes, that store variables and their values and description belong to `PKP\mail\variables` namespace and extend 
`PKP\mail\variables\Variable`. 
### Mailable traits
Traits can enhance functionality of Mailables. `PKP\mail\mailables\Sender` and `PKP\mail\mailables\Recipient` trait are 
used to facilitate sender and recipient assignment to the Mailable, e.g.:
```php
$mailable = new class extends \PKP\mail\Mailable
{
    use \PKP\mail\mailables\Recipient;
    use \PKP\mail\mailables\Sender;
}

$recipient = \PKP\facades\Repo::user()->get($userId);
$sender = PKPApplication::get()->getRequest()->getUser();
$mailable->setRecipients([$recipient])->setSender($sender);
```
Note, usage of Recipient and Sender traits forbids setting recipients and sender in usual way with `from()` and `to()` methods.

Another important function of Recipient and Sender traits is adding correspondent variables to the Mailable, described in 
`PKP\mail\variables\SenderEmailVariable` and `PKP\mail\variables\RecipientEmailVariable`
### Why extending Mailables?
Extending Mailable and overloading constructor allows associate it with the specific template and variables before it's even
instantiated. The constructor is scanned by `ReflectionClass` methods to identify passed arguments and associate them 
with correspondent variables, their names and description, before their actual values are known. 
```php
namespace PKP\mail\mailables;

class SpecificMailable extends \PKP\mail\Mailable
{
    // Associate Mailable with a specific mail template
    public const EMAIL_KEY = 'NOTIFICATION';
    
    // This property is used to organize mailable, how it is shown in the UI 
    protected static array $groupIds = [self::GROUP_SUBMISSION];
    
    // Let the Mailable know which template variables should be assigned by passing associated objects to the constructor
    public function __construct(\PKP\context\Context $context, \PKP\submission\PKPSubmission $submission)
    {
        parent::__construct(func_get_args());
    }
}
```
Retrieve description of template variables assigned to the Mailable:
```php
\APP\i18n\AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);
$variables = \PKP\mail\mailables\SpecificMailable::getVariables();
```
The context of the `$variables` array:
```php
[
    'principalContactSignature' => 'The journal\'s email signature for automated emails',
    'contactEmail' => 'The email address of the journal\'s primary contact',
    'passwordLostUrl' => 'The URL to a page where the user can recover a lost password',
    'journalName' => 'The journal\'s name',
    'journalUrl' => 'The URL to the journal\'s homepage',
    'submissionTitle' => 'The submission\'s title',
    'submissionId' => 'The submission\'s unique ID number',
    'submissionAbstract' => 'The submission\'s abstract',
    'authors' => 'Author names in a form of a shortened string',
    'authorsFull' => 'The full names of the authors',
    'submissionUrl' => 'The URL to the submission in the editorial backend',

]
```
After Mailable instantiation actual values of template variables are stored in the `viewData` property and are used to 
compile email's subject and body. 
```php
$mailable = new SpecificMailable($context, $submission);
$mailable->viewData; // array of context and submission associated template variables: name => value
$mailable->subject('{$journalName}');
$mailable->body('Thank you for submitting a manuscript {$submissionTitle}'); 
```
### Key differences from the Illuminate Mailable
* It's not recommended to configure email header information from within the Mailable class itself.
* Markup and Blade templates aren't supported; adapted to work with MailTemplate classes 
* Calls to Illuminate View are omitted.
* In the `Mailable::viewData`, `message` key is reserved for the current instance of object, which represents Swift Mailer's
message; it's removed before compilation. 
* Template variables are allowed in the email's subject.






