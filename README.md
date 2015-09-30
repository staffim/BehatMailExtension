# Behat Mail Extension

Extension implements classical mail client (like Apple Mail, for example) in PHP with corresponding steps for Behat.

## Using with Mailtrap.io

Mailtrap.io currently is not working with ezcMail (see https://github.com/zetacomponents/Mail/pull/40 for details). You 
can use fork.

## Using with GMail

To enable POP3 access to your GMail account, go to `"Forwarding and POP/IMAP"` and check `"Enable POP for all mail"`.
Using `"delete Google Mail’s copy"` in `"When messages are accessed with POP"` is **required**, because Google doesn't 
support deleting over POP3 (see https://support.google.com/mail/answer/13290?hl=en).

It's good to use separated account and hold it only for needs of acceptance testing.

## Understanding Domain Model

Domain model of this extension includes:
* Mail Agent — service for end user to working with mail (like Apple Mail and Outlook). Same as Browser in Mink.
* SMTP Server — host, port and all data for authentication.
* POP3 Server — host, port and all data for authentication.
* Inbox — virtual folder, concrete filter for all messages set.
* Mail message — concrete letter with attachments and other data.
* Mail — mail messages or concrete mail message.

## Local development (for contributors)

You can run bundled Behat features by creating `behat.yml` file inside root directory with POP3 and SMTP 
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
