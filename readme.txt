=== AutomatePlus - Mautic for WordPress ===
Contributors: brainstormforce, rahulwalunje-1
Donate link: https://www.brainstormforce.com/payment/
Requires at least: 3.0
Tags: mautic, email, contacts, marketing, tags
Stable tag: 1.0.2
Tested up to: 4.7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add registered WP users and commentors to Mautic contacts.

== Description ==

AutomatePlus - Mautic for WordPress allows to add registered WP users and commentors to Mautic contacts using Mautic API. You just need to setup rules and authorize Mautic.

There are triggers and actions for each rule. 

* In trigger part you can select conditions when contacts should be added in mautic either it may be register user on WP or when someone post comment to your site or both.

* In action part you can choose whether add contact in segment, remove from segment or add tags to new mautic contact.

# Configurations

- Go to WordPress Dashboard -> Settings -> AutomatePlus Mautic -> Authenticate
- Enter Mautic Base URL
- Enter Public Key and Secret Key

== Installation ==
# How To Get Mautic API Credentials 

Need help to get Mautic API credentials? Refer [this doc](https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/) to know How to get mautic credentials.

== Changelog ==

= 1.0.2 =
* Optimize code to speed up the process
* Reduce get option calls
* Add spinner on refresh mautic data

= 1.0.1 =
* Fix : Extend Segment display limit.

= 1.0.0 =
* Initial Release.