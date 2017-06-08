=== AutomatePlug - Mautic for WordPress ===
Contributors: brainstormforce, rahulwalunje-1
Donate link: https://www.paypal.me/BrainstormForce
Requires at least: 3.0
Tags: mautic, email, contacts, marketing, tags
Stable tag: 1.0.5
Tested up to: 4.7.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add registered WP users and commentors to Mautic contacts.

== Description ==

AutomatePlug - Mautic for WordPress allows to add registered WP users and commentors to Mautic contacts using Mautic API. You just need to setup rules and authorize Mautic.

There are triggers and actions for each rule. 

* In trigger part you can select conditions when contacts should be added in mautic either it may be register user on WP or when someone post comment to your site or both.

* In action part you can choose whether add contact in segment, remove from segment or add tags to new mautic contact.

# Configurations

- Go to WordPress Dashboard -> Settings -> AutomatePlug Mautic -> Authenticate
- Enter Mautic Base URL
- Enter Public Key and Secret Key

== Installation ==
# How To Get Mautic API Credentials 

Need help to get Mautic API credentials? Refer [this doc](https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/) to know How to get mautic credentials.

== Screenshots ==
1. Authenticate Mautic API
2. Enable/Disable Mautic Javascipt tracking
3. Rule conditions list
4. Rule actions list

== Changelog ==

= 1.0.5 =
* New: Add approved comment users condition in Rule ( Note : contact tracking will not work for this condtions instead new contact will be created. ).
* New: Mautic Addons Compatibility.
* Fix: Optimize code base and admin file loading.
* Fix: Change refresh mautic data link position.

= 1.0.4 =
* Fix: Removed deprecated Mautic v2.6 error messages.
* New: Added function to check, if contact is exist in Mautic.

= 1.0.3 =
* Fix: contact tracking history issue.
* Fix: error handling in list segments when not authenticated.
* Fix: modify get contact by email call to avoid duplicate contacts.

= 1.0.2 =
* Optimize code to speed up the process.
* Reduce get option calls.
* Add spinner on refresh mautic data.

= 1.0.1 =
* Fix: Extend Segment display limit.

= 1.0.0 =
* Initial Release.