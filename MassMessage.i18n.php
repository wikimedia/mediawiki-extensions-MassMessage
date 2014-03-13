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
	'massmessage-unescaped-langlinks' => 'Warning: There are unescaped language links in your message.',
	'massmessage-form-header' => 'Use the form below to send messages to a specified list. All fields are required ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage more information]).',
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
	'log-description-massmessage' => 'These events track users sending messages.',
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

Parameters:
* $1 - the number of deliveries that have been queued',
	'massmessage-just-preview' => 'Warning to user that what they are seeing is just a preview, and they should hit the send button to actually submit it.

Refers to {{msg-mw|Massmessage-form-submit}}.',
	'massmessage-spamlist-doesnotexist' => 'Error message the user sees if an invalid spamlist is provided.

The spamlist is the page containing list of pages to leave a message on.

This message probably means that said page, as provided by the user, does not exist.',
	'massmessage-empty-subject' => 'Error message the user sees if the "subject" field is empty.',
	'massmessage-empty-message' => 'Error message the user sees if the "message" field is empty.',
	'massmessage-unescaped-langlinks' => 'Warning shown to user when previewing if their message text has unescaped language links.',
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
	'massmessage-nextsteps' => 'Message after {{msg-mw|Massmessage-submitted}} on the special page once the form has been submitted.',
);

/** Arabic (العربية)
 * @author Claw eg
 * @author مشعل الحربي
 */
$messages['ar'] = array(
	'massmessage' => 'إرسال رسالة جماعية',
	'massmessage-desc' => 'تسمح للمستخدمين بإرسال رسالة بسهولة إلى قائمة من المستخدمين',
	'massmessage-form-spamlist' => 'صفحة تحتوي على قائمة صفحات يمكن ترك رسالة عليها:',
	'massmessage-form-subject' => 'موضوع الرسالة (سيكون ملخص التحرير أيضًا):',
	'massmessage-form-message' => 'نص الرسالة:',
	'massmessage-form-preview' => 'معاينة',
	'massmessage-form-submit' => 'إرسال',
	'massmessage-fieldset-preview' => 'معاينة',
	'massmessage-just-preview' => 'هذه مجرد معاينة. اضغط "{{int:massmessage-form-submit}}" لإرسال الرسالة.',
	'massmessage-empty-subject' => 'حقل الموضوع فارغ.',
	'massmessage-empty-message' => 'نص الرسالة فارغ.',
	'massmessage-unescaped-langlinks' => 'تحذير: هناك روابط لغة ملغاة في الرسالة الخاصة بك.',
	'massmessage-form-header' => 'استخدم النموذج أدناه لإرسال رسائل إلى قائمة معينة. جميع الحقول مطلوبة.',
	'massmessage-parse-badurl' => '"$1" ليس موقعًا صحيحًا.',
	'massmessage-parse-badpage' => '"$1" ليس عنوان صفحة صحيح.',
	'massmessage-global-disallowed' => 'يمكن إرسال الرسائل إلى الصفحات المحلية فقط.',
	'right-massmessage' => 'إرسال رسالة إلى عدة مستخدمين في وقت واحد',
	'action-massmessage' => 'إرسال رسالة إلى عدة مستخدمين في وقت واحد',
	'log-name-massmessage' => 'سجل الرسائل الجماعية',
	'log-description-massmessage' => 'هذه الأحداث تتعقب المستخدمين الذين يرسلون الرسائل.',
	'logentry-massmessage-send' => '{{GENDER:$2|أرسل|أرسلت}} $1 رسالة إلى $3',
	'logentry-massmessage-skipbadns' => 'جرى تخطي توصيل "$4" إلى $3 لأن الهدف يقع في نطاق لا يمكن الكتابة فيه',
	'logentry-massmessage-skipnouser' => 'جرى تخطي توصيل "$4" إلى $3 لأن حساب المستخدم غير موجود',
	'massmessage-cannot-rename' => 'هذا حساب نظام ولا يمكن إعادة تسميته.',
);

/** Assamese (অসমীয়া)
 * @author Gitartha.bordoloi
 */
$messages['as'] = array(
	'massmessage' => 'সমূহীয়া বাৰ্তা পঠিয়াওক',
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'massmessage' => 'Unviar un mensaxe en masa',
	'massmessage-desc' => "Permite que los usuarios unvien fácilmente una mensaxe a una llista d'usuarios",
	'massmessage-form-spamlist' => 'Páxina que contién la llista de páxines nes que dexar un mensaxe:',
	'massmessage-form-subject' => "Asuntu del mensaxe (úsase tamién como resume d'edición):",
	'massmessage-form-message' => 'Cuerpu del mensaxe:',
	'massmessage-form-preview' => 'Vista previa',
	'massmessage-form-submit' => 'Unviar',
	'massmessage-fieldset-preview' => 'Vista previa',
	'massmessage-submitted' => 'La entrega del so mensaxe a {{PLURAL:$1|una páxina|$1 páxines}} unvióse a la cola.',
	'massmessage-just-preview' => 'Esto sólo ye una vista previa. Calque "{{int:massmessage-form-submit}}" pa unviar el mensaxe.',
	'massmessage-spamlist-doesnotexist' => 'La páxina cola llista de páxines especificada nun esiste.',
	'massmessage-empty-subject' => 'La llinia del asuntu ta balera.',
	'massmessage-empty-message' => 'El cuerpu del mensaxe ta baleru.',
	'massmessage-unescaped-langlinks' => 'Avisu: Hai enllaces de llingua ensin escapar nel so mensaxe.',
	'massmessage-form-header' => "Use'l formulariu de más abaxo pa unviar mensaxes a una llista determinada. Tolos campos son obligatorios.",
	'massmessage-queued-count' => '[[Special:MassMessage|Mensaxes en masa]] en cola',
	'massmessage-hidden-comment' => '<!-- Mensaxe unviáu por User:$1@$2 usando la llista de $3 -->',
	'massmessage-optout-category' => 'Torgar la entrega de mensaxes',
	'massmessage-badhtml' => 'El so mensaxe pue tener {{PLURAL:$2|una etiqueta HTML|etiquetes HTML}} ensin zarrar: $1.',
	'massmessage-parse-badurl' => '"$1" nun ye un sitiu válidu.',
	'massmessage-parse-badpage' => '"$1" nun ye un títulu de páxina válidu.',
	'massmessage-global-disallowed' => 'Los mensaxes sólo puen unviase a páxines llocales.',
	'right-massmessage' => 'Unviar un mensaxe a múltiples usuarios a la vez',
	'action-massmessage' => 'unviar un mensaxe a múltiples usuarios a la vez',
	'log-name-massmessage' => 'Rexistru de mensaxes en masa',
	'log-description-massmessage' => "Estos socesos rexistren los usuarios qu'unviaron mensaxes.",
	'logentry-massmessage-send' => '$1 {{GENDER:$2|unvió un mensaxe}} a $3',
	'logentry-massmessage-failure' => 'La entrega de "$4" a $3 falló con un códigu d\'error de <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'La entrega de "$4" a $3 saltóse porque\'l destín taba nun espaciu de nomes nel que nun se pue publicar',
	'logentry-massmessage-skipoptout' => 'La entrega de "$4" a $3 saltóse porque\'l destinatariu escoyó nun recibir mensaxes',
	'logentry-massmessage-skipnouser' => 'La entrega de "$4" a $3 saltóse porque la cuenta d\'usuariu nun esiste',
	'massmessage-cannot-rename' => 'Esta ye una cuenta del sistema y nun se-y pue camudar el nome.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Unviar otru mensaxe]] o [[Special:Statistics|ver cuántos mensaxes tan na cola]].',
);

/** Belarusian (Taraškievica orthography) (беларуская (тарашкевіца)‎)
 * @author Red Winged Duck
 */
$messages['be-tarask'] = array(
	'massmessage' => 'Адправіць масавае паведамленьне',
	'massmessage-queued-count' => 'Чарга [[Special:MassMessage|масавых паведамленьняў]]',
);

/** Bulgarian (български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'massmessage-parse-badurl' => '„$1“ не е валиден сайт.',
);

/** Bengali (বাংলা)
 * @author Aftab1995
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
	'massmessage-submitted' => '{{PLURAL:$1|$1টি পাতায়}} আপনার পাঠানো বার্তাটি অপেক্ষমান রয়েছে।',
	'massmessage-just-preview' => 'এটি শুধুমাত্র একটি প্রাকদর্শন। বার্তা পাঠাতে "{{int:massmessage-form-submit}}" চাপুন।',
	'massmessage-empty-subject' => 'বিষয় লাইনটি খালি।',
	'massmessage-empty-message' => 'বার্তার মূল অংশ খালি।',
	'massmessage-parse-badurl' => '"$1" কোন বৈধ সাইট নয়।',
	'massmessage-parse-badpage' => '"$1" কোন বৈধ পাতার শিরোনাম নয়।',
	'massmessage-global-disallowed' => 'বার্তা শুধুমাত্র স্থানীয় পাতাসমূহে পাঠানো যেতে পারে।',
	'log-name-massmessage' => 'গণ বার্তা লগ',
	'log-description-massmessage' => 'এই ঘটনাগুলি অনুসরণ ব্যবহারকারীদের বার্তা পাঠানোর।',
	'logentry-massmessage-send' => '$1 $3-এ {{GENDER:$2|একটি বার্তা পাঠিয়েছেন}}',
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

/** Catalan (català)
 * @author Arnaugir
 * @author Vriullop
 */
$messages['ca'] = array(
	'massmessage' => "Enviament d'un missatge massiu",
	'massmessage-desc' => "Permet als usuaris enviar fàcilment un missatge a una llista d'usuaris",
	'massmessage-form-spamlist' => 'Pàgina que conté la llista de pàgines per deixar-hi un missatge:',
	'massmessage-form-subject' => 'Assumpte del missatge (utilitzat també com a resum de la modificació):',
	'massmessage-form-message' => 'Cos del missatge:',
	'massmessage-form-preview' => 'Previsualitza',
	'massmessage-form-submit' => 'Envia',
	'massmessage-fieldset-preview' => 'Previsualització',
	'massmessage-submitted' => "L'enviament del missatge a {{PLURAL:$1|$1 pàgina|$1  pàgines}} ha estat posat en la cua de treballs.",
	'massmessage-just-preview' => 'Això és només una visualització prèvia. Premeu «{{int:massmessage-form-submit}}» per enviar el missatge.',
	'massmessage-spamlist-doesnotexist' => 'La pàgina especificada amb la llista de pàgines no existeix.',
	'massmessage-empty-subject' => "La línia de l'assumpte està buida.",
	'massmessage-empty-message' => 'El cos del missatge està buit.',
	'massmessage-unescaped-langlinks' => 'Atenció: en el missatge hi ha enllaços de llengua sense format.',
	'massmessage-form-header' => 'Utilitzeu el següent formulari per enviar missatges a una llista especificada. Tots els camps són obligatoris.',
	'massmessage-queued-count' => '[[Special:MassMessage|Missatges massius]] en cua de treballs',
	'massmessage-hidden-comment' => '<!-- Missatge enviat per Usuari:$1@$2 utilitzant la llista de $3 -->',
	'massmessage-optout-category' => 'Pàgines excloses de missatges massius',
	'massmessage-badhtml' => 'El missatge sembla que té {{PLURAL:$2|una etiqueta HTML no tancada|etiquetes HTML no tancades}}: $1.',
	'massmessage-parse-badurl' => '«$1» no és un lloc vàlid.',
	'massmessage-parse-badpage' => '«$1» no és un títol de pàgina vàlid.',
	'massmessage-global-disallowed' => 'Només es poden enviar missatges a pàgines locals.',
	'right-massmessage' => "Enviament d'un missatge a diversos usuaris a la vegada",
	'action-massmessage' => 'enviar un missatge a diversos usuaris a la vegada',
	'log-name-massmessage' => 'Registre de missatges massius',
	'log-description-massmessage' => "Accions registrades d'usuaris que han enviat missatges",
	'logentry-massmessage-send' => '$1 {{GENDER:$2|ha enviat}} un missatge a $3',
	'logentry-massmessage-failure' => "L'enviament de «$4» a $3 ha fallat amb el codi d'error <code>$5</code>",
	'logentry-massmessage-skipbadns' => "L'enviament de « $4 » a $3 ha estat omès perquè el destinatari és en un espai de noms on no es pot publicar",
	'logentry-massmessage-skipoptout' => "L'enviament de « $4 » a $3 ha estat omès perquè el destinatari ha escollit no rebre missatges massius",
	'logentry-massmessage-skipnouser' => "L'enviament de « $4 » a $3 ha estat omès perquè el compte d'usuari no existeix",
	'massmessage-cannot-rename' => 'Aquest és un compte del sistema i no es pot reanomenar.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Envia un altre missatge]] o [[Special:Statistics|mostra quants missatges hi ha en la cua]].',
);

/** Chechen (нохчийн)
 * @author Умар
 */
$messages['ce'] = array(
	'massmessage' => 'Дукха хаамаш кхехьийта',
	'massmessage-desc' => 'Дукху дкъашхошка атта хаамаш бахьийта таро хуьлуьйту',
	'massmessage-form-spamlist' => 'Хаам бохуьйтуш болу агӀонийн могӀа болу агӀо:',
	'massmessage-form-subject' => 'Хаамах лаьцна (кхин лелош ю хийцамах лаьцна яздеш):',
	'massmessage-form-message' => 'Хааман йоза:',
	'massmessage-form-preview' => 'Хьалха муха ю хьажа',
	'massmessage-form-submit' => 'ДӀадахьийта',
	'massmessage-fieldset-preview' => 'Хьалха муха ю хьажа',
	'massmessage-just-preview' => 'ХӀара хьалха хьажар ду. ТӀетаӀае «{{int:massmessage-form-submit}}» хаам дӀабахьийта.',
	'massmessage-form-header' => 'ХӀокху агӀонехь йиш ю билгалабина декъашхошка хаамаш бахьийта. Массанхьа яздан деза ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage кхин тӀе хаам]).',
	'massmessage-queued-count' => 'РогӀехь болу [[Special:MassMessage|дукха хаамаш]]',
	'right-massmessage' => 'Цхьана хеннахь массийта декъашхошка хаамаш бахьийтар',
	'action-massmessage' => 'массийта декъашхошка хаамаш бахьийтар цкъачунна дӀадайина ду',
	'log-name-massmessage' => 'Дукха хаамаш кхехьийтаран тептар',
	'log-description-massmessage' => 'Декъашхочо хаамаш кхехьийтар гойту тептар.',
);

/** Sorani Kurdish (کوردی)
 * @author Calak
 */
$messages['ckb'] = array(
	'right-massmessage' => 'ناردنی ھاوکاتی پەیامێک بۆ چەند بەکارھێنەر',
	'action-massmessage' => 'ناردنی ھاوکاتی پەیامێک بۆ چەند بەکارھێنەر',
);

/** Czech (čeština)
 * @author Mormegil
 * @author Utar
 */
$messages['cs'] = array(
	'massmessage' => 'Poslat hromadnou zprávu',
	'massmessage-desc' => 'Umožňuje uživatelům snadno odesílat zprávy seznamu uživatelů',
	'massmessage-form-spamlist' => 'Stránka obsahující seznam stránek, na kterých se má zanechat zpráva:',
	'massmessage-form-subject' => 'Předmět zprávy (použit také jako shrnutí editace):',
	'massmessage-form-message' => 'Tělo zprávy:',
	'massmessage-form-preview' => 'Náhled',
	'massmessage-form-submit' => 'Odeslat',
	'massmessage-fieldset-preview' => 'Náhled',
	'massmessage-submitted' => 'Požadované doručení zprávy na {{PLURAL:$1|$1 stránku|$1 stránky|$1 stránek}} bylo zařazeno do fronty.',
	'massmessage-just-preview' => 'Toto je jen náhled. Pro odeslání zprávy klikněte na „{{int:massmessage-form-submit}}“.',
	'massmessage-spamlist-doesnotexist' => 'Uvedená stránka se seznamem stránek neexistuje.',
	'massmessage-empty-subject' => 'Předmět je prázdný.',
	'massmessage-empty-message' => 'Tělo zprávy je prázdné.',
	'massmessage-unescaped-langlinks' => 'Upozornění: Vaše zpráva obsahuje neošetřené jazykové odkazy.',
	'massmessage-form-header' => 'Pomocí níže uvedeného formuláře můžete rozeslat zprávy podle zadaného seznamu. Všechna pole jsou povinná ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage více informací]).',
	'massmessage-queued-count' => 'Počet [[Special:MassMessage|hromadných zpráv]] ve frontě',
	'massmessage-hidden-comment' => '<!-- Zprávu zaslal Uživatel:$1@$2 pomocí seznamu na $3 -->',
	'massmessage-optout-category' => 'Odhlášení z příjmu zpráv',
	'massmessage-badhtml' => 'Vaše zpráva možná obsahuje {{PLURAL:$2|neuzavřenou HTML značku|neuzavřené HTML značky}}: $1.',
	'massmessage-parse-badurl' => '„$1“ není platný projekt.',
	'massmessage-parse-badpage' => '„$1“ není platný název stránky.',
	'massmessage-global-disallowed' => 'Zprávy lze odesílat jen na místní stránky.',
	'right-massmessage' => 'Rozesílání zpráv více uživatelům současně',
	'action-massmessage' => 'rozesílat zprávu více uživatelům současně',
	'log-name-massmessage' => 'Kniha hromadných zpráv',
	'log-description-massmessage' => 'Tyto události zaznamenávají uživatele, kteří odesílají zprávy.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|poslal|poslala}} zprávu pro $3',
	'logentry-massmessage-failure' => 'Doručení „$4“ pro $3 selhalo s chybovým kódem <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Doručení „$4“ pro $3 bylo přeskočeno, protože cíl je ve jmenném prostoru, do kterého nelze psát',
	'logentry-massmessage-skipoptout' => 'Doručení „$4“ pro $3 bylo přeskočeno, protože cíl se odhlásil z příjmu zpráv',
	'logentry-massmessage-skipnouser' => 'Doručení „$4“ pro $3 bylo přeskočeno, protože uživatelský účet neexistuje',
	'massmessage-cannot-rename' => 'Toto je systémový účet, který nemůže být přejmenován.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Poslat další zprávu]] nebo [[Special:Statistics|zobrazit počet zpráv ve frontě]].',
);

/** Danish (dansk)
 * @author Christian List
 * @author Sarrus
 */
$messages['da'] = array(
	'massmessage' => 'Send massebesked',
	'massmessage-desc' => 'Giver brugerne mulighed for nemt at sende en besked til en liste af brugere',
	'massmessage-form-spamlist' => 'Side, der indeholder oversigten over sider, beskeden skal leveres på:',
	'massmessage-form-subject' => 'Beskedens emne (bruges også som kommentar til redigeringen):',
	'massmessage-form-message' => 'Beskedens indhold:',
	'massmessage-form-preview' => 'Forhåndsvisning',
	'massmessage-form-submit' => 'Send',
	'massmessage-fieldset-preview' => 'Forhåndsvisning',
	'massmessage-submitted' => 'Levering af din besked til {{PLURAL:$1|$1 side|$1 sider}} er sat i kø.',
	'massmessage-just-preview' => 'Dette er kun en forhåndsvisning. Klik på "{{int:massmessage-form-submit}}" for at sende beskeden.',
	'massmessage-spamlist-doesnotexist' => 'Den angivne side med oversigten over beskedmodtagere eksisterer ikke.',
	'massmessage-empty-subject' => 'Emnelinjen er tom.',
	'massmessage-empty-message' => 'Beskedfeltet er tomt.',
	'massmessage-unescaped-langlinks' => 'Advarsel: Der er unescaped sprog hyperlinks i din besked.',
	'massmessage-form-header' => 'Brug den nedenstående formular for at sende beskeder til en bestemt liste. Alle felter er påkrævede.',
	'massmessage-queued-count' => '[[Special:MassMessage|Massebeskeder]] i kø',
	'massmessage-hidden-comment' => '<!-- Besked sendt af Bruger:$1@$2 via listen på $3 -->',
	'massmessage-optout-category' => 'Fravalgt at modtage besked',
	'massmessage-badhtml' => 'Din besked kan have {{PLURAL:$2|en uafsluttet HTML tag|uafsluttede HTML tags}}:  $1.',
	'massmessage-parse-badurl' => '"$1" er ikke et gyldigt websted.',
	'massmessage-parse-badpage' => '"$1" er ikke en gyldig sidetitel.',
	'massmessage-global-disallowed' => 'Beskeder kan kun sendes til lokale sider.',
	'right-massmessage' => 'Send en besked til flere brugere på én gang',
	'action-massmessage' => 'send en besked til flere brugere på én gang',
	'log-name-massmessage' => 'Massebeskedslog',
	'log-description-massmessage' => 'Disse begivenheder sporer brugere der sender beskeder.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sendte en besked}} til $3',
	'logentry-massmessage-failure' => 'Levering af "$4" til $3 mislykkedes med fejlkoden <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Levering af "$4" til $3 blev sprunget over, fordi målsiden var i et navnerum, der ikke kan leveres beskeder til',
	'logentry-massmessage-skipoptout' => 'Levering af "$4" til $3 blev sprunget over, fordi målet har fravalgt at modtage beskeder',
	'logentry-massmessage-skipnouser' => 'Levering af "$4" til $3 blev sprunget over, fordi brugerkontoen ikke findes',
	'massmessage-cannot-rename' => 'Dette er en systemkonto og kan ikke omdøbes.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Send en til besked]] eller [[Special:Statistics|se hvor mange meddelelser der er i kø]].',
);

/** German (Deutsch)
 * @author Filzstift
 * @author Metalhead64
 * @author Se4598
 */
$messages['de'] = array(
	'massmessage' => 'Massennachricht senden',
	'massmessage-desc' => 'Ermöglicht Benutzern das einfache Versenden von Nachrichten an eine Benutzerliste',
	'massmessage-form-spamlist' => 'Seite mit der Seitenliste der Nachrichtenempfänger:',
	'massmessage-form-subject' => 'Betreff der Nachricht (wird auch für die Bearbeitungszusammenfassung verwendet):',
	'massmessage-form-message' => 'Nachrichtentext:',
	'massmessage-form-preview' => 'Vorschau',
	'massmessage-form-submit' => 'Senden',
	'massmessage-fieldset-preview' => 'Vorschau',
	'massmessage-submitted' => 'Dein Nachrichtenversand an {{PLURAL:$1|eine Seite|$1 Seiten}} wurde in die Sendewarteschlange eingefügt!',
	'massmessage-just-preview' => 'Dies ist nur eine Vorschau. Klicke auf „{{int:massmessage-form-submit}}“, um die Nachricht abzusenden.',
	'massmessage-spamlist-doesnotexist' => 'Die angegebene Seitenlistenseite ist nicht vorhanden.',
	'massmessage-empty-subject' => 'Die Betreffszeile ist leer.',
	'massmessage-empty-message' => 'Der Nachrichtenkörper ist leer.',
	'massmessage-unescaped-langlinks' => 'Warnung: Deine Nachricht enthält ungültige Sprachlinks.',
	'massmessage-form-header' => 'Benutze das unten stehende Formular, um Nachrichten an eine angegebene Liste zu senden. Es sind alle Felder erforderlich ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage weitere Informationen]).',
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
	'log-description-massmessage' => 'Dieses Logbuch protokolliert Ereignisse von Benutzern, die Nachrichten versandt haben.',
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

/** Greek (Ελληνικά)
 * @author Geraki
 */
$messages['el'] = array(
	'massmessage' => 'Αποστολή μαζικού μηνύματος',
	'massmessage-desc' => 'Επιτρέπει στους χρήστες να στείλουν εύκολα ένα μήνυμα σε μια λίστα των χρηστών',
	'massmessage-form-spamlist' => 'Σελίδα που περιέχει κατάλογο των σελίδων στις οποίες θα αφήσετε ένα μήνυμα:',
	'massmessage-form-subject' => 'Θέμα του μηνύματος (χρησιμοποιείται επίσης ως σύνοψη της επεξεργασίας):',
	'massmessage-form-message' => 'Σώμα του μηνύματος:',
	'massmessage-form-preview' => 'Προεπισκόπηση',
	'massmessage-form-submit' => 'Αποστολή',
	'massmessage-fieldset-preview' => 'Προεπισκόπηση',
	'massmessage-submitted' => 'Η παράδοση του μηνύματός σας σε {{PLURAL:$1|$1  σελίδα|$1 σελίδες}} έχει τοποθετηθεί σε ουρά.',
	'massmessage-just-preview' => 'Αυτό είναι μόνο μια προεπισκόπηση. Πατήστε το πλήκτρο {{int:massmessage-form-submit}}"  για να στείλετε το μήνυμα.',
	'massmessage-spamlist-doesnotexist' => 'Η καθορισμένη σελίδα καταλόγου-σελίδων δεν υπάρχει.',
	'massmessage-empty-subject' => 'Η γραμμή θέματος είναι κενή.',
	'massmessage-empty-message' => 'Το σώμα του μηνύματος είναι κενό.',
	'massmessage-unescaped-langlinks' => 'Προειδοποίηση: Υπάρχουν γλωσσικές συνδέσεις χωρίς διαφυγή στο μήνυμά σας.',
	'massmessage-form-header' => 'Χρησιμοποιήστε την παρακάτω φόρμα για να στείλετε μηνύματα σε μια συγκεκριμένη λίστα. Όλα τα πεδία είναι υποχρεωτικά.',
	'massmessage-queued-count' => '[[Special:MassMessage|Μαζικά μηνύματα]] στην ουρά',
	'massmessage-hidden-comment' => '<!-- Το μήνυμα στάλθηκε από τον User:$1@$2 με βάση τη λίστα στην $3 -->',
	'massmessage-optout-category' => 'Αποεπιλογή παράδοσης μηνυμάτων',
	'massmessage-badhtml' => 'Το μήνυμα σας μπορεί να έχει {{PLURAL:$2|μια ανοικτή HTML ετικέτα|ανοικτές HTML ετικέτες}}: $1.',
	'massmessage-parse-badurl' => '"$1" δεν είναι έγκυρη τοποθεσία',
	'massmessage-parse-badpage' => '"$1" δεν είναι έγκυρος τίτλος σελίδας',
	'massmessage-global-disallowed' => 'Μηνύματα μπορούν να σταλούν μόνο τοπικές σελίδες.',
	'right-massmessage' => 'Αποστολή μηνύματος σε πολλούς χρήστες ταυτόχρονα',
	'action-massmessage' => 'στείλετε ένα μήνυμα σε πολλούς χρήστες ταυτόχρονα',
	'log-name-massmessage' => 'Καταγραφή μαζικών μηνυμάτων',
	'log-description-massmessage' => 'Αυτά τα γεγονότα παρακολουθούν τους χρήστες που κάνουν αποστολή μηνυμάτων.',
	'logentry-massmessage-send' => '{{GENDER:$2|Ο|Η}} $1 έστειλε μήνυμα στην $3',
	'logentry-massmessage-failure' => 'Παράδοση του "$4" στην $3 απέτυχε με κωδικό σφάλματος <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Παράδοση του "$4" στην $3 παραλείφθηκε επειδή ο στόχος ήταν σε ένα χώρο ονομάτων στον οποίο δεν μπορεί να αναρτηθεί',
	'logentry-massmessage-skipoptout' => 'Παράδοση του "$4" στην $3 παραλείφθηκε επειδή ο στόχος έχει αποεπιλέξει την παράδοση μηνυμάτων',
	'logentry-massmessage-skipnouser' => 'Παράδοση του "$4" στη $3 παραλείφθηκε επειδή ο λογαριασμός χρήστη δεν υπάρχει',
	'massmessage-cannot-rename' => 'Αυτό είναι ένας λογαριασμός του συστήματος και δεν μπορεί να μετονομαστεί.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Στείλετε άλλο μήνυμα]] ή [[Special:Statistics|προβάλετε τον αριθμό των μηνυμάτων που βρίσκονται στην ουρά]].',
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
 * @author Ciencia Al Poder
 * @author Csbotero
 * @author Fitoschido
 * @author Ovruni
 */
$messages['es'] = array(
	'massmessage' => 'Enviar mensaje masivo',
	'massmessage-desc' => 'Permite al usuario enviar un mensaje a una lista de usuarios fácilmente',
	'massmessage-form-spamlist' => 'Página que contiene la lista de páginas donde dejar un mensaje:',
	'massmessage-form-subject' => 'Asunto del mensaje (usado también como resumen de edición):',
	'massmessage-form-message' => 'Cuerpo del mensaje:',
	'massmessage-form-preview' => 'Previsualizar',
	'massmessage-form-submit' => 'Enviar',
	'massmessage-fieldset-preview' => 'Previsualización',
	'massmessage-just-preview' => 'Esto solo es una previsualización. Pulsa en «{{int:massmessage-form-submit}}» para enviar el mensaje.',
	'massmessage-spamlist-doesnotexist' => 'La lista de páginas especificada no existe.',
	'massmessage-empty-subject' => 'El asunto está vacío.',
	'massmessage-empty-message' => 'El cuerpo del mensaje está vacío.',
	'massmessage-form-header' => 'Utilice el siguiente formulario para enviar mensajes a una lista específica. Todos los campos son obligatorios ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage more information]).',
	'massmessage-queued-count' => '[[Special:MassMessage|Mensajes masivos]] en cola',
	'massmessage-hidden-comment' => '<!-- Mensaje enviado por Usuario:$1@$2 mediante la lista en $3 -->',
	'massmessage-parse-badurl' => '"$1" no es un sitio válido',
	'massmessage-parse-badpage' => '"$1" no es un título de página valido',
	'massmessage-global-disallowed' => 'Solo se puede enviar mensajes a páginas locales.',
	'right-massmessage' => 'Enviar un mensaje a varios usuarios a la vez',
	'action-massmessage' => 'enviar un mensaje a varios usuarios a la vez',
	'log-name-massmessage' => 'Registro de mensajes masivos',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|ha enviado}} un mensaje a $3',
	'logentry-massmessage-failure' => 'Ha fallado la entrega de «$4» a $3 con el código de error <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Se omitió la entrega de «$4» a $3 porque el destino se encuentra en un espacio de nombres donde no se puede publicar',
	'logentry-massmessage-skipoptout' => 'Se omitió la entrega de «$4» a $3 porque el usuario ha elegido no recibir mensajes',
	'logentry-massmessage-skipnouser' => 'Se omitió la entrega de «$4» a $3 porque la cuenta de usuario no existe',
	'massmessage-cannot-rename' => 'Esta es una cuenta del sistema y no se puede renombrar.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Envía otro mensaje]] o [[Special:Statistics|ve cuántos mensajes hay en espera]].',
);

/** Estonian (eesti)
 * @author Pikne
 */
$messages['et'] = array(
	'massmessage' => 'Hulgasõnumi saatmine',
	'massmessage-desc' => 'Võimaldab lihtsasti saata loetletud kasutajatele sõnumi.',
	'massmessage-form-spamlist' => 'Lehekülg, kus on loetletud leheküljed, kuhu sõnum jätta:',
	'massmessage-form-subject' => 'Sõnumi teema (ühtlasi muudatuse resümee):',
	'massmessage-form-message' => 'Sõnumi kehatekst:',
	'massmessage-form-preview' => 'Eelvaade',
	'massmessage-form-submit' => 'Saada',
	'massmessage-fieldset-preview' => 'Eelvaade',
	'massmessage-submitted' => 'Sinu sõnumi saatmine {{PLURAL:$1|ühele|$1}} leheküljele on tööjärjes ootel.',
	'massmessage-just-preview' => 'See on kõigest eelvaade. Vajuta "{{int:massmessage-form-submit}}", et sõnum saata.',
	'massmessage-spamlist-doesnotexist' => 'Lehekülgede määratud loendit pole.',
	'massmessage-empty-subject' => 'Teemarida on tühi.',
	'massmessage-empty-message' => 'Sõnumi keha on tühi.',
	'massmessage-unescaped-langlinks' => 'Hoiatus: Sinu muudatus sisaldab katkestamata keelelinke.',
	'massmessage-form-header' => 'Kasuta järgmist vormi, et saata sõnum määratud loendisse. Kõik väljad on nõutavad ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage lisateave]).',
	'massmessage-queued-count' => 'Järjekorras [[Special:MassMessage|hulgasõnumid]]',
	'massmessage-hidden-comment' => '<!-- Sõnumi saatis Kasutaja:$1@$2 loendisse leheküljel "$3". -->',
	'massmessage-optout-category' => 'Ei soovi sõnumeid',
	'massmessage-badhtml' => 'Sinu sõnumis võib olla sulgemata {{PLURAL:$2|HTML-silt|HTML-silte}}: $1.',
	'massmessage-parse-badurl' => '"$1" pole sobiv võrgukoht.',
	'massmessage-parse-badpage' => '"$1" pole sobiv lehekülje pealkiri.',
	'massmessage-global-disallowed' => 'Sõnumeid saab saata ainult kohalikele lehekülgedele.',
	'right-massmessage' => 'Saata sõnum mitmele kasutajale korraga',
	'action-massmessage' => 'saata sõnumit korraga mitmele kasutajale',
	'log-name-massmessage' => 'Hulgasõnumite logi',
	'log-description-massmessage' => 'Siin on logisissekanded saadetud sõnumite kohta.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|saatis sõnumi}} asukohta $3',
	'logentry-massmessage-failure' => 'Sõnumi "$4" saatmine leheküljele $3 ebaõnnestus tõrke <code>$5</code> tõttu.',
	'logentry-massmessage-skipbadns' => 'Sõnumi "$4" saatmine leheküljele $3 jäi vahele, sest lehekülg asub nimeruumis, kuhu ei saa postitada.',
	'logentry-massmessage-skipoptout' => 'Sõnumi "$4" saatmine leheküljele $3 jäi vahele, sest saaja ei soovi sõnumeid.',
	'logentry-massmessage-skipnouser' => 'Sõnumi "$4" saatmine leheküljele $3 jäi vahele, sest kasutajakontot pole olemas.',
	'massmessage-cannot-rename' => 'See on süsteemikonto, mille nime ei saa muuta.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Saada teine sõnum]] või [[Special:Statistics|vaata mitu sõnumit on tööjärjes ootel]].',
);

/** Basque (euskara)
 * @author An13sa
 */
$messages['eu'] = array(
	'massmessage-form-submit' => 'Bidali',
);

/** Persian (فارسی)
 * @author Armin1392
 * @author Ebraminio
 * @author Reza1615
 */
$messages['fa'] = array(
	'massmessage' => 'ارسال انبوه پیام',
	'massmessage-desc' => 'اجازه می‌دهد کاربران به‌سادگی پیامی را فهرست از کاربران بفرستند',
	'massmessage-form-spamlist' => 'صفحه‌های حاوی فهرستی از صفحه‌ها برای فرستادن پیام:',
	'massmessage-form-subject' => 'عنوان پیام (همچنین در خلاصهٔ ویرایش استفاده شده‌است):',
	'massmessage-form-message' => 'بدنهٔ پیام:',
	'massmessage-form-preview' => 'پیش‌نمایش',
	'massmessage-form-submit' => 'ارسال',
	'massmessage-fieldset-preview' => 'پیش‌نمایش',
	'massmessage-submitted' => 'پیام شما به {{PLURAL:$1|$1 page|$1 صفحه}} در صف قرار گرفت.',
	'massmessage-just-preview' => 'این فقط یک پیش‌نمایش است. «{{int:massmessage-form-submit}}» را برای ارسال پیام بفشارید.',
	'massmessage-spamlist-doesnotexist' => 'صفحه-فهرست صفحه مشخص‌شده موجود نیست.',
	'massmessage-empty-subject' => 'خط موضوع خالی است.',
	'massmessage-empty-message' => 'بدنهٔ پیام خالی است.',
	'massmessage-unescaped-langlinks' => 'هشدار: پیوندهای زبان غیررها در پیام شما وجود دارد.',
	'massmessage-form-header' => 'از فرم زیر برای ارسال پیام‌ها به یک فهرست مشخص‌شده استفاده کنید. همهٔ زمینه‌ها مورد نیاز ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage more information]) هستند.',
	'massmessage-queued-count' => '[[Special:MassMessage|پیام‌های انبوه]] در صف قرار گرفت',
	'massmessage-hidden-comment' => '<!-- پیام توسط کاربر:$1@$2 با استفاده از فهرست در $3 ارسال شده‌است -->',
	'massmessage-optout-category' => 'بیرون آمدن از رساندن پیام',
	'massmessage-badhtml' => 'پیام شما {{PLURAL:$2|یک|چند}} برچسپ بسته‌نشده اچ‌تی‌ام‌ال دارد: $1.',
	'massmessage-parse-badurl' => '«$1» وب‌گاه معتبری نیست.',
	'massmessage-parse-badpage' => '«$1» عنوان معتبری برای صفحه نیست.',
	'massmessage-global-disallowed' => 'پیام‌های فقط می‌تواند به صفحه‌های محلی ارسال شود.',
	'right-massmessage' => 'ارسال یکجای یک پیام به چندین کاربر',
	'action-massmessage' => 'ارسال یکجای یک پیام به چندین کاربر',
	'log-name-massmessage' => 'سیاههٔ ارسال انبوه',
	'log-description-massmessage' => 'این رویدادها ارسال پیام کاربران را دنبال می‌کند.',
	'logentry-massmessage-send' => '$1 پیامی به $3 {{GENDER:$2|ارسال کرد}}',
	'logentry-massmessage-failure' => 'رساندن «$4» به $3 به پیام خطای <code>$5</code> شکست خورد',
	'logentry-massmessage-skipbadns' => 'رساندن «$4» به $3 نادیده گرفته‌شد به این دلیل که در فضای نامی بود نمی‌توانست ارسال شود',
	'logentry-massmessage-skipoptout' => 'رساندن «$4» به $3 نادیده گرفته‌شد به این دلیل که هدف از رسیدن پیام بیرون آمده‌است',
	'logentry-massmessage-skipnouser' => 'رساندن «$4» به $3 نادیده گرفته‌شد به این دلیل که حساب کاربری موجود نیست',
	'massmessage-cannot-rename' => 'این یک حساب سامانه‌ای است و نمی‌تواند تغییر نام یابد',
	'massmessage-nextsteps' => '[[Special:MassMessage|ارسال پیام دیگر]] یا [[Special:Statistics|مشاهدهٔ اینکه چند پیام در صف قرار دارند]].',
);

/** Finnish (suomi)
 * @author Nike
 * @author Pxos
 */
$messages['fi'] = array(
	'massmessage' => 'Lähetä joukkoviesti',
	'massmessage-desc' => 'Antaa käyttäjille helpon keinon lähettää viestin käyttäjille, jotka on merkitty luetteloon.',
	'massmessage-form-spamlist' => 'Sivu, joka sisältää luettelon niistä sivuista, joihin viesti halutaan:',
	'massmessage-form-subject' => 'Viestin aihe (käytetään samalla muokkauksen yhteenvetona):',
	'massmessage-form-message' => 'Viestin tekstisisältö:',
	'massmessage-form-preview' => 'Esikatsele',
	'massmessage-form-submit' => 'Lähetä viesti',
	'massmessage-fieldset-preview' => 'Esikatselu',
	'massmessage-submitted' => 'Joukkoviestisi toimittaminen {{PLURAL:$1|$1 sivulle}} on laitettu jonoon.',
	'massmessage-just-preview' => 'Tämä on pelkästään viestin esikatselu. Paina "{{int:massmessage-form-submit}}" niin viesti lähtee liikkeelle.',
	'massmessage-spamlist-doesnotexist' => 'Antamaasi luettelosivua kohdesivuista ei ole olemassa.',
	'massmessage-empty-subject' => 'Viestin aihetta ei ole annettu.',
	'massmessage-empty-message' => 'Viestissä ei ole lainkaan tekstiä.',
	'massmessage-unescaped-langlinks' => 'Varoitus: Viestissäsi on sellaisia kielilinkkejä, jotka eivät kelpaa ja joita ei voi käyttää.',
	'massmessage-form-header' => 'Käytä alla olevaa lomaketta lähettääksesi viestejä ennalta määriteltyyn luetteloon. Kaikki kentät on täytettävä. ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage katso lisätietoja englanniksi])',
	'massmessage-queued-count' => 'Jonossa olevat [[Special:MassMessage|joukkoviestit]]',
	'massmessage-hidden-comment' => '<!-- Viestin on lähettänyt Käyttäjä:$1@$2 käyttämällä luetteloa, joka on sijainnissa $3 -->',
	'massmessage-optout-category' => 'Eivät halua joukkoviestejä',
	'massmessage-badhtml' => 'Viestissäsi saattaa olla {{PLURAL:$2|sulkematon HTML tag|sulkemattomia HTML tagejä}}: $1.',
	'massmessage-parse-badurl' => '"$1" ei ole kelvollinen sivusto.',
	'massmessage-parse-badpage' => '"$1" ei ole kelvollinen sivun nimi.',
	'massmessage-global-disallowed' => 'Viestejä voi lähettää ainoastaan paikallisille sivuille.',
	'right-massmessage' => 'Lähettää viesti usealle käyttäjälle yhtä aikaa',
	'action-massmessage' => 'lähettää viestiä usealle käyttäjälle samalla kertaa',
	'log-name-massmessage' => 'Joukkoviestien loki',
	'log-description-massmessage' => 'Nämä tapahtumat merkitsevät käyttäjiä, jotka lähettävät viestejä.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|lähetti viestin}} luetteloon $3',
	'logentry-massmessage-failure' => 'Viestin "$4" toimittaminen kohteeseen $3 ei onnistunut. Virhekoodi on <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Viestiä "$4" ei toimitettu kohteeseen $3, koska kohde on sellaisessa nimiavaruudessa, johon ei voi lähettää viestejä',
	'logentry-massmessage-skipoptout' => 'Viestiä "$4" ei toimitettu kohteeseen $3, koska kohde ei halua vastaanottaa viestejä',
	'logentry-massmessage-skipnouser' => 'Viestiä "$4" ei toimitettu kohteeseen $3, koska käyttäjätunnusta ei ole olemassa',
	'massmessage-cannot-rename' => 'Tämä on järjestelmän käyttämä tunnus eikä sitä voi nimetä uudelleen.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Lähetä toinen viesti]] tai [[Special:Statistics|katso kuinka moni viesti on jonossa]].',
);

/** French (français)
 * @author DavidL
 * @author Gomoko
 * @author Ltrlg
 * @author Rastus Vernon
 * @author Seb35
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
	'massmessage-submitted' => 'L’envoi de votre message à {{PLURAL:$1|$1 page|$1 pages}} a été mis en file d’attente.',
	'massmessage-just-preview' => 'Ceci est simplement un aperçu. Appuyez sur « {{int:massmessage-form-submit}} » pour envoyer le message.',
	'massmessage-spamlist-doesnotexist' => 'La listes de pages spécifiée n’existe pas.',
	'massmessage-empty-subject' => 'La ligne du sujet est vide.',
	'massmessage-empty-message' => 'Le corps du message est vide.',
	'massmessage-unescaped-langlinks' => 'Attention : il y a des liens de langue non échappés dans votre message.',
	'massmessage-form-header' => 'Utilisez le formulaire ci-dessous pour envoyer des messages à une liste indiquée. Tous les champs sont obligatoires ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage plus d’information]).',
	'massmessage-queued-count' => '[[Special:MassMessage|Messages de masse]] en file d’attente',
	'massmessage-hidden-comment' => '<!-- Message envoyé par User:$1@$2 en utilisant la liste à $3 -->',
	'massmessage-optout-category' => 'Exempté de réception de messages',
	'massmessage-badhtml' => 'Votre message semble contenir {{PLURAL:$2|une balise HTML mal fermée|des balises HTML mal fermées}} :  $1.',
	'massmessage-parse-badurl' => '« $1 » n’est pas un site valide',
	'massmessage-parse-badpage' => '« $1 » n’est pas un titre de page valide',
	'massmessage-global-disallowed' => 'Les messages ne peuvent être envoyés qu’aux pages locales.',
	'right-massmessage' => 'Envoyer un message à plusieurs utilisateurs à la fois',
	'action-massmessage' => 'envoyer un message à plusieurs utilisateurs à la fois',
	'log-name-massmessage' => 'Journal des messages de masse',
	'log-description-massmessage' => 'Ces événements tracent les utilisateurs ayant envoyé des messages.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|a envoyé un message}} à $3',
	'logentry-massmessage-failure' => 'La livraison de « $4 » sur $3 a échoué avec le code d’erreur « <code>$5</code> »',
	'logentry-massmessage-skipbadns' => 'L’envoi de « $4 » à $3 a été sauté parce que la cible était dans un espace de noms qui ne peut pas recevoir de message',
	'logentry-massmessage-skipoptout' => 'L’envoi de « $4 » à $3 a été sauté parce que la cible a choisi de ne pas recevoir de messages',
	'logentry-massmessage-skipnouser' => 'L’envoi de « $4 » à $3 a été sauté parce que le compte utilisateur n’existe pas',
	'massmessage-cannot-rename' => 'Ceci est un compte système qui ne peut être renommé.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Envoyer un autre message]] ou [[Special:Statistics|voir combien de messages sont en attente]].',
);

/** Northern Frisian (Nordfriisk)
 * @author Murma174
 */
$messages['frr'] = array(
	'massmessage' => 'Schüür en streibriaf',
	'massmessage-desc' => 'Diarmä kön brükern lacht en bööd tu en list faan brükern schüür',
	'massmessage-form-spamlist' => 'Sidj mä en sidjenlist för streibriaw',
	'massmessage-form-subject' => 'Hoodrä (woort uk üs tuupfaadang brükt):',
	'massmessage-form-message' => 'Tekst faan din bööd:',
	'massmessage-form-preview' => 'Föörskau',
	'massmessage-form-submit' => 'Schüür',
	'massmessage-fieldset-preview' => 'Föörskau',
	'massmessage-submitted' => 'Din bööd tu {{PLURAL:$1|ian sidj|$1 sidjen}} teewt nü üüb a postluuper.',
	'massmessage-just-preview' => 'Det as man en föörskau. Trak üüb "{{int:massmessage-form-submit}}", an do woort din bööd wechschüürd.',
	'massmessage-spamlist-doesnotexist' => 'Detdiar sidj üüb din sidjenlist jaft at ei.',
	'massmessage-empty-subject' => 'Det hoodrä as leesag.',
	'massmessage-empty-message' => 'Di tekst faan din bööd as leesag.',
	'massmessage-unescaped-langlinks' => 'Paase üüb: Uun din bööd san ei tuläät ferwisangen tu ööder spriaken.',
	'massmessage-form-header' => 'Brük det formulaar diar oner, am en bööd tu en sidjenlist tu schüüren. Arke fial skal ütjfald wees. ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage muar diartu]).',
	'massmessage-queued-count' => '[[Special:MassMessage|Streibriaw]] teew üüb a postluuper.',
	'massmessage-hidden-comment' => '<!-- Bööd as faan di brüker:$1@$2 auer det list $3 ferschüürd wurden-->',
	'massmessage-optout-category' => 'Det ufleewrin faan bööd as ufsteld wurden',
	'massmessage-badhtml' => 'Uun din bööd küd {{PLURAL:$2|en eeben HTML-tag|eeben HTML-tags}} wees: $1.',
	'massmessage-parse-badurl' => '"$1" as nian tuläät wääbsid.',
	'massmessage-parse-badpage' => '"$1" as nään tuläät sidjennööm.',
	'massmessage-global-disallowed' => 'Bööd koon bluas to lokaal sidjen ferschüürd wurd.',
	'right-massmessage' => 'En bööd tu en hiale rä faan brükern schüür',
	'action-massmessage' => 'en bööd tu en hiale rä faan brükern tu schüüren',
	'log-name-massmessage' => 'Streibriaf-logbuk',
	'log-description-massmessage' => 'Detdiar logbuk skraft en protokol auer brükern, diar bööd ferschüür.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|hää bööd schüürd}} tu $3',
	'logentry-massmessage-failure' => 'Det ufleewrin faan "$4" tu $3 as skiaf gingen. Di feelercode as <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Det ufleewrin faan "$4" tu $3 as auersprüngen wurden, auer det sidj uun en nöömrüm as, huar nian bööd henschüürd wurd koon.',
	'logentry-massmessage-skipoptout' => 'Det ufleewrin faan "$4" tu $3 as auersprüngen wurden, auer det sidj nian bööd uunnem wal.',
	'logentry-massmessage-skipnouser' => 'Det ufleewrin faan "$4" tu $3 as auersprüngen wurden, auer diar ei son brükerkonto as.',
	'massmessage-cannot-rename' => 'Det as en süsteemkonto an koon ei amnäämd wurd.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Ian bööd muar schüür]] of iarst ans [[Special:Statistics|luke, hü fölen al üüb a postluuper teew]].',
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
	'massmessage-unescaped-langlinks' => 'Atención: Hai ligazóns de lingua sen formato na súa mensaxe.',
	'massmessage-form-header' => 'Utilice o formulario inferior para enviar mensaxes a unha lista especificada. Todos os campos son obrigatorios.', # Fuzzy
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
	'log-description-massmessage' => 'Este rexistro garda os usuarios que enviaron mensaxes.',
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
	'massmessage-unescaped-langlinks' => 'אזהרה: יש קישורי שפה לא מחולפים בהודעה שלך.',
	'massmessage-form-header' => 'אפשר להשתמש בטופס להלן כדי לשלוח הודעות לרשימה מוגדרת. כל השדות נדרשים. ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage מידע נוסף]).',
	'massmessage-queued-count' => '[[Special:MassMessage|הודעות המוניות]] בתור',
	'massmessage-hidden-comment' => '<!-- הודעה שנשלחה על־ידי User:$1@$2 באמצעות הרשימה בדף $3 -->',
	'massmessage-optout-category' => 'משתמשים שביקשו לא לקבל הודעות המוניות',
	'massmessage-badhtml' => 'ייתכן שבהודעה שלך יש {{PLURAL:$2|תג HTML לא סגור|תגי HTML לא סגורים}}: $1.',
	'massmessage-parse-badurl' => '"$1" אינו אתר תקין.',
	'massmessage-parse-badpage' => '"$1" אינו שם דף תקין.',
	'massmessage-global-disallowed' => 'אפשר לשלוח הודעות רק לדפים מקומיים.',
	'right-massmessage' => 'שליחה של הודעות למשתמשים מרובים',
	'action-massmessage' => 'לשלוח הודעות למשתמשים רבים',
	'log-name-massmessage' => 'יומן הודעות המוניות',
	'log-description-massmessage' => 'האירועים האלה עוקבים אחרי שליחת הודעות.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|שלח|שלחה}} הודעה אל $3',
	'logentry-massmessage-failure' => 'מסירה של "$4" ל{{GRAMMAR:תחילית|$3}} נכשלה עם הודעת השגיאה <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'מסירה של "$4" ל{{GRAMMAR:תחילית|$3}} לא התבצעה כי היעד נמצא במרחב שמות שלא ניתן לשלוח אליו',
	'logentry-massmessage-skipoptout' => 'מסירה של "$4" ל{{GRAMMAR:תחילית|$3}} לא התבצעה כי היעד ביקש שלא ימסרו לו הודעות',
	'logentry-massmessage-skipnouser' => 'מסירה של "$4" ל{{GRAMMAR:תחילית|$3}} לא התבצעה כי חשבון המשתמש אינו קיים',
	'massmessage-cannot-rename' => 'זה חשבון מערכת ולא ניתן לשנות את שמו.',
	'massmessage-nextsteps' => '[[Special:MassMessage|שליחת הודעה אחרת]] או [[Special:Statistics|הצגה של מספר ההודעות בתור]].',
);

/** Hindi (हिन्दी)
 * @author Siddhartha Ghai
 */
$messages['hi'] = array(
	'massmessage' => 'सामूहिक सन्देश भेजें',
	'massmessage-desc' => 'सदस्यों को अनेक सदस्यों की एक सूची को सामूहिक सन्देश भेजने में मदद करता है',
	'massmessage-form-spamlist' => 'पृष्ठ जिस पर उन पृष्ठों की सूची है जिन पर सन्देश छोड़ना है:',
	'massmessage-form-subject' => 'सन्देश का विषय (सम्पादन सारांश की तरह भी प्रयुक्त):',
	'massmessage-form-message' => 'सन्देश:',
	'massmessage-form-preview' => 'पूर्वावलोकन',
	'massmessage-form-submit' => 'भेजें',
	'massmessage-fieldset-preview' => 'पूर्वावलोकन',
	'massmessage-submitted' => '{{PLURAL:$1|$1 पृष्ठ|$1 पृष्ठों}} पर भेजने हेतु आपका सन्देश कतार में जोड़ दिया गया है।',
	'massmessage-just-preview' => 'यह केवल एक झलक है। सन्देश भेजने के लिए "{{int:massmessage-form-submit}}" पर क्लिक करें।',
	'massmessage-spamlist-doesnotexist' => 'निर्दिष्ट पृष्ठ-सूची पृष्ठ मौजूद नहीं है।',
	'massmessage-empty-subject' => 'विषय खाली है।',
	'massmessage-empty-message' => 'सन्देश खाली है।',
	'massmessage-unescaped-langlinks' => 'चेतावनी: आपके सन्देश में बिना कोलन के अंतरविकि कड़ियाँ हैं।',
	'massmessage-form-header' => 'एक निर्दिष्ट सूची को सन्देश देने के लिए निम्न फ़ॉर्म का प्रयोग करें। सभी इनपुट आवश्यक हैं ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage अधिक जानकारी])।',
	'massmessage-queued-count' => '[[Special:MassMessage|सामूहिक सन्देश]] जो कतार में हैं',
	'massmessage-hidden-comment' => '<!-- $3 पर मौजूद सूची का प्रयोग कर के User:$1@$2 द्वारा भेजा गया सन्देश -->',
	'massmessage-optout-category' => 'सन्देश ना पाना चयनित',
	'massmessage-badhtml' => 'आपके सन्देश में {{PLURAL:$2|एक खुला एच॰टी॰एम॰एल टैग हो सकता है|खुले एच॰टी॰एम॰एल टैग हो सकते हैं}}: $1।',
	'massmessage-parse-badurl' => '"$1" मान्य साइट नहीं है।',
	'massmessage-parse-badpage' => '"$1" मान्य पृष्ठ शीर्षक नहीं है।',
	'massmessage-global-disallowed' => 'सन्देश केवल स्थानीय पृष्ठों को भेजे जा सकते हैं।',
	'right-massmessage' => 'अनेक सदस्यों को इकट्ठे सन्देश भेजें',
	'action-massmessage' => 'अनेक सदस्यों को इकट्ठे सन्देश भेजने',
	'log-name-massmessage' => 'सामूहिक सन्देश लॉग',
	'log-description-massmessage' => 'ये घटनाएँ सदस्यों द्वारा सन्देश भेजने की हैं।',
	'logentry-massmessage-send' => '$1 ने $3 {{GENDER:$2|को सन्देश भेजा}}',
	'logentry-massmessage-failure' => '"$4" सन्देश $3 पृष्ठ पर छोड़ना विफल रहा। त्रुटि कोड: <code>$5</code>',
	'logentry-massmessage-skipbadns' => '"$4" पृष्ठ पर $3 सन्देश नहीं भेजा गया क्योंकि लक्ष्य पृष्ठ ऐसे नामस्थान में है जिसमें सन्देश नहीं छोड़े जा सकते।',
	'logentry-massmessage-skipoptout' => '"$4" सन्देश $3 पृष्ठ पर नहीं भेजा गया क्योंकि लक्ष्य पृष्ठ ने सन्देश ना पाने का चयन किया है।',
	'logentry-massmessage-skipnouser' => '"$4" सन्देश $3 पृष्ठ पर नहीं भेजा गया क्योंकि सदस्य खाता मौजूद नहीं है।',
	'massmessage-cannot-rename' => 'यह एक सिस्टम खाता है और इसका नाम नहीं बदला जा सकता है।',
	'massmessage-nextsteps' => '[[Special:MassMessage|एक और सन्देश भेजें]] या [[Special:Statistics|देखें कितने सन्देश कतार में हैं]]।',
);

/** Iloko (Ilokano)
 * @author Lam-ang
 */
$messages['ilo'] = array(
	'massmessage' => 'Agipatulod ti adu a mensahe',
	'massmessage-desc' => 'Agipalubos kadagiti agar-aramat ti nalaka a panangipatulod ti mensahe iti listaan dagiti agar-aramat',
	'massmessage-form-spamlist' => 'Panid nga aglaon ti listaan ti pampanid a pangibatian ti mensahe:',
	'massmessage-form-subject' => 'Suheto iti mensahe (mausar pay a kas ti pakabuklan ti inurnos):',
	'massmessage-form-message' => 'Bagi ti mensahe:',
	'massmessage-form-preview' => 'Ipadas',
	'massmessage-form-submit' => 'Ipatulod',
	'massmessage-fieldset-preview' => 'Ipadas',
	'massmessage-submitted' => 'Ti pannakaipatulod ti mensahe iti {{PLURAL:$1|$1 panid|$1 pampanid}} ket naiyur-urayen.',
	'massmessage-just-preview' => 'Daytoy ket panagpadas laeng. Talmegan ti "{{int:massmessage-form-submit}}" tapno maipatulod ti mensahe.',
	'massmessage-spamlist-doesnotexist' => 'Awan ti nainaganan a panid ti listaan.',
	'massmessage-empty-subject' => 'Awan ti linaon daytoy a linia ti suheto.',
	'massmessage-empty-message' => 'Awan ti linaon ti bagi ti mensahe.',
	'massmessage-unescaped-langlinks' => 'Ballaag: Adda dagiti di napatalawan a silpo ti pagsasao iti mensahem.',
	'massmessage-form-header' => 'Usaren ti porma dita baba tapno makaipatulod kadagiti mensahe iti nainaganan a listaan.
Nasken a maikkan amin dagiti pagikabilan.',
	'massmessage-queued-count' => '[[Special:MassMessage|Dagiti adu a mensahe]] a naiyur-uray',
	'massmessage-hidden-comment' => '<!-- Mensahe nga inpatulod babaen ti Agar-aramat:$1@$2 a nagusar ti listaan iti $3 -->',
	'massmessage-optout-category' => 'Di nagkayat iti panagipatulod ti mensahe',
	'massmessage-badhtml' => 'Ti mensahem ket mabalin nga adda {{PLURAL:$2|maysa a saan a narikepan nga etiketa ti HTML|dagiti saan a narikepan nga etiketa ti HTML}}: $1.',
	'massmessage-parse-badurl' => 'Ti "$1" ket saan nga umiso a sitio.',
	'massmessage-parse-badpage' => 'Ti "$1" ket saan nga umiso a titulo ti panid.',
	'massmessage-global-disallowed' => 'Dagiti mensahe ket mabalin laeng a maipatulod kadagiti lokal a panid.',
	'right-massmessage' => 'Maminsan nga agipatulod ti mensahe kadagiti adu nga agar-aramat',
	'action-massmessage' => 'maminsan nga agipatulod ti mensahe kadagiti adu nga agar-aramat',
	'log-name-massmessage' => 'Listaan ti adu a mensahe',
	'log-description-massmessage' => 'Dagitoy a pasamak ket surotenna dagiti agar-aramat a nagipatulod kadagiti mensahe.',
	'logentry-massmessage-send' => 'Ni $1 {{GENDER:$2|ket nagipatulod ti mensahe}} idiay $3',
	'logentry-massmessage-failure' => 'Napaay ti pannakaipatulod iti "$4" idiay $3 nga adda ti biddut a kodigo iti <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Nalabsan idi ti pannakaipatulod iti "$4" idiay $3 gaputa ti puntaan ket adda idi iti nagan ti espasio a saan a mabalin a pagipablaakan',
	'logentry-massmessage-skipoptout' => 'Nalabsan idi ti pannakaipatulod iti "$4" idiay $3 gaputa ti puntaan ket di nagkayat iti panangipatulod ti mensahe',
	'logentry-massmessage-skipnouser' => 'Nalabsan idi ti pannakaipatulod iti "$4" idiay $3 gaputa awan ti pakabilangan ti agar-aramat',
	'massmessage-cannot-rename' => 'Daytoy ket pakabilangan ti sistema ken saan a mabalin a managanan manen.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Agipatulod pay ti sabali a mensahe]] wenno [[Special:Statistics|kitaen no mano kadagiti mensahe ti naiyur-uray]].',
);

/** Italian (italiano)
 * @author Beta16
 * @author Rosh
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
	'massmessage-unescaped-langlinks' => 'Attenzione: ci sono collegamenti di lingua senza escape nel tuo messaggio.',
	'massmessage-form-header' => 'Utilizza il modulo sottostante per inviare messaggi a un elenco specificato. Tutti i campi sono obbligatori. ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage ulteriori informazioni]).',
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
	'log-description-massmessage' => "Questi eventi registrano l'invio di messaggi ad utenti.",
	'logentry-massmessage-send' => '$1 {{GENDER:$2|ha inviato un messaggio}} a $3',
	'logentry-massmessage-failure' => 'Consegna di "$4" a $3 non riuscita con codice di errore<code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Consegna di "$4" a $3 omessa poiché la destinazione si trova in un namespace su cui non può essere pubblicato',
	'logentry-massmessage-skipoptout' => 'Consegna di "$4" a $3 omessa poiché la destinazione è esclusa dalla ricezione di messaggi massivi',
	'logentry-massmessage-skipnouser' => 'Consegna di "$4" a $3 omessa poiché l\'utenza non esiste',
	'massmessage-cannot-rename' => "Questa è un'utenza di sistema e non può essere rinominata.",
	'massmessage-nextsteps' => '[[Special:MassMessage|Invia un altro messaggio]] o [[Special:Statistics|vedi quanti messaggi sono accodati]].',
);

/** Japanese (日本語)
 * @author Fryed-peach
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
	'massmessage-unescaped-langlinks' => '警告: メッセージ内にエスケープされていない言語リンクがあります。',
	'massmessage-form-header' => 'このフォームでは、指定した一覧のページにメッセージを送信できます。すべて必須項目です ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage 詳細情報])。',
	'massmessage-queued-count' => '順番待ち中の[[Special:MassMessage|一括送信メッセージ]]',
	'massmessage-hidden-comment' => '<!-- User:$1@$2 が $3 のリストを使用して送信したメッセージ -->',
	'massmessage-optout-category' => 'メッセージ配信拒否',
	'massmessage-badhtml' => 'メッセージ内に、{{PLURAL:$2|閉じられていない HTML タグ}}があります: $1',
	'massmessage-parse-badurl' => '「$1」は有効なサイトではありません。',
	'massmessage-parse-badpage' => '「$1」は有効なページ名ではありません。',
	'massmessage-global-disallowed' => 'メッセージを送信できるのはローカル ページのみです。',
	'right-massmessage' => '複数の利用者に一度にメッセージを送信',
	'action-massmessage' => '複数の利用者へのメッセージの一斉送信',
	'log-name-massmessage' => '一斉メッセージ記録',
	'log-description-massmessage' => 'これらのイベントは、利用者によるメッセージの送信を追跡します。',
	'logentry-massmessage-send' => '$1 が $3 に{{GENDER:$2|メッセージを送信しました}}',
	'logentry-massmessage-failure' => '$3 に「$4」を送信する際にコード <code>$5</code> のエラーが発生しました',
	'logentry-massmessage-skipbadns' => '宛先の名前空間には投稿できないため、$3 への「$4」の送信がスキップされました',
	'logentry-massmessage-skipoptout' => '宛先がメッセージ配信を拒否しているため、$3 への「$4」の送信をスキップしました',
	'logentry-massmessage-skipnouser' => '利用者アカウントが存在しないため、$3 への「$4」の送信がスキップされました',
	'massmessage-cannot-rename' => 'これはシステム アカウントであるため、名前を変更できません。',
	'massmessage-nextsteps' => '[[Special:MassMessage|別のメッセージを送信できます]]。[[Special:Statistics|待ち行列内のメッセージ数を閲覧することもできます]]。',
);

/** Georgian (ქართული)
 * @author David1010
 */
$messages['ka'] = array(
	'massmessage' => 'მასობრივი შეტყობინების გაგზავნა',
	'massmessage-form-spamlist' => 'გვერდი, იმ გვერდების სიით სადაც შეტყობინების დატოვებაა საჭირო:',
	'massmessage-form-subject' => 'შეტყობინების თემა (გამოიყენება აგრეთვე ცვლილების აღსაწერად):',
	'massmessage-form-message' => 'შეტყობინების ტექსტი:',
	'massmessage-empty-message' => 'ტექსტის ველი ცარიელია.',
);

/** Korean (한국어)
 * @author Hym411
 * @author Kwj2772
 * @author Priviet
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
	'massmessage-unescaped-langlinks' => '경고: 당신의 메시지에 이스케이프되지 않은 언어 링크가 있습니다.',
	'massmessage-form-header' => '지정된 목록에서 메시지를 보내려면 아래 양식을 사용하세요. 모든 필드는 필수입니다([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage 자세한 정보]).',
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
	'log-description-massmessage' => '이 기록 내용은 메시지를 보낸 사용자를 추적합니다.',
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
	'massmessage-unescaped-langlinks' => 'Opgepasst: Et gëtt net valabel Sproochlinken an Ärem Message.',
	'massmessage-form-header' => 'Benotzt de Formulaire hei drënner fir Messagen un eng spezifesch Lëscht ze schécken. All Felder sinn obligatoresch. ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage méi Informatiounen]).',
	'massmessage-hidden-comment' => "<!-- Message gouf vum User:$1@$2 geschéckt, deen d'Lëscht op $3 benotzt huet-->",
	'massmessage-parse-badurl' => '"$1" ass kee valabele Site',
	'massmessage-parse-badpage' => '"$1" ass kee valabelen Titel fir eng Säit',
	'massmessage-global-disallowed' => 'Message kënnen nëmme op lokal Säite geschéckt ginn.',
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
	'massmessage-unescaped-langlinks' => 'Предупредување: Во пораката имате неекранизирани јазични врски.',
	'massmessage-form-header' => 'Послужете се со образецот подолу за да испраќате на пораки на укажан список на примачи. Сите полиња се задолжителни ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage повеќе информации]).',
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
	'log-description-massmessage' => 'Овие настани следат корисници што испраќаат пораки.',
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
	'massmessage' => 'കൂട്ടമായി സന്ദേശങ്ങൾ അയയ്ക്കുക',
	'massmessage-desc' => 'ഒരു പട്ടികയിലെ ഉപയോക്താക്കൾക്കാകെ ഒരുമിച്ച് എളുപ്പം സന്ദേശം അയയ്ക്കാൻ ഉപയോക്താക്കളെ അനുവദിക്കൽ',
	'massmessage-form-spamlist' => 'സന്ദേശമിടണ്ട താളുകളുടെ പട്ടിക ഈ താളിലുണ്ട്:',
	'massmessage-form-subject' => 'സന്ദേശത്തിന്റെ വിഷയം (തിരുത്തലിന്റെ സംഗ്രഹമായും ഉപയോഗിക്കപ്പെടും):',
	'massmessage-form-message' => 'സന്ദേശത്തിന്റെ ഉള്ളടക്കം:',
	'massmessage-form-preview' => 'എങ്ങനെയുണ്ടെന്നു കാണുക',
	'massmessage-form-submit' => 'അയക്കുക',
	'massmessage-fieldset-preview' => 'എങ്ങനെയുണ്ടെന്ന് കാണുക',
	'massmessage-submitted' => '{{PLURAL:$1|ഒരു താളിൽ|$1 താളുകളിൽ}}താങ്കളുടെ സന്ദേശത്തിന്റെ വിതരണം തയ്യാറായി നിൽക്കുന്നു.',
	'massmessage-just-preview' => 'ഇത് ഒരു പ്രിവ്യൂ മാത്രമാണ്. സന്ദേശം അയയ്ക്കാൻ "{{int:massmessage-form-submit}}" അമർത്തുക.',
	'massmessage-spamlist-doesnotexist' => 'നൽകിയിരിക്കുന്ന താളുകളുടെ പട്ടിക നിലവിലില്ല.',
	'massmessage-empty-subject' => 'വിഷയം നൽകിയിട്ടില്ല.',
	'massmessage-empty-message' => 'സന്ദേശത്തിന്റെ ഉള്ളടക്കം നൽകിയിട്ടില്ല.',
	'massmessage-unescaped-langlinks' => 'മുന്നറിയിപ്പ്: താങ്കളുടെ സന്ദേശത്തിൽ ഭാഷാ കണ്ണികളുടെ മനസ്സിലാക്കാൻ ബുദ്ധിമുട്ടുള്ള രൂപങ്ങൾ ഉൾപ്പെട്ടിട്ടുണ്ട്.',
	'massmessage-form-header' => 'ഒരു പ്രത്യേക പട്ടികയിലേക്ക് സന്ദേശങ്ങൾ അയയ്ക്കാൻ താഴെക്കൊടുക്കുന്ന ഫോം ഉപയോഗിക്കുക. എല്ലാം പൂരിപ്പിക്കുക ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage കൂടുതൽ വിവരങ്ങൾ]).',
	'massmessage-queued-count' => '[[Special:MassMessage|കൂട്ട സന്ദേശങ്ങൾ]] അയയ്ക്കാൻ സജ്ജമായി നിൽക്കുന്നു',
	'massmessage-hidden-comment' => '<!-- $3 എന്ന പട്ടിക ഉപയോഗിച്ച് ഉപയോക്താവ്:$1@$2 അയച്ച സന്ദേശം  -->',
	'massmessage-optout-category' => 'സന്ദേശം വിതരണം ചെയ്യാൻ തിരഞ്ഞെടുത്തിരിക്കുന്നു',
	'massmessage-badhtml' => 'താങ്കളുടെ സന്ദേശത്തിൽ {{PLURAL:$2|അടയ്ക്കാത്ത എച്ച്.റ്റി.എം.എൽ. ടാഗ്|അടയ്ക്കാത്ത എച്ച്.റ്റി.എം.എൽ. ടാഗുകൾ}} ഉണ്ടായേക്കാം: $1.',
	'massmessage-parse-badurl' => '"$1" എന്നത് സാധുവായ സൈറ്റ് അല്ല.',
	'massmessage-parse-badpage' => '"$1" എന്നത് സാധുവായ തലക്കെട്ടല്ല.',
	'massmessage-global-disallowed' => 'സന്ദേഅങ്ങൾ പ്രാദേശികതാളുകളിലേയ്ക്ക് അയയ്ക്കാൻ മാത്രമേ കഴിയുള്ളു.',
	'right-massmessage' => 'നിരവധി ഉപയോക്താക്കൾക്ക് ഒറ്റയടിക്ക് സന്ദേശം അയയ്ക്കുക',
	'action-massmessage' => 'നിരവധി ഉപയോക്താക്കൾക്ക് ഒറ്റയടിക്ക് സന്ദേശം അയയ്ക്കുക',
	'log-name-massmessage' => 'കൂട്ടസന്ദേശ രേഖ',
	'log-description-massmessage' => 'ഇവ സന്ദേശമയയ്ക്കുന്ന ഉപയോക്താക്കളെ രേഖപ്പെടുത്തുന്നു.',
	'logentry-massmessage-send' => '$1 $3 എന്ന താളിൽ {{GENDER:$2|സന്ദേശമിട്ടു}}',
	'logentry-massmessage-failure' => '$3 എന്നതിലേക്കുള്ള "$4" എന്നതിന്റെ വിതരണം <code>$5</code> എന്ന പിഴവ് കോഡോടെ പരാജയപ്പെട്ടു',
	'logentry-massmessage-skipbadns' => '$3 നൽകാൻ കഴിയാത്ത നാമമേഖലയിലായതിനാൽ അവിടെ "$4" ന്റെ വിതരണം ഒഴിവാക്കി',
	'logentry-massmessage-skipoptout' => 'സന്ദേശങ്ങൾ വേണ്ട എന്ന് ലക്ഷ്യം തിരഞ്ഞെടുത്തിട്ടുള്ളതിനാൽ $3 എന്നതിലേക്കുള്ള "$4" എന്നതിന്റെ വിതരണം ഒഴിവാക്കി',
	'logentry-massmessage-skipnouser' => 'ഉപയോക്തൃ അംഗത്വം നിലവിലില്ലാത്തതിനാൽ $3 എന്നതിലേക്കുള്ള "$4" എന്നതിന്റെ വിതരണം ഒഴിവാക്കി',
	'massmessage-cannot-rename' => 'സംവിധാനത്തിന്റെ ഭാഗമായ ഈ അംഗത്വം പുനർനാമകരണം ചെയ്യാനാവില്ല.',
	'massmessage-nextsteps' => '[[Special:MassMessage|മറ്റൊരു സന്ദേശം അയയ്ക്കുക]] അല്ലെങ്കിൽ [[Special:Statistics|എത്ര സന്ദേശങ്ങൾ പോകാനവശേഷിക്കുന്നുവെന്ന് കാണുക]].',
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
	'massmessage-form-header' => 'खालील निवेदन एका उल्लेखित यादीस संदेश पाठविण्यास वापरा.सर्व क्षेत्रे आवश्यक आहेत. ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage अधिक माहितीसाठी]).',
	'massmessage-queued-count' => 'रांगेतील [[Special:MassMessage|एकगठ्ठा संदेश]]',
	'right-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'action-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'log-name-massmessage' => 'एकगठ्ठा संदेशाच्या नोंदी',
	'log-description-massmessage' => 'हे प्रसंग,[[Special:MassMessage]] मार्फत संदेश पाठविणाऱ्या सदस्यांचा थांग (ट्रॅक) लावतात.', # Fuzzy
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
	'massmessage-form-header' => 'Gunakan borang yang berikut untuk mengirim pesanan kepada senarai yang tertentu. semua ruangan wajib diisi ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage keterangan lanjut]).',
	'massmessage-queued-count' => '[[Special:MassMessage|Pesanan beramai-ramai]] terbaris gilir',
	'massmessage-hidden-comment' => '<!-- Pesanan dihantar oleh Pengguna:$1@$2 yang menggunakan senarai di $3 -->',
	'massmessage-optout-category' => 'Terkecuali dari pengiriman pesanan',
	'massmessage-badhtml' => 'Pesanan anda mungkin mengandungi {{PLURAL:$2|suatu teg HTML yang tidak tertutup|teg-teg HTML yang tidak tertutup}}: $1.',
	'massmessage-parse-badurl' => '"$1" bukan laman web yang sah',
	'massmessage-parse-badpage' => '"$1" bukan tajuk halaman yang sah',
	'right-massmessage' => 'Mengirim pesanan kepada berbilang pengguna sekali gus',
	'action-massmessage' => 'menghantar pesanan kepada berbilang pengguna sekali gus',
	'log-name-massmessage' => 'Log pesanan beramai-ramai',
	'log-description-massmessage' => 'Peristiwa-peristiwa ini menjejak pengguna yang mengirim pesanan melalui [[Special:MassMessage]].', # Fuzzy
	'logentry-massmessage-send' => '$1 telah {{GENDER:$2|menghantar}} pesanan kepada $3',
	'logentry-massmessage-failure' => '"$4" gagal dikirim kepada $3 dengan kod ralat <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Pengiriman "$4" kepada $3 dilangkau kerana sasaran tergolong dalam ruang nama yang tidak boleh dikirimi',
	'logentry-massmessage-skipoptout' => 'Pengiriman "$4" kepada $3 dilangkau kerana sasaran mengecualikan diri daripada pengiriman pesanan.',
	'logentry-massmessage-skipnouser' => 'Pengiriman "$4" kepada $3 dilangkau kerana akaun pengguna tidak wujud',
	'massmessage-cannot-rename' => 'Ini ialah akaun sistem yang tidak boleh ditukar namanya.',
);

/** Dutch (Nederlands)
 * @author Arent
 * @author Hansmuller
 * @author Konovalov
 * @author Siebrand
 * @author Southparkfan
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
	'massmessage-unescaped-langlinks' => 'Waarschuwing: uw bericht bevat onjuiste taalkoppelingen.',
	'massmessage-form-header' => 'Gebruik het onderstaande formulier om berichten te verzenden aan een lijst ontvangers. Alle velden zijn verplicht ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage meer informatie]).',
	'massmessage-queued-count' => '[[Special:MassMessage|Bulkberichten]] in de wachtrij',
	'massmessage-hidden-comment' => '<!-- Bericht verzonden door User:$1@$2 via de lijst op de pagina $3 -->',
	'massmessage-optout-category' => 'Wil geen berichten ontvangen',
	'massmessage-badhtml' => 'Uw bericht heeft mogelijk {{PLURAL:$2|een ongesloten HTML-tag|ongesloten HTML-tags}}: $1.',
	'massmessage-parse-badurl' => '"$1" is geen geldige site.',
	'massmessage-parse-badpage' => '"$1" is geen geldige paginanaam.',
	'massmessage-global-disallowed' => "Berichten kunnen alleen afgeleverd worden op lokale pagina's.",
	'right-massmessage' => 'Berichten verzenden aan meerdere ontvangers tegelijk',
	'action-massmessage' => 'berichten te verzenden aan meerdere ontvangers tegelijk',
	'log-name-massmessage' => 'Bulkberichtenlogboek',
	'log-description-massmessage' => 'Deze gebeurtenissen zijn gerelateerd aan verzonden berichten via de functie bulkberichten.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|heeft}} een bericht geplaatst op $3',
	'logentry-massmessage-failure' => 'Het afleveren van "$4" voor $3 is mislukt met de foutcode <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Het afleveren van "$4" voor $3 is overgeslagen omdat het doel zich in een naamruimte bevindt waarin geen berichten geplaatst kunnen worden',
	'logentry-massmessage-skipoptout' => 'Het afleveren van "$4" voor $3 is overgeslagen omdat er op de doelpagina geen berichten geplaatst mogen worden',
	'logentry-massmessage-skipnouser' => 'Het afleveren van "$4" voor $3 is overgeslagen omdat de gebruiker niet bestaat',
	'massmessage-cannot-rename' => 'Dit is een systeemgebruiker die niet hernoemd kan worden.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Nog een bericht verzenden]] of [[Special:Statistics|bekijken hoeveel berichten er in de wachtrij staan]].',
);

/** Norwegian Nynorsk (norsk nynorsk)
 * @author Njardarlogar
 */
$messages['nn'] = array(
	'massmessage' => 'Send massemeldingar',
	'massmessage-desc' => 'Gjer det mogeleg for brukarar å greitt kunna senda ei melding til ei liste med brukarar',
	'massmessage-form-spamlist' => 'Side som inneheld ei liste over sider som det skal leggjast att ei melding på:',
	'massmessage-form-subject' => 'Meldingsemne (vert òg brukt som endringssamandrag):',
	'massmessage-form-message' => 'Meldingstekst:',
	'massmessage-form-preview' => 'Førehandsvis',
	'massmessage-form-submit' => 'Send',
	'massmessage-fieldset-preview' => 'Førehandsvising',
	'massmessage-submitted' => 'Meldingsleveransen din til {PLURAL:$1|éi side|$1 sider}} er sett i kø.', # Fuzzy
	'massmessage-just-preview' => 'Dette er berre ei førehandsvising. Trykk «{{int:massmessage-form-submit}}» for å senda meldinga.',
	'massmessage-spamlist-doesnotexist' => 'Den oppgjevne sida med sideliste finst ikkje.',
	'massmessage-empty-subject' => 'Emnelina er tom.',
	'massmessage-empty-message' => 'Meldingsteksten er tom.',
	'massmessage-unescaped-langlinks' => 'Åtvaring: Det er språklenkjer utan unnatak i meldinga di.',
	'massmessage-form-header' => 'Bruk skjemaet under for å senda meldingar til ei oppgjeven liste. Alle felta er påkravde.',
	'massmessage-queued-count' => 'Sette [[Special:MassMessage|massemeldingar]] i kø',
	'massmessage-hidden-comment' => '<!-- Melding send av Brukar:$1@$2 gjennom lista på $3 -->',
	'massmessage-optout-category' => 'Bortvalde frå meldingsmottak',
	'massmessage-badhtml' => 'Meldinga di kan ha {{PLURAL:$2|eit ope HTML-merke|opne HTML-merke}}: $1.',
	'massmessage-parse-badurl' => '«$1» er ikkje ein gild nettstad.',
	'massmessage-parse-badpage' => '«$1» er ikkje ein gild sidetittel.',
	'massmessage-global-disallowed' => 'Meldingar kan berre sendast til lokale sider.',
	'right-massmessage' => 'Senda ei melding til fleire brukarar samstundes',
	'action-massmessage' => 'senda ei melding til fleire brukarar samstundes',
	'log-name-massmessage' => 'Logg over massemeldingar',
	'log-description-massmessage' => 'Desse hendingane sporar brukarar som sender meldingar.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sende}} ei melding til $3',
	'logentry-massmessage-failure' => 'Leveringa av «$4» til «$3» gjekk ikkje; fekk feilkoden <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Leveringa av «$4» til «$3» var hoppa over av di målet låg i eit namnerom som det ikkje kan sendast meldingar til',
	'logentry-massmessage-skipoptout' => 'Leveringa av «$4» til «$3» vart hoppa over av di målet har valt bort meldingsmottak',
	'logentry-massmessage-skipnouser' => 'Leveringa av «$4» til «$3» vart hoppa over av di brukarkontoen ikkje finst',
	'massmessage-cannot-rename' => 'Dette er ein systemkonto og kan ikkje døypast om.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Send endå ei melding]] eller [[Special:Statistics|sjå kor mange meldingar som er i kø]].',
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
	'log-description-massmessage' => "Aqueles eveniments traçan los utilizaires qu'an mandat de messatges.",
	'logentry-massmessage-send' => '$1 {{GENDER:$2|a mandat un messatge}} a $3',
	'logentry-massmessage-failure' => 'La liurason de « $4 » sus $3 a fracassat amb un còde d’error de <code>$5</code>',
	'massmessage-cannot-rename' => "Aquò's un compte sistèma que pòt pas èsser renomenat.",
);

/** Polish (polski)
 * @author Chrumps
 * @author Rzuwig
 * @author WTM
 * @author Woytecr
 */
$messages['pl'] = array(
	'massmessage' => 'Wysyłanie masowej wiadomości',
	'massmessage-desc' => 'Pozwala użytkownikom na wysłanie wiadomości do określonej listy użytkowników',
	'massmessage-form-spamlist' => 'Strona zawierająca listę stron, na których zostawić wiadomość:',
	'massmessage-form-subject' => 'Temat wiadomości (także używany jako podsumowanie edycji):',
	'massmessage-form-message' => 'Treść wiadomości:',
	'massmessage-form-preview' => 'Podgląd',
	'massmessage-form-submit' => 'Wyślij',
	'massmessage-fieldset-preview' => 'Podgląd',
	'massmessage-submitted' => 'Twoja wiadomość została umieszczona w kolejce.', # Fuzzy
	'massmessage-just-preview' => 'To jest tylko podgląd. Naciśnij "{{int:massmessage-form-submit}}" aby wysłać wiadomość.',
	'massmessage-spamlist-doesnotexist' => 'Określona strona z listą stron nie istnieje.',
	'massmessage-empty-subject' => 'Pole tematu jest puste.',
	'massmessage-empty-message' => 'Treść wiadomości jest pusta.',
	'massmessage-form-header' => 'Użyj poniższego formularza, aby wysłać wiadomości do określonej listy. Wszystkie pola są wymagane. ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage Więcej informacji]).',
	'massmessage-queued-count' => 'Zakolejkowane [[Special:MassMessage|masowe wiadomości]]',
	'massmessage-parse-badpage' => '„$1” nie jest poprawnym tytułem strony',
	'right-massmessage' => 'Wysyłanie wiadomości do wielu użytkowników jednocześnie',
	'action-massmessage' => 'wyślij wiadomość do wielu użytkowników jednocześnie',
	'log-name-massmessage' => 'Log masowych wiadomości',
	'log-description-massmessage' => 'To jest lista zdarzeń służąca do śledzenia wysyłanych wiadomości.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|wysłał|wysłała}} wiadomość do $3',
	'logentry-massmessage-skipbadns' => 'Dostarczanie wiadomości „$4” do $3 zostało pominięte, ponieważ miejsce docelowe znajdowało się w przestrzeni nazw, w której nie mogło być umieszczone',
	'logentry-massmessage-skipnouser' => 'Dostarczanie wiadomości „$4” do $3 zostało pominięte, ponieważ konto użytkownika nie istnieje',
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

/** Portuguese (português)
 * @author Hamilton Abreu
 * @author Helder.wiki
 */
$messages['pt'] = array(
	'massmessage-unescaped-langlinks' => 'Aviso: Há links interlínguas na sua mensagem que não começam com dois pontos.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|enviou uma mensagem}} para $3',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Cainamarques
 * @author Fúlvio
 * @author Helder.wiki
 * @author Luckas
 */
$messages['pt-br'] = array(
	'massmessage' => 'Enviar mensagem em massa',
	'massmessage-desc' => 'Permite que os usuários enviem facilmente uma mensagem para uma lista de usuários',
	'massmessage-form-spamlist' => 'Página que contêm uma lista de páginas nas quais deve ser entregue uma mensagem:',
	'massmessage-form-subject' => 'Assunto da mensagem (também usado como sumário de edição):',
	'massmessage-form-message' => 'Corpo da mensagem:',
	'massmessage-form-preview' => 'Visualizar',
	'massmessage-form-submit' => 'Enviar',
	'massmessage-fieldset-preview' => 'Visualização',
	'massmessage-submitted' => 'Sua mensagem foi adicionada à fila.', # Fuzzy
	'massmessage-just-preview' => 'Esta é apenas uma visualização. Pressione "{{int:massmessage-form-submit}}" para enviar a mensagem.',
	'massmessage-spamlist-doesnotexist' => 'A lista de páginas especificada não existe.',
	'massmessage-empty-subject' => 'O espaço do assunto está vazio.',
	'massmessage-empty-message' => 'O corpo da mensagem está vazio.',
	'massmessage-form-header' => 'Use o formulário abaixo para enviar mensagens a uma lista específcia. Todos os campos são obrigatórios.', # Fuzzy
	'massmessage-hidden-comment' => '<!-- Mensagem enviada pelo usuário:$1@$2 utilizando a lista $3 -->',
	'right-massmessage' => 'Envie uma mensagem para vários usuários ao mesmo tempo',
	'action-massmessage' => 'envie uma mensagem para vários usuários ao mesmo tempo',
	'log-name-massmessage' => 'Registro de mensagens em massa',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|enviou uma mensagem}} para $3',
);

/** Romansh (rumantsch)
 * @author Kazu89
 */
$messages['rm'] = array(
	'massmessage' => 'Trametter in messadi da massa',
	'log-name-massmessage' => 'Protocol da messadis da massa',
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
	'massmessage-form-header' => "Ause 'u module aqquà sotte pe mannà messàgge a 'n'elenghe specifiche. Tutte le cambe sò richieste.", # Fuzzy
	'massmessage-queued-count' => 'Accodate [[Special:MassMessage|messàgge de masse]]',
	'massmessage-hidden-comment' => "<!-- Messàgge mannate da l'Utende:$1@$2 ausanne l'elenghe a $3 -->",
	'massmessage-optout-category' => "Scartate da l'invie de le messàgge",
	'massmessage-badhtml' => "'U messàgge tune pò tenè {{PLURAL:$2|'nu tag HTML non achiuse|tag HTML non achiuse}}: $1.",
	'massmessage-parse-badurl' => '"$1" non g\'è \'nu site valide',
	'massmessage-parse-badpage' => '"$1" non g\'è \'nu titole d\'a vôsce valide',
	'right-massmessage' => "Manne 'nu messàgge a cchiù utinde jndr'à 'na botte",
	'action-massmessage' => "manne 'nu messàgge a cchiù utinde jndr'à 'na botte",
	'log-name-massmessage' => 'Archivije de le messàgge de masse',
	'log-description-massmessage' => "Ste avvenemìnde tracciane l'utinde ca mannane messàgge.",
	'logentry-massmessage-send' => "$1 {{GENDER:$2|mannate 'nu messàgge}} a $3",
	'logentry-massmessage-failure' => 'Invìe de "$4" a $3 fallite cu \'nu codece de errore <code>$5</code>',
	'logentry-massmessage-skipbadns' => '\'A spedizione de "$4" a $3 ha state zumbate purcé \'a destinazione stè jndr\'à \'nu namespace ca non ge pozze mannà',
	'logentry-massmessage-skipoptout' => '\'A spedizione de "$4" a $3 ha state zumbate purcé \'a destinazione non ge vole le messàgge de masse',
	'logentry-massmessage-skipnouser' => '\'A spedizione de "$4" a $3 ha state zumbate purcé \'u cunde utende non g\'esiste',
	'massmessage-cannot-rename' => "Quiste jè 'nu cunde de sisteme e non ge pò essere renominate.",
);

/** Russian (русский)
 * @author Kaganer
 * @author NBS
 * @author Okras
 */
$messages['ru'] = array(
	'massmessage' => 'Отправить массовое сообщение',
	'massmessage-desc' => 'Позволяет легко отправить сообщение списку пользователей',
	'massmessage-form-spamlist' => 'Страница, содержащая список страниц, на которых необходимо оставить сообщение:',
	'massmessage-form-subject' => 'Тема сообщения (также используется для описания изменений):',
	'massmessage-form-message' => 'Текст сообщения:',
	'massmessage-form-preview' => 'Предпросмотр',
	'massmessage-form-submit' => 'Отправить',
	'massmessage-fieldset-preview' => 'Предпросмотр',
	'massmessage-submitted' => 'Доставка вашего сообщения на {{PLURAL:$1|$1 страницу|$1 страниц|$1 страницы}} была поставлена в очередь.',
	'massmessage-just-preview' => 'Это только предварительный просмотр. Нажмите «{{int:massmessage-form-submit}}» для отправки сообщения.',
	'massmessage-spamlist-doesnotexist' => 'Указанная страница со списком страниц не существует.',
	'massmessage-empty-subject' => 'Строка с темой не заполнена.',
	'massmessage-empty-message' => 'Отсутствует текст сообщения.',
	'massmessage-unescaped-langlinks' => 'Предупреждение: в вашем сообщении есть неэкранированные языковые ссылки.',
	'massmessage-form-header' => 'Используйте форму ниже для отправки сообщения по указанному списку. Все поля обязательны для заполнения ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage дополнительная информация]).',
	'massmessage-queued-count' => 'Поставленные в очередь [[Special:MassMessage|массовые сообщения]]',
	'massmessage-hidden-comment' => '<!-- Сообщение отправил Участник:$1@$2, используя список на странице $3 -->',
	'massmessage-optout-category' => 'Отказано в доставке сообщений',
	'massmessage-badhtml' => 'Ваше сообщение может содержать {{PLURAL:$2|1=незакрытый HTML-тег|незакрытые HTML-теги}}: $1.',
	'massmessage-parse-badurl' => '«$1» не является корректным адресом сайта',
	'massmessage-parse-badpage' => '«$1» не является корректным заголовком страницы.',
	'massmessage-global-disallowed' => 'Сообщения могут быть отправлены только на локальные страницы.',
	'right-massmessage' => 'Отправка сообщения нескольким пользователям одновременно',
	'action-massmessage' => 'отправку сообщения нескольким пользователям одновременно',
	'log-name-massmessage' => 'Журнал массовых сообщений',
	'log-description-massmessage' => 'Это журнал, отслеживающий рассылку сообщений пользователям.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|отправил|отправила}} сообщение на $3',
	'logentry-massmessage-failure' => 'Доставка «$4» для $3 потерпела неудачу, вернув код ошибки <code>$5</code>',
	'logentry-massmessage-skipbadns' => 'Доставка «$4» для $3 была пропущена, поскольку целевая страница находится в пространстве имён, в котором не может быть опубликовано сообщение',
	'logentry-massmessage-skipoptout' => 'Доставка «$4» для $3 была пропущена, поскольку целевой страницей отказано в доставке сообщения',
	'logentry-massmessage-skipnouser' => 'Доставка «$4» для $3 была пропущена, поскольку учётная запись пользователя не существует',
	'massmessage-cannot-rename' => 'Это системная учётная запись, и она не может быть переименована.',
	'massmessage-nextsteps' => '[[Special:MassMessage|Отправить другое сообщение]] или [[Special:Statistics|посмотреть, сколько сообщений поставлено в очередь]].',
);

/** Scots (Scots)
 * @author John Reid
 */
$messages['sco'] = array(
	'massmessage-form-header' => 'Uise the form ablo tae send messages til ae speceefied leet. Aw fields ar required ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage mair informâtion]).',
);

/** Serbo-Croatian (srpskohrvatski / српскохрватски)
 * @author Kolega2357
 */
$messages['sh'] = array(
	'massmessage-form-preview' => 'Prikaži',
	'massmessage-form-submit' => 'Pošalji',
	'massmessage-fieldset-preview' => 'Prikaži',
	'log-name-massmessage' => 'Evidencija masovnih poruka',
	'log-description-massmessage' => 'Ovi događaji prate slanje poruka korisnicima.',
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
 * @author Lokal Profil
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
	'massmessage-unescaped-langlinks' => 'Varning: Det finns "unescaped" språklänkar i ditt meddelande.',
	'massmessage-form-header' => 'Använd formuläret nedan för att skicka meddelanden till en angiven lista. Alla fält är obligatoriska ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage för mer information]).',
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
	'log-description-massmessage' => 'Dessa händelser spårar användare som skickar meddelanden.',
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

/** Thai (ไทย)
 * @author Nullzero
 */
$messages['th'] = array(
	'massmessage-optout-category' => 'หน้าที่ไม่รับข้อความ',
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
	'massmessage-unescaped-langlinks' => 'Увага: наявні неекрановані мовні посилання в повідомленні.',
	'massmessage-form-header' => "Використовуйте форму нижче для надсилання повідомлення за вказаним списком. Всі поля обов'язкові для заповнення ([https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage додаткова інформація]).",
	'massmessage-queued-count' => 'У черзі [[Special:MassMessage|масових повідомлень]]',
	'massmessage-hidden-comment' => '<!-- Повідомлення надіслано користувачем:$1@$2 використовується список у $3 -->',
	'massmessage-optout-category' => 'Відмова від доставки повідомлень',
	'massmessage-badhtml' => 'Ваше повідомлення, можливо, має {{PLURAL:$2|1=незакритий теґ HTML|незакриті теґи HTML}}:  $1 .',
	'massmessage-parse-badurl' => '"$1" не є коректним сайтом',
	'massmessage-parse-badpage' => '"$1" не є коректною назвою сторінки',
	'massmessage-global-disallowed' => 'Повідомлення можна надіслати лише до місцевих сторінок.',
	'right-massmessage' => 'Надіслати повідомлення кільком користувачам одночасно',
	'action-massmessage' => 'надіслати повідомлення кільком користувачам одночасно',
	'log-name-massmessage' => 'Журнал масових повідомлень',
	'log-description-massmessage' => 'Ці події відстежують надсилання повідомлень користувачами.',
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
	'massmessage' => 'Gửi thông báo rộng rãi',
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
	'massmessage-unescaped-langlinks' => 'Cảnh báo: Thông báo của bạn có liên kết ngôn ngữ không được thoát.',
	'massmessage-form-header' => 'Hãy điền biểu mẫu sau để nhắn tin cho một danh sách được định rõ. Tất cả các trường là bắt buộc.',
	'massmessage-queued-count' => 'Số [[Special:MassMessage|tin nhắn cho nhiều người]] trong hàng đợi',
	'massmessage-hidden-comment' => '<!-- Tin nhắn của Thành viên:$1@$2 gửi cho mọi người trong danh sách tại $3 -->',
	'massmessage-optout-category' => 'Trang từ chối nhận tin nhắn',
	'massmessage-badhtml' => 'Tin nhắn của bạn có thể không đóng {{PLURAL:$2|một thẻ HTML|các thẻ HTML}}: $1.',
	'massmessage-parse-badurl' => '“$1” không phải là trang Web hợp lệ.',
	'massmessage-parse-badpage' => '“$1” không phải là tên trang hợp lệ.',
	'massmessage-global-disallowed' => 'Chỉ có thể gửi thông điệp cho những người trong một trang địa phương.',
	'right-massmessage' => 'Gửi thông báo cho nhiều người cùng một lúc',
	'action-massmessage' => 'gửi thông báo cho nhiều người cùng một lúc',
	'log-name-massmessage' => 'Nhật trình thông báo rộng rãi',
	'log-description-massmessage' => 'Nhật trình này có các tin nhắn được gửi cho nhiều người dùng.',
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
 * @author Cwek
 * @author Gakmo
 * @author Liuxinyu970226
 * @author Qiyue2001
 * @author Shizhao
 * @author Yfdyh000
 */
$messages['zh-hans'] = array(
	'massmessage' => '群发消息',
	'massmessage-desc' => '使用户可以轻松将消息发送给一个列表中的用户',
	'massmessage-form-spamlist' => '包含有要留言页面名单的页面：',
	'massmessage-form-subject' => '消息主题（同时用作编辑摘要）：',
	'massmessage-form-message' => '消息正文：',
	'massmessage-form-preview' => '预览',
	'massmessage-form-submit' => '发送',
	'massmessage-fieldset-preview' => '预览',
	'massmessage-submitted' => '您要投递到$1个页面的消息已开始排队。',
	'massmessage-just-preview' => '这只是预览。点击“{{int:massmessage-form-submit}}”发出此消息。',
	'massmessage-spamlist-doesnotexist' => '指定的页面列表页面不存在。',
	'massmessage-empty-subject' => '主题为空。',
	'massmessage-empty-message' => '消息正文为空。',
	'massmessage-unescaped-langlinks' => '警告：在你的消息中有未转义的语言链接。',
	'massmessage-form-header' => '使用下面的表单以将消息发送到指定的列表。所有字段都是必需的（[https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:MassMessage 更多信息]）。',
	'massmessage-queued-count' => '[[Special:MassMessage|群发消息]]已排队',
	'massmessage-hidden-comment' => '<!-- 消息发送者 User:$1@$2，使用的列表为 $3 -->',
	'massmessage-optout-category' => '不再接受群发投递的消息',
	'massmessage-badhtml' => '你的消息可能含有$2个未闭合的HTML标签：$1。',
	'massmessage-parse-badurl' => '“$1”不是一个有效的站点。',
	'massmessage-parse-badpage' => '“$1”不是一个有效的页面标题。',
	'massmessage-global-disallowed' => '消息只可以发送至本地页面。',
	'right-massmessage' => '一次将一条消息发送给多个用户',
	'action-massmessage' => '一次将一条消息发送给多个用户',
	'log-name-massmessage' => '批量消息日志',
	'log-description-massmessage' => '这些事件跟踪着用户发送的消息。',
	'logentry-massmessage-send' => '$1{{GENDER:$2|发送了一条消息}}到清单$3上的所有目标',
	'logentry-massmessage-failure' => '投递“$4”到“$3”失败，错误代码<code>$5</code>',
	'logentry-massmessage-skipbadns' => '投递“$4”到“$3”已跳过，因为目标所在的命名空间无法张贴',
	'logentry-massmessage-skipoptout' => '投递“$4”到“$3”已跳过，因为目标已选择退出群发消息投递',
	'logentry-massmessage-skipnouser' => '投递“$4”到“$3”已跳过，因为用户账户不存在',
	'massmessage-cannot-rename' => '这是一个系统账户，不能被重命名。',
	'massmessage-nextsteps' => '[[Special:MassMessage|发送下一条信息]]，或者[[Special:Statistics|查看有多少信息正在排队]]。',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Liuxinyu970226
 */
$messages['zh-hant'] = array(
	'massmessage-unescaped-langlinks' => '警告：您的訊息中有無轉義語言連結。',
);
