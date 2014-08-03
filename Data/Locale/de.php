<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    '- please select -'
        => '- bitte auswählen -',

    'A brief summary of the translation status'
        => 'Eine Zusammenfassung des Übersetzungsstatus',
    'Abort'
        => 'Abbruch',
    'Access denied'
        => 'Zugriff verweigert',
    'Access to kitFramework User Accounts'
        => 'Zugriff auf die kitFramework Benutzerkonten',
    'Account'
        => 'Benutzerkonto',
    'Accounts'
        => 'Benutzerkonten',
    'Actual SMTP Settings'
        => 'Aktuelle SMTP Einstellungen',
    'Add the extension <b>%name%</b> to the catalog.'
        => 'Die Erweiterung <b>%name%</b> wurde dem Katalog hinzugefügt.',
    'Add the extension <b>%name%</b> to the register.'
        => 'Die Erweiterung <b>%name%</b> wurde dem Register hinzugefügt.',
    'Additional information'
        => 'Zusatzinformation',
    'Available extensions'
        => 'Verfügbare Erweiterungen',

    'Bad credentials'
        => 'Die Angaben sind unvollständig oder ungültig!',
    'Be aware: Changing the email address or username may influence kitFramework extensions which are using the account data to identify users.'
        => 'Bitte beachen Sie: Änderungen der E-Mail Adresse oder des Benutzernamen können sich auf kitFramework Anwendungen auswirken, die Daten der Benutzerkonten zur Identifizierung der Nutzer verwenden.',
    'Be aware: Changing your email address may influence kitFramework extensions which are using the account data to identify you - please contact the webmaster in these cases. The username can only be changed by the administrator.'
        => 'Bitte beachten Sie: Eine Änderung Ihrer E-Mail Adresse beeinflusst eventuell die mit Ihnen verbundenen Datensätze in den verschiedenen Anwendungen. Bitte informieren Sie in diesen Fällen den Webmaster. Der Benutzername kann nur durch einen Administrator geändert werden.',
    'Be aware: You are now authenticated as user <b>%username%</b>!'
        => 'Vorsicht! Sie sind momentan als der Benutzer <b>%username%</b> angemeldet!',

    'Can not read the extension.json for %name%!<br />Error message: %error%'
        => 'Kann die Beschreibungsdatei extension.json für die Erweiterung <b>%name%</b> nicht lesen!<br />Fehlermeldung: %error%',
    'Can not read the information file for the kitFramework!'
        => 'Kann die Beschreibungsdatei für das kitFramework nicht lesen!',
    "Can't read the the %repository% from %organization% at Github!"
        => 'Kann das Repository %repository% von %organization% auf Github nicht lesen!',
    "Can't create a new GUID as long the last GUID is not expired. You must wait 24 hours between the creation of new passwords."
        => 'Es kann keine neue GUID erzeugt werden, solange die letzte noch gültig ist. Sie können das Anlegen eines neuen Passwort nur einmal innerhalb von 24 Stunden anfordern!',
    "Can't open the file <b>%file%</b>!"
        => 'Kann die Datei <b>%file%</b> nicht öffnen!',
    "Can't read the the %repository% from %organization% at Github!"
        => 'Kann das Repository <b>%repository%</b> von der Organisation <b>%organization%</b> auf Github nicht lesen!',
    'Cancel'
        => 'Abbruch',
    'captcha-timeout'
        => 'Zeitüberschreitung bei der CAPTCHA Übermittlung, bitte versuchen Sie es erneut.',
    'Check the email settings and send a email to the webmaster for testing purpose'
        => 'E-Mail Einstellungen kontrollieren und eine Test E-Mail an den Webmaster schicken',
    'commercial use only'
        => '- nur kommerzielle Verwendung -',
    'Configuration'
        => 'Konfiguration',
    'Copied kitCommand to clipboard!'
        => 'Das kitCommand wurde in die Zwischenablage kopiert!',
    'Copy the GUID to the clipboard'
        => 'Die GUID in die Zwischenablage kopieren',
    'Copy this kitCommand to the clipboard'
        => 'Dieses kitCommand in die Zwischenablage kopieren',
    'Create a new account'
        => 'Ein neues Benutzerkonto anlegen',
    'Create a new password' =>
        'Ein neues Password anlegen',

    'Data replication'
        => 'Datenabgleich',
    'Delete this account irrevocable'
        => 'Benutzerkonto unwiderruflich löschen',
    'Displayname'
        => 'Angezeigter Name',
    'Documentation'
        => 'Dokumentation',

    'Edit kitFramework configuration file'
        => 'kitFramework Konfigurationsdatei bearbeiten',
    'Edit the locale %locale%'
        => 'Die %locale% Übersetzungen bearbeiten',
    'Email'
        => 'E-Mail',
    'Entry points'
        => 'Zugangspunkte',
    "<b>Error</b>: Can't execute the kitCommand: <i>%command%</i>"
        => '<b>Fehler</b>: Das kitCommand <i>%command%</i> konnte nicht ausgeführt werden.',
    'Error executing the kitCommand <b>%command%</b>'
        => 'Bei der Ausführung des kitCommand <b>%command%</b> ist ein Fehler aufgetreten',
    'Execute'
        => 'Ausführen',
    'Execute Update'
        => 'Aktualisierung durchführen',
    'Extension'
        => 'Erweiterung',
    'Extensions'
        => 'Erweiterungen',

    'File'
        => 'Datei',
    'Filename'
        => 'Dateiname',
    'First steps'
        => 'Erste Schritte',
    'For the event with the ID %event_id% is no recurring defined.'
        => 'Für die Veranstaltung mit der ID %event_id% ist keine Wiederholung festgelegt.',
    'Forgot your password?'
        => 'Passwort vergessen?',

    'Generate a globally unique identifier (GUID)'
        => 'Eine weltweit eindeutige Kennziffer erstellen (GUID)',
    'Get in touch with the developers, receive support, tipps and tricks for %command%!'
        => 'Treten Sie mit den Entwicklern in Kontakt und erhalten Unterstützung, erfahren Tipps sowie Tricks zu %command%!',
    'Get more information about %command%'
        => 'Erfahren Sie mehr über %command%',
    'Goodbye'
        => 'Auf Wiedersehen',

    'Hello %name%,<br />you have asked to create a new password for the kitFramework hosted at %server%.'
        => 'Hallo %name%,<br />Sie haben darum gebeten ein neues Passwort für das kitFramework auf %server% zu erhalten.',
    "Hello %name%, you want to change your password, so please type in a new one, repeat it and submit the form. If you won't change your password just leave this dialog."
        => 'Hallo %name%,<br />Sie möchten Ihr Passwort ändern, bitte geben Sie das neue Passwort ein, wiederholen Sie es zur Sicherheit und schicken Sie das Formular ab.<br />Falls Sie Ihr Passwort nicht ändern möchten, verlassen Sie bitte einfach diesen Dialog.',
    'Help'
        => 'Hilfe',
    'help_accounts_list_json'
        => '<p>Diese Datei ermöglicht es Ihnen die angezeigten Spalten und die Sortierung der Felder in der <a href="%FRAMEWORK_URL%/admin/accounts/list" target="_blank">Übersicht der Benutzerkonten</a> zu ändern.</p><p>Verfügbare Felder für die Verwendung in <var>columns</var> und <var>list > order > by</var> sind: <var>id, username, email, password, displayname, last_login, roles, guid, guid_timestamp, guid_status, status</var> und <var>timestamp</var>. Mit <var>list > rows_per_page</var> legen Sie fest, wie viele Benutzerkonten pro Seite angezeigt werden.</p>',
    'help_cms_json'
        => '<p>Diese Konfigurationsdatei enthält Informationen zu dem übergeordneten Content Management System (CMS). Falls Sie die URL der Website verändert haben oder sich das Stammverzeichnis auf dem Webserver geändert hat, sollten Sie die Einstellungen in dieser Datei prüfen und anpassen.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#cmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>cms.json</var>.</p>',
    'help_config_jsoneditor_json'
        => '<p>Diese Konfigurationsdatei enthält die Einstellungen für den Konfigurations Editor, den Sie just in diesem Moment verwenden.</p><p>Normalerweise ist es nicht erforderlich an diesen Einstellungen etwas zu ändern. Die Datei enthält die Hilfeinformationen, die zu den einzelnen Konfigurationsdateien angezeigt werden sowie die Liste der verfügbaren Konfigurationsdateien um ein Überprüfen des Systems bei jedem Aufruf des Editors zu verhindern.</p><p>Falls Sie eine Konfigurationsdatei vermissen, z.B. für eine gerade erst installierte Erweiterung, verwenden Sie bitte den <key>Suchlauf</key> Schalter um ein erneutes Durchsuchen des Systems zu erzwingen.</p>',
    'help_doctrine_cms_json'
        => '<p>Diese Konfigurationsdatei enthält die Datenbankeinstellungen. Wenn Sie die Datenbankeinstellungen für das übergeordnete Content Management System (CMS) ändern, müssen Sie die Einstellungen in dieser Datei ebenfalls anpassen - andernfalls wird das kitFramework nicht mehr korrekt funktionieren.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#doctrinecmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>doctrine.cms.json</var>.</p>',
    'help_framework_json'
        => '<p>Dies ist die zentrale Konfigurationsdatei für das kitFramework. Hier können Sie den <var>DEBUG</var> und <var>CACHE</var> Modus ein- oder ausschalten und das kitFramework anweisen stets Ihre benutzerdefinierten Templates vor den Standardvorlagen zu laden.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>framework.json</var>.</p>',
    'help_proxy_json'
        => '<p>Falls Sie einen Proxy Server verwenden benötigen Sie diese Konfigurationsdatei. Bitte fragen Sie Ihren Systemadministrator nach den benötigten Einstellungen.</p>',
    'help_swift_cms_json'
        => '<p>Diese Konfigurationsdatei wird benötigt um die E-Mail Einstellungen für das kitFramework festzulegen. Bitte fragen Sie Ihren E-Mail Anbieter nach den benötigten Einstellungen für den SMTP Server, Port, Benutzername und Passwort.</p><p>Sie können die E-Mail Einstellungen überprüfen, in dem Sie eine <a href="%FRAMEWORK_URL%/admin/test/mail" target="_blank">Testmail versenden</a>.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#swiftcmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>swift.cms.json</var>.</p>',

    'If you have forgotten your password, you can order a link to create a new one. Please type in the email address assigned to your account and submit the form.'
        => 'Falls Sie Ihr Passwort vergessen haben, können Sie einen Link anfordern um ein neues Passwort zu erstellen. Bitte tragen Sie die E-Mail Adresse ein, die ihrem Konto zugeordnet ist und übermitteln Sie das Formular.',
    'If you have not asked to create a new password, just do nothing. The link above is valid only for 24 hours and your actual password has not changed now.'
        => 'Falls Sie kein neues Passwort angefordert haben, ignorieren Sie diese E-Mail bitte. Der o.a. Link ist lediglich für 24 Stunden gültig und ihr aktuelles Passwort wurde nicht geändert.',
    'incorrect-captcha-sol'
        => 'Der übermittelte CAPTCHA ist nicht korrekt.',
    'Information about the i18nEditor'
        => 'Informationen über den i18nEditor',
    'Install'
        => 'Installieren',
    'Install, update or remove kitFramework Extensions'
        => 'Installieren, aktualisieren oder entfernen Sie kitFramework Erweiterungen',
    'Installed extensions'
        => 'Installierte Erweiterungen',
    'Insufficient user role'
        => 'Ungenügende Zugangsberechtigung',
    'Internationalization'
        => 'Internationalisierung',
    'invalid-request-cookie'
        => 'Ungültige ReCaptcha Anfrage',
    'invalid-site-private-key'
        => 'Der private Schlüssel für den ReCaptcha Service ist ungültig, prüfen Sie die Einstellungen!',
    'Issues'
        => 'Mängel',

    '<p>Last registered file modification: <strong>%modification%</strong><br />Scanned files: <strong>%scanned%</strong><br />Locale hits: <strong>%hits%</strong></p><p>Switch to developer mode to get also information about problems and conflicts.</p>'
        => '<p>Letzte erfasste Dateiänderung: <strong>%modification%</strong><br />Durchsuchte Dateien: <strong>%scanned%</strong><br />Quelltext Treffer: <strong>%hits%</strong></p><p>Schalten Sie in den <em>Entwickler Modus</em> um, um zusäzliche Informationen über Probleme in den Übersetzungsdaten zu erhalten.</p>',
    '<p>Last registered file modification: <strong>%modification%</strong><br />Scanned files: <strong>%scanned%</strong><br />Locale hits: <strong>%hits%</strong><br /><br />Conflicting translations: <strong>%conflicts%</strong><br />Unassigned translations: <strong>%unassigned%</strong></p><p>You execute the i18nEditor in <strong>developer mode</strong>.</p>'
        => '<p>Letzte erfasste Dateiänderung:: <strong>%modification%</strong><br />Durchsuchte Dateien: <strong>%scanned%</strong><br />Quelltext Treffer: <strong>%hits%</strong><br /><br />Kollidierende Übersetzungen: <strong>%conflicts%</strong><br />Nicht zugeordnete Übersetzungen: <strong>%unassigned%</strong></p><p>Sie führen den i18nEditor im <strong>Entwickler Modus</strong> aus.</p>',
    'License'
        => 'Lizenz',
    'Line'
        => 'Zeile',
    'Link transmitted'
        => 'Link übermittelt',
    'List'
        => 'Liste',
    'List of translation sources'
        => 'Liste der Quelltexte für die Übersetzungen',
    'Load file'
        => 'Datei laden',
    'Load the configuration file <strong>%file%</strong> into the editor.'
        => 'Die Konfigurationsdatei <strong>%file%</strong> wurde in den Editor geladen.',
    'Load the selected configuration file into the editor'
        => 'Die ausgewählte Datei in den Editor laden',
    'Locale id'
        => 'Locale ID',
    'Locale remark'
        => 'Notiz',
    'Locale source'
        => 'Quelltext',
    'Login' =>
        'Anmelden',
    'Logout' =>
        'Abmelden',

    'Message'
        => 'Mitteilung',
    'Missing the parameter: %parameter%'
        => 'Benötige den Parameter: %parameter%',

    'New kitFramework release available!'
        => 'Es ist eine neue kitFramework Release verfügbar!',
    'No account? <a href="%register_url%">Register a user account for DogPaw</a>!'
        => 'Kein Benutzerkonto? <a href="%register_url%">Melden Sie sich kostenlos an</a>!',
    'No fitting user role dectected!'
        => 'Es wurde kein passendes Benutzerrecht gefunden',

    'Oooops, missing the alert which should be prompted here ... '
        => 'Hoppla, da fehlt die Meldung die hier eigentlich angezeigt werden sollte ...',
    'Open this helpfile in a new window'
        => 'Diese Hilfedatei in einem neuen Fenster öffnen',

    'Password'
        => 'Passwort',
    'Password changed'
        => 'Passwort geändert',
    'Password repeat'
        => 'Passwort wiederholen',
    'Please check the username and password and try again!'
        => 'Bitte prüfen Sie den angegebenen Benutzernamen sowie das Passwort und versuchen Sie es erneut!',
    'Please <a href="%link%" target="_blank">comment this help</a> to improve the kitCommand <b>%command%</b>.'
        => 'Bitte <a href="%link%" target="_blank">kommentieren und ergänzen Sie diese Hilfe</a> um das kitCommand <b>%command%</b> zu verbessern.',
    'Please login to the kitFramework with your username or email address and the assigned password. Your can also use your username and password for the CMS.'
        => 'Bitte melden Sie sich am kitFramework mit Ihrem Benutzernamen oder Ihrer E-Mail Adresse und Ihrem Passwort an. Sie können sich auch mit Ihrem Benutzernamen und Passwort für das CMS anmelden.',
    'Please report all issues and help to improve %command%!'
        => 'Bitte melden Sie alle auftretenden Probleme und helfen Sie mit %command% zu verbessern!',
    'Please select the configuration file you want to edit.'
        => 'Bitte wählen Sie die Konfigurationsdatei aus, die Sie bearbeiten möchten.',
    'Please use the following link to create a new password: %reset_url%'
        => 'Bitte verwenden Sie den folgenden Link um ein neues Passwort anzulegen:<br />%reset_url%',
    'Problems'
        => 'Probleme',
    'Problems with the translation data'
        => 'Probleme mit den Übersetzungsdaten',
    'published at'
        => 'veröffentlicht am',

    'Real active user roles'
        => 'Tatsächlich aktive Anwenderrechte',
    'References'
        => 'Verweise',
    'Referenced files'
        => 'Verweisende Dateien',
    'Regards, Your kitFramework team'
        => 'Mit freundlichen Grüßen<br />Ihr kitFramework Team',
    'Repeat by pattern, i.e. at the last tuesday of the month'
        => 'Nach einem Muster, z.B. am letzten Donnerstag im Monat',
    'Repeat Password'
        => 'Passwort wiederholen',
    'Repeat sequently at day x of month'
        => 'Regelmäßig am Tag x des Monats',
    'Report problems'
        => 'Fehler melden',
    'Rescan'
        => 'Suchlauf',
    'Reveal this e-mail address'
        => 'Anklicken um die vollständige E-Mail Adresse anzuzeigen',
    'Roles'
        => 'Benutzerrechte, Rollen',

    'Save file'
        => 'Datei sichern',
    'Scan for installed extensions'
        => 'Nach installierten Erweiterungen suchen',
    'Scan the kitFramework for existing configuration files'
        => 'Das kitFramework nach Konfigurationsdateien durchsuchen',
    'Scan the online catalog for available extensions'
        => 'Den online Katalog nach verfügbaren Erweiterungen durchsuchen',
    'Select day'
        => 'Wochentag auswählen',
    'Select pattern'
        => 'Muster auswählen',
    'Send account info to the user'
        => 'Dem Benutzer eine Kontoinformation zusenden',
    'Send a account information to the user %name%'
        => 'Dem Benutzer %name% wurde eine Kontoinformation zugesendet.',
    'Send email (only if the password has changed)'
        => 'Zugangsdaten senden (nur wenn das Passwort geändert wurde)',
    'Show a list of all installed kitCommands'
        => 'Eine Liste mit den installierten kitCommands anzeigen',
    'Sorry, but only Administrators are allowed to access this kitFramework extension.'
        => 'Ihre Berechtigung ist nicht ausreichend, nur Administratoren dürfen das kitFramework CMS Tool verwenden.',
    'Sorry, but the configuration file <strong>%filename%</strong> was not found. Please be aware that this controller may fail if you try to open a configuration file of a just installed extension, perhaps the extension must be executed first and you should also do a <key>rescan</key> for the configuration files.'
        => 'Entschuldigung, die Konfigurationsdatei <strong>%filename%</strong> wurde nicht gefunden. Bitte beachten Sie, dass der Controller die Datei möglicherweise nicht findet, wenn Sie versuchen eine Konfigurationsdatei einer gerade erst installierten Erweiterung zu öffnen, eventuell muss die Erweiterung mindestens einmal ausgeführt worden sein und Sie sollten einen <key>Suchlauf</key> durchführen.',
    'Sorry, but the submitted GUID is invalid. Please contact the webmaster.'
        => 'Die übermittelte GUID ist ungültig. Bitte nehmen Sie mit dem Webmaster Kontakt auf.',
    'Sorry, but you are not allowed to access any entry point!'
        => 'Sie sind leider nicht berechtigt auf einen der kitFramework Zugangspunkte zuzugreifen.',
    'Sorry, there is currently no information available about <strong>%file%</strong>, please suggest a hint and help to improve the Configuration Editor!'
        => 'Entschuldigung, es ist leider keine Information zu <strong>%file%</strong> verfügbar, bitte schlagen Sie einen Hinweis vor und helfen Sie mit den Konfigurations Editor zu verbessern!',
    'Source'
        => 'Quelltext',
    'Sources'
        => 'Quelltexte',
    'Start search run'
        => 'Suchlauf starten',
    'Submit'
        => 'Übermitteln',
    'Successfull created a account for the user %name%.'
        => 'Für den Benutzer %name% wurde ein Konto eingerichtet.',
    'Successfull installed the extension %extension%.'
        => 'Die Erweiterung %extension% wurde erfolgreich installiert.',
    'Successful scanned the kitFramework for *.json configuration files'
        => 'Das kitFramework wurde nach *.json Konfigurationsdateien durchsucht',
    'Successfull scanned the kitFramework for installed extensions.'
        => 'Das kitFramework wurde nach installierten Erweiterungen durchsucht.',
    'Successfull scanned the kitFramework online catalog for available extensions.'
        => 'Der online Katalog für das kitFramework wurde nach verfügbaren Erweiterungen durchsucht.',
    'Successfull uninstalled the extension %extension%.'
        => 'Die Erweiterung %extension% wurde erfolgreich entfernt.',
    'Successfull updated the extension %extension%.'
        => 'Die Erweiterung %extension% wurde erfolgreich aktualisiert.',
    'Support'
        => 'Unterstützung',
    'Switch back to the administration of this user account'
        => 'Zur Verwaltung dieses Benutzerkontos zurückkehren',
    'Switch to this user to see the real active roles'
        => 'Zu diesem Anwender umschalten um die aktiven Rechte zu sehen',

    'Template'
        => 'Vorlage',
    'Test email'
        => 'Test E-Mail',
    'Thank you for using the kitFramework'
        => 'Vielen Dank für den Einsatz des kitFramework',
    'The account for the user %name% was successfull deleted.'
        => 'Das Benutzerkonto für %name% wurde gelöscht.',
    'The account was not changed.'
        => 'Das Benutzerkonto wurde nicht verändert.',
    'The account was succesfull updated.'
        => 'Das Benutzerkontor wurde aktualisiert',
    'The account with the ID %id% does not exists!'
        => 'Das Benutzerkonto mit der ID %id% existiert nicht!',
    'The account with the username or email address %name% does not exists!'
        => 'Es existiert kein Benutzerkonto für den Benutzername oder die E-Mail Adresse %name%!',
    'The both passwords you have typed in does not match, please try again!'
        => 'Die beiden Passwörter die Sie eingegeben haben stimmen nicht überein, bitte versuchen Sie es noch einmal!',
    'The configuration file <strong>%file%</strong> was successful saved.'
        => 'Die Konfigurationsdatei <strong>%file%</strong> wurde erfolgreich gesichert.',
    'The controller has detected <strong>%count%</strong> configuration files with the name <strong>%filename%</strong> and loaded the first hit into the editor.'
        => 'Der Controller hat <strong>%count%</strong> Konfigurationsdateien mit der Bezeichnung <strong>%filename%</strong> gefunden und den ersten Treffer in den Editor geladen.',
    'The email address %email% is already used by another account!'
        => 'Die E-Mail Adresse %email% wird bereits von einem anderen Benutzerkonto verwendet!',
    'The email address %email% is invalid!'
        => 'Die E-Mail Adresse %email% ist ungültig, bitte prüfen Sie Ihre Eingabe!',
    'The extension with the ID %extension_id% does not exists!'
        => 'Die Erweiterung mit der ID %extension_id% existiert nicht!',
    'The file %file% does not exists in Gist %gist_id%!'
        => 'Die Datei %file% existiert nicht im Gist %gist_id%',
    'The form seems to be compromitted, can not check the data!'
        => 'Das Formular scheint kompromitiert worden zu sein, kann die Daten nicht ändern!',
    'The extension.json of <b>%name%</b> does not contain all definitions, check GUID, Group and Release!'
        => 'Die Beschreibungsdatei extension.json für die Erweiterung <b>%name%</b> enthält nicht alle Definitionen, prüfen Sie <i>GUID</i>, <i>Group</i> und <i>Release</i>!',
    'The kitFramework was never checked for the existing locale sources and translations, please start a search run!'
        => 'Das kitFramework wurde noch nie nach Quelltexten für Übersetzungen durchsucht, bitte starten Sie einen Suchlauf!',
    'The parameter <code>%parameter%[%value%]</code> for the kitCommand <code>~~ %command% ~~</code> is unknown, please check the parameter and the given value!'
        => 'Der Parameter <code>%parameter%[%value%]</code> für das kitCommand <code>~~ %command% ~~</code> ist nicht bekannt oder übergibt einen ungültigen Wert, bitte prüfen Sie Ihre Eingabe!',
    'The password for the kitFramework was successfull changed. You can now <a href="%login%">login using the new password</a>.'
        => 'Ihr Passwort für das kitFramework wurde erfolgreich geändert.<br />Sie können sich jetzt <a href="%login%">mit Ihrem neuen Passwort anmelden</a>.',
    'The password you have typed in is not strength enough. Please choose a password at minimun 8 characters long, containing lower and uppercase characters, numbers and special chars. Spaces are not allowed.'
        => 'Das übermittelte Passwort ist nicht stark genug. Bitte wählen Sie ein Passwort mit mindestens 8 Zeichen Länge, mit einem Mix aus Groß- und Kleinbuchstaben, Zahlen und Sonderzeichen. Leerzeichen sind nicht gestattet.',
    'The password you typed in is not correct, please try again.'
        => 'Das angegebene Passwort is nicht korrekt, bitte geben Sie es erneut ein',
    'The received extension.json does not specifiy the path of the extension!'
        => 'Die empfangene extension.json enthält nicht den Installationspfand für die Extension!',
    'The received repository has an unexpected directory structure!'
        => 'Das empfangene Repository hat eine unterwartete Verzeichnisstruktur und kann nicht eingelesen werden.',
    'The record was successfull inserted'
        => 'Der Datensatz wurde erfolgreich eingefügt',
    'The record was successfull updated'
        => 'Der Datensatz wurde erfolgreich aktualisiert!',
    'The record with the ID %id% does not exists!'
        => 'Es existiert kein Datensatz mit der ID %id%!',
    'The requested page could not be found!'
        => 'Die angeforderte Seite wurde nicht gefunden!',
    'The submitted GUID is expired and no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.'
        => 'Die übermittelte GUID ist abgelaufen und nicht länger gültig.<br />Bitte <a href="%password_forgotten%">fordern Sie einen neuen Link an</a>.',
    'The submitted GUID was already used and is no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.'
        => 'Die übermittelte GUID wurde bereits verwendet und ist nicht mehr gültig.<br />Bitte <a href="%password_forgotten%">fordern Sie einen neuen Link an</a>.',
    'The test mail to %email% was successfull send.'
        => 'Die Test E-Mail wurde erfolgreich an %email% versendet!',
    'There exists no catalog entry for the extension %name% with the GUID %guid%.'
        => 'Es existiert kein Katalog Eintrag für die Erweiterung %name% mit der GUID %guid%.',
    'There exists no user with the submitted email address.'
        => 'Die übermittelte E-Mail Adresse kann keinem Benutzer zugeordnet werden.',
    'There is no help available for the kitCommand <b>%command%</b>.'
        => 'Für das kitCommand <b>%command%</b> ist keine Hilfe verfügbar.',
    "This link enable you to change your password once within 24 hours."
        => "Dieser Link ermöglicht es Ihnen, ihr Passwort einmal innerhalb von 24 Stunden zu ändern.",
    'The ROLE_USER is needed if you want enable the user to access and change his own account. The ROLE_ADMIN is the highest available role and grant access to really everything.'
        => 'Das Recht ROLE_USER ist erforderlich um einem Benutzer Zugriff auf sein Konto zu ermöglichen. Das Recht ROLE_ADMIN ist das höchste verfügbare Recht und garantiert einen uneingeschränkten Zugriff auf alle Funktionen des kitFramework.',
    'The username %username% is already in use, please select another one!'
        => 'Der Benutzername %username% wird bereits verwendet, bitte wählen Sie einen anderen Benutzernamen.',
    'There are no roles assigned to this user.'
        => 'Diesem Benutzer sind keine Rechte zugewiesen',
    '<p>This list contain %count% translation sources.</p><p>Click at <em>ID</em> or at <em>References</em> for information in which files the sources are used.</p>'
        => '<p>Diese Liste enthält %count% Quelltexte als Basis für die Übersetzungen.</p><p>Klicken Sie auf <em>ID</em> oder <em>Verweise</em> um zu erfahren in welchen Dateien diese Quelltexte verwendet werden.</p>',
    'This user are assigned %count% roles.'
        => 'Diesem Benutzer sind insgesamt <b>%count%</b> Rechte zugewiesen.',
    'This value is not a valid email address.'
        => 'Es wurde keine gültige E-Mail Adresse übergeben!',

    'Update available!'
        => 'Aktualisierung verfügbar!',
    'Updated the catalog data for <b>%name%</b>.'
        => 'Die Katalogdaten für die Erweiterung <b>%name%</b> wurden aktualisiert.',
    'Updated the register data for <b>%name%</b>.'
        => 'Die Registrierdaten für die Erweiterung <b>%name%</b> wurden aktualisiert.',
    'Usage'
        => 'Verwendung',
    'User roles may depend from others and can be set or extended dynamically by the kitFramework extensions. To see the roles really associated to this account if the user is authenticated use the "switch to" button.'
        => 'Benutzerrechte können von einander abhängig sein und dynamisch durch kitFramework Anwendungen erweitert werden. Um die Benutzerrechte zu sehen, die tatsächlich einem angemeldeten Anwender zugewiesen sind nutzen Sie bitte die Funktion "Zum Anwender umschalten".',
    'Username'
        => 'Benutzername',
    'Username or email address'
        => 'Benutzername oder <nobr>E-Mail</nobr> Adresse',


    'Vendor'
        => 'Anbieter',
    'View and edit the kitFramework configuration files'
        => 'Die kitFramework Konfigurationsdateien einsehen und bearbeiten',
    'View the general help for kitCommands'
        => 'Die allgemeine Hilfe zu den kitCommands anzeigen',
    'View the helpfile for %command%'
        => 'Die Hilfedatei zu %command% anzeigen',
    'Visit the Wiki for %command% and learn more about it!'
        => 'Besuchen Sie das Wiki zu %command% und erfahren Sie mehr über die Möglichkeiten!',

    'We have send a link to your email address %email%.'
        => 'Wir haben Ihnen einen Link an Ihre E-Mail Adresse %email% gesendet.',
    'Welcome' =>
        'Herzlich Willkommen!',
    'Welcome back, %user%! Please select the entry point you want to use.'
        => 'Herzlich willkommen, %user%! Bitte wählen Sie den gewünschten Zugangspunkt.',

    'You are not allowed to access this resource!'
        => 'Sie sind nicht befugt auf diese Resource zuzugreifen.',
    'You have already an account? <a href="%login_url%">Please login</a>!'
        => 'Sie haben bereits ein Benutzerkonto? <a href="%login_url%">Melden Sie sich an</a>!',
    'Your are not authenticated, please login!'
        => 'Sie sind nicht angemeldet, bitte authentifizieren Sie sich zunächst!',
    'Your account is locked, but it seems that you have not activated your account. Please use the activation link you have received.'
        => 'Ihr Benutzerkonto ist gesperrt, es sieht allerdings so aus, als ob Sie das Konto noch nicht aktiviert haben. Bitte verwenden Sie den Aktivierungslink den Sie erhalten haben.',
    'Your account is locked, please contact the webmaster.'
        => 'Ihr Benutzerkonto ist gesperrt, bitte nehmen Sie Verbindung mit dem Webmaster auf.',
    'Your account was succesfull updated.'
        => 'Ihr Benutzerkonto wurde erfolgreich geändert.',
);
