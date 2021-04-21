<?php
/**
 * Translations of the User_blog namespace.
 *
 * @file
 */

$namespaceNames = [];

// For wikis where the SimpleBlogPage extension is not installed.
if ( !defined( 'NS_USER_BLOG' ) ) {
	define( 'NS_USER_BLOG', 900 );
}

if ( !defined( 'NS_USER_BLOG_TALK' ) ) {
	define( 'NS_USER_BLOG_TALK', 901 );
}

$wgNamespacesWithSubpages[900] = true;

/** English */
$namespaceNames['en'] = [
	NS_USER_BLOG => 'User_blog',
	NS_USER_BLOG_TALK => 'User_blog_talk',
];
