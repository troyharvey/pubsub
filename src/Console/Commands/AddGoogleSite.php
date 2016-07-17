<?php

namespace GenTux\GooglePubSub\Console\Commands;

use Google_Client;
use Google_Service_SiteVerification;
use Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequest;
use Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequestSite;
use Google_Service_SiteVerification_SiteVerificationWebResourceResource;
use Google_Service_SiteVerification_SiteVerificationWebResourceResourceSite;
use Google_Service_Webmasters;
use Google_Service_Webmasters_Resource_Sites;
use Google_Service_Webmasters_WmxSite;
use Illuminate\Console\Command;

class AddGoogleSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:add-site {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = $this->argument('url');

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setApplicationName(\Config::get('queue.connections.pubsub.app'));
        $client->setScopes([Google_Service_Webmasters::WEBMASTERS, Google_Service_SiteVerification::SITEVERIFICATION]);


        $site = new Google_Service_SiteVerification_SiteVerificationWebResourceResourceSite();
        $site->setIdentifier($url);
        $site->setType('SITE');

        $request = new Google_Service_SiteVerification_SiteVerificationWebResourceResource();
        $request->setSite($site);

        $service = new Google_Service_SiteVerification($client);
        $webResource = $service->webResource;
        $result = $webResource->insert('FILE',$request);

        $this->line(json_encode($result));

        /** @var Google_Service_SiteVerification_SiteVerificationWebResourceResource $site */
        foreach ($webResource->listWebResource()->getItems() as $site) {
            $this->line(' - ' . $site->getSite()->identifier);
        }





//        $site = new Google_Service_Webmasters_WmxSite();
//        $site->setPermissionLevel('siteOwner');
//        $site->setSiteUrl($url);
//        $service->sites->add($site);


        $site = new Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequestSite();
        $site->setIdentifier($url);
        $site->setType('SITE');

        $request = new Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequest();
        $request->setSite($site);
        $request->setVerificationMethod('FILE');

        $service = new Google_Service_SiteVerification($client);
        $webResource = $service->webResource;
        $result = $webResource->getToken($request);
        file_put_contents(public_path($result->token), "google-site-verification: {$result->token}");

        $site = new Google_Service_SiteVerification_SiteVerificationWebResourceResourceSite();
        $site->setIdentifier($url);
        $site->setType('SITE');

        $request = new Google_Service_SiteVerification_SiteVerificationWebResourceResource();
        $request->setSite($site);

        $webResource = $service->webResource;

        $this->info('Verified sites');
        /** @var Google_Service_SiteVerification_SiteVerificationWebResourceResource $site */
        foreach ($webResource->listWebResource()->getItems() as $site) {
            $this->line(' - ' . $site->getSite()->identifier);
        }



        $service = new Google_Service_Webmasters($client);
//        $service->sites->delete('https://9cd2abd5.ngrok.io/');
//        $service->sites->delete('https://4b5f5d05.ngrok.io/');
        $service->sites->add($url);

        $this->info('Webmasters sites');
        foreach($service->sites->listSites() as $site) {
            $this->line(' - ' . json_encode($site));
        }





    }
}
