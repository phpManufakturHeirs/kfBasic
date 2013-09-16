## kitFramework::kfBasic ##

(c) 2013 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

**0.33** - 2013-09-16

* added ReCaptcha handling and Twig extension
* Introduce kitFilter and add as first filter MailHide with ReCaptcha
* Changed behaviour of the welcome dialog: only admins can access, at first access the user must login to create a kitFramework account and enable auto-login for the future access.
* added path variables to the Twig extension
* changed minimun height for kitCommand iFrames to 5px
* changed static iframe ID to dynamically created IDs - this enable multiple kitCommand iFrames at the same WYSIWYG page

**0.32** - 2013-09-12

* looks like 'BETA' is coming soon ... 8-)

**0.31** - 2013-08-26

* just in progress ...

**0.28** - 2013-08-07

* changed handling of initParameters() in the kitCommand Basic class

**0.27** - 2013-08-07

* changed internal handling for kitCommands
* controllers can now use classes
* fixed a problem with proxies

**0.26** - 2013-08-06

* added support for installations behind a proxy
* restructured the template directory
* removed no longer used code

**0.25** - 2013-08-02

* added support for BlackCat CMS

**0.24** - 2013-08-01

* added the EmbeddedAdministration feature
* switched cURL SSL check off

**0.23** - 2013-07-25

* first beta release

**0.10** - 2013-04-05

* initial release