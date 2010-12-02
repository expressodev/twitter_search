<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Twitter Search 2 module by Crescendo (support@crescendo.net.nz)
 * Concept based on Twitter Search for EE 1.6 by David Rencher (http://www.lumis.com/)
 * 
 * Copyright (c) 2010 Crescendo Multimedia Ltd
 * All rights reserved.
 * 
 * This software is licensed under a Creative Commons Attribution-ShareAlike 3.0 License.
 * http://creativecommons.org/licenses/by-sa/3.0/
 * 
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

$plugin_info = array(
	'pi_name'			=> 'Twitter Search 2',
	'pi_version'		=> '2.0.2',
	'pi_author'			=> 'Crescendo Multimedia',
	'pi_author_url'		=> 'http://www.crescendo.net.nz/',
	'pi_description'	=> 'Find tweets based on search text or location',
	'pi_usage'			=> twitter_search::usage()
);

class Twitter_search
{
	var $twitter_url = 'http://search.twitter.com/search.json?callback=';
	var $return_data = '';
	
	function Twitter_search()
	{
		$this->EE =& get_instance();
		
		// detect cURL library
		if (!function_exists('curl_init'))
		{
			$this->return_data = 'The cURL library must be installed and enabled to use Twitter Search!';
			return;
		}
		
		if (trim($this->EE->TMPL->tagdata) == '') { return; }
		
		$json_data = $this->query_twitter($this->EE->TMPL->tagparams);
		if (empty($json_data))
		{
			// no twitter results
			return $this->no_tweets($this->EE->TMPL->tagdata);
		}
		elseif (is_string($json_data))
		{
			// probably a cURL error
			return $json_data;
		}
		
		// check we found some tweets
		if (count($json_data->results) == 0)
		{
			return $this->no_tweets($this->EE->TMPL->tagdata);
		}
		
		// configure typography library
		$this->EE->load->library('typography'); 
		$this->EE->typography->initialize();
		
		$this->EE->typography->allow_img_url = 'n';
		$this->EE->typography->auto_links = $this->EE->TMPL->fetch_param('auto_links') == "yes";
		$this->EE->typography->convert_curly = FALSE;
		$this->EE->typography->parse_smileys = FALSE;
		$this->EE->typography->text_format = "none";
		$this->EE->typography->word_censor = $this->EE->TMPL->fetch_param('word_censor') != "no";
		
		// loop over tweets and build array for template
		$tweets = array();
		$i=0;
		foreach($json_data->results as $tweet_data)
		{
			// run standard properties through xss filter
			foreach(array('profile_image_url', 'from_user', 'to_user_id', 'id', 'from_user_id', 'geo', 'iso_language_code', 'source') as $key)
			{
				$tweets[$i][$key] = $this->EE->security->xss_clean($tweet_data->$key);
			}
			
			// run tweet text through typography class
			$tweets[$i]['text'] = $this->EE->typography->parse_type($tweet_data->text);
			
			// do we need to nofollow links?
			if ($this->EE->typography->auto_links AND $this->EE->TMPL->fetch_param('nofollow') != "no")
			{
				$tweets[$i]['text'] = str_ireplace('<a href="', '<a rel="nofollow" href="', $tweets[$i]['text']);
			}
			
			// php datestamps
			$tweets[$i]['created_at'] = strtotime($tweet_data->created_at);
			$tweets[$i]['relative_date'] = $this->EE->localize->format_timespan(time() - $tweets[$i]['created_at']);
			
			$tweets[$i]['no_tweets'] = 0;
			$i++;
		}
		
		// output template content
		$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $tweets);
	}
	
	function query_twitter($tagparams)
	{
		$query_url = $this->twitter_url;
		$skip_vars = array('cache', 'refresh', 'word_censor', 'auto_links');
		
		// generate query string
		foreach ($tagparams as $key => $value)
		{
			if (!in_array($key, $skip_vars))
			{
				$query_url .= "&".$key."=".urlencode($value);
			}
		}
		
		// request data from twitter
		if (!extension_loaded('curl') OR !function_exists('curl_init'))
		{
			return "cURL library not found!";
		}
		
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $query_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		
		$return_data = curl_exec($ch);
		$curl_error = curl_error($ch);
		
		// check for curl errors
		if (empty($curl_error))
		{
			// decode JSON data
			return json_decode($return_data);
		}
		else
		{
			return $curl_error;
		}
	}
	
	function no_tweets($tagdata)
	{
		// based on no_results code in ./system/expressionengine/libraries/Template.php	
		if (strpos($tagdata, 'if no_tweets') !== FALSE && preg_match("/".LD."if no_tweets".RD."(.*?)".LD.'\/'."if".RD."/s", $tagdata, $match)) 
		{
			if (stristr($match[1], LD.'if'))
			{
				$match[0] = $this->EE->functions->full_tag($match[0], $tagdata, LD.'if', LD.'\/'."if".RD);
			}
			
			// return the no_tweets template
			return substr($match[0], strlen(LD."if no_tweets".RD), -strlen(LD.'/'."if".RD));
		}
		else
		{
			return '';
		}
	}
	
	function usage()
	{
		return <<<EOF
{exp:twitter_search q="query"}

Find tweets matching a specific query. Accepts pretty much anything, e.g.

* q="food"
* q="#ExpressionEngine"
* q="@CrescendoNZ"
* q="from:CrescendoNZ"
* q="to:CrescendoNZ"

Optional Parameters

* lang="en" restricts tweets to the given language, given by an ISO 639-1 code
* rpp="" the number of tweets to return, up to a max of 100
* page="" the page number (starting at 1) to return, up to a max of roughly 1500 results
* geocode="latitude,longitude,radius" returns tweets by users located within a given radius of the location
* cache="yes" refresh="5" cache tags output
* auto_links="yes" converts url's in the {text} into links
* nofollow="no" sorry about the double negative - this disables rel="nofollow" on auto_links
* word_censor="no" turns off the EE word censor
* for advanced parameters, see http://dev.twitter.com/doc/get/search

Tag Variables available

* {text}
* {to_user_id}
* {from_user}
* {id}
* {from_user_id}
* {iso_language_code}
* {profile_image_url}
* {source}
* {created_at format="%D, %M %d %Y - %g:%i %a"}
* {relative_date} - the relative date expressed in words, e.g. "3 hours, 10 minutes ago"
* {if no_tweets} - conditional only, displayed if no results found

Example Usage

{exp:twitter_search q="food" geocode="-41.291285,174.775134,10km" rpp="5" lang="en" auto_links="yes" cache="yes" refresh="5"}
<div class="tweet">
	{text}<br />
	{from_user} {relative_date}
	{if no_tweets}Nothing to display!{/if}
</div>
{/exp:twitter_search}

Concept based on Twitter Search for EE 1.6 by David Rencher (http://www.lumis.com/)
Rewritten for ExpressionEngine 2.0 and PHP5 by Crescendo Multimedia (http://www.crescendo.net.nz/)
EOF;
	}
}

/* End of file pi.twitter_search.php */