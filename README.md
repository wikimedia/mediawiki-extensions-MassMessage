# MassMessage

MassMessage is a [MediaWiki](https://www.mediawiki.org/) extension that lets you easily send a talk page message to a large number of users at once. The extension also works over "wikifarm" setups.

## Configuration

    $wgNamespacesToPostIn = [ NS_PROJECT ];

This limits the extension to only posting in talk namespaces (by default) and the **Project:** namespace.

    $wgNamespacesToConvert = [ NS_USER => NS_USER_TALK ];

This allows a user to specify a page in the **User:** namespace, and the extension will automatically convert it to the **User talk:** namespace.

    $wgAllowlistedMassMessageTargets = [];

This is used to specify page IDs for pages that if targeted by a MassMessage will have all but the 'MassMessage exclude category' check bypassed. For example, adding the Main Page (with ID 1) to this list allows MassMessage to post there even if messages can't be posted to the mainspace.

    $wgMassMessageAccountUsername = 'MediaWiki message delivery';

The account name that the extension will post with. If this is an existing account, the extension will automatically take it over.

    $wgAllowGlobalMessaging = true;

Whether to enable sending messages from one wiki to another. Can be disabled on all wikis except one "central wiki" which will keep the log entries in one location.

    $wgMassMessageWikiAliases = [ 'foo-old.example.org' => 'foowiki' ];

A mapping of domain names to their database name. Useful if you have moved or renamed a wiki and need previous input lists to continue working.

Messages are delivered using the [job queue](https://www.mediawiki.org/wiki/Manual:Job_queue). It is recommended that you set up a cron job to empty the queue rather than relying on web requests. You can view how many MassMessage jobs are still queued by visiting `Special:Statistics` on your wiki (under "Other statistics" in the table).


## Usage

Messages are delivered using **Special:MassMessage**, and can be used by anyone with the `massmessage` userrights, which is given to the sysop group by default.

You will be allowed to preview how your message will look, after which a "Submit" button will appear.

The form requires three different fields:

### Input list

The input list provided to the special page must be formatted using a custom parser function.

In the example below, the target page is `[[Project:Noticeboard]]` on your local site.

    {{#target:Project:Noticeboard}}

In this one below, the target is `[[User talk:Example]]` on the "en.wikipedia.org" domain.

    {{#target:User talk:Example|en.wikipedia.org}}

### Subject

The subject line will become the header of your message, and is also used as the edit summary. For this reason, it is limited to 240 bytes.

### Body

The body is the main text of the message. You will be automatically warned if your message is detected to have unclosed HTML tags, but it will not prevent message delivery.

Wikis can choose to add an enforced footer by editing the `massmessage-message-footer` message. In addition to that, a hidden comment will be added containing the user who sent the message, what project it was sent from, and the input list that was used. That comment can be modified by editing the `massmessage-hidden-comment` on the target site.

## License

MassMessage is licensed under the GNU General Public License 2.0 or any later version. You may obtain a copy of this license at <https://www.gnu.org/copyleft/gpl.html>.

## Credits

A full list of contributors can be found in the version control history. The MassMessage extension is based off of code from the [TranslationNotifications extension](https://mediawiki.org/wiki/Extension:TranslationNotifications). Thank you to [MZMcBride](https://en.wikipedia.org/wiki/User:MZMcBride) for helping with the design and implementation of this extension.

Many thanks to the users on [TranslateWiki](https://translatewiki.net) who translated the extension.
