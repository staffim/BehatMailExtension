<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;
use Staffim\Behat\MailExtension\MailAgent;

class FeatureContext implements Context, SnippetAcceptingContext, MailAwareInterface
{
    /**
     * @var MailAgent
     */
    private $agent;

    /**
     * @var string
     */
    private $sharedMailbox;

    public function __construct($sharedMailbox)
    {
        $this->sharedMailbox = $sharedMailbox;

        // For mailtrap.io, see https://github.com/zetacomponents/Mail/pull/40.
        ezcMailTools::setLineBreak("\n");
    }

    /**
     * @param MailAgent $mailAgent
     */
    public function setMailAgent(MailAgent $mailAgent)
    {
        $this->agent = $mailAgent;
    }

    /**
     * @param array $parameters
     */
    public function setMailAgentParameters(array $parameters)
    {
    }

    /**
     * @BeforeScenario
     */
    public function cleanMailbox(BeforeScenarioScope $scope)
    {
        // Actual for Mailtrap.io, GMail don't support deletion.
        $this->agent->removeMailFromServer();
    }

    /**
     * @Given I have an email in my inbox
     */
    public function iHaveEmailInMyInbox()
    {
        $mail = new ezcMailComposer();
        $mail->from = new ezcMailAddress('andrey@example.com', 'Andrey Fureev');
        $mail->addTo(new ezcMailAddress('alexey@example.com', 'Alexey Shockov'));
        // For GMail and other real environments.
        $mail->addBcc(new ezcMailAddress($this->sharedMailbox));
        $mail->subject = 'Reset Your Password';
        $mail->plainText = <<<MAIL
Do you really need to reset your password? Follow this link to restore: http://example.com/restore
MAIL;
        $mail->build();

        $this->agent->send($mail);
    }
}
