<?php
/**
 * Translations of the Blog namespace.
 *
 * @file
 */

$namespaceNames = [];

// For wikis where the SimpleBlogPage extension is not installed.
if ( !defined( 'NS_USER_BLOG' ) ) {
	define( 'NS_USER_BLOG', 500 );
}

if ( !defined( 'NS_USER_BLOG_TALK' ) ) {
	define( 'NS_USER_BLOG_TALK', 501 );
}

$wgNamespacesWithSubpages[500] = true;

/** English */
$namespaceNames['en'] = [
	NS_USER_BLOG => 'User_blog',
	NS_USER_BLOG_TALK => 'User_blog_talk',
];