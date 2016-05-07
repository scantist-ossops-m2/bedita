<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2016 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\API\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\NotAcceptableException;
use Cake\Routing\Router;

/**
 * Base class for all API Controller endpoints.
 *
 * @since 4.0.0
 *
 * @property \BEdita\API\Controller\Component\JsonApiComponent $JsonApi
 */
class AppController extends Controller
{
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        if ($this->request->is(['json', 'jsonApi'])) {
            $this->RequestHandler->config('viewClassMap.json', 'BEdita/API.JsonApi');
            $this->loadComponent('BEdita/API.JsonApi', [
                'checkMediaType' => $this->request->is('jsonApi'),
            ]);
        }

        if (empty(Router::fullBaseUrl())) {
            Router::fullBaseUrl(
                rtrim(
                    sprintf('%s://%s/%s', $this->request->scheme(), $this->request->host(), $this->request->base),
                    '/'
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        $acceptHtml = (Configure::read('debug') || Configure::read('Accept.html'));
        if (!$this->request->is(['json', 'jsonApi']) && (!$this->request->is('html') || !$acceptHtml)) {
            throw new NotAcceptableException('Bad request content type "' . implode('" "', $this->request->accepts()) . '"');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        if ($this->request->is('html')) {
            $this->viewBuilder()->layout('default_api');
            $templatePath = $this->viewBuilder()->templatePath();
            $templatePath = substr($templatePath, 0, strrpos($templatePath, DS));
            $this->viewBuilder()->templatePath($templatePath . 'Common');
            $this->viewBuilder()->template('html_json');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterFilter(Event $event)
    {
        if ($this->request->is('json')) {
            $this->response->type('json');
        }
    }
}
