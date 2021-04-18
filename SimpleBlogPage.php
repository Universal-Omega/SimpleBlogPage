<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SimpleBlogPage' );

	$wgMessagesDirs['SimpleBlogPage'] = __DIR__ . '/i18n';

	$wgExtensionMessagesFiles['SimpleBlogPageAlias'] = __DIR__ . '/includes/SimpleBlogPage.alias.php';
	$wgExtensionMessagesFiles['SimpleBlogPageNamespaces'] = __DIR__ . '/includes/SimpleBlogPage.namespaces.php';

	wfWarn(
		'Deprecated PHP entry point used for the SimpleBlogPage extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);

	return;
} else {
	die( 'This version of the SimpleBlogPage extension requires MediaWiki 1.35+' );
}
