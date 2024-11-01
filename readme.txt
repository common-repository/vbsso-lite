=== vBSSO-lite ===

Contributors: extremeidea
Tags: sso, single sign-on, login, registration, user management, authentication, vbulletin, bridge
Requires at least: 4.0
Tested up to: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LM25KRQVLRLDS 
Contact Us: https://www.extreme-idea.com/

== Description ==

Looking for SSO tool for your WordPress and vBulletin sites?

Try vBSSO for FREE.

vBSSO-lite - is a plugin that provides universal Secure Single Sign-On between vBulletin and WordPress - allows users to sign in to WordPress using vBulletin credentials.

The plugin consists of two synchronization vBulletin (acts as a Master) and WordPress (acts as a Slave) lightweight extensions where vBulletin holds the master users database and all the user-related operations are managed there.

The list of features:

1. Login.
2. Logout.
3. Registration.
4. The vBSSO footer link is available only on wp-admin.

Read more about <a href="https://www.vbsso.com/">vBSSO</a>.
The plugin is developed and supported by <a href="https://www.extreme-idea.com/">Extreme Idea LLC</a>. Our entire team is ready to help you. Ask your questions in the support forum, or <a href="https://www.extreme-idea.com/contact-us/">contact us directly</a>.

== Installation == 

To install the vBSSO plugin on WordPress: 
1. Log in as administrator to WordPress Admin Panel. 
2. Navigate to Plugins > press Add New button > press Upload Plugin button. 
3. Browse for vBSSO.zip file > press Install Now button. 
4. The plugin should be successfully installed (navigate to Plugins > Settings > navigate to the vBSSO extension). 
5. Enable pretty permalinks URL structure: navigate to Settings > Permalink Settings > Common Settings > choose any URL structure except plain or default > Save (default permalinks will not work).

To install the vBSSO extension on vBulletin site: 
1. Download vBulletin vBSSO (Required). 2. Unzip and upload everything from upload folder to the / root directory of your forum. 3. Log in to forum’s /admincp/ Control Panel as administrator: 
4. Navigate to Plugins & Products section. 
5. Expand section and click on the Manage Products link. 
6. Scroll down right frame until you find Add/Import Product link. 
7. Click on the link and choose for vBSSO.xml file (unpack the archive). 
8. Click on the Import button. 

== Uninstallation ==

To Uninstall the WordPress SSO extension: 
1. Log in as WordPress administrator to WordPress Admin Panel: 
2. Navigate to Plugins > press Installed Plugins button > navigate to the vBSSO extension. 
3. Press Deactivate button. 
4. Press Delete button. The plugin should be successfully deleted. 

To Uninstall the extension via the vBulletin dashboard: 
1. Log in to your forum’s /admincp/ control panel as administrator. 
2. Navigate to the Plugins & Products section. 
3. Expand section and click on the Manage Products link. 
4. Find vBSSO extension and select Uninstall.

== Upgrade Notice ==

To update the plugin:

1. Log in as administrator to Admin Panel.
2. Uninstall the plugin (see Uninstall chapter).
3. (Re)install the plugin (see Install chapter).
The plugin should be successfully re-installed.

== Configuration ==

How to connect the platforms:

CONFIGURATION, CASE 1

1. Log in to WordPress as administrator.
2. Navigate to Settings -> vBSSO.
3. Modify your default Platform Shared Key by setting it to more secure unreadable phrase to encrypt exchanged data.
4. Save Changes.

CONFIGURATION, CASE 2

1. Log in to your vBulletin control panel as administrator.
2. Navigate to vBSSO section.
3. Expand section and click on the Platforms link.
4. Copy Platform Url link and Shared Key field from WordPress installation to vBulletin.
5. Click on Connect button to connect your new platform.
5. Back to WordPress vBSSO Settings page and verify that API Connections fields are filled out.

== Error Log ==

1. Log in to your forum’s /admincp/ control panel as administrator.
2. Navigate to vBSSO section.
3. Expand section and click on the Logging & Notifications link.
4. Set ‘Logging Level’ to ‘All’ and specify your email address to receive the debug information.

If you don’t need to receive debug messages, clean up your email address field and switch ‘Logging Level’ to ‘Warn’.

== Screenshots ==

1. screenshot-1.png

2. screenshot-2.png

== Changelog ==

=  1.4.3 = 2019-03-25

* Added PHP 71, 72, 73 support (Feature #6421).

= 1.0.0 = 2017-05-18

* First release.

<a href=" https://www.extreme-idea.com/plugins/vbsso_connect_for_wordpress">More info about the plugin</a>