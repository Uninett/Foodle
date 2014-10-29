<?php

class Pages_PageRSS {

        protected $config;      
        protected $parameters;
        
        protected $fdb;

        function __construct($config, $parameters) {
                $this->config = $config;
                $this->parameters = $parameters;
                
                $this->fdb = new FoodleDBConnector($this->config);
        }
        
        function show() {
        

                $url = 'https://foodl.org/api/e/feed/uninett-sj3gr86sb3';
                $as = new Data_EventStream($this->fdb);
                

                $feed = $this->parameters[0];
                $as->prepareFeed($feed);


                // $feed = $this->parameters[0];
                // header('Content-type: text/plain; charset=utf-8');
                // echo "hello world\n" . $feed;
                // print_r($as->getData());

                $feedData = $as->getData();

                $rssfeed = new RSS2FeedWriter();
                $rssfeed->setTitle('UNINETT Foodle');
                $rssfeed->setLink('https://foodl.org');
                $rssfeed->setDescription('Foodle is a simple tool for meeting invitations and polls');
                // $rssfeed->setImage('Testing the RSS writer class','http://www.ajaxray.com/projects/rss','http://www.rightbrainsolution.com/_resources/img/logo.png');
                

                foreach($feedData AS $feedItem) {
                        $rssitem = $rssfeed->createNewItem();

                        $rssitem->setTitle($feedItem['foodle']['name']);
                        $rssitem->setLink('https://foodl.org/foodle/' . $feedItem['foodle']['id']);
                        //The parameter is a timestamp for setDate() function
                        $rssitem->setDate($feedItem['foodle']['unix']);
                        $rssitem->setDescription($feedItem['foodle']['descr']);
                        $rssfeed->addItem($rssitem);
                }

                $rssfeed->generateFeed();
                exit;




        }


        
}