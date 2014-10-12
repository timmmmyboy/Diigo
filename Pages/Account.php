<?php

    /**
     * Diigo pages
     */

    namespace IdnoPlugins\Diigo\Pages {

        /**
         * Default class to serve Diigo-related account settings
         */
        class Account extends \Idno\Common\Page
        {

            function getContent()
            {
                $t = \Idno\Core\site()->template();
                $body = $t->draw('account/diigo');
                $t->__(['title' => 'Diigo', 'body' => $body])->drawPage();
            }

            function postContent() {
                $username = $this->getInput('dgusername');
                $password = $this->getInput('dgpassword');
                $apiKey = $this->getInput('dgapiKey');
                \Idno\Core\site()->config->config['diigo'] = [
                    'dgusername' => $username,
                    'dgpassword' => $password,
                    'dgapiKey' => $apiKey
                ];
                \Idno\Core\site()->config()->save();
                \Idno\Core\site()->session()->addMessage('Your Diigo credentials were saved.');
                $this->forward('/account/diigo/');
            }

        }

    }