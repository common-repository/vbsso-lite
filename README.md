# vBSSO

Provides universal Secure Single Sign-On between vBulletin and different popular platforms like WordPress.

### Description

Provides universal Single Sign-On feature so that WordPress can use the vBulletin user database to manage authentication
and user profile data. 

The system has two components. First, there’s a vBulletin plugin that creates an interface for authenticating, controlling access, and managing user profile data. The second component is plugins for other platforms (like WordPress) that teach it to talk to vBulletin and exchange data.

Support is available at https://www.vbsso.com only.

This plugin is provided as is. Support and additional customizations are available at an hourly rate.

Plugin doesn't share any user related information with any third party side. It strongly synchronizes the information
between your own platforms connected personally to your vBulletin instance.

Plugin doesn't revert already sync data back to its original state if you decide to disable plugin later.

More details are available at https://www.vbsso.com

The plugin is developed and supported by <a href="https://www.extreme-idea.com/">Extreme Idea LLC</a>. Our entire team is ready to help you. Ask your questions in the support forum, or <a href="https://www.extreme-idea.com/contact-us/">contact us directly</a>.


### Compatibility

| Product | PHP version  | Platform |
| ------- | --- | --- |
| vBSSO WordPress 1.4.3 | 7.1, 7.2 | WordPress 4.0, 4.1, 4.2, 4.3, 4.4, 4.5, 4.7, 4.8, 5.1 |
| vBSSO WordPress 1.4.0 | 5.3, 5.4, 5.5, 5.6, 7.0 | WordPress 4.0, 4.1, 4.2, 4.3, 4.4, 4.5, 4.7, 4.8 |
| vBSSO WordPress 1.3.0 | 5.3, 5.4, 5.5, 5.6, 7.0 | WordPress 4.0, 4.1, 4.2, 4.3, 4.4, 4.5, 4.7 |
| vBSSO WordPress 1.2.11| 5.2, 5.3 | WordPress 3.0, 4.0, 4.1, 4.2, 4.3, 4.4, 4.5 |

### Requirements

* Supported vBulletin versions: 4.0, 4.1 or 4.2.
* Installed PHP cURL, mCrypt extensions.

### Install

Note please: It's best to put the site into Maintenance Mode first and take a backup, before you start making any other changes!

1. Log in as administrator to WordPress Admin Panel.
2. Navigate to Plugins > press Add New button > press Upload Plugin button.
3. Browse for vBSSO.zip file > press Install Now button.
4. The plugin should be successfully installed.
5. Navigate to the `Plugins` section and activate vBSSO plugin (or use your FTP program and copy the plugin folder/files to .../wp-content/plugins folder).
6. Navigate to Appearance > Widgets > and add vBSSO Login Form to appear in your site.
7. Enable pretty permalinks in Settings > Permalinks (default permalinks will not work).

### Upgrade

Note please: It's best to put the site into Maintenance Mode first and take a backup, before you start making any other changes!

1. Uninstall the old plugin installation (see Uninstall chapter).
2. Install the latest plugin version (see Install chapter).

### Uninstall

Note please: It's best to put the site into Maintenance Mode first and take a backup, before you start making any other changes!

1. Log in to WordPress as administrator.
2. Navigate to `Plugins` or `Network -> Plugins` (in case of enabled network) section deactivate and delete vBSSO plugin.
3. (Or use your FTP program to delete the plugin folder/files).

### Configuration

CONFIGURATION, CASE 1

1. Log in to your WordPress control panel as administrator.
2. Navigate to `Settings` - > `vBSSO`.
3. Modify your default Platform Shared Key by setting it to more secure unreadable phrase to encrypt exchanged data.
4. Save Changes.

CONFIGURATION, CASE 2

1. Log in to your vBulletin control panel as administrator.
2. Navigate to `vBSSO` section.
3. Expand section and click on the `Connect Platforms` link.
4. Copy `Platform Url` link and `Shared Key` field from WordPress installation to vBulletin.
5. Click on `Connect` button to connect your new platform.
6. Back to WordPress vBSSO Settings page and verify that API Connections fields are filled out.