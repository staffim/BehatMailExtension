# Behat Mail Extension

Extension implements classical mail client (like Apple Mail, for example) in PHP with corresponding steps for Behat.

## Usage in development

Mailtrap.io or any other solution (cloud or self-hosted SMTP and POP3 servers) are useful for development, because you 
can catch all application mail and profile it.

### Mailtrap.io

It's good to have separated inbox in Mailtrap.io only for acceptance testing (to prevent concurrent access from 
people and machines).

P.S.

Mailtrap.io currently is not working with ezcMail (see https://github.com/zetacomponents/Mail/pull/40 for details). You 
should use fork.

## Usage in production

If you would like to check you features on production environment, you should prepare your scenarios first. In 
development environment you can use any addresses, because all mail will be caught by your preferred mail profile 
service (Mailtrap.io, for example). But in production you can't: all mail needs to be sent to real addresses, and you 
are limited to only those mailboxes to which you have access.

One of available solutions to this problem is: having one real mailbox in production (example@example.com) and using 
addresses with `+` sign (like example+applicant@example.com, example+recruiter@example.com and so on) in your scenarios. 
In this case you will be able to check messages in any environment.

### GMail

To enable POP3 access to your GMail account, go to `"Forwarding and POP/IMAP"` and check `"Enable POP for all mail"`.
Using `"delete Google Mail’s copy"` in `"When messages are accessed with POP"` is **required**, because Google doesn't 
support deleting over POP3 (see https://support.google.com/mail/answer/13290?hl=en).

It's good to use separated account and hold it only for needs of acceptance testing.

## Understanding Domain Model

Goal of this extension is to provide convenient way to describe our actions in Behat scenarios (with predefined or 
custom steps, that can be built with provided API). 

And we all already understand main mail concepts, but let's define them again:
* Mail Agent — end user's application to working with mailbox(es) (examples: Apple Mail, Outlook or GMail's web 
interface). Same as Browser in Mink.
* Mailbox — place to receive mail to and/or to deliver mail from (examples: concrete account in GMail or Hotmail, local 
Exim or Postrix on your own server).
* Inbox — folder inside mailbox for incoming mail. Usually you can create additional inbox folders with special filters 
for mail.

## For contributors

You can run bundled Behat features by creating `behat.yml` file inside root directory of the project with POP3 and SMTP 
credentials, for example:

``` yaml
imports:
  - behat.yml.dist

# mailtrap.io is default
default:
  extensions:
    Staffim\Behat\MailExtension\BehatMailExtension:
      pop3_host: mailtrap.io
      pop3_port: 1100
      pop3_user: 12920824f17cf7168
      pop3_password: 7803feb8172ffd
      smtp_host: mailtrap.io
      smtp_port: 25
      smtp_user: 12920824f17df7168
      smtp_password: 7803fea8172ffd

# Gmail
gmail:
  suites:
    default:
      contexts:
        - \Staffim\Behat\MailExtension\Context\MailContext
        - FeatureContext:
          - example@shockov.com
  extensions:
    Staffim\Behat\MailExtension\BehatMailExtension:
      pop3_host: pop.gmail.com
      pop3_port: 995
      pop3_secure: true
      pop3_user: example@shockov.com
      pop3_password: jmeSVEzJRPSo
      smtp_host: smtp.gmail.com
      smtp_port: 465
      smtp_secure: true
      smtp_user: example@shockov.com
      smtp_password: jmeSVEzJRPSo
```

And run Behat like `vendor/bin/behat` or `vendor/bin/behat -p gmail`.
