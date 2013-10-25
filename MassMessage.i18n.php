<?php

/**
 * Translations and stuff.
 *
 * @file
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$messages = array();

/** English
 * @author Kunal Mehta
 */
$messages['en'] = array(
	'massmessage' => 'Send mass message',
	'massmessage-desc' => 'Allows users to easily send a message to a list of users',
	'massmessage-form-spamlist' => 'Page containing list of pages to leave a message on:',
	'massmessage-form-subject' => 'Subject of the message (also used as the edit summary):',
	'massmessage-form-message' => 'Body of the message:',
	'massmessage-form-preview' => 'Preview',
	'massmessage-form-submit' => 'Send',
	'massmessage-fieldset-preview' => 'Preview',
	'massmessage-submitted' => 'Your message delivery to {{PLURAL:$1|$1 page|$1 pages}} has been queued.',
	'massmessage-just-preview' => 'This is just a preview. Press "{{int:massmessage-form-submit}}" to send the message.',
	'massmessage-spamlist-doesnotexist' => 'The specified page-list page does not exist.',
	'massmessage-empty-subject' => 'The subject line is empty.',
	'massmessage-empty-message' => 'The message body is empty.',
	'massmessage-form-header' => 'Use the form below to send messages to a specified list. All fields are required.',
	'massmessage-target' => '[//$1$2?title={{urlencode:$3|WIKI}} $3]',
	'massmessage-queued-count' => 'Queued [[Special:MassMessage|mass messages]]',
	'massmessage-hidden-comment' => '<!-- Message sent by User:$1@$2 using the list at $3 -->',
	'massmessage-optout-category' => 'Opted-out of message delivery',
	'massmessage-badhtml' => 'Your message may have {{PLURAL:$2|an unclosed HTML tag|unclosed HTML tags}}: $1.',
	'massmessage-parse-badurl' => '"$1" is not a valid site.',
	'massmessage-parse-badpage' => '"$1" is not a valid page title.',
	'massmessage-global-disallowed' => 'Messages can only be sent to local pages.',
	'right-massmessage' => 'Send a message to multiple users at once',
	'action-massmessage' => 'send a message to multiple users at once',
	'log-name-massmessage' => 'Mass message log',
	'log-description-massmessage' => 'These events track users sending messages through [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sent a message}} to $3',
	'logentry-massmessage-failure' => 'Delivery of "$4" to $3 failed with an error code of <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Delivery of "$4" to $3 was skipped because target was in a namespace that cannot be posted in',
	'logentry-massmessage-skipoptout' => 'Delivery of "$4" to $3 was skipped because target has opted-out of message delivery',
	'logentry-massmessage-skipnouser' => 'Delivery of "$4" to $3 was skipped because user account does not exist',
	'massmessage-cannot-rename' => 'This is a system account and cannot be renamed.',
	'massmessage-message-footer' => '',
	'massmessage-nextsteps' => '[[Special:MassMessage|Send another message]] or [[Special:Statistics|view how many messages are queued]].',
);

/** Message documentation (Message documentation)
 * @author Kunal Mehta
 * @author Nemo bis
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'massmessage' => '{{doc-special|MassMessage}}',
	'massmessage-desc' => '{{desc|name=Mass Message|url=http://www.mediawiki.org/wiki/Extension:MassMessage}}',
	'massmessage-form-spamlist' => 'Label for an inputbox on the special page.',
	'massmessage-form-subject' => 'Label for an inputbox on the special page.',
	'massmessage-form-message' => 'Used as label for a textarea on the special page.',
	'massmessage-form-preview' => 'Label for the preview button on the special page.
{{Identical|Preview}}',
	'massmessage-form-submit' => 'Label for the submit button on the special page.

Used in {{msg-mw|Massmessage-just-preview}}.
{{Identical|Send}}',
	'massmessage-fieldset-preview' => 'Label for the fieldset box around the page preview.
{{Identical|Preview}}',
	'massmessage-submitted' => 'Confirmation message the user sees after the form is submitted successfully and the request is queued in the job queue.

* $1 is the number of deliveries that have been queued.',
	'massmessage-just-preview' => 'Warning to user that what they are seeing is just a preview, and they should hit the send button to actually submit it.

Refers to {{msg-mw|Massmessage-form-submit}}.',
	'massmessage-spamlist-doesnotexist' => 'Error message the user sees if an invalid spamlist is provided.

The spamlist is the page containing list of pages to leave a message on.

This message probably means that said page, as provided by the user, does not exist.',
	'massmessage-empty-subject' => 'Error message the user sees if the "subject" field is empty.',
	'massmessage-empty-message' => 'Error message the user sees if the "message" field is empty.',
	'massmessage-form-header' => 'Introduction text at the top of the form.',
	'massmessage-target' => 'Used to display the {{#target}} parserfunction.
* $1 is the domain (example: "en.wikipedia.org")
* $2 is <code>$wgScriptPath</code> (example: "/w/index.php")
* $3 the page name (example: "User talk:Example")',
	'massmessage-queued-count' => 'Text for row on [[Special:Statistics]].',
	'massmessage-hidden-comment' => 'A hidden HTML comment that is appended after the message. Parameters:
* $1 - the username of the user who sent the message
* $2 - the database name of the wiki from where the message was sent
* $3 - the URL of the input list that was used to send the message',
	'massmessage-optout-category' => 'Name of category which users add pages to that will opt-it out of receiving messages.',
	'massmessage-badhtml' => 'Shown in a JavaScript popup if we detect unclosed HTML tags

* $1 - comma separated list of unclosed HTML tags
* $2 - number of unclosed HTML tags',
	'massmessage-parse-badurl' => 'Error message shown when the url that the user provides is invalid.
* $1 is the url the user provided',
	'massmessage-parse-badpage' => 'Error message shown when the page name that the user provides is invalid.
* $1 is the page title the user provided',
	'massmessage-global-disallowed' => 'Error message shown if the target wiki is not the current wiki and cross-wiki messaging has been disabled (via $wgAllowGlobalMessaging).',
	'right-massmessage' => '{{doc-right|massmessage}}
See also:
* {{msg-mw|Right-massmessage-global}}',
	'action-massmessage' => '{{doc-action|massmessage}}',
	'log-name-massmessage' => 'Log page title',
	'log-description-massmessage' => 'Log page description',
	'logentry-massmessage-send' => '$3 here is the title of a page containing a list of recipients, so an example log entry would look like "Legoktm sent a message to Wikipedia:Wikipedia Signpost/Subscriptions (Signpost for Aug 14, 2013)".
----
{{logentry}}',
	'logentry-massmessage-failure' => '{{logentry}}
* $4 is the subject line of the message
* $5 is the API error code. This will always be in English.',
	'logentry-massmessage-skipbadns' => '{{logentry}}
* $4 - the subject line of the message',
	'logentry-massmessage-skipoptout' => '{{logentry}}
* $4 - the subject line of the message',
	'logentry-massmessage-skipnouser' => '{{logentry}}
* $4 - the subject line of the message',
	'massmessage-cannot-rename' => 'Error message a user sees when they try renaming the bot account.',
	'massmessage-message-footer' => 'The footer that is by added at the end of very message. It is left blank so individual wikis can customize it.',
	'massmessage-nextsteps' => 'Message after {{mw-msg|massmessage-submitted}} on the special page once the form has been submitted.',
);

/** Bulgarian (български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'massmessage-parse-badurl' => '„$1“ не е валиден сайт.',
);

/** Bengali (বাংলা)
 * @author Bellayet
 */
$messages['bn'] = array(
	'massmessage' => 'গণ বার্তা পাঠাও',
	'massmessage-desc' => 'একটি তালিকার ব্যবহারকারীদের সহজে কোনো বার্তা পাঠানোর সহজ ব্যবস্থা',
	'massmessage-form-spamlist' => 'পাতাটিতে একটি পাতার তালিকা রয়েছে যেখানে বার্তা রাখতে হবে।',
	'massmessage-form-subject' => 'বার্তার বিষয়। সম্পাদনা সারাংশ হিসেবেও ব্যবহৃত হবে।',
	'massmessage-form-message' => 'বার্তার মূল অংশ।',
	'massmessage-form-preview' => 'প্রাকদর্শন',
	'massmessage-form-submit' => 'পাঠাও',
	'massmessage-fieldset-preview' => 'প্রাকদর্শন',
	'massmessage-submitted' => 'আপনার বার্তাটি অপেক্ষমান রয়েছে।', # Fuzzy
	'massmessage-empty-subject' => 'বিষয় লাইনটি খালি।',
	'massmessage-empty-message' => 'বার্তার মূল অংশ খালি।',
);

/** Breton (brezhoneg)
 * @author Y-M D
 */
$messages['br'] = array(
	'massmessage' => 'Kas ur gemennadenn a-vras',
	'massmessage-form-preview' => 'Rakwelet',
	'massmessage-form-submit' => 'Kas',
	'massmessage-fieldset-preview' => 'Rakwelet',
	'massmessage-submitted' => 'Lakaet eo bet ho kemennadenn er roll gortoz.', # Fuzzy
	'massmessage-just-preview' => 'N\'eo nemet ur rakweladenn. Pouezit war " {{int:massmessage-form-submit}} " evit kas ar gemennadenn.',
	'right-massmessage' => "Kas ur gemennadenn da veur a implijer d'un taol.",
);

/** Czech (česky)
 * @author Mormegil
 */
$messages['cs'] = array(
	'massmessage-cannot-rename' => 'Toto je systémový účet, který nemůže být přejmenován.',
);

/** German (Deutsch)
 * @author Metalhead64
 * @author Se4598
 */
$messages['de'] = array(
	'massmessage' => 'Massennachricht senden',
	'massmessage-desc' => 'Ermöglicht Benutzern das einfache Versenden von Nachrichten an eine Benutzerliste',
	'massmessage-form-spamlist' => 'Seite, die eine Seitenliste zum Hinterlassen einer Nachricht beinhaltet.',
	'massmessage-form-subject' => 'Betreff der Nachricht. Wird auch als Bearbeitungszusammenfassung verwendet.',
	'massmessage-form-message' => 'Der Textbereich der Nachricht.',
	'massmessage-form-preview' => 'Vorschau',
	'massmessage-form-submit' => 'Senden',
	'massmessage-fieldset-preview' => 'Vorschau',
	'massmessage-submitted' => 'Dein Nachrichtenversand an {{PLURAL:$1|eine Seite|$1 Seiten}} wurde in die Sendewarteschlange eingefügt!',
	'massmessage-just-preview' => 'Dies ist nur eine Vorschau. Klicke auf „{{int:massmessage-form-submit}}“, um die Nachricht abzusenden.',
	'massmessage-spamlist-doesnotexist' => 'Die angegebene Seitenlistenseite ist nicht vorhanden.',
	'massmessage-empty-subject' => 'Die Betreffszeile ist leer.',
	'massmessage-empty-message' => 'Der Nachrichtenkörper ist leer.',
	'massmessage-form-header' => 'Benutze das unten stehende Formular, um Nachrichten an eine angegebene Liste zu senden. Es sind alle Felder erforderlich.',
	'massmessage-queued-count' => '[[Special:MassMessage|Massennachrichten]] in der Warteschlange',
	'massmessage-hidden-comment' => '<!-- Nachricht versandt von Benutzer:$1@$2 durch Verwendung der Liste unter $3 -->',
	'massmessage-optout-category' => 'Von der Nachrichtenzustellung abgemeldet',
	'massmessage-badhtml' => 'Deine Nachricht enthält möglicherweise {{PLURAL:$2|ein nicht geschlossenes HTML-Tag|nicht geschlossene HTML-Tags}}: $1.',
	'massmessage-parse-badurl' => '„$1“ ist keine gültige Website',
	'massmessage-parse-badpage' => '„$1“ ist kein gültiger Seitentitel',
	'massmessage-global-disallowed' => 'Nachrichten können nur an lokale Seiten versandt werden.',
	'right-massmessage' => 'Gleichzeitig Nachrichten an mehrere Benutzer senden',
	'action-massmessage' => 'gleichzeitig Nachrichten an mehrere Benutzer zu senden',
	'log-name-massmessage' => 'Massennachrichten-Logbuch',
	'log-description-massmessage' => 'Dieses Logbuch protokolliert Ereignisse von Benutzern, die Nachrichten von [[Special:MassMessage]] versandt haben.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sandte eine Nachricht}} an $3',
	'logentry-massmessage-failure' => 'Versand von „$4“ nach $3 fehlgeschlagen mit dem Fehlercode <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Der Versand von „$4“ an $3 wurde übersprungen, da sich das Ziel in einem Namensraum befindet, in dem keine Nachrichten gespeichert werden können.',
	'logentry-massmessage-skipoptout' => 'Der Versand von „$4“ an $3 wurde übersprungen, da das Ziel vom Nachrichtenversand ausgeschlossen wurde.',
	'logentry-massmessage-skipnouser' => 'Der Versand von „$4“ an $3 wurde übersprungen, da das Benutzerkonto nicht vorhanden ist.',
	'massmessage-cannot-rename' => 'Dies ist ein Systemkonto und kann nicht umbenannt werden.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Eine weitere Nachricht versenden]] oder [[Special:Statistics|die Anzahl der Nachrichten in der Warteschlange ansehen]].',
);

/** Zazaki (Zazaki)
 * @author Gorizon
 */
$messages['diq'] = array(
	'massmessage' => 'Mass mesac bırş',
	'massmessage-form-preview' => 'Verqayt',
	'massmessage-form-submit' => 'Bırş',
	'massmessage-fieldset-preview' => 'Verqayt',
);

/** Esperanto (Esperanto)
 * @author KuboF
 */
$messages['eo'] = array(
	'massmessage' => 'Sendi amasmesaĝon',
	'massmessage-desc' => 'Permesi al uzantoj facile sendi mesaĝon al listo de uzantoj',
	'massmessage-form-spamlist' => 'Paĝo kun listo de paĝoj en kiuj estu postasita mesaĝo:',
	'massmessage-form-message' => 'Teksto de la mesaĝo:',
	'massmessage-form-preview' => 'Antaŭvidi',
	'massmessage-form-submit' => 'Sendi',
	'massmessage-fieldset-preview' => 'Antaŭvidi',
	'massmessage-spamlist-doesnotexist' => 'La specifita paĝo kun paĝolisto ne ekzistas.',
	'massmessage-empty-message' => 'La mesaĝo ne enhavas tekston.',
	'massmessage-form-header' => 'Uzu la suban formularon por sendi mesaĝon al specifita listo. Ĉiuj kampoj estas postulataj.',
	'right-massmessage' => 'Sendi mesaĝon al multaj uzantoj samtempe',
	'action-massmessage' => 'sendi mesaĝon al multaj uzantoj samtempe',
	'log-name-massmessage' => 'Protokolo de amasmesaĝoj',
);

/** Spanish (español)
 * @author Fitoschido
 * @author Ovruni
 */
$messages['es'] = array(
	'massmessage-form-message' => 'Cuerpo del mensaje:',
	'massmessage-form-preview' => 'Previsualizar',
	'massmessage-form-submit' => 'Enviar',
	'massmessage-fieldset-preview' => 'Previsualización',
	'massmessage-empty-subject' => 'El asunto está vacío.',
	'massmessage-empty-message' => 'El cuerpo del mensaje está vacío.',
	'massmessage-hidden-comment' => '<!-- Mensaje enviado por Usuario:$1@$2 mediante la lista en $3 -->',
	'massmessage-parse-badurl' => '"$1" no es un sitio válido',
	'massmessage-parse-badpage' => '"$1" no es un título de página valido',
	'right-massmessage' => 'Enviar un mensaje a varios usuarios a la vez',
	'action-massmessage' => 'enviar un mensaje a varios usuarios a la vez',
	'logentry-massmessage-send' => '$1 ha enviado un mensaje a $3', # Fuzzy
	'logentry-massmessage-failure' => 'Ha fallado la entrega de «$4» a $3 con el código de error <code>$5</code>',
	'massmessage-cannot-rename' => 'Esta es una cuenta del sistema y no se puede renombrar.',
);

/** Basque (euskara)
 * @author An13sa
 */
$messages['eu'] = array(
	'massmessage-form-submit' => 'Bidali',
);

/** French (français)
 * @author DavidL
 * @author Gomoko
 * @author Rastus Vernon
 * @author Sherbrooke
 */
$messages['fr'] = array(
	'massmessage' => 'Envoyer un message de masse',
	'massmessage-desc' => 'Permet aux utilisateurs d’envoyer facilement un message à une liste d’utilisateurs',
	'massmessage-form-spamlist' => 'Page contenant la liste des pages sur lesquelles laisser un message :',
	'massmessage-form-subject' => 'Sujet du message (utilisé également dans le résumé de la modification) :',
	'massmessage-form-message' => 'Corps du message :',
	'massmessage-form-preview' => 'Aperçu',
	'massmessage-form-submit' => 'Envoyer',
	'massmessage-fieldset-preview' => 'Aperçu',
	'massmessage-submitted' => 'Votre message envoyé à {{PLURAL:$1|$1 page|$1 pages}} a été mis en file.',
	'massmessage-just-preview' => 'Ceci est simplement un aperçu. Appuyez sur « {{int:massmessage-form-submit}} » pour envoyer le message.',
	'massmessage-spamlist-doesnotexist' => 'La page de listes de pages spécifiée n’existe pas.',
	'massmessage-empty-subject' => 'La ligne du sujet est vide.',
	'massmessage-empty-message' => 'Le corps du message est vide.',
	'massmessage-form-header' => 'Utilisez le formulaire ci-dessous pour envoyer des messages à une liste indiquée. Tous les champs sont obligatoires.',
	'massmessage-queued-count' => "[[Special:MassMessage|Messages de masse]] en file d'attente",
	'massmessage-hidden-comment' => '<!-- Message envoyé par User:$1@$2 en utilisant la liste à $3 -->',
	'massmessage-optout-category' => 'Désengagé de la réception de messages',
	'massmessage-badhtml' => 'Votre message semble contenir {{PLURAL:$2|une balise HTML mal fermée|des balises HTML mal fermées}} :  $1.',
	'massmessage-parse-badurl' => '« $1 » n’est pas un site valide',
	'massmessage-parse-badpage' => '« $1 » n’est pas un titre de page valide',
	'massmessage-global-disallowed' => 'Les messages ne peuvent être envoyés qu’aux pages locales.',
	'right-massmessage' => 'Envoyer un message à plusieurs utilisateurs à la fois',
	'action-massmessage' => 'envoyer un message à plusieurs utilisateurs à la fois',
	'log-name-massmessage' => 'Journal des messages de masse',
	'log-description-massmessage' => 'Ces événements tracent les utilisateurs ayant envoyé des messages via [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|a envoyé un message}} à $3',
	'logentry-massmessage-failure' => 'La livraison de « $4 » sur $3 a échoué avec un code d’erreur de <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'L’envoi de « $4 » à $3 a été sauté parce que la cible était dans un espace de noms qui ne peut pas recevoir de message',
	'logentry-massmessage-skipoptout' => 'L’envoi de « $4 » à $3 a été sauté parce que la cible a choisi de ne pas recevoir de messages',
	'logentry-massmessage-skipnouser' => 'L’envoi de « $4 » à $3 a été sauté parce que le compte utilisateur n’existe pas',
	'massmessage-cannot-rename' => 'Ceci est un compte système qui ne peut être renommé.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Envoyer un autre message]] ou [[Special:Statistics|voir combien de messages sont en attente]].',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'massmessage' => 'Enviar unha mensaxe en masa',
	'massmessage-desc' => 'Permite aos usuarios enviar facilmente unha mensaxe a unha lista de usuarios',
	'massmessage-form-spamlist' => 'Páxina que conteña a lista de páxinas nas que deixar a mensaxe:',
	'massmessage-form-subject' => 'Asunto da mensaxe (tamén se usa de resumo de edición):',
	'massmessage-form-message' => 'Corpo da mensaxe:',
	'massmessage-form-preview' => 'Vista previa',
	'massmessage-form-submit' => 'Enviar',
	'massmessage-fieldset-preview' => 'Vista previa',
	'massmessage-submitted' => 'A entrega da súa mensaxe a {{PLURAL:$1|unha páxina|$1 páxinas}} púxose á cola.',
	'massmessage-just-preview' => 'Isto só é unha vista previa. Prema en "{{int:massmessage-form-submit}}" para enviar a mensaxe.',
	'massmessage-spamlist-doesnotexist' => 'A páxina especificada coa lista de páxinas non existe.',
	'massmessage-empty-subject' => 'A liña do asunto está baleira.',
	'massmessage-empty-message' => 'O corpo da mensaxe está baleiro.',
	'massmessage-form-header' => 'Utilice o formulario inferior para enviar mensaxes a unha lista especificada. Todos os campos son obrigatorios.',
	'massmessage-queued-count' => '[[Special:MassMessage|Mensaxes en masa]] na cola de espera',
	'massmessage-hidden-comment' => '<!-- Mensaxe enviada por User:$1@$2 mediante a lista presente en $3 -->',
	'massmessage-optout-category' => 'Excluídos da recepción de mensaxes',
	'massmessage-badhtml' => 'A súa mensaxe pode ter {{PLURAL:$2|unha etiqueta HTML|varias etiquetas HTML}} sen pechar: $1.',
	'massmessage-parse-badurl' => '"$1" non é un sitio válido',
	'massmessage-parse-badpage' => '"$1" non é un título de páxina válido',
	'massmessage-global-disallowed' => 'As mensaxe só se poden enviar a páxinas locais.',
	'right-massmessage' => 'Enviar unha mensaxe a varios usuarios á vez',
	'action-massmessage' => 'enviar unha mensaxe a varios usuarios á vez',
	'log-name-massmessage' => 'Rexistro de mensaxes en masa',
	'log-description-massmessage' => 'Este rexistro garda os usuarios que enviaron mensaxes mediante [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|enviou unha mensaxe}} a $3',
	'logentry-massmessage-failure' => 'A entrega de "$4" a $3 fallou e devolveu o código de erro <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'A entrega de "$4" a $3 saltouse porque o destino estaba nun espazo de nomes no que non se pode publicar',
	'logentry-massmessage-skipoptout' => 'A entrega de "$4" a $3 saltouse porque o destino retirouse da entrega de mensaxes',
	'logentry-massmessage-skipnouser' => 'A entrega de "$4" a $3 saltouse porque a conta de usuario non existe',
	'massmessage-cannot-rename' => 'Esta é unha conta do sistema e non se lle pode mudar o nome.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Envíe outra mensaxe]] ou [[Special:Statistics|consulte a cantidade de mensaxes que están á cola]].',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'massmessage' => 'שליחת הודעה לאנשים מרובים',
	'massmessage-desc' => 'אפשרות לשלוח בקלות הודעה לרשימת משתמשים',
	'massmessage-form-spamlist' => 'דף שמכיל רשימת דפים שאליהם תישלח ההודעה:',
	'massmessage-form-subject' => 'כותרת ההודעה (משמשת גם כתקציר עריכה):',
	'massmessage-form-message' => 'גוף ההודעה:',
	'massmessage-form-preview' => 'תצוגה מקדימה',
	'massmessage-form-submit' => 'שליחה',
	'massmessage-fieldset-preview' => 'תצוגה מקדימה',
	'massmessage-submitted' => 'שליחת ההודעה שלך {{PLURAL:$1|לדף|ל־$1 דפים}} נוספה לתור.',
	'massmessage-just-preview' => 'זוהי רק תצוגה מקדימה. יש ללחות "{{int:massmessage-form-submit}}" כדי לשלוח את ההודעה.',
	'massmessage-spamlist-doesnotexist' => 'הדף עם רשימת הדפים אינו קיים.',
	'massmessage-empty-subject' => 'שורת הנושא ריקה.',
	'massmessage-empty-message' => 'גוף ההודעה ריק.',
	'massmessage-form-header' => 'נא להשתמש בטופס להלן כדי לשלוח הודעות לרשימה מוגדרת. כל השדות נדרשים.',
	'massmessage-queued-count' => '[[Special:MassMessage|הודעות המוניות]] בתור',
	'massmessage-hidden-comment' => '<!-- הודעה שנשלחה על־ידי User:$1@$2 באמצעות הרשימה בדף $3 -->',
	'right-massmessage' => 'שליחה של הודעות למשתמשים מרובים',
	'action-massmessage' => 'לשלוח הודעות למשתמשים רבים',
	'log-name-massmessage' => 'יומן הודעות המוניות',
	'log-description-massmessage' => 'האירועים האלה עוקבים אחרי שליחת הודעות דרך [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|שלח|שלחה}} הודעה אל $3',
);

/** Italian (italiano)
 * @author Beta16
 */
$messages['it'] = array(
	'massmessage' => 'Invia messaggi massivamente',
	'massmessage-desc' => 'Consente agli utenti di inviare facilmente un messaggio a un elenco di utenti',
	'massmessage-form-spamlist' => "Pagina contenente l'elenco delle pagine su cui lasciare un messaggio:",
	'massmessage-form-subject' => "Oggetto del messaggio (anche usato come l'oggetto della modifica):",
	'massmessage-form-message' => 'Corpo del messaggio:',
	'massmessage-form-preview' => 'Anteprima',
	'massmessage-form-submit' => 'Invia',
	'massmessage-fieldset-preview' => 'Anteprima',
	'massmessage-submitted' => "L'invio del tuo messaggio su {{PLURAL:$1|$1 pagina|$1 pagine}} è stato accodato.",
	'massmessage-just-preview' => 'Questa è solo un anteprima. Premi "{{int:massmessage-form-submit}}" per inviare il messaggio.',
	'massmessage-spamlist-doesnotexist' => "La pagina con l'elenco indicata non esiste.",
	'massmessage-empty-subject' => "La riga dell'oggetto è vuota.",
	'massmessage-empty-message' => 'Il corpo del messaggio è vuoto.',
	'massmessage-form-header' => 'Utilizza il modulo sottostante per inviare messaggi a un elenco specificato. Tutti i campi sono obbligatori.',
	'massmessage-queued-count' => '[[Special:MassMessage|Messaggi massivi]] accodati',
	'massmessage-hidden-comment' => "<!-- Messaggio inviato da User:$1@$2 usando l'elenco su $3 -->",
	'massmessage-optout-category' => 'Esclusione dalla ricezione di messaggi massivi',
	'massmessage-badhtml' => 'Il tuo messaggio potrebbe avere {{PLURAL:$2|un|alcuni}} tag HTML non {{PLURAL:$2|chiuso|chiusi}}: $1.',
	'massmessage-parse-badurl' => '"$1" non è un sito valido',
	'massmessage-parse-badpage' => '"$1" non è un titolo di pagina valido',
	'massmessage-global-disallowed' => 'I messaggi possono essere inviati solo alle pagine locali.',
	'right-massmessage' => 'Invia un messaggio a più utenti contemporaneamente',
	'action-massmessage' => 'inviare un messaggio a più utenti contemporaneamente',
	'log-name-massmessage' => 'Messaggi massivi',
	'log-description-massmessage' => "Questi eventi registrano l'invio di messaggi ad utenti tramite [[Special:MassMessage]].",
	'logentry-massmessage-send' => '$1 {{GENDER:$2|ha inviato un messaggio}} a $3',
	'logentry-massmessage-failure' => 'Consegna di "$4" a $3 non riuscita con codice di errore<code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Consegna di "$4" a $3 omessa poiché la destinazione si trova in un namespace su cui non può essere pubblicato',
	'logentry-massmessage-skipoptout' => 'Consegna di "$4" a $3 omessa poiché la destinazione è esclusa dalla ricezione di messaggi massivi',
	'logentry-massmessage-skipnouser' => 'Consegna di "$4" a $3 omessa poiché l\'utenza non esiste',
	'massmessage-cannot-rename' => "Questa è un'utenza di sistema e non può essere rinominata.",
	'massmessage-nextsteps' => '[[Special:MassMessage|Invia un altro messaggio]] o [[Special:Statistics|vedi quanti messaggi sono accodati]].',
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'massmessage' => 'メッセージの一斉送信',
	'massmessage-desc' => '利用者が複数の利用者に簡単にメッセージを送信できるようにする',
	'massmessage-form-spamlist' => 'メッセージを書き込むページの一覧を含むページ:',
	'massmessage-form-subject' => 'メッセージの件名 (編集の要約としても使用されます):',
	'massmessage-form-message' => 'メッセージの本文:',
	'massmessage-form-preview' => 'プレビュー',
	'massmessage-form-submit' => '送信',
	'massmessage-fieldset-preview' => 'プレビュー',
	'massmessage-submitted' => '{{PLURAL:$1|$1 件のページ}}へのメッセージ送信を待ち行列に登録しました。',
	'massmessage-just-preview' => 'これはプレビューしているだけに過ぎません。メッセージを送信するには「{{int:massmessage-form-submit}}」をクリックしてください。',
	'massmessage-spamlist-doesnotexist' => 'ページ一覧として指定したページは存在しません。',
	'massmessage-empty-subject' => '件名を入力していません。',
	'massmessage-empty-message' => 'メッセージの本文を入力していません。',
	'massmessage-form-header' => 'このフォームでは、指定した一覧のページにメッセージを送信できます。すべて必須項目です。',
	'massmessage-queued-count' => '順番待ち中の[[Special:MassMessage|一括送信メッセージ]]',
	'massmessage-hidden-comment' => '<!-- User:$1@$2 が $3 のリストを使用して送信したメッセージ -->',
	'massmessage-badhtml' => 'メッセージ内に、{{PLURAL:$2|閉じられていない HTML タグ}}があります: $1',
	'massmessage-parse-badurl' => '「$1」は有効なサイトではありません。',
	'massmessage-parse-badpage' => '「$1」は有効なページ名ではありません。',
	'massmessage-global-disallowed' => 'メッセージを送信できるのはローカル ページのみです。',
	'right-massmessage' => '複数の利用者に一度にメッセージを送信',
	'action-massmessage' => '複数の利用者へのメッセージの一斉送信',
	'log-name-massmessage' => '一斉メッセージ記録',
	'log-description-massmessage' => 'これらのイベントは、利用者による [[Special:MassMessage]] でのメッセージの送信を追跡します。',
	'logentry-massmessage-send' => '$1 が $3 に{{GENDER:$2|メッセージを送信しました}}',
	'logentry-massmessage-failure' => '$3 に「$4」を送信する際にコード <code>$5</code> のエラーが発生しました',
	'logentry-massmessage-skipbadns' => '宛先の名前空間には投稿できないため、$3 への「$4」の送信がスキップされました',
	'logentry-massmessage-skipnouser' => '利用者アカウントが存在しないため、$3 への「$4」の送信がスキップされました',
	'massmessage-cannot-rename' => 'これはシステム アカウントであるため、名前を変更できません。',
	'massmessage-nextsteps' => '[[Special:MassMessage|別のメッセージを送信できます]]。[[Special:Statistics|待ち行列内のメッセージ数を閲覧することもできます]]。',
);

/** Korean (한국어)
 * @author Hym411
 * @author Kwj2772
 * @author 아라
 */
$messages['ko'] = array(
	'massmessage' => '메시지 대량 보내기',
	'massmessage-desc' => '목록에 있는 사용자에게 쉽게 메시지를 보낼 수 있습니다',
	'massmessage-form-spamlist' => '메시지를 남길 문서의 목록이 있는 문서:',
	'massmessage-form-subject' => '메시지의 제목 (편집 요약에도 쓰임):',
	'massmessage-form-message' => '메시지 본문:',
	'massmessage-form-preview' => '미리 보기',
	'massmessage-form-submit' => '보내기',
	'massmessage-fieldset-preview' => '미리 보기',
	'massmessage-submitted' => '{{PLURAL:$1|문서 $1개}}로 발송할 메시지가 대기열에 추가되었습니다.',
	'massmessage-just-preview' => '이것은 미리보기일 뿐입니다. 메시지를 보내려면 "{{int:massmessage-form-submit}}"를 누르세요.',
	'massmessage-spamlist-doesnotexist' => '지정한 문서 목록의 문서가 존재하지 않습니다.',
	'massmessage-empty-subject' => '제목 줄이 비어 있습니다.',
	'massmessage-empty-message' => '메시지 본문이 비어 있습니다.',
	'massmessage-form-header' => '지정된 목록에서 메시지를 보내려면 아래 양식을 사용하세요. 모든 필드는 필수입니다.',
	'massmessage-queued-count' => '대기 중인 [[Special:MassMessage|대량 메시지]]',
	'massmessage-hidden-comment' => '<!-- $3의 목록을 사용해 User:$1@$2님이 보낸 메시지 -->',
	'massmessage-optout-category' => '메시지 전달을 받지 않는 사용자',
	'massmessage-badhtml' => '당신의 메시지에 {{PLURAL:$2|닫히지 않은 HTML 태그}}가 포함되었을 수 있습니다: $1.',
	'massmessage-parse-badurl' => '"$1"은 올바른 사이트가 아닙니다.',
	'massmessage-parse-badpage' => '"$1"은 올바른 문서 제목이 아닙니다.',
	'massmessage-global-disallowed' => '메시지는 이 위키 내의 문서로만 보낼 수 있습니다.',
	'right-massmessage' => '한 번에 여러 사용자에게 메시지 보내기',
	'action-massmessage' => '한 번에 여러 사용자에게 메시지 보내기',
	'log-name-massmessage' => '대량 메시지 기록',
	'log-description-massmessage' => '이 이벤트는 [[Special:MassMessage]]를 통해 메시지를 보낸 사용자를 추적합니다.',
	'logentry-massmessage-send' => '$1 사용자가 $3에 {{GENDER:$2|메시지를 보냈습니다}}',
	'logentry-massmessage-failure' => '$3에 "$4"(을)를 보내지 못했으며 오류 코드는 <code>$5</code>입니다',
	'logentry-massmessage-skipbadns' => '"$4"의 $3으로의 배달은 해당 이름공간에 문서를 쓸 수 없었기 때문에 넘어갔습니다.',
	'logentry-massmessage-skipoptout' => '"$4"의 $3으로의 배달이 목표가 메시지 배달을 받지 않도록 설정했기 때문에 넘어갔습니다.',
	'logentry-massmessage-skipnouser' => '"$4"의 "$3"으로의 배달이 사용자 계정이 존재하지 않기 때문에 넘어갔습니다.',
	'massmessage-cannot-rename' => '이 계정은 시스템 계정이고 이름을 바꿀 수 없습니다.',
	'massmessage-nextsteps' => '[[Special:MassMessage|다른 메시지 보내기]] 혹은 [[Special:Statistics|얼마나 많은 메시지들이 대기 중인지 보기]]',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 * @author Soued031
 */
$messages['lb'] = array(
	'massmessage-desc' => 'Erlaabt et Benotzer fir einfach Messagen un eng Lëscht vu Benotzer ze schécken',
	'massmessage-form-spamlist' => 'Säit mat der Lëscht vu Säiten op déi e Message geschriwwe gi soll:',
	'massmessage-form-subject' => 'Sujet vum Message (Gëtt och fir de Resumé vun der Ännerung benotzt):',
	'massmessage-form-preview' => 'Kucken ouni ze späicheren',
	'massmessage-form-submit' => 'Schécken',
	'massmessage-fieldset-preview' => 'Kucken ouni ze späicheren',
	'massmessage-just-preview' => 'De Message gëtt just gewisen. Klickt "{{int:massmessage-form-submit}}" fir de Message ze schécken.',
	'massmessage-empty-subject' => "D'Linn mam Sujet ass eidel.",
	'massmessage-empty-message' => 'Den Inhalt vum Message ass eidel.',
	'massmessage-form-header' => 'Benotzt de Formulaire hei drënner fir Messagen un eng spezifesch Lëscht ze schécken. All Felder sinn obligatoresch.',
	'massmessage-hidden-comment' => "<!-- Message gouf vum User:$1@$2 geschéckt, deen d'Lëscht op $3 benotzt huet-->",
	'massmessage-parse-badurl' => '"$1" ass kee valabele Site',
	'massmessage-parse-badpage' => '"$1" ass kee valabelen Titel fir eng Säit',
	'right-massmessage' => 'E Message u méi Benotzer matenee schécken',
	'action-massmessage' => 'e Message u verschidde Benotzer gläichzäiteg schécken',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|huet e Message}} un de(n) $3 geschéckt',
	'logentry-massmessage-failure' => 'D\'Schécke vu(n) "$4" op $3 huet net funktionéiert: de Feelercode ass <code>$5</code>',
	'massmessage-cannot-rename' => 'Dëst ass a System-Benotzerkont, deen net kann ëmbenannt ginn.',
	'massmessage-nextsteps' => "[[Special:MassMessage|Nach e Message schécken]] oder [[Special:Statistics|d'Zuel vu Message weisen déi drop waarde geschéckt ze ginn]].",
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'massmessage' => 'Испраќање на масовна порака',
	'massmessage-desc' => 'Овозможува корисниците да испраќаат масовни пораки на списоци од корисници',
	'massmessage-form-spamlist' => 'Страница со список од страници на кои треба да се остави пораката.',
	'massmessage-form-subject' => 'Наслов на пораката. Ќе се користи и како опис на уредувањето.',
	'massmessage-form-message' => 'Текст на пораката.',
	'massmessage-form-preview' => 'Преглед',
	'massmessage-form-submit' => 'Испрати',
	'massmessage-fieldset-preview' => 'Преглед',
	'massmessage-submitted' => 'Испраќањето на пораката на {{PLURAL:$1|$1 страница|$1 страници}} е ставено во редица.',
	'massmessage-just-preview' => 'Ова е само преглед. Стиснете на „{{int:massmessage-form-submit}}“ за да ја испратите пораката.',
	'massmessage-spamlist-doesnotexist' => 'Укажаната страница со список од страници не постои.',
	'massmessage-empty-subject' => 'Насловот е празен.',
	'massmessage-empty-message' => 'Порака нема текст.',
	'massmessage-form-header' => 'Образецов служи за испраќање на пораки на укажан список на примачи. Сите полиња се задолжителни.',
	'massmessage-queued-count' => '[[Special:MassMessage|Масовни пораки]] во редица',
	'massmessage-hidden-comment' => '<!-- Пораката ја испрати Корисник:$1@$2 преку списокот на $3 -->',
	'massmessage-optout-category' => 'Одлучиле да не примаат известувања',
	'massmessage-badhtml' => 'Пораката може да има {{PLURAL:$2|незатворена ознака за HTML tag|незатворени ознаки за HTML}}: $1.',
	'massmessage-parse-badurl' => '„$1“ не претставува важечко мрежно место',
	'massmessage-parse-badpage' => '„$1“ не претставува важечки наслов на страница',
	'massmessage-global-disallowed' => 'Пораките можат да се испраќаат само на локални страници.',
	'right-massmessage' => 'Испраќање на порака на повеќе корисници наеднаш.',
	'action-massmessage' => 'испраќање порака на повеќе корисници наеднаш',
	'log-name-massmessage' => 'Дневник на масовни пораки',
	'log-description-massmessage' => 'Овој дневник следи испраќања на пораки преку [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|испрати порака}} до $3',
	'logentry-massmessage-failure' => 'Испраќањето на „$4“ на $3 не успеа, со грешка бр. <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Испраќањето на „$4“ на $3 е изоставено бидејќи целта беше во именски простор што не прима пораки',
	'logentry-massmessage-skipoptout' => 'Испраќањето на „$4“ на $3 е изоставено бидејќи целната личност одлучила да не прима пораки',
	'logentry-massmessage-skipnouser' => 'Испраќањето на „$4“ на $3 е изоставено бидејќи корисничката сметка не постои',
	'massmessage-cannot-rename' => 'Ова е системска сметка и затоа не може да се преименува.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Испратете друга порака]] или [[Special:Statistics|погледајте колку пораки стојат во ред]].',
);

/** Malayalam (മലയാളം)
 * @author Akhilan
 * @author Kavya Manohar
 * @author Praveenp
 */
$messages['ml'] = array(
	'massmessage-form-preview' => 'എങ്ങനെയുണ്ടെന്നു കാണുക',
	'massmessage-form-submit' => 'അയക്കുക',
	'massmessage-submitted' => 'താങ്കളുടെ ഇമെയിൽ അയച്ചു കഴിഞ്ഞിരിക്കുന്നു.', # Fuzzy
);

/** Marathi (मराठी)
 * @author V.narsikar
 */
$messages['mr'] = array(
	'massmessage' => 'एकगठ्ठा संदेश पाठवा',
	'massmessage-desc' => 'सदस्यांच्या यादीत असलेल्या सदस्यांना, सोप्या रितीने संदेश पाठविण्यास वापरकर्त्यास  परवानगी देते.',
	'massmessage-form-spamlist' => 'या पानावर संदेश देण्यायोग्य असलेल्या पानांची यादी आहे.',
	'massmessage-form-subject' => 'संदेशाचा विषय. याचा वापर संपादन सारांश म्हणुनही होईल.',
	'massmessage-form-message' => 'संदेशाचा मायना',
	'massmessage-form-preview' => 'झलक',
	'massmessage-form-submit' => 'पाठवा',
	'massmessage-fieldset-preview' => 'झलक',
	'massmessage-submitted' => 'आपल्या संदेशास रांगेत ठेविल्या गेले आहे!', # Fuzzy
	'massmessage-just-preview' => 'ही फक्त झलक आहे. संदेश पाठविण्यास "{{int:massmessage-form-submit}}" हे बटन दाबा.',
	'massmessage-spamlist-doesnotexist' => 'उल्लेखित पान-यादी असलेले पान अस्तित्वात नाही.',
	'massmessage-empty-subject' => 'विषय रिकामा आहे.',
	'massmessage-empty-message' => 'संदेशाचा मायना रिकामा आहे.',
	'massmessage-form-header' => 'खालील निवेदन एका उल्लेखित यादीस संदेश पाठविण्यास वापरा.सर्व क्षेत्रे आवश्यक आहेत.',
	'massmessage-queued-count' => 'रांगेतील [[Special:MassMessage|एकगठ्ठा संदेश]]',
	'right-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'action-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'log-name-massmessage' => 'एकगठ्ठा संदेशाच्या नोंदी',
	'log-description-massmessage' => 'हे प्रसंग,[[Special:MassMessage]] मार्फत संदेश पाठविणाऱ्या सदस्यांचा थांग (ट्रॅक) लावतात.',
	'logentry-massmessage-send' => '$1 ने $3 ला{{GENDER:$2|संदेश पाठविला}}',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'massmessage' => 'Hantar pesanan beramai-ramai',
	'massmessage-desc' => 'Membenarkan pengguna untuk menghantar pesanan dengan mudah kepada satu senarai pengguna',
	'massmessage-form-spamlist' => 'Halaman yang mengandungi senarai halaman untuk ditinggalkan pesanan:',
	'massmessage-form-subject' => 'Tajuk pesanan (juga digunakan sebagai ringkasan suntingan):',
	'massmessage-form-message' => 'Isi kandungan pesanan:',
	'massmessage-form-preview' => 'Pralihat',
	'massmessage-form-submit' => 'Hantar',
	'massmessage-fieldset-preview' => 'Pralihat',
	'massmessage-submitted' => 'Pengiriman pesanan anda kepada $1 halaman telah dibaris-gilirkan.',
	'massmessage-just-preview' => 'Ini sekadar pratayangan. Tekan "{{int:massmessage-form-submit}}" untuk menghantar pesanan.',
	'massmessage-spamlist-doesnotexist' => 'Halaman senarai halaman yang dinyatakan tidak wujud.',
	'massmessage-empty-subject' => 'Baris tajuk kosong.',
	'massmessage-empty-message' => 'Ruangan isi pesanan kosong.',
	'massmessage-form-header' => 'Gunakan borang yang berikut untuk mengirim pesanan kepada senarai yang tertentu. semua ruangan wajib diisi.',
	'massmessage-queued-count' => '[[Special:MassMessage|Pesanan beramai-ramai]] terbaris gilir',
	'massmessage-hidden-comment' => '<!-- Pesanan dihantar oleh Pengguna:$1@$2 yang menggunakan senarai di $3 -->',
	'massmessage-optout-category' => 'Terkecuali dari pengiriman pesanan',
	'massmessage-badhtml' => 'Pesanan anda mungkin mengandungi {{PLURAL:$2|suatu teg HTML yang tidak tertutup|teg-teg HTML yang tidak tertutup}}: $1.',
	'massmessage-parse-badurl' => '"$1" bukan laman web yang sah',
	'massmessage-parse-badpage' => '"$1" bukan tajuk halaman yang sah',
	'right-massmessage' => 'Mengirim pesanan kepada berbilang pengguna sekali gus',
	'action-massmessage' => 'menghantar pesanan kepada berbilang pengguna sekali gus',
	'log-name-massmessage' => 'Log pesanan beramai-ramai',
	'log-description-massmessage' => 'Peristiwa-peristiwa ini menjejak pengguna yang mengirim pesanan melalui [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 telah {{GENDER:$2|menghantar}} pesanan kepada $3',
	'logentry-massmessage-failure' => '"$4" gagal dikirim kepada $3 dengan kod ralat <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Pengiriman "$4" kepada $3 dilangkau kerana sasaran tergolong dalam ruang nama yang tidak boleh dikirimi',
	'logentry-massmessage-skipoptout' => 'Pengiriman "$4" kepada $3 dilangkau kerana sasaran mengecualikan diri daripada pengiriman pesanan.',
	'logentry-massmessage-skipnouser' => 'Pengiriman "$4" kepada $3 dilangkau kerana akaun pengguna tidak wujud',
	'massmessage-cannot-rename' => 'Ini ialah akaun sistem yang tidak boleh ditukar namanya.',
);

/** Dutch (Nederlands)
 * @author Hansmuller
 * @author Konovalov
 * @author Siebrand
 */
$messages['nl'] = array(
	'massmessage' => 'Bulkberichten verzenden',
	'massmessage-desc' => 'Maakt het mogelijk om berichten naar een lijst ontvangers te verzenden',
	'massmessage-form-spamlist' => "Pagina met een lijst met pagina's om een bericht op te plaatsen:",
	'massmessage-form-subject' => 'Onderwerp voor bericht (ook gebruikt als bewerkingssamenvatting):',
	'massmessage-form-message' => 'Hoofdtekst van bericht:',
	'massmessage-form-preview' => 'Voorvertoning',
	'massmessage-form-submit' => 'Verzenden',
	'massmessage-fieldset-preview' => 'Voorvertoning',
	'massmessage-submitted' => "Uw bericht voor {{PLURAL:$1|één pagina|$1 pagina's}} is in de wachtrij geplaatst.",
	'massmessage-just-preview' => 'Dit is enkel een voorvertoning. Klik op "{{int:massmessage-form-submit}}" om het bericht te verzenden.',
	'massmessage-spamlist-doesnotexist' => 'De opgegeven paginalijst bestaat niet.',
	'massmessage-empty-subject' => 'Er wordt geen onderwerp aangegeven.',
	'massmessage-empty-message' => 'Het bericht bevat geen tekst.',
	'massmessage-form-header' => 'Gebruiker het onderstaande formulier om berichten te verzenden aan een lijst ontvangers. Alle velden zijn verplicht.',
	'massmessage-parse-badurl' => '"$1" is geen geldige site.',
	'massmessage-parse-badpage' => '"$1" is geen geldige paginanaam.',
	'right-massmessage' => 'Berichten verzenden aan meerdere ontvangers tegelijk',
	'action-massmessage' => 'berichten te verzenden aan meerdere ontvangers tegelijk',
	'log-name-massmessage' => 'Bulkberichtenlogboek',
	'log-description-massmessage' => 'Deze gebeurtenissen zijn gerelateerd aan verzonden berichten via de functie [[Special:MassMessage|bulkberichten]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|heeft}} een bericht geplaatst op $3',
	'massmessage-nextsteps' => '[[Special:MassMessage|Nog een bericht verzenden]] of [[Special:Statistics|bekijken hoeveel berichten er in de wachtrij staan]].',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'massmessage' => 'Mandar un messatge de massa',
	'massmessage-desc' => 'Permet als utilizaires de mandar aisadament un messatge a una lista d’utilizaires',
	'massmessage-form-spamlist' => 'Pagina que conten la lista de las paginas sus las qualas cal daissar un messatge :',
	'massmessage-form-subject' => 'Subjècte del messatge (utilizat tanben dins lo resumit de la modificacion) :',
	'massmessage-form-message' => 'Còs del messatge :',
	'massmessage-form-preview' => 'Apercebut',
	'massmessage-form-submit' => 'Mandar',
	'massmessage-fieldset-preview' => 'Apercebut',
	'massmessage-submitted' => "Vòstre mandadís es estat mes dins la fila d'espèra.", # Fuzzy
	'massmessage-just-preview' => "Aquò's simplament un apercebut. Quichatz sus « {{int:massmessage-form-submit}} » per mandar lo messatge.",
	'massmessage-spamlist-doesnotexist' => 'La pagina de listas de paginas especificada existís pas.',
	'massmessage-empty-subject' => 'La linha del subjècte es voida.',
	'massmessage-empty-message' => 'Lo còs del messatge es void.',
	'massmessage-form-header' => 'Utilizatz lo formulari çaijós per mandar de messatges a una lista indicada. Totes los camps son obligatòris.',
	'massmessage-queued-count' => "[[Special:MassMessage|Messatges de massa]] en fila d'espèra",
	'massmessage-hidden-comment' => '<!-- Message mandat per User:$1@$2 en utilizant la lista a $3 -->',
	'massmessage-optout-category' => 'Desengatjat de la recepcion de messatges',
	'right-massmessage' => "Mandar un messatge a mantun utilizaire a l'encòp",
	'action-massmessage' => "mandar un messatge a mantun utilizaire a l'encòp",
	'log-name-massmessage' => 'Jornal dels messatges de massa',
	'log-description-massmessage' => "Aqueles eveniments traçan los utilizaires qu'an mandat de messatges via [[Special:MassMessage]].",
	'logentry-massmessage-send' => '$1 {{GENDER:$2|a mandat un messatge}} a $3',
	'logentry-massmessage-failure' => 'La liurason de « $4 » sus $3 a fracassat amb un còde d’error de <code>$5</code>',
	'massmessage-cannot-rename' => "Aquò's un compte sistèma que pòt pas èsser renomenat.",
);

/** Polish (polski)
 * @author Chrumps
 * @author WTM
 * @author Woytecr
 */
$messages['pl'] = array(
	'massmessage' => 'Wyślij masową wiadomość',
	'massmessage-desc' => 'Pozwala użytkownikom na wysłanie wiadomości do określonej listy użytkowników',
	'massmessage-form-spamlist' => 'Strona zawierająca listę stron, na których zostawić wiadomość:',
	'massmessage-form-subject' => 'Temat wiadomości (Także używany jako podsumowanie edycji):',
	'massmessage-form-message' => 'Treść wiadomości:',
	'massmessage-form-preview' => 'Podgląd',
	'massmessage-form-submit' => 'Wyślij',
	'massmessage-fieldset-preview' => 'Podgląd',
	'massmessage-submitted' => 'Twoja wiadomość została umieszczona w kolejce.', # Fuzzy
	'massmessage-just-preview' => 'To jest tylko podgląd. Naciśnij "{{int:massmessage-form-submit}}" aby wysłać wiadomość.',
	'massmessage-spamlist-doesnotexist' => 'Określona strona z listą stron nie istnieje.',
	'massmessage-empty-subject' => 'Pole tematu jest puste.',
	'massmessage-empty-message' => 'Treść wiadomości jest pusta.',
	'massmessage-form-header' => 'Użyj poniższego formularza aby wysłać wiadomości do określonej listy. Wszystkie pola są wymagane.',
	'massmessage-parse-badpage' => '„$1” nie jest poprawnym tytułem strony',
	'right-massmessage' => 'Wyślij wiadomość do wielu użytkowników jednocześnie',
	'action-massmessage' => 'wyślij wiadomość do wielu użytkowników jednocześnie',
	'log-name-massmessage' => 'Log masowych wiadomości',
	'log-description-massmessage' => 'To jest lista zdarzeń służąca do śledzenia wysyłanych wiadomości poprzez [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|wysłał|wysłała}} wiadomość do $3',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'massmessage' => 'ډله ايز پيغام لېږل',
	'massmessage-form-message' => 'د پيغام جوسه',
	'massmessage-form-preview' => 'مخليدنه',
	'massmessage-form-submit' => 'لېږل',
	'massmessage-fieldset-preview' => 'مخليدنه',
	'massmessage-submitted' => 'ستاسو پيغام ولېږل شو!', # Fuzzy
	'log-name-massmessage' => 'ډله ايز پيغام يادښت',
	'logentry-massmessage-send' => '$1، $3 ته، {{GENDER:$2|يو پيغام ولېږه}}',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Cainamarques
 * @author Fúlvio
 * @author Luckas
 */
$messages['pt-br'] = array(
	'massmessage' => 'Enviar mensagem em massa',
	'massmessage-desc' => 'Permite que os usuários enviem facilmente uma mensagem para uma lista de usuários',
	'massmessage-form-spamlist' => 'Página que contêm a lista de páginas para enviar uma mensagem em:',
	'massmessage-form-subject' => 'Assunto da mensagem (Também usado como sumário de edição):',
	'massmessage-form-message' => 'Corpo da mensagem:',
	'massmessage-form-preview' => 'Visualizar',
	'massmessage-form-submit' => 'Enviar',
	'massmessage-fieldset-preview' => 'Visualização',
	'massmessage-submitted' => 'Sua mensagem foi adicionada à fila.', # Fuzzy
	'massmessage-just-preview' => 'Esta é apenas uma visualização. Pressione "{{int:massmessage-form-submit}}" para enviar a mensagem.',
	'massmessage-spamlist-doesnotexist' => 'A lista de páginas especificada não existe.',
	'massmessage-empty-subject' => 'O espaço do assunto está vazio.',
	'massmessage-empty-message' => 'O corpo da mensagem está vazio.',
	'massmessage-form-header' => 'Use o formulário abaixo para enviar mensagens a uma lista específcia. Todos os campos são obrigatórios.',
	'massmessage-hidden-comment' => '<!-- Mensagem enviada pelo usuário:$1@$2 utilizando a lista $3 -->',
	'right-massmessage' => 'Envie uma mensagem para vários usuários ao mesmo tempo',
	'action-massmessage' => 'envie uma mensagem para vários usuários ao mesmo tempo',
	'log-name-massmessage' => 'Registro de mensagens em massa',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|envie uma mensagem}} para $3',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'massmessage' => 'Manne messàgge de masse',
	'massmessage-desc' => "Permette a l'utinde de mannà messàgge de masse facile facile a 'n'elenghe de utinde",
	'massmessage-form-spamlist' => "Pàgene ca tène 'n'elenghe de pàggene pe lassà 'nu messàgge sus a:",
	'massmessage-form-subject' => "Oggette d'u messàgge (pure ausate cumme riepiloghe d'u cangiamende):",
	'massmessage-form-message' => "Cuèrpe d'u messàgge:",
	'massmessage-form-preview' => 'Andeprime',
	'massmessage-form-submit' => 'Manne',
	'massmessage-fieldset-preview' => 'Andeprime',
	'massmessage-submitted' => "'U messàgge tune ha state mannate a  {{PLURAL:$1|$1 pàgene|$1 pàggene}} in code.",
	'massmessage-just-preview' => "Queste jè sulamende 'n'andeprime. Cazze \"{{int:massmessage-form-submit}}\" pe mannà 'u messàgge.",
	'massmessage-spamlist-doesnotexist' => "'A pàgene de l'elenghe specificate de le pàggene non g'esiste.",
	'massmessage-empty-subject' => "'A linèe de l'oggette jè vacande.",
	'massmessage-empty-message' => "'U cuèrpe d'u messàgge jè vacande.",
	'massmessage-form-header' => "Ause 'u module aqquà sotte pe mannà messàgge a 'n'elenghe specifiche. Tutte le cambe sò richieste.",
	'massmessage-queued-count' => 'Accodate [[Special:MassMessage|messàgge de masse]]',
	'massmessage-hidden-comment' => "<!-- Messàgge mannate da l'Utende:$1@$2 ausanne l'elenghe a $3 -->",
	'massmessage-optout-category' => "Scartate da l'invie de le messàgge",
	'massmessage-badhtml' => "'U messàgge tune pò tenè {{PLURAL:$2|'nu tag HTML non achiuse|tag HTML non achiuse}}: $1.",
	'massmessage-parse-badurl' => '"$1" non g\'è \'nu site valide',
	'massmessage-parse-badpage' => '"$1" non g\'è \'nu titole d\'a vôsce valide',
	'right-massmessage' => "Manne 'nu messàgge a cchiù utinde jndr'à 'na botte",
	'action-massmessage' => "manne 'nu messàgge a cchiù utinde jndr'à 'na botte",
	'log-name-massmessage' => 'Archivije de le messàgge de masse',
	'log-description-massmessage' => "Ste avveneminde traccene l'utinde ca mannane messàgge cu [[Special:MassMessage]].",
	'logentry-massmessage-send' => "$1 {{GENDER:$2|mannate 'nu messàgge}} a $3",
	'logentry-massmessage-failure' => 'Invìe de "$4" a $3 fallite cu \'nu codece de errore <code>$5</code>',
	'logentry-massmessage-skipbadns' => '\'A spedizione de "$4" a $3 ha state zumbate purcé \'a destinazione stè jndr\'à \'nu namespace ca non ge pozze mannà',
	'logentry-massmessage-skipoptout' => '\'A spedizione de "$4" a $3 ha state zumbate purcé \'a destinazione non ge vole le messàgge de masse',
	'logentry-massmessage-skipnouser' => '\'A spedizione de "$4" a $3 ha state zumbate purcé \'u cunde utende non g\'esiste',
	'massmessage-cannot-rename' => "Quiste jè 'nu cunde de sisteme e non ge pò essere renominate.",
);

/** Russian (русский)
 * @author Okras
 */
$messages['ru'] = array(
	'massmessage-desc' => 'Позволяет легко отправить сообщение списку пользователей',
	'massmessage-form-spamlist' => 'Страница, содержащая список страниц, на которых необходимо оставить сообщение:',
	'massmessage-form-subject' => 'Тема сообщения (также используется для описания изменений):',
	'massmessage-form-message' => 'Текст сообщения:',
	'massmessage-form-preview' => 'Предпросмотр',
	'massmessage-form-submit' => 'Отправить',
	'massmessage-fieldset-preview' => 'Предпросмотр',
	'massmessage-hidden-comment' => '<!-- Сообщение отправил Участник:$1@$2, используя список на странице $3 -->',
	'massmessage-badhtml' => 'Ваше сообщение может содержать {{PLURAL:$2|незакрытый HTML-тег|незакрытые HTML-теги}}: $1.',
	'massmessage-cannot-rename' => 'Это системная учётная запись, и она не может быть переименована.',
);

/** Slovenian (slovenščina)
 * @author Eleassar
 */
$messages['sl'] = array(
	'massmessage-submitted' => 'Dostava vašega sporočila na {{PLURAL:$1|$1 stran|$1 strani}} je bila vnesena v čakalno vrsto.',
	'massmessage-parse-badurl' => '»$1« ni veljavna stran',
	'massmessage-parse-badpage' => '»$1« ni veljaven naslov strani',
	'logentry-massmessage-skipbadns' => 'Dostava »$4« na stran $3 je bila preskočena, ker je bil cilj v imenskem prostoru, v katerem ni mogoče objavljati',
	'logentry-massmessage-skipoptout' => 'Dostava »$4« na stran $3 je bila preskočena, ker je cilj zavrnil dostavo sporočil',
	'logentry-massmessage-skipnouser' => 'Dostava »$4« na $3 je bila preskočena, ker uporabniški račun ne obstaja',
);

/** Swedish (svenska)
 * @author Bengt B
 * @author Jopparn
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'massmessage' => 'Skicka massmeddelande',
	'massmessage-desc' => 'Tillåter användare att enkelt skicka ett meddelande till en lista över användare',
	'massmessage-form-spamlist' => 'Sidan innehåller en lista över sidor att lämna ett meddelande på.',
	'massmessage-form-subject' => 'Ämnet för meddelandet. Används också som redigeringskommentar.',
	'massmessage-form-message' => 'Meddelandetexten.',
	'massmessage-form-preview' => 'Förhandsgranska',
	'massmessage-form-submit' => 'Skicka',
	'massmessage-fieldset-preview' => 'Förhandsgranska',
	'massmessage-submitted' => 'Ditt meddelandeleverans till {{PLURAL:$1|$1 sida|$1 sidor}} har placerats i kö.',
	'massmessage-just-preview' => 'Detta är bara en förhandsgranskning. Tryck på "{{int:massmessage-form-skicka}}" för att skicka meddelandet.',
	'massmessage-spamlist-doesnotexist' => 'Den angivna sidan, som innehåller listan med sidor, existerar inte.',
	'massmessage-empty-subject' => 'Ämnesraden är tom.',
	'massmessage-empty-message' => 'Meddelandets innehåll är tomt.',
	'massmessage-form-header' => 'Använd formuläret nedan för att skicka meddelanden till en angiven lista. Alla fält är obligatoriska.',
	'massmessage-queued-count' => 'Köade [[Special:MassMessage|massmeddelanden]]',
	'massmessage-hidden-comment' => '<!-- Meddelande skickades av User:$1@$2 med hjälp av listan på $3 -->',
	'massmessage-optout-category' => 'Valt bort meddelandeleverans',
	'massmessage-badhtml' => 'Ditt meddelande kan ha {{PLURAL:$2|oavslutat HTML-tagg|oavslutade HTML-taggar}}: $1.',
	'massmessage-parse-badurl' => '"$1" är inte en giltig webbplats',
	'massmessage-parse-badpage' => '"$1" är inte en giltig sidtitel',
	'massmessage-global-disallowed' => 'Meddelanden kan bara skickas till lokala sidor.',
	'right-massmessage' => 'Skicka ett meddelande till flera användare på en gång',
	'action-massmessage' => 'skicka ett meddelande till flera användare på en gång',
	'log-name-massmessage' => 'Massmeddelandelogg',
	'log-description-massmessage' => 'Dessa händelser spårar användare som skickar meddelanden via [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|skicka ett meddelande}} till $3',
	'logentry-massmessage-failure' => 'Leveransen av "$4" till $3 misslyckades med felkoden <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Leverans av "$4" till $3 hoppades över eftersom målet var i en namnrymd där det inte kan publiceras',
	'logentry-massmessage-skipoptout' => 'Leverans av "$4" till $3 hoppades över eftersom målet har valt att inte ta emot meddelanden',
	'logentry-massmessage-skipnouser' => 'Leverans av "$4" till $3 hoppades över eftersom användarkontot inte existerar',
	'massmessage-cannot-rename' => 'Detta är ett systemkonto och kan inte byta namn.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Skicka ett annat meddelande]] eller [[Special:Statistics|se hur många meddelanden som är köade]].',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'massmessage-form-submit' => 'పంపించు',
);

/** Turkish (Türkçe)
 * @author Emperyan
 */
$messages['tr'] = array(
	'massmessage' => 'Toplu ileti gönderin',
	'massmessage-form-subject' => 'İleti konusu (Değişiklik özeti olarak da kullanılır)',
	'massmessage-form-message' => 'İleti metni:',
	'massmessage-form-preview' => 'Ön izleme',
	'massmessage-form-submit' => 'Gönder',
	'massmessage-fieldset-preview' => 'Ön izleme',
	'massmessage-submitted' => 'İletiniz sıraya eklendi.', # Fuzzy
	'massmessage-just-preview' => 'Bu yalnızca bir ön izlemedir. İletiyi göndermek için "{{int:massmessage-form-submit}}" düğmesine basınız.',
	'massmessage-spamlist-doesnotexist' => 'Belirtilen sayfa-liste sayfası yok.',
	'massmessage-empty-subject' => 'Konu satırı boş.',
	'massmessage-empty-message' => 'İleti metni boş.',
	'massmessage-form-header' => 'Belirtilen listeye ileti göndermek için aşağıdaki formu kullanın. Bütün alanların doldurulması zorunludur.',
	'right-massmessage' => 'Aynı anda birden fazla kullanıcıya ileti gönder',
	'action-massmessage' => 'aynı anda birden fazla kullanıcıya ileti gönder',
	'log-name-massmessage' => 'Toplu ileti günlüğü',
);

/** Ukrainian (українська)
 * @author Andriykopanytsia
 */
$messages['uk'] = array(
	'massmessage' => 'Відправити масове повідомлення',
	'massmessage-desc' => 'Дозволяє користувачам легко відправляти повідомлення списку користувачів',
	'massmessage-form-spamlist' => 'Сторінки, яка містить список сторінок, щоб залишити повідомлення на:',
	'massmessage-form-subject' => 'Тема повідомлення (також використовується в якості резюме редагування):',
	'massmessage-form-message' => 'Текст повідомлення:',
	'massmessage-form-preview' => 'Попередній перегляд',
	'massmessage-form-submit' => 'Надіслати',
	'massmessage-fieldset-preview' => 'Попередній перегляд',
	'massmessage-submitted' => 'Доставка вашого повідомлення на {{PLURAL:$1|$1 сторінку|$1 сторінки|$1 сторінок}} поставлена в чергу.',
	'massmessage-just-preview' => 'Це тільки попередній перегляд. Натисніть "{{int:massmessage-form-submit}}", щоб відправити повідомлення.',
	'massmessage-spamlist-doesnotexist' => 'Указаної сторінки з переліком сторінок не існує.',
	'massmessage-empty-subject' => 'Рядок теми порожній.',
	'massmessage-empty-message' => 'Тіла повідомлення порожнє.',
	'massmessage-form-header' => "Використовуйте форму нижче, щоб надсилати повідомлення до вказаного списку. Всі поля обов'язкові для заповнення.",
	'massmessage-queued-count' => 'У черзі [[Special:MassMessage|масових повідомлень]]',
	'massmessage-hidden-comment' => '<!-- Повідомлення надіслано користувачем:$1@$2 використовується список у $3 -->',
	'massmessage-optout-category' => 'Відмова від доставки повідомлень',
	'massmessage-badhtml' => 'Ваше повідомлення, можливо, має {{PLURAL:$2|незакритий теґ HTML|незакриті теґи HTML}}:  $1 .',
	'massmessage-parse-badurl' => '"$1" не є коректним сайтом',
	'massmessage-parse-badpage' => '"$1" не є коректною назвою сторінки',
	'massmessage-global-disallowed' => 'Повідомлення можна надіслати лише до місцевих сторінок.',
	'right-massmessage' => 'Надіслати повідомлення кільком користувачам одночасно',
	'action-massmessage' => 'надіслати повідомлення кільком користувачам одночасно',
	'log-name-massmessage' => 'Журнал масових повідомлень',
	'log-description-massmessage' => 'Ці події відстежують надсилання повідомлень користувачами через [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|надіслав повідомлення|надіслала повідомлення}} до $3',
	'logentry-massmessage-failure' => 'Доставка „$4“ на $3 провалилася з кодом помилки<code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Доставка "$4" до $3 була пропущена, бо ціль була в просторі імен, які не можуть бути розміщені в',
	'logentry-massmessage-skipoptout' => 'Доставка "$4" до $3 була пропущена, бо ціль відмовила у доставці повідомлень',
	'logentry-massmessage-skipnouser' => 'Доставка „$4“ до  $3  була пропущено, бо обліковий запис користувача відсутній',
	'massmessage-cannot-rename' => 'Це - системний обліковий запис і його не можна перейменувати.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Надіслати інше повідомлення]] або [[Special:Statistics|переглянути скільки повідомлень у черзі]].',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'massmessage' => 'Nhắn tin cho nhiều người',
	'massmessage-desc' => 'Cho phép người dùng nhắn tin dễ dàng cho một danh sách người dùng',
	'massmessage-form-spamlist' => 'Trang chứa danh sách các trang để nhắn tin:',
	'massmessage-form-subject' => 'Tiêu đề tin nhắn (cũng tóm lược sửa đổi):',
	'massmessage-form-message' => 'Nội dung tin nhắn:',
	'massmessage-form-preview' => 'Xem trước',
	'massmessage-form-submit' => 'Nhắn',
	'massmessage-fieldset-preview' => 'Xem trước',
	'massmessage-submitted' => 'Tin nhắn của bạn cho $1 trang đã được xếp hàng đợi.',
	'massmessage-just-preview' => 'Đây chỉ là một bản xem trước. Hãy bấm “{{int:massmessage-form-submit}}” để gửi tin nhắn.',
	'massmessage-spamlist-doesnotexist' => 'Trang để lấy danh sách trang không tồn tại.',
	'massmessage-empty-subject' => 'Tin nhắn không có tiêu đề.',
	'massmessage-empty-message' => 'Tin nhắn không có nội dung.',
	'massmessage-form-header' => 'Hãy điền biểu mẫu sau để nhắn tin cho một danh sách được định rõ. Tất cả các trường là bắt buộc.',
	'massmessage-queued-count' => 'Số [[Special:MassMessage|tin nhắn cho nhiều người]] trong hàng đợi',
	'massmessage-hidden-comment' => '<!-- Tin nhắn của Thành viên:$1@$2 gửi cho mọi người trong danh sách tại $3 -->',
	'massmessage-optout-category' => 'Đã chọn không nhận tin nhắn',
	'massmessage-badhtml' => 'Tin nhắn của bạn có thể không đóng {{PLURAL:$2|một thẻ HTML|các thẻ HTML}}: $1.',
	'massmessage-parse-badurl' => '“$1” không phải là trang Web hợp lệ.',
	'massmessage-parse-badpage' => '“$1” không phải là tên trang hợp lệ.',
	'massmessage-global-disallowed' => 'Chỉ có thể gửi thông điệp cho những người trong một trang địa phương.',
	'right-massmessage' => 'Nhắn tin cho nhiều người một lúc',
	'action-massmessage' => 'nhắn tin cho nhiều người một lúc',
	'log-name-massmessage' => 'Nhật trình nhắn tin cho nhiều người',
	'log-description-massmessage' => 'Nhật trình này có các tin nhắn được gửi cho nhiều người dùng [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 đã {{GENDER:$2}}nhắn tin cho $3',
	'logentry-massmessage-failure' => 'Việc gửi “$4” cho $3 bị thất bại với mã lỗi <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Không thể gửi “$4” cho $3 vì trang nằm trong một không gian tên không cho phép nhắn tin',
	'logentry-massmessage-skipoptout' => 'Không thể gửi “$4” cho $3 vì người dùng đã chọn không nhận tin nhắn',
	'logentry-massmessage-skipnouser' => 'Không thể gửi “$4” cho $3 vì tài khoản người dùng này không tồn tại',
	'massmessage-cannot-rename' => 'Không thể đổi tên của tài khoản hệ thống này.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Nhắn tin khác]] hoặc [[Special:Statistics|xem hàng đợi có bao nhiêu tin nhắn]].',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'massmessage-hidden-comment' => '<!-- אנזאג געשיקט דורך User:$1@$2 ניצנדיק די ליסטער ביי $3 -->',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Liuxinyu970226
 * @author Qiyue2001
 * @author Shizhao
 */
$messages['zh-hans'] = array(
	'massmessage' => '发送大量消息',
	'massmessage-desc' => '使用户可以轻松将消息发送给列表中的用户',
	'massmessage-form-spamlist' => '页面包含了留言的页面列表：',
	'massmessage-form-subject' => '消息主题（还用作编辑摘要）：',
	'massmessage-form-message' => '消息正文：',
	'massmessage-form-preview' => '预览',
	'massmessage-form-submit' => '发送',
	'massmessage-fieldset-preview' => '预览',
	'massmessage-submitted' => '您要发送到{{PLURAL:$1|该页面|$1个页面}}的消息已开始排队。',
	'massmessage-just-preview' => '这只是预览。点击“{{int:massmessage-form-submit}}”来发送消息。',
	'massmessage-spamlist-doesnotexist' => '指定的页面列表页面不存在。',
	'massmessage-empty-subject' => '主题为空。',
	'massmessage-empty-message' => '消息正文为空。',
	'massmessage-form-header' => '使用下面的表单以将消息发送到指定的列表。所有字段都是必需的。',
	'massmessage-queued-count' => '[[Special:MassMessage|大量消息]]已排队',
	'massmessage-parse-badurl' => '"$1"不是一个有效的站点',
	'massmessage-parse-badpage' => '“$1”不是一个有效的页面标题',
	'massmessage-global-disallowed' => '消息只可以发送至本地页面。',
	'right-massmessage' => '一次将消息发送到多个用户',
	'action-massmessage' => '一次将消息发送到多个用户',
	'log-name-massmessage' => '批量消息日志',
	'log-description-massmessage' => '这些事件跟踪用户使用[[Special:MassMessage]]发送消息。',
	'logentry-massmessage-send' => '$1{{GENDER:$2|发送了一条消息}}到$3',
	'massmessage-nextsteps' => '[[Special:MassMessage|发送其他信息]]或者[[Special:Statistics|查看多少信息等待排队]]。',
);
