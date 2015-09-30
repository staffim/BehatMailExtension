Feature: Mail
  In order to communicate with other people
  As an Internet user
  I need to be able to send and receive mail

  Scenario: Reading mail
    Given I have an email in my inbox
    And my inbox is "alexey@example.com"
    And should see at least 1 mail message
    And should see mail message with "Reset Your Password" in subject
    And go to "Reset Your Password" mail message
    And should see "Follow this link to restore" in mail message
