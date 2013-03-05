# Twitter Search plugin for ExpressionEngine (DEPRECATED)

-----

**This plugin uses the old Twitter v1 API, and will stop working in the near future!**

We recommend migrating to an alternative Twitter plugin which has been updated to use the new API.

-----

Twitter Search 2 is a simple plugin which queries Twitter for tweets based on a search string
you specify. You can easily find tweets sent from or to a user, or based on any query string.

Concept and syntax are based on the original Twitter Search module for EE 1.6
by [David Rencher](http://www.lumis.com/).

Requirements
------------

* ExpressionEngine 2.1.3+
* PHP 5.2+ with the JSON extension enabled

Installation
------------

To install Twitter Search, simply copy the entire `twitter_search` folder to
`/system/expressionengine/third_party` on your server.

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

For performance, you should always include the standard ExpressionEngine
cache="yes" parameter, unless your site is in development (otherwise your
server will make a request every single time the page loads).

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
* `var_prefix="tweet"` - adds a prefix to all variables (see below)
* for advanced parameters, see [http://dev.twitter.com/doc/get/search](http://dev.twitter.com/doc/get/search)

Tag Variables available
-----------------------

* `{id}`
* `{text}`
* `{from_user}`
* `{from_user_id}`
* `{to_user}`
* `{to_user_id}`
* `{iso_language_code}`
* `{profile_image_url}`
* `{tweet_url}` - a permanent link to this tweet
* `{tweet_date format="%D, %M %d %Y - %g:%i %a"}`
* `{relative_tweet_date}` - the relative date expressed in words, e.g. "3 hours, 10 minutes ago"
* `{if no_tweets}{/if}` - conditional variable, content displayed if no tweets are found

Legacy variables, still available:

* `{created_at}`
* `{relative_date}`

When using the `var_prefix` parameter, all variables will be prefixed. This allows you to nest the plugin inside other
tags which may have naming conflicts (for example, the channel entries tag).

For example, when using `var_prefix="tweet"`, the following variables are available:

* `{tweet:text}`
* `{tweet:from_user}`
* etc

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

**2.0.8** *(2013-02-27)*

* Fixed PHP error caused by Twitter API changes.

**2.0.7** *(2012-01-05)*

* Removed {source} variable which is no longer being returned by the Twitter API.

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
