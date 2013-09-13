# MassMessage

MassMessage is a [MediaWiki](https://www.mediawiki.org/) extension that lets you easily send a talk page message to a large number of users at once. The extension also works over "wikifarm" setups.

## Configuration

    $wgNamespacesToPostIn = array( NS_PROJECT, NS_USER_TALK );

This limits the bot to only posting in the Project: and User talk: namespaces.

    $wgNamespacesToConvert = array( NS_USER => NS_USER_TALK );

This allows a user to specify a page in the User: namespace, and the bot will automatically convert it to the User talk: namespace.

    $wgMassMessageAccountUsername = 'MessengerBot';

The account name that the bot will post with. If this is an existing account, the extension will automatically take it over.

Messages are delivered using the [job queue](https://www.mediawiki.org/wiki/Manual:Job_queue). It is recommended that you set up a cron job to empty the queue rather than relying on web requests. You can view how many MassMessage jobs are still queued by visiting `Special:Version` on your wiki.


## Usage

Messages are delivered using Special:MassMessage, and can be used by anyone with the `massmessage` userright, which is given to the sysop group by default.

You will be allowed to preview how your message will look, after which a "Submit" button will appear.

The form requires three different fields:

### Input list

The input list provided to the special page must be formatted using a custom parser function.


    {{#target:Project:Noticeboard}}

In this example, the target page is `[[Project:Noticeboard]]` on your local site.

    {{#target:User talk:Example|en.wikipedia.org}}

In this one, the target is `[[User talk:Example]]` on the "en.wikipedia.org" domain.

### Subject

The subject line will become the header of your message, and is also used as the edit summary. For this reason, it is limited to 240 bytes.

### Body

The body is the main text of the message. You will be automatically warned if your message is detected to have unclosed HTML tags, but it will not prevent message delivery.

Wikis can choose to add an enforced footer by editing the `massmessage-message-footer` message. In addition to that, a hidden comment will be added containing the user who sent the message, what project it was sent from, and the input list that was used. That comment can be modified by editing the `massmessage-hidden-comment` on the target site.

## License

MassMessage is licensed under the GNU General Public License 2.0 or any later version. You may obtain a copy of this license at <http://www.gnu.org/copyleft/gpl.html>.

## Credits

A full list of contributors can be found in the version control history. The MassMessage extension is based off of code from the [TranslationNotifications extension](https://mediawiki.org/wiki/Extension:TranslationNotifications). Thank you to [MZMcBride](https://en.wikipedia.org/wiki/User:MZMcBride) for helping with the design and implementation of this extension.

Many thanks to the users on [TranslateWiki](https://translatewiki.net) who translated the extension.
