<?php

use MediaWiki\MediaWikiServices;

/**
 * All SimpleBlogPage's hooked functions. These were previously scattered all over
 * the place in various files.
 *
 * @file
 */
class SimpleBlogPageHooks {
	public static function onRegistration() {
		global $wgUserProfileDisplay;

		$wgUserProfileDisplay['articles'] = true;
	}

	/**
	 * Checks that the user is logged is, is not blocked via Special:Block and has
	 * the 'edit' user right when they're trying to edit a page in the NS_USER_BLOG NS.
	 *
	 * @param EditPage $editPage
	 * @return bool true if the user should be allowed to continue, else false
	 */
	public static function onAlternateEdit( EditPage $editPage ) {
		$context = $editPage->getContext();
		$output = $context->getOutput();
		$user = $context->getUser();

		$basetitle = $editPage->getTitle()->getRootText();

		$isnewpost = !$editPage->getTitle()->exists();
		$isnotowner = !( $basetitle === $user->getName() );

		// This prevents users from creating blog posts under another user's name. 
		$c0 = $isnewpost ? 'true' : 'false';
		$c1 = $isnotowner ? 'true' : 'false';

		if ( $editPage->getTitle()->getNamespace() == NS_USER_BLOG ) {
			if ( $user->isAnon() ) { // anons can't edit blog pages
				if ( $isnewpost ) {
					$output->addWikiMsg( 'blog-login' );
				} else {
					$output->addWikiMsg( 'blog-login-edit' );
				}

				return false;
			}

			if ( !$user->isAllowed( 'edit' ) || $user->isBlocked() ) {
				$output->addWikiMsg( 'blog-permission-required' );

				return false;
			}

			if ( $isnewpost && $isnotowner ) {
				$output->addWikiMsg( 'blog-newpost-denied' );

				return false;
			}
		}

		return true;
	}

	/**
	 * Calls SimpleBlogPage instead of standard Article for pages in the NS_USER_BLOG
	 * namespace.
	 *
	 * @param Title &$title
	 * @param Article|SimpleBlogPage &$article Instance of Article that we convert into a SimpleBlogPage
	 * @param RequestContext $context
	 */
	public static function onArticleFromTitle( Title &$title, &$article, $context ) {
		if ( $title->getNamespace() == NS_USER_BLOG ) {
			$out = $context->getOutput();

			if ( method_exists( $out, 'disableClientCache' ) ) {
				// MW 1.38+
				$out->disableClientCache();
			} else {
				$out->enableClientCache( false );
			}

			// Add CSS
			$out->addModuleStyles( 'ext.simpleBlogPage' );

			$article = new SimpleBlogPage( $title );
		}
	}

	/**
	 * @param array &$modules
	 * @param Skin $skin
	 * @return void
	 */
	public static function onCosmosRailBuilder( array &$modules, Skin $skin ): void {
		global $wgSimpleBlogPageDisplay;

		if (
			$skin->getTitle()->getNamespace() !== NS_USER_BLOG ||
			$wgSimpleBlogPageDisplay['recent_editors'] === false
		) {
			return;
		}

		$simpleBlogPage = new SimpleBlogPage( $skin->getTitle() );
		$content = $simpleBlogPage->recentEditors( false );

		if ( $content ) {
			$modules['SimpleBlogPage'] = [
				'body' => $content,
				'header' => 'blog-recent-editors',
				'class' => 'recent-container',
				'type' => 'normal',
			];
		}
	}
}
