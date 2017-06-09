Aligent LiveChat Integration Bundle for OroCRM
==============================================

Facts
-----
- version: 1.0.0
- composer name: aligent/oro-livechat-bundle

Description
-----------
This bundle provides an OroCRM integration for the chat service provided by Live 
Chat Inc.  When a customer intiiates a chat, this bundle can process the 
chat_start webhook and provide Oro Contact information for the LiveChat interface.  
On chat_ended webhook we capture the chat transcript and save it to OroCRM 
associating it with the relevant contact.

Installation Instructions
-------------------------
1. Install this module via Composer

        composer require aligent/oro-livechat-bundle

1. Clear cache and run migrations
1. Set a password for the webhook via the Oro System Configuration
1. Log into your LiveChat web interface.  Go to Settings (gear icon in top right), Integrations, Webhooks., 
    1. Create a webhook for the "chat_started" event (leave all options ticked) using the following URL: <https://livechatinc:[PasswordYouSetInStep2]@[YourDomainHere]/livechatinc/webhook/chatStart>
    1. Create a webhook for the "chat_ended" event (leave all options ticked) using the following URL: <https://livechatinc:[PasswordYouSetInStep2]@[YourDomainHere]/livechatinc/webhook/chatEnd>


Support
-------
If you have any issues with this bundle, please create a [pull request](https://github.com/aligent/orocrm-livechat-bundle/pulls) with a failing test that demonstrates the problem you've found.  If you're really stuck, feel free to open [GitHub issue](https://github.com/aligent/orocrm-livechat-bundle/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Jim O'Halloran <jim@aligent.com.au>

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2017 Aligent Consulting