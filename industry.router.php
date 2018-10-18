<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \classes\middleware\ValidateParam as ValidateParam;
use \classes\middleware\ValidateParamURL as ValidateParamURL;
use \classes\middleware\ApiKey as ApiKey;
use \classes\SimpleCache as SimpleCache;
use \modules\enterprise_customer\Industry as Industry;

    // POST api to create new industry
    $app->post('/enterprise_customer/industry/data/new', function (Request $request, Response $response) {
        $i = new Industry($this->db);
        $i->lang = (empty($_GET['lang'])?$this->settings['language']:$_GET['lang']);
        $datapost = $request->getParsedBody();
        $i->username = $datapost['Username'];
        $i->token = $datapost['Token'];
        $i->industry = $datapost['Industry'];
        $body = $response->getBody();
        $body->write($i->add());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('Industry','1-50','required'))
        ->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam('Username','1-50','required'));

    // POST api to update industry
    $app->post('/enterprise_customer/industry/data/update', function (Request $request, Response $response) {
        $i = new Industry($this->db);
        $i->lang = (empty($_GET['lang'])?$this->settings['language']:$_GET['lang']);
        $datapost = $request->getParsedBody();
        $i->username = $datapost['Username'];
        $i->token = $datapost['Token'];
        $i->industryid = $datapost['IndustryID'];
        $i->industry = $datapost['Industry'];
        $body = $response->getBody();
        $body->write($i->update());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('IndustryID','1-11','numeric'))
        ->add(new ValidateParam('Industry','1-50','required'))
        ->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam('Username','1-50','required'));

    // POST api to delete industry
    $app->post('/enterprise_customer/industry/data/delete', function (Request $request, Response $response) {
        $i = new Industry($this->db);
        $i->lang = (empty($_GET['lang'])?$this->settings['language']:$_GET['lang']);
        $datapost = $request->getParsedBody();
        $i->industryid = $datapost['IndustryID'];
        $i->username = $datapost['Username'];
        $i->token = $datapost['Token'];
        $body = $response->getBody();
        $body->write($i->delete());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('IndustryID','1-11','numeric'))
        ->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam('Username','1-50','required'));

    // GET api to show all data industry pagination registered user
    $app->get('/enterprise_customer/industry/data/search/{username}/{token}/{page}/{itemsperpage}/', function (Request $request, Response $response) {
        $i = new Industry($this->db);
        $i->lang = (empty($_GET['lang'])?$this->settings['language']:$_GET['lang']);
        $i->search = filter_var((empty($_GET['query'])?'':$_GET['query']),FILTER_SANITIZE_STRING);
        $i->username = $request->getAttribute('username');
        $i->token = $request->getAttribute('token');
        $i->page = $request->getAttribute('page');
        $i->itemsPerPage = $request->getAttribute('itemsperpage');
        $body = $response->getBody();
        $body->write($i->searchIndustryAsPagination());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParamURL('query'));

    // GET api to show all data industry pagination public
    $app->map(['GET','OPTIONS'],'/enterprise_customer/industry/data/public/search/{page}/{itemsperpage}/', function (Request $request, Response $response) {
        $body = $response->getBody();
        $response = $this->cache->withEtag($response, $this->etag2hour.'-'.trim($_SERVER['REQUEST_URI'],'/'));
        if (SimpleCache::isCached(3600,["apikey","query","lang"])){
            $datajson = SimpleCache::load(["apikey","query","lang"]);
        } else {
            $i = new Industry($this->db);
            $i->lang = (empty($_GET['lang'])?$this->settings['language']:$_GET['lang']);
            $i->search = filter_var((empty($_GET['query'])?'':$_GET['query']),FILTER_SANITIZE_STRING);
            $i->page = $request->getAttribute('page');
            $i->itemsPerPage = $request->getAttribute('itemsperpage');
            $datajson = SimpleCache::save($i->searchIndustryAsPaginationPublic(),["apikey","query","lang"],null,3600);
        }
        $body->write($datajson);
        return classes\Cors::modify($response,$body,200,$request);
    })->add(new ValidateParamURL('lang','0-2'))
        ->add(new ValidateParamURL('query'))
        ->add(new ApiKey);

    // GET api to show all data industry
    $app->get('/enterprise_customer/industry/data/list/{username}/{token}', function (Request $request, Response $response) {
        $i = new Industry($this->db);
        $i->lang = (empty($_GET['lang'])?$this->settings['language']:$_GET['lang']);
        $i->username = $request->getAttribute('username');
        $i->token = $request->getAttribute('token');
        $body = $response->getBody();
        $body->write($i->showOptionIndustry());
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to show all data industry public
    $app->map(['GET','OPTIONS'],'/enterprise_customer/industry/data/list/public/', function (Request $request, Response $response) {
        $body = $response->getBody();
        $response = $this->cache->withEtag($response, $this->etag2hour.'-'.trim($_SERVER['REQUEST_URI'],'/'));
        if (SimpleCache::isCached(3600,["apikey","lang"])){
            $datajson = SimpleCache::load(["apikey","lang"]);
        } else {
            $i = new Industry($this->db);
            $i->lang = (empty($_GET['lang'])?$this->settings['language']:$_GET['lang']);
            $datajson = SimpleCache::save($i->showOptionIndustryPublic(),["apikey","lang"],null,3600);
        }
        $body->write($datajson);
        return classes\Cors::modify($response,$body,200,$request);
    })->add(new ValidateParamURL('lang','0-2'))->add(new ApiKey);