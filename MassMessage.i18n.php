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
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'Page containing list of pages to leave a message on:',
	'massmessage-form-subject' => 'Subject of the message (Also used as the edit summary):',
	'massmessage-form-message' => 'Body of the message:',
	'massmessage-form-global' => 'This is a global message.',
	'massmessage-form-preview' => 'Preview',
	'massmessage-form-submit' => 'Send',
	'massmessage-fieldset-preview' => 'Preview',
	'massmessage-submitted' => 'Your message has been queued.',
	'massmessage-just-preview' => 'This is just a preview. Press "{{int:massmessage-form-submit}}" to send the message.',
	'massmessage-account-blocked' => 'The account used to deliver messages has been blocked.',
	'massmessage-spamlist-doesnotexist' => 'The specified page-list page does not exist.',
	'massmessage-empty-subject' => 'The subject line is empty.',
	'massmessage-empty-message' => 'The message body is empty.',
	'massmessage-form-header' => 'Use the form below to send messages to a specified list. All fields are required.',
	'massmessage-target' => '[//$1$2?title={{urlencode:$3|WIKI}} $3]',
	'massmessage-queued-count' => 'Queued [[Special:MassMessage|mass messages]]',
	'right-massmessage' => 'Send a message to multiple users at once',
	'action-massmessage' => 'send a message to multiple users at once',
	'right-massmessage-global' => 'Send a message to multiple users on different wikis at once',
	'log-name-massmessage' => 'Mass message log',
	'log-description-massmessage' => 'These events track users sending messages through [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sent a message}} to $3',
);

/** Message documentation (Message documentation)
 * @author Kunal Mehta
 * @author Nemo bis
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'massmessage' => '{{doc-special|MassMessage}}',
	'massmessage-desc' => '{{desc|name=MassMessage|url=https://www.mediawiki.org/wiki/Extension:MassMessage}}',
	'massmessage-sender' => 'Username of the account which sends out messages.',
	'massmessage-form-spamlist' => 'Label for an inputbox on the special page.',
	'massmessage-form-subject' => 'Label for an inputbox on the special page.',
	'massmessage-form-message' => 'Used as label for a textarea on the special page.',
	'massmessage-form-global' => 'Label for a checkbox on the special page.',
	'massmessage-form-preview' => 'Label for the preview button on the special page.
{{Identical|Preview}}',
	'massmessage-form-submit' => 'Label for the submit button on the special page.
{{Identical|Send}}',
	'massmessage-fieldset-preview' => 'Label for the fieldset box around the page preview.
{{Identical|Preview}}',
	'massmessage-submitted' => 'Confirmation message the user sees after the form is submitted successfully and the request is queued in the job queue.',
	'massmessage-just-preview' => 'Warning to user that what they are seeing is just a preview, and they should hit the send button to actually submit it.

Refers the message {{msg-mw|Massmessage-form-submit}}.',
	'massmessage-account-blocked' => 'Error message the user sees if the bot account has been blocked.',
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
	'massmessage-queued-count' => 'Text for row on Special:Statistics',
	'right-massmessage' => '{{doc-right|massmessage}}
See also:
* {{msg-mw|Right-massmessage-global}}',
	'action-massmessage' => '{{doc-action|massmessage}}',
	'right-massmessage-global' => '{{doc-right|massmessage-global}}
See also:
* {{msg-mw|Right-massmessage}}',
	'log-name-massmessage' => 'Log page title',
	'log-description-massmessage' => 'Log page description',
	'logentry-massmessage-send' => '{{logentry}}',
);

/** Bengali (বাংলা)
 * @author Bellayet
 */
$messages['bn'] = array(
	'massmessage' => 'গণ বার্তা পাঠাও',
	'massmessage-desc' => 'একটি তালিকার ব্যবহারকারীদের সহজে কোনো বার্তা পাঠানোর সহজ ব্যবস্থা',
	'massmessage-sender' => 'ম্যাজেঞ্জারবট',
	'massmessage-form-spamlist' => 'পাতাটিতে একটি পাতার তালিকা রয়েছে যেখানে বার্তা রাখতে হবে।',
	'massmessage-form-subject' => 'বার্তার বিষয়। সম্পাদনা সারাংশ হিসেবেও ব্যবহৃত হবে।',
	'massmessage-form-message' => 'বার্তার মূল অংশ।',
	'massmessage-form-global' => 'এটি একটি বৈশ্বিক বার্তা।',
	'massmessage-form-preview' => 'প্রাকদর্শন',
	'massmessage-form-submit' => 'পাঠাও',
	'massmessage-fieldset-preview' => 'প্রাকদর্শন',
	'massmessage-submitted' => 'আপনার বার্তাটি অপেক্ষমান রয়েছে।',
	'massmessage-account-blocked' => 'বার্তা পাঠাতে ব্যবহৃত অ্যাকাউন্ট বাঁধা প্রদান করা হয়েছে।',
	'massmessage-empty-subject' => 'বিষয় লাইনটি খালি।',
	'massmessage-empty-message' => 'বার্তার মূল অংশ খালি।',
);

/** German (Deutsch)
 * @author Metalhead64
 * @author Se4598
 */
$messages['de'] = array(
	'massmessage' => 'Massennachricht senden',
	'massmessage-desc' => 'Ermöglicht Benutzern das einfache Versenden von Nachrichten an eine Benutzerliste',
	'massmessage-sender' => 'NachrichtenBot',
	'massmessage-form-spamlist' => 'Seite, die eine Seitenliste zum Hinterlassen einer Nachricht beinhaltet.',
	'massmessage-form-subject' => 'Betreff der Nachricht. Wird auch als Bearbeitungszusammenfassung verwendet.',
	'massmessage-form-message' => 'Der Textbereich der Nachricht.',
	'massmessage-form-global' => 'Dies ist eine globale Nachricht.',
	'massmessage-form-preview' => 'Vorschau',
	'massmessage-form-submit' => 'Senden',
	'massmessage-fieldset-preview' => 'Vorschau',
	'massmessage-submitted' => 'Deine Nachricht wurde in die Sendewarteschlange eingefügt!',
	'massmessage-just-preview' => 'Dies ist nur eine Vorschau. Klicke auf „{{int:massmessage-form-submit}}“, um die Nachricht abzusenden.',
	'massmessage-account-blocked' => 'Das zum Versenden von Nachrichten benutzte Benutzerkonto wurde gesperrt.',
	'massmessage-spamlist-doesnotexist' => 'Die angegebene Seitenlistenseite ist nicht vorhanden.',
	'massmessage-empty-subject' => 'Die Betreffszeile ist leer.',
	'massmessage-empty-message' => 'Der Nachrichtenkörper ist leer.',
	'massmessage-form-header' => 'Benutze das unten stehende Formular, um Nachrichten an eine angegebene Liste zu senden. Es sind alle Felder erforderlich.',
	'massmessage-queued-count' => '[[Special:MassMessage|Massennachrichten]] in der Warteschlange',
	'right-massmessage' => 'Gleichzeitig Nachrichten an mehrere Benutzer senden',
	'action-massmessage' => 'gleichzeitig Nachrichten an mehrere Benutzer zu senden',
	'right-massmessage-global' => 'Gleichzeitig Nachrichten an mehrere Benutzer auf unterschiedlichen Wikis senden',
	'log-name-massmessage' => 'Massennachrichten-Logbuch',
	'log-description-massmessage' => 'Dieses Logbuch protokolliert Ereignisse von Benutzern, die Nachrichten von [[Special:MassMessage]] versandt haben.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sandte eine Nachricht}} an $3',
);

/** Esperanto (Esperanto)
 * @author KuboF
 */
$messages['eo'] = array(
	'massmessage' => 'Sendi amasmesaĝon',
	'massmessage-desc' => 'Permesi al uzantoj facile sendi mesaĝon al listo de uzantoj',
	'massmessage-form-spamlist' => 'Paĝo kun listo de paĝoj en kiuj estu postasita mesaĝo:',
	'massmessage-form-message' => 'Teksto de la mesaĝo:',
	'massmessage-form-global' => 'Tio ĉi estas tutvikia mesaĝo.',
	'massmessage-form-preview' => 'Antaŭvidi',
	'massmessage-form-submit' => 'Sendi',
	'massmessage-fieldset-preview' => 'Antaŭvidi',
	'massmessage-account-blocked' => 'La konto uzata por liveradi mesaĝojn estis forbarita.',
	'massmessage-spamlist-doesnotexist' => 'La specifita paĝo kun paĝolisto ne ekzistas.',
	'massmessage-empty-message' => 'La mesaĝo ne enhavas tekston.',
	'massmessage-form-header' => 'Uzu la suban formularon por sendi mesaĝon al specifita listo. Ĉiuj kampoj estas postulataj.',
	'right-massmessage' => 'Sendi mesaĝon al multaj uzantoj samtempe',
	'action-massmessage' => 'sendi mesaĝon al multaj uzantoj samtempe',
	'right-massmessage-global' => 'Sendi mesaĝon al multaj uzantoj en diversaj vikioj samtempe',
	'log-name-massmessage' => 'Protokolo de amasmesaĝoj',
);

/** Basque (euskara)
 * @author An13sa
 */
$messages['eu'] = array(
	'massmessage-form-submit' => 'Bidali',
);

/** French (français)
 * @author Gomoko
 * @author Rastus Vernon
 * @author Sherbrooke
 */
$messages['fr'] = array(
	'massmessage' => 'Envoyer un message de masse',
	'massmessage-desc' => 'Permet aux utilisateurs d’envoyer facilement un message à une liste d’utilisateurs',
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'Page contenant la liste des pages sur lesquelles laisser un message :',
	'massmessage-form-subject' => 'Sujet du message (utilisé également dans le résumé de la modification) :',
	'massmessage-form-message' => 'Corps du message :',
	'massmessage-form-global' => 'Ceci est un message global.',
	'massmessage-form-preview' => 'Aperçu',
	'massmessage-form-submit' => 'Envoyer',
	'massmessage-fieldset-preview' => 'Aperçu',
	'massmessage-submitted' => 'Votre message a été mis en file !',
	'massmessage-just-preview' => 'Ceci est simplement un aperçu. Appuyez sur « {{int:massmessage-form-submit}} » pour envoyer le message.',
	'massmessage-account-blocked' => 'Le compte utilisé pour envoyer les messages a été bloqué.',
	'massmessage-spamlist-doesnotexist' => 'La page de listes de pages spécifiée n’existe pas.',
	'massmessage-empty-subject' => 'La ligne du sujet est vide.',
	'massmessage-empty-message' => 'Le corps du message est vide.',
	'massmessage-form-header' => 'Utilisez le formulaire ci-dessous pour envoyer des messages à une liste indiquée. Tous les champs sont obligatoires.',
	'massmessage-queued-count' => "[[Special:MassMessage|Messages de masse]] en file d'attente",
	'right-massmessage' => 'Envoyer un message à plusieurs utilisateurs à la fois',
	'action-massmessage' => 'envoyer un message à plusieurs utilisateurs à la fois',
	'right-massmessage-global' => 'Envoyer un message à plusieurs utilisateurs de différents wikis à la fois',
	'log-name-massmessage' => 'Journal des messages de masse',
	'log-description-massmessage' => 'Ces événements tracent les utilisateurs ayant envoyé des messages via [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|a envoyé un message}} à $3',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'massmessage' => 'Enviar unha mensaxe en masa',
	'massmessage-desc' => 'Permite aos usuarios enviar facilmente unha mensaxe a unha lista de usuarios',
	'massmessage-sender' => 'Bot de mensaxes',
	'massmessage-form-spamlist' => 'Páxina que conteña a lista de páxinas nas que deixar a mensaxe:',
	'massmessage-form-subject' => 'Asunto da mensaxe (tamén se usa de resumo de edición):',
	'massmessage-form-message' => 'Corpo da mensaxe:',
	'massmessage-form-global' => 'Esta é unha mensaxe global.',
	'massmessage-form-preview' => 'Vista previa',
	'massmessage-form-submit' => 'Enviar',
	'massmessage-fieldset-preview' => 'Vista previa',
	'massmessage-submitted' => 'A súa mensaxe púxose á cola.',
	'massmessage-just-preview' => 'Isto só é unha vista previa. Prema en "{{int:massmessage-form-submit}}" para enviar a mensaxe.',
	'massmessage-account-blocked' => 'A conta empregada para entregar as mensaxes está bloqueada.',
	'massmessage-spamlist-doesnotexist' => 'A páxina especificada coa lista de páxinas non existe.',
	'massmessage-empty-subject' => 'A liña do asunto está baleira.',
	'massmessage-empty-message' => 'O corpo da mensaxe está baleiro.',
	'massmessage-form-header' => 'Utilice o formulario inferior para enviar mensaxes a unha lista especificada. Todos os campos son obrigatorios.',
	'massmessage-queued-count' => '[[Special:MassMessage|Mensaxes en masa]] na cola de espera',
	'right-massmessage' => 'Enviar unha mensaxe a varios usuarios á vez',
	'action-massmessage' => 'enviar unha mensaxe a varios usuarios á vez',
	'right-massmessage-global' => 'Enviar unha mensaxe a varios usuarios de diferentes wikis á vez',
	'log-name-massmessage' => 'Rexistro de mensaxes en masa',
	'log-description-massmessage' => 'Este rexistro garda os usuarios que enviaron mensaxes mediante [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|enviou unha mensaxe}} a $3',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'massmessage' => 'שליחת הודעה לאנשים מרובים',
	'massmessage-desc' => 'אפשרות לשלוח בקלות הודעה לרשימת משתמשים',
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'דף שמכיל רשימת דפים שאליהם תישלח ההודעה:',
	'massmessage-form-subject' => 'כותרת ההודעה (משמשת גם כתקציר עריכה):',
	'massmessage-form-message' => 'גוף ההודעה:',
	'massmessage-form-global' => 'זוהי הודעה גלובלית.',
	'massmessage-form-preview' => 'תצוגה מקדימה',
	'massmessage-form-submit' => 'שליחה',
	'massmessage-fieldset-preview' => 'תצוגה מקדימה',
	'massmessage-submitted' => 'ההודעה שלך נוספה לתור.',
	'massmessage-just-preview' => 'זוהי רק תצוגה מקדימה. יש ללחות "{{int:massmessage-form-submit}}" כדי לשלוח את ההודעה.',
	'massmessage-account-blocked' => 'החשבון שמשמש לשליחת הודעות נחסם.',
	'massmessage-spamlist-doesnotexist' => 'הדף עם רשימת הדפים אינו קיים.',
	'massmessage-empty-subject' => 'שורת הנושא ריקה.',
	'massmessage-empty-message' => 'גוף ההודעה ריק.',
	'massmessage-form-header' => 'נא להשתמש בטופס להלן כדי לשלוח הודעות לרשימה מוגדרת. כל השדות נדרשים.',
	'massmessage-queued-count' => '[[Special:MassMessage|הודעות המוניות]] בתור',
	'right-massmessage' => 'שליחה של הודעות למשתמשים מרובים',
	'action-massmessage' => 'לשלוח הודעות למשתמשים רבים',
	'right-massmessage-global' => 'שליחת הודעות למשתמשים באתרי ויקי שונים',
	'log-name-massmessage' => 'יומן הודעות המוניות',
	'log-description-massmessage' => 'האירועים האלה עוקבים אחרי שליחת הודעות דרך [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|שלח|שלחה}} הודעה אל $3',
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'massmessage' => 'メッセージの一斉送信',
	'massmessage-desc' => '利用者が複数の利用者に簡単にメッセージを送信できるようにする',
	'massmessage-sender' => 'メッセンジャーボット',
	'massmessage-form-spamlist' => 'メッセージを書き込むページの一覧を含むページ:',
	'massmessage-form-subject' => 'メッセージの件名 (編集の要約としても使用されます):',
	'massmessage-form-message' => 'メッセージの本文:',
	'massmessage-form-global' => 'グローバル メッセージ',
	'massmessage-form-preview' => 'プレビュー',
	'massmessage-form-submit' => '送信',
	'massmessage-fieldset-preview' => 'プレビュー',
	'massmessage-submitted' => 'メッセージを待ち行列に登録しました。',
	'massmessage-just-preview' => 'これはプレビューしているだけに過ぎません。メッセージを送信するには「{{int:massmessage-form-submit}}」をクリックしてください。',
	'massmessage-account-blocked' => 'メッセージの送信に使用するアカウントがブロックされています。',
	'massmessage-spamlist-doesnotexist' => 'ページ一覧として指定したページは存在しません。',
	'massmessage-empty-subject' => '件名を入力していません。',
	'massmessage-empty-message' => 'メッセージの本文を入力していません。',
	'massmessage-form-header' => 'このフォームでは、指定した一覧のページにメッセージを送信できます。すべて必須項目です。',
	'massmessage-queued-count' => '順番待ち中の[[Special:MassMessage|一括送信メッセージ]]',
	'right-massmessage' => '複数の利用者に一度にメッセージを送信',
	'action-massmessage' => '複数の利用者へのメッセージの一斉送信',
	'right-massmessage-global' => '異なるウィキの複数の利用者に一度にメッセージを送信',
	'log-name-massmessage' => '一斉メッセージ記録',
	'log-description-massmessage' => 'これらのイベントは、利用者による [[Special:MassMessage]] でのメッセージの送信を追跡します。',
	'logentry-massmessage-send' => '$1 が $3 に{{GENDER:$2|メッセージを送信しました}}',
);

/** Korean (한국어)
 * @author Kwj2772
 * @author 아라
 */
$messages['ko'] = array(
	'massmessage' => '메시지 대량 보내기',
	'massmessage-desc' => '목록에 있는 사용자에게 쉽게 메시지를 보낼 수 있습니다',
	'massmessage-sender' => '메신저봇',
	'massmessage-form-spamlist' => '메시지를 남길 문서의 목록이 있는 문서:',
	'massmessage-form-subject' => '메시지의 제목 (편집 요약에도 쓰임):',
	'massmessage-form-message' => '메시지 본문:',
	'massmessage-form-global' => '전역 메시지입니다.',
	'massmessage-form-preview' => '미리 보기',
	'massmessage-form-submit' => '보내기',
	'massmessage-fieldset-preview' => '미리 보기',
	'massmessage-submitted' => '메시지가 대기되었습니다.',
	'massmessage-just-preview' => '이것은 미리보기일 뿐입니다. 메시지를 보내려면 "{{int:massmessage-form-submit}}"를 누르세요.',
	'massmessage-account-blocked' => '메시지를 전송하기 위한 계정이 차단되었습니다.',
	'massmessage-spamlist-doesnotexist' => '지정한 문서 목록의 문서가 존재하지 않습니다.',
	'massmessage-empty-subject' => '제목 줄이 비어 있습니다.',
	'massmessage-empty-message' => '메시지 본문이 비어 있습니다.',
	'massmessage-form-header' => '지정된 목록에서 메시지를 보내려면 아래 양식을 사용하세요. 모든 필드는 필수입니다.',
	'right-massmessage' => '한 번에 여러 사용자에게 메시지 보내기',
	'action-massmessage' => '한 번에 여러 사용자에게 메시지 보내기',
	'right-massmessage-global' => '한 번에 다른 위키에 있는 여러 사용자에게 메시지 보내기',
	'log-name-massmessage' => '대량 메시지 기록',
	'log-description-massmessage' => '이 이벤트는 [[Special:MassMessage]]를 통해 메시지를 보낸 사용자를 추적합니다.',
	'logentry-massmessage-send' => '$1 사용자가 $3에 {{GENDER:$2|메시지를 보냈습니다}}',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'massmessage' => 'Испраќање на масовна порака',
	'massmessage-desc' => 'Овозможува корисниците да испраќаат масовни пораки на списоци од корисници',
	'massmessage-sender' => 'БотГласник',
	'massmessage-form-spamlist' => 'Страница со список од страници на кои треба да се остави пораката.',
	'massmessage-form-subject' => 'Наслов на пораката. Ќе се користи и како опис на уредувањето.',
	'massmessage-form-message' => 'Текст на пораката.',
	'massmessage-form-global' => 'Ова е глобална порака.',
	'massmessage-form-preview' => 'Преглед',
	'massmessage-form-submit' => 'Испрати',
	'massmessage-fieldset-preview' => 'Преглед',
	'massmessage-submitted' => 'Пораката е ставена во редица.',
	'massmessage-just-preview' => 'Ова е само преглед. Стиснете на „{{int:massmessage-form-submit}}“ за да ја испратите пораката.',
	'massmessage-account-blocked' => 'Сметката со која се доставуваат пораки е блокирана.',
	'massmessage-spamlist-doesnotexist' => 'Укажаната страница со список од страници не постои.',
	'massmessage-empty-subject' => 'Насловот е празен.',
	'massmessage-empty-message' => 'Порака нема текст.',
	'massmessage-form-header' => 'Образецов служи за испраќање на пораки на укажан список на примачи. Сите полиња се задолжителни.',
	'massmessage-queued-count' => '[[Special:MassMessage|Масовни пораки]] во редица',
	'right-massmessage' => 'Испраќање на порака на повеќе корисници наеднаш.',
	'action-massmessage' => 'испраќање порака на повеќе корисници наеднаш',
	'right-massmessage-global' => 'Испраќање на порака на повеќе корисници на разни викија наеднаш.',
	'log-name-massmessage' => 'Дневник на масовни пораки',
	'log-description-massmessage' => 'Овој дневник следи испраќања на пораки преку [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|испрати порака}} до $3',
);

/** Malayalam (മലയാളം)
 * @author Akhilan
 * @author Praveenp
 */
$messages['ml'] = array(
	'massmessage-form-submit' => 'അയക്കുക',
	'massmessage-submitted' => 'താങ്കളുടെ ഇമെയിൽ അയച്ചു കഴിഞ്ഞിരിക്കുന്നു.', # Fuzzy
);

/** Marathi (मराठी)
 * @author V.narsikar
 */
$messages['mr'] = array(
	'massmessage' => 'एकगठ्ठा संदेश पाठवा',
	'massmessage-desc' => 'सदस्यांच्या यादीत असलेल्या सदस्यांना, सोप्या रितीने संदेश पाठविण्यास वापरकर्त्यास  परवानगी देते.',
	'massmessage-sender' => 'संदेश-सांगकाम्या',
	'massmessage-form-spamlist' => 'या पानावर संदेश देण्यायोग्य असलेल्या पानांची यादी आहे.',
	'massmessage-form-subject' => 'संदेशाचा विषय. याचा वापर संपादन सारांश म्हणुनही होईल.',
	'massmessage-form-message' => 'संदेशाचा मायना',
	'massmessage-form-global' => 'हा वैश्विक संदेश आहे.',
	'massmessage-form-submit' => 'पाठवा',
	'massmessage-submitted' => 'आपल्या संदेशास रांगेत ठेविल्या गेले आहे!',
	'massmessage-account-blocked' => 'संदेश देण्यासाठी वापरण्यात येणारे खाते अवरुद्ध करण्यात आले आहे.',
	'massmessage-spamlist-doesnotexist' => 'उल्लेखित पान-यादी असलेले पान अस्तित्वात नाही.',
	'massmessage-empty-subject' => 'विषय रिकामा आहे.',
	'massmessage-empty-message' => 'संदेशाचा मायना रिकामा आहे.',
	'massmessage-form-header' => 'खालील निवेदन एका उल्लेखित यादीस संदेश पाठविण्यास वापरा.सर्व क्षेत्रे आवश्यक आहेत.',
	'right-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'action-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'right-massmessage-global' => 'वेगवेगळ्या विकिंवर असलेल्या बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'log-name-massmessage' => 'एकगठ्ठा संदेशाच्या नोंदी',
	'log-description-massmessage' => 'हे प्रसंग,[[Special:MassMessage]] मार्फत संदेश पाठविणाऱ्या सदस्यांचा थांग (ट्रॅक) लावतात.',
	'logentry-massmessage-send' => '$1 ने $3 ला{{GENDER:$2|संदेश पाठविला}}',
);

/** Dutch (Nederlands)
 * @author Hansmuller
 * @author Konovalov
 * @author Siebrand
 */
$messages['nl'] = array(
	'massmessage' => 'Bulkberichten verzenden',
	'massmessage-desc' => 'Maakt het mogelijk om berichten naar een lijst ontvangers te verzenden',
	'massmessage-sender' => 'Berichtenbot',
	'massmessage-form-spamlist' => "Pagina met een lijst met pagina's om een bericht op te plaatsen:",
	'massmessage-form-subject' => 'Onderwerp voor bericht (ook gebruikt als bewerkingssamenvatting):',
	'massmessage-form-message' => 'Hoofdtekst van bericht:',
	'massmessage-form-global' => 'Dit is een globaal bericht.',
	'massmessage-form-preview' => 'Voorvertoning',
	'massmessage-form-submit' => 'Verzenden',
	'massmessage-fieldset-preview' => 'Voorvertoning',
	'massmessage-submitted' => 'Uw bericht is in de wachtrij geplaatst.',
	'massmessage-just-preview' => 'Dit is enkel een voorvertoning. Klik op "{{int:massmessage-form-submit}}" om het bericht te verzenden.',
	'massmessage-account-blocked' => 'De gebruiker om de berichten te bezorgen is geblokkeerd.',
	'massmessage-spamlist-doesnotexist' => 'De opgegeven paginalijst bestaat niet.',
	'massmessage-empty-subject' => 'Er wordt geen onderwerp aangegeven.',
	'massmessage-empty-message' => 'Het bericht bevat geen tekst.',
	'massmessage-form-header' => 'Gebruiker het onderstaande formulier om berichten te verzenden aan een lijst ontvangers. Alle velden zijn verplicht.',
	'right-massmessage' => 'Berichten verzenden aan meerdere ontvangers tegelijk',
	'action-massmessage' => 'berichten te verzenden aan meerdere ontvangers tegelijk',
	'right-massmessage-global' => "Berichten verzenden aan meerdere ontvangers op meerdere wiki's tegelijk",
	'log-name-massmessage' => 'Bulkberichtenlogboek',
	'log-description-massmessage' => 'Deze gebeurtenissen zijn gerelateerd aan verzonden berichten via de functie [[Special:MassMessage|bulkberichten]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|heeft}} een bericht geplaatst op $3',
);

/** Polish (polski)
 * @author WTM
 * @author Woytecr
 */
$messages['pl'] = array(
	'massmessage' => 'Wyślij masową wiadomość',
	'massmessage-desc' => 'Pozwala użytkownikom na wysłanie wiadomości do określonej listy użytkowników',
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'Strona zawierająca listę stron, na których zostawić wiadomość:',
	'massmessage-form-subject' => 'Temat wiadomości (Także używany jako podsumowanie edycji):',
	'massmessage-form-message' => 'Treść wiadomości:',
	'massmessage-form-global' => 'To jest globalna wiadomość.',
	'massmessage-form-preview' => 'Podgląd',
	'massmessage-form-submit' => 'Wyślij',
	'massmessage-fieldset-preview' => 'Podgląd',
	'massmessage-submitted' => 'Twoja wiadomość została umieszczona w kolejce.',
	'massmessage-just-preview' => 'To jest tylko podgląd. Naciśnij "{{int:massmessage-form-submit}}" aby wysłać wiadomość.',
	'massmessage-account-blocked' => 'To konto używane do dostarczania wiadomości zostało zablokowane.',
	'massmessage-spamlist-doesnotexist' => 'Określona strona z listą stron nie istnieje.',
	'massmessage-empty-subject' => 'Pole tematu jest puste.',
	'massmessage-empty-message' => 'Treść wiadomości jest pusta.',
	'massmessage-form-header' => 'Użyj poniższego formularza aby wysłać wiadomości do określonej listy. Wszystkie pola są wymagane.',
	'right-massmessage' => 'Wyślij wiadomość do wielu użytkowników jednocześnie',
	'action-massmessage' => 'wyślij wiadomość do wielu użytkowników jednocześnie',
	'right-massmessage-global' => 'Wyślij wiadomość do wielu użytkowników na różnych wiki za jednym razem',
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
	'massmessage-form-global' => 'دا يو نړيوال پيغام دی',
	'massmessage-form-preview' => 'مخليدنه',
	'massmessage-form-submit' => 'لېږل',
	'massmessage-fieldset-preview' => 'مخليدنه',
	'massmessage-submitted' => 'ستاسو پيغام ولېږل شو!', # Fuzzy
	'log-name-massmessage' => 'ډله ايز پيغام يادښت',
	'logentry-massmessage-send' => '$1، $3 ته، {{GENDER:$2|يو پيغام ولېږه}}',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Fúlvio
 * @author Luckas
 */
$messages['pt-br'] = array(
	'massmessage' => 'Enviar mensagem em massa',
	'massmessage-desc' => 'Permite que os usuários enviem facilmente uma mensagem para uma lista de usuários',
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'Página que contêm a lista de páginas para enviar uma mensagem em:',
	'massmessage-form-subject' => 'Assunto da mensagem (Também usado como sumário de edição):',
	'massmessage-form-message' => 'Corpo da mensagem:',
	'massmessage-form-global' => 'Esta é uma mensagem global.',
	'massmessage-form-preview' => 'Visualizar',
	'massmessage-form-submit' => 'Enviar',
	'massmessage-fieldset-preview' => 'Visualização',
	'massmessage-submitted' => 'Sua mensagem foi adicionada à fila.',
	'massmessage-just-preview' => 'Esta é apenas uma visualização. Pressione "{{int:massmessage-form-submit}}" para enviar a mensagem.',
	'massmessage-account-blocked' => 'A conta usada para enviar mensagens foi bloqueada.',
	'massmessage-spamlist-doesnotexist' => 'A lista de páginas especificada não existe.',
	'massmessage-empty-subject' => 'O espaço do assunto está vazio.',
	'massmessage-empty-message' => 'O corpo da mensagem está vazio.',
	'massmessage-form-header' => 'Use o formulário abaixo para enviar mensagens a uma lista específcia. Todos os campos são obrigatórios.',
	'right-massmessage' => 'Envie uma mensagem para vários usuários ao mesmo tempo',
	'action-massmessage' => 'envie uma mensagem para vários usuários ao mesmo tempo',
	'right-massmessage-global' => 'Envie uma mensagem para vários usuários, em diferentes wikis, ao mesmo tempo',
	'log-name-massmessage' => 'Registro de mensagens em massa',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|envie uma mensagem}} para $3',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'massmessage' => 'Manne messàgge de masse',
	'massmessage-desc' => "Permette a l'utinde de mannà messàgge de masse facile facile a 'n'elenghe de utinde",
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => "Pàgene ca tène 'n'elenghe de pàggene pe lassà 'nu messàgge sus a:",
	'massmessage-form-subject' => "Oggette d'u messàgge (pure ausate cumme riepiloghe d'u cangiamende):",
	'massmessage-form-message' => "Cuèrpe d'u messàgge:",
	'massmessage-form-global' => "Quiste jè 'nu messàgge globbale.",
	'massmessage-form-preview' => 'Andeprime',
	'massmessage-form-submit' => 'Manne',
	'massmessage-fieldset-preview' => 'Andeprime',
	'massmessage-submitted' => "'U messàgge tune ha state mise in code.",
	'massmessage-just-preview' => "Queste jè sulamende 'n'andeprime. Cazze \"{{int:massmessage-form-submit}}\" pe mannà 'u messàgge.",
	'massmessage-account-blocked' => "'U cunde ausate pe mannà le messàgge ha state bloccate.",
	'massmessage-spamlist-doesnotexist' => "'A pàgene de l'elenghe specificate de le pàggene non g'esiste.",
	'massmessage-empty-subject' => "'A linèe de l'oggette jè vacande.",
	'massmessage-empty-message' => "'U cuèrpe d'u messàgge jè vacande.",
	'massmessage-form-header' => "Ause 'u module aqquà sotte pe mannà messàgge a 'n'elenghe specifiche. Tutte le cambe sò richieste.",
	'right-massmessage' => "Manne 'nu messàgge a cchiù utinde jndr'à 'na botte",
	'action-massmessage' => "manne 'nu messàgge a cchiù utinde jndr'à 'na botte",
	'right-massmessage-global' => "Manne 'nu messàgge a cchiù utinde jndr'à 'na botte sus a uicchi diverse",
	'log-name-massmessage' => 'Archivije de le messàgge de masse',
	'log-description-massmessage' => "Ste avveneminde traccene l'utinde ca mannane messàgge cu [[Special:MassMessage]].",
	'logentry-massmessage-send' => "$1 {{GENDER:$2|mannate 'nu messàgge}} a $3",
);

/** Swedish (svenska)
 * @author Jopparn
 */
$messages['sv'] = array(
	'massmessage' => 'Skicka massmeddelande',
	'massmessage-desc' => 'Tillåter användare att enkelt skicka ett meddelande till en lista över användare',
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'Sidan innehåller en lista över sidor att lämna ett meddelande på.',
	'massmessage-form-subject' => 'Ämnet för meddelandet. Används också som redigeringskommentar.',
	'massmessage-form-message' => 'Meddelandetexten.',
	'massmessage-form-global' => 'Detta är ett globalt meddelande.',
	'massmessage-form-submit' => 'Skicka',
	'massmessage-submitted' => 'Ditt meddelande har skickats!', # Fuzzy
	'massmessage-account-blocked' => 'Kontot som används för att leverera meddelanden har blockerats.',
	'massmessage-spamlist-doesnotexist' => 'Den angivna sidan, som innehåller listan med sidor, existerar inte.',
	'right-massmessage' => 'Skicka ett meddelande till flera användare på en gång',
	'action-massmessage' => 'skicka ett meddelande till flera användare på en gång',
	'right-massmessage-global' => 'Skicka ett meddelande till flera användare på olika wikis på en gång',
	'log-name-massmessage' => 'Massmeddelandelogg',
	'log-description-massmessage' => 'Dessa händelser spårar användare som skickar meddelanden via [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|skicka ett meddelande}} till $3',
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
	'massmessage-sender' => 'HaberciBot',
	'massmessage-form-subject' => 'İleti konusu (Değişiklik özeti olarak da kullanılır)',
	'massmessage-form-message' => 'İleti metni:',
	'massmessage-form-global' => 'Bu küresel bir iletidir.',
	'massmessage-form-preview' => 'Ön izleme',
	'massmessage-form-submit' => 'Gönder',
	'massmessage-fieldset-preview' => 'Ön izleme',
	'massmessage-submitted' => 'İletiniz sıraya eklendi.',
	'massmessage-just-preview' => 'Bu yalnızca bir ön izlemedir. İletiyi göndermek için "{{int:massmessage-form-submit}}" düğmesine basınız.',
	'massmessage-account-blocked' => 'İletileri göndermek için kullanılan kullanıcı hesabı engellendi.',
	'massmessage-spamlist-doesnotexist' => 'Belirtilen sayfa-liste sayfası yok.',
	'massmessage-empty-subject' => 'Konu satırı boş.',
	'massmessage-empty-message' => 'İleti metni boş.',
	'massmessage-form-header' => 'Belirtilen listeye ileti göndermek için aşağıdaki formu kullanın. Bütün alanların doldurulması zorunludur.',
	'right-massmessage' => 'Aynı anda birden fazla kullanıcıya ileti gönder',
	'action-massmessage' => 'aynı anda birden fazla kullanıcıya ileti gönder',
	'right-massmessage-global' => 'Aynı anda farklı vikilerdeki birden fazla kullanıcıya ileti gönder',
	'log-name-massmessage' => 'Toplu ileti günlüğü',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Qiyue2001
 */
$messages['zh-hans'] = array(
	'massmessage' => '发送批量消息',
	'massmessage-desc' => '允许用户能够轻松地向列表中的用户发送消息',
	'massmessage-form-spamlist' => '页面包含了留言的页面列表：',
	'massmessage-form-subject' => '消息主题（还用作编辑摘要）：',
	'massmessage-form-message' => '消息正文：',
	'massmessage-form-global' => '这是一个全域信息。',
	'massmessage-form-preview' => '预览',
	'massmessage-form-submit' => '发送',
	'massmessage-fieldset-preview' => '预览',
	'massmessage-submitted' => '您的消息已添加到队列！',
	'massmessage-just-preview' => '这只是预览。点击“{{int:massmessage-form-submit}}”来发送消息。',
	'massmessage-account-blocked' => '用来传递消息的帐户已被阻止。',
	'massmessage-spamlist-doesnotexist' => '指定的页面列表页面不存在。',
	'massmessage-empty-subject' => '主题为空。',
	'massmessage-empty-message' => '消息正文为空。',
	'massmessage-form-header' => '使用下面的表单以将消息发送到指定的列表。所有字段都是必需的。',
	'right-massmessage' => '一次将消息发送到多个用户',
	'action-massmessage' => '一次将消息发送到多个用户',
	'right-massmessage-global' => '一次将消息发送到不同wiki上的多个用户',
	'log-name-massmessage' => '批量消息日志',
	'log-description-massmessage' => '这些事件跟踪用户使用[[Special:MassMessage]]发送消息。',
	'logentry-massmessage-send' => '$1{{GENDER:$2|发送了一条消息}}到$3',
);
