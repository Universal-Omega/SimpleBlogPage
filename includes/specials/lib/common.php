<?php

use MediaWiki\MediaWikiServices;

/**
 * Get the 1 billion newest blog posts from the database.
 *
 * @return string HTML
 */
function getNewestPosts($includeall = true, $AuthorName = '') {
	wfDebugLog( 'SimpleBlogPage', 'Got newest posts in ArticlesHome from DB' );
	$dbr = wfGetDB( DB_REPLICA );
	// Code sporked from Rob Church's NewestPages extension
	$res = $dbr->select(
		[ 'page' ],
		[
			'page_namespace', 'page_title', 'page_is_redirect',
			'page_id',
		],
		[
			'page_namespace' => NS_USER_BLOG,
			'page_is_redirect' => 0,
		],
		__METHOD__,
		[
			'ORDER BY' => 'page_id DESC',
			'LIMIT' => 1000000000
		]
	);

	$newestBlogPosts = [];
	foreach ( $res as $row ) {
		// only include blog posts by current user
		$titleObj = Title::makeTitle( NS_USER_BLOG, $row->page_title );
		// do not include Blog:[username] pages as they are not blog posts
		$authorOfPost = Title::newFromText( $titleObj->getText() )->getRootText();

		if ( ( $includeall || $authorOfPost === $AuthorName ) && strstr( $titleObj->getText(), '/' ) ) { 
			$newestBlogPosts[] = [
				'title' => $titleObj,
				'ns' => $row->page_namespace,
				'id' => $row->page_id
			];
		}
	}


	$output = '<div class="listpages-container">';
	if ( empty( $newestBlogPosts ) ) {
		$output .= wfMessage( 'ah-no-results' )->escaped();
	} else {
		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		foreach ( $newestBlogPosts as $newestBlogPost ) {
			$titleObj = $newestBlogPost['title'];
			$output .= '<div class="listpages-item">';
			$wordcount = 0;
			$blurb = ( new SimpleBlogPage( $newestBlogPost['title'] ) )->getBlurb(
				$newestBlogPost['title']->getText(),
				$newestBlogPost['ns'],
				300,
				$wordcount
			);
			$wordcount = max(0, $wordcount - 10);
			$output .= '<a href="' . htmlspecialchars( $titleObj->getFullURL() ) . '">' .
					htmlspecialchars( $titleObj->getSubpageText() ) .
					'</a> [' . strval($wordcount) . 
					' words]<div class="listpages-date">';

			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$author = $linkRenderer->makeKnownLink( Title::newFromText( $authorOfPost, NS_USER_BLOG ), $authorOfPost, [ 'style' => 'font-size: 10px;' ] );

//			$output .= 'created by ' . $author ' on ' . gmdate("Y M j D G:i:s T", strtotime( SimpleBlogPage::getCreateDate( $newestBlogPost['id'] ) ));
			$output .= '(' .
				wfMessage( 'blog-created-by',
						// need to strtotime() it because getCreateDate() now
						// returns the raw timestamp from the database; in the past
						// it converted it to UNIX timestamp via the SQL function
						// UNIX_TIMESTAMP but that was no good for our purposes
					$author,
					gmdate("Y M j D G:i:s T", strtotime( SimpleBlogPage::getCreateDate( $newestBlogPost['id'] ) ))
						//
						//)
				)->text() . ')';

			$output .= "</div>
			<div class=\"listpages-blurb\">\n" . $blurb .
				'</div><!-- .listpages-blurb -->
							</div><!-- .listpages-item -->
			<div class="visualClear"></div>' . "\n";
		}
	}

	$output .= '</div>' . "\n"; // .listpages-container

	return $output;
}


