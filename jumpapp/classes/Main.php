<?php

/**
 * TO DO:
 * - use CSRF token in weatherdata and icon api
 *
 */

namespace Jump;

use Nette\Routing\RouteList;

class Main {

    private Cache $cache;
    private Config $config;
    private \Nette\Http\Request $request;
    private \Nette\Http\Session $session;

    public function __construct() {
        $this->config = new Config();
        $this->cache = new Cache($this->config);
        $this->router = new RouteList;

        // Set up the routes that Jump expects.
        $this->router->addRoute('/tag/<tag>', [
			'class' => 'Jump\Pages\TagPage'
		]);
        $this->router->addRoute('/api/icon?siteurl=<siteurl>', [
			'class' => 'Jump\API\Icon'
		]);
        $this->router->addRoute('/api/unsplash[/<token>]', [
			'class' => 'Jump\API\Unsplash'
		]);
        $this->router->addRoute('/api/weather[/<token>[/<lat>[/<lon>]]]', [
      'class' => 'Jump\API\Weather'
    ]);
        $this->router->addRoute('/api/health', [
      'class' => 'Jump\API\Health'
    ]);
    }

    function init() {
        // Create a request object based on globals so we can utilise url rewriting etc.
        $this->request = (new \Nette\Http\RequestFactory)->fromGlobals();

        // Initialise a new session using the request object.
        $this->session = new \Nette\Http\Session($this->request, new \Nette\Http\Response);
        $this->session->setName($this->config->get('sessionname'));
        $this->session->setExpiration($this->config->get('sessiontimeout'));

        // Get a Nette session section for CSRF data.
        $csrfsection = $this->session->getSection('csrf');

        // Create a new CSRF token within the section if one doesn't exist already.
        if (!$csrfsection->offsetExists('token')){
            $csrfsection->set('token', bin2hex(random_bytes(32)));
        }

        // Try to match the correct route based on the HTTP request.
        $matchedroute = $this->router->match($this->request);

        // If we do not have a matched route then just serve up the home page.
        $outputclass = $matchedroute['class'] ?? 'Jump\Pages\HomePage';

        // Instantiate the correct class to build the requested page, get the
        // content and return it.
        $page = new $outputclass($this->config, $this->cache, $this->session, $matchedroute ?? null);
        return $page->get_output();
    }

}
