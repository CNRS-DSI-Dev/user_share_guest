# User share guest

This application allows users to share files to external members of CNRS, it adds a type of sharing and a user (guest) to the application.

When a user share a file to a guest, a new account is create if the email isn't in MyCore. If the user exists on Labintel, a default account is created.

A guest can only see files that are shared to him and can only download them.

If a guest has no more files shared with him, his account is disabled and an expiration date is setted on the account.
A delay (days) can be defined in the administration If you do not want that accounts be deleted the next day by the cleaning cron

The time before deleting an inactive account can be defined in the administration

3 CRON can be placed for :
- check the activity of the guests (verify) 
- Delete inactive guest accounts (clean)
- Send statistics (mail) to the accounts having shared files to guests

These jobs can be launch on admin page

## Installation

Copy / Past the application's folder to your app folder
On Owncloud, enable the application on "setting/apps"


## Config

If you want to deny access to some part of the site to the guests, add the name of your applications in config.php as follow :

'user_share_guest_forbidden_apps' => array('app1', 'app2')

If you want to allow specifics mail domains, add the domain in administration page :

## Hooks

         Hooks          |     Access to data     |     data modifications     |
------------------------|------------------------|----------------------------|
pre_createguest         |            V           |              V             |
post_createguest        |            V           |                            |
pre_addguestlist        |            V           |              V             |
post_guestlist          |            V           |              V             |
pre_deleteguestshare    |            V           |              V             |
post_deleteguestshare   |            V           |                            |
pre_guestdelete         |            V           |              V             |
post_guestdelete        |            V           |                            |
post_guestsetpassword   |            V           |                            |
------------------------|------------------------|----------------------------|

pre_createguest, pre_deleteguestshare, pre_guestdelete have attributes that could prevent the current action

For more information about the hooks, look in GuestController and PageController pages.

/ ! \ Important / ! \
If you use a custom theme, you maybe have modified the file usershareguestview.js (core/js).
In that case, you must reflect the change in the file js/sahredialogview-surcharged.js.
If you modify the template variable, add changes in js/template.js
The comments /* SURCHARGE */ and /* FIN SURCHARGE */ show where the modifications have been done.
If you want to add other views on the shareDialog, you must specify them in subviews in the initialize function (line 38)


## License and authors

|                      |                                           					|
|:---------------------|:-----------------------------------------------------------|
| **Author:**          | Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
| **Copyright:**       | 2015 CNRS DSI / GLOBALIS media      systems
| **License:**         | AGPL v3, see the COPYING file.
