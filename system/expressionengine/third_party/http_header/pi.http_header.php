<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'HTTP Header',
	'pi_version' => '1.1.0',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'https://github.com/rsanchez',
	'pi_description' => 'Set the HTTP Headers for your template.',
	'pi_usage' => 'See https://github.com/rsanchez/http_header',

);

/**
 * HTTP Header
 *
 * Set the HTTP Headers for your template.
 *
 * @author Rob Sanchez
 * @link https://github.com/rsanchez/http_header
 *
 * @property CI_Controller $EE
 */
class Http_header
{
	/**
	 * @var string the plugin result
	 */
	public $return_data = '';

	/**
	 * constructor and plugin renderer
	 *
	 * @return string
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		if ($this->EE->TMPL->fetch_param('status') !== FALSE)
		{
			$this->set_status($this->EE->TMPL->fetch_param('status'));
		}

		if ($this->EE->TMPL->fetch_param('location') !== FALSE)
		{
			$this->set_location($this->EE->TMPL->fetch_param('location'));
		}

		$charset = $this->EE->TMPL->fetch_param('charset') !== FALSE ? $this->EE->TMPL->fetch_param('charset') : $this->EE->config->item('charset');

		if ($this->EE->TMPL->fetch_param('content_type') !== FALSE)
		{
			$this->set_content_type($this->EE->TMPL->fetch_param('content_type'), $charset);
		}
		else
		{
			//thanks @mistermuckle, @pashamalla
			switch ($this->EE->TMPL->template_type)
			{
				case 'js':
					$this->set_content_type('text/javascript', $charset);
					break;
				case 'css':
					$this->set_content_type('text/css', $charset);
					break;
				default:
					$this->set_content_type('text/html', $charset);
			}
		}

		// Added by @pvledoux
		if ($this->EE->TMPL->fetch_param('content_disposition') !== FALSE)
		{
			$this->set_content_disposition($this->EE->TMPL->fetch_param('content_disposition'), $this->EE->TMPL->fetch_param('filename'));
		}
		
		if ($this->EE->TMPL->fetch_param('content_language') !== FALSE)
		{
			$this->set_content_language($this->EE->TMPL->fetch_param('content_language'));
		}

		// Added by @ccorda
		if ($this->EE->TMPL->fetch_param('cache_seconds') !== FALSE)
		{
			$this->set_cache($this->EE->TMPL->fetch_param('cache_seconds'));
		}

		if ($this->EE->TMPL->fetch_param('vary') !== FALSE)
		{
			$this->set_vary($this->EE->TMPL->fetch_param('vary'));
		}

		if ($this->EE->TMPL->fetch_param('access_control_allow_origin') !== FALSE)
		{
			$this->set_access_control_allow_origin($this->EE->TMPL->fetch_param('access_control_allow_origin'));
		}

		if ($this->EE->TMPL->fetch_param('x_frame_options') !== FALSE)
		{
			$this->set_x_frame_options($this->EE->TMPL->fetch_param('x_frame_options'));
		}

		if ($this->EE->TMPL->fetch_param('terminate') === 'yes')
		{
			foreach ($this->EE->output->headers as $header)
			{
				@header($header[0], $header[1]);
			}

			exit;
		}

		//this tricks the output class into NOT sending its own headers
		$this->EE->config->set_item('send_headers', FALSE);
		$this->EE->TMPL->template_type = 'cp_asset';

		return $this->return_data = $this->EE->TMPL->tagdata;
	}

	/**
	 * set the http status code
	 *
	 * @param int $code ex. 404
	 *
	 * @return void
	 */
	protected function set_status($code)
	{
		$this->EE->output->set_status_header($code);
	}

	/**
	 * set the Location header
	 *
	 * @param string $location full url or template/template string
	 *
	 * @return void
	 */
	protected function set_location($location)
	{
		if (strpos($location, '{site_url}') !== FALSE)
		{
			$location = str_replace('{site_url}', $this->EE->functions->fetch_site_index(1), $location);
		}

		if (strpos($location, LD.'path=') !== FALSE)
		{
			$location = preg_replace_callback('/'.LD.'path=[\042\047]?(.*?)[\042\047]?'.RD.'/', array($this->EE->functions, 'create_url'), $location);
		}

		//it's not a proper url, so it's a template/template string, make it a proper url
		if ( ! preg_match('#^/|[a-z]+://#', $location))
		{
			$location = $this->EE->functions->create_url($location);
		}

		$this->EE->output->set_header('Location: '.$location);
	}

	/**
	 * set the Content-Type header
	 *
	 * @param string $content_type ex. "text/html", "application/json"
	 * @param string $charset ex. "utf-8", "iso-8859-1" (optional)
	 *
	 * @return void
	 */
	protected function set_content_type($content_type, $charset = '')
	{
		//add a charset if there isn't one already defined in the $content_type string
		if ($charset && strpos($content_type, 'charset=') === FALSE)
		{
			$content_type .= '; charset='.strtolower($charset);
		}

		$this->EE->output->set_header('Content-Type: '.$content_type);
	}

	/**
	 * set the Content-Disposition header
	 *
	 * @author Pv Ledoux (@pvledoux)
	 * @param string $content_disposition ex. "attachment"
	 * @param string $filename (optional)
	 *
	 * @return void
	 */
	protected function set_content_disposition($content_disposition, $filename = '')
	{
		//add a filename if there isn't one already defined in the $content_disposition string
		if ($filename && strpos($content_disposition, 'filename=') === FALSE)
		{
			$content_disposition .= '; filename='.strtolower($filename);
		}

		$this->EE->output->set_header('Content-Disposition: '.$content_disposition);
	}

	/**
	 * set the various Caching headers
	 *
	 * @author Cameron Corda (@ccorda)
	 * @param int $cache_seconds ex. 300
	 *
	 * @return void
	 */
	protected function set_cache($cache_seconds)
	{
		// nonfirm that we're getting a number
		if (is_numeric($cache_seconds)) 
		{
			// set no-cache if set to 0, otherwise set cache-control
			if ($cache_seconds == 0) 
			{
				$this->EE->output->set_header('Pragma: no-cache');
				$this->EE->output->set_header('Cache-Control: no-cache');
			} 
			else 
			{
				$expires = gmdate('D, d M Y H:i:s', time() + $cache_seconds) . ' GMT';
				$this->EE->output->set_header('Pragma: public');
				$this->EE->output->set_header('Cache-Control: max-age='.$cache_seconds);
				$this->EE->output->set_header('Expires: '.$expires);
			}
		}
	}
	
	/**
	 * set the Content-Language header
	 *
	 * @author Maurizio Napoleoni (@mimo84)
	 * @param string $content_language ex. "en", "en-US"
	 *
	 * @return void
	 */
	protected function set_content_language($content_language)
	{
		$this->EE->output->set_header('Content-Language: '.$content_language);
	}

	/**
	 * Set the Vary header
	 * @param string $vary ex. "Accept", "User-Agent"
	 */
	protected function set_vary($vary)
	{
		$this->EE->output->set_header('Vary: '.$vary);
	}

	/**
	 * Set the Access-Control-Allow-Origin header
	 * @param string $header ex. "*"
	 */
	protected function set_access_control_allow_origin($header)
	{
		$this->EE->output->set_header('Access-Control-Allow-Origin: '.$header);
	}

	/**
	 * Set the X-Frame-Options header - prevents page from Clickjacking (control the site being used in iFrame)
	 * @param string $header
	 * ref: https://developer.mozilla.org/en-US/docs/Web/HTTP/X-Frame-Options
	 * EE ref: http://expressionengine.stackexchange.com/questions/26353/disable-x-frame-options-header-in-channel-form
	 */
	protected function set_x_frame_options($header)
	{
		$header = strtoupper($header);

		// Check Values passed
		if ($header !== 'DENY' && $header !== 'SAMEORIGIN' && strncmp($header, 'ALLOW-FROM', 10) === 0)
		{
			// Default
			$header = 'SAMEORIGIN';
		}

		$this->EE->output->set_header('X-Frame-Options: '.$header);
	}
}

/* End of file pi.http_header.php */
/* Location: ./system/expressionengine/third_party/http_header/pi.http_header.php */
