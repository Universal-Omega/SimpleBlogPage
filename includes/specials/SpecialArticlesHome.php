<?php
/**
 * Blogs homepage - lists all blog articles by everyone in chronological order.
 *
 * @file
 * @ingroup Extensions
 */

use MediaWiki\MediaWikiServices;

require_once "lib/common.php";

class ArticlesHome extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ArticlesHome' );
	}

	/**
	 * Show the new special page
	 *
	 * @param string $type What kind of articles to show? Hardcoded 'new'
	 */
	public function execute( $subPage ) {
		$out = $this->getOutput();

		// Add CSS
		$out->addModuleStyles( [ 'ext.simpleBlogPage.articlesHome' ] );

		// Determine the page title and set it
		$name = $this->msg( 'ah-all-posts' );

		$out->setPageTitle( $name );

		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		$today = $contLang->date( wfTimestampNow() );

		// Start building the HTML output
		$output = '<div class="main-page-left">';
		$output .= '<div class="logged-in-articles">';
		$output .= '<p class="main-page-sub-links"><a href="' .
			htmlspecialchars( SpecialPage::getTitleFor( 'CreateBlogPost' )->getFullURL() ) . '">' .
			$this->msg( 'ah-write-article' )->escaped() . '</a> - <a href="' .
				// original used date( 'F j, Y' ) which returned something like
				// December 5, 2008
				htmlspecialchars( Title::makeTitle( NS_CATEGORY, $today )->getFullURL() ) . '">' .
				$this->msg( 'ah-todays-articles' )->escaped() . '</a> - <a href="' .
				htmlspecialchars( Title::newMainPage()->getFullURL() ) . '">' .
					$this->msg( 'mainpage' )->escaped() . '</a></p>' . "\n\n";

		$output .= getNewestPosts();

		$output .= '</div>';
		$output .= '</div>';

		$output .= '<div class="visualClear"></div>';

		$out->addHTML( $output );
	}

}
