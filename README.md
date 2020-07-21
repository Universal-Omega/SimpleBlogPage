# SimpleBlogPage
Mediawiki extension based on BlogPage with different features &amp; no dependencies

I created SimpleBlogPage to fix some problems I had with BlogPage. It is based on BlogPage version 2.9.0 and differs in several respects:

Major changes:

- Got rid of dependencies on SocialProfile, VoteNY and Comments. So this extension has NO dependencies!
- SimpleBlogPage is NOT compatible with BlogPage (e.g it uses the same namespace), you MUST NOT have both extensions installed at the same time!
- You need to install SimpleBlogPage by adding require_once "$IP/extensions/SimpleBlogPage/SimpleBlogPage.php"; in your LocalSettings.php instead of wfLoadExtension( 'BlogPage' ); This is because of the switch to using subpages.

Features added:

- Added support for the <!--more--> tag in the blurb, so that it knows to stop grabbing text for the blurb after the tag. Made the default blurb grab much bigger so you can more easily tell when you forgot to use the <!--more--> tag.
- Added button to the WikiEditor toolbar to insert the <!--more--> tag. I drew the logo myself from scratch, pixel by pixel, so there are no copyright/licensing issues. Obviously, you'll only see the button if you actually have the WikiEditor extension enabled. 
- Added button to add syntax highlighting (the icon for which I again drew from scratch, to avoid licensing issues), which is a poor man's substitute for CodeEditor which I just can't seem to get working on my setup. 
- Added the author name to the "created" line under every listed blog post.
- Added the word count next to the blog post title.

Features removed: 

- Got rid of avatars, votes, comments, “most popular”, "opinions count", "about the author", and a few other social features 
- Got rid of all special pages except: Articles Home and Create Blog Post
- Blog posts can now only have a single author. Anyone can still edit everyone else's blog posts.

Potentially breaking changes:

- Switched to using subpages in order to get the author name for various functions. 
- CreateBlogPost has been changed to create blog posts under Blog:username/blogposttitle rather than Blog:blogposttitle. 
- The URL Blog:[username] has been hijacked to display a list of all of the user's posts in order of time created with the showUserPosts function 
- Pages with titles of the form Blog:[username] are not treated as blog posts, in fact they are hidden completely and cannot be viewed normally
- The newly created SimpleBlogPage.php enable subpages for the NS_BLOG namespace. 

Quality of life improvements / pessimizations:

- Got rid of all uses of cache (it wasn’t coded properly which was causing the blog home page to always be 15 minutes stale)
- Changed “created x days ago” in homepage to “created by [username] on datetime”.
- Added hideContentsub.css to articlesHome and blogpage in extension.json to hide messages like "go back to ..." and "View or restore 2 deleted revisions". If you want to see those messages just delete that filename from extension.json.
- Removed padding in main-page-left CSS to get rid of annoying gap between title and content.
- Disallow users from creating blog posts under another user's name. Make sure to disallow 'move' permissions for normal users as they can still move pages to under other user's names. Do this by adding the line
```php
    $wgGroupPermissions['user']['move'] = false;
```
    to your LocalSettings.php
- Increased number of posts on home page to ALL blog posts (well, 1 billion) rather than just the last 25
- Changed the right hand side "other editors" box to only appear when other people have edited the page. I got rid of the avatars and replaced them with just the usernames of the editors.

Bug fixes:

- Fixed misspelling of “cahce” to “cache”, which was a critical bug that was breaking homepage. Then I removed cache completely. 
- Got rid of duplicate categories bottom bar

Protip: https://validator.w3.org/ is a very useful tool for finding mismatched <div> and </div> tags. 
