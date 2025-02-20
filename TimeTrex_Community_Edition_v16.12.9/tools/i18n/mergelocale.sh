#!/bin/bash

##$License$##
##
# File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
# http://osc.co.cr
##

# This script is useful for merging latest changes from the master .pot file into
# a given locale file, and then generating the corresponding .mo binary file.

# This script is intended to be run from the tools/i18n directory

# arg1 should be the locale ID
lc=$1
cd ../../interface/locale

#Clear the en_US translation to avoid fuzzy translations, always create it from scratch.
if [ $lc == 'en_US' ] ; then
	rm -f $lc/LC_MESSAGES/messages.po
  cat << EOF > $lc/LC_MESSAGES/messages.po
# English translation for timetrex
msgid ""
msgstr ""
"Project-Id-Version: timetrex\n"
"Report-Msgid-Bugs-To: \n"
"PO-Revision-Date: N/A\n"
"Last-Translator: <N/A>\n"
"Language-Team: <N/A>\n"
"Language: en\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"X-Launchpad-Export-Date: 2015-07-10 17:45+0000\n"
"X-Generator: Launchpad (build 17620)\n"
EOF

fi

if [ $lc == 'yi_US' ] ; then
	#This is a test locale, change all strings to something that stands out so we can find untranslated ones easily.
	cat $lc/LC_MESSAGES/messages.po | sed -e '15,$s/msgstr ""/msgstr "Z"/g' > $lc/LC_MESSAGES/messages.po.tmp
	mv $lc/LC_MESSAGES/messages.po.tmp $lc/LC_MESSAGES/messages.po
fi

#Don't use fuzzy matching with msgmerge to avoid issues with non-translating strings or mixing up of strings.
touch $lc/LC_MESSAGES/messages.po && \
	msguniq -u --no-wrap --use-first $lc/LC_MESSAGES/messages.po -o $lc/LC_MESSAGES/messages.po && \
	msgmerge -N --no-wrap -s --update $lc/LC_MESSAGES/messages.po ./messages.pot && \
	msgfmt -c -o $lc/LC_MESSAGES/messages.mo $lc/LC_MESSAGES/messages.po

#Convert to .JSON file
php ../../tools/i18n/po2json.php -i $lc/LC_MESSAGES/messages.po -o $lc/LC_MESSAGES/messages.json -n i18n_dictionary

rm -f $lc/LC_MESSAGES/messages.po~
