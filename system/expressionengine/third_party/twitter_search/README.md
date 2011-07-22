Twitter Search 2
================

Twitter Search 2 is a simple plugin which queries Twitter for tweets based on a search string
you specify. You can easily find tweets sent from or to a user, or based on any query string.

Concept and syntax based on original Twitter Search module for EE 1.6 by [David Rencher](http://www.lumis.com/).

Rewritten for ExpressionEngine 2.0 by [Crescendo](http://crescendo.net.nz/)

Basic Queries
-------------

	{exp:twitter_search q="query"}

Find tweets matching a specific query. Accepts pretty much anything, e.g.

* `q="food"`
* `q="#winning"`
* `q="@CrescendoNZ"`
* `q="@CrescendoNZ #eecms"`
* `q="from:CrescendoNZ"`
* `q="to:CrescendoNZ"`

Optional Parameters
-------------------

* `lang="en"` - restricts tweets to the given language, given by an ISO 639-1 code
* `rpp=""` - the number of tweets to return, up to a max of 100
* `page=""` - the page number (starting at 1) to return, up to a max of roughly 1500 results
* `geocode="latitude,longitude,radius"` - returns tweets by users located within a given radius of the location
* `cache="yes" refresh="5"` - standard ExpressionEngine tag output caching
* `auto_links="no"` - don't convert URLs in the {text} into links
* `nofollow="no"` - this disables rel="nofollow" on links
* `word_censor="no"` - turns off the EE word censor
* for advanced parameters, see [http://dev.twitter.com/doc/get/search](http://dev.twitter.com/doc/get/search)

Tag Variables available
-----------------------

* `{text}`
* `{to_user_id}`
* `{from_user}`
* `{id}`
* `{from_user_id}`
* `{iso_language_code}`
* `{profile_image_url}`
* `{tweet_url}` - a permanent link to this tweet
* `{source}` - an html link to the Twitter client used to post this tweet
* `{tweet_date format="%D, %M %d %Y - %g:%i %a"}`
* `{relative_tweet_date}` - the relative date expressed in words, e.g. "3 hours, 10 minutes ago"
* `{if no_tweets}{/if}` - conditional variable, content displayed if no tweets are found

Legacy variables, still available:

* `{created_at}`
* `{relative_date}`

Example Usage
-------------

	{exp:twitter_search q="food" geocode="-41.291285,174.775134,10km" rpp="5" lang="en" auto_links="yes" cache="yes" refresh="5"}
	<div class="tweet">
		{text}<br />
		{from_user} <a href="{tweet_url}">{relative_tweet_date}</a>
		{if no_tweets}Nothing to display!{/if}
	</div>
	{/exp:twitter_search}

Please note this plugin is limited to what the Twitter search API will let you query - usually
only tweets from the last 5 days or so.

Changelog
---------

**2.0.6** *(2011-07-23)*

* Renamed tweet date variables to avoid overlapping with channel entry date variables.

**2.0.5** *(2011-05-01)*

* Fixed issue displaying {id} variable
* Added {tweet_url} variable

**2.0.4** *(2011-03-30)*

* URLs are converted to links by default
* @usernames and #hashtags are now converted to links

**2.0.3** *(2010-12-23)*

* Removed HTML encoding from {source} so that it may be used directly in templates

**2.0.2** *(2010-12-02)*

* Added rel="nofollow" to links by default, this can be configured in the tag

**2.0.1** *(2010-11-26)*

* Added check for cURL library, and an error message on the template if it is not found

**2.0** *(2010-08-08)*

* Initial release