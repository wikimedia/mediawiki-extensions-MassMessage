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
	'massmessage-form-submit' => 'Send',
	'massmessage-submitted' => 'Your message has been queued.',
	'massmessage-account-blocked' => 'The account used to deliver messages has been blocked.',
	'massmessage-spamlist-doesnotexist' => 'The specified page-list page does not exist.',
	'right-massmessage' => 'Send a message to multiple users at once',
	'action-massmessage' => 'send a message to multiple users at once',
	'right-massmessage-global' => 'Send a message to multiple users on different wikis at once',
	'log-name-massmessage' => 'Mass message log',
	'log-description-massmessage' => 'These events track users sending messages through [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sent a message}} to $3',
);

/** Message documentation (Message documentation)
 * @author Kunal Mehta
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
	'massmessage-form-submit' => 'Label for the submit button on the special page.
{{Identical|Send}}',
	'massmessage-submitted' => 'Confirmation message the user sees after the form is submitted successfully and the request is queued in the job queue.',
	'massmessage-account-blocked' => 'Error message the user sees if the bot account has been blocked.',
	'massmessage-spamlist-doesnotexist' => 'Error message the user sees if an invalid spamlist is provided.

spamlist?

This message probably means "The specified page which contains list of pages, does not exist".',
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
	'massmessage-form-submit' => 'পাঠাও',
	'massmessage-submitted' => 'আপনার বার্তাটি পাঠানো হয়েছে!',
	'massmessage-account-blocked' => 'বার্তা পাঠাতে ব্যবহৃত অ্যাকাউন্ট বাঁধা প্রদান করা হয়েছে।',
);

/** German (Deutsch)
 * @author Metalhead64
 */
$messages['de'] = array(
	'massmessage' => 'Massennachricht senden',
	'massmessage-desc' => 'Ermöglicht Benutzern das einfache Versenden von Nachrichten an eine Benutzerliste',
	'massmessage-sender' => 'NachrichtenBot',
	'massmessage-form-spamlist' => 'Seite, die eine Seitenliste zum Hinterlassen einer Nachricht beinhaltet.',
	'massmessage-form-subject' => 'Betreff der Nachricht. Wird auch als Bearbeitungszusammenfassung verwendet.',
	'massmessage-form-message' => 'Der Textbereich der Nachricht.',
	'massmessage-form-global' => 'Dies ist eine globale Nachricht.',
	'massmessage-form-submit' => 'Senden',
	'massmessage-submitted' => 'Deine Nachricht wurde versandt!',
	'massmessage-account-blocked' => 'Das zum Versenden von Nachrichten benutzte Benutzerkonto wurde gesperrt.',
	'massmessage-spamlist-doesnotexist' => 'Die angegebene Seitenlistenseite ist nicht vorhanden.',
	'right-massmessage' => 'Gleichzeitig Nachrichten an mehrere Benutzer senden',
	'action-massmessage' => 'gleichzeitig Nachrichten an mehrere Benutzer zu senden',
	'right-massmessage-global' => 'Gleichzeitig Nachrichten an mehrere Benutzer auf unterschiedlichen Wikis senden',
	'log-name-massmessage' => 'Massennachrichten-Logbuch',
	'log-description-massmessage' => 'Dieses Logbuch protokolliert Ereignisse von Benutzern, die Nachrichten von [[Special:MassMessage]] versandt haben.',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sandte eine Nachricht}} an $3',
);

/** French (français)
 * @author Gomoko
 */
$messages['fr'] = array(
	'massmessage' => 'Envoyer un message de masse',
	'massmessage-desc' => 'Permet aux utilisateurs d’envoyer facilement un message à une liste d’utilisateurs',
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'Page contenant la liste des pages sur lesquelles laisser un message :',
	'massmessage-form-subject' => 'Sujet du message (utilisé également dans le résumé de la modification) :',
	'massmessage-form-message' => 'Corps du message :',
	'massmessage-form-global' => 'Ceci est un message global.',
	'massmessage-form-submit' => 'Envoyer',
	'massmessage-submitted' => 'Votre message a été envoyé !',
	'massmessage-account-blocked' => 'Le compte utilisé pour envoyer les messages a été bloqué.',
	'massmessage-spamlist-doesnotexist' => 'La page de liste de pages spécifiée n’existe pas.',
	'right-massmessage' => 'Envoyer un message à plusieurs utilisateurs à la fois',
	'action-massmessage' => 'envoyer un message à plusieurs utilisateurs à la fois',
	'right-massmessage-global' => 'Envoyer un message à plusieurs utilisateurs de différents wikis à la fois',
	'log-name-massmessage' => 'Journal des messages de masse',
	'log-description-massmessage' => 'Ces événements tracent les utilisateurs ayant envoyé des messages via [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|a envoyé un message}} à $3',
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
	'massmessage-form-submit' => '送信',
	'massmessage-submitted' => 'メッセージを送信しました。',
	'massmessage-account-blocked' => 'メッセージの送信に使用するアカウントがブロックされています。',
	'massmessage-spamlist-doesnotexist' => 'ページ一覧として指定したページは存在しません。',
	'right-massmessage' => '複数の利用者に一度にメッセージを送信',
	'action-massmessage' => '複数の利用者へのメッセージの一斉送信',
	'right-massmessage-global' => '異なるウィキの複数の利用者に一度にメッセージを送信',
	'log-name-massmessage' => '一斉メッセージ記録',
	'log-description-massmessage' => 'これらのイベントは、利用者による [[Special:MassMessage]] でのメッセージの送信を追跡します。',
	'logentry-massmessage-send' => '$1 が $3 に{{GENDER:$2|メッセージを送信しました}}',
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
	'massmessage-form-submit' => 'Испрати',
	'massmessage-submitted' => 'Пораката е испратена!',
	'massmessage-account-blocked' => 'Сметката со која се доставуваат пораки е блокирана.',
	'massmessage-spamlist-doesnotexist' => 'Укажаната страница со список од страници не постои.',
	'right-massmessage' => 'Испраќање на порака на повеќе корисници наеднаш.',
	'action-massmessage' => 'испраќање порака на повеќе корисници наеднаш',
	'right-massmessage-global' => 'Испраќање на порака на повеќе корисници на разни викија наеднаш.',
	'log-name-massmessage' => 'Дневник на масовни пораки',
	'log-description-massmessage' => 'Овој дневник следи испраќања на пораки преку [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|испрати порака}} до $3',
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
	'massmessage-form-message' => 'संदेशाचे अंग',
	'massmessage-form-global' => 'हा वैश्विक संदेश आहे.',
	'massmessage-form-submit' => 'पाठवा',
	'massmessage-submitted' => 'आपला संदेश पाठविल्या गेला आहे!',
	'massmessage-account-blocked' => 'संदेश देण्यासाठी वापरण्यात येणारे खाते अवरुद्ध करण्यात आले आहे.',
	'massmessage-spamlist-doesnotexist' => 'निविष्ट (ईनपूट) पानांची यादी अस्तित्वात नाही.', # Fuzzy
	'right-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'action-massmessage' => 'बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'right-massmessage-global' => 'वेगवेगळ्या विकिंवर असलेल्या बहुविध सदस्यांना एकत्रितरित्या संदेश पाठवा',
	'log-name-massmessage' => 'एकगठ्ठा संदेशाच्या नोंदी',
	'log-description-massmessage' => 'हे प्रसंग,[[Special:MassMessage]] मार्फत संदेश पाठविणाऱ्या सदस्यांचा थांग (ट्रॅक) लावतात.',
	'logentry-massmessage-send' => '$1 ने $3 ला{{GENDER:$2|संदेश पाठविला}}',
);

/** Dutch (Nederlands)
 * @author Konovalov
 * @author Siebrand
 */
$messages['nl'] = array(
	'massmessage-form-submit' => 'Verzenden',
	'massmessage-submitted' => 'Uw bericht is verzonden!',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'massmessage' => 'ډله ايز پيغام لېږل',
	'massmessage-form-message' => 'د پيغام جوسه',
	'massmessage-form-global' => 'دا يو نړيوال پيغام دی',
	'massmessage-form-submit' => 'لېږل',
	'massmessage-submitted' => 'ستاسو پيغام ولېږل شو!',
	'log-name-massmessage' => 'ډله ايز پيغام يادښت',
	'logentry-massmessage-send' => '$1، $3 ته، {{GENDER:$2|يو پيغام ولېږه}}',
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
	'massmessage-submitted' => 'Ditt meddelande har skickats!',
	'massmessage-account-blocked' => 'Kontot som används för att leverera meddelanden har blockerats.',
	'massmessage-spamlist-doesnotexist' => 'Den angivna sidan, som innehåller listan med sidor, existerar inte.',
	'right-massmessage' => 'Skicka ett meddelande till flera användare på en gång',
	'action-massmessage' => 'skicka ett meddelande till flera användare på en gång',
	'right-massmessage-global' => 'Skicka ett meddelande till flera användare på olika wikis på en gång',
	'log-name-massmessage' => 'Massmeddelandelogg',
	'log-description-massmessage' => 'Dessa händelser spårar användare som skickar meddelanden via [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|skicka ett meddelande}} till $3',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Qiyue2001
 */
$messages['zh-hans'] = array(
	'massmessage' => '发送批量消息',
	'massmessage-desc' => '允许用户能够轻松地向列表中的用户发送消息',
	'massmessage-form-subject' => '消息主题（还用作编辑摘要）：',
	'massmessage-form-message' => '消息正文：',
	'massmessage-form-global' => '这是一个全域信息。',
	'massmessage-form-submit' => '发送',
	'massmessage-submitted' => '您的消息已经发出！',
	'massmessage-account-blocked' => '用来传递消息的帐户已被阻止。',
	'massmessage-spamlist-doesnotexist' => '指定的页面列表页面不存在。',
	'right-massmessage' => '一次将消息发送到多个用户',
	'action-massmessage' => '一次将消息发送到多个用户',
	'right-massmessage-global' => '一次将消息发送到不同wiki上的多个用户',
	'log-name-massmessage' => '批量消息日志',
	'log-description-massmessage' => '这些事件跟踪用户使用[[Special:MassMessage]]发送消息。',
	'logentry-massmessage-send' => '$1{{GENDER:$2|发送了一条消息}}到$3',
);
