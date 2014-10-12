<?php

    namespace IdnoPlugins\Diigo {
    
        class Main extends \Idno\Common\Plugin {

            function registerPages() {
                
                // Register settings page
                    \Idno\Core\site()->addPageHandler('account/diigo/?','\IdnoPlugins\Diigo\Pages\Account');

                /** Template extensions */
                // Add menu items to admin screen
                    \Idno\Core\site()->template()->extendTemplate('account/menu/items','account/diigo/menu');
            }

            function registerEventHooks() {

                \Idno\Core\site()->syndication()->registerService('diigo', function () {
                    return $this->hasDiigo();
                }, ['bookmark']);

                // Push likes to Diigo
                \Idno\Core\site()->addEventHook('post/bookmark/diigo', function (\Idno\Core\Event $event) {
                    if ($this->hasDiigo()) {
	                    $object = $event->data()['object'];
                        $digObj = $this->connect();
                        $url = $object->body;
                        $title = $object->getTitleFromURL($url);
                        $tags = str_replace('#','',implode(',', $object->getTags()));
                        $desc = str_replace($object->getTags(),'',$object->description);
                        $optionalData = array('tags'=>$tags,'desc'=>$desc);
                        $access = $object->getAccess();
                        if ($access == 'PUBLIC'){
	                        $optionalData['shared']='yes';
                        }
                        $response = json_decode($digObj->updateBookMark($url, $title, $optionalData), true);
                        if ($response) {
                            $object->setPosseLink('diigo', 'https://www.diigo.com/user/' . \Idno\Core\site()->config()->diigo['dgusername']);
                            $object->save();
                        }
                  
				  	} 
                });
            }

            /**
             * Connect to Diigo
             * @return bool|\Diigo
             */
            function connect(){
            	require_once(dirname(__FILE__) . '/external/diigo.php');
                    
                    $dguser = \Idno\Core\site()->config()->diigo['dgusername'];
                    $dgpass = \Idno\Core\site()->config()->diigo['dgpassword'];
                    $dgapi = \Idno\Core\site()->config()->diigo['dgapiKey'];
                    $diigo = new \Diigo($dguser,$dgpass,$dgapi);
                    return $diigo;
               
                
            }

            /**
             * Can the current user use Diigo?
             * @return bool
             */
            function hasDiigo(){
               if (!empty(\Idno\Core\site()->config()->diigo)) {
                    return true;
               }
			   return false;
            }

        }

    }
