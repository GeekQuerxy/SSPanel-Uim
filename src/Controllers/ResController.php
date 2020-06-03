<?php


namespace App\Controllers;

use Gregwar\Captcha\CaptchaBuilder;
use Slim\Http\Request;
use Slim\Http\Response;

class ResController
{
    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function captcha($request, $response, $args)
    {
        $id = $args['id'];
        $builder = new CaptchaBuilder();
        $builder->build();
        //$builder->getPhrase();
        $newResponse = $response->withHeader('Content-type', ' image/jpeg'); //->getBody()->write($builder->output());
        $newResponse->write($builder->output());
        return $newResponse;
    }
}
